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

try {

    if (isset($_POST['id_array'])) {
        $p_id_array = $_POST['id_array'];


        //echo_result(2,$p_id_array);

        $p_id_usr = $session_user;

//------------------------------------------------------

        $QE = "update lgm_cancel_tmp set enable = 0;";

        $result = pg_query($Link, $QE);
        if (!($result)) {
            echo_result(2, pg_last_error($Link));
            return;
        }

       $result = pg_query($Link, "select crt_ttbl();");
        if (!($result)) {
            echo_result(2, pg_last_error($Link) );
            return;
        }

        $result = pg_query($Link, "insert into syi_sysvars_tmp (id, ident,type_ident, value_ident) 
            values(120,'id_person','int', $session_user);");

        if (!($result)) {
            echo_result(2, pg_last_error($Link) );
            return;
        }

        
        foreach ($p_id_array as $vid) {

            $QE = "update lgm_cancel_tmp set enable = 1 where id = $vid;";
            $res_e = pg_query($Link, $QE);
            
            if (!($res_e)) {
                echo_result(2, pg_last_error($Link));
                return;
            }
            
        }

        $QE = "select lgm_cancel_exec_fun()";

        $result = pg_query($Link, $QE);
        if (!($result)) {
            echo_result(2, pg_last_error($Link));
        }
        else
        {
            echo_result(1, 'Data upd');
        }
        
    }
 else {
        echo_result(0, 'Nothing to do');
    }
} catch (Exception $e) {

    echo echo_result(1, 'Error: ' . $e->getMessage());
}
?>


