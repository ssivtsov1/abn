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

$id_root = $_POST['id_root']; 
$id_class = $_POST['id_class']; 
$mode = $_POST['mode']; 

if ($mode=='init')
{
   if ($id_root==0) 
       $id_root = 'null';
   
   if ($id_class==0) 
       $id_class = 'null';
   
   $SQL = "select parse_addr_tree_lazy_fun($id_root,-1,$id_class,1,'') as treedata; ";
}

if ($mode=='lazy')
   $SQL = "select parse_addr_tree_lazy_fun($id_root,-1,null,0,'') as treedata; ";

$result = pg_query($Link,$SQL) or die("SQL Error: " .pg_last_error($Link) );

$row = pg_fetch_array($result);

$data = $row['treedata'];

header("Content-type: application/json;charset=utf-8");
echo $data;

?>