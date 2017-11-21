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

$table_name = 'acm_headpay_tbl';

try {

  if (isset($_POST['oper'])) {
      
    $oper = $_POST['oper'];

    $p_id = sql_field_val('id', 'int');
    $p_reg_num = sql_field_val('reg_num', 'string');
    $p_reg_date = sql_field_val('reg_date', 'date');
    
    $p_id_origin = sql_field_val('id_origin', 'int');
    
    $p_count_pay = sql_field_val('count_pay', 'int');
    $p_sum_pay = sql_field_val('sum_pay', 'numeric');
    
    $p_id_usr = $session_user;
    
    
    
//------------------------------------------------------
    if ($oper == "edit") {

      $result = pg_query($Link, "begin");
      if (!($result)) {
          echo_result(2, pg_last_error($Link));
         return;
      }        
      
     $result = pg_query($Link, "select crt_ttbl();");
     if (!($result)) {
        echo_result(2, pg_last_error($Link) );
        pg_query($Link, "rollback");
        return;
     }
        

    $result = pg_query($Link, "insert into syi_sysvars_tmp (id, ident,type_ident, value_ident) 
         values(120,'id_person','int', $session_user);");

    if (!($result)) {
        echo_result(2, pg_last_error($Link) );
        pg_query($Link, "rollback");
        return;
    }     
     
        
      $QE = "update $table_name set
        reg_num = $p_reg_num, reg_date = $p_reg_date, id_origin = $p_id_origin, 
        count_pay = $p_count_pay,  sum_pay = $p_sum_pay, id_person = $p_id_usr
        where id = $p_id;"; 

      $res_e = pg_query($Link, $QE);
      
      if (!($res_e)) 
      {
          echo_result(2,pg_last_error($Link));
          pg_query($Link, "rollback");
          return;
      }         
      
  //-----------------------------------------------------    
  if ($_FILES) 
  {
      
                if ($_FILES['pay_file']['error'] <= 0) {
                    $name = $_FILES['pay_file']['name'];
                    $size = $_FILES['pay_file']['size'];
                    $tmp_name = $_FILES['pay_file']['tmp_name'];

                    $info = new SplFileInfo($name);
                    if ($info->getExtension()=='dbf')
                    {
                    
                    $QE = "delete from acm_pay_load_tbl where id_headpay = $p_id;";
       
                    $res_e = pg_query($Link, $QE);
                        
                    if (!($res_e)) 
                    {
                        echo_result(2,pg_last_error($Link));
                        pg_query($Link, "rollback");
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
                            $fields_str = 'name_citi,name_city,name_strit,rc,mfo,mfo_ot,abcount,pdate,date_ob,summ,fio,code_c,code_s,house,korpus,bukva,kvartira,dateb,datee,counte,countb,countd,idfusl';
                            $fields = explode(',', $fields_str);
                            
                            $field_names='';

                            $field_values = '';
                            for ($j = 0; $j < $nf; $j++) {
                                //if ($field_names!='') $field_names.=',';

                                $field_name = strtolower($column_info[$j]['name']);
                                
                                //echo $field_name;

                                if (in_array($field_name, $fields)) {
                                    
                                    if ($field_names!='') $field_names.=',';
                        
                                    if ($field_name=='name_city')
                                      $field_names.='name_citi';
                                    else
                                      $field_names.=$field_name;
                                    
                                    if ($field_values != '') $field_values.=',';

                                    $field_values.=dbf_field_val($row[$j], $column_info[$j]['type']);
                                }
                            }

                            $QE = "insert into acm_pay_load_tbl (id_headpay, id_person, $field_names) values ($p_id, $p_id_usr, $field_values) ;";
                            
                            //echo $QE;
                            
                            $res = pg_query($Link, $QE);
                            if (!($res)) {
                                echo_result(2, pg_last_error($Link));
                                pg_query($Link, "rollback");
                                return;
                            }

                            //echo $field_names;
                            //echo $field_values;
                        }
                        
                          $QE = "select load_bank_dbf($p_id);";
                          $res = pg_query($Link, $QE);
                          if (!($res))
                          {
                            echo_result(2, pg_last_error($Link));
                            pg_query($Link, "rollback");
                            return;
                          }
                     }   
                    else {
                        echo_result(2, 'Dbf error' . $tmp_name);
                        pg_query($Link, "rollback");
                        return;
                    }
                     
                  }    
                  if ($info->getExtension()=='csv')
                  {
                    chmod($tmp_name, 0777);
                    $QE = "select load_bank_pumb($id_pack,'$tmp_name');";
                    $res = pg_query($Link, $QE);
                    if (!($res))
                    {
                        echo_result(2, pg_last_error($Link));
                        pg_query($Link, "rollback");
                        return;
                    }
                  }                     
                     
                  $QE = "update $table_name set name_file = '$name' ,
                             count_pay = cnt, sum_pay = summ
                          from ( select count(p.id_doc) as cnt, sum(value) as summ from 
                          acm_pay_tbl as p where p.id_headpay = $p_id ) as ss
                          where acm_headpay_tbl.id = $p_id;";                      
                      
                      
                  $res_e = pg_query($Link, $QE);
                        
                  if (!($res_e)) 
                  {
                      echo_result(2,pg_last_error($Link));
                      pg_query($Link, "rollback");
                      return;
                  }    
                    
                }
   }
   //-----------------------------------------------------        
      
      pg_query($Link, "commit");
      echo_result(1, 'Data updated'.$QE);
    }
//------------------------------------------------------
    if ($oper == "add") {

        $result = pg_query($Link, "begin");
        if (!($result)) {
            echo_result(2, pg_last_error($Link));
            return;
        }        
        
        $result = pg_query($Link, "select crt_ttbl();");
        if (!($result)) {
           echo_result(2, pg_last_error($Link) );
           pg_query($Link, "rollback");
           return;
        }
        
        $result = pg_query($Link, "insert into syi_sysvars_tmp (id, ident,type_ident, value_ident) 
             values(120,'id_person','int', $session_user);");

        if (!($result)) {
            echo_result(2, pg_last_error($Link) );
            pg_query($Link, "rollback");
            return;
        }     
        
        
        
      $QE = "insert into $table_name ( id, reg_num, reg_date, id_origin, count_pay, sum_pay, id_person)
        values(DEFAULT, $p_reg_num, $p_reg_date, $p_id_origin, $p_count_pay, $p_sum_pay, $p_id_usr) returning id;";
        
      $res_e = pg_query($Link, $QE);
      
        if (!($res_e)) 
        {
            echo_result(2,pg_last_error($Link));
            pg_query($Link, "rollback");
            return;
        }    
      
      $row = pg_fetch_array($res_e);
      $id_pack = $row["id"];
        
      if (!($res_e)) 
      {
          echo_result(2,pg_last_error($Link));
          pg_query($Link, "rollback");
          return;
      }    
  //-----------------------------------------------------    
  if ($_FILES) 
  {
                if ($_FILES['pay_file']['error'] <= 0) 
                {
                    $name = $_FILES['pay_file']['name'];
                    $size = $_FILES['pay_file']['size'];
                    $tmp_name = $_FILES['pay_file']['tmp_name'];

                    $info = new SplFileInfo($name);
                    if ($info->getExtension()=='dbf')
                    {

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
                            $fields_str = 'name_citi,name_city,name_strit,rc,mfo,mfo_ot,abcount,pdate,date_ob,summ,fio,code_c,code_s,house,korpus,bukva,kvartira,dateb,datee,counte,countb,countd,idfusl';
                            $fields = explode(',', $fields_str);
                            
                            $field_names='';

                            $field_values = '';
                            for ($j = 0; $j < $nf; $j++) {
                                //if ($field_names!='') $field_names.=',';

                                $field_name = strtolower($column_info[$j]['name']);
                                
                                //echo $field_name;

                                if (in_array($field_name, $fields)) {
                                    
                                    if ($field_names!='') $field_names.=',';
                        
                                    if ($field_name=='name_city')
                                      $field_names.='name_citi';
                                    else
                                      $field_names.=$field_name;
                                    
                                    if ($field_values != '') $field_values.=',';

                                    $field_values.=dbf_field_val($row[$j], $column_info[$j]['type']);
                                }
                            }

                            $QE = "insert into acm_pay_load_tbl (id_headpay, id_person, $field_names) values ($id_pack, $p_id_usr, $field_values) ;";
                            
                            //echo $QE;
                            
                            $res = pg_query($Link, $QE);
                            if (!($res)) {
                                echo_result(2, pg_last_error($Link));
                                pg_query($Link, "rollback");
                                return;
                            }

                            //echo $field_names;
                            //echo $field_values;
                        }
                        
                          $QE = "select load_bank_dbf($id_pack);";
                          $res = pg_query($Link, $QE);
                          if (!($res))
                          {
                            echo_result(2, pg_last_error($Link));
                            pg_query($Link, "rollback");
                            return;
                          }
                         
                        //echo_result(-1, 'File updated');

                    } else {
                        echo_result(2, 'Dbf error' . $tmp_name);
                        pg_query($Link, "rollback");
                        return;
                    }                          
                }    

                if ($info->getExtension()=='csv')
                {
                    chmod($tmp_name, 0777);
                    
                    $QE = "select load_bank_pumb($id_pack,'$tmp_name');";
                    $res = pg_query($Link, $QE);
                    if (!($res))
                    {
                        echo_result(2, pg_last_error($Link));
                        pg_query($Link, "rollback");
                        return;
                    }
                }
                
                
                $QE = "update $table_name set name_file = '$name' ,
                             count_pay = cnt, sum_pay = summ
                          from ( select count(p.id_doc) as cnt, sum(value) as summ from 
                          acm_pay_tbl as p where p.id_headpay = $id_pack ) as ss
                          where acm_headpay_tbl.id = $id_pack;";
                      
                      
                $res_e = pg_query($Link, $QE);
                        
                if (!($res_e)) 
                {
                    echo_result(2,pg_last_error($Link));
                    pg_query($Link, "rollback");
                    return;
                }   
            }   
                 
   }
   //-----------------------------------------------------   
   pg_query($Link, "commit");
   echo_result(1, 'Data ins',$id_pack);      
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
//------------------------------------------------------
    if ($oper == "lock") {

      $QE = "update $table_name set
        reg_num = $p_reg_num, reg_date = $p_reg_date, id_origin = $p_id_origin, 
        count_pay = $p_count_pay,  sum_pay = $p_sum_pay, id_person = $p_id_usr, user_lock = 1
        where id = $p_id;"; 

      $res_e = pg_query($Link, $QE);
      
      if ($res_e)
        echo_result(1, 'Data locked');
      else
        echo_result(2, pg_last_error($Link));
      
    }    
//------------------------------------------------------
    if ($oper == "unlock") {

      $QE = "update $table_name set user_lock = 0
         where id = $p_id;"; 

      $res_e = pg_query($Link, $QE);
      
      if ($res_e)
        echo_result(1, 'Data unlocked');
      else
        echo_result(2, pg_last_error($Link));
      
    }      
  }
} catch (Exception $e) {

  echo echo_result(1, 'Error: ' . $e->getMessage());
}
?>


