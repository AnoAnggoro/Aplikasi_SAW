<?php
session_start();
if (empty($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

require_once __DIR__.'/db.php';
$type = $_GET['type'] ?? 'products';
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Laporan <?= ucfirst($type) ?> - PWA SAW</title>
<style>
@media print {
  .no-print { display: none !important; }
  @page { margin: 1.5cm; }
}
body { font-family: Arial, sans-serif; margin: 20px; }
.kop-surat { text-align: center; border-bottom: 3px solid #2d89ef; padding-bottom: 15px; margin-bottom: 20px; }
.kop-surat img { width: 80px; height: auto; }
.kop-surat h2 { margin: 8px 0; color: #2d89ef; }
.kop-surat p { margin: 4px 0; font-size: 13px; color: #666; }
.info-laporan { margin: 20px 0; }
.info-laporan table { width: 100%; max-width: 400px; }
.info-laporan td { padding: 4px 8px; font-size: 13px; }
.info-laporan td:first-child { font-weight: 600; width: 120px; }
table.data { width: 100%; border-collapse: collapse; margin-top: 20px; }
table.data th, table.data td { border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 12px; }
table.data th { background: #2d89ef; color: white; font-weight: 600; }
table.data tr:nth-child(even) { background: #f9f9f9; }
h3 { color: #2d89ef; margin-top: 20px; }
.footer { margin-top: 30px; text-align: right; font-size: 12px; }
.no-print { margin: 20px 0; }
</style>
</head>
<body>

<div class="kop-surat">
  <img src="img/logo.png" alt="Logo">
  <h2>SISTEM ANALISIS PRODUK FAST/SLOW MOVING</h2>
  <p>Metode Simple Additive Weighting (SAW)</p>
  <p>Alamat: Jl. Contoh No. 123, Kota | Telp: (021) 12345678 | Email: info@saw.com</p>
</div>

<div class="info-laporan">
  <table>
    <tr><td>Jenis Laporan</td><td>: <?= strtoupper($type) ?></td></tr>
    <tr><td>Tanggal Cetak</td><td>: <?= date('d F Y H:i:s') ?></td></tr>
    <tr><td>Dicetak Oleh</td><td>: <?= htmlspecialchars($_SESSION['user']) ?></td></tr>
  </table>
</div>

<?php if ($type === 'products'): ?>
  <h3>LAPORAN DATA PRODUK</h3>
  <table class="data">
    <thead>
      <tr><th>No</th><th>Kode</th><th>Nama</th><th>Kategori</th><th>Stok</th><th>Biaya (Rp)</th><th>Harga (Rp)</th><th>Terjual</th></tr>
    </thead>
    <tbody>
      <?php
      $stmt = $pdo->query("SELECT p.*, COALESCE(SUM(s.qty), 0) AS terjual FROM products p LEFT JOIN sales s ON s.product_id = p.id GROUP BY p.id ORDER BY p.kode");
      $no = 1;
      foreach ($stmt->fetchAll() as $r):
      ?>
      <tr>
        <td><?= $no++ ?></td>
        <td><?= htmlspecialchars($r['kode']) ?></td>
        <td><?= htmlspecialchars($r['nama']) ?></td>
        <td><?= htmlspecialchars($r['kategori'] ?: '-') ?></td>
        <td><?= $r['stok'] ?></td>
        <td><?= number_format($r['biaya'], 0, ',', '.') ?></td>
        <td><?= number_format($r['harga'], 0, ',', '.') ?></td>
        <td><?= $r['terjual'] ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

<?php elseif ($type === 'sales'): ?>
  <h3>LAPORAN DATA PENJUALAN</h3>
  <table class="data">
    <thead>
      <tr><th>No</th><th>ID</th><th>Kode</th><th>Nama</th><th>Qty</th><th>Harga</th><th>Total</th><th>Return</th><th>Tanggal</th></tr>
    </thead>
    <tbody>
      <?php
      $stmt = $pdo->query("SELECT * FROM sales ORDER BY sales_date DESC");
      $no = 1;
      $total_qty = 0;
      $total_revenue = 0;
      foreach ($stmt->fetchAll() as $s):
        $total = $s['qty'] * $s['harga'];
        $total_qty += $s['qty'];
        $total_revenue += $total;
      ?>
      <tr>
        <td><?= $no++ ?></td>
        <td><?= htmlspecialchars($s['id_penjualan'] ?: '-') ?></td>
        <td><?= htmlspecialchars($s['kode_produk']) ?></td>
        <td><?= htmlspecialchars($s['nama_produk']) ?></td>
        <td><?= $s['qty'] ?></td>
        <td><?= number_format($s['harga'], 0, ',', '.') ?></td>
        <td><?= number_format($total, 0, ',', '.') ?></td>
        <td><?= $s['return_qty'] ?></td>
        <td><?= $s['sales_date'] ?></td>
      </tr>
      <?php endforeach; ?>
      <tr style="font-weight:bold;background:#e3f2fd">
        <td colspan="4" style="text-align:right">TOTAL</td>
        <td><?= $total_qty ?></td>
        <td colspan="2"><?= number_format($total_revenue, 0, ',', '.') ?></td>
        <td colspan="2"></td>
      </tr>
    </tbody>
  </table>

<?php elseif ($type === 'analysis'): ?>
  <h3>LAPORAN ANALISIS SAW</h3>
  
  <?php
  $criteria = $pdo->query("SELECT * FROM criteria ORDER BY id")->fetchAll();
  $products = $pdo->query("SELECT * FROM products ORDER BY id")->fetchAll();
  
  if (count($criteria) > 0 && count($products) > 0):
    // load values
    $vals = [];
    foreach ($pdo->query("SELECT * FROM product_values")->fetchAll() as $v) {
      $vals[$v['product_id']][$v['criteria_id']] = $v['nilai'];
    }
    foreach ($products as $p) {
      foreach ($criteria as $c) {
        if (!isset($vals[$p['id']][$c['id']])) $vals[$p['id']][$c['id']] = 0;
      }
    }
    
    // normalization
    $max = []; $min = [];
    foreach ($criteria as $c) {
      $j = $c['id'];
      $mx = null; $mn = null;
      foreach ($products as $p) {
        $v = $vals[$p['id']][$j];
        if ($mx === null || $v > $mx) $mx = $v;
        if ($mn === null || $v < $mn) $mn = $v;
      }
      $max[$j] = $mx ?: 0.0;
      $min[$j] = $mn ?: 0.0;
    }
    
    $normalized = []; $scores = [];
    foreach ($products as $p) {
      $pid = $p['id'];
      $scores[$pid] = 0.0;
      foreach ($criteria as $c) {
        $j = $c['id'];
        $x = $vals[$pid][$j];
        if ($c['atribut'] === 'benefit') {
          $rij = ($max[$j] > 0) ? ($x / $max[$j]) : 0;
        } else {
          $rij = ($x > 0) ? ($min[$j] / $x) : 0;
        }
        $normalized[$pid][$j] = $rij;
        $scores[$pid] += $rij * floatval($c['bobot']);
      }
    }
    
    arsort($scores);
    $score_vals = array_values($scores);
    sort($score_vals);
    $c = count($score_vals);
    $median = ($c%2==1) ? $score_vals[floor($c/2)] : (($score_vals[$c/2-1]+$score_vals[$c/2])/2);
  ?>
  
  <h4>1. Kriteria Penilaian</h4>
  <table class="data">
    <thead><tr><th>Kode</th><th>Nama</th><th>Bobot</th><th>Atribut</th></tr></thead>
    <tbody>
      <?php foreach ($criteria as $cr): ?>
      <tr><td><?= htmlspecialchars($cr['kode']) ?></td><td><?= htmlspecialchars($cr['nama']) ?></td><td><?= $cr['bobot'] ?></td><td><?= $cr['atribut'] ?></td></tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  
  <h4>2. Ranking & Klasifikasi (Median: <?= number_format($median, 4) ?>)</h4>
  <table class="data">
    <thead><tr><th>Rank</th><th>Kode</th><th>Nama Produk</th><th>Skor</th><th>Klasifikasi</th></tr></thead>
    <tbody>
      <?php
      $rank = 1;
      foreach ($scores as $pid => $score):
        $p = array_values(array_filter($products, fn($i)=>$i['id']==$pid))[0];
        $class = ($score >= $median) ? 'Fast Moving' : 'Slow Moving';
        $bg = ($class === 'Fast Moving') ? '#c8e6c9' : '#ffccbc';
      ?>
      <tr style="background:<?= $bg ?>">
        <td><?= $rank++ ?></td>
        <td><?= htmlspecialchars($p['kode']) ?></td>
        <td><?= htmlspecialchars($p['nama']) ?></td>
        <td><?= number_format($score, 4) ?></td>
        <td><strong><?= $class ?></strong></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  
  <?php endif; ?>
<?php endif; ?>

<div class="footer">
  <p>Dicetak pada: <?= date('d/m/Y H:i:s') ?></p>
  <p style="margin-top:40px">_________________________</p>
  <p>( <?= htmlspecialchars($_SESSION['user']) ?> )</p>
</div>

<div class="no-print" style="text-align:center">
  <button onclick="window.print()" style="padding:10px 20px;background:#2d89ef;color:white;border:none;border-radius:8px;cursor:pointer;font-weight:600">🖨️ Cetak Laporan</button>
  <button onclick="window.close()" style="padding:10px 20px;background:#666;color:white;border:none;border-radius:8px;cursor:pointer;margin-left:8px">❌ Tutup</button>
</div>

</body>
</html>
