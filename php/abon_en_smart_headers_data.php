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

$page = $_POST['page']; // get the requested page
$limit = $_POST['rows']; // get how many rows we want to have into the grid
$sidx = $_POST['sidx']; // get index row - i.e. user click to sort
$sord = $_POST['sord']; // get the direction

 // get how many rows we want to have into the grid

if(!$sidx) $sidx =2;
if(!$limit) $limit = 500;
if(!$page) $page = 1;

//if ($sidx=='dt_action')  $sidx="dt_action $sord ,int_book $sord ,int_code $sord ,code";
//if ($sidx=='book')  $sidx="int_book $sord ,int_code $sord ,code";
//if ($sidx=='code')  $sidx="int_book ASC , int_code $sord ,code";

// connect to the database
$fildsArray =DbGetFieldsArray($Link,'ind_smart_files_tbl');
$fildsArray['ident'] = array('f_name' => 'ident', 'f_type' => 'character varying');
$fildsArray['addr_str'] = array('f_name' => 'addr_str', 'f_type' => 'character varying');
$fildsArray['book'] = array('f_name' => 'book', 'f_type' => 'character varying');


$qWhere= DbBuildWhere($_POST,$fildsArray);

$p_mmgg = sql_field_val('p_mmgg', 'mmgg');

if ($qWhere!='') $qWhere=$qWhere.' and ';
else $qWhere=' where ';

$qWhere=$qWhere." mmgg = {$p_mmgg}::date ";



$Query="SELECT COUNT(*) AS count FROM 
(
 select f.*, h.ident, h.book,
 (adr.adr||' '||
   (coalesce('буд.'||(h.addr).house||'','')||
		coalesce('/'||(h.addr).slash||' ','')||
			coalesce(' корп.'||(h.addr).korp||'','')||
                            coalesce(','||(h.addr).note||' ','') )::varchar
 )::varchar as addr_str
from ind_smart_files_tbl as f
left join ind_smart_house_tbl as h on (h.id = f.id_house) 
left join adt_addr_tbl as adr on (adr.id = (h.addr).id_class)
) as ss
$qWhere;";

$result = pg_query($Link,$Query) or die("SQL Error: " .pg_last_error($Link) );
//$result = pg_query($Link,$Query) or die("SQL Error: " .$Query );

$row = pg_fetch_array($result);
$count = $row['count'];

//print("<br> count =  $count "); 

if( $count >0  && $limit > 0) {
	$total_pages = ceil($count/$limit);

} else {
	$total_pages = 0;
}

if ($page > $total_pages) $page=$total_pages;

$start = $limit*$page - $limit; // do not put $limit*($page - 1)
if($start < 0) $start = 0; 

$SQL = "select * from (
   select f.*, h.ident, h.book,
   (adr.adr||' '||
    (coalesce('буд.'||(h.addr).house||'','')||
		coalesce('/'||(h.addr).slash||' ','')||
			coalesce(' корп.'||(h.addr).korp||'','')||
                            coalesce(','||(h.addr).note||' ','') )::varchar
   )::varchar as addr_str
   from ind_smart_files_tbl as f
   left join ind_smart_house_tbl as h on (h.id = f.id_house) 
   left join adt_addr_tbl as adr on (adr.id = (h.addr).id_class)
) as ss
  $qWhere Order by $sidx $sord LIMIT $limit OFFSET $start ";

$result = pg_query($Link,$SQL) or die("SQL Error: " .pg_last_error($Link).$SQL );
 if (!$result) { print("<br> no records found");}
 else {
  $data['page'] = $page;
  $data['total'] = $total_pages;
  $data['records'] = $count;

  $i = 0; 
 while($row = pg_fetch_array($result)) {

      $data['rows'][$i]['cell'][] = $row['id'];
      $data['rows'][$i]['cell'][] = $row['id_house'];
      $data['rows'][$i]['cell'][]=  $row['name_file'];
      $data['rows'][$i]['cell'][] = $row['ident'];
      $data['rows'][$i]['cell'][] = $row['addr_str'];      
      $data['rows'][$i]['cell'][] = $row['book'];            
      $data['rows'][$i]['cell'][] = $row['status'];                  
      $data['rows'][$i]['cell'][] = $row['date_ind'];                  
      $data['rows'][$i]['cell'][] = $row['mmgg'];                  
      $data['rows'][$i]['cell'][] = $row['dt'];                  

    $i++;
 } 
}


header("Content-type: application/json;charset=utf-8");
echo json_encode($data);

?>