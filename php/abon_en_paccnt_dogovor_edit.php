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

        $p_id_iagreem      = sql_field_val('id_iagreem', 'int');         
        $p_shifr           = sql_field_val('shifr', 'string');        
        $p_num_agreem      = sql_field_val('num_agreem', 'string');        
        $p_date_agreem     = sql_field_val('date_agreem', 'date');        
        $p_dt_b            = sql_field_val('dt_b', 'date');         
        $p_dt_e            = sql_field_val('dt_e', 'date');          
        $p_id_town_agreem  = sql_field_val('id_town_agreem', 'int');        
        $p_id_abon         = sql_field_val('id_abon', 'int');        
        $p_agreem_abon      = sql_field_val('agreem_abon', 'string');                
        $p_power           = sql_field_val('power', 'numeric');        
        $p_categ           = sql_field_val('categ', 'int');      
        
        
      
        $p_id_usr = $session_user; // где взять текущего юзера?    

        $result = pg_query($Link, "begin");
        if (!($result)) {
            echo_result(2, pg_last_error($Link));
            return;
        }

        $QE = "select eqt_change_fun(41,$p_id_paccnt,null,$p_dt_b,$p_id_usr,null,null,1)";

        $result = pg_query($Link, $QE);
        if (!($result)) {
            echo_result(2, pg_last_error($Link));
            pg_query($Link, "rollback");
            return;
        }


        $QE = "INSERT INTO clm_agreem_tbl(id_paccnt,id_iagreem, shifr, num_agreem, date_agreem, dt_b, dt_e, 
            id_town_agreem, id_abon, agreem_abon, power, categ, id_person)
            VALUES ($p_id_paccnt,$p_id_iagreem, $p_shifr, $p_num_agreem, $p_date_agreem, $p_dt_b, $p_dt_e, 
            $p_id_town_agreem, $p_id_abon, $p_agreem_abon, $p_power, $p_categ, $p_id_usr) ;";

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
    if ($oper == "edit") {

        $p_id_paccnt = sql_field_val('id_paccnt', 'int');        

        $p_id_iagreem      = sql_field_val('id_iagreem', 'int');         
        $p_shifr           = sql_field_val('shifr', 'string');        
        $p_num_agreem      = sql_field_val('num_agreem', 'string');        
        $p_date_agreem     = sql_field_val('date_agreem', 'date');        
        $p_dt_b            = sql_field_val('dt_b', 'date');         
        $p_dt_e            = sql_field_val('dt_e', 'date');          
        $p_id_town_agreem  = sql_field_val('id_town_agreem', 'int');        
        $p_id_abon         = sql_field_val('id_abon', 'int');     
        $p_agreem_abon     = sql_field_val('agreem_abon', 'string');                        
        $p_power           = sql_field_val('power', 'numeric');        
        $p_categ           = sql_field_val('categ', 'int');        
        
        $p_change_date    = sql_field_val('change_date','date');
        
        $p_id_usr = $session_user; // где взять текущего юзера?    
        $p_id = sql_field_val('id', 'int');

        $result = pg_query($Link, "begin");
        if (!($result)) {
            echo_result(2, pg_last_error($Link));
            return;
        }

        $QE = "select eqt_change_fun(42,$p_id_paccnt,null,$p_change_date,$p_id_usr,null,null,1)";

        $result = pg_query($Link, $QE);
        if (!($result)) {
            echo_result(2, pg_last_error($Link));
            pg_query($Link, "rollback");
            return;
        }


        $QE = "update clm_agreem_tbl set 
        id_iagreem = $p_id_iagreem, shifr = $p_shifr, num_agreem = $p_num_agreem,
        date_agreem = $p_date_agreem, id_town_agreem = $p_id_town_agreem, 
        id_abon = $p_id_abon, agreem_abon = $p_agreem_abon,
        power = $p_power, categ = $p_categ,
        dt_b=$p_dt_b, dt_e=$p_dt_e
        where id = $p_id ;";

        $res_e = pg_query($Link, $QE);
        
        if ($res_e) {
            echo_result(1, 'Data updated');
            pg_query($Link, "commit");            
        } else {
            echo_result(2, pg_last_error($Link));
            pg_query($Link, "rollback");            
        }
    }
    
//------------------------------------------------------
    if ($oper == "del") {

        $p_id = sql_field_val('id', 'int');
        $p_change_date    = sql_field_val('change_date','date');
        $p_id_usr = $session_user; 
        $p_id_paccnt = sql_field_val('id_paccnt', 'int');        
        
        $result = pg_query($Link, "begin");
        if (!($result)) {
            echo_result(2, pg_last_error($Link));
            return;
        }

        $QE = "select eqt_change_fun(43,$p_id_paccnt,null,$p_change_date,$p_id_usr,null,null,1)";

        $result = pg_query($Link, $QE);
        if (!($result)) {
            echo_result(2, pg_last_error($Link));
            pg_query($Link, "rollback");
            return;
        }               
        
        $QE = "Delete from clm_agreem_tbl where id= $p_id;";        
        $res_e = pg_query($Link, $QE);

        if ($res_e) {
            echo_result(-1, 'Data delated');
            pg_query($Link, "commit");
        } else {
            echo_result(2, pg_last_error($Link));
            pg_query($Link, "rollback");
        }
    }
} catch (Exception $e) {

    echo echo_result(1, 'Error: ' . $e->getMessage());
}
?>