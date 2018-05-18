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

   $p_num_doc   = sql_field_val('num_doc', 'string');        
   $p_date_doc    = sql_field_val('date_doc', 'date');         
   
   $p_dt_b    = sql_field_val('dt_b', 'date');         
   $p_dt_e    = sql_field_val('dt_e', 'date');         
   
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

        $QE = "INSERT INTO clm_notlive_tbl(id_paccnt, num_doc,date_doc, dt_b, dt_e, comment, id_person)
            VALUES ($p_id_paccnt, $p_num_doc, $p_date_doc, $p_dt_b, $p_dt_e, $p_comment ,$p_id_usr) ;";

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

        $QE = "update clm_notlive_tbl set 
        num_doc = $p_num_doc, date_doc = $p_date_doc,  dt_b = $p_dt_b, dt_e = $p_dt_e,  comment = $p_comment
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

        $QE = "Delete from clm_notlive_tbl where id= $p_id;";        
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