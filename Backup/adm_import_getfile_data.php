<?php

header('Content-type: text/html; charset=utf-8');
 
require 'abon_en_func.php'; 
require 'abon_ded_func.php';



session_name("session_kaa");
session_start();

error_reporting(0);

$Link = get_database_link($_SESSION,1);
$session_user = $_SESSION['ses_usr_id'];
session_write_close();


try {
  if (isset($_POST['submitButton'])) {
        $oper = $_POST['submitButton'];
    } else {
        if (isset($_POST['oper'])) {
            $oper = $_POST['oper'];
        }
    }    

//------------------------------------------------------
    if ($oper == "get_pay") {

        $p_dt_file = sql_field_val('dt_file', 'date');

        $QE = '';
        if ($p_dt_file != 'null') {
            
            $QE = "select expimp_call_pay ( $p_dt_file ) as file_name;";
        }
        
        if ($QE != '')
        {
            $res_e = pg_query($Link, $QE);
 
            if ($res_e) {
                $row = pg_fetch_array($res_e);
                $file_name = $row['file_name'];
                
                echo_result(-1, $file_name);

                
            } else {
                // pg_query($Link, "rollback");
                echo_result(2, 'Помилка формування файлу!');
            }
        }
    }
//------------------------------------------------------
    if ($oper == "get_bank") {

        $p_dt_file = sql_field_val('dt_file', 'mmgg');

        $QE = '';
        if ($p_dt_file != 'null') {
            
            $QE = "select expimp_bank ( $p_dt_file ) as file_name;";
        }
        
        if ($QE != '')
        {
            $res_e = pg_query($Link, $QE);
 
            if ($res_e) {
                $row = pg_fetch_array($res_e);
                $file_name = $row['file_name'];
                
                echo_result(-1, $file_name);

                
            } else {
                // pg_query($Link, "rollback");
                echo_result(2, 'Помилка формування файлу!');
            }
        }
    }
//------------------------------------------------------
    if (($oper == "get_zon")||($oper == "get_zon_cher_res")||($oper == "get_zon_slav")
         ||($oper == "get_zon_mena")||($oper == "get_zon_sosn")    ) {

        $p_dt_file = sql_field_val('dt_file', 'mmgg');

        $QE = '';
        
        $res='null';
        if ($oper == "get_zon_cher_res") $res = '320';
        if ($oper == "get_zon_slav"    ) $res = '330';
        
        if ($oper == "get_zon_mena") $res = '200';
        if ($oper == "get_zon_sosn") $res = '260';
        
        
        if ($p_dt_file != 'null') {
            
            $QE = "select seb_abon_comp_fun ( $p_dt_file , $res) as file_name;";
        }
        
        if ($QE != '')
        {
            $res_e = pg_query($Link, $QE);
 
            if ($res_e) {
                $row = pg_fetch_array($res_e);
                $file_name = $row['file_name'];
                
                echo_result(-1, $file_name);

                
            } else {
                // pg_query($Link, "rollback");
                echo_result(2, 'Помилка формування файлу!');
            }
        }
    }
    
    //------------------------------------------------------
    if ($oper == "get_portmone") {

        $p_dt_file = sql_field_val('dt_file', 'mmgg');

      
        $QE = '';
        if ($p_dt_file != 'null') {
            
            $QE = "select expimp_portmone( $p_dt_file ) as file_name;";
        }
        
        if ($QE != '')
        {
            $res_e = pg_query($Link, $QE);
 
            if ($res_e) {
                $row = pg_fetch_array($res_e);
                $file_name = $row['file_name'];
                
                echo_result(-1, $file_name);

                
            } else {
                // pg_query($Link, "rollback");
                echo_result(2, 'Помилка формування файлу!');
            }
        }
    }

//------------------------------------------------------
    if ($oper == "get_familybank") {

        $p_dt_file = sql_field_val('dt_file', 'mmgg');

      
        $QE = '';
        if ($p_dt_file != 'null') {
            
            $QE = "select expimp_familybank( $p_dt_file ) as file_name;";
        }
        
        if ($QE != '')
        {
            $res_e = pg_query($Link, $QE);
 
            if ($res_e) {
                $row = pg_fetch_array($res_e);
                $file_name = $row['file_name'];
                
                echo_result(-1, $file_name);

                
            } else {
                // pg_query($Link, "rollback");
                echo_result(2, 'Помилка формування файлу!');
            }
        }
    }

//------------------------------------------------------
    if ($oper == "get_allbank") {

        $p_dt_file = sql_field_val('dt_file', 'mmgg');

      
        $QE = '';
        if ($p_dt_file != 'null') {
            
            $QE = "select expimp_allbank( $p_dt_file, null ) as file_name;";
        }
        
        if ($QE != '')
        {
            $res_e = pg_query($Link, $QE);
 
            if ($res_e) {
                $row = pg_fetch_array($res_e);
                $file_name = $row['file_name'];
                
                echo_result(-1, $file_name);

                
            } else {
                // pg_query($Link, "rollback");
                echo_result(2, 'Помилка формування файлу!');
            }
        }
    }

//------------------------------------------------------
    if (($oper == "get_f10")||
            ($oper == "get_f10_neg_m")||($oper == "get_f10_neg_s")||
            ($oper == "get_f10_pril_m")||($oper == "get_f10_pril_s")||
            ($oper == "get_f10_cher_res")||($oper == "get_f10_slav")||  
            ($oper == "get_f10_mena")||($oper == "get_f10_sosn")    ) {

        $p_dt_file = sql_field_val('dt_file', 'mmgg');
        
        $id_neg_m = 39826;
        $id_neg_s = 40854;
        
        $id_pril_m = 39827;
        $id_pril_s = 41141;
        
        $id_region = 'null';

        if ($oper == "get_f10_neg_m") $id_region = $id_neg_m;
        if ($oper == "get_f10_neg_s") $id_region = $id_neg_s;

        if ($oper == "get_f10_pril_m") $id_region = $id_pril_m;
        if ($oper == "get_f10_pril_s") $id_region = $id_pril_s;
        
        if ($oper == "get_f10_cher_res") $id_region = -1;
        if ($oper == "get_f10_slav")     $id_region = -2;

        if ($oper == "get_f10_mena") $id_region = -1;
        if ($oper == "get_f10_sosn") $id_region = -2;
        

        $QE = '';
        if ($p_dt_file != 'null') {
            
            $QE = "select seb_f10_fun ( $p_dt_file,$id_region ) as file_name;";
        }
        
        if ($QE != '')
        {
            $res_e = pg_query($Link, $QE);
 
            if ($res_e) {
                $row = pg_fetch_array($res_e);
                $file_name = $row['file_name'];
                
                echo_result(-1, $file_name);

                
            } else {
                // pg_query($Link, "rollback");
                echo_result(2, 'Помилка формування файлу!');
            }
        }
    }

//---------------------------------------------------------   
    if (($oper == "get_zvit")||($oper == "get_zvit_cher_res")||($oper == "get_zvit_slav")
        ||($oper == "get_zvit_mena")||($oper == "get_zvit_sosn")   ) {

        $p_dt_file = sql_field_val('dt_file', 'mmgg');

        $QE = '';
        
        $res='null';
        if ($oper == "get_zvit_cher_res") $res = '320';
        if ($oper == "get_zvit_slav"    ) $res = '330';

        if ($oper == "get_zvit_mena") $res = '200';
        if ($oper == "get_zvit_sosn") $res = '260';
        
        
        $SQL = "select crt_ttbl();";
        $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
        
        if ($p_dt_file != 'null') {
            
            $QE = "select seb_zvit_fun ( $p_dt_file , $res) as file_name;";
        }
        
        if ($QE != '')
        {
            $res_e = pg_query($Link, $QE);
 
            if ($res_e) {
                $row = pg_fetch_array($res_e);
                $file_name = $row['file_name'];
                
                echo_result(-1, $file_name);

                
            } else {
                // pg_query($Link, "rollback");
                echo_result(2, 'Помилка формування файлу!');
            }
        }
    }
//---------------------------------------------------------   
    if ($oper == "get_periods") {

        $p_dt_file = sql_field_val('dt_file', 'mmgg');
        
        if ($p_dt_file == 'null') {
            echo echo_result(1, 'Error: Вкажіть дату!');            
            return;
        }
        
/*
        $SQL = "select count(*) as cnt from seb_saldo where mmgg = $p_dt_file::date;";
        $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
        if ($result) {
            $row = pg_fetch_array($result);
            if ($row['cnt']>0) 
            {
              $SQL = "delete from seb_saldo_tmp;";
              $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
                
              $SQL = "insert into seb_saldo_tmp select * from seb_saldo where mmgg = $p_dt_file::date and id_pref = 10 ;";
              $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
            }
            else
            {
              $SQL = "select seb_saldo($p_mmgg,0,null);";
              $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
            }
        }
*/           
        $SQL = "select crt_ttbl();";
        $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
        
        
        $QE = "select rep_month_saldo_fun ( $p_dt_file, 1 ) as file_name;";
        
        if ($QE != '')
        {
            $res_e = pg_query($Link, $QE);
 
            if ($res_e) {
                $row = pg_fetch_array($res_e);
                $file_name = $row['file_name'];
                
                echo_result(-1, $file_name);

                
            } else {
                // pg_query($Link, "rollback");
                echo_result(2, "Помилка формування файлу! $QE ".pg_last_error($Link));
            }
        }
    }
//---------------------------------------------------------   
    if ($oper == "get_poks") {

        $p_dt_file = sql_field_val('dt_file', 'mmgg');

        $QE = '';
        if ($p_dt_file != 'null') {
 
          //  $SQL = "set client_encoding = 'WIN';";
          //  $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
             
            
            $QE = "select expimp_demand ( $p_dt_file ) as file_name;";
            
            
        }
        
        if ($QE != '')
        {
            $res_e = pg_query($Link, $QE);
 
            if ($res_e) {
                $row = pg_fetch_array($res_e);
                $file_name = $row['file_name'];
                
                echo_result(-1, $file_name);

                
            } else {
                // pg_query($Link, "rollback");
                echo_result(2, 'Помилка формування файлу!');
            }
        }
    }

//---------------------------------------------------------   
    if (($oper == "get_call_poks")||($oper == "get_call_poks_cher_res")||($oper == "get_call_poks_slav")
            ||($oper == "get_call_poks_mena")||($oper == "get_call_poks_sosn")
            ) 
    {

        $p_dt_file = sql_field_val('dt_file', 'mmgg');

        $QE = '';
        
        $res='null';
        if ($oper == "get_call_poks_cher_res") $res = '320';
        if ($oper == "get_call_poks_slav"    ) $res = '330';

        if ($oper == "get_call_poks_mena") $res = '200';
        if ($oper == "get_call_poks_sosn") $res = '260';
        
        if ($p_dt_file != 'null') {
 
          //  $SQL = "set client_encoding = 'WIN';";
          //  $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
            
            $QE = "select expimp_call_poks ( $p_dt_file, $res ) as file_name;";
            
        }
        
        if ($QE != '')
        {
            $res_e = pg_query($Link, $QE);
 
            if ($res_e) {
                $row = pg_fetch_array($res_e);
                $file_name = $row['file_name'];
                
                echo_result(-1, $file_name);

                
            } else {
                // pg_query($Link, "rollback");
                echo_result(2, 'Помилка формування файлу!');
            }
        }
    }

//---------------------------------------------------------   
    if (($oper == "get_win_poks")||($oper == "get_win_poks_cher_res")||($oper == "get_win_poks_slav")
            ||($oper == "get_win_poks_mena")||($oper == "get_win_poks_sosn")
            ) 
    {

        $p_dt_file = sql_field_val('dt_file', 'mmgg');

        $QE = '';
        
        $res='null';
        if ($oper == "get_win_poks_cher_res") $res = '320';
        if ($oper == "get_win_poks_slav"    ) $res = '330';

        if ($oper == "get_win_poks_mena") $res = '200';
        if ($oper == "get_win_poks_sosn") $res = '260';
        
        if ($p_dt_file != 'null') {
 
            $SQL = "set client_encoding = 'WIN';";
            $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
            
            $QE = "select expimp_call_poks ( $p_dt_file, $res ) as file_name;";
            
        }
        
        if ($QE != '')
        {
            $res_e = pg_query($Link, $QE);
 
            if ($res_e) {
                $row = pg_fetch_array($res_e);
                $file_name = $row['file_name'];
                
                echo_result(-1, $file_name);

                
            } else {
                // pg_query($Link, "rollback");
                echo_result(2, 'Помилка формування файлу!');
            }
        }
    }

//---------------------------------------------------------   

    if (($oper == "get_call_center")||($oper == "get_call_center_cher_res")||($oper == "get_call_center_slav")
            ||($oper == "get_call_center_mena")||($oper == "get_call_center_sosn")
            ) 
    {

        $p_dt_file = sql_field_val('dt_file', 'mmgg');

        $QE = '';
        
        $Query=" select syi_resid_fun()::varchar as res;";
        $result = pg_query($Link,$Query) or die("SQL Error: " .pg_last_error($Link) );
        $row = pg_fetch_array($result);
        $res = $row['res'];  
        
        if ($oper == "get_call_center_cher_res") $res = '320';
        if ($oper == "get_call_center_slav"    ) $res = '330';

        if ($oper == "get_call_center_mena") $res = '200';
        if ($oper == "get_call_center_sosn") $res = '260';
        
        if ($p_dt_file != 'null') {
 
            $SQL = "select crt_ttbl();";
            $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
            
            $QE = "select exp_imp_call ( $p_dt_file, '{$res}' ) ;";
            //echo $QE;
            $res_e = pg_query($Link, $QE);
 
            if (!$res_e) {
                echo_result(2, 'Помилка формування файлу!');                
                return;
            }
                
            $file_names='';
            
            $QE = "select name_file from call_center_tmp  ;";            
            $res_e = pg_query($Link, $QE);
            
            while($row = pg_fetch_array($res_e)) {
                    $file_names.= $row['name_file'].' ';
            }
            
            $zip_name = '/home/local/replicat/'.$res.'cdcall.zip';
            if ($file_names!='')
            {
                
              if ($res=='310')  
              {
                $out = shell_exec("7z a $zip_name $file_names ");
              }
              else
              {
                $out = shell_exec("zip -9 -j $zip_name $file_names ");
              }
              sleep(10);
                
            }
            
            echo_result(-1, $zip_name);
                
        }
    }

//---------------------------------------------------------   
    
    
} catch (Exception $e) {

    echo echo_result(1, 'Error: ' . $e->getMessage());
}
?>