<?php
session_start();
if (!isset($_SESSION['login'])) {
  header("Location: index.php");
  exit;
}

$source = __DIR__;
$dest   = __DIR__ . "/backup/backup_" . date("Ymd_His");

function copyFolder($src, $dst) {
  mkdir($dst, 0777, true);
  foreach (scandir($src) as $file) {
    if ($file == '.' || $file == '..' || $file == 'backup') continue;
    if (is_dir("$src/$file")) {
      copyFolder("$src/$file", "$dst/$file");
    } else {
      copy("$src/$file", "$dst/$file");
    }
  }
}

copyFolder($source, $dest);
echo "<h3>✅ Backup Folder Berhasil</h3>";
echo "Lokasi: <b>$dest</b>";
?>
