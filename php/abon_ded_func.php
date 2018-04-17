<?php
function sql_field_val($fname,$ftype) { 
    
  $result='';
  
  if (isset($_POST[$fname])&& (trim($_POST[$fname])!='')){
      $result=$_POST[$fname]; 

      if ($ftype == 'string') 
          {
            $result=str_replace("'", "''", $result);
            $result=trim($result,' ');
            $result = "'$result'";
          }
      if ($ftype == 'record') 
          {
          
          $array= explode(",", trim($result,')('));
          
          foreach ($array as &$value) {
            if ($value =='')  $value ='null'; 
            else $value = "'$value'";
          }          
          unset($value);          
          
          $result = 'ROW('.implode(',',$array).')';
          
          }
      
          
      if ($ftype == 'numeric') {
            $result=str_replace(',', '.', $result);
        }
          
      if ($ftype == 'int') {
          
        if($result=='on')  {$result = 1;}
        if($result=='On')  {$result = 1;}
        if($result=='Yes') {$result = 1;}
        if($result=='true'){$result = 1;}
        if($result=='off') {$result = 0;}
        if($result=='Off') {$result = 0;}
        if($result=='No')  {$result = 0;}
        if($result=='false') {$result = 0;}
        }

      if ($ftype == 'bool') {
          
        if($result=='on') {$result = 'true';}
        if($result=='On') {$result = 'true';}
        if($result=='Yes'){$result = 'true';}
        if($result=='1')  {$result = 'true';}        
        if($result=='off'){$result = 'false';}
        if($result=='Off'){$result = 'false';}
        if($result=='No') {$result = 'false';}
        if($result=='0')  {$result = 'false';}        
        }
        
      if ($ftype == 'date') {
        //Return ($result);        
        //$date = DateTime::createFromFormat('d.m.Y', $result);
        //$result= $date->format($date, 'Y-m-d');          
        if ((trim($result)=='--')||($result=='\u00a0')||(trim($result)=='__.__.____')) 
            {$result = 'null';}
        else{    
        list($day, $month, $year) = split('[/.-]', $result);
        if(($day!='') && ($month!='')&& ($year!=''))
            {$result = "'$year-$month-$day'"; }
        else
            {$result = 'null';}
        }
      }

      if ($ftype == 'mmgg') {
        if ((trim($result)=='--')||($result=='\u00a0')||(trim($result)=='__.__.____')) 
            {$result = 'null';}
        else{    
        list($day, $month, $year) = split('[/.-]', $result);
        if(($day!='') && ($month!='')&& ($year!=''))
            {$result = "'$year-$month-01'"; }
        else
            {$result = 'null';}
        }
      }
      
      if ($ftype == 'datetime') {
        if ((trim($result)=='--')||($result=='\u00a0')||(trim($result)=='__.__.____')) 
            {$result = 'null';}
        else{    
            $parts=explode(" ",$result);
            $date = $parts[0];
            $time = $parts[1];
            list($day, $month, $year ) = split('[/.-]', $date);
            
            if(($day!='') && ($month!='')&& ($year!=''))
              {$result = "'$year-$month-$day $time'"; }
            else
              {$result = 'null';}
        }
      }
      
        
  } else {$result = 'null';}

  Return ($result);

};

function dbf_field_val($fval,$ftype) { 
    
  $result='';
  
 // if (isset($_POST[$fname])&& (trim($_POST[$fname])!='')){
 //     $result=$_POST[$fname]; 

      if ($ftype == 'character') 
          {
            $fval_utf =  iconv("cp866","utf-8", str_replace("\\","\\\\",str_replace("'","''",trim($fval))));
            $result = "'$fval_utf'";
          }

      if ($ftype == 'number') {$result = "$fval";}

        
      if ($ftype == 'date') {
        //Return ($result);        
        //$date = DateTime::createFromFormat('d.m.Y', $result); 
        //$result= $date->format($date, 'Y-m-d');          
/*
        if (trim($fval)=='') 
            {$result = 'null';}
        else{    
        list($day, $month, $year) = split('[/.-]', $result);
        if(($day!='') && ($month!='')&& ($year!=''))
            {$result = "'$year-$month-$day'"; }
        else
            {$result = 'null';}
        }
 */
            $result = "$fval";
            if ($result=='') {$result = 'null';}
            else
            {
                $day = substr($result,6,2);
                $month= substr($result,4,2);
                $year= substr($result,0,4);
                
                if(($day!='') && ($month!='')&& ($year!=''))
                    {$result = "'$year-$month-$day'"; }
                else
                    {$result = 'null';}
                        
            }
            
      }

      //if (($result=='')&&($ftype!='character'))
      if ($result=='')   {$result = 'null';}
      if ($result=="''")   {$result = 'null';}
      if ($result=="'    -  -  '")   {$result = 'null';}

  //} else {$result = 'null';}

  Return ($result);

};

function echo_result($errcode,$errstr,$id=0,$add_data='') {
    
    $arr = array('errcode' => $errcode, 'errstr' => $errstr, 'id' => $id, 'add_data' => $add_data);
    //$convertedText = mb_convert_encoding($errstr, 'cp1251', mb_detect_encoding($errstr));
    //echo $convertedText;
    echo json_encode($arr);
};

function DbTableSelList($lnk,$table,$fid,$fname) {
// для лукапа из jqGrid    
    $SQL = "select $fid, $fname from $table Order by $fid ";

    $result = pg_query($lnk,$SQL);

    
    if (!$result) { $res_str = "";}
    else {
    
    $res_str = "'null: ;";
    $i =0;
    while($row = pg_fetch_array($result)) {
       $i++;
       if ($i>1){$res_str.=';';};
       $res_str.= $row[$fid].':'.str_replace("'", '`', $row[$fname]);
    }
    $res_str.= "'";    
 } 
 return $res_str;
    
};

function DbTableSelect($lnk,$table,$fid,$fname) {
    // для лукапа из формы    
    $SQL = "select $fid, $fname from $table Order by $fid ";
         
    $result = pg_query($lnk,$SQL);
    //echo pg_last_error();   
    if (!$result) { $res_str = "";}
    else {
    
    $res_str = "";
    $i =0;
    $res_str.= '<option value="null">  </option>';    
    while($row = pg_fetch_array($result)) {
       $i++;
       
       $res_str.= '<option value="'.$row[$fid].'">'.$row[$fname].'</option>';
    }
 } 
 return $res_str;
    
};

function DbGetFieldsArray($lnk,$table) {
    
    $SQL = "select column_name, data_type from information_schema.columns where table_name = '$table'; ";

    $result = pg_query($lnk,$SQL);

    $array = array();
    
    if ($result) 
    {
    
        while($row = pg_fetch_array($result)) 
        {
            $array[$row['column_name']]= array('f_name'=>$row['column_name'],'f_type'=>$row['data_type']);
        }
       

    } 
    return $array;
    
};

// Построение выражения WHERE (функция подкорректирована ЦЭК)
function DbBuildWhere($_POST_val,$farrey, $fparse_all=0,$farray_exeption) {
    
$qW = ' WHERE ';
//var_dump($farray_exeption);

$stringTypes = array('text', 'character varying','character','string','char');

if ((((isset($_POST_val['_search']) && $_POST_val['_search'] == 'true')|| 
    (isset($_POST_val['user_search']) && $_POST_val['user_search'] == 'true')))||($fparse_all==1)) 
{    
    
    //throw new Exception($searchData);
    //throw new Exception(json_encode($_POST));
    $firstElem = true;    
    
    foreach ($_POST_val as $k=>$v) {
      
        $v = trim($v);
        
        if (array_key_exists($k, $farrey)) {
          
          if ($v =='null')  continue;
              
          if (!$firstElem) {
              $qW .= ' AND ';
          }
          else {
            $firstElem = false;
          }
            
          $qW .=$farrey[$k]['f_name'];
          if (in_array($farrey[$k]['f_type'],$stringTypes))
          {
              if ($farrey[$k]['f_type']=='char')
                $qW .=" = '$v' ";
              else
              if(in_array($farrey[$k]['f_name'],$farray_exeption))
                   $qW .=" = '$v' ";
              else
                    $qW .=" ilike '%$v%' ";
          }
          else
          {
              if ($farrey[$k]['f_type']=='date')
              {
                  
                $day = substr($v,0,2);
                $month= substr($v,3,2);
                $year= substr($v,6,4);
                
                if(($day!='') && ($month!='')&& ($year!=''))
                {
                    $v = "'$year-$month-$day'"; 
                    $qW .=" = $v ";              
                } 
              }
              else
                $qW .=" = $v ";              
          }
          
          //$qW .="'".$v."'";
        }        
        
    }
    
    if (isset($_POST_val['searchField']) && isset($_POST_val['searchString']) && isset($_POST_val['searchOper']))     
    {
       if (!$firstElem) {
          $qW .= ' AND ';
       }
       else {
          $firstElem = false;
       }
       
       $qW .= $_POST_val['searchField'];

        switch ($_POST_val['searchOper']) 
        {
          case 'eq': $qW .= " = '".$_POST_val['searchString']."'" ; break;
          case 'ne': $qW .= " <> '".$_POST_val['searchString']."'" ; break;
          case 'bw': $qW .= " iLIKE '".$_POST_val['searchString']."%'" ; break;
          case 'bn': $qW .= " NOT iLIKE '".$_POST_val['searchString']."%'" ; break;
          case 'ew': $qW .= " iLIKE '%".$_POST_val['searchString']."'" ; break;
          case 'en': $qW .= " NOT iLIKE '%".$_POST_val['searchString']."'" ; break;
          case 'cn': $qW .= " iLIKE '%".$_POST_val['searchString']."%'" ; break;
          case 'nc': $qW .= " NOT iLIKE '%".$_POST_val['searchString']."%'" ; break;
          case 'nu': $qW .= " is null " ; break;
          case 'nn': $qW .= " is not null " ; break;
          case 'in': $qW .= " in (".$_POST_val['searchString'].")" ; break;
          case 'ni': $qW .= " not in (".$_POST_val['searchString'].")" ; break;
        }
       
    }
    
/*
    //объединяем все полученные условия
    $searchData = json_decode($_POST['filters']);
    foreach ($searchData->rules as $rule) {
      if (!$firstElem) {
        //объединяем условия (с помощью AND или OR)
        if (in_array($searchData->groupOp, $allowedOperations)) {
          $qWhere .= ' '.$searchData->groupOp.' ';
        }
        else {
          //если получили не существующее условие - возвращаем описание ошибки
          throw new Exception('!!!');
        }
      }
      else {
        $firstElem = false;
      }
      
      //вставляем условия

        switch ($rule->op) 
        {
          case 'eq': $qWhere .= $rule->field.' = '.$rule->data; break;
          case 'ne': $qWhere .= $rule->field.' <> '.$rule->data; break;
          case 'bw': $qWhere .= $rule->field.' LIKE '.$rule->data.'%'; break;
          case 'cn': $qWhere .= $rule->field.' LIKE '.'%'.$rule->data.'%'; break;
          default: throw new Exception('!!!');
        }
    }
 
 */
}

if ($qW == ' WHERE ') {$qW ='';};

// Отладка
//$ff=fopen('aaa','w');
//fputs($ff,$qW);
//fclose($ff);

//$f = fopen("log.txt","a+");
//$str = implode(" ",var_dump($_POST_val, TRUE));
//fputs($f, $str); 
//fclose($f);

//    ob_start();
//    var_dump($_POST_val);
//    $output = ob_get_clean();
//    file_put_contents('aaa', $output);
//    
//    ob_start();
//    var_dump($farrey);
//    $output = ob_get_clean();
//    file_put_contents('aaa', $output);

return $qW;    
}

function ukr_month($month_num, $format)
{


$month=array(
    '1'=>"Січень",'2'=>"Лютий",'3'=>"Березень",
    '4'=>"Квітень",'5'=>"Травень",'6'=>"Червень",
    '7'=>"Липень",'8'=>"Серпень",'9'=>"Вересень",
    '10'=>"Жовтень",'11'=>"Листопад",'12'=>"Грудень");

$month_2=array(
    '1'=>"січня",'2'=>"лютого",'3'=>"березня",
    '4'=>"квітня",'5'=>"травня",'6'=>"червня",
    '7'=>"липня",'8'=>"серпня",'9'=>"вересня",
    '10'=>"жовтня",'11'=>"листопада",'12'=>"грудня");

$month_3=array(
    '1'=>"у січні",'2'=>" у лютому",'3'=>" у березні",
    '4'=>"у квітні",'5'=>" у травні",'6'=>" у червні",
    '7'=>"у липні",'8'=>"у серпні",'9'=>" у вересні",
    '10'=>"у жовтні",'11'=>" у листопаді",'12'=>"у грудні");

if ($format == 0)
{
 return $month[$month_num];
}

if ($format == 1)
{
 return $month_2[$month_num];
}

if ($format == 2)
{
 return $month_3[$month_num];
}

}

function ukr_date($date,$show_day = 1, $format = 2)
{

   $month_1=array(
    '01'=>"Січень",'02'=>"Лютий",'03'=>"Березень",
    '04'=>"Квітень",'05'=>"Травень",'06'=>"Червень",
    '07'=>"Липень",'08'=>"Серпень",'09'=>"Вересень",
    '10'=>"Жовтень",'11'=>"Листопад",'12'=>"Грудень");
    
   $month_2=array(
    '01'=>"Січня",'02'=>"Лютого",'03'=>"Березня",
    '04'=>"Квітня",'05'=>"Травня",'06'=>"Червня",
    '07'=>"Липня",'08'=>"Серпня",'09'=>"Вересня",
    '10'=>"Жовтня",'11'=>"Листопада",'12'=>"Грудня");
    
    
   if ((trim($date)=='--')||($date=='\u00a0')||(trim($date)=='__.__.____')) 
    {$result = '';}
   else{    
     list($day, $month, $year) = split('[/.-]', $date);
     if(($day!='') && ($month!='')&& ($year!=''))
         {

         if ($format==1)
           $month_str = $month_1[$month];
         else
           $month_str = $month_2[$month];
         
          if ($show_day==1)
             $result = "$day $month_str $year р."; 
          else
             $result = "$month_str $year р.";               
         }
     else
         {$result = '';}
   }
 return $result;

}


function CheckLevel($lnk,$fname,$user) {

  $Query=" select sys_check_lvl($user,'$fname') as edit_full; ";
  $result = pg_query($lnk,$Query) or die("SQL Error: " .pg_last_error($lnk) );
  $row = pg_fetch_array($result);
   
  return $row['edit_full'];

  //return 3;
};

function CheckTownInAddrHidden($lnk) {

 $Query=" select value_ident::int as value from syi_sysvars_tbl where ident='id_res'; ";
 $result = pg_query($lnk,$Query) or die("SQL Error: " .pg_last_error($lnk) );
 $row = pg_fetch_array($result);
 $id_res = $row['value'];

  if ($id_res==310)
    return 'true';
  else
    return 'false';    
    
};

// принадлежность РЭСа к ЦЭК
function is_cek($lnk){
$Query=" select getsysvarn('id_res') as kod";
$result = pg_query($lnk,$Query) or die("SQL Error: " .pg_last_error($Link) );
$row = pg_fetch_array($result);
$kod_res = $row['kod'];
if($kod_res<100)
	$flag_cek = 1;  // ЦЕК
else
	$flag_cek = 0;  // РЭСы Чернигова

return $flag_cek;

}

?>
