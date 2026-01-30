<?php
session_start();
include "koneksi.php";

if (!isset($_SESSION['login'])) {
  header("Location: index.php");
  exit;
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

/* QUERY DATA */
$q = mysqli_query($koneksi, "
  SELECT s.nis, s.nama,
    SUM(CASE WHEN t.jenis='setor' THEN t.jumlah ELSE 0 END) AS total_setor,
    SUM(CASE WHEN t.jenis='tarik' THEN t.jumlah ELSE 0 END) AS total_tarik,
    (SUM(CASE WHEN t.jenis='setor' THEN t.jumlah ELSE 0 END) -
     SUM(CASE WHEN t.jenis='tarik' THEN t.jumlah ELSE 0 END)) AS saldo
  FROM santri s
  LEFT JOIN tabungan t ON s.nis = t.nis
  GROUP BY s.nis
");

/* GRAND TOTAL */
$grand_total = 0;
$data = [];
while($row = mysqli_fetch_assoc($q)){
  $grand_total += $row['saldo'];
  $data[] = $row;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Laporan Saldo Tabungan</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>
body{background:#f4f8f5}
.container-box{
  background:#fff;
  padding:25px;
  border-radius:12px;
  box-shadow:0 4px 15px rgba(0,0,0,.08);
  margin-top:30px;
}
h3{color:#2e7d32;font-weight:600}
.table thead th{
  background:#a5d6a7;
  color:#1b5e20;
  text-align:center;
}
.text-rp{width:35px;text-align:center}
.text-nominal{text-align:right}
.saldo{font-weight:700;color:#2e7d32}
tfoot td{
  background:#e8f5e9;
  font-weight:700;
}
@media print{
  .no-print{display:none}
}
</style>
</head>

<body>

<div class="container">
  <div class="container-box">

    <!-- HEADER -->
    <div class="d-flex justify-content-between mb-4">
      <h3><i class="bi bi-wallet2"></i> Laporan Saldo Tabungan</h3>
      <div class="no-print">
        <button onclick="printSaldoAkhir()" class="btn btn-outline-success btn-sm">
          <i class="bi bi-printer"></i> Cetak Saldo Akhir
        </button>
        <a href="dashboard.php" class="btn btn-success btn-sm ms-2">
          <i class="bi bi-arrow-left"></i> Kembali
        </a>
      </div>
    </div>

    <!-- TABEL -->
    <div class="table-responsive">
      <table id="tabelSaldo" class="table table-bordered table-hover align-middle">
        <thead>
          <tr>
            <th>No</th>
            <th>NIS</th>
            <th>Nama</th>
            <th>Rp</th>
            <th>Total Setor</th>
            <th>Rp</th>
            <th>Total Tarik</th>
            <th>Rp</th>
            <th>Saldo Akhir</th>
          </tr>
        </thead>
        <tbody>
        <?php $no=1; foreach($data as $d): ?>
          <tr>
            <td class="text-center"><?= $no++ ?></td>
            <td>
              <a href="#" class="fw-semibold text-decoration-none"
                 onclick="showHistory('<?= $d['nis'] ?>')">
                 <?= $d['nis'] ?>
              </a>
            </td>
            <td><?= $d['nama'] ?></td>
            <td class="text-rp">Rp</td>
            <td class="text-nominal"><?= number_format($d['total_setor'],0,',','.') ?></td>
            <td class="text-rp">Rp</td>
            <td class="text-nominal"><?= number_format($d['total_tarik'],0,',','.') ?></td>
            <td class="text-rp saldo">Rp</td>
            <td class="text-nominal saldo"><?= number_format($d['saldo'],0,',','.') ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>

        <!-- GRAND TOTAL -->
        <tfoot>
          <tr>
            <td colspan="8" class="text-end">GRAND TOTAL SALDO</td>
            <td class="text-nominal saldo">
              Rp <?= number_format($grand_total,0,',','.') ?>
            </td>
          </tr>
        </tfoot>
      </table>
    </div>

  </div>
</div>

<!-- CETAK SALDO AKHIR -->
<div id="printArea" style="display:none">
  <h4 class="text-center mb-3">Laporan Saldo Akhir Santri</h4>
  <table class="table table-bordered">
    <thead>
      <tr>
        <th>No</th>
        <th>NIS</th>
        <th>Nama</th>
        <th>Saldo Akhir</th>
      </tr>
    </thead>
    <tbody>
    <?php $no=1; foreach($data as $d): ?>
      <tr>
        <td><?= $no++ ?></td>
        <td><?= $d['nis'] ?></td>
        <td><?= $d['nama'] ?></td>
        <td>Rp <?= number_format($d['saldo'],0,',','.') ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
    <tfoot>
      <tr>
        <th colspan="3" class="text-end">GRAND TOTAL</th>
        <th>Rp <?= number_format($grand_total,0,',','.') ?></th>
      </tr>
    </tfoot>
  </table>
</div>

<!-- MODAL RIWAYAT -->
<div class="modal fade" id="modalHistory" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title">Riwayat Tabungan</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="historyContent">Memuat data...</div>
      <div class="modal-footer">
        <button class="btn btn-outline-success" onclick="printDiv('historyContent')">
          <i class="bi bi-printer"></i> Cetak
        </button>
        <button class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<!-- JS -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
$('#tabelSaldo').DataTable({
  pageLength:10,
  language:{
    search:"Cari:",
    lengthMenu:"Tampilkan _MENU_ data",
    info:"Menampilkan _START_ - _END_ dari _TOTAL_ data"
  }
});

function showHistory(nis){
  $('#modalHistory').modal('show');
  $('#historyContent').load('riwayat_tabungan.php?nis='+nis);
}

function printDiv(id){
  let isi=document.getElementById(id).innerHTML;
  let w=window.open('','','width=900,height=600');
  w.document.write('<html><head><title>Cetak</title>');
  w.document.write('<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">');
  w.document.write('</head><body>'+isi+'</body></html>');
  w.document.close(); w.print();
}

function printSaldoAkhir(){
  let isi=document.getElementById('printArea').innerHTML;
  let w=window.open('','','width=900,height=600');
  w.document.write('<html><head><title>Cetak Saldo Akhir</title>');
  w.document.write('<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">');
  w.document.write('</head><body>'+isi+'</body></html>');
  w.document.close(); w.print();
}
</script>

</body>
</html>
