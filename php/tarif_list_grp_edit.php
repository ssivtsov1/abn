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

$table_name = 'aqi_grptar_tbl';

try {

  if (isset($_POST['oper'])) {
      
    $oper = $_POST['oper'];

    $p_id = sql_field_val('id', 'int');
    $p_nm = sql_field_val('nm', 'string');
    $p_sh_nm = sql_field_val('sh_nm', 'string');
    $p_ident = sql_field_val('ident', 'string');
    $p_typ_tar = sql_field_val('typ_tar', 'int');
    $p_id_lgt_group = sql_field_val('id_lgt_group', 'int');

//------------------------------------------------------
    if ($oper == "edit") {

      $QE = "UPDATE $table_name
        SET nm=$p_nm, sh_nm=$p_sh_nm,  ident = $p_ident, typ_tar = $p_typ_tar, id_lgt_group = $p_id_lgt_group
        WHERE id=$p_id;";

      $res_e = pg_query($Link, $QE);
      if ($res_e) {
        echo_result(1, 'Data updated');
      } else {
        echo_result(2, pg_last_error($Link));
      }
    }
//------------------------------------------------------
    if ($oper == "add") {

      $QE = "INSERT INTO $table_name (nm, sh_nm, ident, typ_tar,id_lgt_group)
      VALUES($p_nm,$p_sh_nm,$p_ident,$p_typ_tar,$p_id_lgt_group)";

      $res_e = pg_query($Link, $QE);
      if ($res_e)
        echo_result(-1 , 'Data ins');
      else
        echo_result(2, pg_last_error($Link).$QE);
    }
//------------------------------------------------------
    if ($oper == "del") {

      $QE = "DELETE FROM $table_name WHERE id= $p_id;";

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