<?php

header("Content-type: application/json;charset=utf-8");

include_once 'abon_en_func.php';
include_once 'abon_ded_func.php';

session_name("session_kaa");
session_start();

error_reporting(0);

$Link = get_database_link($_SESSION,2);
$session_user = $_SESSION['ses_usr_id'];
session_write_close();

try {

  if (isset($_POST['id_usr'])) {
      
    $id_usr = sql_field_val('id_usr', 'int');
    $passwd = sql_field_val('passwd', 'string'); 
    $p_id_usr = $session_user;
    
    if ($passwd=='null')    
    {
      $QE = "update syi_user set pwd_code = null
          where id = $id_usr;";         
        
      $res_e = pg_query($Link, $QE);
      
      if (!($res_e)) 
      {
           echo_result(2,pg_last_error($Link));
           return;
      }    
      else {
           echo_result(-1,'Пароль змінено');
           return;
      }
    }     
    else
    {
        
      $SQL = "select position('8.2' in version()) as ver; "; 
      $res = pg_query($Link, $SQL);
      $row = pg_fetch_array($res);
      $version_flag = $row["ver"];
      
      if ($version_flag == 0 )
         $QE = "update syi_user set pwd_code = ('x'||substr(md5($passwd),1,8))::bit(32)::int where id = $id_usr;";          
      else
         $QE = "update syi_user set pwd_code = hashname($passwd::name)where id = $id_usr;";
        
      $res_e = pg_query($Link, $QE);
      
      if (!($res_e)) 
      {
           echo_result(2,pg_last_error($Link).$QE);
           return;
       }    
      else {
           echo_result(-1,'Пароль змінено');
           return;
      }
       
    }     
   }    
 
} catch (Exception $e) {

  echo echo_result(1, 'Error: ' . $e->getMessage());
}
?>


