<?php
// DB connection - sesuaikan kredensial bila perlu
$host = '127.0.0.1';
$db   = 'saw_db';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'DB connection failed: '.$e->getMessage()]);
    exit;
}

// Normalisasi SAW. benefit: x/max ; cost: min/x, tapi x=0 pada cost = kondisi
// terbaik (mis. tanpa retur) sehingga rij = 1, bukan 0.
function saw_rij($x, $min, $max, $atribut) {
    if ($atribut === 'benefit') return ($max > 0) ? ($x / $max) : 0.0;
    return ($x == 0) ? 1.0 : ($min / $x);
}
