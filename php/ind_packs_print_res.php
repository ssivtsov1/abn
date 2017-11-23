<?php

header('Content-type: text/html; charset=utf-8');

require 'abon_en_func.php';
require 'abon_ded_func.php';

session_name("session_kaa");
session_start();
error_reporting(0);

$Link = get_database_link($_SESSION);
$session_user = $_SESSION['ses_usr_id'];
session_write_close();

  
$id_pack = sql_field_val('id_pack','int');

$id_sector = sql_field_val('id_sector', 'int');
$sector = sql_field_val('sector', 'str');
$id_runner = sql_field_val('id_runner', 'int');
$runner = sql_field_val('runner', 'str');
$runner_param = $runner;
if ($runner=='null') $runner='__________________________';

$operator = sql_field_val('operator', 'str');
$operator_param = $operator;

if ($operator!='null') $operator=" Оператор : $operator";
else $operator="";

$id_ioper = sql_field_val('id_ioper', 'int');
$mmgg_str = $_POST['work_period']; 
$mmgg_str_param = $_POST['work_period']; 
$mmgg = sql_field_val('work_period','date'); 

list($day, $month, $year) = split('[/.-]', $mmgg_str);
if(($day!='') && ($month!='')&& ($year!=''))
{
    $mmgg_str= ukr_month(ltrim($month,'0'),0).' '.$year;
}
  
$num_pack = sql_field_val('num_pack', 'str');
$dt_pack = sql_field_val('dt_pack', 'str');

$lines_count = sql_field_val('lines_count', 'int');
if ($lines_count=='null') $lines_count  = 27;

$new_street_new_page = sql_field_val('new_street_new_page','int'); 
if ($new_street_new_page=='null') 
{
  $new_street_new_page=0;  
  $Query="  select value_ident::int from syi_sysvars_tbl where ident='ind_st_newpage' ;";
  $result = pg_query($Link,$Query) or die("SQL Error: " .pg_last_error($Link) );
  if ($result) 
  {
   while($row = pg_fetch_array($result)) 
    {
      $new_street_new_page = $row['value_ident'];  
    }
  }    
}    
if ($new_street_new_page==1)
{
    $new_street_new_page_checked = 'checked';
}
else
{
    $new_street_new_page_checked = '';
}

$nmp1 = "АЕ-Відомість $num_pack ";
start_mpage($nmp1); //заголовок
head_addrpage();  // хедер, подключение библиотек


print('<link rel="stylesheet" type="text/css" href="css/ded_styles.css" /> ');
print('<link rel="stylesheet" type="text/css" href="css/layout-default-latest.css" /> ');
print('<script type="text/javascript" src="js/jquery.layout.min-latest.js"></SCRIPT>');
print('<script type="text/javascript" src="js/jquery.form.js"></script>');
print('<script type="text/javascript" src="js/jquery.validate.min.js?version='.$app_version.'" ></script>');
print('<script type="text/javascript" src="js/jquery-ui-1.8.20.custom.min.js"></script> ');
print('<script type="text/javascript" src="js/jquery.ui.datepicker-uk.js"></script> ');
print('<script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script> ');

?> 

<style type="text/css">
body {background-color: white}
.tab_head {font-family: "Times New Roman", Times, serif; font-size: 13px; }
.table_footer{font-family: "Times New Roman", Times, serif; font-size: 13px; }

table.indic_print_table { 
    /*border-collapse:collapse; */
    border-collapse:initial; border-spacing:0px;
    border-right:1px dotted rgb(150,150,150); border-left:0; border-bottom:1px dotted rgb(150,150,150); border-top:0;
    font-family: "Times New Roman", Times, serif; font-size: 13px; }

table.indic_print_table td { vertical-align:top; page-break-inside: avoid; 
  /*   border:1px dotted rgb(150,150,150); */
    border-left:1px dotted rgb(150,150,150); border-right:0; border-top:1px dotted rgb(150,150,150); border-bottom:0 ;
    padding-top:1px; padding-bottom: 0px; padding-left:2px;padding-right:0px;}

table.indic_print_table th { page-break-inside: avoid; 
    /*border:1px solid rgb(150,150,150);*/
    border-left:1px solid rgb(150,150,150); border-right:0; border-top:1px solid rgb(150,150,150); border-bottom:0 ;
    padding:2px; }

table.indic_print_table td.tab_street { border:1px solid black; 
             font-family: "Arial", Courier, monospace; font-size: 12px; font-weight: bold;}
#pheader { font-family: "Times New Roman", Times, serif; font-size: 13px; font-weight: bold; }
#pheader2 { font-family: "Times New Roman", Times, serif; font-size: 16px; font-weight: bold;}

.num_met {font-family: "Courier New", Courier, monospace; font-size: 12px; font-weight: bold; }
.carry {text-align:center }

@page {margin-left:0.3cm; margin-right:0.3cm;
     size: portrait;
     counter-increment: page;
     @bottom-right {
        padding-right:20px;
        content: "Page " counter(page);
      }     
}
 
@media print
      {    
          .no-print, .no-print *
          {
              display: none !important;
          }
      } 

</style>

</head>
<body >

        <form id="fHeaderEdit" name="fHeaderEdit" method="post" action="ind_packs_print_res.php" target="" class="no-print" >
          <div style='display:none;' >
            <input name="id_pack" type="hidden" id="fid" value="<?php echo "$id_pack" ?>" />
            
            
            <input name="work_period" type="hidden"  value="<?php echo "$mmgg_str_param" ?>" />                            

            <input name="num_pack" type="text" id = "fnum_pack" size="20"  value= "<?php echo "$num_pack" ?>" data_old_value = ""/>
            <input name="dt_pack" type="text" size="12" value="<?php echo "$dt_pack" ?>" id="fdt_pack"  data_old_value = "" />
            <input name="id_sector" type="hidden" id = "fid_sector" size="10" value= "<?php echo "$id_sector" ?>" data_old_value = ""/>    
            
            <input name="sector" type="text" id = "fsector" size="60" value= "<?php echo "$sector" ?>" data_old_value = ""  />
            <input name="id_runner" type="hidden" id = "fid_runner" size="10" value= "<?php echo "$id_runner" ?>" data_old_value = ""/>    
            <input name="operator" type="hidden" id = "foperator" size="10" value= "<?php echo "$operator_param" ?>" data_old_value = ""/>                        
            <input name="runner" type="text" id = "frunner" size="60" value= "<?php echo "$runner_param" ?>" data_old_value = ""  />
            <input name="id_ioper" id = "fid_ioper" size="10"  value= "<?php echo "$id_ioper" ?>" data_old_value = ""/>

           </div>
            Рядків на лист: <input name="lines_count" size="6" type="text" id="flines_count" value="<?php echo "$lines_count" ?>" />
            <label><input type='checkbox' name='new_street_new_page' id='fnew_street_new_page' value='1' <?php echo "$new_street_new_page_checked" ?> /> Вулиця з нової сторінки</label>         
           <button name='pack_submit'  value='All' type='submit' class ='btn btnSmall' id='pack_submit'>Застосувати</button>
          </FORM>    
    

    <DIV id="pmain_center" style="padding:2px; margin:3px;">    

        <div id="pheader" style="padding:2px; margin:3px;text-align:center;line-height: 14px; ">  
            ВІДОМІСТЬ  <br/> 
            облiку показань лiчильникiв та вручення рахункiв на оплату електроенергii <br/> 
            у побутових споживачiв за <?php echo "$mmgg_str" ?> року.   
        </div>                


<table class ="indic_print_table"> 
 <thead>        
  <tr>      
    <td id="pheader2" colspan ="11">Дільниця: <?php echo "$sector" ?>;  &nbsp;&nbsp;&nbsp;&nbsp;
        Кур`єр : <?php echo "$runner" ?> &nbsp;&nbsp;&nbsp;&nbsp; <?php echo "$operator" ?> </td>     
  </tr>
  <tr>
    <th width="4%" scope="col"><span class="tab_head">№<br/>п/п </span></th>
    <th width="24%" scope="col"><span class="tab_head">Прiзвище, iм'я, по батьковi <br/>  Адреса</span></th>
    <th width="7%" scope="col"><span class="tab_head">Особ.<br/>рах.</span></th>
    <th width="6%" scope="col"><span class="tab_head">Номер <br/>лiчиль- <br/>ника</span></th>
    <th width="4%" scope="col"><span class="tab_head">Роз ря дів</span></th>
    <th width="4%" scope="col"><span class="tab_head">Зона фаз. </span></th>
    <th width="9%" scope="col"><span class="tab_head">Попер. показ. ліч. </span></th>
    <th width="10%" scope="col"><span class="tab_head">Дата зняття попер. показань </span></th>
    <th width="10%" scope="col"><span class="tab_head">Показан. лічильн. на дату зняття </span></th>
    <th width="10%" scope="col"><span class="tab_head">Дата зняття пок.</span></th> 
    <th width="9%" scope="col"><span class="tab_head">Підпис споживача </span></th>
  </tr>
  </thead>
  <tbody>    

 <?php
  
$flag_cek = is_cek($Link);  //принадлежность РЭСа к ЦЭК
$street_style='';

if ($new_street_new_page==1)
      $street_style = "style = 'page-break-before: always;' ";

 
$SQL = "select coalesce(s.sort_flag,0) as sort_flag
    from ind_pack_header as h 
    join prs_runner_sectors as s on (s.id = h.id_sector)
    where h.id_pack =  $id_pack ; "; 

$result = pg_query($Link,$SQL) or die("SQL Error: " .pg_last_error($Link) );

$row = pg_fetch_array($result);
if ($row['sort_flag']==1 )
{
    $order = "int_book, acc.book, int_code, acc.code, z.id";
}
else
{
    $order = "adr.adr, int_house, (acc.addr).house,(acc.addr).korp , int_flat, int_code, acc.code, z.id";
}
 //emp.name
//inner join eqk_meter_places_tbl emp on (data_c.id_extra=emp.id)
if($flag_cek)
$SQL = " select pd.*, acc.book, acc.code, adr.adr as street,
 address_print(acc.addr)||coalesce('&nbsp;&nbsp;&nbsp;&nbsp;(&nbsp;відкл.'||to_char(sw.dt_action , 'DD.MM.YYYY')||')','') as adr,
 ( CASE WHEN acc.idk_house = 3 THEN 'Д&nbsp;' ELSE '' END||
   CASE WHEN coalesce(lg.lgt_cnt,0)>0 THEN '+&nbsp;' ELSE '' END||
   CASE WHEN sb.id_paccnt is not null THEN 'S&nbsp;' ELSE '' END|| 
   substr((c.last_name||'&nbsp;'||coalesce(c.name,'')||'&nbsp;'||coalesce(c.patron_name,''))::varchar,1,40))::varchar as abon,
   coalesce(c.mob_phone,'') as phone,coalesce(im.name,'') as type_meter,
   CASE WHEN length(pd.num_meter)> 9 THEN substr(pd.num_meter,1,8)||'<br/>'||substr(pd.num_meter,9,100) ELSE pd.num_meter END as num_meter,
 pd.carry ,
 (CASE WHEN z.id = 0 THEN '' ELSE z.nm END)||(CASE WHEN im.phase = 1 THEN '' ELSE ' 3f' END)::varchar as zone_phase,
('0'||coalesce(regexp_replace(regexp_replace((acc.addr).house, '-.*?$', '') , '[^0-9]', '','g'),''))::int as int_house,
('0'||coalesce(regexp_replace(regexp_replace((acc.addr).flat, '-.*?$', '') , '[^0-9]', '','g'),''))::int as int_flat,
('0'||substring(acc.code FROM '[0-9]+'))::int as int_code,
('0'||substring(acc.book FROM '[0-9]+'))::int as int_book,   
i.value as p_indic, to_char(i.dat_ind, 'DD.MM.YYYY') as dt_p_indic,min(emp.name) as place_counter
from 
ind_pack_data as pd 
join clm_paccnt_tbl as acc on (acc.id = pd.id_paccnt)
join clm_meterpoint_h as data_c on (pd.id_paccnt=data_c.id_paccnt)
left join eqk_meter_places_tbl emp on (data_c.id_extra=emp.id)
join clm_abon_tbl as c on (c.id = acc.id_abon) 
join eqi_meter_tbl as im on (im.id = pd.id_type_meter)
join eqk_zone_tbl as z on (z.id = pd.id_zone)
join adt_addr_tbl as adr on (adr.id = (acc.addr).id_class)
left join (
  select id_paccnt, count(*) as lgt_cnt from lgm_abon_tbl 
     where ((dt_start < ($mmgg::date+'1 month'::interval) and dt_end is null)
            or 
            tintervalov(tinterval(dt_start::timestamp::abstime,dt_end::timestamp::abstime),tinterval($mmgg::timestamp::abstime,($mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime)))
     group by id_paccnt order by id_paccnt
 )  as lg on (lg.id_paccnt = acc.id)
left join (
 select distinct id_paccnt 
 from acm_subs_tbl as s 
 join (select max(mmgg) as mmgg from acm_subs_tbl where mmgg <= $mmgg ) as mm on (mm.mmgg = s.mmgg)
 where tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($mmgg::timestamp::abstime,($mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
 and s.dt_b is not null
 ) as sb on (sb.id_paccnt = acc.id)
left join acm_indication_tbl as i on (i.id = pd.id_p_indic) 
left join 
(
       select csw.id_paccnt, csw.dt_action, csw.action
       from clm_switching_tbl as csw 
       join (select id_paccnt, max(dt_action) as maxdt from clm_switching_tbl where action in (1,2) group by id_paccnt) as csdt 
       on (csw.id_paccnt = csdt.id_paccnt and csw.dt_action = csdt.maxdt) 
       left join clm_switching_tbl as cswc on (csw.id_paccnt = cswc.id_paccnt and cswc.dt_action >= csw.dt_action and ((cswc.action =3 and csw.action =1) or (cswc.action =4  and csw.action =2) ) ) 
       where csw.action =1 and cswc.id_paccnt is null 

) as sw on (sw.id_paccnt = acc.id)
 where id_pack = $id_pack  
 group by 1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,
 acc.addr,i.value,i.dat_ind,z.id    
 order by $order ;"; 
else
    $SQL = " select pd.*, acc.book, acc.code, adr.adr as street,
 address_print(acc.addr)||coalesce('&nbsp;&nbsp;&nbsp;&nbsp;(&nbsp;відкл.'||to_char(sw.dt_action , 'DD.MM.YYYY')||')','') as adr,
 ( CASE WHEN acc.idk_house = 3 THEN 'Д&nbsp;' ELSE '' END||
   CASE WHEN coalesce(lg.lgt_cnt,0)>0 THEN '+&nbsp;' ELSE '' END||
   CASE WHEN sb.id_paccnt is not null THEN 'S&nbsp;' ELSE '' END|| 
   substr((c.last_name||'&nbsp;'||coalesce(c.name,'')||'&nbsp;'||coalesce(c.patron_name,''))::varchar,1,40))::varchar as abon,
   CASE WHEN length(num_meter)> 9 THEN substr(num_meter,1,8)||'<br/>'||substr(num_meter,9,100) ELSE num_meter END as num_meter,
 pd.carry ,
 (CASE WHEN z.id = 0 THEN '' ELSE z.nm END)||(CASE WHEN im.phase = 1 THEN '' ELSE ' 3f' END)::varchar as zone_phase,
('0'||coalesce(regexp_replace(regexp_replace((acc.addr).house, '-.*?$', '') , '[^0-9]', '','g'),''))::int as int_house,
('0'||coalesce(regexp_replace(regexp_replace((acc.addr).flat, '-.*?$', '') , '[^0-9]', '','g'),''))::int as int_flat,
('0'||substring(acc.code FROM '[0-9]+'))::int as int_code,
('0'||substring(acc.book FROM '[0-9]+'))::int as int_book,   
i.value as p_indic, to_char(i.dat_ind, 'DD.MM.YYYY') as dt_p_indic
from 
ind_pack_data as pd 
join clm_paccnt_tbl as acc on (acc.id = pd.id_paccnt)
join clm_abon_tbl as c on (c.id = acc.id_abon) 
join eqi_meter_tbl as im on (im.id = pd.id_type_meter)
join eqk_zone_tbl as z on (z.id = pd.id_zone)
join adt_addr_tbl as adr on (adr.id = (acc.addr).id_class)
left join (
  select id_paccnt, count(*) as lgt_cnt from lgm_abon_tbl 
     where ((dt_start < ($mmgg::date+'1 month'::interval) and dt_end is null)
            or 
            tintervalov(tinterval(dt_start::timestamp::abstime,dt_end::timestamp::abstime),tinterval($mmgg::timestamp::abstime,($mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime)))
     group by id_paccnt order by id_paccnt
 )  as lg on (lg.id_paccnt = acc.id)
left join (
 select distinct id_paccnt 
 from acm_subs_tbl as s 
 join (select max(mmgg) as mmgg from acm_subs_tbl where mmgg <= $mmgg ) as mm on (mm.mmgg = s.mmgg)
 where tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($mmgg::timestamp::abstime,($mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
 and s.dt_b is not null
 ) as sb on (sb.id_paccnt = acc.id)
left join acm_indication_tbl as i on (i.id = pd.id_p_indic) 
left join 
(
       select csw.id_paccnt, csw.dt_action, csw.action
       from clm_switching_tbl as csw 
       join (select id_paccnt, max(dt_action) as maxdt from clm_switching_tbl where action in (1,2) group by id_paccnt) as csdt 
       on (csw.id_paccnt = csdt.id_paccnt and csw.dt_action = csdt.maxdt) 
       left join clm_switching_tbl as cswc on (csw.id_paccnt = cswc.id_paccnt and cswc.dt_action >= csw.dt_action and ((cswc.action =3 and csw.action =1) or (cswc.action =4  and csw.action =2) ) ) 
       where csw.action =1 and cswc.id_paccnt is null 

) as sw on (sw.id_paccnt = acc.id)
 where id_pack = $id_pack  
 order by $order ;"; 

$result = pg_query($Link,$SQL) or die("SQL Error: " .pg_last_error($Link) );
$nn = 0;  
$prn_nn = 4;
//$prn_max = 55;
$prn_max = $lines_count*2+1;
$table_text= "";
$cur_street= "";
//$row1 = pg_fetch_array($result);
//var_dump($row1);
//return;

if ($result) 
{
 while($row = pg_fetch_array($result)) 
  {
    $nn++;
    $abon            =$row['abon']; 
    if($flag_cek==1){
    if(!empty($row['phone']) && strlen(trim($row['phone']))>0)
      $phone           ='<br>'.'('.norm_tel($row['phone']).')';
    else
        $phone = '<br>'.'('.'&nbsp;'. '&nbsp;'.'&nbsp;'.'&nbsp;'.'&nbsp;'. '&nbsp;'.'&nbsp;'.'&nbsp;'
            .'&nbsp;'.'&nbsp;'.'&nbsp;'.'&nbsp;'.'&nbsp;'. '&nbsp;'.'&nbsp;'.'&nbsp;'
            .'&nbsp;'. '&nbsp;'.'&nbsp;'.'&nbsp;'.'&nbsp;'. '&nbsp;'.'&nbsp;'
            .'&nbsp;'. '&nbsp;'.'&nbsp;'.'&nbsp;'.'&nbsp;'. '&nbsp;'.'&nbsp;'.')';
    }
    $adr             =$row['adr']; 
    $street          =$row['street'];     
    $code            =$row['code']; 
    $book            =$row['book']; 
    $num_meter       =$row['num_meter']; 
    if($flag_cek==1){
        $type_meter      =$row['type_meter'];
        $place_counter      =$row['place_counter'];
    }
    $carry           =$row['carry']; 
    $zone_phase      =$row['zone_phase']; 
    $p_indic         =number_format($row['p_indic'],0); 
    $p_indic = str_replace(',','',$p_indic);        
    $dt_p_indic      =$row['dt_p_indic']; 
    
    if ($cur_street != $street) {
        $prn_nn ++;

        if ($cur_street=='')
        {
          $cur_street=$street;
        
          $table_text.= "
             <tr>
              <td colspan = '11' class = 'tab_street' >$cur_street</td>
             </tr> ";
        }
        else
        {
          $cur_street=$street;
        
          $table_text.= "
             <tr $street_style >
              <td colspan = '11' class = 'tab_street' >$cur_street</td>
             </tr> ";
          if ($new_street_new_page==1)  $prn_nn = 2;
        }
    }

    $prn_nn=$prn_nn+2;
    
    if ($prn_nn>=$prn_max)
    {
        $row_style = "style = 'page-break-before: always;' ";
        $prn_nn = 2;
    }
    else
    {
       if($flag_cek==0)
        $row_style ='';
       else
        $row_style = 'style = "page-break-before: avoid;" ';
    }
        
  if($flag_cek==0){
    $table_text.= <<<TAB_STR

  <tr height=15 $row_style >
    <td rowspan="2">$nn</td>
    <td>$abon</td>
    <td rowspan="2">$book/$code</td>
    <td rowspan="2" class='num_met'>$num_meter</td>
    <td rowspan="2" class='carry' >$carry</td>
    <td rowspan="2">$zone_phase</td>
    <td rowspan="2">$p_indic</td>
    <td rowspan="2">$dt_p_indic</td>
    <td rowspan="2">&nbsp;</td>
    <td rowspan="2">&nbsp;</td>
    <td rowspan="2">&nbsp;</td>
  </tr>
  <tr height=15 style="page-break-before:avoid;">
    <td>$adr</td>
  </tr>
   
TAB_STR;
  }
  else
  {
  
      $table_text.= <<<TAB_STR

  <tr height=15 $row_style >
    <td rowspan="2">$nn</td>
    <td>$abon.$phone</td>
    <td rowspan="2">$book/$code</td>
    <td rowspan="2" class='num_met'>$num_meter / $type_meter / $place_counter</td>
    <td rowspan="2" class='carry' >$carry</td>
    <td rowspan="2">$zone_phase </td>
    <td rowspan="2">$p_indic</td>
    <td rowspan="2">$dt_p_indic</td>
    <td rowspan="2">&nbsp;</td>
    <td rowspan="2">&nbsp;</td>
    <td rowspan="2">&nbsp;</td>
  </tr>
  <tr height=15 style= "page-break-before: avoid;">
    <td>$adr</td>
  </tr>
   
TAB_STR;
  }
  }
}
echo $table_text;
 
 ?>
</tbody>    
</table>
    <div class ="table_footer">        
       <p>
        &nbsp;&nbsp;Дата здачi вiдомостi 
       <p/>
       <p>
        &nbsp;&nbsp;Дата прийому виконаної роботи
       <p/>       

       <div style="float:left; width: 400; ">
        &nbsp;&nbsp;Пiдпис вiдповiдальної особи, яка приймає завдання <br/><br/>
        &nbsp;&nbsp;Пiдпис виконавця
       </div>
       <div style="float:left; text-align:center;">
        _________________________________<br/>
        посада, П.I.Б.<br/>
        _________________________________<br/>
        посада, П.I.Б.<br/>

       </div>
     </div>        
    </div>        
    

<?php

print('<div id="message_zone" style ="padding: 5px;color: blue;" >  </div>');


end_mpage();
?>

<?php
// Нормализация № телефона в формате 067 000-00-00
// распознает широкоизвестные коды операторов Украины
// отсекает другие символы (кроме цифр).
function norm_tel($p){
        $tel='';
        $y = strlen($p);

        for($i=0;$i<$y;$i++)
        {
        $c = substr($p,$i,1);
        $kod=ord($c);
        if($kod>47 && $kod<58) $tel.=$c;
        }
        $op = substr($tel,0,3);
        $y = strlen($tel);
        if($y<10) {
        return '';
        }
        switch($op) {
        case '050':  $flag = 1;
        break;
        case '096':  $flag = 1;
        break;
        case '097':  $flag = 1;
        break;
        case '098':  $flag = 1;
        break;
        case '099':  $flag = 1;
        break;

        case '091':  $flag = 1;
        break;
        case '063':  $flag = 1;
        break;
        case '073':  $flag = 1;
        break;
        case '067':  $flag = 1;
        break;
        case '066':  $flag = 1;
        break;

        case '093':  $flag = 1;
        break;
        case '095':  $flag = 1;
        break;
        case '039':  $flag = 1;
        break;
        case '068':  $flag = 1;
        break;
        case '092':  $flag = 1;
        break;
        case '094':  $flag = 1;
        break;
        default:  return $tel;

        }
        $rez = '';
        $add = substr($tel,3,3);
        $rez.=$add.'-';
        $add = substr($tel,6,2);
        $rez.=$add.'-';
            $add = substr($tel,8);
        $rez.=$add;

        if($flag) {
        $rez = $op.' '.$rez;
        }
        else{
        $rez = '('.$op.')'.' '.$rez;
        }
        return $rez;
        }
?>

