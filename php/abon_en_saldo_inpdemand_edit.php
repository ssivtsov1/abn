<?php

header('Content-type: text/html; charset=utf-8');

include_once 'abon_en_func.php';
include_once 'abon_ded_func.php'; 


session_name("session_kaa");
session_start();

error_reporting(0);

$Link = get_database_link($_SESSION,2);
$session_user = $_SESSION['ses_usr_id'];
session_write_close();

$table_name = 'acm_inpdemand_tbl';
/*
$result = pg_query($Link, "select crt_ttbl();");
if (!($result)) {
    echo_result(2, pg_last_error($Link) );
    //pg_query($Link, "rollback");
    return;
}

$result = pg_query($Link, "insert into syi_sysvars_tmp (id, ident,type_ident, value_ident) 
     values(120,'id_person','int', $session_user);");

if (!($result)) {
    echo_result(2, pg_last_error($Link) );
    //pg_query($Link, "rollback");
    return;
} 
*/
try {

  if (isset($_POST['oper'])) {
      
    $oper = $_POST['oper'];

    $p_id = sql_field_val('id', 'int');
    $p_id_paccnt = sql_field_val('id_paccnt', 'int');
    $p_id_meter = sql_field_val('id_meter', 'int');
    $p_id_energy = 10; 
    $p_id_zone = sql_field_val('id_zone', 'int');
    
    $p_dat_b = sql_field_val('dat_b', 'date');
    $p_dat_e = sql_field_val('dat_e', 'date');
    
    $p_demand = sql_field_val('demand', 'numeric');

    $p_id_usr = $session_user;    
    
//------------------------------------------------------
    if ($oper == "edit") {

      $QE = "update $table_name set
        id_zone = $p_id_zone, dat_b = $p_dat_b, dat_e = $p_dat_e, demand = $p_demand,
        id_person = $session_user, dt = now()
        where id = $p_id;"; 

      $res_e = pg_query($Link, $QE);
      if ($res_e) {
        echo_result(1, 'Data updated');
      } else {
        echo_result(2, pg_last_error($Link));
      }
    }
//------------------------------------------------------
    if ($oper == "add") {

      $QE = "insert into $table_name (id_paccnt, id_meter, id_energy, id_zone, dat_b, dat_e, 
            demand, id_person)
        select $p_id_paccnt, m.id, $p_id_energy, $p_id_zone, $p_dat_b, $p_dat_e,$p_demand,
        $session_user
        from clm_meterpoint_tbl as m 
        where m.id_paccnt = $p_id_paccnt limit 1;";
        
      $res_e = pg_query($Link, $QE);
      
        if (!($res_e)) 
        {
            echo_result(2,pg_last_error($Link));
            return;
        }    
      
     // $row = pg_fetch_array($res_e);
     // $id_doc = $row["id_doc"];
        
      if ($res_e){
        echo_result(1, 'Data ins');
      }
      else{
        echo_result(2, pg_last_error($Link));
      }
    }
//------------------------------------------------------
    if ($oper == "del") {

      $QE = "delete from $table_name where id = $p_id;";                 

      $res_e = pg_query($Link, $QE);

      if ($res_e)
        echo_result(1, 'Data deleted');
      else
        echo_result(2, pg_last_error($Link));
    }
  }
} catch (Exception $e) {

  echo echo_result(1, 'Error: ' . $e->getMessage());
}
?>


