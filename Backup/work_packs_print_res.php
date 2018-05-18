<?php

header('Content-type: text/html; charset=utf-8');

require 'abon_en_func.php';
require 'abon_ded_func.php';

function echo_html($buffer, $flag=1) {
    if ($flag ==1 )  
    {
     echo sanitize_output($buffer);   
    }
    
}

function sanitize_output($buffer) {

    $search = array(
        '/\>[^\S ]+/s',  // strip whitespaces after tags, except space
        '/[^\S ]+\</s',  // strip whitespaces before tags, except space
        '/(\s)+/s'       // shorten multiple whitespace sequences
    );

    $replace = array(
        '>',
        '<',
        '\\1'
    );

    $buffer = preg_replace($search, $replace, $buffer);

    return $buffer;
}

session_name("session_kaa");
session_start();
error_reporting(0);

$Link = get_database_link($_SESSION);
$session_user = $_SESSION['ses_usr_id'];
session_write_close();

$nmp1 = "АЕ-Відомість рознесення показань";
  
start_mpage($nmp1); //заголовок
head_addrpage();  // хедер, подключение библиотек

$id_pack = sql_field_val('id','int');

$id_sector = sql_field_val('id_sector', 'int');
$sector = sql_field_val('sector', 'str');

$id_position = sql_field_val('id_position', 'int');
$position = sql_field_val('position', 'str');

$idk_work = sql_field_val('idk_work', 'int');

$dt_work=$_POST['dt_work'];
$dt_work_date=sql_field_val('dt_work', 'date');
$dt_work_param=$_POST['dt_work'];

$book = sql_field_val('book', 'str');

$addr = $_POST['addr'];
$addr_str = sql_field_val('addr', 'str');

$mmgg_str = $_POST['work_period']; 
$mmgg_str_param = $_POST['work_period']; 
$mmgg = sql_field_val('work_period','date'); 

list($day, $month, $year) = split('[/.-]', $mmgg_str);
if(($day!='') && ($month!='')&& ($year!=''))
{
    $mmgg_str= ukr_month(ltrim($month,'0'),0).' '.$year;
}

list($day, $month, $year) = split('[/.-]', $dt_work);
if(($day!='') && ($month!='')&& ($year!=''))
{
    $date_str= $day.' '.ukr_month(ltrim($month,'0'),1).' '.$year;
}
//echo $addr_str;
if ($book!='null') $discriptor='по книзі '.$book;
if ($addr_str!='null') $discriptor='за адресою : '.$addr_str;
if ($sector!='null') $discriptor='дільниця  : '.$sector;

$lines_count = sql_field_val('lines_count', 'int');
if ($lines_count=='null') $lines_count  = 26;

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
.tab_head {font-family: "Times New Roman", Times, serif; font-size: 10px; }
.table_footer{font-family: "Times New Roman", Times, serif; font-size: 10px; }
.t_str{height:0.7cm;}
 
table.indic_print_table { border-collapse:collapse; font-family: "Times New Roman", Times, serif; font-size: 9px; }
table.indic_print_table td { vertical-align:top; page-break-inside: avoid; border:1px dotted rgb(150,150,150);
    padding-top:1px; padding-bottom: 0px; padding-left:2px;padding-right:1px;}
table.indic_print_table th { page-break-inside: avoid; border:1px solid rgb(150,150,150);padding:2px; }

.tab_street { border:1px solid black; font-family: "Courier New", Courier, monospace; font-size: 11px; font-weight: bold; height:0.4cm;}
#pheader0 { font-family: "Times New Roman", Times, serif; font-size: 12px; font-weight: bold; }
#pheader { font-family: "Times New Roman", Times, serif; font-size: 13px; font-weight: bold; }
#pheader2 { font-family: "Times New Roman", Times, serif; font-size: 12px; font-weight: bold;  height:0.4cm;}

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

        <form id="fHeaderEdit" name="fHeaderEdit" method="post" action="work_packs_print_res.php" target="" class="no-print" >
          <div style='display:none;' >
            <input name="id" type="hidden" id="fid" value="<?php echo "$id_pack" ?>" />
            <input name="work_period" type="hidden"  value="<?php echo "$mmgg_str_param" ?>" />                            
            
            <input name="idk_work" type="text" id = "fidk_work" size="20"  value= "<?php echo "$idk_work" ?>" data_old_value = ""/>
            <input name="dt_work" type="text" size="12" value="<?php echo "$dt_work_param" ?>" id="fdt_work"  data_old_value = "" />

            <input name="id_position" type="hidden" id = "fid_position" size="10" value= "<?php echo "$id_position" ?>" />    
            <input name="position" type="text" id = "fposition" size="50" value= "<?php echo "$position" ?>"  readonly />
            
            <input name="id_sector" type="hidden" id = "fid_sector" size="10" value= "<?php echo "$id_sector" ?>" data_old_value = ""/>    
            <input name="sector" type="text" id = "fsector" size="60" value= "<?php echo "$sector" ?>" data_old_value = ""  />
            
            <input name="book" type="text" id = "fbook" size="10" value= "<?php echo "$book" ?>"   />            
            
            <input name="addr" type="hidden" id = "faddr" size="10" value= "<?php echo "$addr" ?>" />    
            <input name="addr_str" type="text" id = "faddr_str" size="70" value= "<?php echo "$addr_str" ?>"  readonly />

           </div>
            Рядків на лист: <input name="lines_count" size="6" type="text" id="flines_count" value="<?php echo "$lines_count" ?>" />
           <button name='pack_submit'  value='All' type='submit' class ='btn btnSmall' id='pack_submit'>Застосувати</button>
          </FORM>    

    <div id="pheader0" style="padding:2px; margin:3px;text-align:left;line-height: 16px; ">  
            Дільниця - ________________  <br/> <br/> 
            __________________________________ <br/> 
            П.І.Б. контролера <br/> 
            ( <?php echo "$date_str" ?> )   
        </div>                
    

    <DIV id="pmain_center" style="padding:2px; margin:3px;">    

        <div id="pheader" style="padding:2px; margin:3px;text-align:center;line-height: 14px; ">  
            ВІДОМІСТЬ  <br/> 
            контрольного зйому показань лічильників та технічного стану обліків <br/> 
            у побутових споживачiв за <?php echo "$mmgg_str" ?> року.   
        </div>                


<table class ="indic_print_table"> 
 <thead>        
  <tr>      
    <td id="pheader2" colspan ="20"> <?php echo "$discriptor" ?>  </td>     
  </tr>
  <tr>
    <th rowspan="2" width="4%" scope="col"><span class="tab_head">№<br/>п/п </span></th>
    <th rowspan="2" width="5%" scope="col"><span class="tab_head">Особ.<br/>рах.</span></th>
    <th rowspan="2" width="12%" scope="col"><span class="tab_head">ПІБ</span></th>
    <th rowspan="2" width="12%" scope="col"><span class="tab_head">Адреса</span></th>
    <th colspan="4" width="20%" scope="col"><span class="tab_head">Лічильник</span></th>
    <th colspan="2" width="7%" scope="col"><span class="tab_head">Номер&nbsp;пломби</span></th>
    <th rowspan="2" width="5%" scope="col"><span class="tab_head">Акт <br/> збереж</span></th>
    <th rowspan="2" width="5%" scope="col"><span class="tab_head">Дата<br/>догов.</span></th>
    <th rowspan="2" width="5%" scope="col"><span class="tab_head">Дата<br/>угоди</span></th>
    <th rowspan="2" width="5%" scope="col"><span class="tab_head">Код<br/>плг</span></th>
    <th rowspan="2" width="5%" scope="col"><span class="tab_head">Номер<br/>посвід.</span></th>
    <th rowspan="2" width="5%" scope="col"><span class="tab_head">Дата<br/>о/об</span></th>
    <th rowspan="2" width="5%" scope="col"><span class="tab_head">Середн.<br/>спожив.</span></th>
    <th rowspan="2" width="10%" scope="col"><span class="tab_head">Пок.лічил<br/>при&nbsp;перев.</span></th>
    <th rowspan="2" width="10%" scope="col"><span class="tab_head">&nbsp;&nbsp;&nbsp;&nbsp;Дата&nbsp;&nbsp;&nbsp;&nbsp;<br/>перев.</span></th>
    <th rowspan="2" width="10%" scope="col"><span class="tab_head">Підпис<br/>&nbsp;&nbsp;абонента&nbsp;&nbsp;</span></th>
    </tr>
    <tr>
        <th scope="col"><span class="tab_head">Дата</span></th>
        <th scope="col"><span class="tab_head">Номер</span></th>
        <th scope="col"><span class="tab_head">Тип</span></th>
        <th scope="col"><span class="tab_head">Зона</span></th>
        <th scope="col"><span class="tab_head">Держ</span></th>
        <th scope="col"><span class="tab_head">Енерг</span></th>
    </tr>        
  </tr>
  </thead>
  <tbody>    

 <?php
 

$order = "adr.town,adr.street, int_house, (acc.addr).house,(acc.addr).korp , int_flat, int_code, acc.code, z.id";

   //CASE WHEN length(num_meter)> 9 THEN substr(num_meter,1,8)||'<br/>'||substr(num_meter,9,100) ELSE num_meter END as num_meter,
// address_print(acc.addr)||coalesce('&nbsp;&nbsp;&nbsp;&nbsp;(&nbsp;відкл.'||to_char(sw.dt_action , 'DD.MM.YYYY')||')','') as adr,

$SQL = " select pd.*, acc.book, acc.code, adr.town as town,  adr.street as street,
 address_print(acc.addr) as adr,
  substr((c.last_name||'&nbsp;'||coalesce(c.name,'')||'&nbsp;'||coalesce(c.patron_name,''))::varchar,1,40)::varchar as abon,
  pd.num_meter,im.name as type_meter,
 pd.carry , lg.ident as lg_ident,(coalesce(lg.s_doc,'')||' '||coalesce(lg.n_doc,''))::varchar as lg_doc,
 to_char(m.dt_b, 'DD.MM.YY') as meter_date,
 to_char(acc.dt_dod, 'DD.MM.YY') as dt_dod,
 to_char(dog.date_agreem, 'DD.MM.YY') as date_agreem,
 to_char(ww.last_date, 'DD.MM.YY') as last_date,
 p1.plomb_num as plomb_chnoe, p2.plomb_num as plomb_der,
 CASE WHEN z.id = 0 THEN '' WHEN z.id in (6,9) THEN 'ніч'
                            WHEN z.id = 7 THEN 'напівпік'
                            WHEN z.id = 8 THEN 'пік'  
                            WHEN z.id = 10 THEN 'день' END as zone,
('0'||coalesce(regexp_replace(regexp_replace((acc.addr).house, '-.*?$', '') , '[^0-9]', '','g'),''))::int as int_house,
('0'||coalesce(regexp_replace(regexp_replace((acc.addr).flat, '-.*?$', '') , '[^0-9]', '','g'),''))::int as int_flat,
('0'||substring(acc.code FROM '[0-9]+'))::int as int_code,
('0'||substring(acc.book FROM '[0-9]+'))::int as int_book   

from 
clm_work_pack_data_tbl as pd 
join clm_paccnt_tbl as acc on (acc.id = pd.id_paccnt)
join clm_meterpoint_tbl as m on (m.id = pd.id_meter) 
join clm_abon_tbl as c on (c.id = acc.id_abon) 
join eqi_meter_tbl as im on (im.id = pd.id_type_meter)
join eqk_zone_tbl as z on (z.id = pd.id_zone)
join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class)        
left join (
  select id_paccnt, s_doc,n_doc, g.ident
     from lgm_abon_tbl as l
     join lgi_group_tbl as g on (l.id_grp_lgt = g.id)
     where ((dt_start < ($mmgg::date+'1 month'::interval) and dt_end is null)
            or 
            tintervalov(tinterval(dt_start::timestamp::abstime,dt_end::timestamp::abstime),tinterval($mmgg::timestamp::abstime,($mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime)))
     order by id_paccnt
 )  as lg on (lg.id_paccnt = acc.id)

left join 
(
       select csw.id_paccnt, csw.dt_action, csw.action
       from clm_switching_tbl as csw 
        join (select id_paccnt, max(dt_action) as maxdt from clm_switching_tbl where action in (1,2) group by id_paccnt) as csdt 
       on (csw.id_paccnt = csdt.id_paccnt and csw.dt_action = csdt.maxdt) 
       left join clm_switching_tbl as cswc on (csw.id_paccnt = cswc.id_paccnt and cswc.dt_action >= csw.dt_action and ((cswc.action =3 and csw.action =1) or (cswc.action =4  and csw.action =2) ) ) 
       where csw.action =1 and cswc.id_paccnt is null 

) as sw on (sw.id_paccnt = acc.id)
left join 
(
 select id_paccnt,max(plomb_num) as plomb_num  from clm_plomb_tbl
 where id_plomb_owner = 1 and id_place in (1,2,3) and dt_off is null
 group by id_paccnt order by id_paccnt
) as p1 on (p1.id_paccnt = acc.id)
left join 
(
 select id_paccnt,plomb_num from clm_plomb_tbl 
 where id_plomb_owner = 3 and dt_off is null
) as p2 on (p2.id_paccnt = acc.id)
left join 
( select id_paccnt, max(date_agreem) as date_agreem
  from clm_agreem_tbl group by id_paccnt order by id_paccnt
) as dog on (dog.id_paccnt = acc.id)
left join
(
 select id_paccnt, max(dt_work) as last_date
 from clm_works_tbl as w where idk_work = $idk_work and dt_work < $dt_work_date
 group by id_paccnt order by id_paccnt
) as ww on (ww.id_paccnt = acc.id)
 where id_pack = $id_pack  
 order by $order ;"; 


$result = pg_query($Link,$SQL) or die("SQL Error: " .pg_last_error($Link) );
$nn = 0;  
$prn_nn = 12;
//$prn_max = 55;
$prn_max = $lines_count*2+1;
$table_text= "";
$cur_street= "";
if ($result) 
{
 while($row = pg_fetch_array($result)) 
  {
    $nn++;
    $abon            =$row['abon']; 
    $adr             =$row['street'].' '.$row['adr']; 
    $street          =$row['town'].', '.$row['street'];     
    $code            =$row['code']; 
    $book            =$row['book']; 
    $num_meter       =$row['num_meter']; 
    $carry           =$row['carry']; 
    $zone_phase      =$row['zone_phase']; 
    $p_indic         =number_format($row['p_indic'],0); 
    $p_indic = str_replace(',','',$p_indic);        
    $dt_p_indic      =$row['dt_p_indic']; 
    
    $zone_phase      =$row['zone_phase']; 
    
    $adr = str_replace(' ','&nbsp;',$adr);
    
    if ($cur_street != $street) {
        $prn_nn ++;
        $cur_street=$street;
            $table_text.= "
             <tr class = 'tab_street'>
              <td colspan = '20'>$cur_street</td>
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

  <tr class ="t_str" height=15 $row_style >
    <td>$nn</td>
    <td>$book/$code</td>
    <td>$abon</td>
    <td>$adr</td>
    
    <td>{$row['meter_date']}</td>
    <td>$num_meter</td>
    <td>{$row['type_meter']}</td>
    <td>{$row['zone']}</td>
    <td>{$row['plomb_der']}</td>
    <td>{$row['plomb_chnoe']}</td>
    <td>&nbsp;</td>
    <td>{$row['date_agreem']}</td>
    <td>{$row['dt_dod']}</td>
    <td>{$row['lg_ident']}</td>
    <td>{$row['lg_doc']}</td>
    <td>{$row['last_date']}</td>        
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
   
TAB_STR;

  }
}
echo_html( $table_text);
 
 ?>
</tbody>    
</table>
    <div class ="table_footer">        
       <p>
        &nbsp;&nbsp;Дата здачi вiдомостi контрольного обходу
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