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

if ($sidx=='dt_end')  $sidx="dt_end $sord ,dt_start ";

$hist_mode = $_POST['hist_mode'];

 // get how many rows we want to have into the grid

if(!$sidx) $sidx =2;
if(!$limit) $limit = 500;
if(!$page) $page = 1;

// connect to the database
$fildsArray =DbGetFieldsArray($Link,'lgm_abon_tbl');

$fildsArray['grp_lgt'] =   array('f_name'=>'grp_lgt','f_type'=>'character varying');
$fildsArray['calc_name'] =   array('f_name'=>'calc_name','f_type'=>'character varying');
$fildsArray['ident'] =   array('f_name'=>'ident','f_type'=>'character varying');
$fildsArray['alt_code'] =   array('f_name'=>'alt_code','f_type'=>'integer');
$fildsArray['lgt_calc_info'] =   array('f_name'=>'lgt_calc_info','f_type'=>'character varying');
$fildsArray['edit_info'] =   array('f_name'=>'edit_info','f_type'=>'character varying');

$qWhere= DbBuildWhere($_POST,$fildsArray);

$pid_paccnt = $_POST['p_id'];
if ($qWhere!='') $qWhere=$qWhere.' and ';
else $qWhere=' where ';

$qWhere=$qWhere.' id_paccnt = '.$pid_paccnt;


if ($hist_mode==1)
{ 
 
 $SQL = "select * from (
  select lg.id, lg.id_paccnt, lg.id_grp_lgt, lg.id_calc, lg.prior_lgt, lg.fio_lgt, lg.family_cnt, lg.closed,
       lg.id_doc, lg.s_doc, lg.n_doc, lg.dt_doc,lg.dt_doc_end, lg.ident_cod_l, lg.dt_reg, lg.dt_start,
       CASE WHEN h1.dt_e = '2099-01-01' THEN null else h1.dt_e END as dt_end, lg.id_dep, lg.period_open as work_period, lg.dt_open as dt, 
       lg.id_person_open as id_person, i.ident, i.alt_code, '' as note,
    i.name as  grp_lgt, cl.name as calc_name, ''::varchar as lgt_calc_info, ''::varchar as edit_info
 from lgm_abon_h as lg
 join ( select id, min(dt_b) as dt_b, max(coalesce(dt_end,'2099-01-01')) as dt_e from lgm_abon_h 
      $qWhere group by id 
       order by id ) as h1 on (h1.id = lg.id and h1.dt_b = lg.dt_b)
 left join lgi_group_tbl as i on (i.id = lg.id_grp_lgt)
 left join lgi_calc_header_tbl as cl on (cl.id = lg.id_calc)
 ) as ss
  Order by dt_start";
    
}
else
{
 $SQL = "select * from (
  select lg.id, lg.id_paccnt, lg.id_grp_lgt, lg.id_calc, lg.prior_lgt, lg.fio_lgt, lg.family_cnt, lg.closed,
       lg.id_doc, lg.s_doc, lg.n_doc, lg.dt_doc,lg.dt_doc_end, lg.ident_cod_l, lg.dt_reg, lg.dt_start, 
       lg.dt_end, lg.id_dep, lg.work_period, lg.dt, lg.id_person , lg.note,
  i.name as  grp_lgt, cl.name as calc_name, i.ident, i.alt_code,
  ('Діючі норми : мін '||n.norm_min::varchar||' макс.'||n.norm_max::varchar)::varchar as lgt_calc_info,
    ed.edit_info
 from lgm_abon_tbl as lg
 join clm_paccnt_tbl as c on (c.id = lg.id_paccnt)
 join aqi_grptar_tbl as tar on (tar.id = c.id_gtar)
 join lgi_group_tbl as i on (i.id = lg.id_grp_lgt)
 left join lgi_calc_header_tbl as cl on (cl.id = lg.id_calc)
 left join lgi_norm_tbl as  n on (n.id_calc = i.id_calc and (n.id_tar_grp = tar.id_lgt_group or n.id_tar_grp is null))
 left join (
    select ch.id_lgt, ('останнє ред. '||to_char(date_change,'dd.mm.yyyy')||coalesce('; підстава: '||r.name,'')||'; час ред. '||to_char(dt,'dd.mm.yyyy HH24:MI'))::varchar as edit_info
    from lgm_changelog_tbl as ch
    join (select id_lgt, max(id_change) as max_change 
    from lgm_changelog_tbl where id_paccnt = $pid_paccnt group by id_lgt) as ch2 on (ch2.max_change = ch.id_change)
    left join lgi_change_reason_tbl as r on (r.id = ch.id_reason)
    where ch.id_paccnt = $pid_paccnt  ) as ed on (ed.id_lgt = lg.id)
 ) as ss
  $qWhere Order by $sidx $sord ;";
};

$result = pg_query($Link,$SQL) or die("SQL Error: " .pg_last_error($Link).$SQL );
 if (!$result) { print("<br> no records found");}
 else {
  $data['page'] = $page;
  $data['total'] = $total_pages;
  //$data['records'] = $count;

  $i = 0; 
 while($row = pg_fetch_array($result)) {

     foreach ($fildsArray as $fild) {
         $data['rows'][$i][$fild['f_name']] = $row[$fild['f_name']];
     }

    $i++;
 } 
 $count=$i;
 $data['records'] = $count;

}


header("Content-type: application/json;charset=utf-8");
echo json_encode($data);

?>