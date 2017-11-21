<?php

header('Content-type: text/html; charset=utf-8');

require_once 'Abon_en_func.php';
session_name("session_kaa");
session_start();


error_reporting(0);

$Link = get_database_link($_SESSION);
$session_user = $_SESSION['ses_usr_id'];
/*
if (isset($_SESSION['id_sess'])) {
  $ids = $_SESSION['id_sess'];

  $res_par = sel_par($ids);
  $row_par = pg_fetch_array($res_par);
  $cons = $row_par['con_str1'];
  $Q = $row_par['qu'];
  $na = $row_par['nacc'];
  $Link = pg_connect($cons);
}


$lnk_sys = log_s_pgsql("login");
$lnk1 = $lnk_sys['lnks'];

$nacc2 = 0;
$idppack = 0;
$idpdoc = 0;
$idpack = 0;
$idindic = 0;

$QS = "update dbusr_var set idppack=" . $idppack . ",idpdoc=" . $idpdoc . ", idpack=" . $idpack . ",idindic=" . $idindic .
        " where id_sess=" . $ids;
$res_qs = pg_query($lnk1, $QS);
*/
$nm1 = "Абон-енерго (Пачки)";
start_mpage($nm1);
head2_mpage();
middle_mpage();
print("<br>\n");
print("<br>\n");
//	print("<Table>\n");
//	print("<tr>\n");
//	print("<td>\n");
//	print("<a href=\"paccntipack.php\"> Пачки введення показників</a>\n");
//	print("</td>\n");
//	print("<td width=100>\n");
//	print("</td>\n");
//	print("<td>\n");
//	print("<a href=\"paccntppack.php\"> Пачки платіжних документів</a>\n");
//	print("</td>\n");
//	print("</tr>\n");
//	print("</Table>\n");

echo '<a href="dbf/upload_privat.php">Загрузка оплат</a><br>',
 '<a href="acc_oplata.php">Разнесение оплаты абонента</a><br><br>',
 
 '<a href="ind_packs.php">Показники</a><br>';


end_mpage();
?>