<?php
header('Content-type: text/html; charset=utf-8');

require_once 'abon_en_func.php';
require 'abon_ded_func.php';

//session_name("session_kaa");
//session_start();

error_reporting(1);
try {

//$link = get_database_link($_SESSION);

$base = $_GET['name_base']; // sql_field_val('name_base', 'varchar');
$id_user = $_GET['user'];//sql_field_val('user', 'int');
$passwd = $_GET['passwd']; //sql_field_val('passwd', 'string');

$book='';
$code='';
$index_accnt='';
$mode = 1;
$enter_status = 0;


 if (isset($_GET['book'])&& (trim($_GET['book'])!='')){
      $book=$_GET['book'];
 }

 if (isset($_GET['code'])&& (trim($_GET['code'])!='')){
      $code=$_GET['code'];
 }
 
 if (isset($_GET['index_accnt'])&& (trim($_GET['index_accnt'])!='')){
      $index_accnt=$_GET['index_accnt'];
 }
 
 if (isset($_GET['mode'])&& (trim($_GET['mode'])!='')){
      $mode=$_GET['mode'];
 }
 
 
 
$ip="'".$_SERVER["REMOTE_ADDR"]."'";

$cstr="host=$app_host dbname=$base user=$app_user password=$app_pass"; 
//$link = pg_connect($cstr) or die("Connection Error: " . pg_last_error($link));

if (!($link = pg_connect($cstr))) 
{
    echo("Connection Error 1: ". pg_last_error($link));
    return;
}

if (!($sys_link = pg_connect($app_maint_cstr) ))
{
    echo("Connection Error 2: " . pg_last_error($sys_link));
    return;
}
/*

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
*/
$base_name = $base;
 
$Query= " select sys_check_pwd ($id_user,'$passwd')::int as r;";

 $result=pg_query($link,$Query);
 if (!($result)) 
 {
   echo(pg_last_error($link));
   return;
 }    
 else 
 {
     $row = pg_fetch_array($result);
     if ($row['r']==1)
     {
        if (isset($_SESSION['id_sess'])) {
            unset($_SESSION['id_sess']);  // when we use this id_sess?
            session_destroy();
        }
        /*
        if (!($sys_link = pg_connect($app_maint_cstr))) 
        {
            echo_result(2,"Connection Error 3: " . pg_last_error($sys_link));
            return;
        }
          */                      
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

          //setcookie("fizabon_app_base",$base);           
          //setcookie("fizabon_app_user",$id_user);
        
          //echo_result(-1,'Вхід виконано!');
         // return;
          
          start_mpage('Вхід'); 
          head_addrpage();

                 
          if(( $book!='')&&($code!=''))
          {
              $where = " book = '$book' and code = '$code' ";
          }
          
          if($index_accnt!='')
          {
              $where = " index_accnt = $index_accnt ";
          }

          $SQL = "select acc.id, acc.book, acc.code, acc.note, acc.archive,
            (c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon,
            (adr.street||' '||
            (coalesce('буд.'||(acc.addr).house||'','')||
		coalesce('/'||(acc.addr).slash||' ','')|| 
			coalesce(' корп.'||(acc.addr).korp||'','')||
				coalesce(', кв. '||(acc.addr).flat,'')||
					coalesce('/'||(acc.addr).f_slash,''))::varchar
            )::varchar as addr
            from clm_paccnt_tbl as acc 
            join clm_abon_tbl as c on (c.id = acc.id_abon) 
            join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class)
            where $where ";
          
            //echo $SQL;
          
            $result=pg_query($link,$SQL);
            if (!($result)) 
            {
                echo(pg_last_error($link));
                //return;
            }    
            else 
            {
                $row = pg_fetch_array($result);
                $id_paccnt = $row['id'];
                $book =  $row['book'];
                $code =  $row['code'];
                $abon =  $row['abon'];
                $info = $row['book'].'/'.$row['code'].' '.$row['addr'].' - '.$row['abon'];
                $enter_status = 1;
                
                if ($mode==1)
                {
                   $module_name = 'abon_en_paccnt.php';
                }

                if ($mode==2)
                {
                   $module_name = 'abon_en_saldo.php';
                }
                
            }

     echo ("  <script type='text/javascript'>
                var mode = $mode ;
                var enter_status = $enter_status ;
 
             jQuery(function(){ 
                if(enter_status==1)
                {
                      document.paccnt_params.submit();
                }
             
             });
             
            </script>  ");
            
     echo ("</head> <body >");
         
     echo (" <p> Вхід дозволено. </p> ");
          
          
     $redirect_form =  <<<REDIRECT_FORM
   
<DIV style="display:none;">
    <form id="fpaccnt_params" name="paccnt_params" method="post" action="$module_name">
        <input type="text" name="mode" id="pmode" value="0" />
        <input type="text" name="id_paccnt" id="pid_paccnt" value="$id_paccnt" />         
        <input type="text" name="paccnt_info" id="ppaccnt_info" value="$info" />    
        <input type="text" name="paccnt_book" id="ppaccnt_book" value="$book" />                  
        <input type="text" name="paccnt_code" id="ppaccnt_code" value="$code" />                  
        <input type="text" name="paccnt_name" id="ppaccnt_name" value="$abon" />                  
      
    </form>
</DIV>          
REDIRECT_FORM;
          
          echo $redirect_form;
          end_mpage();
          
        }
        else {
           echo(pg_last_error($sys_link));
           return;
        }
         
     }
     else 
     {
         echo('Помилковий пароль!');
         return;
     }
 
 }

}
catch (Exception $e) {

 echo ('Error: '.$e->getMessage());
}


?>