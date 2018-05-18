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

$SQL = "select id,name,coalesce(short_prefix,'') as short_prefix,
    coalesce(short_postfix,'') as short_postfix, ident, 
    coalesce(icon,'') as icon
    from adk_class_tbl order by id; ";

$result = pg_query($Link,$SQL) or die("SQL Error: " .pg_last_error($Link) );

 while($row = pg_fetch_array($result)) { 

      $data[$row['id']]['id'] = $row['id'];
      $data[$row['id']]['name'] = $row['name'];
      $data[$row['id']]['sh_prf'] = $row['short_prefix'];
      $data[$row['id']]['sh_post'] = $row['short_postfix'];
      $data[$row['id']]['ident'] = $row['ident'];
      $data[$row['id']]['icon'] = $row['icon'];
 } 


header("Content-type: application/json;charset=utf-8");
echo json_encode($data);

?>
