<?php

header('Content-type: text/html; charset=utf-8');

require 'abon_en_func.php';
require 'abon_ded_func.php';

$big_bill_count = 0;
$big_bill_array[0] =''; 

$bill_str_count = 0;

function BillIndicStr($text, $ind1, $ind2, $demand, $tarif, $sum, $sign,$str_count,$z = '') {
  global $bill_str_count;  
            //<td><div align="right"><span class="snorm"> $z </span></div></td>
  if ($z =="Нема" ) $z ="";
  $bill_str_count++;  
  if ($str_count==0)
  {    
    return <<<BILL_INDICSTR1
      <tr>
        <td rowspan="indic_str_count"><div align="right"><span class="snorm"> $z </span></div></td>
        <td><div align="right"><span class="snorm"> $ind1 </span></div></td>
        <td><div align="right"><span class="snorm"> $ind2 </span></div></td>
        <td><div align="right"><span class="snorm"> $demand </span></div></td>
        <td><div align="right"><span class="snorm"> $tarif </span></div></td>
        <td><span class="snorm"> $sign</span></td>
        <td><div align="right"><span class="snorm"> $sum </span></div></td>
      </tr>    
BILL_INDICSTR1;
  }
  else
  {    
    return <<<BILL_INDICSTR2
      <tr>
        <td><div align="right"><span class="snorm"> $ind1 </span></div></td>
        <td><div align="right"><span class="snorm"> $ind2 </span></div></td>
        <td><div align="right"><span class="snorm"> $demand </span></div></td>
        <td><div align="right"><span class="snorm"> $tarif </span></div></td>
        <td><span class="snorm"> $sign</span></td>
        <td><div align="right"><span class="snorm"> $sum </span></div></td>
      </tr>    
BILL_INDICSTR2;
  }
      
}

function BillDemandStr($text, $caption, $demand, $tarif, $sum) {
    global $bill_str_count;  
    if ($sum <0)
    {
        $ssum=-$sum;
        $sign = '(-)';
    }
    else 
    {
        $ssum=$sum;
        $sign = '(+)';
    }
    $bill_str_count++;
    
    return <<<BILL_DEMCSTR
      <tr>
        <td colspan="3"><span class="snorm"> $caption </span></td>
        <td><div align="right"><span class="snorm"> $demand </span></div></td>
        <td><div align="right"><span class="snorm"> $tarif </span></div></td>
        <td><span class="snorm"> $sign </span></td>
        <td><div align="right"><span class="snorm"> $ssum </span></div></td>
      </tr>

BILL_DEMCSTR;
    
}

function BillSumStr($text, $caption, $sum) {
    global $bill_str_count;  
    if ($sum <0)
    {
        $ssum=-$sum;
        $sign = '(-)';
    }
    else 
    {
        $ssum=$sum;
        $sign = '(+)';
    }
    $bill_str_count++;
    $ssum_txt = number_format($ssum,2,'.', '');
    
    return <<<BILL_SUMSTR
      <tr>
        <td colspan="5"><span class="snorm"> $caption </span></td>
        <td><span class="snorm"> $sign </span></td>
        <td><div align="right"><span class="snorm"> $ssum_txt </span></div></td>
      </tr>

BILL_SUMSTR;
    
}

function BillInfoSumStr($text, $caption, $sum) {
    global $bill_str_count;  
    $ssum=$sum;
    $sign = ' ';
    $bill_str_count++;
    return <<<BILL_SUMSTR
      <tr>
        <td colspan="5"><span class="snorm"> $caption </span></td>
        <td><span class="snorm"> $sign </span></td>
        <td><div align="right"><span class="snorm"> $ssum </span></div></td>
      </tr>

BILL_SUMSTR;
    
}

function BillIndicStrRight($text, $ind1, $ind2, $demand) {
   global $bill_str_count;  
   $bill_str_count++;
    return <<<BILL_INDICSTRR
      <tr>
        <td>&nbsp;</td>
        <td><div align="right"><span class="snorm"> $ind1 </span></div></td>
        <td><div align="right"><span class="snorm"> $ind2 </span></div></td>
        <td><div align="right"><span class="snorm"> $demand </span></div></td>
      </tr>

BILL_INDICSTRR;
}

function BillPlanDemandStr($text, $plan_month, $demand, $tarif, $sum, $z = '') {
    global $bill_str_count;  
    if ($z =="Нема" ) $z ="";
    $bill_str_count++;    
    if ($sum <0)
    {
        $ssum=-$sum;
        $sign = '(-)';
    }
    else 
    {
        $ssum=$sum;
        $sign = '(+)';
    }
    return <<<BILL_PLANDEMCSTR
      <tr>
        <td colspan="3" rowspan="indic_str_count" ><span class="snorm"> Плановий платіж за $plan_month $z </span></td>
        <td><div align="right"><span class="snorm"> $demand </span></div></td>
        <td><div align="right"><span class="snorm"> $tarif </span></div></td>
        <td><span class="snorm"> $sign </span></td>
        <td><div align="right"><span class="snorm"> $ssum </span></div></td>
      </tr>

BILL_PLANDEMCSTR;
    
}

function BillPlanDemandTarStr($text, $demand, $tarif, $sum) {
  global $bill_str_count;  
            //<td><div align="right"><span class="snorm"> $z </span></div></td>
  if ($z =="Нема" ) $z ="";
  $bill_str_count++;  
  return <<<BILL_INDICSTR22
      <tr>
        <td><div align="right"><span class="snorm"> $demand </span></div></td>
        <td><div align="right"><span class="snorm"> $tarif </span></div></td>
        <td><span class="snorm"> </span></td>
        <td><div align="right"><span class="snorm"> $sum </span></div></td>
      </tr>    
BILL_INDICSTR22;
      
}


session_name("session_kaa");
session_start();
error_reporting(0);

$Link = get_database_link($_SESSION);
$session_user = $_SESSION['ses_usr_id'];
session_write_close();


$nmp1 = "АЕ-Друк рахунків";
  
start_mpage($nmp1); //заголовок
head_addrpage();  // хедер, подключение библиотек

$book='';
$mmgg='';
$list_mode=0;

 if ((isset($_POST['bill_list']))&& ($_POST['bill_list']!=''))
 {
    $json_str = $_POST['bill_list'];
    $json_str = stripslashes($json_str); 
    $bill_id_list = json_decode($json_str,true);
    
    $where = " and (b.idk_doc = 200 or (b.idk_doc = 220 and b.demand >0)) and b.id_doc in ( ";
    //$where = " and b.idk_doc = 200 and b.id_doc in ( ";
    
    $i = 0;
    foreach($bill_id_list as $id_bill) {
    
        if ($i>0) $where.=",";
        
        $where.="$id_bill";
        $i++;
    }
    $where.=") ";
    $list_mode = 1;
 }
 else
 {
  if ((isset($_POST['id_sector_filter']))&& ($_POST['id_sector_filter']!='0'))     
  {
    $mmgg = sql_field_val('mmgg','data');
    $id_sector_filter= sql_field_val('id_sector_filter','int');
/*    
    $where = " and b.mmgg = '$mmgg' and (b.idk_doc = 200 or (b.idk_doc = 220 and b.demand >0))
            and exists (select id from prs_runner_paccnt as rp 
            where rp.id_paccnt = acc.id and rp.id_sector = $id_sector_filter )
            and not exists (select cb.id_doc from acm_bill_tbl as cb where cb.id_corr_doc = b.id_doc) ";
*/    
    
    $where = " and b.mmgg = '$mmgg' and b.idk_doc = 200 
            and rp.id_sector = $id_sector_filter ";
    
  }
  else
  {
    $book = sql_field_val('book','int');
    $mmgg = sql_field_val('mmgg','data');
    $id_sector_filter= sql_field_val('id_sector_filter','int');
/*  
    $where = " and book =  $id_book and (b.idk_doc = 200 or (b.idk_doc = 220 and b.demand >0))
    and b.mmgg = $mmgg 
    and not exists (select cb.id_doc from acm_bill_tbl as cb where cb.id_corr_doc = b.id_doc)  ";
*/
    $where = " and book =  $id_book and b.idk_doc = 200
    and b.mmgg = $mmgg ";
    
  }
  
 }
      
print('<link rel="stylesheet" type="text/css" href="css/ded_styles.css" /> ');
//print('<link rel="stylesheet" type="text/css" href="css/layout-default-latest.css" /> ');
//print('<script type="text/javascript" src="js/jquery.layout.min-latest.js"></SCRIPT>');
//print('<script type="text/javascript" src="js/jquery.form.js"></script>');
//print('<script type="text/javascript" src="js/jquery.validate.min.js?version='.$app_version.'" ></script>');
//print('<script type="text/javascript" src="js/jquery-ui-1.8.20.custom.min.js"></script> ');
//print('<script type="text/javascript" src="js/jquery.ui.datepicker-uk.js"></script> ');
//print('<script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script> ');
print('<script type="text/javascript" src="js/jquery-barcode-2.0.2.min.js"></script>');

?>

<style type="text/css">
body {background-color: white}
.bill_class {min-height:9.5cm;page-break-inside: avoid;}
.bill_big_class {page-break-inside: avoid;}
.sbold {font-size: 9pt; font-weight: bold; font-style: italic; margin: 0px; padding: 0px; font-family: "Courier New", Courier, monospace; line-height: 10px; }
.sbolds {font-size: 8pt; font-weight: bold; font-style: italic; margin: 0px; padding: 0px; font-family: "Courier New", Courier, monospace; line-height: 9px; }
.sboldm {font-size: 9pt; font-weight: bold; font-style: italic; margin: 0px; padding: 0px; font-family: "Courier New", Courier, monospace; line-height: 20px; }
.snorm {font-style: italic; margin: 0px; padding: 0px; font-size: 9pt; font-family: "Courier New", Courier, monospace; line-height: 10px; }
.snorm_p {font-style: italic; margin: 0px; padding: 0px; font-size: 8pt; font-family: "Courier New", Courier, monospace; line-height: 8px; }

/*.smod1 {font-style: italic; margin:5px; padding: 0px; font-size: 9pt; font-family: "Courier New", Courier, monospace; 	line-height: 10px;}*/
.smod1 {font-style: italic; margin:5px; padding: 0px; font-size: 9pt; font-family: "Times New Roman", Times, serif; line-height: 9px;}
.smod2 {font-style: italic; margin:0, 5px; padding: 0px; font-size: 10pt; font-family: "Courier New", Courier, monospace; line-height: 10px;}

.ssmall {padding: 0px; font-family: "Times New Roman", Times, serif; line-height: 7px; font-size: 9px; margin: 0px;}
.ssmallb {padding: 0px; font-family: "Times New Roman", Times, serif; font-weight: bold; line-height: 8px; font-size: 9px; margin: 0px;}
.sboldp {font-size: 9pt; font-weight: bold; font-style: italic; margin: 0px; padding: 3px; font-family: "Courier New", Courier, monospace; line-height: 10px; }

table.bill_left_table { border-collapse:collapse; }
table.bill_left_table tr {line-height: 15px; }
table.bill_left_table td, table.bill_left_table th { border:1px solid black; }
table.bill_left_table td {padding: 0px 3px;}
table.bill_right_indic_table {border-collapse:collapse;}
table.bill_right_indic_table tr {line-height: 12px; padding: 0px;}
/*table.bill_right_indic_table td {height: 10px; padding: 0px;}*/
table.bill_left_total { border-collapse:collapse; }
table.bill_left_total td, table.bill_left_total th { border:1px solid black; }

p { margin: 0; }
@page {margin-left:0.5cm; margin-right:0.5cm;}

</style>

<script type="text/javascript">

        var settings = {
          output:"css",
          bgColor: "#FFFFFF",
          color: "#000000",
          barWidth: 1,
          barHeight: 20,
          moduleSize: 5,
          posX: 0,
          posY: 0,
          addQuietZone: 1
        };

        var btype = "code128";
        
        jQuery(function(){ 
            
            $('.barcode_area').each(function() {
                  $(this).html("").show().barcode($(this).attr('data_value'), btype, settings);
            });

        });

</script>

</head>
<body >
    
<?php
 
$SQL = " select r.*, address_print_full(r.addr,4) as addr_full,
 b.name as ae_bank_name 
    from syi_resinfo_tbl as r
    left join bank as b on (b.mfo = r.ae_mfo)
    where r.id_department = syi_resid_fun() ;"; 

$result = pg_query($Link,$SQL) or die("SQL Error: " .pg_last_error($Link) );
$res_info = pg_fetch_array($result);
 
$res_name       =$res_info['name']; 
$res_short_name =$res_info['short_name']; 

$res_okpo_num =$res_info['okpo_num']; 
$res_bank_acc =$res_info['ae_account']; 
$res_bank =$res_info['ae_bank_name']; 
$res_bank_mfo =$res_info['ae_mfo']; 
$res_phones =$res_info['phone_bill']; 
$ikc_phones =$res_info['phone_ikc']; 
$ikc_addr =$res_info['addr_ikc']; 

 
 
$SQL = " select b.*, to_char(b.reg_date, 'DD.MM.YYYY') as reg_date_str, 
acc.book, acc.code, 
date_part('year', b.mmgg_bill) as bill_year , 
date_part('month', b.mmgg_bill) as bill_month, 
date_part('month', b.mmgg_bill+'1 month'::interval) as next_month, 
date_part('year', b.mmgg_bill+'1 month'::interval) as next_year,
date_part('day', b.mmgg_bill+'1 month'::interval-'1 day'::interval) as last_day ,
round(s.b_val ,2) as saldo_b, s.dt_val, s.dt_valtax, 
round(s.kt_val ,2) as saldo_pay,
round(s.e_val ,2) + COALESCE(bk2.value,0) as saldo_e, 
round(s.e_valtax,2) + COALESCE(bk2.value_tax,0) as e_valtax,
('0'||substring(acc.code FROM '[0-9]+'))::int as int_code,
('0'||substring(acc.book FROM '[0-9]+'))::int as int_book,
acc.index_accnt, coalesce(op.phone||';','') as phone,
CASE WHEN coalesce(b.reg_num,'')='' THEN to_char(b.mmgg_bill, 'MM-YY') ELSE b.reg_num END as bill_num,
coalesce(ba.id_doc,0) as id_avans, COALESCE(ba.value,0) as avans_val,COALESCE(ba.value_tax,0) as avans_tax ,
COALESCE(bk.value,0) as korr_val,
COALESCE(bk2.value,0) as korr_val2,
(adr.street||' '||
   (coalesce('буд.'||(acc.addr).house||'','')||
		coalesce('/'||(acc.addr).slash||' ','')|| 
			coalesce(' корп.'||(acc.addr).korp||'','')||
				coalesce(', кв. '||(acc.addr).flat,'')||
					coalesce('/'||(acc.addr).f_slash,''))::varchar
)::varchar as adr
from acm_bill_tbl as b 
join clm_paccnt_tbl as acc on (acc.id = b.id_paccnt)
join clm_abon_tbl as c on (c.id = acc.id_abon) 
join acm_saldo_tbl as s on (s.id_paccnt = b.id_paccnt and s.mmgg = b.mmgg_bill and s.id_pref = 10)
join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class)

join prs_runner_paccnt as rp on (rp.id_paccnt = acc.id)
join prs_runner_sectors as rs on (rs.id = rp.id_sector)

left join acm_bill_tbl as ba on (ba.id_paccnt = b.id_paccnt and ba.id_pref = 12 and ba.mmgg_bill = (b.mmgg_bill+'1 month'::interval)::date )

left join ( select id_paccnt, mmgg, sum(value) as value from acm_bill_tbl where id_pref = 10 and idk_doc = 220
  group by id_paccnt, mmgg order by id_paccnt) as bk  on (bk.id_paccnt = b.id_paccnt and (bk.mmgg = b.mmgg and b.idk_doc = 200 or bk.mmgg = b.mmgg_bill and b.idk_doc = 220 )  )

left join ( select id_paccnt, mmgg_bill, sum(value) as value , sum(value_tax) as value_tax 
  from acm_bill_tbl where id_pref = 10 and idk_doc = 220
  group by id_paccnt, mmgg_bill order by id_paccnt) as bk2  on (bk2.id_paccnt = b.id_paccnt and bk2.mmgg_bill = b.mmgg_bill and b.idk_doc = 220)

left join prs_persons as op on (op.id = rs.id_operator)   

where b.id_pref = 10 $where
 order by int_book, book, int_code, acc.code, b.mmgg_bill;"; 


// -- address_print_full(acc.addr,5) as adr,  

$result = pg_query($Link,$SQL) or die("SQL Error: " .pg_last_error($Link) );
$nn = 0;  
$prn_nn = 0;
$prn_max = 3;
$table_text= "";
$bill_text= "";
$bill_str_count = 0;

//echo $SQL;

if ($result) 
{
 while($row = pg_fetch_array($result)) 
  {
    $nn++;

    $id_doc          =$row['id_doc']; 
    $id_avans        =$row['id_avans']; 
    $id_paccnt       =$row['id_paccnt']; 
    $adr             =$row['adr']; 
    //$street          =$row['street'];     
    $street          ="";     
    $code            =$row['code']; 
    $book            =$row['book']; 
    
    $reg_num         =$row['bill_num']; 
    $reg_date        =$row['reg_date_str']; 
    
    $index_accnt      =$row['index_accnt']; 
    
    //$value           =number_format($row['value'],2,'.', ''); 
    //$value_tax       =number_format($row['value_tax'],2,'.', ''); 
   
    $bill_mmgg       =$row['mmgg_bill']; 
    $bill_year       =$row['bill_year']; 
    $bill_month      =$row['bill_month']; 
    $last_day        =$row['last_day']; 
    
    $bill_month_i = ukr_month($bill_month,2);
    $bill_month_a = ukr_month($bill_month,1);
    
    $next_year       =$row['next_year']; 
    $next_month      =$row['next_month']; 
    $next_month_s = ukr_month($next_month,0);
    
    $saldo_b = $row['saldo_b']; 
    $saldo_e = $row['saldo_e']; 
    $saldo_e_tax = $row['e_valtax']; 
    $sum_pay = $row['saldo_pay'];

    $avans_val = $row['avans_val']; 
    $avans_tax = $row['avans_tax']; 
    $korr_val = $row['korr_val']; 
    $operator_phone = $row['phone']; 
    
    $value_calc = $row['value_calc'];    

    //сумма к оплате  = конечное сальдо + плановый платеж
//    $total_value =$saldo_e + $avans_val;  
//    $total_value_tax =$saldo_e_tax+$avans_tax;

    if ($saldo_b >=0)
    {
     $caption_saldo_b ="Борг на початок";
    }
    else
    {
     $caption_saldo_b ="Переплата на початок";
    }
        
    if ($saldo_e >=0)
    {
     $caption_saldo_e ="Борг  на кінець";
    }
    else
    {
     $caption_saldo_e ="Переплата на кінець";
    }

    
    $indic_str_count = 0;
    
    $right_meters_text='';
    $left_meters_text='';
    $bill_text= '';
    $bill_str_count = 0;
   
    $SQL2 = "select bs.id_paccnt, bs.id_zone, z.nm as zone, z.koef,
            count(distinct bs.id_meter) as meter_cnt, 
            count(distinct bs.id_summtarif) as tar_cnt,
            sum(bs.demand) as demand, sum(bs.summ) as summ  
        from acm_bill_tbl as b 
        join acm_summ_tbl as bs on (bs.id_doc = b.id_doc)
        join eqk_zone_tbl as z on (z.id = bs.id_zone)
        where b.id_doc = $id_doc
        group by bs.id_paccnt, bs.id_zone,z.nm , z.koef
        order by id_zone ;"; 


    $result2 = pg_query($Link,$SQL2) or die("SQL Error: " .pg_last_error($Link) );

    if ($result2) 
    {
        while($row_zone = pg_fetch_array($result2)) 
        {

            $id_zone     =$row_zone['id_zone'];         
            $zone_koef   =$row_zone['koef'];         
            $zone_name   =$row_zone['zone'];         
            $tar_cnt     =$row_zone['tar_cnt'];          
            $meter_cnt   =$row_zone['meter_cnt'];    
            $zone_demand =$row_zone['demand'];         
            $zone_summ   =$row_zone['summ'];         

            $indic_str_count = 0;
            
            $SQL4 = "select bs.id_summtarif, td.value as tarif_val,
            sum(bs.demand) as demand, sum(bs.summ) as summ
            from acm_summ_tbl as bs
            join aqd_tarif_tbl as td on (td.id = bs.id_summtarif)
            where bs.id_paccnt = $id_paccnt and bs.id_zone = $id_zone
            and bs.id_doc = $id_doc
            group by bs.id_summtarif, td.value
            order by td.value ;"; 
            
            $result4 = pg_query($Link,$SQL4) or die("SQL Error: " .pg_last_error($Link) );
            
            if ($tar_cnt==1)
            {
              $row_tarif = pg_fetch_array($result4);
              $sum_tarif =Round($row_tarif['tarif_val']*100*$zone_koef,2);
            }
            else
            {
              $sum_tarif ='';
            }
            
            $SQL3 = "select i.dat_ind, i.id_meter,i.num_eqp,
            trim(to_char(i.value,'FM999999999.999'),'.')::varchar as value,
            trim(to_char(ip.value,'FM999999999.999'),'.')::varchar as value_prev,
            trim(to_char(i.value_cons,'FM999999999.999'),'.')::varchar as value_cons
            from acm_indication_tbl as i
            left join acm_indication_tbl as ip on (i.id_prev = ip.id)
            where i.id_paccnt = $id_paccnt and i.id_zone = $id_zone
            and date_part('year', i.mmgg) = $bill_year  
            and date_part('month',i.mmgg) = $bill_month  
            and ip.value is not null
            order by i.dat_ind,i.num_eqp ;"; 


            $result3 = pg_query($Link,$SQL3) or die("SQL Error: " .pg_last_error($Link) );

            if ($result3) 
            {
                $indic_count = pg_num_rows($result3);
                
                while($row_meter = pg_fetch_array($result3)) 
                {
                    if ($indic_count>1)
                    {
                        $left_meters_text.=BillIndicStr($left_meters_text, $row_meter['value'],
                                                               $row_meter['value_prev'], 
                                                               $row_meter['value_cons'], 
                                                               '-', '-','',$indic_str_count,$zone_name );
                        $indic_str_count++;                        
                    }
                    else
                    {
                        
                        if (($zone_summ!=0)&&($row_meter['value_prev']==$row_meter['value']))
                        {    //если есть 2 показания по одной зоне, одно из которых с нулевым потр.
                            $left_meters_text.=BillIndicStr($left_meters_text, $row_meter['value'],
                                                               $row_meter['value_prev'], 
                                                               $row_meter['value_cons'], 
                                                               '-', 0,'',$indic_str_count, $zone_name );
                        }
                        else
                        {
                            $left_meters_text.=BillIndicStr($left_meters_text, $row_meter['value'],
                                                               $row_meter['value_prev'],
                                                               $row_meter['value_cons'],
                                                               $sum_tarif, $zone_summ,'(+)',$indic_str_count, $zone_name );
                        }
                        $indic_str_count++;                        
                    }
                    //таблица в правой части счета
                    $right_meters_text.=BillIndicStrRight($right_meters_text, 
                            $row_meter['value'],
                            $row_meter['value_prev'],
                            $row_meter['value_cons']);    
                    
                }

                if ($indic_count>1)
                {
                   $left_meters_text.=BillIndicStr($left_meters_text, '','', 
                                                          $zone_demand,$sum_tarif, $zone_summ, '(+)', $indic_str_count,$zone_name );
                   $indic_str_count++;                    
                }
                
            }
            if ($tar_cnt>1)
            {
                while($row_tarif = pg_fetch_array($result4))
                {
                    $sum_tarif =Round($row_tarif['tarif_val']*100*$zone_koef,2);
                    $left_meters_text.=BillIndicStr($left_meters_text, '','', 
                                                          $row_tarif['demand'],
                                                          $sum_tarif, 
                                                          $row_tarif['summ'],'',$indic_str_count );
                    $indic_str_count++;                    
                }
            }
        $left_meters_text = str_replace('indic_str_count',$indic_str_count,$left_meters_text);             
        }
    };
    
   // bill header !!!!
    $bill_text.= <<<BILL_HDR_LEFT
    
    <div class="bill_class">
    <div class="bill_left_part" style="float:left; width:12.8cm"> 
     <p class="sbold"> $res_short_name ПАТ &quot;ЧЕРНІГІВОБЛЕНЕРГО&quot; м.Чернігів</p> 
     <p class="sbold">Рахунок № $reg_num від $reg_date року ЗА ЕЛЕКТРОЕНЕРГІЮ ($index_accnt)</p> 
     <p class="snorm">Особ рах. $book/$code,Адреса:$street $adr</p> 
        
     <table class="bill_left_table" width="480" border="1" cellspacing="0"> 
    
BILL_HDR_LEFT;
    
//<td width="96" rowspan="$indic_str_count" valign="top"><span class="snorm">&nbsp;Нараховано&nbsp;</span></td>

$bill_text.= <<<BILL_TABLEHDR_LEFT
        <tr>
            <td width="96" rowspan="2" valign="top"><span class="snorm">&nbsp;Нараховано&nbsp;</span></td>
            <td colspan="2"><span class="snorm">Показ. лічильника </span></td>
            <td width="49"><span class="snorm">Спожито</span></td>
            <td width="63"><span class="snorm">Вартість</span></td>
            <td colspan="2"><div align="center"><span class="snorm">Сума </span></div></td>
        </tr>
        <tr>
            <td width="66"><div align="center"><span class="snorm"> останні </span></div></td>
            <td width="88"><div align="center"><span class="snorm">попередні </span></div></td>
            <td><div align="center"><span class="snorm">кВтг</span></div></td>
            <td><span class="snorm">коп/кВтг </span></td>
            <td colspan="2"><div align="center" class="snorm">грн.</div></td>
          </tr>
    
BILL_TABLEHDR_LEFT;
    
    $bill_text.=$left_meters_text;

    $bill_text.=BillsumStr($bill_text, "$caption_saldo_b $bill_month_a $bill_year р." ,$saldo_b  );    
    
    if ($korr_val<>0)
    {
      $bill_text.=BillsumStr($bill_text, "Коригування попередніх періодів." ,$korr_val  );            
    }

    //------------------------------------------------------------------
    // если есть платежки типа корректировка, их тоже отдельными строками
     $SQL_corpay = "select round( sum(value) ,2) as cor_pay
     from acm_pay_tbl as p
     where p.id_paccnt = $id_paccnt 
     and p.mmgg = '$bill_mmgg' and p.id_pref = 10 and p.idk_doc =120 ;"; 
     $sum_corpay=0;
            
     $result_cor = pg_query($Link,$SQL_corpay) or die("SQL Error: " .pg_last_error($Link) );
            
     if ($result_cor) 
     {
       while($row_cor = pg_fetch_array($result_cor)) 
       {
           
        $sum_corpay = $row_cor['cor_pay'];
        
        if ($sum_corpay !=0)
        {
           $bill_text.=BillsumStr($bill_text, "Коригування опл. попередніх періодів." ,-$sum_corpay );
        }
        
       }
     }
    

// -------------льготы ---------------------------
     $SQL_lgt = "select id_grp_lgt, bill_name as name,
            sum(demand_lgt) as demand, sum(summ_lgt) as summ from 
            (
             select s.id_grp_lgt,  s.demand_lgt, s.summ_lgt,
             CASE WHEN coalesce(s.demand_lgt,0) = 0 and coalesce(s.summ_lgt,0)<>0 THEN lg.bill_name||'(перерах)' ELSE lg.bill_name END 
             from acm_lgt_summ_tbl as s
             join lgi_group_tbl as lg on (lg.id = s.id_grp_lgt)
             where s.id_paccnt = $id_paccnt 
             and s.id_doc = $id_doc
            ) as ss
            group by id_grp_lgt, bill_name
            order by bill_name ;"; 
            
     $result_lgt = pg_query($Link,$SQL_lgt) or die("SQL Error: " .pg_last_error($Link) );
            
     if ($result_lgt) 
     {
       while($row_lgt = pg_fetch_array($result_lgt)) 
       {
        $bill_text.=BillDemandStr($bill_text, $row_lgt['name'] , $row_lgt['demand'], "" , -$row_lgt['summ'] ); 
       }
     }

    //------------------------------------------------
    //оплаченная субсидия 
     $sum_subs=0;
     $sum_subs_recalc=0;
     $subs_comment='';
     
     
     
     $SQL_subs = "select round(sum( CASE WHEN p.idk_doc = 110 THEN value END ) ,2) as subs_pay,
                         round(sum( CASE WHEN p.idk_doc = 193 THEN value END ) ,2) as subs_recalc
     from acm_pay_tbl as p
     where p.id_paccnt = $id_paccnt 
     and p.mmgg = '$bill_mmgg' and p.id_pref = 10 and p.idk_doc in ( 110, 193) ;"; 
     
            
     $result_subs = pg_query($Link,$SQL_subs) or die("SQL Error: " .pg_last_error($Link) );
            
     if ($result_subs) 
     {
       while($row_subs = pg_fetch_array($result_subs)) 
       {
           
        $sum_subs = $row_subs['subs_pay'];
        
        $sum_subs_recalc = $row_subs['subs_recalc'];
        
        if ($sum_subs !=0)
        {
           $bill_text.=BillsumStr($bill_text, "Компенсацiя призначена вiддiлом субсидiї" ,-$sum_subs );
           
           $subs_comment='(Для абонентiв,що отримують субсидiю,сума до сплати призначаеться вiддiлом субсидiї)<br/>';
        }

        if ($sum_subs_recalc !=0)
        {
           $bill_text.=BillsumStr($bill_text, "Перерахунок субсидii" ,-$sum_subs_recalc );
        }
        
       }
     }
     $sum_pay = round($sum_pay-$sum_subs-$sum_subs_recalc-$sum_corpay,2);
    
     
    // оплата абонента 
     if ($sum_pay !=0)
    {
        $bill_text.=BillsumStr($bill_text, "Сплачено $bill_month_i $bill_year р." ,-$sum_pay  );
    }
    
    $bill_text.=BillsumStr($bill_text, "$caption_saldo_e $bill_month_a $bill_year р." ,$saldo_e  );
    //$bill_text.=BillDemandStr($bill_text, "Плановий платіж за $next_month_s $next_year р." , 149, 28.02 ,41.75  );
   /* 
    // Справочная инф по субсидии
    //обяз.платеж и потребление свіше норми
     $SQL_subs_info = " select ob_pay, norma_pay, subs_svn from acm_subs_tbl
     where id_paccnt = $id_paccnt and mmgg = '$bill_mmgg' and val_month <> 0
     limit 1 ; "; 
            
     $result_subs = pg_query($Link,$SQL_subs_info) or die("SQL Error: " .pg_last_error($Link) );
            
     if ($result_subs) 
     {
       while($row_subs = pg_fetch_array($result_subs)) 
       {
           
        $ob_pay = $row_subs['ob_pay'];
        $norma_pay = $row_subs['norma_pay'];
        $subs_svn = $row_subs['subs_svn'];
        
        if ($ob_pay !=0)
        {
           $bill_text.=BillInfoSumStr($bill_text, "Обов'язковий платіж" ,$ob_pay );
        }

        if ($norma_pay < $value_calc)
        {
           $bill_text.=BillInfoSumStr($bill_text, "Споживання понад норми" , $subs_svn );
        }
        
       }
     }
 */   
    
   // если есть, выводим плановое потребление
   //----------------------------------------------------------------------  
   $sum_avans  =0; 
   $sum_lgt_avans = 0;           
   
   if(($id_avans!=0) && ($sum_subs==0)) 
   // if(false)  
   {
       
    $SQL22 = "select bs.id_paccnt, bs.id_zone, z.nm as zone, z.koef,
            count(distinct bs.id_meter) as meter_cnt, 
            count(distinct bs.id_summtarif) as tar_cnt,
            sum(bs.demand) as demand, sum(bs.summ) as summ  
        from acm_bill_tbl as b 
        join acm_summ_tbl as bs on (bs.id_doc = b.id_doc)
        join eqk_zone_tbl as z on (z.id = bs.id_zone)
        where b.id_doc = $id_avans
        group by bs.id_paccnt, bs.id_zone,z.nm , z.koef
        order by id_zone ;"; 

//echo $SQL22;
    $result22 = pg_query($Link,$SQL22) or die("SQL Error: " .pg_last_error($Link) );
    
    $left_meters_text='';

    if ($result22) 
    {
        while($row_zone = pg_fetch_array($result22)) 
        {

            $id_zone     =$row_zone['id_zone'];         
            $zone_koef   =$row_zone['koef'];         
            $zone_name   =$row_zone['zone'];         
            $tar_cnt     =$row_zone['tar_cnt'];          
            //$meter_cnt   =$row_zone['meter_cnt'];    
            $meter_cnt = 1;
            $zone_demand =$row_zone['demand'];         
            $zone_summ   =$row_zone['summ']; 
            
            $sum_avans = $sum_avans+$zone_summ;

            $indic_str_count = 0;
            
            $SQL44 = "select bs.id_summtarif, td.value as tarif_val,
            sum(bs.demand) as demand, sum(bs.summ) as summ
            from acm_summ_tbl as bs
            join aqd_tarif_tbl as td on (td.id = bs.id_summtarif)
            where bs.id_paccnt = $id_paccnt and bs.id_zone = $id_zone
            and bs.id_doc = $id_avans
            group by bs.id_summtarif, td.value
            order by td.value ;"; 
            
            //echo $SQL44;
            
            $result44 = pg_query($Link,$SQL44) or die("SQL Error: " .pg_last_error($Link) );
            
            if ($tar_cnt==1)
            {
              $row_tarif = pg_fetch_array($result44);
              $sum_tarif =Round($row_tarif['tarif_val']*100*$zone_koef,2);
            }
            else
            {
              $sum_tarif ='';
            }
            
            $SQL33 = "select i.demand 
            from clm_plandemand_tbl as i
            where i.id_paccnt = $id_paccnt and i.id_zone = $id_zone
            and mmgg = '$bill_mmgg'::date+'1 month'::interval ;"; 

            //echo $SQL33;
            $result33 = pg_query($Link,$SQL33) or die("SQL Error: " .pg_last_error($Link) );

            if ($result33) 
            {
                while($row_plan = pg_fetch_array($result33)) 
                {  //echo '-1-'.$row_plan['demand'];
                   $left_meters_text.=BillPlanDemandStr($left_meters_text,"$next_month_s $next_year р." , 
                                                               $row_plan['demand'],
                                                               $sum_tarif, 
                                                               $zone_summ, $zone_name );
                   $indic_str_count++;                        
                }
            }
            if ($tar_cnt>1)
            {
                while($row_tarif = pg_fetch_array($result44))
                {
                    
                    $sum_tarif =Round($row_tarif['tarif_val']*100*$zone_koef,2);
                    
                    $left_meters_text.=BillPlanDemandTarStr($left_meters_text, 
                                                          $row_tarif['demand'],
                                                          $sum_tarif, 
                                                          $row_tarif['summ'],'' );
                    $indic_str_count++;                    
                }
            }
        $left_meters_text = str_replace('indic_str_count',$indic_str_count,$left_meters_text);             
        }
    };
    $bill_text.=$left_meters_text;          
    // -------------льготы для аванса---------------------------
     $SQL_lgt = "select id_grp_lgt, bill_name as name,
            sum(demand_lgt) as demand, sum(summ_lgt) as summ from 
            (
             select s.id_grp_lgt,  s.demand_lgt, s.summ_lgt,
             CASE WHEN coalesce(s.demand_lgt,0) = 0 and coalesce(s.summ_lgt,0)<>0 THEN lg.bill_name||'(перерах)' ELSE lg.bill_name END 
             from acm_lgt_summ_tbl as s
             join lgi_group_tbl as lg on (lg.id = s.id_grp_lgt)
             where s.id_paccnt = $id_paccnt 
             and s.id_doc = $id_avans
            ) as ss
            group by id_grp_lgt, bill_name
            order by bill_name ;"; 
            
     $result_lgt = pg_query($Link,$SQL_lgt) or die("SQL Error: " .pg_last_error($Link) );

     if ($result_lgt) 
     {
       while($row_lgt = pg_fetch_array($result_lgt)) 
       {
        $bill_text.=BillDemandStr($bill_text, $row_lgt['name'] , $row_lgt['demand'], "", -$row_lgt['summ'] ); 

        $sum_lgt_avans = $sum_lgt_avans+$row_lgt['summ'];
        
       }
     }
    
  //  $total_value=$total_value+$sum_avans - $sum_lgt_avans;     
  //  $total_value_tax = $total_value/6; 
     
    $total_value =$saldo_e + $avans_val;  
    $total_value_tax =$saldo_e_tax+$avans_tax;
     
   }
   else
   {
    $total_value =$saldo_e;  
    $total_value_tax =$saldo_e_tax;
       
   }
   // --------------------------------------------------------------------------------------

   
   
    if ($total_value>0)
        $value_txt           =number_format($total_value,2,'.', ''); 
    else
        $value_txt = '0.00';
        
    if ($total_value_tax>0)
        $value_tax_txt       =number_format($total_value_tax,2,'.', ''); 
    else
        $value_tax_txt = '0.00';
    

    $bill_text.= <<<BILL_FOOTER_LEFT

         <tr>
        <td colspan="3" rowspan="2"><span class="sbolds"> Підлягає оплаті в 10-денний термін <br>
          з дня отримання. Зберігати 3 роки </span>
        </td>
        <td colspan="2"><div align="right"><span class="sbold">Всього до оплати </span></div></td>
        <td colspan="2"><div align="right"><span class="sbold">$value_txt</span></div></td>
      </tr>
      <tr>
        <td colspan="2"><div align="right"><span class="snorm">втч ПДВ - 20 %</span></div></td>
        <td colspan="2"><div align="right"><span class="snorm">$value_tax_txt</span></div></td>
      </tr>
    </table>
    
    <p class="sbold">$subs_comment Телефони по розрахунках: $operator_phone $res_phones</p>

    <p class="ssmall">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Ви можете зазначити свою суму до сплати у платіжному документі</p>
    <p class="ssmall">Відповідно до п.22 ПКЕЕН, оплата спожитої електроенергії може здійснюватися за</p>
    <p class="ssmall">розрахунковими книжками або за платіжними документами, виписаними енергопостачальником</p>
    
    <p class="ssmall">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Якщо у Вас виникли питання з приводу постачання електроенергії або врегулювання </p>   
    <p class="ssmall">взаємовідносин з енергопостачальником пропонуємо звернутися до : </p>        
  
    <p class="ssmall">&nbsp;&nbsp;- інформаційно-консультаційного центру (ІКЦ) тел. $ikc_phones </p>

    <p class="ssmall">&nbsp;&nbsp;- кол-центру тел.:0-800-210310, E-mail: call-center@energy.cn.ua </p>        
    <p class="ssmall">адреса веб-сайту: http://chernigivoblenergo.com.ua </p>        

    <p class="ssmallb">Не будьте байдужими, повідомляйте про факти крадіжок електроенергії по тел. 0-800-210310 </p>

</div>

BILL_FOOTER_LEFT;
    
//    <p class="sbold">Адреса ІКЦ $ikc_addr</p>
    
    
    $bill_text.= <<<BILL_HEADER_RIGHT

  <div style="float:left ; width:6.5cm;margin-left:1cm">
    <p align="center" class="sbold">Повідомлення № $reg_num ($index_accnt)</p>
    <p align="center" class="sbold">ПАТ &quot;ЧЕРНІГІВОБЛЕНЕРГО&quot; </p>
    <p align="center" class="snorm">$res_short_name</p>
    <p align="center" class="snorm_p">код $res_okpo_num р/р $res_bank_acc</p>
    <p align="center" class="snorm_p">$res_bank ,МФО $res_bank_mfo</p>
    
    <p class="sboldm">Особ рах. $book/$code </p>
    
    <p class="sbold"> ПІБ_________________________________</p>
    <p class="smod1">(ПІБ споживача заповнюється самостійно в обов'язковому порядку)</p>
    
    <p class="smod2">$street $adr</p>
    <table class="bill_right_indic_table" width="252" border="0" cellspacing="0" >
      <tr>
        <td width="77"><span class="snorm">Лічильник: </span></td>
        <td width="53"><div align="right"><span class="snorm">Поточні</span></div></td>
        <td width="63"><div align="right"><span class="snorm">Попередні</span></div></td>
        <td width="63"><div align="right"><span class="snorm">Різниця</span></div></td>
      </tr>


BILL_HEADER_RIGHT;

    $bill_text.=$right_meters_text;
    
    $bill_text.= <<<BILL_FOOTER_RIGHT

    </table>
    <p class="ssmall">&nbsp;</p>
    <table width="93%" border="1" cellspacing="0" cellpadding="0" class="bill_left_total">
      <tr>
        <td width="64%" ><div align="left" class="sboldp">ВСЬОГО ДО ОПЛАТИ </div></td>
        <td width="36%" ><div align="right" class="sboldp">$value_txt</div></td>
      </tr>
    </table>
    <p class="snorm">&nbsp; </p>
    <!--<div align="center" class="barcode_area" data_value = "$id_doc;$id_paccnt;$bill_month.$bill_year;$value"> </div> <br/> -->
    
    <p class="snorm" style = "line-height: 15px;" >ФАКТИЧНО ___________,_______ Підпис </p>
    <p align="center" class="snorm" style = "line-height: 15px;"> Покази лічильника на дату оплати</p>

   <div align="center">
    <table width="70%" border="1" cellspacing="0" cellpadding="0" class="bill_left_total">
      <tr>
        <td width="16%" >&nbsp;</td>
        <td width="16%" >&nbsp;</td>
        <td width="16%" >&nbsp;</td>
        <td width="16%" >&nbsp;</td>
        <td width="16%" >&nbsp;</td>
        <td width="16%" >&nbsp;</td>
      </tr>
    </table>
   </div> 
   </div>
</div>
<div style="clear:both;" > </div>

BILL_FOOTER_RIGHT;

   if ($bill_str_count > 12)
   {
    $big_bill_array[$big_bill_count] =$bill_text; 
    $big_bill_count++;
   }
   else
   {    
    
    $prn_nn=$prn_nn+1;
    if ($prn_nn>=$prn_max)
    {
        $bill_text.= '<br style="page-break-after: always">';

        $prn_nn = 0;
    
    }
    else
    {
        $bill_text.= '<HR style="margin-bottom: 10px" SIZE="1" WIDTH="100%" NOSHADE> ';
    }

    $table_text.=$bill_text;
   }
  }
  
}

// ---------------------------------------------------------- 
if ($big_bill_count>0)
{
    foreach($big_bill_array as $big_bill) {
        
      $table_text.=$big_bill;
      $table_text.= '<HR style="margin-bottom: 10px; margin-top: 10px" SIZE="1" WIDTH="100%" NOSHADE> ';
      
    }
    
}


echo $table_text;

end_mpage();
?>