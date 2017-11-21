<?php
set_time_limit(12000); 

header('Content-type: text/html; charset=utf-8');

include_once 'abon_en_func.php';
include_once 'abon_ded_func.php';


session_name("session_kaa");
session_start();

error_reporting(0);

$Link = get_database_link($_SESSION,2);
$session_user = $_SESSION['ses_usr_id'];
session_write_close();

$a_file_name = '';
$a_file_tmp_name = '';
$af_file_name = '';
$af_file_tmp_name = '';

$Query=" select syi_resid_fun()::varchar as res;";
$result = pg_query($Link,$Query) or die("SQL Error: " .pg_last_error($Link) );
$row = pg_fetch_array($result);
$res = $row['res']; 

//sleep(10);
try {
    
  if ($_FILES)
  {
       $p_id_region = sql_field_val('id_region', 'int');
       if ($_FILES['a_file']['error'] <= 0) 
       {
             $name = $_FILES['a_file']['name'];
             $size = $_FILES['a_file']['size'];
             $tmp_name =$_FILES['a_file']['tmp_name'];
             
             $a_file_name = $name;
             $a_file_tmp_name = $tmp_name;
             
             $QE = "delete from rep_upszn_tbl ;";
             $res = pg_query($Link, $QE);
             if (!($res))
             {  
                echo_result(2, pg_last_error($Link));
                return;
             }
           
             $db = dbase_open($tmp_name, 0);

              if ($db) {
                $record_numbers = dbase_numrecords($db);
                $nf  = dbase_numfields($db);
                $column_info = dbase_get_header_info($db);
               // print_r($column_info);
                for ($i = 1; $i <= $record_numbers; $i++) {   
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

                   $QE = "insert into rep_upszn_tbl ($field_names) values ($field_values);";
                   $res = pg_query($Link, $QE);
                    if (!($res))
                    {  
                        echo_result(2, pg_last_error($Link));
                        return;
                    }
                    
                     //echo $field_names;
                     //echo $field_values;
                }
                dbase_close($db); 

              }             
              else
              {
                  echo_result(2, 'Dbf error'.$tmp_name);
              }

              
       }
       //---------------------------------
       if ($_FILES['af_file']['error'] <= 0) 
       {
             $name = $_FILES['af_file']['name'];
             $size = $_FILES['af_file']['size'];
             $tmp_name =$_FILES['af_file']['tmp_name'];
             
             $af_file_name = $name;
             $af_file_tmp_name = $tmp_name;
             
             $QE = "delete from rep_upszn_fam_tbl ;";
             $res = pg_query($Link, $QE);
             if (!($res))
             {  
                echo_result(2, pg_last_error($Link));
                return;
             }
             
             $db = dbase_open($tmp_name, 0);

              if ($db) {
                $record_numbers = dbase_numrecords($db);
                $nf  = dbase_numfields($db);
                $column_info = dbase_get_header_info($db);
               // print_r($column_info);
                for ($i = 1; $i <= $record_numbers; $i++) {   
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

                   $QE = "insert into rep_upszn_fam_tbl ($field_names) values ($field_values);";
                   $res = pg_query($Link, $QE);
                    if (!($res))
                    {  
                        echo_result(2, pg_last_error($Link));
                        return;
                    }
                    
                     //echo $field_names;
                     //echo $field_values;
                }
                //echo_result(-1, 'File updated');
                dbase_close($db); 
              }             
              else
              {
                  echo_result(2, 'Dbf error'.$tmp_name);
              }


       }
       //---------------------------------
       if ($_FILES['tarif_file']['error'] <= 0) 
       {
             $name = $_FILES['tarif_file']['name'];
             $size = $_FILES['tarif_file']['size'];
             $tmp_name =$_FILES['tarif_file']['tmp_name'];
             
             //$af_file_name = $name;
             //$af_file_tmp_name = $tmp_name;
             
             $QE = "delete from rep_upszn_tarif_tbl ;";
             $res = pg_query($Link, $QE);
             if (!($res))
             {  
                echo_result(2, pg_last_error($Link));
                return;
             }
             
             $db = dbase_open($tmp_name, 0);

              if ($db) {
                $record_numbers = dbase_numrecords($db);
                $nf  = dbase_numfields($db);
                $column_info = dbase_get_header_info($db);
               // print_r($column_info);
                for ($i = 1; $i <= $record_numbers; $i++) {   
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

                    $QE = "insert into rep_upszn_tarif_tbl ($field_names) values ($field_values);";
                    $res = pg_query($Link, $QE);
                    if (!($res))
                    {  
                        echo_result(2, pg_last_error($Link));
                        return;
                    }
                    
                     //echo $field_names;
                     //echo $field_values;
                }
                //echo_result(-1, 'File updated');
                dbase_close($db); 
              }             
              else
              {
                  echo_result(2, 'Dbf error'.$tmp_name);
              }


       }

     //-------------------------------------------------------------------------   

    
        $QE = "select rep_upszn_data_fun($p_id_region);";
        $res = pg_query($Link, $QE);
        if (!($res))
        {  
          echo_result(2, pg_last_error($Link));
          return;
        }
           
     //------------------------------------------------------------------------       
     $file_names = '';   
        
     $template_name = '../dbf/a_empty.dbf';
     $dest_name = "../tmp/$a_file_name";
     
     $file_names.=$dest_name;
     
      if (!copy($template_name, $dest_name)) {
           //echo "не удалось скопировать $dest_name";
           echo_result(2,"не удалось скопировать $dest_name");
           return;
      }
      chmod($dest_name, 0777);
      
      $db = dbase_open($dest_name, 2);
      
      if (!($db)) {
          //echo "ошибка открытия файла $dest_name";
          echo_result(2,"ошибка открытия файла $dest_name");
          return;
      }
       
     $QE = "select own_num, ree_num, opp, numb, mark, code, ent_cod, frog, fl_pay, 
       nm_pay, debt, code2_1, code2_2, code2_3, code2_4, code2_5, code2_6, 
       code2_7, code2_8, norm_f_1, norm_f_2, norm_f_3, norm_f_4, norm_f_5, 
       norm_f_6, norm_f_7, norm_f_8, own_num_sr, 
       to_char(dat1, 'YYYYMMDD')::varchar as dat1,
       to_char(dat2, 'YYYYMMDD')::varchar as dat2,
       ozn_prz, 
       to_char(dat_f_1, 'YYYYMMDD')::varchar as dat_f_1,
       to_char(dat_f_2, 'YYYYMMDD')::varchar as dat_f_2,
       to_char(dat_fop_1, 'YYYYMMDD')::varchar as dat_fop_1,
       to_char(dat_fop_2, 'YYYYMMDD')::varchar as dat_fop_2,
       id_raj, sur_nam, f_nam, 
       m_nam, ind_cod, indx, n_name, vul_name, bld_num, corp_num, flat, 
       code3_1, code3_2, code3_3, code3_4, code3_5, code3_6, code3_7, 
       code3_8, opp_serv, reserv1, reserv2
       from rep_upszn_tbl order by id_key;";

     $res_e = pg_query($Link, $QE);

     if ($res_e) {

       while ($row = pg_fetch_array($res_e)) {

        //  $own_num =  iconv("utf-8","cp866", trim($row['own_num']));
        $sur_nam = iconv("utf-8", "cp866", str_replace("і", "i", str_replace("І", "I", trim($row['sur_nam']))));
        $f_nam = iconv("utf-8", "cp866", str_replace("і", "i", str_replace("І", "I", trim($row['f_nam']))));
        $m_nam = iconv("utf-8", "cp866", str_replace("і", "i", str_replace("І", "I", trim($row['m_nam']))));
        $n_name = iconv("utf-8", "cp866", str_replace("і", "i", str_replace("І", "I", trim($row['n_name']))));
        $vul_name = iconv("utf-8", "cp866", str_replace("і", "i", str_replace("І", "I", trim($row['vul_name']))));
        $own_num_sr = iconv("utf-8", "cp866", str_replace("і", "i", str_replace("І", "I", trim($row['own_num_sr']))));
        
        $bld_num = iconv("utf-8", "cp866",  trim($row['bld_num']));
        $numb = intval($row['numb']);
        $frog = floatval($row['frog']);
        $fl_pay = floatval($row['fl_pay']);
        $nm_pay = floatval($row['nm_pay']);
        $debt = floatval($row['debt']);
        $norm_f_1 = floatval($row['norm_f_1']);
        $norm_f_2 = floatval($row['norm_f_2']);
        $norm_f_3 = floatval($row['norm_f_3']);
        $norm_f_4 = floatval($row['norm_f_4']);
        $norm_f_5 = floatval($row['norm_f_5']);
        $norm_f_6 = floatval($row['norm_f_6']);
        $norm_f_7 = floatval($row['norm_f_7']);
        $norm_f_8 = floatval($row['norm_f_8']);

        // $n_doc =  iconv("utf-8","cp866", trim($row['n_doc']));
        // $bookcode =  iconv("utf-8","cp866", trim($row['bookcode']));

        dbase_add_record($db, array(
            $row['own_num'],
            $row['ree_num'],
            $row['opp'],
            $numb,
            $row['mark'],
            $row['code'],
            $row['ent_cod'],
            $frog,
            $fl_pay,
            $nm_pay,
            $debt,
            $row['code2_1'],
            $row['code2_2'],
            $row['code2_3'],
            $row['code2_4'],
            $row['code2_5'],
            $row['code2_6'],
            $row['code2_7'],
            $row['code2_8'],
            $norm_f_1,
            $norm_f_2,
            $norm_f_3,
            $norm_f_4,
            $norm_f_5,
            $norm_f_6,
            $norm_f_7,
            $norm_f_8,
            $own_num_sr,
            $row['dat1'],
            $row['dat2'],
            $row['ozn_prz'],
            $row['dat_f_1'],
            $row['dat_f_2'],
            $row['dat_fop_1'],
            $row['dat_fop_2'],
            $row['id_raj'],
            $sur_nam,
            $f_nam,
            $m_nam,
            $row['ind_cod'],
            $row['indx'],
            $n_name,
            $vul_name,
            $bld_num,
            $row['corp_num'],
            $row['flat'],
            $row['code3_1'],
            $row['code3_2'],
            $row['code3_3'],
            $row['code3_4'],
            $row['code3_5'],
            $row['code3_6'],
            $row['code3_7'],
            $row['code3_8'],
            $row['opp_serv'],
            $row['reserv1'],
            $row['reserv2']
        ));
      }
    }

    dbase_close($db);

    //echo_result(-1, $dest_name);
       
    //------------------------------
    
     $template_name = '../dbf/af_empty.dbf';
     $dest_name = "../tmp/$af_file_name";
     
     $file_names.=" ".$dest_name;
     
      if (!copy($template_name, $dest_name)) {
           //echo "не удалось скопировать $dest_name";
           echo_result(2,"не удалось скопировать $dest_name");
           return;
      }
      chmod($dest_name, 0777);
      
      $db = dbase_open($dest_name, 2);
      
      if (!($db)) {
          //echo "ошибка открытия файла $dest_name";
          echo_result(2,"ошибка открытия файла $dest_name");
          return;
      }
       
       
     $QE = "SELECT own_num, ree_num, own_num_sr, fam_num, sur_nam, f_nam, m_nam, 
       ind_cod, psp_ser, psp_num, ozn, cm_area, heat_area, own_frm, 
       hostel, priv_cat, ord_fam, ozn_sq_add, ozn_abs, reserv1, reserv2, 
       id_key, id_paccnt, id_lgt
       FROM rep_upszn_fam_tbl 
       order by id_key;";

     $res_e = pg_query($Link, $QE);

     if ($res_e) {

       while ($row = pg_fetch_array($res_e)) {

        $sur_nam = iconv("utf-8", "cp866", str_replace("і", "i", str_replace("І", "I", trim($row['sur_nam']))));
        $f_nam = iconv("utf-8", "cp866", str_replace("і", "i", str_replace("І", "I", trim($row['f_nam']))));
        $m_nam = iconv("utf-8", "cp866", str_replace("і", "i", str_replace("І", "I", trim($row['m_nam']))));
        $psp_ser = iconv("utf-8", "cp866", str_replace("і", "i", str_replace("І", "I", trim($row['psp_ser']))));
        $own_num_sr = iconv("utf-8", "cp866", str_replace("і", "i", str_replace("І", "I", trim($row['own_num_sr']))));
        $own_frm = intval($row['own_frm']);
        $hostel = intval($row['hostel']);
        $priv_cat = intval($row['priv_cat']);
        $ord_fam = intval($row['ord_fam']);
        $ozn_sq_add = intval($row['ozn_sq_add']);
        $ozn_abs = intval($row['ozn_abs']);
        $cm_area = floatval($row['cm_area']);
        $heat_area = floatval($row['heat_area']);
        $reserv1 = floatval($row['reserv1']);

       

        dbase_add_record($db, array(
            $row['own_num'],
            $row['ree_num'],
            $own_num_sr,
            $row['fam_num'],
            $sur_nam,
            $f_nam,
            $m_nam,
            $row['ind_cod'],
            $psp_ser,
            $row['psp_num'],
            $row['ozn'],
            $cm_area,
            $heat_area,
            $own_frm,
            $hostel,
            $priv_cat,
            $ord_fam,
            $ozn_sq_add,
            $ozn_abs,
            $reserv1,
            $row['reserv2']
        ));
      }
    }

    dbase_close($db);
   //--------------------------------------------------------------------
    
    
   $zip_name = '/home/local/replicat/'.str_replace(".dbf", ".zip",strtolower ($a_file_name));
   if ($file_names!='')
   {
                
     if ($res=='310')  
     {
        $command = "7z a $zip_name $file_names ";
        //$command = "zip -9 -j $zip_name $file_names ";
     }
     else
     {
        $command = "zip -9 -j $zip_name $file_names ";
     }
     
     $out = shell_exec($command);
     sleep(10);
   }
    
    
    echo_result(-1, $zip_name,0,$command);
    
    
    //   echo_result(-1, 'File updated');
       
  }

} catch (Exception $e) {

  echo echo_result(1, 'Error: ' . $e->getMessage());
}
?>


