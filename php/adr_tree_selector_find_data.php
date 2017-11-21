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

$id_root = sql_field_val('id_root', 'int'); 
$id_find_root = sql_field_val('id_find_root', 'int'); 
$cnt = sql_field_val('cnt', 'int'); 
$pattern = sql_field_val('pattern', 'string'); 

$SQL = "select find_addr_fun($pattern,$cnt,$id_root,$id_find_root) as find_path; ";

$result = pg_query($Link,$SQL) or die("SQL Error: " .pg_last_error($Link) );

$row = pg_fetch_array($result);

$data = $row['find_path'];

//header("Content-type: application/json;charset=utf-8");
echo $data;

?>
