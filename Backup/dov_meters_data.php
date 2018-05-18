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

//throw new Exception(json_encode($_POST));
$fildsArray =DbGetFieldsArray($Link,'eqi_meter_vw');
/*
$fildsArray = array();
$fildsArray['id'] = array('f_name'=>'id','f_type'=>'integer');
$fildsArray['name'] = array('f_name'=>'name','f_type'=>'character varying');
$fildsArray['normative'] = array('f_name'=>'normative','f_type'=>'character varying');
$fildsArray['voltage_nom'] = array('f_name'=>'voltage_nom','f_type'=>'numeric');
$fildsArray['amperage_nom'] = array('f_name'=>'amperage_nom','f_type'=>'integer');
$fildsArray['kind_meter'] = array('f_name'=>'kind_meter','f_type'=>'integer');
$fildsArray['phase'] = array('f_name'=>'phase','f_type'=>'integer');
$fildsArray['carry'] = array('f_name'=>'carry','f_type'=>'integer');
$fildsArray['zones'] = array('f_name'=>'zones','f_type'=>'integer');
$fildsArray['zone_time_min'] = array('f_name'=>'zone_time_min','f_type'=>'numeric');
$fildsArray['term_control'] = array('f_name'=>'term_control','f_type'=>'integer');
$fildsArray['cl'] = array('f_name'=>'cl','f_type'=>'numeric');    
$fildsArray['buffle'] = array('f_name'=>'buffle','f_type'=>'numeric');    
$fildsArray['show_def'] = array('f_name'=>'show_def','f_type'=>'integer');
$fildsArray['ae'] = array('f_name'=>'ae','f_type'=>'integer');
$fildsArray['re'] = array('f_name'=>'re','f_type'=>'integer');
$fildsArray['aeg'] = array('f_name'=>'aeg','f_type'=>'integer');
$fildsArray['reg'] = array('f_name'=>'reg','f_type'=>'integer');
*/
//=====================================================================
//throw new Exception(json_encode($_POST));
$qWhere= DbBuildWhere($_POST,$fildsArray);
//throw new Exception(json_encode($qWhere));
//=====================================================================

$selected_id = 0;
if (isset($_POST['selected_id']))  
{
 $selected_id = $_POST['selected_id'];
}


$SQL = "select * from eqi_meter_vw as m $qWhere Order by $sidx $sord";

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


$SQL = "select * from dataset_tmp order by cnt LIMIT $limit OFFSET $start ";


/*
// connect to the database
$Query="SELECT COUNT(*) AS count FROM eqi_meter_vw $qWhere ;";
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

$SQL = "select * from eqi_meter_vw as m $qWhere Order by $sidx $sord LIMIT $limit OFFSET $start ";
*/
$result = pg_query($Link,$SQL) or die("SQL Error: " .pg_last_error($Link) );
 if (!$result) { print("<br> no records found");}
 else {
  $data['page'] = $page;
  $data['total'] = $total_pages;
  $data['records'] = $count;

  $i = 0; 
 while($row = pg_fetch_array($result)) {

//     foreach ($fildsArray as $fild) {
//         $data['rows'][$i][$fild['f_name']] = $row[$fild['f_name']];
//     }

      $data['rows'][$i]['cell'][] = $row['id'];
      $data['rows'][$i]['cell'][] = $row['name'];
      $data['rows'][$i]['cell'][]=  $row['normative'];
      $data['rows'][$i]['cell'][] = $row['voltage_nom'];
      $data['rows'][$i]['cell'][] = $row['amperage_nom'];      
      $data['rows'][$i]['cell'][] = $row['kind_meter'];            
      $data['rows'][$i]['cell'][] = $row['phase'];                  
      
      $data['rows'][$i]['cell'][] = $row['carry'];                        
      $data['rows'][$i]['cell'][] = $row['zones'];                        
      $data['rows'][$i]['cell'][] = $row['zone_time_min'];                        
      $data['rows'][$i]['cell'][] = $row['term_control'];                        
      $data['rows'][$i]['cell'][] = $row['cl'];                              
      $data['rows'][$i]['cell'][] = $row['buffle'];                              
      $data['rows'][$i]['cell'][] = $row['ae'];                                    
      $data['rows'][$i]['cell'][] = $row['re'];                                    
      $data['rows'][$i]['cell'][] = $row['aeg'];                                          
      $data['rows'][$i]['cell'][] = $row['reg'];                                          
      $data['rows'][$i]['cell'][] = $row['show_def'];                                    

    $i++;
 } 
}


header("Content-type: application/json;charset=utf-8");
echo json_encode($data);

?>