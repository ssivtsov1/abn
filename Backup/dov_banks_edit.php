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

        $p_mfo = sql_field_val('mfo', 'int');
        $p_name = sql_field_val('name', 'string');
        $p_short_name = sql_field_val('short_name', 'string');

//------------------------------------------------------
        if ($oper == "edit") {

            $QE = "UPDATE bank 
                SET name=$p_name, short_name=$p_short_name, mfo = $p_mfo 
                WHERE mfo = $p_mfo;";

            $res_e = pg_query($Link, $QE);
            if ($res_e) {
                echo_result(1, 'Data updated');
            } else {
                echo_result(2, pg_last_error($Link));
            }
        }
//------------------------------------------------------
        if ($oper == "add") {


            $QE = "INSERT INTO bank(mfo,name,short_name)
                values( $p_mfo,$p_name,$p_short_name );";

            $res_e = pg_query($Link, $QE);
            if ($res_e) {
                echo_result(1, 'Data ins');
            } else {
                echo_result(2, pg_last_error($Link));
            }
        }
//------------------------------------------------------
        if ($oper == "del") {

            $p_id = sql_field_val('id', 'int');
            $QE = "Delete from bank where mfo= $p_id;";

            $res_e = pg_query($Link, $QE);

            if ($res_e) {
                echo_result(1, 'Data delated');
            } else {
                echo_result(2, pg_last_error($Link));
            }
        }
    }
} catch (Exception $e) {

    echo echo_result(1, 'Error: ' . $e->getMessage());
}
?>