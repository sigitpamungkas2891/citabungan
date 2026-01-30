<?php
session_start();
include "koneksi.php";
if (!isset($_SESSION['login'])) header("Location: index.php");

// Simpan transaksi
if (isset($_POST['simpan'])) {
  $nis = $_POST['nis'];
  $jenis = $_POST['jenis'];
  $jumlah = $_POST['jumlah'];
  $ket = $_POST['keterangan'];
  $tgl = $_POST['tanggal']; // ambil tanggal dari input

  mysqli_query($koneksi, "INSERT INTO tabungan (nis, tanggal, jenis, jumlah, keterangan)
                          VALUES ('$nis','$tgl','$jenis','$jumlah','$ket')");
  header("Location: transaksi.php?success=add");
  exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Transaksi Tabungan | CIT Tabungan Santri</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Bootstrap, Icons & SweetAlert -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
    body {
      background: linear-gradient(135deg, #e8f5e9, #f1f8f5);
      font-family: 'Poppins', sans-serif;
    }
    .navbar {
      background: linear-gradient(90deg, #006d3d, #00a36c);
      box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    }
    .navbar .nav-link {
      color: #fff !important;
      font-weight: 500;
      transition: all 0.3s;
    }
    .navbar .nav-link:hover {
      background: rgba(255,255,255,0.15);
      border-radius: 10px;
    }
    .btn-logout {
      background: #fff;
      color: #006d3d;
      font-weight: bold;
      border-radius: 50px;
      transition: all 0.3s;
    }
    .btn-logout:hover { background: #ff4d4f; color: #fff; }
    .card {
      border: none;
      border-radius: 15px;
      box-shadow: 0 3px 8px rgba(0,0,0,0.1);
      transition: 0.3s;
    }
    .table thead {
      background: linear-gradient(90deg, #198754, #43c27e);
      color: white;
    }
    .fade-in { animation: fadeIn 0.6s ease-in-out; }
    @keyframes fadeIn { from {opacity: 0; transform: translateY(10px);} to {opacity: 1; transform: translateY(0);} }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center fw-bold" href="dashboard.php">
      <img src="https://cdn-icons-png.flaticon.com/512/3135/3135755.png" width="35" class="me-2">
      <span>CIT Tabungan Santri</span>
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCIT">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse justify-content-end" id="navbarCIT">
      <ul class="navbar-nav align-items-lg-center">
        <li class="nav-item mx-2"><a class="nav-link" href="dashboard.php"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a></li>
        <li class="nav-item mx-2"><a class="nav-link" href="data_santri.php"><i class="bi bi-people-fill me-1"></i>Data Santri</a></li>
        <li class="nav-item mx-2"><a class="nav-link active" href="transaksi.php"><i class="bi bi-wallet2 me-1"></i>Transaksi</a></li>
        <li class="nav-item mx-2"><a class="nav-link" href="saldo.php"><i class="bi bi-cash-stack me-1"></i>Saldo</a></li>
        <li class="nav-item ms-lg-3 mt-2 mt-lg-0">
          <a href="logout.php" class="btn btn-logout px-3"><i class="bi bi-box-arrow-right me-1"></i>Logout</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<!-- Konten -->
<div class="container mt-5 fade-in">
  <div class="card p-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
      <h3 class="text-success fw-bold"><i class="bi bi-wallet2 me-2"></i>Transaksi Tabungan</h3>
    </div>

    <!-- Form Transaksi -->
    <form method="POST" class="row g-2 mb-4">
      <div class="col-md-2">
        <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>" required>
      </div>
      <div class="col-md-3">
        <select name="nis" class="form-select" required>
          <option value="">Pilih Santri</option>
          <?php
          $santri = mysqli_query($koneksi, "SELECT * FROM santri ORDER BY nis ASC");
          while ($s = mysqli_fetch_array($santri)) {
            echo "<option value='$s[nis]'>$s[nis] - $s[nama]</option>";
          }
          ?>
        </select>
      </div>
      <div class="col-md-2">
        <select name="jenis" class="form-select">
          <option value="setor">Setor</option>
          <option value="tarik">Tarik</option>
        </select>
      </div>
      <div class="col-md-2">
        <input name="jumlah" type="number" step="0.01" class="form-control" placeholder="Jumlah" required>
      </div>
      <div class="col-md-2">
        <input name="keterangan" class="form-control" placeholder="Keterangan">
      </div>
      <div class="col-md-1">
        <button class="btn btn-success w-100" name="simpan"><i class="bi bi-check-circle me-1"></i>OK</button>
      </div>
    </form>

    <!-- Pencarian -->
    <div class="input-group mb-3">
      <span class="input-group-text bg-success text-white"><i class="bi bi-search"></i></span>
      <input type="text" id="searchInput" class="form-control" placeholder="Cari transaksi berdasarkan NIS, jenis, atau keterangan...">
    </div>

    <!-- Tabel Transaksi -->
    <div class="table-responsive">
      <table class="table table-bordered text-center align-middle">
        <thead>
          <tr>
            <th>Tanggal</th>
            <th>NIS</th>
            <th>Jenis</th>
            <th>Jumlah</th>
            <th>Keterangan</th>
          </tr>
        </thead>
        <tbody id="transaksiData">
        <?php
        $q = mysqli_query($koneksi, "SELECT * FROM tabungan ORDER BY tanggal DESC LIMIT 50");
        while ($t = mysqli_fetch_array($q)) {
            $jenisColor = ($t['jenis'] == 'setor') ? 'text-success fw-bold' : 'text-danger fw-bold';
            $tanggal = date('d/m/Y', strtotime($t['tanggal']));
            $jumlah = number_format($t['jumlah'], 0, ',', '.');
            $jenis = ucfirst($t['jenis']);
            echo "
            <tr>
                <td>$tanggal</td>
                <td>$t[nis]</td>
                <td class='$jenisColor'>$jenis</td>
                <td class='text-start'>
                    <div class='d-flex justify-content-between'>
                        <span>Rp.</span>
                        <span class='fw-semibold text-end'>$jumlah</span>
                    </div>
                </td>
                <td class='text-start'>$t[keterangan]</td>
            </tr>
            ";
        }
        ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Script -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Notifikasi sukses
<?php if (isset($_GET['success']) && $_GET['success'] == 'add') { ?>
Swal.fire({
  icon: 'success',
  title: 'Transaksi berhasil disimpan!',
  showConfirmButton: false,
  timer: 1500
});
<?php } ?>

// Pencarian real-time
$('#searchInput').on('keyup', function(){
  var keyword = $(this).val().toLowerCase();
  $("#transaksiData tr").filter(function() {
    $(this).toggle($(this).text().toLowerCase().indexOf(keyword) > -1);
  });
});
</script>
</body>
</html>
