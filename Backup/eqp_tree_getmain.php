<?php
header("Content-type: application/json;charset=utf-8");

require 'abon_en_func.php';
require 'abon_ded_func.php';

session_name("session_kaa");
session_start();

error_reporting(1);

$Link = get_database_link($_SESSION);
$session_user = $_SESSION['ses_usr_id'];
session_write_close();

$id = $_POST['id']; 
$eqp_type = $_POST['eqp_type']; 
//$id_point = $_POST['id_point']; 

if ($id == "new_eqp") {
    $SQL = "select 0 as id, dk.id as type_eqp, null as num_eqp, null as name_eqp, null as addr, 
    null as id_paccnt, null as dt_install, 
    null as dt_change, null as loss_power, null as id_dep, null as is_owner, 
    dk.name as typename, dk.calc_lost,
    null as dt_install_str,
    null as dt_change_str,
    0 as id_mp,
    dk.edit_form, 0 as lvl
    from eqi_device_kinds_tbl as dk where dk.id = $eqp_type;
";
} else {
/*
    if (($id == "new_eqp+") && ($eqp_type == 12)) {
        
    $SQL = "select 0 as id, dk.id as type_eqp, mmm.num_meter as num_eqp, null as name_eqp, null as addr, 
    null as id_paccnt, mmm.dt_b as dt_install, 
    mmm.dt_b as dt_change, null as loss_power, null as id_dep, null as is_owner, 
    dk.name as typename, dk.calc_lost,
    mmm.dt_b_str as dt_install_str,
    mmm.dt_b_str as dt_change_str,
    dk.edit_form, mmm.*
    from eqi_device_kinds_tbl as dk,
    (select 
       mp.id as id_mp,mp.id_station, mp.power, mp.calc_losts, mp.id_extra, 
       mp.smart, mp.magnet, mp.id_type_meter, mp.num_meter, mp.carry, to_char(mp.dt_control, 'DD.MM.YYYY') as dt_control, 
       mp.id_typecompa, to_char(mp.dt_control_ca, 'DD.MM.YYYY') as dt_control_ca, 
       mp.id_typecompu, to_char(mp.dt_control_cu, 'DD.MM.YYYY') as dt_control_cu,
       to_char(mp.dt_b, 'DD.MM.YYYY') as dt_b_str,mp.dt_b,
       ca.name as typecompa,  cu.name as typecompu, mp.coef_comp, m.name as type_meter,
       tp.name as station 
        from 
        clm_meterpoint_tbl as mp 
        left join eqi_meter_tbl as m on (m.id = mp.id_type_meter)
        left join eqi_compensator_i_tbl as ca on (ca.id = mp.id_typecompa)
        left join eqi_compensator_i_tbl as cu on (cu.id = mp.id_typecompu)
        left join eqm_tp_tbl as tp on (tp.id = mp.id_station)
        where mp.id = $id_point) as mmm
    where dk.id = $eqp_type;
    ";
    } else { 
*/
    switch ($eqp_type) {
            case 2: // compensator
                $SQL = "select eq.*, address_print_full(eq.addr,4) as addr_str,
        dk.name as typename, dk.calc_lost,
        to_char(eq.dt_install, 'DD.MM.YYYY') as dt_install_str,
        to_char(eq.dt_change, 'DD.MM.YYYY') as dt_change_str, dk.edit_form,
        c.id_type_eqp as id_type_compens, i.name as type_compens
        from eqm_equipment_tbl as eq 
        join eqi_device_kinds_tbl as dk on (dk.id = eq.type_eqp)
        left join eqm_compensator_tbl as c on (c.code_eqp = eq.id)
        left join eqi_compensator_tbl as i on (i.id = c.id_type_eqp )
        where eq.id = $id";
                break;
            case 3: //switch
                $SQL = "select eq.*, address_print_full(eq.addr,4) as addr_str,
                dk.name as typename, dk.calc_lost,
        to_char(eq.dt_install, 'DD.MM.YYYY') as dt_install_str,
        to_char(eq.dt_change, 'DD.MM.YYYY') as dt_change_str, dk.edit_form,
        sw.id_type_eqp as id_type_switch, i.name as type_switch
        from eqm_equipment_tbl as eq 
        join eqi_device_kinds_tbl as dk on (dk.id = eq.type_eqp)
        left join eqm_switch_tbl as sw on (sw.code_eqp = eq.id)
        left join eqi_switch_tbl as i on (i.id = sw.id_type_eqp )        
        where eq.id = $id";
                break;
            case 5: //fuse
                $SQL = "select eq.*, address_print_full(eq.addr,4) as addr_str,
                dk.name as typename, dk.calc_lost,
        to_char(eq.dt_install, 'DD.MM.YYYY') as dt_install_str,
        to_char(eq.dt_change, 'DD.MM.YYYY') as dt_change_str, dk.edit_form,
        f.id_type_eqp as id_type_fuse, i.name as type_fuse
        from eqm_equipment_tbl as eq 
        join eqi_device_kinds_tbl as dk on (dk.id = eq.type_eqp)
        left join eqm_fuse_tbl as f on (f.code_eqp = eq.id)
        left join eqi_fuse_tbl as i on (i.id = f.id_type_eqp )        
        where eq.id = $id";
                break;

            case 6: //cable
                $SQL = "select eq.*, address_print_full(eq.addr,4) as addr_str,
                dk.name as typename, dk.calc_lost,
        to_char(eq.dt_install, 'DD.MM.YYYY') as dt_install_str,
        to_char(eq.dt_change, 'DD.MM.YYYY') as dt_change_str, dk.edit_form,
        lc.id_type_eqp as id_type_cable,lc.length as length_cable, lc.id_voltage as id_voltage_cable,
        i.name as type_cable
        from eqm_equipment_tbl as eq 
        join eqi_device_kinds_tbl as dk on (dk.id = eq.type_eqp)
        left join eqm_line_c_tbl as lc on (lc.code_eqp = eq.id)
        left join eqi_cable_tbl as i on (i.id = lc.id_type_eqp )        
        where eq.id = $id";
                break;
            case 7: //air
                $SQL = "select eq.*, address_print_full(eq.addr,4) as addr_str,
                dk.name as typename, dk.calc_lost,
        to_char(eq.dt_install, 'DD.MM.YYYY') as dt_install_str,
        to_char(eq.dt_change, 'DD.MM.YYYY') as dt_change_str, dk.edit_form,
        la.id_type_eqp as id_type_corde,la.length as length_corde, la.id_voltage as id_voltage_corde,
        la.id_pillar, i.name as type_corde
        from eqm_equipment_tbl as eq 
        join eqi_device_kinds_tbl as dk on (dk.id = eq.type_eqp)
        left join eqm_line_a_tbl as la on (la.code_eqp = eq.id)
        left join eqi_corde_tbl as i on (i.id = la.id_type_eqp )        
        where eq.id = $id";
                break;
            case 12: //point
                $SQL = "select eq.*, address_print_full(eq.addr,4) as addr_str,
                dk.name as typename, dk.calc_lost,
        to_char(eq.dt_install, 'DD.MM.YYYY') as dt_install_str,
        to_char(eq.dt_change, 'DD.MM.YYYY') as dt_change_str, dk.edit_form,
       mp.id as id_mp,mp.id_station, mp.power, mp.calc_losts, mp.id_extra, 
       mp.smart, mp.magnet, mp.id_type_meter, mp.num_meter, mp.carry, to_char(mp.dt_control, 'DD.MM.YYYY') as dt_control, 
       mp.id_typecompa, to_char(mp.dt_control_ca, 'DD.MM.YYYY') as dt_control_ca, 
       mp.id_typecompu, to_char(mp.dt_control_cu, 'DD.MM.YYYY') as dt_control_cu,
       ca.name as typecompa,  cu.name as typecompu, mp.coef_comp, m.name as type_meter,
       tp.name as station 
        from eqm_equipment_tbl as eq 
        join eqi_device_kinds_tbl as dk on (dk.id = eq.type_eqp)
        left join clm_meterpoint_tbl as mp on (mp.code_eqp = eq.id)
        left join eqi_meter_tbl as m on (m.id = mp.id_type_meter)
        left join eqi_compensator_i_tbl as ca on (ca.id = mp.id_typecompa)
        left join eqi_compensator_i_tbl as cu on (cu.id = mp.id_typecompu)
        left join eqm_tp_tbl as tp on (tp.id = mp.id_station)
        where eq.id = $id";
                break;
        }
 //   }
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
