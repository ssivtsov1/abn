<?php

ob_start();

exec('./backup/q.sh');

if ($_GET['a'] == 1) {
  $file = "./backup/abn.dmp.gz";
  if (file_exists($file)) {
    header("Content-Type: application/octet-stream");
    header("Accept-Ranges: bytes");
    header("Content-Length: " . filesize($file));
    $dt = date('Ymd-Hi');
    header("Content-Disposition: attachment; filename=$dt.gz");
    readfile($file);
  } else {
    echo "$file not exists!!";
  }
}

if ($_GET['a'] == 2) {
  $_SESSION['backup'] = 1;
  header('Location: ' . $_SERVER['HTTP_REFERER']);
}

ob_end_flush();
?>
