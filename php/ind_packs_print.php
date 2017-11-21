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

$nmp1 = "АЕ-Відомість рознесення показань";
  
start_mpage($nmp1); //заголовок
head_addrpage();  // хедер, подключение библиотек

$id_pack = sql_field_val('id_pack','int');

$id_sector = sql_field_val('id_sector', 'int');
$sector = sql_field_val('sector', 'str');
$id_runner = sql_field_val('id_runner', 'int');
$runner = sql_field_val('runner', 'str');
$id_ioper = sql_field_val('id_ioper', 'int');
$mmgg_str = $_POST['work_period']; 
$mmgg = sql_field_val('work_period','date'); 

list($day, $month, $year) = split('[/.-]', $mmgg_str);
if(($day!='') && ($month!='')&& ($year!=''))
{
    $mmgg_str= ukr_month(ltrim($month,'0'),0).' '.$year;
}
  
$num_pack = sql_field_val('num_pack', 'str');
$dt_pack = sql_field_val('dt_pack', 'str');

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
.tab_head {font-family: "Times New Roman", Times, serif; font-size: 14px; }
.table_footer{font-family: "Times New Roman", Times, serif; font-size: 14px; }

table.indic_print_table { 
  /* border-collapse:collapse; */
   border-collapse:initial; border-spacing:0px;
   border-right:1px dotted rgb(150,150,150); border-left:0; border-bottom:1px dotted rgb(150,150,150); border-top:0;
   font-family: "Times New Roman", Times, serif; font-size: 14px; 
   border-right:0.6pt solid black; border-left:0; border-bottom:0.6pt solid black; border-top:0;
}
table.indic_print_table td { vertical-align:top; page-break-inside: avoid; 
    /*border:1px dotted rgb(150,150,150);*/
    border-left:1px dotted rgb(150,150,150); border-right:0; border-top:1px dotted rgb(150,150,150); border-bottom:0 ;
    padding-top:1px; padding-bottom: 0px; padding-left:2px;padding-right:0px;}
table.indic_print_table th { page-break-inside: avoid; 
    /*border:1px solid rgb(150,150,150);*/
    border-left:1px solid rgb(150,150,150); border-right:0; border-top:1px solid rgb(150,150,150); border-bottom:0 ;
    padding:2px; }

table.indic_print_table td.tab_street { border:1px solid black; font-family: "Courier New", Courier, monospace; font-size: 11px; font-weight: bold;}


#pheader { font-family: "Times New Roman", Times, serif; font-size: 12px; }
#pheader2 { font-family: "Times New Roman", Times, serif; font-size: 12px; }

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


</style>

</head>
<body >


    <DIV id="pmain_center" style="padding:2px; margin:3px;">    

        <div id="pheader" style="padding:2px; margin:3px;text-align:center;line-height: 14px; ">  
            ВІДОМІСТЬ  <br/> 
            облiку показань лiчильникiв та вручення рахункiв на оплату електроенергii <br/> 
            у побутових споживачiв за <?php echo "$mmgg_str" ?> року.   
        </div>                
        <div id="pheader2" style="padding:2px; margin:3px;text-align:left;line-height: 14px; ">          
            Дата зняття показань та вручення рахунку _______________ &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;  Контролер _____________________________ <br/> 
        </div>                            


<table class ="indic_print_table"> 
 <thead>        
  <tr>      
    <td colspan ="10">Дільниця: <?php echo "$sector" ?>; </td>     
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
    <th width="10%" scope="col"><span class="tab_head">Показаники лічильн. на дату зняття </span></th>
<!--    <th width="10%" scope="col"><span class="tab_head">Дата зняття показань та вручення рахунку</span></th> -->
    <th width="9%" scope="col"><span class="tab_head">Підпис споживача </span></th>
  </tr>
  </thead>
  <tbody>    

 <?php
 
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
 
$SQL = " select pd.*, acc.book, acc.code, adr.adr as street, 
address_print(acc.addr)||coalesce('&nbsp;&nbsp;&nbsp;&nbsp;(&nbsp;відкл.'||to_char(sw.dt_action , 'DD.MM.YYYY')||')','') as adr,
 ( CASE WHEN acc.idk_house = 3 THEN 'Д&nbsp;' ELSE '' END||
   CASE WHEN coalesce(lg.lgt_cnt,0)>0 THEN '+&nbsp;' ELSE '' END||
   CASE WHEN sb.id_paccnt is not null THEN 'S&nbsp;' ELSE '' END|| 
   substr((c.last_name||'&nbsp;'||coalesce(c.name,'')||'&nbsp;'||coalesce(c.patron_name,''))::varchar,1,42))::varchar as abon,
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
left join eqk_zone_tbl as z on (z.id = pd.id_zone)
left join adt_addr_tbl as adr on (adr.id = (acc.addr).id_class)
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
$prn_nn = 6;
$prn_max = 54;
$table_text= "";
$cur_street= "";
if ($result) 
{
 while($row = pg_fetch_array($result)) 
  {
    $nn++;
    $abon            =$row['abon']; 
    $adr             =$row['adr']; 
    $street          =$row['street'];     
    $code            =$row['code']; 
    $book            =$row['book']; 
    $num_meter       =$row['num_meter']; 
    $carry           =$row['carry']; 
    $zone_phase      =$row['zone_phase']; 
    $p_indic         =number_format($row['p_indic'],0); 
    $p_indic = str_replace(',','',$p_indic);        
    $dt_p_indic      =$row['dt_p_indic']; 
    
    if ($cur_street != $street) {
        $prn_nn ++;
        $cur_street=$street;
            $table_text.= "
             <tr>
              <td class = 'tab_street' colspan = '10'>$cur_street</td>
             </tr> ";
    }

    $prn_nn=$prn_nn+2;
    
    if ($prn_nn>=$prn_max)
    {
        $row_style = "style = 'page-break-before: always;' ";
        $prn_nn = 2;
    }
    else
    {
        $row_style ='';
    }
        
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
  </tr>
  <tr height=15 style="page-break-before:avoid;">
    <td>$adr</td>
  </tr>
   
TAB_STR;
    /*
    $prn_nn=$prn_nn+2;

    if ($prn_nn>=$prn_max)
    {
        $table_text.= '</table>';
        $table_text.= '<br style="page-break-after: always">';
        $table_text.= '<table class ="indic_print_table"> <tr>
    <th width="4%" height="51" scope="col"><span class="tab_head">№ п/п </span></th>
    <th width="24%" scope="col"><span class="tab_head">Прiзвище, iм\'я, по батьковi <br> 
    Адреса</span></th>
    <th width="7%" scope="col"><span class="tab_head">Особ.рах.</span></th>
    <th width="6%" scope="col"><span class="tab_head">Номер лiчиль- ника</span></th>
    <th width="5%" scope="col"><span class="tab_head">Роз ряд ність</span></th>
    <th width="6%" scope="col"><span class="tab_head">Зона фазн. </span></th>
    <th width="9%" scope="col"><span class="tab_head">Порередні показання лічильника </span></th>
    <th width="10%" scope="col"><span class="tab_head">Дата зняття попер. показань </span></th>
    <th width="10%" scope="col"><span class="tab_head">Ноказання лічильника на дату зняття </span></th>
    <th width="10%" scope="col"><span class="tab_head">Дата зняття показань та вручення рахунку</span></th>
    <th width="9%" scope="col"><span class="tab_head">Підпис споживача </span></th>
  </tr>';

        $prn_nn = 1;
        
    
    }
    */
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
    

      <div id="dialog-confirm" title="" style="display:none;">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
        <p id="dialog-text"> </p></p>
      </div>

    <div id="dialog-changedate" title="Дата редагування" style="display:none;">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
        <p id="dialog-text">Вкажіть дату редагування </p></p>
        <br>
        <input name="date_change" type="text" size="20" class="dtpicker" id ="fdate_change"/>
    </div>

<?php

print('<div id="message_zone" style ="padding: 5px;color: blue;" >  </div>');


end_mpage();
?>