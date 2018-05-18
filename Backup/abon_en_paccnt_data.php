<?php
header("Content-type: application/json;charset=utf-8");

require 'abon_en_func.php';
require 'abon_ded_func.php';

session_name("session_kaa");
session_start();

$Link = get_database_link($_SESSION,1);
$session_user = $_SESSION['ses_usr_id'];
session_write_close();

error_reporting(1);

$id = $_POST['id']; 

if ($id !=0) 
{
$SQL = "select acc.id, acc.book, acc.code, acc.id_abon, acc.addr, acc.archive,acc.id_dep, subs_name, 
    to_char(CASE WHEN acc.archive =1 THEN (select min(dt_b) from clm_paccnt_h where archive =1 and id = $id ) ELSE null END ,'DD.MM.YYYY') as dt_archive,
    address_print_full(acc.addr,4) as addr_str,
    acc.note, acc.recalc_subs, 
    coalesce(task_name||' '||task_dt||';','')||' '||coalesce(sw_name||' '||sw_dt,'') as sw_info,
 (c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon,
  acc.id_cntrl, cn.name as cntrl, acc.id_agreem, acc.id_gtar, tar.sh_nm as gtar,
  acc.n_subs , acc.activ, acc.rem_worker, acc.not_live,acc.idk_house, acc.pers_cntrl ,acc.green_tarif,
  to_char(acc.dt_b, 'DD.MM.YYYY') as dt_b, acc.dt_input, acc.heat_area,
  to_char(acc.dt_dod, 'DD.MM.YYYY') as dt_dod,
  sector.id_sector, 
  coalesce((sector.code||': ')::varchar,'')||coalesce(sector.name,'')||coalesce('('||sector.represent_name||coalesce('/'||sector.operator,'')||')','')::varchar as sector_info,
  'Субсидія: '||subs_dt_b||'-'||subs_dt_e as subs_info ,
  coalesce( round(sal.e_val,2), 0.00) as  e_val
from clm_paccnt_tbl as acc 
left join clm_abon_tbl as c on (c.id = acc.id_abon) 
left join cli_addparam_tbl as cn on (cn.id = acc.id_cntrl)     
left join aqi_grptar_tbl as tar on (tar.id = acc.id_gtar)   
left join acm_saldo_tbl as sal on (sal.id_paccnt = acc.id and sal.id_pref = 10 and sal.mmgg =  fun_mmgg())
left join (
       select csw.id_paccnt, to_char(coalesce(csw.dt_action,csw.dt_create), 'DD.MM.YYYY') as sw_dt , csw.action, a.name as sw_name
       from clm_switching_tbl as csw 
        join cli_switch_action_tbl as a on (a.id = csw.action )
        join (select id_paccnt, max(dt_action) as maxdt from clm_switching_tbl  group by id_paccnt) as csdt 
       on (csw.id_paccnt = csdt.id_paccnt and csw.dt_action = csdt.maxdt) 
       left join clm_switching_tbl as cswc on (csw.id_paccnt = cswc.id_paccnt and csw.dt_action = cswc.dt_action and cswc.action in (3,4)) 
       where csw.action not in (3,4) and cswc.id_paccnt is null and csw.id_paccnt = $id
) as sw on (sw.id_paccnt = acc.id) 
left join (
       select cst.id_paccnt, to_char(coalesce(cst.date_work,cst.date_print), 'DD.MM.YYYY') as task_dt , 'Завдання на відкл.'::varchar as task_name
       from clm_tasks_tbl as cst 
       where cst.idk_work =1 and cst.task_state = 1 and cst.id_paccnt = $id
) as st on (st.id_paccnt = acc.id) 
left join
(
  select rp.id_sector, prs.represent_name , rs.name, rp.id_paccnt, rs.code, 
    pr2.represent_name as operator
    from prs_runner_paccnt as rp 
    join prs_runner_sectors as rs on (rs.id = rp.id_sector)
    left join prs_persons as prs on (prs.id = rs.id_runner)
    left join prs_persons as pr2 on (pr2.id = rs.id_operator)
    where rp.id_paccnt = $id
) as sector on (sector.id_paccnt = acc.id)
left join (
 select id_paccnt, to_char(s.dt_b, 'DD.MM.YYYY') as subs_dt_b, 
    to_char(s.dt_e, 'DD.MM.YYYY') as subs_dt_e
 from acm_subs_tbl as s 
 join (select max(mmgg) as mmgg from acm_subs_tbl ) as mm on (mm.mmgg = s.mmgg)
 where s.id_paccnt = $id
 limit 1   
) as sb on (sb.id_paccnt = acc.id)
left join
( select id_paccnt, param_value as subs_name 
     from cli_ext_params_tbl where id_paccnt = $id and id_param = 1 limit 1
 ) as sn on (sn.id_paccnt = acc.id)
 where acc.id = $id ;";
   
$result = pg_query($Link,$SQL);

 if ($result) {
    $row = pg_fetch_array($result);
    echo json_encode($row);
 }
 else
 {
     echo_result(2,pg_last_error($Link));
 }

}    
 else {
     echo_result(2,"Unknown id!");    
}




?>
