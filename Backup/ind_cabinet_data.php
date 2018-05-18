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

 $table_name = 'acd_cabindication_tbl';
 

if(!$sidx) $sidx =2;
if(!$limit) $limit = 500;
if(!$page) $page = 1;
 
$fildsArray =DbGetFieldsArray($Link,$table_name);
$fildsArray['book'] =   array('f_name'=>'book','f_type'=>'char');
$fildsArray['code'] =   array('f_name'=>'code','f_type'=>'char');
$fildsArray['address']= array('f_name'=>'address','f_type'=>'character varying');
$fildsArray['abon'] =   array('f_name'=>'abon','f_type'=>'character varying');
$fildsArray['sector'] =   array('f_name'=>'sector','f_type'=>'character varying');
$fildsArray['name_zone'] =   array('f_name'=>'name_zone','f_type'=>'character varying');
$fildsArray['represent_name'] =   array('f_name'=>'represent_name','f_type'=>'character varying');
$fildsArray['demand'] =   array('f_name'=>'demand','f_type'=>'int');


$qWhere= DbBuildWhere($_POST,$fildsArray);

$p_mmgg = sql_field_val('p_mmgg', 'date');

if ($qWhere!='') $qWhere=$qWhere.' and ';
else $qWhere=' where ';

$qWhere=$qWhere." date_trunc('month', dat_ind ) = date_trunc('month', {$p_mmgg}::date) ";


$Query="SELECT COUNT(*) AS count FROM 
(
 select pd.*, acc.book, acc.code, 
 adr.street||' '||address_print(acc.addr) as address, 
 (c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon,
 rs.name as sector, pr.represent_name, z.nm as name_zone
 from
  acd_cabindication_tbl as pd 
  join eqk_zone_tbl as z on (z.id = pd.id_zone)
  join clm_paccnt_tbl as acc on (acc.id = pd.id_paccnt)
  join clm_abon_tbl as c on (c.id = acc.id_abon) 
  join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class)
  join prs_runner_paccnt as rp on (rp.id_paccnt = acc.id)
  join prs_runner_sectors as rs on (rp.id_sector = rs.id) 
  left join prs_persons as pr on (rs.id_operator = pr.id) 
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

$SQL = "select * from (select pd.*, acc.book, acc.code, 
  adr.street||' '||address_print(acc.addr) as address, 
  (c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon,
  ('0'||substring(acc.code FROM '[0-9]+'))::int as int_code,
  ('0'||substring(acc.book FROM '[0-9]+'))::int as int_book,
  rs.name as sector, pr.represent_name, z.nm as name_zone,
  round(calc_demand_carry(pd.value_ind, pd.value_prev ,pd.carry),0)::int as demand
  from acd_cabindication_tbl as pd 
  join eqk_zone_tbl as z on (z.id = pd.id_zone)
  join clm_paccnt_tbl as acc on (acc.id = pd.id_paccnt)
  join clm_abon_tbl as c on (c.id = acc.id_abon) 
  join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class)
  join prs_runner_paccnt as rp on (rp.id_paccnt = acc.id)
  join prs_runner_sectors as rs on (rp.id_sector = rs.id) 
  left join prs_persons as pr on (rs.id_operator = pr.id) 
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
      $data['rows'][$i]['cell'][] = $row['id_meter'];
      $data['rows'][$i]['cell'][] = $row['id_meter_type'];
      $data['rows'][$i]['cell'][] = $row['id_previndic'];
      
      $data['rows'][$i]['cell'][] = $row['book'];
      $data['rows'][$i]['cell'][] = $row['code'];
      $data['rows'][$i]['cell'][] = $row['address'];
      $data['rows'][$i]['cell'][] = $row['abon'];
      
      $data['rows'][$i]['cell'][] = $row['sector'];
      $data['rows'][$i]['cell'][] = $row['represent_name'];
            
      $data['rows'][$i]['cell'][] = $row['num_eqp'];      
//      $data['rows'][$i]['cell'][] = $row['meter_type_name'];     
      
      $data['rows'][$i]['cell'][] = $row['carry'];     
//      $data['rows'][$i]['cell'][] = $row['koef'];     
      $data['rows'][$i]['cell'][] = $row['id_zone'];     
      $data['rows'][$i]['cell'][] = $row['name_zone'];     
      
      $data['rows'][$i]['cell'][] = $row['value_prev'];     
      
      $data['rows'][$i]['cell'][] = $row['value_ind'];     
      $data['rows'][$i]['cell'][] = $row['dat_ind'];           
      
      $data['rows'][$i]['cell'][] = $row['demand'];    
      $data['rows'][$i]['cell'][] = $row['now'];    
      $data['rows'][$i]['cell'][] = $row['id_operation'];    
      $data['rows'][$i]['cell'][] = $row['id_status'];    
      
    $i++;
 } 
}


header("Content-type: application/json;charset=utf-8");
echo json_encode($data);

?>