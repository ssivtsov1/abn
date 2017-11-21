<?php
 
$QE = '';
$dir_name ='';

function LoadRtfFile($fullfilename,$filename,$Lnk) {

  global $dir_name;
  
  //$local_name = iconv("utf-8", "windows-1251",str_replace('../tmp/smart/','',$fullfilename));
  $local_name = str_replace('../tmp/smart/','',$fullfilename);
  $d_name = str_replace("$dir_name/",'',$local_name);
  $d_name = str_replace("/$filename",'',$d_name);

//  echo '--';
//  echo $d_name;
//  echo '--';
//  echo $filename;

  list($day, $month, $year) = split('[/.-]', $dir_name);
  
  if(($day!='') && ($month!='')&& ($year!=''))
    {$date_ind = "'$year-$month-$day'"; }
  else {
    $date_ind = 'null';
  }
  
  $QE = "insert into ind_smart_files_tbl(id , name_file, status , date_ind ) values (DEFAULT,'{$local_name}',1, $date_ind) returning id;";

  $res = pg_query($Lnk, $QE);
  if (!($res)) {
      echo_result(2,pg_last_error($Lnk) );
      return;
  }
  $row = pg_fetch_array($res);
  $id_file = $row['id'];
 
  $QE = "update ind_smart_files_tbl set id_house = h.id  
        from ind_smart_house_tbl as h where h.path = '{$d_name}' and  h.name = '{$filename}' 
        and ind_smart_files_tbl.id = $id_file";  
  $res = pg_query($Lnk, $QE);
  if (!($res)) {
      echo_result(2,pg_last_error($Lnk) );
      return;
  }

  $QE = "set client_encoding = 'win'";   
  
  $res = pg_query($Lnk, $QE);
  if (!($res)) {
      echo_result(2,pg_last_error($Lnk) );
      return;
  }
  
  //echo($fullfilename);  
  $fh = fopen($fullfilename,'r');
  $cnt = 0;  
  while ($line = fgets($fh)) {

      //{   Точка учета\cell    Канал\cell    Фаза\cell    Зона A\cell    Зона B\cell    Зона C\cell    Зона D\cell    Дата\cell }
    //echo($line);
    $l = trim($line) ;

    if((substr_count($l, '\cell')==8)&&($l[0]=='{')&&($l[strlen($l)-1]=='}'))
    {
        //echo($l);
        $l = str_replace('\cell',':',$l);
        $l = str_replace('{','',$l);
        $l = str_replace('}','',$l);
        $l = trim($l,': ') ;
        
        list($point, $chanal, $phase, $zone_a,$zone_b,$zone_c,$zone_d,$date) = split(':', $l);
        
        if ($cnt >0)
        {

           list($day, $month, $year) = split('[/.-]', trim($date));
  
           if(($day!='') && ($month!='')&& ($year!=''))
              {$date_ind = "'$year-$month-$day'"; }
           else {
              $date_ind = 'null';
           }
            
         // $chanal = iconv("windows-1251", "utf-8", $chanal); 
          //$phase =  trim (iconv("windows-1251", "utf-8", $phase)); 
          $chanal = trim ($chanal); 
          $phase =  trim($phase);
           
          $zone_a = str_replace(' ','',$zone_a);
          $zone_b = str_replace(' ','',$zone_b);
          $zone_c = str_replace(' ','',$zone_c);
          $zone_d = str_replace(' ','',$zone_d);
          
          $QE = "insert into ind_smart_indics_tbl(id_smart, tu, ind_a, ind_b, ind_c, ind_d, ind_date ) 
             values ($id_file, '$point', $zone_a,$zone_b,$zone_c,$zone_d,$date_ind );";

          $res = pg_query($Lnk, $QE);
          if (!($res)) {
             echo_result(2,pg_last_error($Lnk) );
             
             $QE = "set client_encoding = 'utf8'";  
             $res = pg_query($Lnk, $QE);
             
             return;
           }
           //echo($point);            
        }
        $cnt++;
        
        //echo($chanal);
        //echo($date);
        //echo('---');
    }
  }
  fclose($fh); 
  
  $QE = "set client_encoding = 'utf8'";  
  
  $res = pg_query($Lnk, $QE);
  if (!($res)) {
      echo_result(2,pg_last_error($Lnk) );
      return;
  }
  
  return 0;
} 


function ScanDirectoryRec($dir,$Lnk) {
  
   //$result = array();

   $cdir = scandir($dir);
   foreach ($cdir as $key => $value)
   {
      if (!in_array($value,array(".","..")))
      {
         if (is_dir($dir . DIRECTORY_SEPARATOR . $value))
         {
            ScanDirectoryRec($dir . DIRECTORY_SEPARATOR . $value,$Lnk);
         }
         else
         {
            //$result[] = $value;
           //  echo $dir . DIRECTORY_SEPARATOR .$value;
             LoadRtfFile($dir . DIRECTORY_SEPARATOR .$value, $value, $Lnk);
         }
      }
   }
  
   //return $result;
   return 0;
} 

function recursiveDelete($str) {
    if (is_file($str)) {
        return @unlink($str);
    }
    elseif (is_dir($str)) {
        $scan = glob(rtrim($str,'/').'/*');
        foreach($scan as $index=>$path) {
            recursiveDelete($path);
        }
        return @rmdir($str);
    }
}

header('Content-type: text/html; charset=utf-8');

include_once 'abon_en_func.php';
include_once 'abon_ded_func.php';


session_name("session_kaa");
session_start();

error_reporting(0);

$Link = get_database_link($_SESSION, 2);
$session_user = $_SESSION['ses_usr_id'];
//sleep(10);
session_write_close();

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
            if ($_FILES['smart_file']['error'] <= 0) {
                $name = $_FILES['smart_file']['name'];
                $size = $_FILES['smart_file']['size'];
                $tmp_name = $_FILES['smart_file']['tmp_name'];


            if (is_uploaded_file($tmp_name)) {
                
                $scan = glob(rtrim("../tmp/smart/",'/').'/*');
                foreach($scan as $index=>$path) {
                    recursiveDelete($path);
                };
                
                // Если файл загружен успешно, перемещаем его из временной директории в конечную
                $b = move_uploaded_file($tmp_name, "../tmp/smart/".$name);
                if($b == 1){
                    //if(file_exists('../tmp/smart_unzip')){
                    //echo 'ok';
                    //exec('./backup/rest.sh');
                    $info = new SplFileInfo($name); 
                    
                    if (($info->getExtension()=='zip')||($info->getExtension()=='7z'))
                    {
                        
                       if(file_exists('../tmp/smart_unzip')){

                         $out = shell_exec("../tmp/smart_unzip $name");
                         sleep(1);
                         //echo $out;
                       
                         $dir_name = $info->getBasename(".".$info->getExtension());
                         //$cdir  = scandir("../tmp/smart/".$dir_name);
                         
                         ScanDirectoryRec("../tmp/smart/".$dir_name,$Link);
                         
                         echo_result(-1, 'Data uploaded');
                       }
                        
                    }
                    if ($info->getExtension()=='rtf')
                    {
                       LoadRtfFile("../tmp/smart/".$name, $name, $Link); 
                       echo_result(-1, 'Data uploaded');
                    }
                    
                }
                }
            } else {
                echo("Ошибка загрузки файла");
            }

        }
    }
    if ($oper == "apply") {

        
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
        
        
        $p_mmgg = sql_field_val('mmgg', 'mmgg');
        $QE = "select ind_smart_apply_fun($p_mmgg,$session_user);";
        $res = pg_query($Link, $QE);
        if (!($res)) {
              echo_result(2, pg_last_error($Link));
              return;
        }
        echo_result(-1, 'Data parsed');
        
    }    
    
} catch (Exception $e) {

    echo echo_result(1, 'Error: ' . $e->getMessage());
}
?>