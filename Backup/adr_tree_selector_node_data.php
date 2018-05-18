<?php
header('Content-type: text/html; charset=utf-8');

require 'abon_en_func.php';
require 'abon_ded_func.php';

session_name("session_kaa");
session_start();

error_reporting(1);

$Link = get_database_link($_SESSION,1);
$session_user = $_SESSION['ses_usr_id'];
session_write_close();

$id_class = $_POST['id_class']; 


$SQL = "select * from adi_class_tbl where id = $id_class ;";


$result = pg_query($Link,$SQL) or die("SQL Error: " .pg_last_error($Link) );

$row = pg_fetch_array($result);


 /*
 $data['id'] = $row['id'];
 $data['id_parent'] = $row['id_parent'];
 $data['ident'] = $row['ident'];
 $data['name'] = $row['name'];
 $data['name_full'] = $row['name_full'];
 $data['idk_class'] = $row['id'];
 $data['id'] = $row['id'];
 $data['id'] = $row['id'];
 $data['id'] = $row['id'];
 */

header("Content-type: application/json;charset=utf-8");
echo json_encode($row);

?>
