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

// connect to the database
$fildsArray =DbGetFieldsArray($Link,'clm_abon_tbl');
$fildsArray['user_name'] =   array('f_name'=>'user_name','f_type'=>'character varying');

$qWhere= DbBuildWhere($_POST,$fildsArray);

$selected_id = 0;
if (isset($_POST['selected_id']))  
{
 $selected_id = $_POST['selected_id'];
}

/*
$SQL = "select с.*, adr1.adr as addr_reg, adr2.adr as addr_live 
from clm_abon_tbl as с 
left join adv_address_tbl as adr1 on (adr1.id = с.id_addr_reg)
left join adv_address_tbl as adr2 on (adr2.id = с.id_addr_live)
  $qWhere Order by $sidx $sord ";
*/

$SQL = "select * from (select c.*, adr1.adr||' '||address_print(c.addr_reg) as addr_reg_str, 
adr2.adr||' '||address_print(c.addr_live) as addr_live_str , u1.name as user_name 
from clm_abon_tbl as c 
left join adt_addr_tbl as adr1 on (adr1.id = (c.addr_reg).id_class)
left join adt_addr_tbl as adr2 on (adr2.id = (c.addr_live).id_class)  
 left join syi_user as u1 on (u1.id = c.id_person)
) as sss
  $qWhere Order by $sidx $sord ";
 
//---------------- -- -- --------------- -- -- -----------------------------//

$Query = "CREATE temp SEQUENCE row_numbers_seq minvalue 1;";
$result = pg_query($Link,$Query) or die("SQL Error: " .pg_last_error($Link) );

$Query = "CREATE temp TABLE dataset_tmp AS
         select nextval('row_numbers_seq') as cnt, * from ($SQL) as ss;";
$result = pg_query($Link,$Query) or die("SQL Error: " .pg_last_error($Link) );


$Query="SELECT COUNT(*) AS count FROM dataset_tmp ;";
$result = pg_query($Link,$Query) or die("SQL Error: " .pg_last_error($Link) );
$row = pg_fetch_array($result);
$count = $row['count'];

if( $count >0  && $limit > 0) {
	$total_pages = ceil($count/$limit);

} else {
	$total_pages = 0;
}
if ($page > $total_pages) $page=$total_pages;

if ($selected_id!=0) 
{
    $Query="select cnt from dataset_tmp where id = $selected_id ;";
    $result = pg_query($Link,$Query) or die("SQL Error: " .pg_last_error($Link) );
    if ($result)
    {
       $row = pg_fetch_array($result);
       $row_cnt = $row['cnt'];
       if ($row_cnt>0)
       {
         $page = ceil($row_cnt/$limit);
       }
     }
}
 
$start = $limit*$page - $limit; // do not put $limit*($page - 1)
if($start < 0) $start = 0; 


$Query = "select * from dataset_tmp order by cnt LIMIT $limit OFFSET $start ";

$result = pg_query($Link,$Query) or die("SQL Error: " .pg_last_error($Link) );
 if (!$result) { print("<br> no records found");}
 else {
  $data['page'] = $page;
  $data['total'] = $total_pages;
  $data['records'] = $count;

  $i = 0; 
 while($row = pg_fetch_array($result)) {

      $data['rows'][$i]['cell'][] = $row['id'];
      $data['rows'][$i]['cell'][] = $row['last_name'];
      $data['rows'][$i]['cell'][]=  $row['name'];
      $data['rows'][$i]['cell'][] = $row['patron_name'];
      $data['rows'][$i]['cell'][] = $row['s_doc'];      
      $data['rows'][$i]['cell'][] = $row['n_doc'];
      $data['rows'][$i]['cell'][] = $row['dt_doc'];      
      $data['rows'][$i]['cell'][] = $row['who_doc'];            
      $data['rows'][$i]['cell'][] = $row['addr_reg'];                  
      $data['rows'][$i]['cell'][] = $row['addr_reg_str'];                        
      $data['rows'][$i]['cell'][] = $row['addr_live'];                  
      $data['rows'][$i]['cell'][] = $row['addr_live_str'];                        
      $data['rows'][$i]['cell'][] = $row['tax_number'];                        
      $data['rows'][$i]['cell'][] = $row['home_phone'];                        
      $data['rows'][$i]['cell'][] = $row['work_phone'];                        
      $data['rows'][$i]['cell'][] = $row['mob_phone'];                        
      $data['rows'][$i]['cell'][] = $row['e_mail'];                        
      $data['rows'][$i]['cell'][] = $row['dt_b'];                        
      $data['rows'][$i]['cell'][] = $row['user_name'];                        
      $data['rows'][$i]['cell'][] = $row['dt_input'];                              
      $data['rows'][$i]['cell'][] = $row['note'];                              
      
    $i++;
 } 
}


header("Content-type: application/json;charset=utf-8");
echo json_encode($data);

?>