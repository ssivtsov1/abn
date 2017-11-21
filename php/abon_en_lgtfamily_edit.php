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

//------------------------------------------------------
    if ($oper == "add") {

        $p_id_lgt   = sql_field_val('id_lgt', 'int');        
        $p_id_paccnt= sql_field_val('id_paccnt', 'int');        
        $p_fio      = sql_field_val('fio', 'string');        
        $p_id_rel   = sql_field_val('id_rel', 'int');        
        $p_dt_birth = sql_field_val('dt_birth', 'date');         
        $p_lgt      = sql_field_val('lgt', 'bool');         

        $p_dt_b      = sql_field_val('dt_start', 'date');         
        $p_dt_e      = sql_field_val('dt_end', 'date');          
        
        $p_id_usr = $session_user; // где взять текущего юзера?    
/*
        $result = pg_query($Link, "begin");
        if (!($result)) {
            echo_result(2, pg_last_error($Link));
            return;
        }

        $QE = "select eqt_change_fun(11,$p_id_paccnt,null,$p_dt_start,$p_id_usr,null,$p_id_meter,1)";

        $result = pg_query($Link, $QE);
        if (!($result)) {
            echo_result(2, pg_last_error($Link));
            pg_query($Link, "rollback");
            return;
        }
*/

        $QE = "INSERT INTO lgm_family_tbl(id_lgt,id_paccnt,  fio, id_rel, dt_birth, dt_start, dt_end, lgt, id_person, active)
            VALUES ($p_id_lgt, $p_id_paccnt, $p_fio, $p_id_rel, $p_dt_birth, $p_dt_b, $p_dt_e, $p_lgt,$p_id_usr, true);";

        $res_e = pg_query($Link, $QE);
        
        if ($res_e) {
          //  pg_query($Link, "commit");
            echo_result(-1, 'Data ins');
        } else {
           // pg_query($Link, "rollback");
            echo_result(2, pg_last_error($Link));
        }
    }
//------------------------------------------------------
    if ($oper == "edit") {

        $p_fio      = sql_field_val('fio', 'string');        
        $p_id_paccnt= sql_field_val('id_paccnt', 'int');        
        $p_id_rel   = sql_field_val('id_rel', 'int');        
        $p_dt_birth = sql_field_val('dt_birth', 'date');         
        $p_lgt      = sql_field_val('lgt', 'bool');         

        $p_dt_b      = sql_field_val('dt_start', 'date');         
        $p_dt_e      = sql_field_val('dt_end', 'date');          
        $p_active   = sql_field_val('active', 'bool');                 
        
        $p_id_usr = $session_user; // где взять текущего юзера?    
        $p_id = sql_field_val('id', 'int');
/*
        $result = pg_query($Link, "begin");
        if (!($result)) {
            echo_result(2, pg_last_error($Link));
            return;
        }

        $QE = "select eqt_change_fun(11,$p_id_paccnt,null,$p_dt_start,$p_id_usr,null,$p_id_meter,1)";

        $result = pg_query($Link, $QE);
        if (!($result)) {
            echo_result(2, pg_last_error($Link));
            pg_query($Link, "rollback");
            return;
        }
*/

        $QE = "update lgm_family_tbl set 
        fio = $p_fio, id_rel=$p_id_rel, dt_birth = $p_dt_birth, lgt = $p_lgt,
        dt_start=$p_dt_b, dt_end=$p_dt_e, active = $p_active, 
        id_paccnt = $p_id_paccnt
        where id = $p_id ;";

        $res_e = pg_query($Link, $QE);
        
        if ($res_e) {
          //  pg_query($Link, "commit");
            echo_result(1, 'Data updated');
        } else {
           // pg_query($Link, "rollback");
            echo_result(2, pg_last_error($Link));
        }
    }
    
//------------------------------------------------------
    if ($oper == "del") {

        $p_id = sql_field_val('id', 'int');
        
        $QE = "Delete from lgm_family_tbl where id= $p_id;";        
        $res_e = pg_query($Link, $QE);

        if ($res_e) {
            //pg_query($Link, "commit");
            echo_result(-1, 'Data delated');
        } else {
            //pg_query($Link, "rollback");
            echo_result(2, pg_last_error($Link));
        }
    }
} catch (Exception $e) {

    echo echo_result(1, 'Error: ' . $e->getMessage());
}
?>