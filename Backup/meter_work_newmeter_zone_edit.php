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

    if (isset($_POST['oper'])) {
        $oper = $_POST['oper'];
    }

    $p_id = sql_field_val('id', 'int');
    $p_id_paccnt = sql_field_val('id_paccnt', 'int');        
    $p_id_meter = sql_field_val('id_meter', 'int');
    $p_id_zone = sql_field_val('id_zone', 'int');
    $p_dt_start = sql_field_val('dt_start', 'date');
    $p_id_session= sql_field_val('id_session', 'int');
    $p_id_work= sql_field_val('id_work', 'int');
    $p_indic= sql_field_val('indic', 'int');
    $p_id_usr = $session_user; // где взять текущего юзера?    
    
    
//------------------------------------------------------
    if ($oper == "add") {


        $QE = "INSERT INTO clm_meter_zone_tmp(id_work, id_session,id_meter, id_zone, kind_energy, indic)
            VALUES ($p_id_work, $p_id_session, $p_id_meter, $p_id_zone, 10,$p_indic);";

        $res_e = pg_query($Link, $QE);
        if ($res_e) {
            echo_result(-1, 'Data ins');
        } else {
            echo_result(2, pg_last_error($Link));
        }
    }
//------------------------------------------------------
    if ($oper == "edit") {


        $QE = "update clm_meter_zone_tmp set id_zone = $p_id_zone,  indic = $p_indic
               where id = $p_id and id_session = $p_id_session ;";

        $res_e = pg_query($Link, $QE);
        if ($res_e) {
            echo_result(-1, 'Data upd');
        } else {
            echo_result(2, pg_last_error($Link));
        }
    }
//------------------------------------------------------

    if ($oper == "del") {


        $QE = "Delete from clm_meter_zone_tmp 
                       where id = $p_id and id_session = $p_id_session ;";
        $res_e = pg_query($Link, $QE);

        if ($res_e) {
            echo_result(-1, 'Data delated');
        } else {
            echo_result(2, pg_last_error($Link));
        }
    }
} catch (Exception $e) {

    echo echo_result(1, 'Error: ' . $e->getMessage());
}
?>