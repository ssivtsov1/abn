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
//sleep(10);
try {
    
  if ($_FILES)
  {
       if ($_FILES['subs_file']['error'] <= 0) 
       {
             $name = $_FILES['subs_file']['name'];
             $size = $_FILES['subs_file']['size'];
             $tmp_name =$_FILES['subs_file']['tmp_name'];
             
             $QE = "delete from tmp_loadsubs_tbl ;";
             $res = pg_query($Link, $QE);
             if (!($res))
             {  
                echo_result(2, pg_last_error($Link));
                return;
             }
             
             $p_id_region = sql_field_val('id_region', 'int');
             $id_res = 'null';
             
             if ($p_id_region!='null')
             {
                $SQL = " select coalesce(r.code_res::varchar,'null') as id_res
                    from  cli_region_tbl as r 
                    where r.id = $p_id_region ;"; 

                $result = pg_query($Link,$SQL) or die("SQL Error: " .pg_last_error($Link) );      
                $row = pg_fetch_array($result);

                if ($row['id_res']!='null')
                {
                    $id_res = "'".$row['id_res']."'";  
                }       
    
             }             
             
             $db = dbase_open($tmp_name, 0);

              if ($db) {
                $record_numbers = dbase_numrecords($db);
                $nf  = dbase_numfields($db);
                $column_info = dbase_get_header_info($db);
               // print_r($column_info);
                for ($i = 1; $i <= $record_numbers; $i++) {   
                    //выполнение каких-либо действий с записью
                    //$row_n = dbase_get_record_with_names($db, $i);
                    $row = dbase_get_record($db, $i);
                    $field_names = '';
                    $field_values = '';
                    for ($j = 0; $j < $nf; $j++) 
                    {   
                        if ($field_names!='') $field_names.=',';
                        
                        $field_names.=$column_info[$j]['name'];
                        
                        
                        if ($field_values!='') $field_values.=',';
                        
                        $field_values.=dbf_field_val($row[$j],$column_info[$j]['type']);
                        
                        
                    }

                    $QE = "insert into tmp_loadsubs_tbl ($field_names,name_file) values ($field_values,'$name');";
                    $res = pg_query($Link, $QE);
                    if (!($res))
                    {  
                        echo_result(2, pg_last_error($Link));
                        return;
                    }
                    
                     //echo $field_names;
                     //echo $field_values;
                }
                
                $QE = "select load_subsid($id_res);";
                $res = pg_query($Link, $QE);
                if (!($res))
                {  
                  echo_result(2, pg_last_error($Link));
                  return;
                }
                
                echo_result(-1, 'File updated');
              }             
              else
              {
                  echo_result(2, 'Dbf error'.$tmp_name);
              }


       }
       
  }

} catch (Exception $e) {

  echo echo_result(1, 'Error: ' . $e->getMessage());
}
?>


