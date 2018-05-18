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
$flag_cek = is_cek($Link);  //принадлежность РЭСа к ЦЭК

if(!$sidx) $sidx =2;
if(!$limit) $limit = 500;
if(!$page) $page = 1;

if ($sidx=='code')  $sidx='book,int_code,code';
if ($sidx=='book')  $sidx='book,int_code,code';

//$fildsArray =DbGetFieldsArray($Link,$table_name);
$fildsArray['id'] =   array('f_name'=>'id','f_type'=>'int');
$fildsArray['book'] =   array('f_name'=>'book','f_type'=>'char');
$fildsArray['code'] =   array('f_name'=>'code','f_type'=>'char');
$fildsArray['abon'] =   array('f_name'=>'abon','f_type'=>'character varying');
$fildsArray['town'] =   array('f_name'=>'town','f_type'=>'character varying');
$fildsArray['street'] =   array('f_name'=>'street','f_type'=>'character varying');

if($flag_cek){
    $fildsArray['house'] =   array('f_name'=>'house','f_type'=>'char');
    $fildsArray['house_letter'] =   array('f_name'=>'house_letter','f_type'=>'char');
}
else {
    $fildsArray['house'] =   array('f_name'=>'house','f_type'=>'character varying');
}

$fildsArray['slash'] =   array('f_name'=>'slash','f_type'=>'character varying');
$fildsArray['korp'] =   array('f_name'=>'korp','f_type'=>'character varying');
$fildsArray['flat'] =   array('f_name'=>'flat','f_type'=>'char');
$fildsArray['f_slash'] =   array('f_name'=>'f_slash','f_type'=>'character varying');
$fildsArray['sector'] =   array('f_name'=>'sector','f_type'=>'character varying');

// Массив исключений по типам данных
//$fieldsExeption = ['num_pack'];
//$qWhere= DbBuildWhere($_POST,$fildsArray,0,$fieldsExeption);

$qWhere= DbBuildWhere($_POST,$fildsArray);


/*
$Query="SELECT COUNT(*) AS count FROM 
(select acc.id, acc.book, acc.code, t.name as town, s.name as street, 
    adr.house , adr.slash,adr.korp,adr.flat,adr.f_slash, ps.name as sector,
 (c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon
from clm_paccnt_tbl as acc 
join clm_abon_tbl as c on (c.id = acc.id_abon) 
left join adm_address_tbl as adr on (adr.id = acc.id_addr)
left join adi_street_tbl as s on (s.id = adr.id_street)
left join adi_town_tbl as t  on (t.id = s.id_town)
left join prs_runner_paccnt as pp on (acc.id = pp.id_paccnt)
left join prs_runner_sectors as ps on (ps.id = pp.id_sector)
) as ss $qWhere;";

*/
if($flag_cek){
$Query="SELECT COUNT(*) AS count FROM 
(select acc.id, acc.book, acc.code, 
 regexp_replace(regexp_replace(acc.code, '-.*?$', '') , '[^0-9]', '','g')::int as int_code,
   CASE WHEN k1.ident in (4,5) THEN a1.name  
        WHEN k2.ident in (4,5) THEN a2.name  
        WHEN k3.ident in (4,5) THEN a3.name  
   END as town,
   CASE WHEN k1.ident = 7 THEN a1.name  
        WHEN k2.ident = 7 THEN a2.name  
        WHEN k3.ident = 7 THEN a3.name  
   END as street,
   CASE WHEN isdigit((acc.addr).house)='t' THEN cast((acc.addr).house as int)
   WHEN isdigit((acc.addr).house)='f' THEN cast(regexp_replace((acc.addr).house, '[^0-9]', '', 'g') as int) END as house,
   
   CASE WHEN isdigit((acc.addr).house)='t' THEN null
   WHEN isdigit((acc.addr).house)='f' THEN regexp_replace((acc.addr).house, '[0-9]', '', 'g') END as house_letter,  

   (acc.addr).slash,(acc.addr).korp,
   cast(CASE WHEN isdigit((acc.addr).flat)='t' THEN cast((acc.addr).flat as int)
            WHEN isdigit((acc.addr).flat)='f' THEN null
        END as int) as flat, (acc.addr).f_slash,
  ps.name as sector,
 (c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon
from clm_paccnt_tbl as acc 
join clm_abon_tbl as c on (c.id = acc.id_abon) 
join adi_class_tbl as a1 on (a1.id = (acc.addr).id_class)
join adk_class_tbl as k1 on (k1.id =a1.idk_class)
left join adi_class_tbl as a2 on (a2.id = a1.id_parent)
left join adk_class_tbl as k2 on (k2.id =a2.idk_class)
left join adi_class_tbl as a3 on (a3.id = a2.id_parent)
left join adk_class_tbl as k3 on (k3.id =a3.idk_class)
left join prs_runner_paccnt as pp on (acc.id = pp.id_paccnt)
left join prs_runner_sectors as ps on (ps.id = pp.id_sector)
) as ss $qWhere;";
}
else {
  $Query="SELECT COUNT(*) AS count FROM 
(select acc.id, acc.book, acc.code, 
 regexp_replace(regexp_replace(acc.code, '-.*?$', '') , '[^0-9]', '','g')::int as int_code,
   CASE WHEN k1.ident in (4,5) THEN a1.name  
        WHEN k2.ident in (4,5) THEN a2.name  
        WHEN k3.ident in (4,5) THEN a3.name  
   END as town,
   CASE WHEN k1.ident = 7 THEN a1.name  
        WHEN k2.ident = 7 THEN a2.name  
        WHEN k3.ident = 7 THEN a3.name  
   END as street,
   (acc.addr).house , (acc.addr).slash,(acc.addr).korp,(acc.addr).flat,(acc.addr).f_slash,
  ps.name as sector,
 (c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon
from clm_paccnt_tbl as acc 
join clm_abon_tbl as c on (c.id = acc.id_abon) 
join adi_class_tbl as a1 on (a1.id = (acc.addr).id_class)
join adk_class_tbl as k1 on (k1.id =a1.idk_class)
left join adi_class_tbl as a2 on (a2.id = a1.id_parent)
left join adk_class_tbl as k2 on (k2.id =a2.idk_class)
left join adi_class_tbl as a3 on (a3.id = a2.id_parent)
left join adk_class_tbl as k3 on (k3.id =a3.idk_class)
left join prs_runner_paccnt as pp on (acc.id = pp.id_paccnt)
left join prs_runner_sectors as ps on (ps.id = pp.id_sector)
) as ss $qWhere;";

}

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
// cast((acc.addr).house as int) as house
if($flag_cek){
$SQL = "select * from (
    select acc.id, acc.book, acc.code, 
    regexp_replace(regexp_replace(acc.code, '-.*?$', '') , '[^0-9]', '','g')::int as int_code,
   CASE WHEN k1.ident in (4,5) THEN a1.name  
        WHEN k2.ident in (4,5) THEN a2.name  
        WHEN k3.ident in (4,5) THEN a3.name  
   END as town,
   CASE WHEN k1.ident = 7 THEN a1.name  
        WHEN k2.ident = 7 THEN a2.name  
        WHEN k3.ident = 7 THEN a3.name  
   END as street,
   CASE WHEN isdigit((acc.addr).house)='t' THEN cast((acc.addr).house as int)
   WHEN isdigit((acc.addr).house)='f' THEN cast(regexp_replace((acc.addr).house, '[^0-9]', '', 'g') as int) END as house,
   
   CASE WHEN isdigit((acc.addr).house)='t' THEN null
   WHEN isdigit((acc.addr).house)='f' THEN regexp_replace((acc.addr).house, '[0-9]', '', 'g') END as house_letter,
   
   (acc.addr).slash,(acc.addr).korp,
   cast(CASE WHEN isdigit((acc.addr).flat)='t' THEN cast((acc.addr).flat as int)
             WHEN isdigit((acc.addr).flat)='f' THEN null
        END as int) as flat, (acc.addr).f_slash,
  ps.name as sector,
 (c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon
from clm_paccnt_tbl as acc 
join clm_abon_tbl as c on (c.id = acc.id_abon) 
join adi_class_tbl as a1 on (a1.id = (acc.addr).id_class)
join adk_class_tbl as k1 on (k1.id =a1.idk_class)
left join adi_class_tbl as a2 on (a2.id = a1.id_parent)
left join adk_class_tbl as k2 on (k2.id =a2.idk_class)
left join adi_class_tbl as a3 on (a3.id = a2.id_parent)
left join adk_class_tbl as k3 on (k3.id =a3.idk_class)
left join prs_runner_paccnt as pp on (acc.id = pp.id_paccnt)
left join prs_runner_sectors as ps on (ps.id = pp.id_sector)
) as ss
  $qWhere Order by $sidx $sord LIMIT $limit OFFSET $start ";
}
else {
    $SQL = "select * from (
    select acc.id, acc.book, acc.code, 
    regexp_replace(regexp_replace(acc.code, '-.*?$', '') , '[^0-9]', '','g')::int as int_code,
   CASE WHEN k1.ident in (4,5) THEN a1.name  
        WHEN k2.ident in (4,5) THEN a2.name  
        WHEN k3.ident in (4,5) THEN a3.name  
   END as town,
   CASE WHEN k1.ident = 7 THEN a1.name  
        WHEN k2.ident = 7 THEN a2.name  
        WHEN k3.ident = 7 THEN a3.name  
   END as street,
   (acc.addr).house,(acc.addr).slash,(acc.addr).korp,
   (acc.addr).flat,(acc.addr).f_slash,
  ps.name as sector,
 (c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon
from clm_paccnt_tbl as acc 
join clm_abon_tbl as c on (c.id = acc.id_abon) 
join adi_class_tbl as a1 on (a1.id = (acc.addr).id_class)
join adk_class_tbl as k1 on (k1.id =a1.idk_class)
left join adi_class_tbl as a2 on (a2.id = a1.id_parent)
left join adk_class_tbl as k2 on (k2.id =a2.idk_class)
left join adi_class_tbl as a3 on (a3.id = a2.id_parent)
left join adk_class_tbl as k3 on (k3.id =a3.idk_class)
left join prs_runner_paccnt as pp on (acc.id = pp.id_paccnt)
left join prs_runner_sectors as ps on (ps.id = pp.id_sector)
) as ss
  $qWhere Order by $sidx $sord LIMIT $limit OFFSET $start ";
}
    
//$ff=fopen('aaa_sql','w');
//fputs($ff,$SQL);
//fclose($ff);

//throw new Exception(json_encode($SQL));

$result = pg_query($Link,$SQL) or die("SQL Error: " .pg_last_error($Link) );
 if (!$result) { print("<br> no records found");}
 else {
  $data['page'] = $page;
  $data['total'] = $total_pages;
  $data['records'] = $count;

  $i = 0; 
 while($row = pg_fetch_array($result)) {
      $data['rows'][$i]['cell'][] = $row['id'];
      
      $data['rows'][$i]['cell'][] = $row['book'];
      $data['rows'][$i]['cell'][] = $row['code'];
      $data['rows'][$i]['cell'][] = $row['town'];
      $data['rows'][$i]['cell'][] = $row['street'];
      $data['rows'][$i]['cell'][] = $row['house'];
      
      if($flag_cek)
         $data['rows'][$i]['cell'][] = $row['house_letter'];
      
      $data['rows'][$i]['cell'][] = $row['slash'];
      $data['rows'][$i]['cell'][] = $row['korp'];
      $data['rows'][$i]['cell'][] = $row['flat'];
      $data['rows'][$i]['cell'][] = $row['f_slash'];

      $data['rows'][$i]['cell'][] = $row['abon'];     
      $data['rows'][$i]['cell'][] = $row['sector'];     
    $i++;
 } 
}


header("Content-type: application/json;charset=utf-8");
echo json_encode($data);

?>

<?php
// Функция нормализации № телефона [ЦЭК]
function normal_flat($flat){
     $result = is_numeric($flat);
     if(!$result)
        $result = 0;
     else
        $result = intval($flat);
     
     return $result;
}
?>