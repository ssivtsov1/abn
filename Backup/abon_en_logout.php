<?php

ob_start();

header('Content-type: text/html; charset=utf-8');

//error_reporting(0);

require 'abon_en_func.php';

session_name("session_kaa");
session_start();

if (isset($_SESSION['id_sess'])) {
  $ids = $_SESSION['id_sess'];

  $res_par = sel_par($ids);
  $row_par = pg_fetch_array($res_par);
  $cons = $row_par['con_str1'];
  $lnk_cl = pg_connect($cons);

  $cl = pg_close($lnk_cl);

  if ($cl) {
    del_par($ids);
    unset($_SESSION['id_sess']);
//		unset($_SESSION['check_str']);
    session_destroy();
  } else {
    print"Error";
  }
}

header("Location: abon_en_login.php");
ob_end_flush();
?>