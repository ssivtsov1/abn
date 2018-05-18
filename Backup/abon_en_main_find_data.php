<?php

header('Content-type: text/html; charset=utf-8');

require 'abon_en_func.php';
require 'abon_ded_func.php';



session_name("session_kaa");
session_start();

error_reporting(0);

$Link = get_database_link($_SESSION,1);
$session_user = $_SESSION['ses_usr_id'];
session_write_close();

try {

    if (isset($_POST['submitButton'])) {
        $oper = $_POST['submitButton'];
    }

//------------------------------------------------------
    if ($oper == "find") {

        $p_num_meter = sql_field_val('num_meter', 'string');
        $p_num_meter_hist = sql_field_val('num_meter_hist', 'string');
        $p_num_plomb = sql_field_val('num_plomb', 'string');
        $p_num_plomb_hist = sql_field_val('num_plomb_hist', 'string');
        $p_index_accnt = sql_field_val('index_accnt', 'int');
        $p_lgt_name = sql_field_val('lgt_name', 'string');
        $p_lgt_inn = sql_field_val('lgt_inn', 'string');
        
        $p_result_cnt = sql_field_val('result_cnt', 'int');
        
        //$p_house = sql_field_val('house_str', 'string');
        //$p_korp = sql_field_val('korp_str', 'string');

        $QE = '';
        if ($p_num_meter != 'null') {
            
            $QE = "select id_paccnt from clm_meterpoint_tbl where num_meter = $p_num_meter limit 1 offset $p_result_cnt ;";
        }

        if ($p_num_meter_hist != 'null') {
            
            $QE = "select id_paccnt from clm_meterpoint_h where num_meter = $p_num_meter_hist and dt_e is not null limit 1 offset $p_result_cnt ;";
        }

        if ($p_num_plomb != 'null') {
             
            $QE = "select id_paccnt from clm_plomb_tbl where trim(plomb_num) = $p_num_plomb and dt_off is null limit 1 offset $p_result_cnt ;";
        }

        if ($p_num_plomb_hist != 'null') {
            
            $QE = "select id_paccnt from clm_plomb_tbl where trim(plomb_num) = $p_num_plomb_hist and dt_off is not null limit 1 offset $p_result_cnt ;";
        }

        if ($p_index_accnt != 'null') {
            
            $QE = "select id as id_paccnt from clm_paccnt_tbl where index_accnt = $p_index_accnt limit 1 offset $p_result_cnt ;";
        }

        if ($p_lgt_name != 'null') {
            
            $QE = "select id_paccnt from lgm_abon_tbl where fio_lgt ~ $p_lgt_name limit 1 offset $p_result_cnt ;";
        }
        
        if ($p_lgt_inn != 'null') {
            
            $QE = "select id_paccnt from lgm_abon_tbl where ident_cod_l ~ $p_lgt_inn limit 1 offset $p_result_cnt ;";
        }
        
        if ($QE != '')
        {
            $res_e = pg_query($Link, $QE);
            $count = pg_num_rows($res_e);
 
            if (($res_e) && ($count > 0)) {
                $row = pg_fetch_array($res_e);

                echo_result(1, 'Особовий рахунок знайдено!', $row['id_paccnt']);
            } else {
                // pg_query($Link, "rollback");
                echo_result(2, 'Нічого не знайдено!');
            }
        }
    }
    
    if ($oper == "clear") {    
        echo_result(-1, '');        
    }
//------------------------------------------------------
   
} catch (Exception $e) {

    echo echo_result(1, 'Error: ' . $e->getMessage());
}
?>