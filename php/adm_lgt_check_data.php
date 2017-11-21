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

if ($sidx=='ident')  $sidx="ident $sord, book $sord, int_code $sord, dt_oper $sord, note";
if ($sidx=='code')  $sidx="book $sord, int_code $sord, dt_oper $sord, note";
if ($sidx=='dt_oper')  $sidx="dt_oper $sord, book $sord, int_code $sord, note";

$p_mmgg = sql_field_val('p_mmgg', 'date');

$QE = "select lgm_cancel_sel_fun($p_mmgg)";

$result = pg_query($Link, $QE);
if (!($result)) {
    echo_result(2, pg_last_error($Link));
    //pg_query($Link, "rollback");
    return;
}


//$fildsArray =DbGetFieldsArray($Link,$table_name);
$fildsArray['id'] =   array('f_name'=>'id','f_type'=>'int');
$fildsArray['book'] =   array('f_name'=>'book','f_type'=>'char');
$fildsArray['code'] =   array('f_name'=>'code','f_type'=>'char');
$fildsArray['addr'] =   array('f_name'=>'addr','f_type'=>'character varying');
$fildsArray['ident'] =   array('f_name'=>'ident','f_type'=>'character varying');
$fildsArray['alt_code'] =   array('f_name'=>'alt_code','f_type'=>'character varying');
$fildsArray['grp_lgt'] =   array('f_name'=>'grp_lgt','f_type'=>'character varying');
$fildsArray['s_doc'] =   array('f_name'=>'s_doc','f_type'=>'character varying');
$fildsArray['n_doc'] =   array('f_name'=>'n_doc','f_type'=>'character varying');
$fildsArray['dt_doc'] =   array('f_name'=>'dt_doc','f_type'=>'date');
$fildsArray['dt_doc_end'] =   array('f_name'=>'dt_doc_end','f_type'=>'date');

$fildsArray['dt_birth'] =   array('f_name'=>'dt_birth','f_type'=>'date');
$fildsArray['dt_child_end'] =   array('f_name'=>'dt_child_end','f_type'=>'date');

$fildsArray['dt_start'] =   array('f_name'=>'dt_start','f_type'=>'date');
$fildsArray['dt_end'] =   array('f_name'=>'dt_end','f_type'=>'date');

$fildsArray['family_cnt'] =   array('f_name'=>'family_cnt','f_type'=>'integer');
$fildsArray['family_cnt_new'] =   array('f_name'=>'family_cnt_new','f_type'=>'integer');
$fildsArray['dt_oper'] =   array('f_name'=>'dt_oper','f_type'=>'date');
$fildsArray['note'] =   array('f_name'=>'note','f_type'=>'character varying');

$qWhere= DbBuildWhere($_POST,$fildsArray);


$Query="SELECT COUNT(*) AS count FROM 
(select t.id,acc.book, acc.code,  
(adr.adr||' '||
   (coalesce('буд.'||(acc.addr).house||'','')||
		coalesce('/'||(acc.addr).slash||' ','')||
			coalesce(' корп.'||(acc.addr).korp||'','')||
				coalesce(', кв. '||(acc.addr).flat,'')||
					coalesce('/'||(acc.addr).f_slash,''))::varchar
)::varchar as addr,
 (c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon,
lg.name as grp_lgt,lg.ident, lg.alt_code,
t.dt_doc, t.s_doc,t.n_doc, t.dt_doc_end,t.family_cnt, t.family_cnt_new, t.dt_oper, t.note,
t.dt_birth,t.dt_child_end, l.dt_start, l.dt_end, 
regexp_replace(regexp_replace(acc.code, '-.*?$', '') , '[^0-9]', '','g')::int as int_code

from lgm_cancel_tmp as t
join clm_paccnt_tbl as acc on (acc.id = t.id_paccnt)
join clm_abon_tbl as c on (c.id = acc.id_abon) 
left join lgm_abon_tbl as l on (l.id = t.id_lgt)
left join lgi_group_tbl as lg on (lg.id = t.id_grp_lgt) 
left join adt_addr_tbl as adr on (adr.id = (acc.addr).id_class)
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

$SQL = "select * from (select t.id,acc.book, acc.code,  
(adr.adr||' '||
   (coalesce('буд.'||(acc.addr).house||'','')||
		coalesce('/'||(acc.addr).slash||' ','')||
			coalesce(' корп.'||(acc.addr).korp||'','')||
				coalesce(', кв. '||(acc.addr).flat,'')||
					coalesce('/'||(acc.addr).f_slash,''))::varchar
)::varchar as addr,
 (c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon,
lg.name as grp_lgt,lg.ident, lg.alt_code,
t.dt_doc, t.s_doc,t.n_doc, t.dt_doc_end,t.family_cnt, t.family_cnt_new, t.dt_oper, t.note,
t.dt_birth,t.dt_child_end, l.dt_start, l.dt_end, 
regexp_replace(regexp_replace(acc.code, '-.*?$', '') , '[^0-9]', '','g')::int as int_code

from lgm_cancel_tmp as t
join clm_paccnt_tbl as acc on (acc.id = t.id_paccnt)
join clm_abon_tbl as c on (c.id = acc.id_abon) 
left join lgm_abon_tbl as l on (l.id = t.id_lgt)
left join lgi_group_tbl as lg on (lg.id = t.id_grp_lgt) 
left join adt_addr_tbl as adr on (adr.id = (acc.addr).id_class)
) as ss
  $qWhere order by $sidx $sord LIMIT $limit OFFSET $start ";

//throw new Exception(json_encode($SQL));

$result = pg_query($Link,$SQL) or die("SQL Error: " .pg_last_error($Link) );
 if (!$result) { print("<br> no records found");}
 else {
  $data['page'] = $page;
  $data['total'] = $total_pages;
  $data['records'] = $count;

  $i = 0; 
  
    while($row = pg_fetch_array($result)) {

        foreach ($fildsArray as $fild) {
             $data['rows'][$i][$fild['f_name']] = $row[$fild['f_name']];
        }

        $i++;
     } 

  
}


header("Content-type: application/json;charset=utf-8");
echo json_encode($data);

?>