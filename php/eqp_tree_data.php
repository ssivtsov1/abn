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

$id_tree = $_POST['id_tree']; 
$id_paccnt = $_POST['id_paccnt']; 

$SQL = "select parse_tree_start_fun($id_paccnt) as treedata; ";

$result = pg_query($Link,$SQL) or die("SQL Error: " .pg_last_error($Link) );

$row = pg_fetch_array($result);

$data = $row['treedata'];

header("Content-type: application/json;charset=utf-8");
echo $data;

?>
