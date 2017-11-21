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
   $p_id_type= sql_field_val('id_type', 'int');        
   $p_id_plomb_owner = sql_field_val('id_plomb_owner', 'int');        
   $p_id_place = sql_field_val('id_place', 'int');           
   $p_plomb_num   = sql_field_val('plomb_num', 'string');        
   $p_id_meter    = sql_field_val('id_meter', 'int');        
   $p_num_meter     = sql_field_val('num_meter', 'string');        
   
   $p_id_person_on     = sql_field_val('id_person_on', 'int');         
   $p_id_person_off     = sql_field_val('id_person_off', 'int');            
   
   $p_dt_on    = sql_field_val('dt_on', 'date');         
   $p_dt_off    = sql_field_val('dt_off', 'date');         
   
   $p_comment = sql_field_val('comment', 'string');         
   
   $p_id_usr = $session_user; // где взять текущего юзера?    
    
    
//------------------------------------------------------
    if ($oper == "add") {

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

        $QE = "INSERT INTO clm_plomb_tbl(id_paccnt, id_type, id_plomb_owner, id_place, plomb_num, 
            id_meter, num_meter, id_person_on, id_person_off, dt_on, dt_off,  comment, id_person)
            VALUES ($p_id_paccnt, $p_id_type, $p_id_plomb_owner, $p_id_place, $p_plomb_num, 
            $p_id_meter, $p_num_meter, $p_id_person_on, $p_id_person_off, $p_dt_on, $p_dt_off,  $p_comment ,$p_id_usr) ;";

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

        $QE = "update clm_plomb_tbl set 
        id_type = $p_id_type, id_plomb_owner = $p_id_plomb_owner, id_place = $p_id_place, plomb_num=$p_plomb_num, 
            id_meter = $p_id_meter, num_meter = $p_num_meter, id_person_on = $p_id_person_on, id_person_off =$p_id_person_off, 
            dt_on = $p_dt_on, dt_off = $p_dt_off,  comment = $p_comment
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

        $QE = "Delete from clm_plomb_tbl where id= $p_id;";        
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