<?php
include "koneksi.php";

if (!isset($_GET['nis'])) {
  echo "<p class='text-danger'>NIS tidak ditemukan.</p>";
  exit;
}

$nis = $_GET['nis'];

// Ambil data santri
$santri = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT nama FROM santri WHERE nis='$nis'"));
if (!$santri) {
  echo "<p class='text-danger'>Data santri tidak ditemukan.</p>";
  exit;
}

// Ambil riwayat tabungan
$q = mysqli_query($koneksi, "SELECT * FROM tabungan WHERE nis='$nis' ORDER BY tanggal ASC, id ASC");

if (mysqli_num_rows($q) == 0) {
  echo "<p class='text-center text-muted'>Belum ada transaksi untuk santri ini.</p>";
  exit;
}

// Perhitungan saldo berjalan
$saldo = 0;
?>

<!-- Judul dan Tombol Cetak -->
<div class="d-flex justify-content-between align-items-center mb-3">
  <h5 class="text-success fw-bold mb-0">
    Nama: <?= htmlspecialchars($santri['nama']) ?> (<?= htmlspecialchars($nis) ?>)
  </h5>
  <button class="btn btn-success btn-sm" onclick="window.print()">
    🖨️ Cetak
  </button>
</div>

<!-- Tabel Riwayat Tabungan -->
<div class="table-responsive">
  <table class="table table-bordered table-striped align-middle">
    <thead class="table-success text-center">
      <tr>
        <th>Tanggal</th>
        <th>Jenis</th>
        <th>Nominal</th>
        <th>Keterangan</th>
        <th>Saldo</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($d = mysqli_fetch_assoc($q)): ?>
        <?php
          if ($d['jenis'] == 'setor') $saldo += $d['jumlah'];
          else if ($d['jenis'] == 'tarik') $saldo -= $d['jumlah'];
        ?>
        <tr>
          <td><?= date('d/m/Y', strtotime($d['tanggal'])) ?></td>
          <td class="text-capitalize text-center"><?= $d['jenis'] ?></td>
          <td>
            <div class="d-flex justify-content-between">
              <span>Rp</span>
              <span><?= number_format($d['jumlah'], 0, ',', '.') ?></span>
            </div>
          </td>
          <td><?= $d['keterangan'] ?: '-' ?></td>
          <td>
            <div class="d-flex justify-content-between text-success fw-semibold">
              <span>Rp</span>
              <span><?= number_format($saldo, 0, ',', '.') ?></span>
            </div>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>

    <!-- Footer hanya muncul di halaman terakhir saat cetak -->
    <tfoot>
      <tr class="table-light fw-bold">
        <td colspan="4" class="text-end">Saldo Akhir:</td>
        <td>
          <div class="d-flex justify-content-between text-success">
            <span>Rp</span>
            <span><?= number_format($saldo, 0, ',', '.') ?></span>
          </div>
        </td>
      </tr>
    </tfoot>
  </table>
</div>

<!-- CSS -->
<style>
  table td, table th {
    vertical-align: middle !important;
  }
  table td div.d-flex {
    font-family: monospace;
  }

  /* Tombol cetak tidak tampil saat print */
  @media print {
    button {
      display: none !important;
    }

    /* Header tabel tetap muncul di setiap halaman */
    thead {
      display: table-header-group;
    }

    /* Footer (saldo akhir) hanya muncul di halaman terakhir */
    tfoot {
      display: table-row-group;
      page-break-inside: avoid;
    }

    /* Hindari terpotong di tengah halaman */
    tr, td, th {
      page-break-inside: avoid;
    }

    body {
      margin: 10mm;
    }
  }
</style>
