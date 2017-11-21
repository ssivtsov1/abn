<?php
//header('Content-type: text/html; charset=utf-8');

require 'abon_en_func.php';
require 'abon_ded_func.php';

//session_name("session_kaa");
//session_start();

error_reporting(1);
try {

//$Link = get_database_link($_SESSION);

$base = sql_field_val('name_base', 'varchar');
$id_user = sql_field_val('id_user', 'int');
$passwd = sql_field_val('passwd', 'string');
$ip="'".$_SERVER["REMOTE_ADDR"]."'";

$cstr="host=$app_host dbname=$base user=$app_user password=$app_pass"; 
//$link = pg_connect($cstr) or die("Connection Error: " . pg_last_error($link));

if (!($link = pg_connect($cstr))) 
{
    echo_result(2,"Connection Error: " . pg_last_error($link));
    return;
}

if (!($sys_link = pg_connect($app_maint_cstr) ))
{
    echo_result(2,"Connection Error: " . pg_last_error($sys_link));
    return;
}


$Query= " select nm_txt from nm_db where nm_db = '{$base}' ;";

 $result=pg_query($sys_link,$Query);
 if ($result) 
 {
     $row = pg_fetch_array($result);
     $base_name = $row['nm_txt'];
     if ($base_name=='') $base_name = $base;
 }
 else
 {
     $base_name = $base;
 }


$Query= " select sys_check_pwd ($id_user,$passwd)::int as r;";

 $result=pg_query($link,$Query);
 if (!($result)) 
 {
   echo_result(2,pg_last_error($Link));
   return;
 }    
 else 
 {
     $row = pg_fetch_array($result);
     if ($row['r']==0)
     {
        if (isset($_SESSION['id_sess'])) {
            unset($_SESSION['id_sess']);  // when we use this id_sess?
            session_destroy();
        }

        //$sys_link = pg_connect($app_maint_cstr) or die("Connection Error: " . pg_last_error($sys_link));
        if (!($sys_link = pg_connect($app_maint_cstr))) 
        {
            echo_result(2,"Connection Error: " . pg_last_error($sys_link));
            return;
        }
                                
        $Query2= " insert into session_log_tbl(id_sess,name_db,id_user,user_ip)
            values(DEFAULT,'$base',$id_user,$ip ) returning id_sess;";
        
        $sys_result = pg_query($sys_link,$Query2);

        if ($sys_result) 
        {
          $sys_row = pg_fetch_array($sys_result);
          $id_sess = $sys_row["id_sess"];
          // create new session
          session_name("session_kaa");
          session_start();
                
          $_SESSION['id_sess'] = $id_sess;
          $_SESSION['ses_link_str'] = $cstr;
          $_SESSION['ses_usr_id'] = $id_user;
          $_SESSION['base_name'] = $base_name;
          $_SESSION['base_pgname'] = $base;

          setcookie("fizabon_app_base",$base);           
          setcookie("fizabon_app_user",$id_user);
        
          echo_result(-1,'Вхід виконано!');
          return;
          
        }
        else {
           echo_result(2,pg_last_error($sys_link));
           return;
        }
         
     }
     else 
     {
         echo_result(1,'Помилковий пароль!');
         return;
     }
 
 }

}
catch (Exception $e) {

 echo echo_result(2,'Error: '.$e->getMessage());
}


?>
