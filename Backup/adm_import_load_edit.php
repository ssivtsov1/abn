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
    
  if ($_FILES)
  {
       if ($_FILES['data_file']['error'] <= 0) 
       {
             $name = $_FILES['data_file']['name'];
             $size = $_FILES['data_file']['size'];
             $tmp_name =$_FILES['data_file']['tmp_name'];
             
             $QE = "delete from tmp_load ;";
             $res = pg_query($Link, $QE);
             if (!($res))
             {  
                echo_result(2, pg_last_error($Link));
                return;
             }
             
             $file_arr = file($tmp_name);
             foreach ($file_arr as $line_num => $line) {
                //echo "Line #{$line_num}: " . $line;
                 
                    $field_value = dbf_field_val($line,'character');
                            
                    $QE = "insert into tmp_load (str_sql) values ($field_value) ;";
                    $res = pg_query($Link, $QE);
                    if (!($res))
                    {  
                        echo_result(2, pg_last_error($Link));
                        return;
                    }
                 
             }             
               
              $QE = "select load_global();";
              $res = pg_query($Link, $QE);
              if (!($res))
              {  
                echo_result(2, pg_last_error($Link));
                return;
              }
                
              echo_result(-1, 'File updated');
             
             
       }
       
  }

} catch (Exception $e) {

  echo echo_result(1, 'Error: ' . $e->getMessage());
}
?>


