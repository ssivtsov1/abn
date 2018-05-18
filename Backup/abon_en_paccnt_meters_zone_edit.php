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

        $p_id_paccnt = sql_field_val('id_paccnt', 'int');        
        $p_id_meter = sql_field_val('id_meter', 'int');
        $p_id_zone = sql_field_val('id_zone', 'int');
        $p_dt_start = sql_field_val('dt_start', 'date');

        $p_id_usr = $session_user; // где взять текущего юзера?    

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


        $QE = "INSERT INTO clm_meter_zone_tbl(id_meter, id_zone, kind_energy,dt_b,id_person)
            VALUES ($p_id_meter, $p_id_zone, 10,$p_dt_start, $p_id_usr);";


        $res_e = pg_query($Link, $QE);
        if ($res_e) {
            pg_query($Link, "commit");
            echo_result(-1, 'Data ins');
        } else {
            pg_query($Link, "rollback");
            echo_result(2, pg_last_error($Link));
        }
    }
//------------------------------------------------------
    if ($oper == "del") {

        $p_id = sql_field_val('id', 'int');
        $p_id_paccnt = sql_field_val('id_paccnt', 'int');        
        $p_id_meter = sql_field_val('id_meter', 'int');
        $p_change_date    = sql_field_val('dt_oper','date');
        
        $p_id_usr = $session_user; // где взять текущего юзера?    

        $result = pg_query($Link, "begin");
        if (!($result)) {
            echo_result(2, pg_last_error($Link));
            return;
        }

        $QE = "select eqt_change_fun(12,$p_id_paccnt,null,$p_change_date,$p_id_usr,null,$p_id_meter,1)";

        $result = pg_query($Link, $QE);
        if (!($result)) {
            echo_result(2, pg_last_error($Link));
            pg_query($Link, "rollback");
            return;
        }

        $QE = "Delete from clm_meter_zone_tbl where id= $p_id;";        
        $res_e = pg_query($Link, $QE);

        if ($res_e) {
            pg_query($Link, "commit");
            echo_result(-1, 'Data delated');
        } else {
            pg_query($Link, "rollback");
            echo_result(2, pg_last_error($Link));
        }
    }
} catch (Exception $e) {

    echo echo_result(1, 'Error: ' . $e->getMessage());
}
?>