<?php
session_start();
if (empty($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

require_once __DIR__.'/db.php';
$type = $_GET['type'] ?? 'products';
$ownerName = $_SESSION['owner_name'] ?? 'Linda Indriana';
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
.footer .signature-block {
  display: inline-block;
  text-align: center;
  margin-top: 28px;
}
.footer .owner-name {
  margin: 0;
  font-weight: 700;
  font-size: 13px;
  line-height: 1.1;
  display: inline-block;
  padding-bottom: 2px;
  border-bottom: 2px solid #000;
}
.footer .owner-title {
  margin: 5px 0 0 0;
  font-size: 13px;
  color: #000;
}
.footer .signature-line {
  display: none;
}
.no-print { margin: 20px 0; }
</style>
</head>
<body>

<div class="kop-surat">
  <img src="img/logo.png" alt="Logo">
  <h2>HIJABEYLI FASHION</h2>
  <p>Kp. Parung Belimbing RT04/ RW04, Pancoran Mas, Depok, Kota Depok, 16431.  | Telp: 081319983240 | Email: hijabeylifashion@gmail.com</p>
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

<?php elseif ($type === 'profit'): ?>
  <h3>LAPORAN LABA / KEUNTUNGAN PENJUALAN</h3>
  <table class="data">
    <thead>
      <tr>
        <th>No</th>
        <th>ID Penjualan</th>
        <th>Tanggal</th>
        <th>Kode</th>
        <th>Nama Produk</th>
        <th>Qty Jual</th>
        <th>Return</th>
        <th>Net Qty</th>
        <th>Harga Jual (Rp)</th>
        <th>Modal (Rp)</th>
        <th>Total Pendapatan (Rp)</th>
        <th>Total Modal (Rp)</th>
        <th>Keuntungan (Rp)</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $stmt = $pdo->query("SELECT s.*, p.biaya FROM sales s LEFT JOIN products p ON s.product_id = p.id ORDER BY s.sales_date DESC, s.id DESC");
      $no = 1;
      $total_net_qty = 0;
      $total_revenue = 0;
      $total_cost = 0;
      $total_profit = 0;
      
      foreach ($stmt->fetchAll() as $s):
        $biaya = floatval($s['biaya'] ?? 0);
        $qty = intval($s['qty']);
        $return_qty = intval($s['return_qty'] ?? 0);
        $net_qty = $qty - $return_qty;
        $harga = floatval($s['harga']);
        
        $revenue = $net_qty * $harga;
        $cost = $net_qty * $biaya;
        $profit = $revenue - $cost;
        
        $total_net_qty += $net_qty;
        $total_revenue += $revenue;
        $total_cost += $cost;
        $total_profit += $profit;
      ?>
      <tr>
        <td><?= $no++ ?></td>
        <td><?= htmlspecialchars($s['id_penjualan'] ?: '-') ?></td>
        <td><?= htmlspecialchars($s['sales_date']) ?></td>
        <td><?= htmlspecialchars($s['kode_produk'] ?: '-') ?></td>
        <td><?= htmlspecialchars($s['nama_produk'] ?: '-') ?></td>
        <td><?= $qty ?></td>
        <td><?= $return_qty ?></td>
        <td><?= $net_qty ?></td>
        <td><?= number_format($harga, 0, ',', '.') ?></td>
        <td><?= number_format($biaya, 0, ',', '.') ?></td>
        <td><?= number_format($revenue, 0, ',', '.') ?></td>
        <td><?= number_format($cost, 0, ',', '.') ?></td>
        <td style="font-weight:600;color:<?= $profit >= 0 ? '#2e7d32' : '#c62828' ?>"><?= number_format($profit, 0, ',', '.') ?></td>
      </tr>
      <?php endforeach; ?>
      <tr style="font-weight:bold;background:#e3f2fd">
        <td colspan="5" style="text-align:right">TOTAL</td>
        <td colspan="2"></td>
        <td><?= $total_net_qty ?></td>
        <td colspan="2"></td>
        <td><?= number_format($total_revenue, 0, ',', '.') ?></td>
        <td><?= number_format($total_cost, 0, ',', '.') ?></td>
        <td style="color:<?= $total_profit >= 0 ? '#2e7d32' : '#c62828' ?>"><?= number_format($total_profit, 0, ',', '.') ?></td>
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
        $rij = saw_rij($x, $min[$j], $max[$j], $c['atribut']);
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
  <?php
  date_default_timezone_set('Asia/Jakarta');
  $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
  $months = [
      1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
      'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
  ];
  $dayName = $days[date('w')];
  $monthName = $months[(int)date('m')];
  ?>
  <p>Depok, <?= $dayName ?> <?= date('j') ?> <?= $monthName ?> <?= date('Y') ?></p>
  <div class="signature-block">
    <p class="owner-name"><?= htmlspecialchars($ownerName) ?></p>
    <p class="owner-title">Owner</p>
  </div>

</div>

<div class="no-print" style="text-align:center">
  <button onclick="handleCetak()" style="padding:10px 20px;background:#2d89ef;color:white;border:none;border-radius:8px;cursor:pointer;font-weight:600">🖨️ Cetak Laporan</button>
  <button onclick="window.close()" style="padding:10px 20px;background:#666;color:white;border:none;border-radius:8px;cursor:pointer;margin-left:8px">❌ Tutup</button>
</div>

<!-- JS PDF and HTML2Canvas Libraries -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<script>
function isMobileOrWebView() {
  const ua = navigator.userAgent || navigator.vendor || window.opera;
  const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(ua);
  const isWebView = (ua.includes('wv') || ua.includes('WebView') || ua.includes('Median') || !window.chrome) && isMobile;
  return isMobile || isWebView;
}

function handleCetak() {
  if (isMobileOrWebView()) {
    exportPDF();
  } else {
    window.print();
  }
}

async function exportPDF() {
  const btnArea = document.querySelector('.no-print');
  if (btnArea) btnArea.style.display = 'none';

  // Add a simple loading overlay
  const loading = document.createElement('div');
  loading.setAttribute('id', 'pdf-loading');
  loading.setAttribute('data-html2canvas-ignore', 'true');
  loading.innerHTML = '<div style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(255,255,255,0.85);display:flex;flex-direction:column;justify-content:center;align-items:center;z-index:9999;font-weight:bold;font-family:Arial,sans-serif;color:#2d89ef;font-size:16px;"><span>⏳ Sedang memproses PDF...</span><span style="font-size:12px;color:#666;margin-top:8px;">Laporan Anda sedang disimpan ke galeri/unduhan</span></div>';
  document.body.appendChild(loading);

  try {
    const { jsPDF } = window.jspdf;
    
    // capture body using html2canvas
    const canvas = await html2canvas(document.body, {
      scale: 2,
      useCORS: true,
      logging: false,
      scrollY: -window.scrollY
    });

    const imgData = canvas.toDataURL('image/jpeg', 0.95);
    const pdf = new jsPDF('p', 'mm', 'a4');
    
    const imgWidth = 210; // A4 size width in mm
    const pageHeight = 297; // A4 size height in mm
    const imgHeight = (canvas.height * imgWidth) / canvas.width;
    
    let heightLeft = imgHeight;
    let position = 0;

    pdf.addImage(imgData, 'JPEG', 0, position, imgWidth, imgHeight);
    heightLeft -= pageHeight;

    while (heightLeft > 0) {
      position = heightLeft - imgHeight;
      pdf.addPage();
      pdf.addImage(imgData, 'JPEG', 0, position, imgWidth, imgHeight);
      heightLeft -= pageHeight;
    }

    const urlParams = new URLSearchParams(window.location.search);
    const type = urlParams.get('type') || 'laporan';
    pdf.save(`laporan_${type}_${new Date().toISOString().slice(0,10)}.pdf`);
    
  } catch (err) {
    console.error('PDF Error:', err);
    alert('Gagal mencetak laporan ke PDF: ' + err.message);
  } finally {
    const loadEl = document.getElementById('pdf-loading');
    if (loadEl) loadEl.remove();
    if (btnArea) btnArea.style.display = 'block';
  }
}
</script>
</body>
</html>
