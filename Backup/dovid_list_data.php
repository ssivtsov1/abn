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

if ($sidx=='dt_sp')  $sidx="dt_sp $sord ,int_book $sord, book $sord, int_code $sord ,code ";

 $table_name = 'rep_spravka_tbl';
 

if(!$sidx) $sidx =2;
if(!$limit) $limit = 500;
if(!$page) $page = 1;

$fildsArray =DbGetFieldsArray($Link,$table_name);
$fildsArray['book'] =   array('f_name'=>'book','f_type'=>'char');
$fildsArray['code'] =   array('f_name'=>'code','f_type'=>'char');
$fildsArray['address']= array('f_name'=>'address','f_type'=>'character varying');
$fildsArray['town']= array('f_name'=>'town','f_type'=>'character varying');
$fildsArray['abon'] =   array('f_name'=>'abon','f_type'=>'character varying');
$fildsArray['person'] =   array('f_name'=>'person','f_type'=>'character varying');


$qWhere= DbBuildWhere($_POST,$fildsArray);

$p_mmgg = sql_field_val('p_mmgg', 'date');

if ($qWhere!='') $qWhere=$qWhere.' and ';
else $qWhere=' where ';

$qWhere=$qWhere." mmgg = date_trunc('month', {$p_mmgg}::date) ";


$Query="SELECT COUNT(*) AS count FROM 
(
 select s.*, acc.book, acc.code, 
 adr.street||' '||address_print(acc.addr) as address, adr.town,
 (c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon,
 p.represent_name as person
 from
  rep_spravka_tbl as s 
  join clm_paccnt_tbl as acc on (acc.id = s.id_paccnt)
  join clm_abon_tbl as c on (c.id = acc.id_abon) 
  join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class)
  left join prs_persons as p on (p.id = s.id_person)
  ) as ss    
$qWhere;";


$result = pg_query($Link,$Query) or die("SQL Error: " .pg_last_error($Link) );
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

$SQL = "select * from (select s.*, acc.book, acc.code, 
  adr.street||' '||address_print(acc.addr) as address, adr.town,
  (c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon,
  p.represent_name as person, 
  ('0'||substring(acc.code FROM '[0-9]+'))::int as int_code,
  ('0'||substring(acc.book FROM '[0-9]+'))::int as int_book
  from rep_spravka_tbl as s 
  join clm_paccnt_tbl as acc on (acc.id = s.id_paccnt)
  join clm_abon_tbl as c on (c.id = acc.id_abon) 
  join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class)
  left join prs_persons as p on (p.id = s.id_person)
 ) as ss    
 $qWhere Order by $sidx $sord LIMIT $limit OFFSET $start ";

//throw new Exception(json_encode($fildsArray));

$result = pg_query($Link,$SQL) or die("SQL Error: " .pg_last_error($Link) );
 if (!$result) { print("<br> no records found");}
 else {
  $data['page'] = $page;
  $data['total'] = $total_pages;
  $data['records'] = $count;
 
  $i = 0; 
 while($row = pg_fetch_array($result)) {
      $data['rows'][$i]['cell'][] = $row['id'];
      $data['rows'][$i]['cell'][] = $row['id_paccnt'];
       
      $data['rows'][$i]['cell'][] = $row['book'];
      $data['rows'][$i]['cell'][] = $row['code'];
      $data['rows'][$i]['cell'][] = $row['town']; 
      $data['rows'][$i]['cell'][] = $row['address'];
      $data['rows'][$i]['cell'][] = $row['abon'];
      
      
      $data['rows'][$i]['cell'][] = $row['num_sp'];     
      $data['rows'][$i]['cell'][] = $row['dt_sp'];     
      
      $data['rows'][$i]['cell'][] = $row['doc_type'];     
      
      $data['rows'][$i]['cell'][] = $row['date_start'];     
      $data['rows'][$i]['cell'][] = $row['date_end'];     

      $data['rows'][$i]['cell'][] = $row['num_input'];     
      $data['rows'][$i]['cell'][] = $row['dt_input'];     
      
      $data['rows'][$i]['cell'][] = $row['people_count'];     
      $data['rows'][$i]['cell'][] = $row['heat_area'];
      $data['rows'][$i]['cell'][] = $row['social_norm'];           
      $data['rows'][$i]['cell'][] = $row['show_norm'];           
      
      $data['rows'][$i]['cell'][] = $row['id_person'];    
      $data['rows'][$i]['cell'][] = $row['person'];    
      $data['rows'][$i]['cell'][] = $row['dt'];
      
      $data['rows'][$i]['cell'][] = $row['hotw'];
      $data['rows'][$i]['cell'][] = $row['hotw_gas'];
      $data['rows'][$i]['cell'][] = $row['coldw'];
      $data['rows'][$i]['cell'][] = $row['plita'];
      
    $i++;
 } 
}


header("Content-type: application/json;charset=utf-8");
echo json_encode($data);

?>