<?php
session_start();
include "koneksi.php";

/* ================= SECURITY ================= */
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['login'])) {
  header("Location: index.php");
  exit;
}

/* ================= KALENDER ================= */
$events = [];

$qKalender = mysqli_query($koneksi, "
  SELECT 
    DATE(tanggal) AS tgl,
    SUM(CASE WHEN jenis='setor' THEN jumlah ELSE 0 END) AS pemasukan,
    SUM(CASE WHEN jenis='tarik' THEN jumlah ELSE 0 END) AS pengeluaran
  FROM tabungan
  GROUP BY DATE(tanggal)
");

while ($r = mysqli_fetch_assoc($qKalender)) {
  if ($r['pemasukan'] > 0) {
    $events[] = [
      "title" => "Masuk: Rp " . number_format($r['pemasukan'],0,',','.'),
      "start" => $r['tgl'],
      "backgroundColor" => "#198754",
      "borderColor" => "#198754"
    ];
  }
  if ($r['pengeluaran'] > 0) {
    $events[] = [
      "title" => "Keluar: Rp " . number_format($r['pengeluaran'],0,',','.'),
      "start" => $r['tgl'],
      "backgroundColor" => "#dc3545",
      "borderColor" => "#dc3545"
    ];
  }
}

/* ================= REKAP PER SANTRI ================= */
$qRekap = mysqli_query($koneksi, "
  SELECT 
    s.nis,
    s.nama,
    COALESCE(SUM(CASE WHEN t.jenis='setor' THEN t.jumlah END),0) AS total_setor,
    COALESCE(SUM(CASE WHEN t.jenis='tarik' THEN t.jumlah END),0) AS total_tarik
  FROM santri s
  LEFT JOIN tabungan t ON s.nis = t.nis
  GROUP BY s.nis, s.nama
  ORDER BY s.nama ASC
");

/* ================= TOTAL SALDO ================= */
$qTotal = mysqli_query($koneksi, "
  SELECT 
    COALESCE(SUM(CASE WHEN jenis='setor' THEN jumlah END),0) -
    COALESCE(SUM(CASE WHEN jenis='tarik' THEN jumlah END),0) AS total_saldo
  FROM tabungan
");

$totalSaldo = mysqli_fetch_assoc($qTotal)['total_saldo'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Tabungan Santri</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>

<style>
body{
  background:#f4f8f6;
  font-family:Poppins, Arial, sans-serif;
}

/* ===== NAVBAR ===== */
.navbar{
  background:linear-gradient(90deg,#006d3d,#00a36c);
}

.nav-link{
  font-weight:500;
}

.nav-link.active{
  background:#ffffff33;
  border-radius:8px;
}

/* ===== CARD ===== */
.card{
  border:none;
  border-radius:16px;
  box-shadow:0 6px 18px rgba(0,0,0,.1);
}

/* ===== FORMAT RUPIAH ===== */
.rp-cell{
  display:flex;
  justify-content:space-between;
  align-items:center;
}

.rp{
  min-width:26px;
  text-align:left;
}

.nominal{
  text-align:right;
  flex:1;
}

/* ===== KONDISI SALDO ===== */
.saldo-minus{
  color:#dc3545;
  font-weight:700;
}

.saldo-warning{
  color:#ffc107;
  font-weight:700;
}
</style>
</head>

<body>

<!-- ================= NAVBAR + MENU DASHBOARD ================= -->
<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
  <div class="container">
    <a class="navbar-brand fw-bold" href="dashboard.php">
      CIT Tabungan Santri
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuDashboard">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="menuDashboard">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link active" href="dashboard.php">Dashboard</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="santri.php">Data Santri</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="transaksi.php">Transaksi</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="backup.php">Backup Databasae</a>
        </li>
         <li class="nav-item">
          <a class="nav-link" href="backup2.php">Backup File</a>
        </li>
      </ul>

      <a href="logout.php" class="btn btn-light btn-sm fw-semibold">
        Logout
      </a>
    </div>
  </div>
</nav>

<div class="container my-5">

<!-- ================= TOTAL SALDO ================= -->
<div class="row mb-4">
  <div class="col">
    <div class="card bg-success text-white text-center p-4">
      <h5>Total Saldo Keseluruhan</h5>
      <h2 class="fw-bold">
        Rp <?= number_format($totalSaldo,0,',','.') ?>
      </h2>
    </div>
  </div>
</div>

<!-- ================= KALENDER ================= -->
<div class="card p-4 mb-5">
  <div id="calendar"></div>
</div>

<!-- ================= REKAP SANTRI ================= -->
<div class="card p-4">
  <h4 class="fw-bold text-success text-center mb-3">
    Rekap Saldo Per Santri
  </h4>

  <div class="table-responsive">
    <table class="table table-bordered table-hover align-middle">
      <thead class="table-success text-center">
        <tr>
          <th>No</th>
          <th>NIS</th>
          <th>Nama Santri</th>
          <th>Total Setor</th>
          <th>Total Tarik</th>
          <th>Saldo Akhir</th>
        </tr>
      </thead>
      <tbody>
      <?php 
      $no=1;
      while($r=mysqli_fetch_assoc($qRekap)):
        $saldo = $r['total_setor'] - $r['total_tarik'];

        $classSaldo = '';
        if ($saldo < 0) {
          $classSaldo = 'saldo-minus';
        } elseif ($saldo <= 5000) {
          $classSaldo = 'saldo-warning';
        }
      ?>
        <tr>
          <td class="text-center"><?= $no++ ?></td>
          <td class="text-center"><?= htmlspecialchars($r['nis']) ?></td>
          <td><?= htmlspecialchars($r['nama']) ?></td>

          <td>
            <div class="rp-cell text-success">
              <span class="rp">Rp</span>
              <span class="nominal"><?= number_format($r['total_setor'],0,',','.') ?></span>
            </div>
          </td>

          <td>
            <div class="rp-cell text-danger">
              <span class="rp">Rp</span>
              <span class="nominal"><?= number_format($r['total_tarik'],0,',','.') ?></span>
            </div>
          </td>

          <td>
            <div class="rp-cell <?= $classSaldo ?>">
              <span class="rp">Rp</span>
              <span class="nominal"><?= number_format($saldo,0,',','.') ?></span>
            </div>
          </td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

</div>

<!-- ================= SCRIPT ================= -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  new FullCalendar.Calendar(document.getElementById('calendar'), {
    initialView: 'dayGridMonth',
    locale: 'id',
    height: 'auto',
    events: <?= json_encode($events) ?>
  }).render();
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
