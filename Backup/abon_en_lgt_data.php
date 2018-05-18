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

if ($sidx=='code')  $sidx="int_book ASC, book ASC ,int_code $sord ,code";
if ($sidx=='book')  $sidx="int_book $sord,book $sord,int_code ASC,code";

// connect to the database
$fildsArray =DbGetFieldsArray($Link,'lgm_abon_load_tbl');
$fildsArray['book'] = array('f_name' => 'book', 'f_type' => 'char');
$fildsArray['code'] = array('f_name' => 'code', 'f_type' => 'char');
$fildsArray['addr'] = array('f_name' => 'addr', 'f_type' => 'character varying');
$fildsArray['town'] = array('f_name' => 'town', 'f_type' => 'character varying');
$fildsArray['abon'] = array('f_name' => 'abon', 'f_type' => 'character varying');
$fildsArray['name_lgt'] = array('f_name' => 'name_lgt', 'f_type' => 'character varying');
$fildsArray['index_town'] = array('f_name' => 'index_town', 'f_type' => 'character varying');

$fildsArray['date_b_txt'] = array('f_name' => 'date_b_txt', 'f_type' => 'character varying');
$fildsArray['date_e_txt'] = array('f_name' => 'date_e_txt', 'f_type' => 'character varying');

$qWhere= DbBuildWhere($_POST,$fildsArray);

$p_file = sql_field_val('p_file', 'int');
  
if ($qWhere!='') $qWhere=$qWhere.' and ';
else $qWhere=' where ';
 
$qWhere=$qWhere." id_file = $p_file "; 

$Query="SELECT COUNT(*) AS count FROM 
(
 select ld.*, acc.book, acc.code, lg.name as name_lgt, coalesce(s.name, text(ld.street_code))::varchar as street,
('0'||substring(acc.code FROM '[0-9]+'))::int as int_code,
('0'||substring(acc.book FROM '[0-9]+'))::int as int_book,
 (adr.street||' '||
   (coalesce('буд.'||(acc.addr).house||'','')||
		coalesce('/'||(acc.addr).slash||' ','')||
			coalesce(' корп.'||(acc.addr).korp||'','')||
				coalesce(', кв. '||(acc.addr).flat,'')||
					coalesce('/'||(acc.addr).f_slash||' ',''))::varchar
)::varchar as addr, adr.town, tt.index_town,
 (c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon,
to_char(ld.date_b, 'DD.MM.YYYY') as date_b_txt,
to_char(ld.date_e, 'DD.MM.YYYY') as date_e_txt
from lgm_abon_load_tbl as ld
left join clm_paccnt_tbl as acc on (ld.id_paccnt = acc.id)
left join clm_abon_tbl as c on (c.id = acc.id_abon) 
left join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class) 
left join lgi_group_tbl as lg on (lg.id = ld.id_lgt)
left join lgk_street_upszn_tbl as s on (s.id = ld.street_code)
left join 
(
 select cc.indx, 
 (COALESCE(k.short_prefix::text || ' '::text, ''::text) || cc.name::text) || COALESCE(' '::text || k.short_postfix::text, ''::text) as index_town
 from
 (
  select c.indx, min(c.id) as id from 
  (
   select indx, min(idk_class) as idk from adi_class_tbl
    group by indx
   ) as k
   join adi_class_tbl as c on (c.indx = k.indx and c.idk_class = k.idk)
   group by c.indx
 ) as aa
 join adi_class_tbl as cc on (cc.id = aa.id)
 JOIN adk_class_tbl as k ON k.id = cc.idk_class
) as tt on (tt.indx = ld.indx )

) as ss $qWhere;";
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

$SQL = "select * from (
 select ld.*, acc.book, acc.code, lg.name as name_lgt, coalesce(s.name, text(ld.street_code))::varchar as street,
('0'||substring(acc.code FROM '[0-9]+'))::int as int_code,
('0'||substring(acc.book FROM '[0-9]+'))::int as int_book,
 (adr.street||' '||
   (coalesce('буд.'||(acc.addr).house||'','')||
		coalesce('/'||(acc.addr).slash||' ','')||
			coalesce(' корп.'||(acc.addr).korp||'','')||
				coalesce(', кв. '||(acc.addr).flat,'')||
					coalesce('/'||(acc.addr).f_slash||' ',''))::varchar
)::varchar as addr, adr.town, tt.index_town,
 (c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon,
to_char(ld.date_b, 'DD.MM.YYYY') as date_b_txt,
to_char(ld.date_e, 'DD.MM.YYYY') as date_e_txt
from lgm_abon_load_tbl as ld
left join clm_paccnt_tbl as acc on (ld.id_paccnt = acc.id)
left join clm_abon_tbl as c on (c.id = acc.id_abon) 
left join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class)
left join lgi_group_tbl as lg on (lg.id = ld.id_lgt)
left join lgk_street_upszn_tbl as s on (s.id = ld.street_code)
left join 
(
 select cc.indx,  
 (COALESCE(k.short_prefix::text || ' '::text, ''::text) || cc.name::text) || COALESCE(' '::text || k.short_postfix::text, ''::text) as index_town
 from
 (
  select c.indx, min(c.id) as id from 
  (
   select indx, min(idk_class) as idk from adi_class_tbl
    group by indx
   ) as k
   join adi_class_tbl as c on (c.indx = k.indx and c.idk_class = k.idk)
   group by c.indx
 ) as aa
 join adi_class_tbl as cc on (cc.id = aa.id)
 JOIN adk_class_tbl as k ON k.id = cc.idk_class

 ) as tt on (tt.indx = ld.indx )
) as ss
 $qWhere
       Order by $sidx $sord LIMIT $limit OFFSET $start ";

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
      $data['rows'][$i]['cell'][] = $row['id_file']; 
      
      $data['rows'][$i]['cell'][] = $row['bookcod'];
      $data['rows'][$i]['cell'][] = $row['fio_lgt'];      

      $data['rows'][$i]['cell'][] = $row['status'];
      
      $data['rows'][$i]['cell'][] = $row['book'];
      $data['rows'][$i]['cell'][] = $row['code'];
      $data['rows'][$i]['cell'][] = $row['abon'];
      $data['rows'][$i]['cell'][] = $row['town'];
      $data['rows'][$i]['cell'][] = $row['addr'];
      
      $data['rows'][$i]['cell'][] = $row['code_lgt'];
      $data['rows'][$i]['cell'][] = $row['id_lgt'];
      $data['rows'][$i]['cell'][] = $row['name_lgt'];
     

      $data['rows'][$i]['cell'][] = $row['indx'];
      $data['rows'][$i]['cell'][] = $row['index_town'];
      $data['rows'][$i]['cell'][] = $row['street'];      
      
      $data['rows'][$i]['cell'][] = $row['house'];
      $data['rows'][$i]['cell'][] = $row['korp '];
      $data['rows'][$i]['cell'][] = $row['flat'];

      $data['rows'][$i]['cell'][] = $row['ident_cod_l'];      
      $data['rows'][$i]['cell'][] = $row['n_doc'];
      
      
      $data['rows'][$i]['cell'][] = $row['date_b_txt'];
      $data['rows'][$i]['cell'][] = $row['date_e_txt'];
      $data['rows'][$i]['cell'][] = $row['work_period'];
      

    $i++;
 } 
}


header("Content-type: application/json;charset=utf-8");
echo json_encode($data);

?>