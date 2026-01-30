<?php
session_start();
include "koneksi.php";
if (!isset($_SESSION['login'])) header("Location: index.php");

// Tambah santri
if (isset($_POST['tambah'])) {
    $nis = $_POST['nis'];
    $nama = $_POST['nama'];
    $kelas = $_POST['kelas'];
    mysqli_query($koneksi, "INSERT INTO santri (nis, nama, kelas) VALUES ('$nis', '$nama', '$kelas')");
    header("Location: data_santri.php?success=add");
    exit;
}

// Update santri (AJAX)
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $kolom = $_POST['kolom'];
    $nilai = $_POST['nilai'];
    mysqli_query($koneksi, "UPDATE santri SET $kolom='$nilai' WHERE id='$id'");
    exit;
}

// Hapus santri
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($koneksi, "DELETE FROM santri WHERE id='$id'");
    header("Location: data_santri.php?success=delete");
    exit;
}

// Pencarian
$where = "";
if (!empty($_GET['cari'])) {
    $keyword = mysqli_real_escape_string($koneksi, $_GET['cari']);
    $where = "WHERE nis LIKE '%$keyword%' OR nama LIKE '%$keyword%' OR kelas LIKE '%$keyword%'";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Data Santri | CIT Tabungan Santri</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Bootstrap & Icons -->
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
    td[contenteditable="true"] {
      background: #fff7e6;
      border-radius: 5px;
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
        <li class="nav-item mx-2"><a class="nav-link active" href="santri.php"><i class="bi bi-people-fill me-1"></i>Data Santri</a></li>
        <li class="nav-item mx-2"><a class="nav-link" href="transaksi.php"><i class="bi bi-wallet2 me-1"></i>Transaksi</a></li>
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
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
      <h3 class="text-success fw-bold mb-2"><i class="bi bi-journal-text me-2"></i>Data Santri</h3>
      <?php
      $total = mysqli_fetch_array(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM santri"))['total'];
      ?>
      <span class="text-muted mb-2">Total Data: <b><?= $total ?></b></span>
    </div>

    <!-- Form Tambah -->
    <form method="POST" class="row g-2 mb-4">
      <div class="col-md-3"><input name="nis" class="form-control" placeholder="NIS" required></div>
      <div class="col-md-4"><input name="nama" class="form-control" placeholder="Nama Santri" required></div>
      <div class="col-md-3"><input name="kelas" class="form-control" placeholder="Kelas" required></div>
      <div class="col-md-2"><button class="btn btn-success w-100" name="tambah"><i class="bi bi-plus-circle me-1"></i>Tambah</button></div>
    </form>

    <!-- Form Pencarian -->
    <div class="input-group mb-4">
      <span class="input-group-text bg-success text-white"><i class="bi bi-search"></i></span>
      <input type="text" id="searchInput" class="form-control" placeholder="Cari berdasarkan NIS, Nama, atau Kelas...">
    </div>

    <!-- Tabel Santri -->
    <div id="tableContainer" class="table-responsive">
      <table class="table table-bordered align-middle text-center">
        <thead>
          <tr>
            <th>No</th>
            <th>NIS</th>
            <th>Nama Santri</th>
            <th>Kelas</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody id="santriData">
          <?php
          $no=1;
          $data = mysqli_query($koneksi, "SELECT * FROM santri $where ORDER BY id DESC");
          while($d=mysqli_fetch_array($data)){
            echo "<tr data-id='$d[id]'>
              <td>$no</td>
              <td contenteditable='true' data-field='nis'>$d[nis]</td>
              <td contenteditable='true' data-field='nama'>$d[nama]</td>
              <td contenteditable='true' data-field='kelas'>$d[kelas]</td>
              <td><button class='btn btn-danger btn-sm' onclick='hapusData($d[id])'><i class=\"bi bi-trash\"></i> Hapus</button></td>
            </tr>";
            $no++;
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
// Notifikasi
<?php if (isset($_GET['success']) && $_GET['success'] == 'add') { ?>
Swal.fire({ icon: 'success', title: 'Santri berhasil ditambahkan!', showConfirmButton: false, timer: 1500 });
<?php } elseif (isset($_GET['success']) && $_GET['success'] == 'delete') { ?>
Swal.fire({ icon: 'warning', title: 'Data santri dihapus!', showConfirmButton: false, timer: 1500 });
<?php } ?>

// Hapus Data
function hapusData(id) {
  Swal.fire({
    title: 'Hapus data ini?',
    text: 'Data santri akan dihapus permanen!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Ya, hapus!',
    cancelButtonText: 'Batal'
  }).then((result) => {
    if (result.isConfirmed) {
      window.location = '?hapus=' + id;
    }
  });
}

// Inline Edit
$(document).ready(function(){
  $('td[contenteditable="true"]').blur(function(){
    var id = $(this).closest('tr').data('id');
    var kolom = $(this).data('field');
    var nilai = $(this).text();
    $.post('data_santri.php', {update: true, id: id, kolom: kolom, nilai: nilai}, function(){
      Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: 'Data diperbarui!',
        showConfirmButton: false,
        timer: 1000
      });
    });
  });

  // Fitur Pencarian Real-time
  $('#searchInput').on('keyup', function(){
    var keyword = $(this).val().toLowerCase();
    $("#santriData tr").filter(function() {
      $(this).toggle($(this).text().toLowerCase().indexOf(keyword) > -1);
    });
  });
});
</script>
</body>
</html>
