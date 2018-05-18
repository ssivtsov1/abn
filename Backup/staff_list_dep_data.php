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

$sidx = $_POST['sidx']; // get index row - i.e. user click to sort
$sord = $_POST['sord']; // get the direction

 
//$node = (integer)$_POST["nodeid"];
//$n_lvl = (integer)$_POST["n_level"];

//if(!$sidx) $sidx =2;
//if(!$limit) $limit = 500;
//if(!$page) $page = 1;

$SQL = "SELECT t1.id FROM prs_department AS t1 LEFT JOIN prs_department as t2 "
   ." ON (t1.id = t2.id_parent_department) WHERE t2.id IS NULL";

$result = pg_query($Link,$SQL) or die("SQL Error: " .pg_last_error($Link) );
$leafnodes = array();
while($rw = pg_fetch_array($result)) {
   $leafnodes[$rw['id']] = $rw['id'];
}

//$fildsArray =DbGetFieldsArray($Link,'lgi_kategor_tbl');

//$qWhere= DbBuildWhere($_POST,$fildsArray);

/*
if($node >0) { 
   $qWhere = 'where id_parent_department='.$node; // parents
   $n_lvl = $n_lvl+1; // we should ouput next level
} else {
   $qWhere = 'where id_parent_department is null'; // roots
}
*/

$SQL = "select d.*, lvl - 1 as level_field  from prs_department as d order by sort;";

//throw new Exception(json_encode($SQL));

$result = pg_query($Link,$SQL) or die("SQL Error: " .pg_last_error($Link) );
 if ($result) 
 {
  $i = 0; 
 while($row = pg_fetch_array($result)) {
/*
      $data['rows'][$i]['id'] = $row['id'];
      $data['rows'][$i]['id_parrent'] = $row['id_parent'];
      $data['rows'][$i]['id_doc'] = $row['id_doc'];
      $data['rows'][$i]['lvl'] = $row['lvl'];
      
      $data['rows'][$i]['name'] = $row['name'];
      $data['rows'][$i]['ident'] = $row['ident'];

      $data['rows'][$i]['dt_b'] = $row['dt_b'];
      $data['rows'][$i]['dt_e'] = $row['dt_e'];
      
      $data['rows'][$i]['dt'] = $row['dt'];

      $data['rows'][$i]['level_field'] = $row['level_field'];
      $data['rows'][$i]['parent_id_field'] = $row['id_parent'];
      $data['rows'][$i]['leaf_field'] = true;
      $data['rows'][$i]['expanded_field'] = true;
      $data['rows'][$i]['loaded'] = true;
      */

      $data['rows'][$i]['cell'][] = $row['id'];
      $data['rows'][$i]['cell'][] = $row['id_parent_department'];
      $data['rows'][$i]['cell'][] = $row['lvl'];
      
      $data['rows'][$i]['cell'][] = $row['name'];
      $data['rows'][$i]['cell'][] = $row['full_name'];

      $data['rows'][$i]['cell'][] = $row['level_field'];
      $data['rows'][$i]['cell'][] = $row['id_parent_department'];
      
      if($row['id'] == $leafnodes[$row['id']]) 
        $data['rows'][$i]['cell'][] =  true;
      else
        $data['rows'][$i]['cell'][] =  false;   
      
      $data['rows'][$i]['cell'][] = true;
      $data['rows'][$i]['cell'][] = true;

    $i++;
 } 
}


header("Content-type: application/json;charset=utf-8");
echo json_encode($data);

?>