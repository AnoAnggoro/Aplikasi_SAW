<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__.'/db.php';

$act = $_REQUEST['action'] ?? '';

if ($act === 'login') {
    $u = $_POST['username'] ?? '';
    $p = $_POST['password'] ?? '';
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username=? LIMIT 1");
    $stmt->execute([$u]);
    $user = $stmt->fetch();
    $storedPassword = $user['password'] ?? '';
    $isHashedPassword = password_get_info($storedPassword)['algo'] !== 0;
    $isValidPassword = $user && (
        ($isHashedPassword && password_verify($p, $storedPassword)) ||
        (!$isHashedPassword && hash_equals($storedPassword, $p))
    );

    if ($isValidPassword) {
        if (!$isHashedPassword) {
            $newHash = password_hash($p, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE users SET password=? WHERE id=?")->execute([$newHash, $user['id']]);
        }
        $_SESSION['user'] = $user['username'];
        echo json_encode(['ok'=>true]);
    } else echo json_encode(['ok'=>false,'msg'=>'Invalid']);
    exit;
}

if ($act === 'register') {
    $u = $_POST['username'] ?? '';
    $p = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    
    if (empty($u) || empty($p)) {
        echo json_encode(['ok'=>false,'msg'=>'Username dan password harus diisi']);
        exit;
    }
    if ($p !== $confirm) {
        echo json_encode(['ok'=>false,'msg'=>'Password tidak cocok']);
        exit;
    }
    
    // check if username exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username=? LIMIT 1");
    $stmt->execute([$u]);
    if ($stmt->fetch()) {
        echo json_encode(['ok'=>false,'msg'=>'Username sudah terdaftar']);
        exit;
    }
    
    $hashedPassword = password_hash($p, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $stmt->execute([$u, $hashedPassword]);
    echo json_encode(['ok'=>true,'msg'=>'Registrasi berhasil, silakan login']);
    exit;
}

if ($act === 'logout') {
    session_destroy();
    echo json_encode(['ok'=>true]); exit;
}

// protect routes
if (empty($_SESSION['user'])) {
    echo json_encode(['ok'=>false,'msg'=>'Not authenticated']); exit;
}

// Products
if ($act === 'add_product') {
    $kode = $_POST['kode'] ?? '';
    $nama = $_POST['nama'] ?? '';
    $stok = intval($_POST['stok'] ?? 0);
    $kategori = $_POST['kategori'] ?? '';
    $biaya = floatval($_POST['biaya'] ?? 0);
    $harga = floatval($_POST['harga'] ?? 0);
    $stmt = $pdo->prepare("INSERT INTO products (kode,nama,stok,kategori,biaya,harga) VALUES (?,?,?,?,?,?)");
    $stmt->execute([$kode,$nama,$stok,$kategori,$biaya,$harga]);
    echo json_encode(['ok'=>true]); exit;
}
if ($act === 'list_products') {
    $data = $pdo->query("SELECT * FROM products ORDER BY id DESC")->fetchAll();
    echo json_encode($data); exit;
}
if ($act === 'list_products_with_sales') {
    $stmt = $pdo->query("
        SELECT p.*, 
               COALESCE(SUM(s.qty), 0) AS terjual
        FROM products p
        LEFT JOIN sales s ON s.product_id = p.id
        GROUP BY p.id
        ORDER BY p.id DESC
    ");
    echo json_encode($stmt->fetchAll()); exit;
}

// Sales
if ($act === 'add_sale') {
    $pid = intval($_POST['product_id']);
    $qty = intval($_POST['qty']);
    $return_qty = intval($_POST['return_qty'] ?? 0);
    $date = $_POST['sales_date'] ?: date('Y-m-d');
    $id_penjualan = $_POST['id_penjualan'] ?? '';
    $kode_produk = $_POST['kode_produk'] ?? '';
    $nama_produk = $_POST['nama_produk'] ?? '';
    $harga = floatval($_POST['harga'] ?? 0);
    $total_harga = floatval($_POST['total_harga'] ?? 0);
    
    // check stok terlebih dahulu
    $stmt = $pdo->prepare("SELECT stok FROM products WHERE id=? LIMIT 1");
    $stmt->execute([$pid]);
    $product = $stmt->fetch();
    
    if (!$product) {
        echo json_encode(['ok'=>false,'msg'=>'Produk tidak ditemukan']); exit;
    }
    
    $stok_current = intval($product['stok']);
    if ($stok_current < $qty) {
        echo json_encode(['ok'=>false,'msg'=>'Stok tidak cukup. Stok tersedia: ' . $stok_current]); exit;
    }
    
    // insert sale
    $stmt = $pdo->prepare("INSERT INTO sales (product_id,qty,sales_date,id_penjualan,kode_produk,nama_produk,harga,return_qty,total_harga) VALUES (?,?,?,?,?,?,?,?,?)");
    $stmt->execute([$pid,$qty,$date,$id_penjualan,$kode_produk,$nama_produk,$harga,$return_qty,$total_harga]);
    
    // update stok: kurangi qty terjual, tambah return
    $new_stok = $stok_current - $qty + $return_qty;
    $stmt = $pdo->prepare("UPDATE products SET stok=? WHERE id=?");
    $stmt->execute([$new_stok, $pid]);
    
    echo json_encode(['ok'=>true,'msg'=>'Penjualan berhasil. Stok produk diperbarui.']); exit;
}
if ($act === 'list_sales') {
    $stmt = $pdo->query("SELECT s.*, p.nama FROM sales s LEFT JOIN products p ON p.id=s.product_id ORDER BY s.id DESC");
    echo json_encode($stmt->fetchAll()); exit;
}

// Criteria
if ($act === 'add_criteria') {
    $kode = $_POST['kode'] ?? '';
    $nama = $_POST['nama'] ?? '';
    $bobot = floatval($_POST['bobot'] ?? 1);
    $atribut = ($_POST['atribut']=='cost') ? 'cost' : 'benefit';
    $stmt = $pdo->prepare("INSERT INTO criteria (kode,nama,bobot,atribut) VALUES (?,?,?,?)");
    $stmt->execute([$kode,$nama,$bobot,$atribut]);
    echo json_encode(['ok'=>true]); exit;
}
if ($act === 'list_criteria') {
    echo json_encode($pdo->query("SELECT * FROM criteria ORDER BY id")->fetchAll()); exit;
}

// Product values (nilai tiap kriteria per produk)
if ($act === 'add_value') {
    $pid = intval($_POST['product_id']);
    $cid = intval($_POST['criteria_id']);
    $nilai = floatval($_POST['nilai']);
    // upsert
    $stmt = $pdo->prepare("SELECT id FROM product_values WHERE product_id=? AND criteria_id=?");
    $stmt->execute([$pid,$cid]);
    if ($stmt->fetch()) {
        $pdo->prepare("UPDATE product_values SET nilai=? WHERE product_id=? AND criteria_id=?")->execute([$nilai,$pid,$cid]);
    } else {
        $pdo->prepare("INSERT INTO product_values (product_id,criteria_id,nilai) VALUES (?,?,?)")->execute([$pid,$cid,$nilai]);
    }
    echo json_encode(['ok'=>true]); exit;
}
if ($act === 'list_values') {
    $data = $pdo->query("SELECT pv.*, p.nama AS product, c.nama AS criteria FROM product_values pv LEFT JOIN products p ON p.id=pv.product_id LEFT JOIN criteria c ON c.id=pv.criteria_id ORDER BY pv.id DESC")->fetchAll();
    echo json_encode($data); exit;
}

// Run SAW
if ($act === 'run_saw') {
    // load criteria
    $criteria = $pdo->query("SELECT * FROM criteria ORDER BY id")->fetchAll();
    if (!$criteria) { echo json_encode(['ok'=>false,'msg'=>'No criteria']); exit; }

    // load products
    $products = $pdo->query("SELECT * FROM products ORDER BY id")->fetchAll();
    if (!$products) { echo json_encode(['ok'=>false,'msg'=>'No products']); exit; }

    // build matrix values[product_id][criteria_id]
    $vals = [];
    foreach ($products as $p) $vals[$p['id']] = [];
    $stmt = $pdo->query("SELECT * FROM product_values");
    $all_vals = $stmt->fetchAll();
    foreach ($all_vals as $v) $vals[$v['product_id']][$v['criteria_id']] = floatval($v['nilai']);

    // ensure all values exist (fill 0)
    foreach ($products as $p) {
        foreach ($criteria as $c) {
            if (!isset($vals[$p['id']][$c['id']])) $vals[$p['id']][$c['id']] = 0.0;
        }
    }

    // normalization
    // for benefit: rij = xij / max_j ; cost: min_j / xij (avoid div0)
    $max = []; $min = [];
    foreach ($criteria as $c) {
        $j = $c['id'];
        $col = array_column(array_map(function($k,$v){ return ['k'=>$k,'v'=>$v]; }, array_keys($vals), array_values($vals)), 'v'); // not used
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

    // ranking
    arsort($scores);
    $ranked = [];
    foreach ($scores as $pid => $score) {
        $p = array_values(array_filter($products, fn($i)=>$i['id']==$pid))[0];
        $ranked[] = ['product_id'=>$pid,'kode'=>$p['kode'],'nama'=>$p['nama'],'score'=>round($score,6)];
    }

    // classify fast/slow by median score
    $score_vals = array_values($scores);
    $median = 0;
    if (count($score_vals)>0) {
        sort($score_vals);
        $c = count($score_vals);
        $median = ($c%2==1) ? $score_vals[floor($c/2)] : (($score_vals[$c/2-1]+$score_vals[$c/2])/2);
    }
    $classification = [];
    foreach ($ranked as $r) {
        $classification[] = array_merge($r, ['class'=> ($r['score'] >= $median ? 'Fast Moving' : 'Slow Moving')]);
    }

    // build calculation table for response
    $calc_table = [];
    foreach ($products as $p) {
        $row = ['product_id'=>$p['id'],'kode'=>$p['kode'],'nama'=>$p['nama']];
        foreach ($criteria as $c) {
            $row['raw_'.$c['id']] = $vals[$p['id']][$c['id']];
            $row['norm_'.$c['id']] = round($normalized[$p['id']][$c['id']],6);
        }
        $row['score'] = round($scores[$p['id']],6);
        $calc_table[] = $row;
    }

    echo json_encode(['ok'=>true,'criteria'=>$criteria,'calc'=>$calc_table,'rank'=>$classification,'median'=>$median]);
    exit;
}

// CSV report (enhanced)
if ($act === 'report_csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="laporan_saw_' . date('Ymd_His') . '.csv"');
    $out = fopen('php://output','w');
    
    // BOM untuk Excel UTF-8
    fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // SECTION 1: Data Produk
    fputcsv($out, ['=== DATA PRODUK ===']);
    fputcsv($out, ['Kode','Nama','Kategori','Stok','Biaya','Harga','Terjual']);
    $stmt = $pdo->query("SELECT p.*, COALESCE(SUM(s.qty), 0) AS terjual FROM products p LEFT JOIN sales s ON s.product_id = p.id GROUP BY p.id ORDER BY p.id");
    foreach ($stmt->fetchAll() as $r) {
        fputcsv($out, [$r['kode'],$r['nama'],$r['kategori'],$r['stok'],$r['biaya'],$r['harga'],$r['terjual']]);
    }
    fputcsv($out, []);
    
    // SECTION 2: Data Penjualan
    fputcsv($out, ['=== DATA PENJUALAN ===']);
    fputcsv($out, ['ID Penjualan','Kode Produk','Nama Produk','Qty','Harga','Total','Return','Tanggal']);
    $stmt = $pdo->query("SELECT * FROM sales ORDER BY sales_date DESC, id DESC");
    foreach ($stmt->fetchAll() as $s) {
        $total = floatval($s['qty']) * floatval($s['harga']);
        fputcsv($out, [$s['id_penjualan'],$s['kode_produk'],$s['nama_produk'],$s['qty'],$s['harga'],$total,$s['return_qty'],$s['sales_date']]);
    }
    fputcsv($out, []);
    
    // SECTION 3: Kriteria
    fputcsv($out, ['=== KRITERIA ===']);
    fputcsv($out, ['Kode','Nama','Bobot','Atribut']);
    $criteria = $pdo->query("SELECT * FROM criteria ORDER BY id")->fetchAll();
    foreach ($criteria as $c) {
        fputcsv($out, [$c['kode'],$c['nama'],$c['bobot'],$c['atribut']]);
    }
    fputcsv($out, []);
    
    // SECTION 4: Nilai Produk per Kriteria
    fputcsv($out, ['=== NILAI PRODUK PER KRITERIA ===']);
    $header = ['Produk'];
    foreach ($criteria as $c) $header[] = $c['kode'];
    fputcsv($out, $header);
    
    $products = $pdo->query("SELECT * FROM products ORDER BY id")->fetchAll();
    $vals = [];
    $stmt = $pdo->query("SELECT * FROM product_values");
    foreach ($stmt->fetchAll() as $v) $vals[$v['product_id']][$v['criteria_id']] = $v['nilai'];
    
    foreach ($products as $p) {
        $row = [$p['nama']];
        foreach ($criteria as $c) {
            $row[] = isset($vals[$p['id']][$c['id']]) ? $vals[$p['id']][$c['id']] : 0;
        }
        fputcsv($out, $row);
    }
    fputcsv($out, []);
    
    // SECTION 5: Hasil SAW (Normalisasi & Skor)
    fputcsv($out, ['=== HASIL PERHITUNGAN SAW ===']);
    
    // calculate SAW
    if (count($criteria) > 0 && count($products) > 0) {
        // build matrix
        $matrix = [];
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
        
        // header normalisasi
        $headerNorm = ['Produk'];
        foreach ($criteria as $c) {
            $headerNorm[] = $c['kode'] . ' (raw)';
            $headerNorm[] = $c['kode'] . ' (norm)';
            $headerNorm[] = $c['kode'] . ' (w×norm)';
        }
        $headerNorm[] = 'Skor';
        fputcsv($out, $headerNorm);
        
        foreach ($products as $p) {
            $row = [$p['nama']];
            foreach ($criteria as $c) {
                $j = $c['id'];
                $raw = $vals[$p['id']][$j];
                $norm = $normalized[$p['id']][$j];
                $weighted = $norm * floatval($c['bobot']);
                $row[] = number_format($raw, 3, '.', '');
                $row[] = number_format($norm, 3, '.', '');
                $row[] = number_format($weighted, 3, '.', '');
            }
            $row[] = number_format($scores[$p['id']], 4, '.', '');
            fputcsv($out, $row);
        }
        fputcsv($out, []);
        
        // SECTION 6: Ranking
        arsort($scores);
        $score_vals = array_values($scores);
        $median = 0;
        if (count($score_vals)>0) {
            sort($score_vals);
            $c = count($score_vals);
            $median = ($c%2==1) ? $score_vals[floor($c/2)] : (($score_vals[$c/2-1]+$score_vals[$c/2])/2);
        }
        
        fputcsv($out, ['=== RANKING & KLASIFIKASI ===']);
        fputcsv($out, ['Rank','Kode','Nama','Skor','Klasifikasi']);
        $rank = 1;
        foreach ($scores as $pid => $score) {
            $p = array_values(array_filter($products, fn($i)=>$i['id']==$pid))[0];
            $class = ($score >= $median) ? 'Fast Moving' : 'Slow Moving';
            fputcsv($out, [$rank, $p['kode'], $p['nama'], number_format($score, 4, '.', ''), $class]);
            $rank++;
        }
    }
    
    fclose($out);
    exit;
}

// CSV report - Products
if ($act === 'report_products') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="laporan_produk_' . date('Ymd_His') . '.csv"');
    $out = fopen('php://output','w');
    fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
    
    fputcsv($out, ['LAPORAN DATA PRODUK']);
    fputcsv($out, ['Tanggal Unduh: ' . date('Y-m-d H:i:s')]);
    fputcsv($out, []);
    fputcsv($out, ['Kode','Nama','Kategori','Stok','Biaya (Rp)','Harga (Rp)','Terjual (Unit)']);
    
    $stmt = $pdo->query("SELECT p.*, COALESCE(SUM(s.qty), 0) AS terjual FROM products p LEFT JOIN sales s ON s.product_id = p.id GROUP BY p.id ORDER BY p.kode");
    foreach ($stmt->fetchAll() as $r) {
        fputcsv($out, [$r['kode'],$r['nama'],$r['kategori']?:'-',$r['stok'],$r['biaya'],$r['harga'],$r['terjual']]);
    }
    fclose($out);
    exit;
}

// CSV report - Sales
if ($act === 'report_sales') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="laporan_penjualan_' . date('Ymd_His') . '.csv"');
    $out = fopen('php://output','w');
    fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
    
    fputcsv($out, ['LAPORAN DATA PENJUALAN']);
    fputcsv($out, ['Tanggal Unduh: ' . date('Y-m-d H:i:s')]);
    fputcsv($out, []);
    fputcsv($out, ['ID Penjualan','Kode Produk','Nama Produk','Qty','Harga (Rp)','Total (Rp)','Return','Tanggal']);
    
    $stmt = $pdo->query("SELECT * FROM sales ORDER BY sales_date DESC, id DESC");
    foreach ($stmt->fetchAll() as $s) {
        $total = floatval($s['qty']) * floatval($s['harga']);
        fputcsv($out, [$s['id_penjualan']?:'-',$s['kode_produk']?:'-',$s['nama_produk']?:'-',$s['qty'],$s['harga'],$total,$s['return_qty']?:0,$s['sales_date']]);
    }
    
    // summary
    fputcsv($out, []);
    $stmt = $pdo->query("SELECT COUNT(*) as total_trx, SUM(qty) as total_qty, SUM(qty*harga) as total_revenue FROM sales");
    $summary = $stmt->fetch();
    fputcsv($out, ['RINGKASAN']);
    fputcsv($out, ['Total Transaksi', $summary['total_trx']]);
    fputcsv($out, ['Total Qty Terjual', $summary['total_qty']]);
    fputcsv($out, ['Total Pendapatan (Rp)', number_format($summary['total_revenue'], 2, '.', '')]);
    
    fclose($out);
    exit;
}

// CSV report - Analysis
if ($act === 'report_analysis') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="laporan_analisis_saw_' . date('Ymd_His') . '.csv"');
    $out = fopen('php://output','w');
    fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
    
    fputcsv($out, ['LAPORAN ANALISIS SAW - FAST/SLOW MOVING']);
    fputcsv($out, ['Tanggal Unduh: ' . date('Y-m-d H:i:s')]);
    fputcsv($out, []);
    
    // load data
    $criteria = $pdo->query("SELECT * FROM criteria ORDER BY id")->fetchAll();
    $products = $pdo->query("SELECT * FROM products ORDER BY id")->fetchAll();
    
    if (count($criteria) === 0 || count($products) === 0) {
        fputcsv($out, ['Data kriteria atau produk tidak tersedia']);
        fclose($out);
        exit;
    }
    
    // Section 1: Kriteria
    fputcsv($out, ['1. KRITERIA PENILAIAN']);
    fputcsv($out, ['Kode','Nama','Bobot','Atribut']);
    foreach ($criteria as $c) {
        fputcsv($out, [$c['kode'],$c['nama'],$c['bobot'],$c['atribut']]);
    }
    fputcsv($out, []);
    
    // Section 2: Nilai Produk
    fputcsv($out, ['2. NILAI PRODUK PER KRITERIA (RAW)']);
    $header = ['Produk'];
    foreach ($criteria as $c) $header[] = $c['kode'];
    fputcsv($out, $header);
    
    $vals = [];
    $stmt = $pdo->query("SELECT * FROM product_values");
    foreach ($stmt->fetchAll() as $v) $vals[$v['product_id']][$v['criteria_id']] = $v['nilai'];
    
    foreach ($products as $p) {
        $row = [$p['nama']];
        foreach ($criteria as $c) {
            $row[] = isset($vals[$p['id']][$c['id']]) ? $vals[$p['id']][$c['id']] : 0;
        }
        fputcsv($out, $row);
    }
    fputcsv($out, []);
    
    // Calculate SAW
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
    
    // Section 3: Normalisasi
    fputcsv($out, ['3. MATRIKS NORMALISASI']);
    $header = ['Produk'];
    foreach ($criteria as $c) $header[] = $c['kode'];
    fputcsv($out, $header);
    
    foreach ($products as $p) {
        $row = [$p['nama']];
        foreach ($criteria as $c) {
            $row[] = number_format($normalized[$p['id']][$c['id']], 3, '.', '');
        }
        fputcsv($out, $row);
    }
    fputcsv($out, []);
    
    // Section 4: Weighted Score
    fputcsv($out, ['4. SKOR TERBOBOT (Normalisasi × Bobot)']);
    $header = ['Produk'];
    foreach ($criteria as $c) $header[] = $c['kode'];
    $header[] = 'Total Skor';
    fputcsv($out, $header);
    
    foreach ($products as $p) {
        $row = [$p['nama']];
        foreach ($criteria as $c) {
            $weighted = $normalized[$p['id']][$c['id']] * floatval($c['bobot']);
            $row[] = number_format($weighted, 3, '.', '');
        }
        $row[] = number_format($scores[$p['id']], 4, '.', '');
        fputcsv($out, $row);
    }
    fputcsv($out, []);
    
    // Section 5: Ranking
    arsort($scores);
    $score_vals = array_values($scores);
    $median = 0;
    if (count($score_vals)>0) {
        sort($score_vals);
        $c = count($score_vals);
        $median = ($c%2==1) ? $score_vals[floor($c/2)] : (($score_vals[$c/2-1]+$score_vals[$c/2])/2);
    }
    
    fputcsv($out, ['5. RANKING & KLASIFIKASI']);
    fputcsv($out, ['Median Skor: ' . number_format($median, 4, '.', '')]);
    fputcsv($out, []);
    fputcsv($out, ['Rank','Kode','Nama Produk','Skor','Klasifikasi']);
    $rank = 1;
    foreach ($scores as $pid => $score) {
        $p = array_values(array_filter($products, fn($i)=>$i['id']==$pid))[0];
        $class = ($score >= $median) ? 'Fast Moving' : 'Slow Moving';
        fputcsv($out, [$rank, $p['kode'], $p['nama'], number_format($score, 4, '.', ''), $class]);
        $rank++;
    }
    
    fclose($out);
    exit;
}

// UPDATE PRODUCT
if ($act === 'update_product') {
    if (empty($_SESSION['user'])) {
        echo json_encode(['ok' => false, 'msg' => 'Tidak terautentikasi']);
        exit;
    }
    
    $id = $_POST['id'] ?? '';
    $kode = $_POST['kode'] ?? '';
    $nama = $_POST['nama'] ?? '';
    $kategori = $_POST['kategori'] ?? '';
    $stok = $_POST['stok'] ?? 0;
    $biaya = $_POST['biaya'] ?? 0;
    $harga = $_POST['harga'] ?? 0;
    
    $stmt = $pdo->prepare("UPDATE products SET kode=?, nama=?, kategori=?, stok=?, biaya=?, harga=? WHERE id=?");
    $stmt->execute([$kode, $nama, $kategori, $stok, $biaya, $harga, $id]);
    
    echo json_encode(['ok' => true, 'msg' => 'Produk berhasil diupdate']);
    exit;
}

// DELETE PRODUCT
if ($act === 'delete_product') {
    if (empty($_SESSION['user'])) {
        echo json_encode(['ok' => false, 'msg' => 'Tidak terautentikasi']);
        exit;
    }
    
    $id = $_POST['id'] ?? '';
    
    // Hapus data terkait dulu
    $pdo->prepare("DELETE FROM product_values WHERE product_id=?")->execute([$id]);
    $pdo->prepare("DELETE FROM sales WHERE product_id=?")->execute([$id]);
    $pdo->prepare("DELETE FROM products WHERE id=?")->execute([$id]);
    
    echo json_encode(['ok' => true, 'msg' => 'Produk berhasil dihapus']);
    exit;
}

// UPDATE SALE
if ($act === 'update_sale') {
    if (empty($_SESSION['user'])) {
        echo json_encode(['ok' => false, 'msg' => 'Tidak terautentikasi']);
        exit;
    }
    
    $id = $_POST['id'] ?? '';
    $product_id = $_POST['product_id'] ?? '';
    $qty = $_POST['qty'] ?? 0;
    $harga = $_POST['harga'] ?? 0;
    $return_qty = $_POST['return_qty'] ?? 0;
    $sales_date = $_POST['sales_date'] ?? date('Y-m-d');
    $id_penjualan = $_POST['id_penjualan'] ?? '';
    $kode_produk = $_POST['kode_produk'] ?? '';
    $nama_produk = $_POST['nama_produk'] ?? '';
    $total_harga = $_POST['total_harga'] ?? 0;
    
    $stmt = $pdo->prepare("UPDATE sales SET product_id=?, qty=?, harga=?, return_qty=?, sales_date=?, id_penjualan=?, kode_produk=?, nama_produk=?, total_harga=? WHERE id=?");
    $stmt->execute([$product_id, $qty, $harga, $return_qty, $sales_date, $id_penjualan, $kode_produk, $nama_produk, $total_harga, $id]);
    
    echo json_encode(['ok' => true, 'msg' => 'Penjualan berhasil diupdate']);
    exit;
}

// DELETE SALE
if ($act === 'delete_sale') {
    if (empty($_SESSION['user'])) {
        echo json_encode(['ok' => false, 'msg' => 'Tidak terautentikasi']);
        exit;
    }
    
    $id = $_POST['id'] ?? '';
    $pdo->prepare("DELETE FROM sales WHERE id=?")->execute([$id]);
    
    echo json_encode(['ok' => true, 'msg' => 'Penjualan berhasil dihapus']);
    exit;
}

// UPDATE CRITERIA
if ($act === 'update_criteria') {
    if (empty($_SESSION['user'])) {
        echo json_encode(['ok' => false, 'msg' => 'Tidak terautentikasi']);
        exit;
    }
    
    $id = $_POST['id'] ?? '';
    $kode = $_POST['kode'] ?? '';
    $nama = $_POST['nama'] ?? '';
    $bobot = $_POST['bobot'] ?? 1;
    $atribut = $_POST['atribut'] ?? 'benefit';
    
    $stmt = $pdo->prepare("UPDATE criteria SET kode=?, nama=?, bobot=?, atribut=? WHERE id=?");
    $stmt->execute([$kode, $nama, $bobot, $atribut, $id]);
    
    echo json_encode(['ok' => true, 'msg' => 'Kriteria berhasil diupdate']);
    exit;
}

// DELETE CRITERIA
if ($act === 'delete_criteria') {
    if (empty($_SESSION['user'])) {
        echo json_encode(['ok' => false, 'msg' => 'Tidak terautentikasi']);
        exit;
    }
    
    $id = $_POST['id'] ?? '';
    
    // Hapus nilai produk terkait dulu
    $pdo->prepare("DELETE FROM product_values WHERE criteria_id=?")->execute([$id]);
    $pdo->prepare("DELETE FROM criteria WHERE id=?")->execute([$id]);
    
    echo json_encode(['ok' => true, 'msg' => 'Kriteria berhasil dihapus']);
    exit;
}

// UPDATE VALUE
if ($act === 'update_value') {
    if (empty($_SESSION['user'])) {
        echo json_encode(['ok' => false, 'msg' => 'Tidak terautentikasi']);
        exit;
    }
    
    $id = $_POST['id'] ?? '';
    $product_id = $_POST['product_id'] ?? '';
    $criteria_id = $_POST['criteria_id'] ?? '';
    $nilai = $_POST['nilai'] ?? 0;
    
    $stmt = $pdo->prepare("UPDATE product_values SET product_id=?, criteria_id=?, nilai=? WHERE id=?");
    $stmt->execute([$product_id, $criteria_id, $nilai, $id]);
    
    echo json_encode(['ok' => true, 'msg' => 'Nilai berhasil diupdate']);
    exit;
}

// DELETE VALUE
if ($act === 'delete_value') {
    if (empty($_SESSION['user'])) {
        echo json_encode(['ok' => false, 'msg' => 'Tidak terautentikasi']);
        exit;
    }
    
    $id = $_POST['id'] ?? '';
    $pdo->prepare("DELETE FROM product_values WHERE id=?")->execute([$id]);
    
    echo json_encode(['ok' => true, 'msg' => 'Nilai berhasil dihapus']);
    exit;
}

echo json_encode(['ok'=>false,'msg'=>'unknown action']);
