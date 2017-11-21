<?php
header("Content-type: application/json;charset=utf-8");

require 'abon_en_func.php';
require 'abon_ded_func.php';

session_name("session_kaa");
session_start();

error_reporting(1);

$Link = get_database_link($_SESSION,1);
$session_user = $_SESSION['ses_usr_id'];
session_write_close();

$id_work = $_POST['id_work']; 
$id_paccnt =$_POST['id_paccnt'];
$id_meter=$_POST['id_meter'];
$idk_work=$_POST['idk_work'];
$id_session = $_POST['id_session'];
$mode = $_POST['mode'];

$SQL = "delete from clm_meter_zone_tmp where (id_session = $id_session and id_work =$id_work )  
  or (dt_input::date+'1 days'::interval < now());";
$result = pg_query($Link,$SQL);
if (!($result)) 
{
     echo_result(2,pg_last_error($Link));
     return;
}

$SQL = "delete from clm_works_lock_tbl where id_paccnt = $id_paccnt and id_person = $session_user; ";
$result = pg_query($Link,$SQL);
if (!($result)) 
{
     echo_result(2,pg_last_error($Link));
     return;
}


if ($mode==0)
{
    
    if (($idk_work==2)&&($id_meter!=0))
    {
    $SQL = "select acc.id as id_paccnt, acc.book, acc.code, acc.id_abon, acc.addr, -1 as newmeter_id,
     address_print_full(acc.addr,4) as addr_str,
     (c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon,
      mp.id_station, mp.code_eqp, mp.power, mp.calc_losts, mp.id_extra,
      mp.smart, mp.magnet, mp.id_type_meter, '' as num_meter, mp.carry, 
      to_char(mp.dt_control, 'DD.MM.YYYY') as dt_control,
       mp.id_typecompa, to_char(mp.dt_control_ca, 'DD.MM.YYYY') as dt_control_ca, 
        mp.id_typecompu,to_char(mp.dt_control_cu, 'DD.MM.YYYY') as dt_control_cu,
        mp.coef_comp, 
        ca.name as typecompa,  cu.name as typecompu, m.name as type_meter, tp.name as station,
        to_char(fun_mmgg(), 'DD.MM.YYYY') as work_period
      from clm_paccnt_tbl as acc 
      join clm_abon_tbl as c on (c.id = acc.id_abon) 
      join clm_meterpoint_tbl as mp on (mp.id_paccnt = acc.id)
      left join eqi_meter_tbl as m on (m.id = mp.id_type_meter)
      left join eqi_compensator_i_tbl as ca on (ca.id = mp.id_typecompa)
      left join eqi_compensator_i_tbl as cu on (cu.id = mp.id_typecompu)
      left join eqm_tp_tbl as tp on (tp.id = mp.id_station)
      where acc.id = $id_paccnt and mp.id = $id_meter ;";
    }
    else
    {
     $SQL = "select acc.id as id_paccnt, acc.book, acc.code, acc.id_abon, acc.addr, 
     address_print_full(acc.addr,4) as addr_str,
     (c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon,
     to_char(fun_mmgg(), 'DD.MM.YYYY') as work_period
     from clm_paccnt_tbl as acc 
     join clm_abon_tbl as c on (c.id = acc.id_abon) 
     where acc.id = $id_paccnt ;";
    }
}   

if ($mode==1)
{
    
    $SQL = "insert into clm_meter_zone_tmp (id_metzone,id_session, id_work, id_meter,id_work_indic, id_zone, kind_energy,indic )
            select i.id_metzone, $id_session,i.id_work,i.id_meter, i.id, i.id_zone, i.kind_energy, i.indic
             from clm_work_indications_tbl as i 
            where i.id_work =$id_work  and  i.idk_oper = 1;";
        
    $result = pg_query($Link,$SQL);

    if (!($result)) 
    {
         echo_result(2,pg_last_error($Link));
         return;
    }
    
    
    if (($idk_work==2)||($idk_work==1))    
    {
        
      $SQL = "select w.id,w.id_paccnt,w.idk_work,w.id_position,w.note, w.dt_input, 
      acc.book, acc.code, acc.addr, 
      address_print_full(acc.addr,4) as addr_str,
      (c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon,
      cn.represent_name as position,
      to_char(w.dt_work, 'DD.MM.YYYY') as dt_work, w.act_num,
      to_char(w.work_period, 'DD.MM.YYYY') as work_period, mmm.*
      from clm_works_tbl as w 
      join clm_paccnt_tbl as acc on(w.id_paccnt = acc.id)
      left join clm_abon_tbl as c on (c.id = acc.id_abon) 
      left join prs_persons as cn on (cn.id = w.id_position)     
      left join (
        select  si.id_work, si.id_meter as newmeter_id ,mp.id_station, mp.code_eqp, mp.power, mp.calc_losts, mp.id_extra,
        mp.smart, mp.magnet, mp.id_type_meter, mp.num_meter, mp.carry, 
        to_char(mp.dt_control, 'DD.MM.YYYY') as dt_control,
        mp.id_typecompa, to_char(mp.dt_control_ca, 'DD.MM.YYYY') as dt_control_ca, 
        mp.id_typecompu,to_char(mp.dt_control_cu, 'DD.MM.YYYY') as dt_control_cu,
        mp.coef_comp, 
        ca.name as typecompa,  cu.name as typecompu, m.name as type_meter, tp.name as station 
        from 
        (select distinct id_work, id_meter, id_history 
         from clm_work_indications_tbl 
         where id_work =$id_work  and  idk_oper = 1 
        ) as si
        join clm_meterpoint_h as mp on (mp.id_key = si.id_history)
        left join eqi_meter_tbl as m on (m.id = mp.id_type_meter)
        left join eqi_compensator_i_tbl as ca on (ca.id = mp.id_typecompa)
        left join eqi_compensator_i_tbl as cu on (cu.id = mp.id_typecompu)
        left join eqm_tp_tbl as tp on (tp.id = mp.id_station)
      ) as mmm on (mmm.id_work = w.id) 
      where w.id = $id_work ;";
        
    }
    else
    {
      $SQL = "select w.id,w.id_paccnt,w.idk_work,w.id_position,w.note, w.dt_input, w.act_num,
      acc.book, acc.code, acc.addr, 
      address_print_full(acc.addr,4) as addr_str,
      (c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon,
      cn.represent_name as position,
      to_char(w.dt_work, 'DD.MM.YYYY') as dt_work,
      to_char(w.work_period, 'DD.MM.YYYY') as work_period        
      from clm_works_tbl as w 
      join clm_paccnt_tbl as acc on(w.id_paccnt = acc.id)
      left join clm_abon_tbl as c on (c.id = acc.id_abon) 
      left join prs_persons as cn on (cn.id = w.id_position)     
      where w.id = $id_work ;";
    }
}   

$result = pg_query($Link,$SQL);

 if ($result) {
    $row = pg_fetch_array($result);
    echo json_encode($row);
 }
 else
 {
     echo_result(2,pg_last_error($Link));
 }


?>
