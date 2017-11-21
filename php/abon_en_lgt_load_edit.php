<?php

header('Content-type: text/html; charset=utf-8');

include_once 'abon_en_func.php';
include_once 'abon_ded_func.php';


session_name("session_kaa");
session_start();

error_reporting(0);

$Link = get_database_link($_SESSION, 2);
$session_user = $_SESSION['ses_usr_id'];

session_write_close();
//sleep(10);
try {

    if (isset($_POST['submitButton'])) 
    {    
        $oper=$_POST['submitButton'];
    }
    else
    {    
        if (isset($_POST['oper'])) {
            $oper=$_POST['oper'];
        }
    }

    if ($oper == "load") {

        if ($_FILES) {
            if ($_FILES['lgt_file']['error'] <= 0) {
                $name = $_FILES['lgt_file']['name'];
                $size = $_FILES['lgt_file']['size'];
                $tmp_name = $_FILES['lgt_file']['tmp_name'];

                $QE = "delete from tmp_loadlgt_tbl ;";
                $res = pg_query($Link, $QE);
                if (!($res)) {
                    echo_result(2, pg_last_error($Link));
                    return;
                }


                $db = dbase_open($tmp_name, 0);

                if ($db) {
                    $record_numbers = dbase_numrecords($db);
                    $nf = dbase_numfields($db);
                    $column_info = dbase_get_header_info($db);
                    // print_r($column_info);
                    for ($i = 1; $i <= $record_numbers; $i++) {
                        //выполнение каких-либо действий с записью
                        //$row_n = dbase_get_record_with_names($db, $i);
                        $row = dbase_get_record($db, $i);
                        $field_names = '';
                        $field_values = '';
                        for ($j = 0; $j < $nf; $j++) {
                            if ($field_names != '')
                                $field_names.=',';

                            $field_names.=$column_info[$j]['name'];


                            if ($field_values != '')
                                $field_values.=',';

                            $field_values.=dbf_field_val($row[$j], $column_info[$j]['type']);
                        }

                        $QE = "insert into tmp_loadlgt_tbl ($field_names) values ($field_values) ;";
                        $res = pg_query($Link, $QE);
                        if (!($res)) {
                            echo_result(2, pg_last_error($Link));
                            return;
                        }

                        //echo $field_names;
                        //echo $field_values;
                    }

                    $p_id_region = sql_field_val('id_region', 'int');
                    $QE = "select load_lgt('$name',$p_id_region) as id_file;";
                    $res = pg_query($Link, $QE);
                    if (!($res)) {
                        echo_result(2, pg_last_error($Link));
                        return;
                    }
                    $row = pg_fetch_array($res);
                    echo_result(-1, $name, $row['id_file']);
                } else {
                    echo_result(2, 'Dbf error' . $tmp_name);
                }
            }
        }
    }
    if ($oper == "apply") {

        $p_id_file = sql_field_val('id_file', 'int');
        $QE = "select apply_lgt($p_id_file,$session_user);";
        $res = pg_query($Link, $QE);
        if (!($res)) {
              echo_result(2, pg_last_error($Link));
              return;
        }
        echo_result(1, 'Data parsed');
        
    }    
    if ($oper == "delete") {

        $p_id_file = sql_field_val('id_file', 'int');
        $QE = "delete from lgm_file_load_tbl where id = $p_id_file;";
        $res = pg_query($Link, $QE);
        if (!($res)) {
              echo_result(2, pg_last_error($Link));
              return;
        }
        echo_result(-2, 'Data deleted');
        
    }    

    
} catch (Exception $e) {

    echo echo_result(1, 'Error: ' . $e->getMessage());
}
?>