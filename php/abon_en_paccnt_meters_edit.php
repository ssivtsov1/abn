<?php

header('Content-type: text/html; charset=utf-8');

require 'abon_en_func.php';
require 'abon_ded_func.php';



session_name("session_kaa");
session_start();

error_reporting(0);

$Link = get_database_link($_SESSION,2);
$session_user = $_SESSION['ses_usr_id'];
session_write_close();

try {

    if (isset($_POST['submitButton'])) {
        $oper = $_POST['submitButton'];
    } else {
        if (isset($_POST['oper'])) {
            $oper = $_POST['oper'];
        }
    }
//------------------------------------------------------
    if ($oper == "edit") {

        $p_id_paccnt = sql_field_val('id_paccnt', 'int');
        $p_id_station = sql_field_val('id_station', 'int');
        $p_code_eqp = sql_field_val('code_eqp', 'int');
        $p_power = sql_field_val('power', 'numeric');
        $p_calc_losts = sql_field_val('calc_losts', 'int');
        $p_id_extra = sql_field_val('id_extra', 'int');
        $p_smart = sql_field_val('smart', 'int');
        $p_magnet = sql_field_val('magnet', 'int');
        $p_id_type_meter = sql_field_val('id_type_meter', 'int');
        $p_num_meter = sql_field_val('num_meter', 'string');
        $p_carry = sql_field_val('carry', 'int');
        $p_dt_control = sql_field_val('dt_control', 'date');
        $p_id_typecompa = sql_field_val('id_typecompa', 'int');
        $p_dt_control_ca = sql_field_val('dt_control_ca', 'date');
        $p_id_typecompu = sql_field_val('id_typecompu', 'int');
        $p_dt_control_cu = sql_field_val('dt_control_cu', 'date');
        $p_coef_comp = sql_field_val('coef_comp', 'int');
        $p_dt_start = sql_field_val('dt_b', 'date');
        //$p_dt_end = sql_field_val('dt_end', 'date');

        $p_id = sql_field_val('id', 'int');
        $p_change_date    = sql_field_val('change_date','date');
        $p_id_usr = $session_user; // где взять текущего юзера?    
        
        $result=pg_query($Link,"begin");
        if (!($result)) 
        {
            echo_result(2,pg_last_error($Link));
            return;
        }    

        $QE="select eqt_change_fun(2,$p_id_paccnt,null,$p_change_date,$p_id_usr,null,$p_id,1)";
 
        $result=pg_query($Link,$QE);
        if (!($result)) 
        {
            echo_result(2,pg_last_error($Link));
            pg_query($Link,"rollback");   
            return;
        }    
        
        
        $QE = "UPDATE clm_meterpoint_tbl
        SET id_station=$p_id_station, code_eqp=$p_code_eqp, power=$p_power, calc_losts=$p_calc_losts, 
        id_extra=$p_id_extra, smart=$p_smart, magnet=$p_magnet, id_type_meter=$p_id_type_meter, num_meter=$p_num_meter, 
        carry=$p_carry, dt_control=$p_dt_control, id_typecompa=$p_id_typecompa, dt_control_ca=$p_dt_control_ca, 
        id_typecompu=$p_id_typecompu, dt_control_cu=$p_dt_control_cu, coef_comp=$p_coef_comp,
        dt_b = $p_dt_start
        WHERE id = $p_id;";

        //echo json_encode(array('errMess'=>'Error: '));

        $res_e = pg_query($Link, $QE);
        if ($res_e) {
            echo_result(1, 'Data updated');
            pg_query($Link,"commit");            
        } else {
            echo_result(2, pg_last_error($Link));
            pg_query($Link,"rollback");               
        }
    }
//------------------------------------------------------
    if ($oper == "add") {

        $p_id_paccnt = sql_field_val('id_paccnt', 'int');
        $p_id_station = sql_field_val('id_station', 'int');
        $p_code_eqp = sql_field_val('code_eqp', 'int');
        $p_power = sql_field_val('power', 'numeric');
        $p_calc_losts = sql_field_val('calc_losts', 'int');
        $p_id_extra = sql_field_val('id_extra', 'int');
        $p_smart = sql_field_val('smart', 'int');
        $p_magnet = sql_field_val('magnet', 'int');
        $p_id_type_meter = sql_field_val('id_type_meter', 'int');
        $p_num_meter = sql_field_val('num_meter', 'string');
        $p_carry = sql_field_val('carry', 'int');
        $p_dt_control = sql_field_val('dt_control', 'date');
        $p_id_typecompa = sql_field_val('id_typecompa', 'int');
        $p_dt_control_ca = sql_field_val('dt_control_ca', 'date');
        $p_id_typecompu = sql_field_val('id_typecompu', 'int');
        $p_dt_control_cu = sql_field_val('dt_control_cu', 'date');
        $p_coef_comp = sql_field_val('coef_comp', 'int');

        $p_dt_start = sql_field_val('dt_b', 'date');
       // $p_dt_end = sql_field_val('dt_end', 'date');

        $p_id_usr = $session_user; // где взять текущего юзера?    

        $result=pg_query($Link,"begin"); 
        if (!($result)) 
        {
            echo_result(2,pg_last_error($Link));
            return;
        }    

        $QE="select eqt_change_fun(1,$p_id_paccnt,null,$p_dt_start,$p_id_usr,null,null,1)";
 
        $result=pg_query($Link,$QE);
        if (!($result)) 
        {
            echo_result(2,pg_last_error($Link).$QE);
            pg_query($Link,"rollback");   
            return;
        }    
        
//$nm_e=addcslashes($nm_e,"'");

        $QE = "INSERT INTO clm_meterpoint_tbl(
            id,id_paccnt, id_station, code_eqp, power, calc_losts, id_extra, 
            smart, magnet, id_type_meter, num_meter, carry, dt_control, id_typecompa, 
            dt_control_ca, id_typecompu, dt_control_cu, coef_comp,dt_b,id_person)
            VALUES (DEFAULT,$p_id_paccnt, $p_id_station, $p_code_eqp, $p_power, $p_calc_losts, $p_id_extra, 
            $p_smart, $p_magnet, $p_id_type_meter, $p_num_meter, $p_carry, $p_dt_control, $p_id_typecompa, 
            $p_dt_control_ca, $p_id_typecompu, $p_dt_control_cu, $p_coef_comp,$p_dt_start,$p_id_usr) returning id;";


        $res_e = pg_query($Link, $QE);

        if (!($res_e)) {
            echo_result(2, pg_last_error($Link).$QE);
            pg_query($Link,"rollback");   
            return;
        }

        $row = pg_fetch_array($res_e);
        $new_id_meter = $row["id"];

        $QE = " INSERT INTO clm_meter_zone_tbl(id_meter, id_zone, kind_energy,dt_b,id_person)
                 VALUES ($new_id_meter, 0, 10,$p_dt_start,$p_id_usr);";

        $res_e = pg_query($Link, $QE);

        if (!($res_e)) {
            echo_result(2, pg_last_error($Link));
            pg_query($Link,"rollback");   
            return;
        } else {
            echo_result(-1, 'Data ins');
            pg_query($Link,"commit");   
        }
    }
//------------------------------------------------------
    if ($oper == "del") {

        $p_id = sql_field_val('id', 'int');
        $p_id_paccnt = sql_field_val('id_paccnt', 'int');
        $p_change_date    = sql_field_val('change_date','date');
        $p_id_usr = $session_user; // где взять текущего юзера?    
        
        $result=pg_query($Link,"begin");
        if (!($result)) 
        {
            echo_result(2,pg_last_error($Link));
            return;
        }    

        $QE="select eqt_change_fun(3,$p_id_paccnt,null,$p_change_date,$p_id_usr,null,$p_id,1)";
 
        $result=pg_query($Link,$QE);
        if (!($result)) 
        {
            echo_result(2,pg_last_error($Link));
            pg_query($Link,"rollback");   
            return;
        }    
        
        
        $QE = "Delete from clm_meterpoint_tbl where id= $p_id;";

        $res_e = pg_query($Link, $QE);

        if ($res_e) {
            echo_result(-1, 'Data delated');
            pg_query($Link,"commit");   
        } else {
            echo_result(2, pg_last_error($Link));
            pg_query($Link,"rollback");   
        }
    }
} catch (Exception $e) {

    echo echo_result(1, 'Error: ' . $e->getMessage());
}
?>