<?php
session_start();
include "koneksi.php";
if (!isset($_SESSION['login'])) header("Location: index.php");

// --- Inisialisasi ---
date_default_timezone_set('Asia/Jakarta');
$date = date('Ymd_His');
$backupDir = "backup";
if (!file_exists($backupDir)) mkdir($backupDir, 0777, true);

$dbName = "db_tabungan"; // ganti sesuai nama database Anda
$backupFile = "$backupDir/db_backup_{$date}.sql";

// --- 1. Ambil semua tabel ---
$tables = [];
$result = mysqli_query($koneksi, "SHOW TABLES");
while ($row = mysqli_fetch_row($result)) {
    $tables[] = $row[0];
}

// --- 2. Buat skrip SQL ---
$sqlScript = "";
foreach ($tables as $table) {
    // Struktur tabel
    $query = mysqli_query($koneksi, "SHOW CREATE TABLE `$table`");
    $row2 = mysqli_fetch_row($query);
    $sqlScript .= "\n\n-- Struktur tabel `$table`\n";
    $sqlScript .= "DROP TABLE IF EXISTS `$table`;\n";
    $sqlScript .= $row2[1] . ";\n\n";

    // Data tabel
    $result_data = mysqli_query($koneksi, "SELECT * FROM `$table`");
    $columnCount = mysqli_num_fields($result_data);

    $sqlScript .= "-- Data untuk tabel `$table`\n";
    while ($row3 = mysqli_fetch_row($result_data)) {
        $sqlScript .= "INSERT INTO `$table` VALUES(";
        for ($j = 0; $j < $columnCount; $j++) {
            $value = isset($row3[$j]) ? addslashes($row3[$j]) : '';
            $value = str_replace("\n", "\\n", $value);
            $sqlScript .= "'$value'";
            if ($j < ($columnCount - 1)) $sqlScript .= ",";
        }
        $sqlScript .= ");\n";
    }
    $sqlScript .= "\n";
}

// --- 3. Simpan ke file SQL ---
file_put_contents($backupFile, $sqlScript);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Backup Database - CIT Tabungan Santri</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: #f4fff8;
      font-family: 'Poppins', sans-serif;
    }
    .container {
      max-width: 700px;
      margin-top: 80px;
    }
    .card {
      border-radius: 15px;
      box-shadow: 0 3px 8px rgba(0,0,0,0.1);
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="card p-5 text-center">
      <h3 class="text-success fw-bold mb-4">✅ Backup Database Berhasil!</h3>
      <p class="text-muted mb-4">
        File backup database Anda telah disimpan di folder <code>/backup</code>.
      </p>
      <a href="<?= htmlspecialchars($backupFile) ?>" class="btn btn-success px-4" download>💾 Download Backup (SQL)</a>
      <a href="dashboard.php" class="btn btn-outline-secondary px-4 ms-2">⬅️ Kembali ke Dashboard</a>
    </div>
  </div>
</body>
</html>
