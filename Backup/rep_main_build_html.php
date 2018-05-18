<?php

set_time_limit(12000); 

if (isset($_POST['submitButton'])) {
      $oper = $_POST['submitButton'];
}
 else {
    
    if (isset($_POST['oper'])) { 
          $oper = $_POST['oper'];
    }
}

 if (isset($_POST['to_xls']))
      $to_xls = trim($_POST['to_xls']);
 else 
      $to_xls =0;
 

//------------------------------------------------

if ($to_xls==1) 
{
    
    header('Content-type: application/vnd.ms-excel; charset=utf-8;');
    //header('Content-type: application/x-msdownload; charset=utf-8');
    //header('Content-disposition: inline; filename="'.$oper.date('_ymd_his', time()).'.xls."');
    header('Content-disposition: attachment; filename="'.$oper.date('_ymd_his', time()).'.xls."');
    //header("Content-Transfer-Encoding: binary ");
}
else
    header('Content-type: text/html; charset=utf-8');



require 'abon_en_func.php';
require 'abon_ded_func.php';
/*
require_once ("phpwee-php-minifier/phpwee.php");

function sanitize_output($buffer) {
      return  PHPWee\Minify::html($buffer);
}
*/

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

//ob_start("sanitize_output");

function number_format_ua ($number, $decimals = null)
{
    global $to_xls;
    
    if ($to_xls==1)
    {
        $result = number_format($number, $decimals);
        $result = str_replace(',','',$result);
    }
    else
    {
        
        if ($number==0)
        {
            $result ='';
        }
        else
        {
            $result = number_format($number, $decimals);
            $result = str_replace(',','&nbsp;',$result);
        }
    }
    Return ($result);
}

function rep_abon_dt($Link, $p_mmgg, $nmonth, $dt_sum) {

    
  $month = $nmonth; 
  $SQL = "select ''''||to_char( value_ident::date , 'YYYY-MM-DD')||'''' as start_mmgg ,
        CASE WHEN (value_ident::date- '1 month'::interval) >= ($p_mmgg::date - '{$month} month'::interval )::date THEN 1 ELSE 0 END as old
        from syi_sysvars_tbl where ident = 'dt_start';";
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);    
    
   
    if ($result) {
        $row = pg_fetch_array($result); 
        $start_mmgg=$row['start_mmgg'];
        $fold = $row['old'];
    }

 $SQL = "delete from rep_abon_dt_tbl;";
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);    
    
    
 $SQL_base_new = "
    insert into rep_abon_dt_tbl(book,code, id_paccnt,summ)
    select book,code, id_paccnt,
    CASE WHEN dt > {$dt_sum} THEN dt ELSE 0 END as debet_sum 
from
(
  select ss.id_paccnt,dt_all,  
  (dt_all - coalesce(p1.pay,0)-  coalesce(bill_corr,0)) as dt , p1.pay, bill_corr, 

   acc.book, acc.code, acc.note,    
  ('0'||substring(acc.code FROM '[0-9]+'))::int as int_code,
  ('0'||substring(acc.book FROM '[0-9]+'))::int as int_book,
  (ab.last_name||' '||coalesce(ab.name,'')||' '||coalesce(ab.patron_name,''))::varchar as abon,
  (adr.street||' '||
        (coalesce('буд.'||(acc.addr).house||'','')||
		coalesce('/'||(acc.addr).slash||' ','')|| 
			coalesce(' корп.'||(acc.addr).korp||'','')||
				coalesce(', кв. '||(acc.addr).flat,'')||
					coalesce('/'||(acc.addr).f_slash,''))::varchar
    )::varchar as addr
   from 
   clm_paccnt_tbl as acc 
   join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class)        
   join clm_abon_tbl as ab on (ab.id = acc.id_abon)         
   join 
   (
    select id_paccnt, sum(b_dtval)-coalesce(sum(b_ktval),0) as dt_all
    from seb_saldo where mmgg = ($p_mmgg::date - '{$month} month'::interval)::date
    and id_pref = 10 
    group by id_paccnt 
    
     union        
    select sb.id_paccnt, sum(sb.value) as dt_all
    from acm_bill_tbl as sb 
    join clm_paccnt_tbl as c on (c.id = sb.id_paccnt)
    where sb.id_pref = 10 
    and sb.mmgg = '2017-09-01'::date and c.id_dep = 260
    and sb.mmgg_bill < ($p_mmgg::date - '{$month} month'::interval)::date
    and idk_doc = 1000   
    and not exists (select id_paccnt from seb_saldo as ss where ss.mmgg = ($p_mmgg::date - '{$month} month'::interval)::date and ss.id_pref = 10  and ss.id_paccnt = sb.id_paccnt  )
    group by sb.id_paccnt 
    
    order by id_paccnt
   ) as ss
   on (ss.id_paccnt = acc.id )
   left join
   (
    select id_paccnt, sum(value) as pay from acm_pay_tbl where 
    ( mmgg <= $p_mmgg::date and 
      mmgg >= ($p_mmgg::date - '{$month} month'::interval)::date )
    and id_pref = 10 
    and idk_doc <> 1000         
    group by id_paccnt order by id_paccnt
   ) as p1 on (p1.id_paccnt = ss.id_paccnt )
   
   left join    	
       (
          select id_paccnt,  -sum(value) as bill_corr 
           from acm_bill_tbl as bb where 
           bb.mmgg >= ($p_mmgg::date - '{$month} month'::interval)  and 
          (bb.mmgg_bill < ($p_mmgg::date - '{$month} month'::interval)  or  ( bb.reg_num <>'' and bb.value <0 and coalesce(bb.demand,0) = 0)) and
           bb.id_pref = 10 and bb.idk_doc=220
           group by id_paccnt 
           having sum(value) <0
           order by id_paccnt
       ) as sum_recalc
       on (sum_recalc.id_paccnt = ss.id_paccnt)       
   where (dt_all - coalesce(p1.pay,0)- coalesce(bill_corr,0) ) > {$dt_sum} 
   and dt_all >0  
) as s 
order by int_book, int_code;";
    
    
    $SQL_base_old = "
    insert into rep_abon_dt_tbl(book,code, id_paccnt,summ)
    select book,code, id_paccnt,
    CASE WHEN dt > {$dt_sum} THEN dt ELSE 0 END as debet_sum 
   
from
(
  select ss.id_paccnt,dt_all,  
  (dt_all - coalesce(p1.pay,0)-  coalesce(bill_corr,0)) as dt , p1.pay, bill_corr, 

   acc.book, acc.code, acc.note,    
  ('0'||substring(acc.code FROM '[0-9]+'))::int as int_code,
  ('0'||substring(acc.book FROM '[0-9]+'))::int as int_book,
  (ab.last_name||' '||coalesce(ab.name,'')||' '||coalesce(ab.patron_name,''))::varchar as abon,
  (adr.street||' '||
        (coalesce('буд.'||(acc.addr).house||'','')||
		coalesce('/'||(acc.addr).slash||' ','')|| 
			coalesce(' корп.'||(acc.addr).korp||'','')||
				coalesce(', кв. '||(acc.addr).flat,'')||
					coalesce('/'||(acc.addr).f_slash,''))::varchar
    )::varchar as addr
   from 
   clm_paccnt_tbl as acc 
   join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class)        
   join clm_abon_tbl as ab on (ab.id = acc.id_abon)         
   join 
   (
     select id_paccnt, sum(value) as dt_all
     from acm_bill_tbl where id_pref = 10 
      and mmgg = ($start_mmgg::date - '1 month'::interval)::date
      and mmgg_bill < ($p_mmgg::date - '{$month} month'::interval)::date
     group by id_paccnt order by id_paccnt
   ) as ss
   on (ss.id_paccnt = acc.id )
   left join
   (
    select id_paccnt, sum(value) as pay from acm_pay_tbl where 

    ( mmgg <= $p_mmgg::date and 
      mmgg >= ($p_mmgg::date - '{$month} month'::interval)::date )

    and id_pref = 10 
    group by id_paccnt order by id_paccnt
   ) as p1 on (p1.id_paccnt = ss.id_paccnt )
   left join    	
       (
          select id_paccnt,  -sum(value) as bill_corr 
           from acm_bill_tbl as bb where 
           bb.mmgg >= ($p_mmgg::date - '{$month} month'::interval)  and 
          (bb.mmgg_bill < ($p_mmgg::date - '{$month} month'::interval)  or  ( bb.reg_num <>'' and bb.value <0 and coalesce(bb.demand,0) = 0)) and
           bb.id_pref = 10 and bb.idk_doc=220
           group by id_paccnt 
           having sum(value) <0
           order by id_paccnt
       ) as sum_recalc
       on (sum_recalc.id_paccnt = ss.id_paccnt)       
   where (dt_all - coalesce(p1.pay,0)-  coalesce(bill_corr,0) ) > {$dt_sum} 
   and dt_all >0  
) as s 
order by int_book, int_code;";

    
  if ($fold ==1)
      $SQL = $SQL_base_old;
  else
      $SQL = $SQL_base_new;

  //echo $SQL;
  
  $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);    
      
}

error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);

session_name("session_kaa");
session_start();

//error_reporting(1);

$Link = get_database_link($_SESSION);
$session_user = $_SESSION['ses_usr_id'];

session_write_close();


$p_id_region = sql_field_val('id_region', 'int');

$Query=" select syi_resid_fun()::int as id_res;";
$result = pg_query($Link,$Query) or die("SQL Error: " .pg_last_error($Link) );
$row = pg_fetch_array($result);
$id_res = $row['id_res'];  

if ($p_id_region!='null')
{
    $SQL = " select coalesce(r.code_res,0) as id_res
            from  cli_region_tbl as r 
            where r.id = $p_id_region ;"; 

    $result = pg_query($Link,$SQL) or die("SQL Error: " .pg_last_error($Link) );      
    $row = pg_fetch_array($result);
    if ($row['id_res']!=0)
    {
       $id_res = $row['id_res'];  
    }       
    
}

$Query=" select p.id, p.represent_name , pp.name as position
 from syi_user as u 
 join prs_persons as p on (p.id = u.id_person)
 left join prs_posts as pp on (pp.id = p.id_post)
 where u.id = $session_user ";
$result = pg_query($Link,$Query) or die("SQL Error: " .pg_last_error($Link) );
$row = pg_fetch_array($result);
$pos_executor  = $row['position'];
$name_executor = $row['represent_name'];

//------------------------------------------------------------
$SQL = "select r.*, address_print_full(r.addr,4) as addr_full,
 b.name as ae_bank_name, 
 boss.represent_name as boss_name,    
 buh.represent_name as buh_name,
 sbut.represent_name as sbutboss_name,    
 
CASE WHEN warn.represent_name ~ '^(.+)\\\\s(.)\\\\.(.)\\\\.$' THEN 
     substr(trim(warn.represent_name),length(warn.represent_name)-3,4)||' '||substr(trim(warn.represent_name),0,length(warn.represent_name)-4) 
     ELSE warn.represent_name END as warningboss_name, 

 av.fulladr as addr_district_name ,
 p.name as warning_name_post,
 p2.name as sbutboss_post
    from syi_resinfo_tbl as r
    left join bank as b on (b.mfo = r.ae_mfo)
    left join prs_persons as boss on (boss.id = r.id_boss)
    left join prs_persons as buh on (buh.id = r.id_buh) 
    
    left join prs_persons as sbut on (sbut.id = r.id_sbutboss)
    left join prs_persons as warn on (warn.id = r.id_warningboss)
    left join prs_posts as p on (p.id = warn.id_post)
    left join prs_posts as p2 on (p2.id = sbut.id_post)
    left join adv_fulladdr_tbl as av on (av.id = r.addr_district)
    where r.id_department = $id_res ;";

$result = pg_query($Link,$SQL);
 if ($result) {
    $row = pg_fetch_array($result);
    
    $res_name =  $row['name'];
    $res_short_name =  $row['short_name'];
    $res_print_name =  $row['print_name'];
    $res_small_name =  $row['small_name']; 
    $res_okpo =  $row['okpo_num'];
    //echo $res_short_name;
    $res_addr =  $row['addr_full'];
    $res_code =  $row['code'];
    //echo $res_code;
    $res_phones =$row['phone_bill']; 
    $boss_name =  $row['boss_name'];
    $buh_name =  $row['buh_name'];
    $sbutboss_name =  $row['sbutboss_name'];
    $sbutboss_pos =  $row['sbutboss_post'];
    $warningboss_name =  $row['warningboss_name'];
    $warningboss_pos  =  $row['warning_name_post'];
    
    $res_warning_phone =  $row['phone_warning'];
    $res_warning_addr =  $row['warning_addr'];
 }

 $SQL = "select fun_mmgg() as mmgg_current ;";
 $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
 $row = pg_fetch_array($result);
 $mmgg_current = "'{$row['mmgg_current']}'";
 
//-------------------------------------------------------------------

if ((isset($_POST['template_name']))&&($_POST['template_name']!='')) 
{
    $template_name = $_POST['template_name'];
}
else
    $template_name = $oper;

//----------------
if (isset($_POST['sum_value'])) { $pp_sum_value = $_POST['sum_value']; }
if (isset($_POST['sum_direction'])) { $pp_sum_direction = $_POST['sum_direction']; }
if (isset($_POST['sector'])) { $pp_sector = $_POST['sector']; }
if (isset($_POST['person'])) { $pp_person = $_POST['person']; }
if (isset($_POST['period_str'])) { $pp_period_str = $_POST['period_str']; }
if (isset($_POST['paccnt_name'])) { $pp_paccnt_name = $_POST['paccnt_name']; }

if (isset($_POST['id_sector'])) { $pp_id_sector = $_POST['id_sector']; }
if (isset($_POST['id_operation'])) { $pp_id_operation = $_POST['id_operation']; }
if (isset($_POST['id_person'])) { $pp_id_person = $_POST['id_person']; }
if (isset($_POST['id_paccnt'])) { $pp_id_paccnt = $_POST['id_paccnt']; }
if (isset($_POST['id_gtar'])) { $pp_id_gtar = $_POST['id_gtar']; }
if (isset($_POST['id_grp_lgt'])) { $pp_id_grp_lgt = $_POST['id_grp_lgt']; }
if (isset($_POST['gtar'])) { $pp_gtar = $_POST['gtar']; }
if (isset($_POST['grp_lgt'])) { $pp_grp_lgt = $_POST['grp_lgt']; }
if (isset($_POST['family_cnt'])) { $pp_family_cnt = $_POST['family_cnt']; }

if (isset($_POST['dt_rep'])) { $pp_dt_rep = $_POST['dt_rep']; }
if (isset($_POST['dt_e'])) { $pp_dt_e = $_POST['dt_e']; }
if (isset($_POST['dt_b'])) { $pp_dt_b = $_POST['dt_b']; }
if (isset($_POST['code'])) { $pp_code = $_POST['code']; }
if (isset($_POST['book'])) { $pp_book = $_POST['book']; }
if (isset($_POST['addr_town_name'])) { $pp_addr_town_name = $_POST['addr_town_name']; }
if (isset($_POST['addr_town'])) { $pp_addr_town = $_POST['addr_town']; }
if (isset($_POST['id_type_meter'])) { $pp_id_type_meter = $_POST['id_type_meter']; }
if (isset($_POST['type_meter'])) { $pp_type_meter = $_POST['type_meter']; }
if (isset($_POST['id_type_meter_array'])) { $pp_id_type_meter_array = $_POST['id_type_meter_array']; }
if (isset($_POST['id_region'])) { $pp_id_region = $_POST['id_region']; }
if (isset($_POST['note'])) { $pp_note = $_POST['note']; }
if (isset($_POST['year_rep'])) { $pp_year_rep = $_POST['year_rep']; }
if (isset($_POST['id_cntrl'])) { $pp_id_cntrl = $_POST['id_cntrl']; }
if (isset($_POST['idk_work'])) { $pp_idk_work = $_POST['idk_work']; }


if (isset($_POST['debet_month'])) { $pp_debet_month = $_POST['debet_month']; }

if (isset($_POST['grid_params'])) { $pp_grid_params = $_POST['grid_params']; }
if (isset($_POST['grid_params2'])) { $pp_grid_params2 = $_POST['grid_params2']; }
else $pp_grid_params2='';
if (isset($_POST['report_caption'])) { $pp_report_caption = $_POST['report_caption']; }


if ((isset($_POST['sum_only']))&&(trim($_POST['sum_only'])==1))
      $sum_only_ch = 'checked';
 else 
      $sum_only_ch ='';
 
if ((isset($_POST['town_detal']))&&(trim($_POST['town_detal'])==1))
      $town_detal_ch = 'checked';
 else 
      $town_detal_ch ='';
 
if ((isset($_POST['show_pager']))&&(trim($_POST['show_pager'])==1))
{
      $show_pager = 1;
      $show_pager_ch= 'checked';
}
 else 
 {
      $show_pager= 0;
      $show_pager_ch= '';      
 }

if ((isset($_POST['oldbill_only']))&&(trim($_POST['oldbill_only'])==1))
{
      $oldbill_only = 1;
      $oldbill_only_ch= 'checked';
}
 else 
 {
      $oldbill_only= 0;
      $oldbill_only_ch= '';      
 }

if ((isset($_POST['old_years']))&&(trim($_POST['old_years'])==1))
{
      $old_years = 1;
      $old_years_ch= 'checked';
}
 else 
 {
      $old_years= 0;
      $old_years_ch= '';      
 }

 if ((isset($_POST['rem_worker']))&&(trim($_POST['rem_worker'])==1))
{
      $rem_worker = 1;
      $rem_worker_ch= 'checked';
}
 else 
 {
      $rem_worker= 0;
      $rem_worker_ch= '';      
 }

 
 //
if (isset($_POST['page_num'])) {
          $page_num = $_POST['page_num'];
}
else
    $page_num=1;

if (isset($_POST['grp_num'])) {
          $grp_num = $_POST['grp_num'];
}
else
    $grp_num =1;


if (isset($_POST['pager_submit'])) {
    
    if ($_POST['pager_submit']== 'GrpDec')
    {
        if ($grp_num>1) $grp_num--;
        $page_num=1;
    }
    
    if ($_POST['pager_submit']== 'GrpInc')
    {
        $grp_num++;
        $page_num=1;
    }

    if ($_POST['pager_submit']== 'PageDec')
    {
        if ($page_num>1) $page_num--;
    }

    if ($_POST['pager_submit']== 'PageInc')
    {
        $page_num++;
    }

    if ($_POST['pager_submit']== 'PageAll')
    {
      $show_pager= 0;
      $show_pager_ch= '';     
      $page_num=1;
      $grp_num=1;
    }
    
    
}
if (isset($_POST['page_size'])) {
          $page_size = $_POST['page_size'];
}
else
    $page_size = 100;

if (isset($_POST['sum_only']))
        $p_sum_only = trim($_POST['sum_only']);
else 
        $p_sum_only =0;

//-------------------------------------------
//$director = 'Чернявський О.О.';
 $director = $sbutboss_name; 
//-------------------------------------------

$header_text = file_get_contents("html_templates/".$template_name."_header.htm");
$footer_text = file_get_contents("html_templates/".$template_name."_footer.htm");
 
start_mpage('Звіт');
if ($to_xls==1)
{
    $css_text0 = file_get_contents("html_templates/reps_common.css");
    $css_text = file_get_contents("html_templates/".$template_name.".css");
    
    print('<style type="text/css"> ');
    echo $css_text0;
    echo $css_text;
    print('</style>');   
}
else
{    
    print('<link rel="stylesheet" type="text/css" href="html_templates/reps_common.css" /> ');
    print('<link rel="stylesheet" type="text/css" href="html_templates/'.$template_name.'.css" /> ');
    print("<link rel=\"shortcut icon\"  href=\"images/logo.ico\" type=\"image/ico\">\n");
    print("<script src=\"js/jquery-1.7.2.min.js\" type=\"text/javascript\"></script> \n");
    print('<script type="text/javascript" src="zvit.js"></script> ');
    

    if ($show_pager==1)
    {
        $pager_text = file_get_contents("html_templates/report_pager.htm");
        eval("\$pager_text = \"$pager_text\";");
        echo $pager_text;
    }
    
}    
print('</head> <body > ');



//---------------------------------------------------------------------------
if ($oper=='rep1')
{
    return;
}
//---------------------------------------------------------------------------
if (($oper=='zvit')||($oper=='zvit_fast')||($oper=='zvit_read'))
    
{  
    $p_mmgg = sql_field_val('dt_b', 'mmgg');

    list($year, $month, $day  ) = split('[/.-]', $p_mmgg);
    $year = trim($year,"'");
    $month_str = ukr_month((int)$month,0);
    $period = "за $month_str $year р.";
    $p_id_paccnt = sql_field_val('id_paccnt', 'int');
    $p_id_town = sql_field_val('addr_town', 'int');
    $p_id_region = sql_field_val('id_region', 'int'); 
    $abon_name = trim($_POST['paccnt_name']);
    
    $params_caption="";
    
    if ((isset($_POST['addr_town_name']))&&($_POST['addr_town_name']!=''))
        $params_caption = trim($_POST['addr_town_name']);
    else 
    {
      if ($p_id_region!='null')
      {
        
        $p_id_town = -$p_id_region;   
        $SQL_H="SELECT name from cli_region_tbl where id = $p_id_region ;";
    
        $result = pg_query($Link, $SQL_H) or die("SQL Error: " . pg_last_error($Link) . $SQL_H);
        $row = pg_fetch_array($result);
        
        $params_caption.= $row['name'];      
        
        if (($id_res==240)&&($p_id_region==1))  $id_res = 242;
        if (($id_res==200)&&($p_id_region==1))  $id_res = 202;
      }
        
    }
    
    
    
    eval("\$header_text = \"$header_text\";");
    echo $header_text;
    
/*
    $SQL = "insert into acm_summ_tbl (id_doc,id_paccnt,id_meter,id_energy,id_zone,id_tarif,id_summtarif,dat_b,dat_e,summ,ident)
            select b.id_doc,b.id_paccnt,0,10,0,min(tv.id_tarif),min(tv.id),bom(b.mmgg),eom(b.mmgg),b.value,-4 from acm_bill_tbl b  left join acm_summ_tbl bs on bs.id_doc=b.id_doc ,
            clm_paccnt_tbl c,aqv_tarif tv
            where b.mmgg=$p_mmgg and bs.id_doc is null and b.id_pref=10
            and c.id=b.id_paccnt and c.id_gtar=tv.id_grptar and b.mmgg>=tv.dt_begin and b.mmgg<tv.dt_end
            group by b.id_doc,b.id_paccnt,bom(b.mmgg),eom(b.mmgg),b.value; ";
 */
    
    
    $p_rebuild = 1;
    if ($oper=='zvit_fast')
    {
        $p_rebuild = 0;
    }
    
    if ($oper!='zvit_read')
    {
        
      $SQL = "insert into acm_summ_tbl (id_doc,id_paccnt,id_meter,id_energy,id_zone,id_tarif,id_summtarif,dat_b,dat_e,summ,demand, ident)
        select b.id_doc,b.id_paccnt,0,10,0,min(tv.id_tarif),min(tv.id),bom(b.mmgg),eom(b.mmgg),
        b.value+coalesce (l.summ_lgt,0),b.demand, -4 
        from acm_bill_tbl b 
        left join acm_summ_tbl bs on bs.id_doc=b.id_doc
        left join (select id_doc,sum(coalesce(summ_lgt,0)) as summ_lgt from acm_lgt_summ_tbl where mmgg=$p_mmgg group by id_doc) l on l.id_doc=b.id_doc,
            clm_paccnt_tbl c,
            aqv_tarif tv join  aqm_tarif_tbl as tt on (tt.id = tv.id_tarif)
            where b.mmgg=$p_mmgg and bs.id_doc is null 
            and b.id_pref=10
            and c.id=b.id_paccnt and c.id_gtar=tv.id_grptar 
            and b.mmgg>=tv.dt_begin 
            and b.mmgg<tv.dt_end
            and (tt.per_min is null or (tt.per_min <= $p_mmgg and tt.per_max >= $p_mmgg ))
            group by b.id_doc,b.id_paccnt,l.summ_lgt, bom(b.mmgg),eom(b.mmgg),b.value;";
      
      

    
      $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    
/*
      $SQL = "update acm_summ_tbl set demand=round(summ/t.value,0) from aqv_tarif t 
         where t.id=acm_summ_tbl.id_summtarif  
         and acm_summ_tbl.ident=-4 and 
         coalesce(acm_summ_tbl.demand,0)=0 and coalesce(acm_summ_tbl.demand,0)<>0;  ";
      $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
*/
      $SQL = "select crt_ttbl();";
      $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
/*
      $SQL = "select clear_empty_recalc($p_mmgg,$session_user);";
      $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
*/      
      
      $SQL = "select rep_zvit_fun($p_mmgg, $p_id_paccnt,$p_id_town, $p_rebuild );";
       $f=fopen('aaa_bill1.sql','w+');
       fputs($f,$SQL);
      $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    }

    $SQL = "select * from rep_zvit_tbl where mmgg =$p_mmgg  and id_dep = $id_res
            and ident !~ 'rsubs_sum' and ident !~ 'rsubs_m'
            order by num; ";

    $f=fopen('aaa_bill.sql','w+');
       fputs($f,$SQL);
/*    
      $SQL = "select z.*, s.seb_code from rep_zvit_tbl as z
         left join seb_zvit_lines_tbl as s on (z.ident = s.ident)
    where mmgg =$p_mmgg  and id_dep = $id_res 
    and z.ident !~ 'rsubs_sum' and z.ident !~ 'rsubs_m'
    order by num; ";
*/
   // throw new Exception(json_encode($SQL));

    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        $i=0;
        while ($row = pg_fetch_array($result)) {

            
            //$r = $baseRow + $i;
            $caption = htmlspecialchars($row['caption']);
            //$caption = strtr($row['caption']," ",'&nbsp;');
            $caption =str_replace(' ','&nbsp;',$caption); 
            
            $class = '';
            if ($row['gr_lvl'] >1 )
                $class = "class='tab_head'";

            if (($row['unit']=='грн.')||($row['unit']=='коп/кВтг')||
                ($row['unit']=='%(шт)')||($row['unit']=='%(грн.)'))
            {
                 $cell_prec = 2;
                 $cell_class = 'c_n';
            }
            else    
            {
                 $cell_prec = 0;
                 $cell_class = 'c_i';
            }
            
            
            $sum_all = number_format_ua ($row['sum_all'],$cell_prec);
            $town_all = number_format_ua ($row['town_all'],$cell_prec);
            
            $town_stove = number_format_ua ($row['town_stove'],$cell_prec);
            $town_heat = number_format_ua ($row['town_heat'],$cell_prec);
            $town_other = number_format_ua ($row['town_other'],$cell_prec);
            
            $village_all = number_format_ua ($row['village_all'],$cell_prec);
            
            $village_stove = number_format_ua ($row['village_stove'],$cell_prec);
            $village_heat = number_format_ua ($row['village_heat'],$cell_prec);
            $village_other = number_format_ua ($row['village_other'],$cell_prec);
            
            if ($row['gr_lvl'] !=0 )
            {    

             if (((($year+0)==2017) && (($month+0) >= 3 ))||(($year+0)>2017))
             {
                $grp_code =  substr($row['ident'],-2);
             }
             else    
             {
                $grp_code =  substr($row['ident'],-1);
             }
             
             if (is_numeric($grp_code))
             {
                if (((($year+0)==2017) && (($month+0) >= 3 ))||(($year+0)>2017))
                {
                    $grp_code =  substr($row['ident'],-2);
                    $row_type =  substr($row['ident'], 0, -2);
                }
                else
                {
                  $row_type =  substr($row['ident'], 0, -1);                    
                }

                //$grp_code = $grp_code+0;
             }
             else
             {
                $row_type =  $row['ident'];
             }                
                
              if ((($row['gr_lvl'] ==1 )&&($to_xls==0))&&
                  (($row_type =='tovar_sum')||($row_type =='bill_sum')||
                  ($row_type =='lgt_sum')||($row_type =='subs_sum')||

                  ($row_type =='tovar_sum_m')||($row_type =='bill_sum_m')||
                  ($row_type =='lgt_sum_m')||($row_type =='subs_m')||

                  ($row_type =='tovar_sum_0m')||($row_type =='bill_sum_0m')||
                  ($row_type =='lgt_sum_0m')||

                  ($row_type =='tovar_sum_1m')||($row_type =='bill_sum_1m')||
                  ($row_type =='lgt_sum_1m')||
                      
                  ($row_type =='tovar_sum_ng')||($row_type =='bill_sum_ng')||
                  ($row_type =='lgt_sum_ng')||($row_type =='subs_sum_ng')||
                  ($row_type =='dop_lgt_sum')   ))
              {    
                
                echo <<<TEXT_BLOC
                <tr $class >
                <td class='c_t'>$caption</td>
                <td class='c_t'>{$row['unit']}</td>
                <td class='{$cell_class}'>{$sum_all}</td>
                <td class='{$cell_class}'>{$town_all}</td>
                <td class='{$cell_class}'><a href='javascript:show_rep_detals("{$row['ident']}",1)' class='zvit_link'>{$town_stove}</a></td>
                <td class='{$cell_class}'><a href='javascript:show_rep_detals("{$row['ident']}",2)' class='zvit_link'>{$town_heat}</a></td>
                <td class='{$cell_class}'><a href='javascript:show_rep_detals("{$row['ident']}",3)' class='zvit_link'>{$town_other}</a></td>
                <td class='{$cell_class}'>{$village_all}</td>
                <td class='{$cell_class}'><a href='javascript:show_rep_detals("{$row['ident']}",4)' class='zvit_link'>{$village_stove}</a></td>
                <td class='{$cell_class}'><a href='javascript:show_rep_detals("{$row['ident']}",5)' class='zvit_link'>{$village_heat}</a></td>
                <td class='{$cell_class}'><a href='javascript:show_rep_detals("{$row['ident']}",6)' class='zvit_link'>{$village_other}</a></td>
                <td class='c_t'>{$row['ident']}</td>
                </tr>  
TEXT_BLOC;
              }
              else
              {
                echo "
                <tr $class >
                <td class='c_t'>$caption</td>
                <td class='c_t'>{$row['unit']}</td>
                <td class='{$cell_class}'>{$sum_all}</td>
                <td class='{$cell_class}'>{$town_all}</td>
                <td class='{$cell_class}'>{$town_stove}</td>
                <td class='{$cell_class}'>{$town_heat}</td>
                <td class='{$cell_class}'>{$town_other}</td>
                <td class='{$cell_class}'>{$village_all}</td>
                <td class='{$cell_class}'>{$village_stove}</td>
                <td class='{$cell_class}'>{$village_heat}</td>
                <td class='{$cell_class}'>{$village_other}</td>
                <td class='c_t'>{$row['ident']}</td>
                </tr>  ";   
              }

              //<td class='c_t'>{$row['seb_code']}</td>
            }
            else
            {
                echo "
                <tr class='tab_head' >
                <td >$caption</td>
                <td >&nbsp;</td>
                <td >&nbsp;</td>
                <td >&nbsp;</td>
                <td >&nbsp;</td>
                <td >&nbsp;</td>
                <td >&nbsp;</td>
                <td >&nbsp;</td>
                <td >&nbsp;</td>
                <td >&nbsp;</td>
                <td >&nbsp;</td>
                <td >&nbsp;</td>
                </tr>  ";        
                
            }
            $i++;
        }
    }

    eval("\$footer_text_eval = \"$footer_text\";");
    echo $footer_text_eval;
    
    //return;
    
}


//-----------------------------------------------------------------------------
if (($oper=='abon_lgt1')||($oper=='abon_lgt_nullnumber')||($oper=='abon_lgt_check_doc') 
        ||($oper=='abon_lgt_check_family') )
{
    $p_mmgg = sql_field_val('dt_b', 'mmgg');
    
    $mmgg1_str = trim($_POST['period_str']);
    
    $p_id_town = sql_field_val('addr_town', 'int');
    $p_id_lgt = sql_field_val('id_grp_lgt', 'int');
    $p_id_tar = sql_field_val('id_gtar', 'int');    

    $p_id_sector = sql_field_val('id_sector', 'int');
    $p_book = sql_field_val('book', 'string');
    $p_id_region = sql_field_val('id_region', 'int');    
    $p_family_cnt = sql_field_val('family_cnt', 'int');    
    
    $where = 'where';
    $params_caption ='';
    $print_flag = 1;
    $print_h_flag = 1;
    
    if (isset($_POST['town_detal']))
        $p_town_detal = trim($_POST['town_detal']);
    else 
        $p_town_detal =0;
    
    if ((isset($_POST['note']))&&($_POST['note']!=''))
    {
        $where.= $_POST['note'];
        $params_caption .= $_POST['note'];
    }
        
    
    if ($p_id_town!='null')
    {
        if ($where!='where') $where.= " and ";
        $where.= " (id_town = $p_id_town or id_parent = $p_id_town ) ";
    }

    if ($p_id_lgt!='null')
    {
        if ($where!='where') $where.= " and ";
        $where.= " id_grp_lgt = $p_id_lgt ";
    }

    if ($p_id_tar!='null')
    {
        if ($where!='where') $where.= " and ";
        $where.= " id_gtar = $p_id_tar ";
    }
    
    if ($oper=='abon_lgt_nullnumber')
    {
        $params_caption.= ' <br/> не вказаний номер посвідчення' ;
        if ($where!='where') $where.= " and ";
        $where.= " (coalesce(n_doc,'')='' and  coalesce(s_doc,'')='') ";
    }

    if ($oper=='abon_lgt_check_doc')
    {
        $params_caption.= ' <br/> прострочені документи' ;
        if ($where!='where') $where.= " and ";
        $where.= " ((dt_doc_end is not null and dt_doc_end<= $p_mmgg::date +'1 month - 1 day'::interval)
        and ( dt_end > dt_doc_end or dt_end is null ))
        
        ";
    }

    if ($p_family_cnt!='null')
    {
        $params_caption.= " <br/> від $p_family_cnt та більше членів родини" ;
        if ($where!='where') $where.= " and ";
        $where.= "  family_cnt >= $p_family_cnt ";
    }
    
    if ($oper=='abon_lgt_check_family')
    {
        $params_caption.= ' <br/> не вказані члени родини' ;
        if ($where!='where') $where.= " and ";
        $where.= " ( family_cnt >2 and not exists (select id from lgm_family_tbl as f where f.id_lgt = id_lgm )) ";
    }
    
    
    if ((isset($_POST['sector']))&&($_POST['sector']!=''))
        $params_caption.= ' Дiльниця: '. trim($_POST['sector']);
    
    if ($p_book!='null')
    {
        if ($where!='where') $where.= " and ";
        $where.= " book = $p_book ";
        $params_caption .= " книга : $p_book ";
    }

    if ($p_id_sector!='null')
    {
        if ($where!='where') $where.= " and ";        
        $where.= " exists (select id from prs_runner_paccnt as rp 
            where rp.id_paccnt = sss.id and rp.id_sector = $p_id_sector ) ";
        
    }
    

    if ($p_id_region!='null')
    {
        if ($where!='where') $where.= " and ";
        $where.= " exists (select rs.id from 
            prs_runner_paccnt as rp 
            join prs_runner_sectors as rs on (rs.id = rp.id_sector)
            where rp.id_paccnt = sss.id and rs.id_region =  $p_id_region ) ";

        $SQL_H="SELECT name from cli_region_tbl where id = $p_id_region ;";
    
        $result = pg_query($Link, $SQL_H) or die("SQL Error: " . pg_last_error($Link) . $SQL_H);
        $row = pg_fetch_array($result);
        
        $params_caption.= $row['name'];        
       
    }    
    if ($p_town_detal==0)
        $order ="int_ident, book, int_code,code";
    else
        $order ="town, int_ident, book, int_code, code";
        
    if ((isset($_POST['gtar']))&&($_POST['gtar']!=''))
        $params_caption .= ' Тарифна група : '. trim($_POST['gtar']);
    
    if ($where=='where') $where= '';
    
   // if ($where == ' WHERE ') $where ='';
     
    $SQL = "CREATE temp TABLE aqm_tarif_tmp
        (
        id integer ,
        id_grptar integer, -- ид группы тарифов
        name character varying(200), -- описание поля
        lim_min integer,
        lim_max integer,
        per_min date, -- период (минимальный)
        per_max date, -- период (максимальный)
        ident character varying(10),
        dt_b date, -- дата (начала) тарифа
        dt_e date, -- дата (завершения) тарифа
        short_name character varying(100),
        value numeric(10,5),
        PRIMARY KEY (id)
        );";
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    
    
    $SQL = "select acm_get_tar_current( $p_mmgg );";
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    
    $SQL = " select * from (
    select c.id, c.code, c.book, lg.id as id_lgm, lg.fio_lgt,lg.family_cnt,lg.s_doc, lg.n_doc,lg.ident_cod_l,
    to_char(lg.dt_reg, 'DD.MM.YYYY') as dt_reg,
     g.name as lgt_name, g.ident,g.alt_code,adr.town, adr.street, address_print(c.addr) as house,adr.id_town,adr.id_parent,
    tt.name as name_tar, n.percent, round(tt.value*100,2) as tar_value, round(tt.value*(100-n.percent),2) as tar_lgt_value,
    bs.demand,bs.demand_lgt,bs.summ_lgt, lg.id_grp_lgt, c.id_gtar,
    regexp_replace(regexp_replace(g.ident, '-.*?$', '') , '[^0-9]', '','g')::int as int_ident,
    regexp_replace(regexp_replace(c.code, '-.*?$', '') , '[^0-9]', '','g')::int as int_code,
    ch.name as calc_name, ls.name as lgt_state,
    lg.dt_doc_end, lg.dt_start, lg.dt_end, 
    to_char(lg.dt_doc, 'DD.MM.YYYY')as dt_doc_txt,
    to_char(lg.dt_doc_end,'DD.MM.YYYY')as dt_doc_end_txt,
    to_char(lg.dt_start, 'DD.MM.YYYY') as dt_start_txt,
    to_char(lg.dt_end, 'DD.MM.YYYY') as dt_end_txt    
    from 
    clm_paccnt_h as c
    join (select id, max(dt_b) as dt_b from clm_paccnt_h  where 
            ((dt_b < ($p_mmgg::date+'1 month'::interval) and dt_e is null)
            or 
            tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
        ) group by id order by id) as c2 
       on (c.id = c2.id and c.dt_b = c2.dt_b)
    join lgm_abon_h as lg on (lg.id_paccnt = c.id) 
    join (select id, max(dt_b) as dt_b from lgm_abon_h  where
            ((dt_b < ($p_mmgg::date+'1 month'::interval) 
             and ( dt_e is null and (dt_end is null or dt_end >= $p_mmgg )  )
             )
            or 
            tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
        )   group by id order by id) as lg2 
    on (lg.id = lg2.id and lg.dt_b = lg2.dt_b)
    join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class)    
    join aqi_grptar_tbl as tar on (tar.id = c.id_gtar)
    join lgi_group_tbl as g on (lg.id_grp_lgt = g.id)
    left join lgi_calc_header_tbl as ch on (g.id_calc = ch.id)
    left join lgi_grp_state_tbl as ls on (ls.id = g.id_state)
    left join lgi_norm_tbl as  n on (n.id_calc = g.id_calc and (n.id_tar_grp = tar.id_lgt_group or n.id_tar_grp is null))
    left join aqm_tarif_tmp as tt on (tt.id_grptar = c.id_gtar and coalesce(tt.lim_min ,0)=0)
    left join 
    (select s.id_paccnt,s.id_grp_lgt, sum(s.demand) as demand, sum(s.demand_lgt) as demand_lgt, sum(s.summ_lgt) as summ_lgt
     from acm_lgt_summ_tbl as s 
     join acm_bill_tbl as b on (b.id_doc = s.id_doc)
     where b.mmgg = $p_mmgg and b.id_pref = 10
     group by s.id_paccnt, s.id_grp_lgt order by s.id_paccnt
    ) as bs
     on (bs.id_paccnt = c.id and bs.id_grp_lgt = lg.id_grp_lgt) 
    where c.archive =0 and (lg.dt_end is null or lg.dt_end >= $p_mmgg::date )
) as sss
    $where
    order by $order ;";
      
   // throw new Exception(json_encode($SQL));
    
    $current_town='';
    $current_lgt='';
    
    if ($p_town_detal == 0)
    {
       if ($p_id_town!='null')
       {
        $current_town =$_POST['addr_town_name'] ;
       }
       else
       {
        $current_town ="-Всі-" ;
       }
       eval("\$header_text_eval = \"$header_text\";");
       echo $header_text_eval;
    }
    
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        //throw new Exception("Try to save file $rows_count ....");    
         
        $baseRow = 8;
        
        $i = $baseRow;
        $cnt_headers = 0;
        $nm=1;

        $hdr_row_start=5;
        $hdr_row_end=7;

        $hdr_col_start=1;
        $hdr_col_end=12;
        
        $sum_lgt_cnt=0;
        $sum_lgt_demall=0;
        $sum_lgt_demlgt=0;
        $sum_lgt_summ=0;

        $sum_town_demall=0;
        $sum_town_demlgt=0;
        $sum_town_summ=0;

        $sum_all_cnt=0;
        $sum_all_demall=0;
        $sum_all_demlgt=0;
        $sum_all_summ=0;
        
        //echo 'Start row inserting';
        //$Sheet->insertNewRowBefore($baseRow,$rows_count);
        $np=0;
        while ($row = pg_fetch_array($result)) {

            
            if (($current_town!=$row['town'])&&($p_town_detal==1))
            {
                if (($cnt_headers == $grp_num-1) || ($show_pager ==0) )
                   $print_h_flag = 1;
                else
                   $print_h_flag = 0;

                if (($cnt_headers == $grp_num) || ($show_pager ==0))
                   $print_h2_flag = 1;
                else
                   $print_h2_flag = 0;
                
                //-------------------------------------
                if ($current_lgt!='')
                {
                    $r = $i++;
                    
                 echo_html( "
                    <tr class='tab_head'>
                     <td colspan='3'>Всього ({$current_lgt}) </td>
                     <td class='c_i'>{$sum_lgt_cnt}</td>
                     <td>&nbsp;</td>
                     <td>&nbsp;</td>
                     <td>&nbsp;</td>
                     <td>&nbsp;</td>
                     <td>&nbsp;</td>
                     <td>&nbsp;</td>
                     <td>&nbsp;</td>
                     
                     <td class='c_i'>{$sum_lgt_demall}</td>
                     <td class='c_i'>{$sum_lgt_demlgt}</td>
                     <td>&nbsp;</td>
                     <td>&nbsp;</td>
                     <td class='c_n'>{$sum_lgt_summ}</td>
                     <td>&nbsp;</td>
                    </tr>  ",$print_flag*$print_h2_flag);        

                    $sum_lgt_cnt=0;
                    $sum_lgt_demall=0;
                    $sum_lgt_demlgt=0;
                    $sum_lgt_summ=0;

                }
                $current_lgt='';
                
                $current_town=$row['town'];
                
                if ($cnt_headers == 0)
                {
                    eval("\$header_text_eval = \"$header_text\";");
                    echo_html($header_text_eval, $print_h_flag);
                }
                else
                {

                    $r = $i++;

                    $nn = $nm-1;
                    echo_html( "
                    <tr class='table_footer'>
                     <td colspan='3'>Всього по нас.пункту </td>
                     <td class='c_n'>{$nn}</td>
                     <td>&nbsp;</td>
                     <td>&nbsp;</td>
                     <td>&nbsp;</td>
                     <td>&nbsp;</td>
                     <td>&nbsp;</td>
                     <td>&nbsp;</td>
                     <td>&nbsp;</td>
                     
                     <td class='c_n'>{$sum_town_demall}</td>
                     <td class='c_n'>{$sum_town_demlgt}</td>
                     <td>&nbsp;</td>
                     <td>&nbsp;</td>
                     <td class='c_n'>{$sum_town_summ}</td>
                     <td>&nbsp;</td>
                    </tr>  ",$print_flag*$print_h2_flag);        
                    
                    //throw new Exception("Try to save file $rows_count ....");    
                    $r = $i++;                    

                    echo $footer_text;    
                    
                    $sum_town_demall=0;
                    $sum_town_demlgt=0;
                    $sum_town_summ=0;
                    $nm=1;   
                                   
                    eval("\$header_text_eval = \"$header_text\";");
                    echo_html($header_text_eval, $print_h_flag);

//                    echo $current_town;
                    $i= $i+4;
                    $r =$i;
                    
                }
                
                $cnt_headers++;
            }
            
            if ($print_h_flag==1) { $np++;  };
            
            if ((($np> ($page_num-1)* $page_size )&& ($np<= $page_num* $page_size )) || ($show_pager ==0))
                $print_flag = 1;
            else
                $print_flag = 0;

            
            if ($current_lgt!=$row['lgt_name'])
            {
                if ($current_lgt!='')
                {
                    $r = $i++;
                    
                 echo_html( "
                    <tr class='tab_head'>
                     <td colspan='3'>Всього ({$current_lgt}) </td>
                     <td class='c_i'>{$sum_lgt_cnt}</td>
                     <td>&nbsp;</td>
                     <td>&nbsp;</td>
                     <td>&nbsp;</td>
                     <td>&nbsp;</td>
                     <td>&nbsp;</td>
                     <td>&nbsp;</td>
                     <td>&nbsp;</td>
                     
                     <td class='c_n'>{$sum_lgt_demall}</td>
                     <td class='c_n'>{$sum_lgt_demlgt}</td>
                     <td>&nbsp;</td>
                     <td>&nbsp;</td>
                     <td class='c_n'>{$sum_lgt_summ}</td>
                     <td>&nbsp;</td>
                    </tr>  ",$print_flag*$print_h_flag);        
         
                    
                    $sum_lgt_cnt=0;
                    $sum_lgt_demall=0;
                    $sum_lgt_demlgt=0;
                    $sum_lgt_summ=0;
                }

                $current_lgt=$row['lgt_name'];
                $current_lgt_ident=$row['ident'];
                $current_lgt_alt=$row['alt_code'];
                
                $current_lgt_calc=$row['calc_name'];
                $current_lgt_state=$row['lgt_state'];
                
                $r = $i++;

                 echo_html( "
                    <tr class='tab_head'>
                     <td colspan='17'>{$current_lgt} &nbsp;({$current_lgt_ident}/{$current_lgt_alt})&nbsp;&nbsp; {$current_lgt_calc} &nbsp;&nbsp;{$current_lgt_state} </td>
                    </tr>  ",$print_flag*$print_h_flag);
                
            }    
            
            $r = $i++;
            
                if ($p_town_detal==1)
                    $addr = $row['street'].' '.$row['house'];
                else
                    $addr = $row['town'].' '.$row['street'].' '.$row['house'];
    
                $book=$row['book'].'/'.$row['code'];
                $doc = $row['s_doc'].' '.$row['n_doc'];
                 echo_html( "
                    <tr class='table_str'>
                     <td class='c_i'>{$nm}</td>                
                     <td>{$addr}</td>
                     <td>{$row['fio_lgt']}</td>            
                     <td>{$row['ident_cod_l']}</td> 
                     <td class='c_t'>{$book}</td>
                     <td class='c_t'>{$doc}</td>
                     
                     <td class='c_t'>{$row['dt_doc_txt']}</td>
                     <td class='c_t'>{$row['dt_doc_end_txt']}</td>
                     <td class='c_t'>{$row['dt_start_txt']}</td>
                     <td class='c_t'>{$row['dt_end_txt']}</td>
                     
                     <td class='c_i'>{$row['family_cnt']}</td>
                     <td class='c_i'>{$row['demand']}</td>
                     <td class='c_i'>{$row['demand_lgt']}</td>
                     <td class='c_n'>{$row['tar_value']}</td>
                     <td class='c_r'>{$row['tar_lgt_value']}</td>
                     <td class='c_n'>{$row['summ_lgt']}</td>
                     <td>{$row['dt_reg']}</td>
                    </tr>  ",$print_flag*$print_h_flag);
            
                     $nm++;

            $sum_lgt_cnt++;
            $sum_lgt_demall+=$row['demand'];
            $sum_lgt_demlgt+=$row['demand_lgt'];
            $sum_lgt_summ+=$row['summ_lgt'];

            $sum_town_demall+=$row['demand'];
            $sum_town_demlgt+=$row['demand_lgt'];
            $sum_town_summ+=$row['summ_lgt'];

            $sum_all_cnt++;
            $sum_all_demall+=$row['demand'];
            $sum_all_demlgt+=$row['demand_lgt'];
            $sum_all_summ+=$row['summ_lgt'];
            
            //$i++;
        }
    }


    if ($current_lgt!='')
    {
      $r = $i++;

      echo_html( "
                    <tr class='tab_head'>
                     <td colspan='3'>Всього ({$current_lgt}) </td>
                     <td class='c_i'>{$sum_lgt_cnt}</td>
                     <td>&nbsp;</td>
                     <td>&nbsp;</td>
                     <td>&nbsp;</td>
                     <td>&nbsp;</td>
                     <td>&nbsp;</td>
                     <td>&nbsp;</td>
                     <td>&nbsp;</td>
                     <td class='c_i'>{$sum_lgt_demall}</td>
                     <td class='c_i'>{$sum_lgt_demlgt}</td>
                     <td>&nbsp;</td>
                     <td>&nbsp;</td>
                     <td class='c_n'>{$sum_lgt_summ}</td>
                     <td>&nbsp;</td>
                    </tr>  ",$print_flag*$print_h_flag);
    
    }
    

    if ($p_town_detal==1)
    {
      $r = $i++;
      $nn = $nm-1;
      echo_html( "
                    <tr class='table_footer'>
                     <td colspan='3'>Всього по нас.пункту</td>
                     <td class='c_n'>{$nn}</td>
                     <td>&nbsp;</td>
                     <td>&nbsp;</td>
                     <td>&nbsp;</td>
                     <td>&nbsp;</td>
                     <td>&nbsp;</td>
                     <td>&nbsp;</td>
                     <td>&nbsp;</td>
                     <td class='c_n'>{$sum_town_demall}</td>
                     <td class='c_n'>{$sum_town_demlgt}</td>
                     <td>&nbsp;</td>
                     <td>&nbsp;</td>
                     <td class='c_n'>{$sum_town_summ}</td>
                     <td>&nbsp;</td>
                    </tr> " ,$print_flag*$print_h_flag);

    }
    $r = $i++;
    

    echo_html( "
                    <tr class='table_footer'>
                     <td colspan='3'>ВСЬОГО</td>
                     <td class='c_i'>{$sum_all_cnt}</td>
                     <td>&nbsp;</td>
                     <td>&nbsp;</td>
                     <td>&nbsp;</td>
                     <td>&nbsp;</td>
                     <td>&nbsp;</td>
                     <td>&nbsp;</td>
                     <td>&nbsp;</td>
                     <td class='c_i'>{$sum_all_demall}</td>
                     <td class='c_i'>{$sum_all_demlgt}</td>
                     <td>&nbsp;</td>
                     <td>&nbsp;</td>
                     <td class='c_n'>{$sum_all_summ}</td>
                     <td>&nbsp;</td>
                    </tr> " ,$print_flag*$print_h_flag);

    echo $footer_text;    
    
}

//-----------------------------------------------------------------------------
if ($oper=='abon_f2')
{
    $p_mmgg = sql_field_val('dt_b', 'mmgg');
    
    $mmgg1_str = trim($_POST['period_str']);
    
    $p_id_town = sql_field_val('addr_town', 'int');
    $p_id_lgt = sql_field_val('id_grp_lgt', 'int');

    $p_id_sector = sql_field_val('id_sector', 'int');
    $p_book = sql_field_val('book', 'string');
    $p_id_region = sql_field_val('id_region', 'int');
    
    
    if (isset($_POST['town_detal']))
        $p_town_detal = trim($_POST['town_detal']);
    else 
        $p_town_detal =0;
    

    $params_caption = '';
    $where = ' ';
    $print_h_flag=0;

    if ($p_id_town!='null')
    {
        $SQL = "delete from adt_addr_town_street_tmp;";
        $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);

        $SQL = "INSERT INTO adt_addr_town_street_tmp(
                    id_town, town, street, id, ident, name, idk_class, indx, is_town)
                select id_town, town, street, id, ident, name, idk_class, indx, is_town    
                from adt_addr_town_street_tbl where ( id_town = $p_id_town or id_parent = $p_id_town )
                    ";
        $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);

        $town_tbl= " adt_addr_town_street_tmp ";
    }
    else
    {
        $town_tbl= " adt_addr_town_street_tbl ";
    }

    if ($p_id_lgt!='null')
    {
        $where.= " and bs.id_grp_lgt = $p_id_lgt ";
    }

    if ((isset($_POST['sector']))&&($_POST['sector']!=''))
        $params_caption.= ' Дiльниця: '. trim($_POST['sector']);

    if ($p_id_sector!='null')
    {
        $where.= "and exists (select id from prs_runner_paccnt as rp 
            where rp.id_paccnt = c.id and rp.id_sector = $p_id_sector ) ";
        
    }
    
    if ($p_id_region!='null')
    {
        
        $where.= "and exists (select rs.id from 
            prs_runner_paccnt as rp 
            join prs_runner_sectors as rs on (rs.id = rp.id_sector)
            where rp.id_paccnt = c.id and rs.id_region =  $p_id_region ) ";

        $SQL_H="SELECT name, code_res  from cli_region_tbl where id = $p_id_region ;";
    
        $result = pg_query($Link, $SQL_H) or die("SQL Error: " . pg_last_error($Link) . $SQL_H);
        $row = pg_fetch_array($result);
        
        $params_caption.= $row['name'];      
        
        if ($row['code_res']==330)
        {
          $res_okpo = '22815333';
        }
       
    }    
    
    if ($p_book!='null')
    {
        $where.= " and book = $p_book ";
        $params_caption .= " книга : $p_book ";
    }
    
    //                   (s.summ_lgt/t.value)*(100/s.percent)
    //if ($where == ' WHERE ') $where ='';
    
    if ($p_town_detal==0)
        $order ="g.ident::int, int_book, int_code, code";
    else
        $order ="g.ident::int, town, int_book,int_code, code";
  
  /*  
    $SQL = "select c.id, c.code, c.book, lg.fio_lgt,lg.family_cnt,lg.s_doc, lg.n_doc,lg.ident_cod_l,
    to_char(lg.dt_reg, 'DD.MM.YYYY') as dt_reg,
     g.name as lgt_name, g.ident,adr.town, adr.street, address_print(c.addr) as house, g.kfk_code, 
     g.ident, n.percent, date_part('year',$p_mmgg::date) as year ,date_part('month',$p_mmgg::date) as month 
    ,cal_daysall_fun($p_mmgg::date,($p_mmgg::date +'1 month -1 days'::interval)::date) as days_all
    ,cal_daysall_fun(
           date_larger($p_mmgg::date, lg.dt_start),
           date_smaller( ($p_mmgg::date +'1 month -1 days'::interval)::date, coalesce(lg.dt_end,($p_mmgg::date +'1 month -1 days'::interval)::date ))
    ) as days_lgt,  bs.*,
    regexp_replace(regexp_replace(c.code, '-.*?$', '') , '[^0-9]', '','g')::int as int_code,
    regexp_replace(regexp_replace(c.book, '-.*?$', '') , '[^0-9]', '','g')::int as int_book
     from 
    clm_paccnt_h as c
    join (select id, max(dt_b) as dt_b from clm_paccnt_h  where 
            ((dt_b < ($p_mmgg::date+'1 month'::interval) and dt_e is null)
            or 
            tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
        ) group by id order by id) as c2 
       on (c.id = c2.id and c.dt_b = c2.dt_b)
    join lgm_abon_h as lg on (lg.id_paccnt = c.id) 
    join (select id, max(dt_b) as dt_b from lgm_abon_h  where
            ((dt_b < ($p_mmgg::date+'1 month'::interval) and dt_e is null)
            or 
            tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
        )   group by id order by id) as lg2 
    on (lg.id = lg2.id and lg.dt_b = lg2.dt_b)
    join aqi_grptar_tbl as tar on (tar.id = c.id_gtar)
    join lgi_group_tbl as g on (lg.id_grp_lgt = g.id)
    join lgi_norm_tbl as  n on (n.id_calc = g.id_calc and (n.id_tar_grp = tar.id_lgt_group or n.id_tar_grp is null))
    left join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class)
    left join 
    ( select id_paccnt, sum(CASE WHEN id_zone = 0 and coalesce(h.ident,'') <> 'light' THEN demand_lgt END ) as demand_lgt0,
                   sum(CASE WHEN id_zone = 0 and coalesce(h.ident,'') <> 'light' THEN summ_lgt END) as summ_lgt0,

		   sum(CASE WHEN id_zone in (6,9) and coalesce(h.ident,'') <> 'light'  THEN demand_lgt END ) as demand_lgt1,
                   sum(CASE WHEN id_zone in (6,9) and coalesce(h.ident,'') <> 'light'  THEN summ_lgt END) as summ_lgt1,

		   sum(CASE WHEN id_zone in (7,10) and coalesce(h.ident,'') <> 'light'  THEN demand_lgt END ) as demand_lgt2,
                   sum(CASE WHEN id_zone in (7,10) and coalesce(h.ident,'') <> 'light'  THEN summ_lgt END) as summ_lgt2,

		   sum(CASE WHEN id_zone = 8  and coalesce(h.ident,'') <> 'light' THEN demand_lgt END ) as demand_lgt3,
                   sum(CASE WHEN id_zone = 8 and coalesce(h.ident,'')<> 'light' THEN summ_lgt END) as summ_lgt3,

		   sum(CASE WHEN coalesce(h.ident,'')= 'light' THEN demand_lgt END ) as demand_lgtl,
                   sum(CASE WHEN coalesce(h.ident,'')= 'light' THEN summ_lgt END) as summ_lgtl

    from acm_lgt_summ_tbl as s 
    join lgi_group_tbl as g on (s.id_grp_lgt = g.id)
    join lgi_calc_header_tbl as h on (h.id = g.id_calc)
    where mmgg = $p_mmgg
     group by id_paccnt order by id_paccnt
      
    ) as bs
    on (bs.id_paccnt = c.id) where c.archive =0 and (lg.dt_end is null or lg.dt_end <= ($p_mmgg::date +'1 month -1 days'::interval)::date)
    $where
    order by $order ;";
*/
$SQL = "select c.id, c.code, c.book, 
coalesce(lg.fio_lgt,lg2.fio_lgt,(ab.last_name||' '||coalesce(ab.name,'')||' '||coalesce(ab.patron_name,'')))::varchar as fio_lgt,
coalesce(lg.ident_cod_l,lg2.ident_cod_l,ab.tax_number) as ident_cod_l,
coalesce(lg.family_cnt,lg2.family_cnt) as family_cnt,
 g.name as lgt_name,   g.ident, g.kfk_code,
 adr.town, adr.street, address_print(c.addr) as house,  
    coalesce(bs.percent,n.percent,0) as percent, 
    date_part('year',bs.mmgg_lgt) as year ,date_part('month',bs.mmgg_lgt) as month ,
    cal_daysall_fun(bs.mmgg_lgt,(bs.mmgg_lgt +'1 month -1 days'::interval)::date) as days_all, bs.days_lgt,
    bs.mmgg_lgt,bs.demand_lgt0,bs.summ_lgt0, bs.demand_lgt1, bs.summ_lgt1, bs.demand_lgt2, bs.summ_lgt2,bs.demand_lgt3, bs.summ_lgt3, bs.demand_lgtl, bs. summ_lgtl,
    bs.demand_lgt1_calc, demand_lgt2_calc, demand_lgt3_calc , 
    regexp_replace(regexp_replace(c.code, '-.*?$', '') , '[^0-9]', '','g')::int as int_code,
    regexp_replace(regexp_replace(c.book, '-.*?$', '') , '[^0-9]', '','g')::int as int_book
     from 
    ( select s.id_paccnt, s.id_grp_lgt, s.percent, coalesce(s.mmgg_corr,b.mmgg_bill) as mmgg_lgt, 
                   sum( demand_lgt ) as demand_lgt_all,
                   sum( summ_lgt ) as summ_lgt_all,
    
                   sum(CASE WHEN coalesce(id_zone,0) = 0 and coalesce(h.ident,'') <> 'light' THEN demand_lgt END )::int as demand_lgt0,
                   sum(CASE WHEN coalesce(id_zone,0) = 0 and coalesce(h.ident,'') <> 'light' THEN summ_lgt END) as summ_lgt0,
    
		   sum(CASE WHEN coalesce(id_zone,0) in (6,9)   THEN demand_lgt END )::int as demand_lgt1,
                   sum(CASE WHEN coalesce(id_zone,0) in (6,9)   THEN summ_lgt END) as summ_lgt1,
    
                   round(sum(CASE WHEN coalesce(id_zone,0) in (6,9)  THEN (s.summ_lgt::numeric/(t.value::numeric*z.koef::numeric))*(100::numeric/percent::numeric) END ),6) as demand_lgt1_calc,

		   sum(CASE WHEN coalesce(id_zone,0) in (7,10)  THEN demand_lgt END )::int as demand_lgt2,
                   sum(CASE WHEN coalesce(id_zone,0) in (7,10)  THEN summ_lgt END) as summ_lgt2,
    
                   round(sum(CASE WHEN coalesce(id_zone,0) in (7,10)  THEN (s.summ_lgt::numeric/(t.value::numeric*z.koef::numeric))*(100::numeric/percent::numeric) END ),6) as demand_lgt2_calc,
    
		   sum(CASE WHEN coalesce(id_zone,0) = 8  THEN demand_lgt END )::int as demand_lgt3,
                   sum(CASE WHEN coalesce(id_zone,0) = 8  THEN summ_lgt END) as summ_lgt3,
    
                   round(sum(CASE WHEN coalesce(id_zone,0) = 8  THEN (s.summ_lgt::numeric/(t.value::numeric*z.koef::numeric))*(100::numeric/percent::numeric) END ),6) as demand_lgt3_calc,
    
		   sum(CASE WHEN (coalesce(h.ident,'')= 'light') and (coalesce(id_zone,0) = 0) THEN demand_lgt END )::int as demand_lgtl,
                   sum(CASE WHEN (coalesce(h.ident,'')= 'light') and (coalesce(id_zone,0) = 0) THEN summ_lgt END) as summ_lgtl,
                   max(dt_fin) - max(dt_beg) +1 as days_lgt
    from acm_lgt_summ_tbl as s 
    join acm_bill_tbl as b on (b.id_doc = s.id_doc)
    join lgi_group_tbl as g on (s.id_grp_lgt = g.id)
    join aqd_tarif_tbl as t on (s.id_summtarif=t.id)
    join eqk_zone_tbl as z on (z.id = s.id_zone)
    left join lgi_calc_header_tbl as h on (h.id = g.id_calc)
    where b.mmgg = $p_mmgg and b.id_pref = 10 and (coalesce(s.demand_lgt,0) <>0 or coalesce(s.demand_add_lgt,0) <>0 ) 
    and g.id_budjet <> 3
     group by s.id_paccnt, s.id_grp_lgt, s.percent, coalesce(s.mmgg_corr,b.mmgg_bill) 
     
   union all 
   
   select lg.id_paccnt, lg.id_grp_lgt, null as percent, coalesce(mmgg_lgt, mmgg) as mmgg_lgt, 
                   lg.demand_val as demand_lgt_all,
                   lg.sum_val as summ_lgt_all,
                   (CASE WHEN coalesce(zz.id_zone,0) = 0 and coalesce(h.ident,'') <> 'light' THEN lg.demand_val END )::int as demand_lgt0,
                   (CASE WHEN coalesce(zz.id_zone,0) = 0 and coalesce(h.ident,'') <> 'light' THEN lg.sum_val END) as summ_lgt0,

		   (CASE WHEN coalesce(zz.id_zone,0) in (6,9) and coalesce(h.ident,'') <> 'light' THEN lg.demand_val END )::int as demand_lgt1,
                   (CASE WHEN coalesce(zz.id_zone,0) in (6,9) and coalesce(h.ident,'') <> 'light' THEN lg.sum_val END) as summ_lgt1, null,

		   (CASE WHEN coalesce(zz.id_zone,0) in (7,10) and coalesce(h.ident,'') <> 'light' THEN lg.demand_val END )::int as demand_lgt2,
                   (CASE WHEN coalesce(zz.id_zone,0) in (7,10) and coalesce(h.ident,'') <> 'light' THEN lg.sum_val END) as summ_lgt2, null,

		   (CASE WHEN coalesce(zz.id_zone,0) = 8 and coalesce(h.ident,'') <> 'light' THEN lg.demand_val END )::int as demand_lgt3,
                   (CASE WHEN coalesce(zz.id_zone,0) = 8 and coalesce(h.ident,'') <> 'light' THEN lg.sum_val END) as summ_lgt3, null,
    
    		   (CASE WHEN coalesce(h.ident,'')= 'light' THEN lg.demand_val END )::int as demand_lgtl,
                   (CASE WHEN coalesce(h.ident,'')= 'light' THEN lg.sum_val END) as summ_lgtl,    
                    null
    from acm_dop_lgt_tbl as lg
    join lgi_group_tbl as g on( g.id = lg.id_grp_lgt)
    left join lgi_calc_header_tbl as h on (h.id = g.id_calc)
    join (select m.id_paccnt, min(id_zone) as id_zone from clm_meter_zone_tbl as mz 
      join clm_meterpoint_tbl as m on (m.id = mz.id_meter) group by m.id_paccnt  ) as zz on (zz.id_paccnt = lg.id_paccnt)
    
    where mmgg = $p_mmgg  and g.id_budjet <> 3 
    -- order by s.id_paccnt
    ) as bs
    join 
    clm_paccnt_h as c on (c.id = bs.id_paccnt)
    join (select id, max(dt_b) as dt_b from clm_paccnt_h  where 
            ((dt_b < ($p_mmgg::date+'1 month'::interval) and dt_e is null)
            or 
            tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
        ) group by id order by id) as c2 
       on (c.id = c2.id and c.dt_b = c2.dt_b)
   join $town_tbl as adr on (adr.id = (c.addr).id_class)
   join lgi_group_tbl as g on (bs.id_grp_lgt = g.id)
   join aqi_grptar_tbl as tar on (tar.id = c.id_gtar)
   join lgi_norm_tbl as n on (n.id_calc = g.id_calc and (n.id_tar_grp = tar.id_lgt_group or n.id_tar_grp is null))
   join clm_abon_h as ab on (ab.id = c.id_abon) 
   join (select id, max(dt_b) as dt_b from clm_abon_h  where 
         ((dt_b < ($p_mmgg::date+'1 month'::interval) and dt_e is null)
         or 
         tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
      )
   group by id order by id) as a2 on (ab.id = a2.id and ab.dt_b = a2.dt_b)

   left join 
    ( select lg.id_paccnt, lg.id_grp_lgt, lg.id_calc , lg.fio_lgt, lg.ident_cod_l, lg.family_cnt
     from lgm_abon_h as lg  
      join
      (select lg.id_paccnt, lg.id_grp_lgt, max(lg.id_key) as id_key 
       from lgm_abon_h as lg 
       join (select id, id_grp_lgt, max(dt_b) as dt_b 
            from lgm_abon_h 
            where dt_b < ($p_mmgg::date+'1 month'::interval)
--    
--            where
--            ((dt_b < ($p_mmgg::date+'1 month'::interval)
--            and ( dt_e is null and (dt_end is null or dt_end >= $p_mmgg )  )
--            )
--            or 
--            tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
--    
--        )
        group by id, id_grp_lgt order by id, id_grp_lgt ) as lg2 
        on (lg.id = lg2.id and lg.id_grp_lgt = lg2.id_grp_lgt and  lg.dt_b = lg2.dt_b)
      group by lg.id_paccnt, lg.id_grp_lgt
      ) as gg
      on(lg.id_key = gg.id_key)
    ) as lg on (lg.id_paccnt = bs.id_paccnt and lg.id_grp_lgt = bs.id_grp_lgt)

   left join 
    ( select lg.id_paccnt, lg.fio_lgt, lg.ident_cod_l, lg.family_cnt
     from lgm_abon_h as lg  join
      (select lg.id_paccnt, max(lg.id_key) as id_key 
       from lgm_abon_h as lg 
        join (select id, max(dt_b) as dt_b from lgm_abon_h  where
            ((dt_b < ($p_mmgg::date+'1 month'::interval) 
             and ( dt_e is null and (dt_end is null or dt_end >= $p_mmgg )  )
            )
            or 
            tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
        )   group by id order by id) as lg2 
        on (lg.id = lg2.id and lg.dt_b = lg2.dt_b)
      group by lg.id_paccnt
      ) as gg
      on(lg.id_key = gg.id_key)
    ) as lg2 on (lg2.id_paccnt = bs.id_paccnt)
    where 
    -- c.archive =0 and 
    ( coalesce( bs.demand_lgt_all,0) <>0 or coalesce(bs.summ_lgt_all,0) <>0 )
    $where
    order by $order ;";
    
   // throw new Exception(json_encode($SQL));

    
    $current_town='';
    $current_lgt='';
    
    $kfk_array = array();
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        //throw new Exception("Try to save file $rows_count ....");    
        
        $baseRow = 8;
        
        $i = $baseRow;
        $cnt_headers = 0;
        $nm=1;
        $nm_split=1;

        $hdr_row_start=5;
        $hdr_row_end=7;

        $hdr_col_start=1;
        $hdr_col_end=12;
        
        $sum_lgt_cnt=0;
        $sum_lgt_cntfam=0;
        
        $sum_lgt_summ0=0;
        $sum_lgt_summ1=0;
        $sum_lgt_summ2=0;
        $sum_lgt_summ3=0;
        $sum_lgt_summ_l=0;

        $sum_lgt_dem0=0;
        $sum_lgt_dem1=0;
        $sum_lgt_dem2=0;
        $sum_lgt_dem3=0;
        $sum_lgt_dem_l=0;
        
        $sum_town_cnt=0;
        $sum_town_cntfam=0;
        $sum_town_summ0=0;
        $sum_town_summ1=0;
        $sum_town_summ2=0;
        $sum_town_summ3=0;
        $sum_town_summ_l=0;

        $sum_all_cnt=0;
        $sum_all_cntfam=0;
        $sum_all_summ0=0;
        $sum_all_summ1=0;
        $sum_all_summ2=0;
        $sum_all_summ3=0;
        $sum_all_summ_l=0;

        $sum_all_dem0=0;
        $sum_all_dem1=0;
        $sum_all_dem2=0;
        $sum_all_dem3=0;
        $sum_all_dem_l=0;
        
        //echo 'Start row inserting';
        //$Sheet->insertNewRowBefore($baseRow,$rows_count);
        $np=0;
        $print_flag = 1;
        while ($row = pg_fetch_array($result)) {

            //if ($current_town!=$row['town'])
            if ($current_lgt!=$row['lgt_name'])
            {

                if (($cnt_headers == $grp_num-1) || ($show_pager ==0) )
                   $print_h_flag = 1;
                else
                   $print_h_flag = 0;

                if (($cnt_headers == $grp_num) || ($show_pager ==0))
                   $print_h2_flag = 1;
                else
                   $print_h2_flag = 0;

                //-------------------------------------
                if (($current_town!='')&&($p_town_detal==1))
                {

                 $sum_all = number_format_ua ($sum_town_summ0+$sum_town_summ1+$sum_town_summ2+$sum_town_summ3+$sum_town_summ_l,2);
                    
                 $sum_town_summ0 = number_format_ua($sum_town_summ0,2);
                 $sum_town_summ1 = number_format_ua($sum_town_summ1,2);
                 $sum_town_summ2 = number_format_ua($sum_town_summ2,2);
                 $sum_town_summ3 = number_format_ua($sum_town_summ3,2);
                 $sum_town_summ_l= number_format_ua($sum_town_summ_l,2);
                 
                 echo echo_html("
                    <tr class='tab_head'>
                     <td colspan='2'>Всього по нас.пункту -</td>
                     <td class='c_i'>{$sum_town_cnt}</td>
                     <td>&nbsp;</td>
                     <td class='c_i'>{$sum_town_cntfam}</td>
                     <td>&nbsp;</td>
                     <td>&nbsp;</td>                     
                     <td>&nbsp;</td>                     
                     <td>&nbsp;</td>                     
                     <td>&nbsp;</td>                                          
                     <td class='c_n'>{$sum_town_summ0}</td>
                     <td class='c_n'>{$sum_town_summ1}</td>
                     <td class='c_n'>{$sum_town_summ2}</td>                     
                     <td class='c_n'>{$sum_town_summ3}</td>                     
                     <td class='c_n'>{$sum_town_summ_l}</td>                                          
                     <td class='c_n'>{$sum_all}</td>
                    </tr>  ",$print_h2_flag*$print_flag);        

                    $sum_town_cnt=0;
                    $sum_town_cntfam=0;
                    $sum_town_summ0=0;
                    $sum_town_summ1=0;
                    $sum_town_summ2=0;
                    $sum_town_summ3=0;
                    $sum_town_summ_l=0;
                }
                $current_town='';
                //-------------------------------------

                if ($cnt_headers == 0)
                {
                    $current_lgt=$row['lgt_name'];
                    eval("\$header_text_eval = \"$header_text\";");
                    
                    if ($page_num == 1)
                       echo_html($header_text_eval, $print_h_flag);
                    else        
                       echo_html("<table class ='lgt_list_table' width='100%' cellspacing='0' cellpadding='0'>", $print_h_flag);
                    
                   // echo "<tbody id = 'tbody$cnt_headers'>";
                }
                else
                {

                    $sum_all =  number_format_ua ($sum_lgt_summ0+$sum_lgt_summ1+$sum_lgt_summ2+$sum_lgt_summ3+$sum_lgt_summ_l,2);
                    $dem_all =  $sum_lgt_dem0+$sum_lgt_dem1+$sum_lgt_dem2+$sum_lgt_dem3+$sum_lgt_dem_l;

                    $sum_lgt_summ0 = number_format_ua($sum_lgt_summ0,2);
                    $sum_lgt_summ1 = number_format_ua($sum_lgt_summ1,2);
                    $sum_lgt_summ2 = number_format_ua($sum_lgt_summ2,2);
                    $sum_lgt_summ3 = number_format_ua($sum_lgt_summ3,2);
                    $sum_lgt_summ_l= number_format_ua($sum_lgt_summ_l,2);
                    
                    
                    echo_html("
                    <tr class='tab_head'>
                     <td rowspan='2' colspan='2'>Всього ({$current_lgt}) -</td>
                     <td rowspan='2' class='c_i'>{$sum_lgt_cnt}</td>
                     <td rowspan='2'>&nbsp;</td>
                     <td rowspan='2' class='c_i'>{$sum_lgt_cntfam}</td>
                     <td rowspan='2'>&nbsp;</td>
                     <td rowspan='2'>&nbsp;</td>                     
                     <td rowspan='2'>&nbsp;</td>                     
                     <td rowspan='2'>&nbsp;</td>                     
                     <td rowspan='2'>&nbsp;</td>                                          
                     <td class='c_n'>{$sum_lgt_dem0}</td>
                     <td class='c_n'>{$sum_lgt_dem1}</td>
                     <td class='c_n'>{$sum_lgt_dem2}</td>                     
                     <td class='c_n'>{$sum_lgt_dem3}</td>                     
                     <td class='c_n'>{$sum_lgt_dem_l}</td>                                          
                     <td class='c_n'>{$dem_all}</td>
                    </tr>  
                    <tr class='tab_head'>
                     <td class='c_n'>{$sum_lgt_summ0}</td>
                     <td class='c_n'>{$sum_lgt_summ1}</td>
                     <td class='c_n'>{$sum_lgt_summ2}</td>                     
                     <td class='c_n'>{$sum_lgt_summ3}</td>                     
                     <td class='c_n'>{$sum_lgt_summ_l}</td>                                          
                     <td class='c_n'>{$sum_all}</td>
                    </tr> </table> ",$print_h2_flag*$print_flag);        
                     
                     
                    if($res_code!='330') 
                    {
                      echo_html("<br style='page-break-after: always' /> ",$print_h2_flag*$print_flag);        
                    }

                    $sum_lgt_cnt=0;
                    $sum_lgt_cntfam=0;
                    $sum_lgt_summ0=0;
                    $sum_lgt_summ1=0;
                    $sum_lgt_summ2=0;
                    $sum_lgt_summ3=0;
                    $sum_lgt_summ_l=0;
                    
                    $sum_lgt_dem0=0;
                    $sum_lgt_dem1=0;
                    $sum_lgt_dem2=0;
                    $sum_lgt_dem3=0;
                    $sum_lgt_dem_l=0;
                    
                    $nm=1;   
                    
                    $nm_split=1;
                    
                    $current_lgt=$row['lgt_name'];
                    eval("\$header_text_eval = \"$header_text\";");
                    //echo_html($header_text_eval,$print_h_flag*$print_flag);
                    
                    if ($page_num == 1)
                       echo_html($header_text_eval, $print_h_flag);
                    else        
                       echo_html("<table class ='lgt_list_table' width='100%' cellspacing='0' cellpadding='0'>", $print_h_flag);
                    
                    //echo "<tbody id = 'tbody$cnt_headers' style='display:none;'>";

                }
                
                $cnt_headers++;
            }
            
            if ($print_h_flag==1) { $np++;  };
            
            if ((($np> ($page_num-1)* $page_size )&& ($np<= $page_num* $page_size )) || ($show_pager ==0))
                $print_flag = 1;
            else
                $print_flag = 0;
            
            
            if (($current_town!=$row['town'])&&($p_town_detal==1))
            {
                if ($current_town!='')
                {
                    
                 $sum_all =  number_format_ua ($sum_town_summ0+$sum_town_summ1+$sum_town_summ2+$sum_town_summ3+$sum_town_summ_l,2);
                 $sum_town_summ0 = number_format_ua($sum_town_summ0,2);   
                 $sum_town_summ1 = number_format_ua($sum_town_summ1,2);
                 $sum_town_summ2 = number_format_ua($sum_town_summ2,2);
                 $sum_town_summ3 = number_format_ua($sum_town_summ3,2);
                 $sum_town_summ_l= number_format_ua($sum_town_summ_l,2);
                 
                 echo_html("
                    <tr class='tab_head'>
                     <td colspan='2'>Всього по нас.пункту -</td>
                     <td class='c_i'>{$sum_town_cnt}</td> 
                     <td>&nbsp;</td>
                     <td class='c_i'>{$sum_town_cntfam}</td>
                     <td>&nbsp;</td>
                     <td>&nbsp;</td>                     
                     <td>&nbsp;</td>                     
                     <td>&nbsp;</td>                     
                     <td>&nbsp;</td>                                          
                     <td class='c_n'>{$sum_town_summ0}</td>
                     <td class='c_n'>{$sum_town_summ1}</td>
                     <td class='c_n'>{$sum_town_summ2}</td>                     
                     <td class='c_n'>{$sum_town_summ3}</td>                     
                     <td class='c_n'>{$sum_town_summ_l}</td>                                          
                     <td class='c_n'>{$sum_all}</td>
                    </tr>  ",$print_h_flag*$print_flag);        

                    $sum_town_cnt=0;
                    $sum_town_cntfam=0;
                    $sum_town_summ0=0;
                    $sum_town_summ1=0;
                    $sum_town_summ2=0;
                    $sum_town_summ3=0;
                    $sum_town_summ_l=0;

                }

                $current_town=$row['town'];

                 echo_html("
                    <tr class='tab_head'>
                     <td colspan='16'>{$current_town}</td>
                    </tr>  ",$print_h_flag*$print_flag);        
                
            }    

            
            $r = $i++;
            
            if ($p_town_detal==1)
                $addr = $row['street'].' '.$row['house'];
            else
                if($res_code=='310')
                    $addr =$row['street'].' '.$row['house'];
                else
                    $addr = $row['town'].' '.$row['street'].' '.$row['house'];
                
                
                $book=$row['book'].'/'.$row['code'];
                $days = $row['days_lgt'];
                $days_all = $row['days_all'];
                if (($days ==$days_all)||($days =='null')) $days='';
                
                $sum_all =  number_format_ua ($row['summ_lgt0']+$row['summ_lgt1']+$row['summ_lgt2']+$row['summ_lgt3']+$row['summ_lgtl'],2);
                $dem_all =  $row['demand_lgt0']+$row['demand_lgt1']+$row['demand_lgt2']+$row['demand_lgt3']+$row['demand_lgtl'];
                
                if ($p_sum_only!=1)
                {
                    
                    echo_html("
                    <tr>
                     <td rowspan='2'>{$nm}</td>
                     <td rowspan='2'>{$row['fio_lgt']}</td>
                     <td rowspan='2'>{$addr}</td>
                     <td rowspan='2' class='c_t'>{$row['ident_cod_l']}</td>
                     <td rowspan='2' class='c_i'>{$row['family_cnt']}</td>
                     <td class='c_r'>{$row['ident']}</td>
                     <td rowspan='2' class='c_t'>{$book}</td>                     
                     <td rowspan='2' class='c_i'>{$row['year']}</td>                     
                     <td rowspan='2' class='c_i'>{$row['month']}</td>                     
                     <td rowspan='2' class='c_i'>$days</td>                                          
                     <td class='c_i'>{$row['demand_lgt0']}</td>
                     <td class='c_i'>{$row['demand_lgt1']}</td>
                     <td class='c_i'>{$row['demand_lgt2']}</td>                     
                     <td class='c_i'>{$row['demand_lgt3']}</td>                     
                     <td class='c_i'>{$row['demand_lgtl']}</td>                                          
                     <td class='c_i'>&nbsp;</td>
                    </tr>  
                    <tr >
                     <td class='c_n'>{$row['percent']}</td>
                     <td class='c_n'>{$row['summ_lgt0']}</td>
                     <td class='c_n'>{$row['summ_lgt1']}</td>
                     <td class='c_n'>{$row['summ_lgt2']}</td>                     
                     <td class='c_n'>{$row['summ_lgt3']}</td>                     
                     <td class='c_n'>{$row['summ_lgtl']}</td>                                          
                     <td class='c_n'>{$sum_all}</td>
                    </tr>  ",$print_h_flag*$print_flag);    
                     
                     //<td class='c_i'>{$dem_all}</td>
                     
                     //<td class='c_n'>{$row['summ_lgt1']}</td>
                     //<td class='c_n'>{$row['summ_lgt2']}</td>                     
                     //<td class='c_n'>{$row['summ_lgt3']}</td>                     
                     
                     //<td class='c_n'>{$row['demand_lgt1_calc']}</td>
                     //<td class='c_n'>{$row['demand_lgt2_calc']}</td>                     
                     //<td class='c_n'>{$row['demand_lgt3_calc']}</td>                     
                     
                    if ($print_h_flag*$print_flag >0 )$nm_split++;                     
                     
                     if (($nm_split==1000)&&($to_xls==1)) 
                     {
                         $nm_split=1;
                          echo_html('</table> <br/>',$print_h_flag*$print_flag);
                          echo_html("<table class ='lgt_list_table' width='100%' cellspacing='0' cellpadding='0'>", $print_h_flag*$print_flag);
                         
                     }
                }     
           
                $kfk_code = $row['kfk_code'];
                
                if (array_key_exists($kfk_code,$kfk_array))
                {
                    $kfk_array[$kfk_code]["sum"]+=$row['summ_lgt0']+$row['summ_lgt1']+$row['summ_lgt2']+$row['summ_lgt3']+$row['summ_lgtl'];
                    $kfk_array[$kfk_code]["dem"]+=$dem_all;
                }
                else
                {
                    $kfk_array[$kfk_code]= array(
                          "dem" => 0,
                          "sum" => 0);
                }
                    
                
            $nm++;
            
            $sum_lgt_cnt ++;
            $sum_lgt_cntfam+= $row['family_cnt'];

            $sum_lgt_summ0+= $row['summ_lgt0'];
            $sum_lgt_summ1+= $row['summ_lgt1'];
            $sum_lgt_summ2+= $row['summ_lgt2'];
            $sum_lgt_summ3+= $row['summ_lgt3'];
            $sum_lgt_summ_l+= $row['summ_lgtl'];

            $sum_lgt_dem0+= $row['demand_lgt0'];
            $sum_lgt_dem1+= $row['demand_lgt1'];
            $sum_lgt_dem2+= $row['demand_lgt2'];
            $sum_lgt_dem3+= $row['demand_lgt3'];
            $sum_lgt_dem_l+= $row['demand_lgtl'];

            $sum_town_cnt++;
            $sum_town_cntfam+= $row['family_cnt'];
            $sum_town_summ0+= $row['summ_lgt0'];
            $sum_town_summ1+= $row['summ_lgt1'];
            $sum_town_summ2+= $row['summ_lgt2'];
            $sum_town_summ3+= $row['summ_lgt3'];
            $sum_town_summ_l+= $row['summ_lgtl'];

            $sum_all_cnt++ ;
            $sum_all_cntfam+= $row['family_cnt'];
            $sum_all_summ0+= $row['summ_lgt0'];
            $sum_all_summ1+= $row['summ_lgt1'];
            $sum_all_summ2+= $row['summ_lgt2'];
            $sum_all_summ3+= $row['summ_lgt3'];
            $sum_all_summ_l+= $row['summ_lgtl'];

            $sum_all_dem0+= $row['demand_lgt0'];
            $sum_all_dem1+= $row['demand_lgt1'];
            $sum_all_dem2+= $row['demand_lgt2'];
            $sum_all_dem3+= $row['demand_lgt3'];
            $sum_all_dem_l+= $row['demand_lgtl'];

        }
    }


    if ($current_town!='')
    {
      $sum_all =  number_format_ua ($sum_town_summ0+$sum_town_summ1+$sum_town_summ2+$sum_town_summ3+$sum_town_summ_l,2);
      $sum_town_summ0 = number_format_ua($sum_town_summ0,2);                 
      $sum_town_summ1 = number_format_ua($sum_town_summ1,2);
      $sum_town_summ2 = number_format_ua($sum_town_summ2,2);
      $sum_town_summ3 = number_format_ua($sum_town_summ3,2);
      $sum_town_summ_l= number_format_ua($sum_town_summ_l,2);
      
      echo_html("
                    <tr class='tab_head'>
                     <td colspan='2'>Всього по нас.пункту -</td>
                     <td class='c_i'>{$sum_town_cnt}</td>
                     <td>&nbsp;</td>
                     <td class='c_i'>{$sum_town_cntfam}</td>
                     <td>&nbsp;</td>
                     <td>&nbsp;</td>                     
                     <td>&nbsp;</td>                     
                     <td>&nbsp;</td>                     
                     <td>&nbsp;</td>                                          
                     <td class='c_n'>{$sum_town_summ0}</td>
                     <td class='c_n'>{$sum_town_summ1}</td>
                     <td class='c_n'>{$sum_town_summ2}</td>                     
                     <td class='c_n'>{$sum_town_summ3}</td>                     
                     <td class='c_n'>{$sum_town_summ_l}</td>                                          
                     <td class='c_n'>{$sum_all}</td>
                    </tr>  ",$print_h_flag*$print_flag);        

    }
 
   $sum_all =  number_format_ua ($sum_lgt_summ0+$sum_lgt_summ1+$sum_lgt_summ2+$sum_lgt_summ3+$sum_lgt_summ_l,2);
   $dem_all =  $sum_lgt_dem0+$sum_lgt_dem1+$sum_lgt_dem2+$sum_lgt_dem3+$sum_lgt_dem_l;
   
   $sum_lgt_summ0 = number_format_ua($sum_lgt_summ0,2);
   $sum_lgt_summ1 = number_format_ua($sum_lgt_summ1,2);
   $sum_lgt_summ2 = number_format_ua($sum_lgt_summ2,2);
   $sum_lgt_summ3 = number_format_ua($sum_lgt_summ3,2);
   $sum_lgt_summ_l= number_format_ua($sum_lgt_summ_l,2);
   
   
   echo_html("
                    <tr class='tab_head'>
                     <td rowspan='2' colspan='2'>Всього ({$current_lgt}) -</td>
                     <td rowspan='2' class='c_i'>{$sum_lgt_cnt}</td>
                     <td rowspan='2'>&nbsp;</td>
                     <td rowspan='2' class='c_i'>{$sum_lgt_cntfam}</td>
                     <td rowspan='2'>&nbsp;</td>
                     <td rowspan='2'>&nbsp;</td>                     
                     <td rowspan='2'>&nbsp;</td>                     
                     <td rowspan='2'>&nbsp;</td>                     
                     <td rowspan='2'>&nbsp;</td>                                          
                     <td class='c_i'>{$sum_lgt_dem0}</td>
                     <td class='c_i'>{$sum_lgt_dem1}</td>
                     <td class='c_i'>{$sum_lgt_dem2}</td>                     
                     <td class='c_i'>{$sum_lgt_dem3}</td>                     
                     <td class='c_i'>{$sum_lgt_dem_l}</td>                                          
                     <td class='c_i'>{$dem_all}</td>
                    </tr>  
                    <tr class='tab_head'>
                     <td class='c_n'>{$sum_lgt_summ0}</td>
                     <td class='c_n'>{$sum_lgt_summ1}</td>
                     <td class='c_n'>{$sum_lgt_summ2}</td>                     
                     <td class='c_n'>{$sum_lgt_summ3}</td>                     
                     <td class='c_n'>{$sum_lgt_summ_l}</td>                                          
                     <td class='c_n'>{$sum_all}</td>
                    </tr>  ",$print_h_flag*$print_flag);        
    

   $sum_all =  number_format_ua ($sum_all_summ0+$sum_all_summ1+$sum_all_summ2+$sum_all_summ3+$sum_all_summ_l,2);
   $dem_all =  $sum_all_dem0+$sum_all_dem1+$sum_all_dem2+$sum_all_dem3+$sum_all_dem_l;
   
   $sum_all_summ0 = number_format_ua($sum_all_summ0,2);
   $sum_all_summ1 = number_format_ua($sum_all_summ1,2);
   $sum_all_summ2 = number_format_ua($sum_all_summ2,2);
   $sum_all_summ3 = number_format_ua($sum_all_summ3,2);
   $sum_all_summ_l= number_format_ua($sum_all_summ_l,2);
   
   
   echo_html("
                    <tr class='tab_head'>
                     <td rowspan='2' colspan='2'>ВСЬОГО </td>
                     <td rowspan='2' class='c_i'>{$sum_all_cnt}</td>
                     <td rowspan='2'>&nbsp;</td>
                     <td rowspan='2' class='c_i'>{$sum_all_cntfam}</td>
                     <td rowspan='2'>&nbsp;</td>
                     <td rowspan='2'>&nbsp;</td>                     
                     <td rowspan='2'>&nbsp;</td>                     
                     <td rowspan='2'>&nbsp;</td>                     
                     <td rowspan='2'>&nbsp;</td>                                          
                     <td class='c_n'>{$sum_all_dem0}</td>
                     <td class='c_n'>{$sum_all_dem1}</td>
                     <td class='c_n'>{$sum_all_dem2}</td>                     
                     <td class='c_n'>{$sum_all_dem3}</td>
                     <td class='c_n'>{$sum_all_dem_l}</td>
                     <td class='c_n'>{$dem_all}</td>
                    </tr>  
                    <tr class='tab_head'>
                     <td class='c_n'>{$sum_all_summ0}</td>
                     <td class='c_n'>{$sum_all_summ1}</td>
                     <td class='c_n'>{$sum_all_summ2}</td>
                     <td class='c_n'>{$sum_all_summ3}</td>
                     <td class='c_n'>{$sum_all_summ_l}</td> 
                     <td class='c_n'>{$sum_all}</td>
                    </tr>  ",$print_h_flag*$print_flag);

    echo_html('</table> <br/>',$print_h_flag);
    $kfk_text = "";
    
                 /*
    $kfk_text = " в тому числі <br/> 
        <table class ='kfk_table' cellspacing='0' cellpadding='0'>";
    
    ksort($kfk_array);
    foreach ($kfk_array as $key => $value)
    {
        if ($value['dem']!=0)
        {
              $summ = number_format_ua($value['sum'],2);
              $kfk_text .= "<tr>
                       <td class='c_t'> КФК {$key}</td>        
                       <td class='c_n'> {$value['dem']} кВтг</td>        
                       <td class='c_n'> {$summ} грн.</td>        
                     </tr>  ";
        }
    };
    
    
    $kfk_text.='</table> <br/>';
    */
    
    eval("\$footer_text_eval = \"$footer_text\";");
    echo_html($footer_text_eval,$print_h_flag*$print_flag);
    
}
//-----------------------------------------------
if ($oper=='zon3')
{
    $p_mmgg = sql_field_val('dt_b', 'mmgg');
    $mmgg1_str = trim($_POST['period_str']);
    $p_id_region = sql_field_val('id_region', 'int');
    
    list($year, $month, $day  ) = split('[/.-]', $p_mmgg);
    $year = trim($year,"'");
    
    $SQL = "select * from eqk_zone_tbl;";
    
    $where='';
    $params_caption ='';
    

    $result = pg_query($Link,$SQL);

    if ($result) {
     
        while ($row = pg_fetch_array($result)) {
            
            if ($row['id']==6)
                $k31=round($row['koef'],2);
            if ($row['id']==7)
                $k32=round($row['koef'],2);
            if ($row['id']==8)
                $k33=round($row['koef'],2);
            
            if ($row['id']==9)
                $k21=round($row['koef'],2);
            if ($row['id']==10)
                $k22=round($row['koef'],2);
        } 
    }    
    
    
    if ($p_id_region!='null')
    {
        
        $where.= "and exists (select rs.id from 
            prs_runner_paccnt as rp 
            join prs_runner_sectors as rs on (rs.id = rp.id_sector)
            where rp.id_paccnt = b.id_paccnt and rs.id_region =  $p_id_region ) ";

        $SQL_H="SELECT name from cli_region_tbl where id = $p_id_region ;";
    
        $result = pg_query($Link, $SQL_H) or die("SQL Error: " . pg_last_error($Link) . $SQL_H);
        $row = pg_fetch_array($result);
        
        $params_caption.= $row['name'];               
    }    
    
    eval("\$header_text = \"$header_text\";");
    
    echo $header_text;
    
    if (((($year+0)==2017) && (($month+0) >= 3 ))||(($year+0)>2017))    
    {

    $SQL = " select ssss.*, tt.name 
    from
    (
    select ident,
    sum(demand1) as demand1, sum(demand2) as demand2, sum(demand3) as demand3, 
    sum(summ1) as summ1,sum(summ2) as summ2,sum(summ3) as summ3, max(tar_val) as tar_val
    from
    (
    select CASE WHEN ident = 'tgr7_2' THEN  'tgr7_11' 
        WHEN ident ~ 'tgr7_11' THEN  'tgr7_11' 
        WHEN ident ~ 'tgr7_12' THEN  'tgr7_12' 
        WHEN ident ~ 'tgr7_21' THEN  'tgr7_11' 
        WHEN ident ~ 'tgr7_22' THEN  'tgr7_12' 
        WHEN ident in ('tgr7_15','tgr7_25','tgr7_35','tgr7_53','tgr7_63') THEN  'tgr7_7' 
        ELSE ident END as ident, t.id,
    ss.*,
    (select round(value - value/6,4) as value from aqd_tarif_tbl as d 
		where d.id_tarif = t.id 
		and dt_begin <= $p_mmgg::date order by dt_begin desc limit 1) as tar_val
    from aqm_tarif_tbl as t 
    left join 
    (
      select id_tarif, 
		   coalesce(sum(CASE WHEN id_zone in (6)  THEN s.demand END ),0) as demand1,
                   coalesce(sum(CASE WHEN id_zone in (6)  THEN s.summ END),0) as summ1,
		   coalesce(sum(CASE WHEN id_zone in (7)  THEN s.demand END ),0) as demand2,
                   coalesce(sum(CASE WHEN id_zone in (7)  THEN s.summ END),0) as summ2,
		   coalesce(sum(CASE WHEN id_zone in (8)  THEN s.demand END ),0) as demand3,
                   coalesce(sum(CASE WHEN id_zone in (8)  THEN s.summ END),0) as summ3
    from acm_summ_tbl as s
    join acm_bill_tbl as b on (b.id_doc=s.id_doc)    
    where b.mmgg = $p_mmgg and id_zone in (6,7,8) and b.id_pref = 10
    $where
     group by id_tarif order by id_tarif
    ) as ss 
    on (ss.id_tarif = t.id)
    ) as sss
    group by ident
    ) as ssss
    right join rep_tarif_names_tbl as tt on (tt.ident = ssss.ident )
    where tt.str_code is not null and grp_code = 2
    order by str_code, tt.ident;";
        
        
/*        
    $SQL = " select ssss.*, tt.name , tt.grp_code, tt.str_code 
    from
    (
    select ident,
    sum(demand1) as demand1, sum(demand2) as demand2, sum(demand3) as demand3, 
    sum(summ1) as summ1,sum(summ2) as summ2,sum(summ3) as summ3, max(tar_val) as tar_val
    from
    (
    select CASE WHEN ident = 'tgr7_2' THEN  'tgr7_21' 
        WHEN ident in ('tgr7_15','tgr7_25','tgr7_35','tgr7_53','tgr7_63') THEN  'tgr7_7' 
        ELSE ident END as ident, t.id,
    ss.*,
    (select round(value - value/6,4) as value from aqd_tarif_tbl as d 
		where d.id_tarif = t.id 
		and dt_begin <= '2017-02-01'::date order by dt_begin desc limit 1) as tar_val
    from aqm_tarif_tbl as t 
    join 
    (
      select s.id_tarif, 
		   coalesce(sum(CASE WHEN id_zone in (6)  THEN s.demand END ),0) as demand1,
                   coalesce(sum(CASE WHEN id_zone in (6)  THEN s.summ END),0) as summ1,
		   coalesce(sum(CASE WHEN id_zone in (7)  THEN s.demand END ),0) as demand2,
                   coalesce(sum(CASE WHEN id_zone in (7)  THEN s.summ END),0) as summ2,
		   coalesce(sum(CASE WHEN id_zone in (8)  THEN s.demand END ),0) as demand3,
                   coalesce(sum(CASE WHEN id_zone in (8)  THEN s.summ END),0) as summ3
    from acm_summ_tbl as s
    join acm_bill_tbl as b on (b.id_doc=s.id_doc)    
    join aqd_tarif_tbl as tv on (tv.id = s.id_summtarif)        
    where b.mmgg = $p_mmgg and id_zone in (6,7,8) and b.id_pref = 10
    and ( tv.dt_begin < '2017-03-01' )   
    $where    
     group by s.id_tarif order by id_tarif
    ) as ss 
    on (ss.id_tarif = t.id)
    ) as sss
    group by ident
    ) as ssss
    right join rep_tarif_names_tbl as tt on (tt.ident = ssss.ident )
    where grp_code = 1 
    and str_code is not null                
    union all
     select * from ( 
        select ssss.*, tt.name , tt.grp_code, tt.str_code
        from
        (
        select ident,
        sum(demand1) as demand1, sum(demand2) as demand2, sum(demand3) as demand3, 
        sum(summ1) as summ1,sum(summ2) as summ2,sum(summ3) as summ3, max(tar_val) as tar_val
        from
        (
        select CASE WHEN ident = 'tgr7_2' THEN  'tgr7_21' 
            WHEN ident in ('tgr7_15','tgr7_25','tgr7_35','tgr7_53','tgr7_63') THEN  'tgr7_7' 
            ELSE ident END as ident, t.id,
        ss.*,
        (select round(value - value/6,4) as value from aqd_tarif_tbl as d 
                    where d.id_tarif = t.id 
                    and dt_begin <= $p_mmgg::date order by dt_begin desc limit 1) as tar_val
        from aqm_tarif_tbl as t 
        join 
        (
          select s.id_tarif, 
		   coalesce(sum(CASE WHEN id_zone in (6)  THEN s.demand END ),0) as demand1,
                   coalesce(sum(CASE WHEN id_zone in (6)  THEN s.summ END),0) as summ1,
		   coalesce(sum(CASE WHEN id_zone in (7)  THEN s.demand END ),0) as demand2,
                   coalesce(sum(CASE WHEN id_zone in (7)  THEN s.summ END),0) as summ2,
		   coalesce(sum(CASE WHEN id_zone in (8)  THEN s.demand END ),0) as demand3,
                   coalesce(sum(CASE WHEN id_zone in (8)  THEN s.summ END),0) as summ3
        from acm_summ_tbl as s
        join acm_bill_tbl as b on (b.id_doc=s.id_doc)    
        join aqd_tarif_tbl as tv on (tv.id = s.id_summtarif)        
        where b.mmgg = $p_mmgg and id_zone in (6,7,8) and b.id_pref = 10
        and ( tv.dt_begin >= '2017-03-01' )        
        $where
         group by s.id_tarif order by id_tarif
        ) as ss 
        on (ss.id_tarif = t.id)
        ) as sss
        group by ident
        ) as ssss
        right join rep_tarif_names_tbl as tt on (tt.ident = ssss.ident )        
        where grp_code = 2 and str_code is not null        
   ) as ssssss
   order by grp_code, str_code, ident ;  ";        
  */      
    }
    else
    {        
    
    $SQL = " select ssss.*, tt.name 
    from
    (
    select ident,
    sum(demand1) as demand1, sum(demand2) as demand2, sum(demand3) as demand3, 
    sum(summ1) as summ1,sum(summ2) as summ2,sum(summ3) as summ3, max(tar_val) as tar_val
    from
    (
    select CASE WHEN ident = 'tgr7_2' THEN  'tgr7_21' 
                WHEN ident in ('tgr7_15','tgr7_25','tgr7_35','tgr7_53','tgr7_63') THEN  'tgr7_7' 
                WHEN ident = 'tgr7_511' and $p_mmgg = '2016-03-01' THEN  'tgr7_11' 
                WHEN ident = 'tgr7_512' and $p_mmgg = '2016-03-01' THEN  'tgr7_12' 
                WHEN ident = 'tgr7_513' and $p_mmgg = '2016-03-01' THEN  'tgr7_121' 
                WHEN ident = 'tgr7_514' and $p_mmgg = '2016-03-01' THEN  'tgr7_21' 
                WHEN ident = 'tgr7_515' and $p_mmgg = '2016-03-01' THEN  'tgr7_22' 
                WHEN ident = 'tgr7_516' and $p_mmgg = '2016-03-01' THEN  'tgr7_221' 
    ELSE ident END as ident, t.id,
    ss.*,
    (select round(value - value/6,4) as value from aqd_tarif_tbl as d 
		where d.id_tarif = t.id 
		and dt_begin <= $p_mmgg::date order by dt_begin desc limit 1) as tar_val
    from aqm_tarif_tbl as t left join 
    (
      select id_tarif, 
		   coalesce(sum(CASE WHEN id_zone in (6)  THEN s.demand END ),0) as demand1,
                   coalesce(sum(CASE WHEN id_zone in (6)  THEN s.summ END),0) as summ1,
		   coalesce(sum(CASE WHEN id_zone in (7)  THEN s.demand END ),0) as demand2,
                   coalesce(sum(CASE WHEN id_zone in (7)  THEN s.summ END),0) as summ2,
		   coalesce(sum(CASE WHEN id_zone in (8)  THEN s.demand END ),0) as demand3,
                   coalesce(sum(CASE WHEN id_zone in (8)  THEN s.summ END),0) as summ3
    from acm_summ_tbl as s
    join acm_bill_tbl as b on (b.id_doc=s.id_doc)    
    where b.mmgg = $p_mmgg and id_zone in (6,7,8) and b.id_pref = 10
    $where
     group by id_tarif order by id_tarif
    ) as ss 
    on (ss.id_tarif = t.id)
    ) as sss
    group by ident
    ) as ssss
    right join rep_tarif_names_tbl as tt on (tt.ident = ssss.ident )
    where tt.str_code is not null and grp_code = 1
    order by str_code, tt.ident;";
    }
   // throw new Exception(json_encode($SQL));
    $sum_dem_z1=0;
    $sum_dem_z2=0;
    $sum_dem_z3=0;
    $sum_summ_0=0;
    $sum_summ_zon=0;
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        $i=0;
        while ($row = pg_fetch_array($result)) {

            
            //$r = $baseRow + $i;
            $caption = htmlspecialchars($row['name']);
            $demand = $row['demand1']+$row['demand2']+$row['demand3'];
            $summ = round($demand *$row['tar_val'],2);
            $summ_txt = number_format_ua ($summ,2);
            $tar1 = $k31*$row['tar_val'];
            $tar2 = $k32*$row['tar_val'];
            $tar3 = $k33*$row['tar_val'];
            $summ_zon = round($row['demand1']*$tar1+$row['demand2']*$tar2+$row['demand3']*$tar3,2);
            $summ_zon_txt = number_format_ua ($summ_zon,2);
            $summ_delta = $summ - $summ_zon;
            $summ_delta_txt = number_format_ua ($summ_delta,2);
            
            
            $sum_dem_z1+=$row['demand1'];
            $sum_dem_z2+=$row['demand2'];
            $sum_dem_z3+=$row['demand3'];
            $sum_summ_0+=$summ;
            $sum_summ_zon+=$summ_zon;
            
            
            //$caption = strtr($row['caption']," ",'&nbsp;');
            //$caption =str_replace(' ','&nbsp',$caption); 
            echo "
            <tr >
            <td>$caption</td>
            <td>2 клас </td>
            <td class='c_n'>{$demand}</td>
            <td class='c_n'>{$row['demand1']}</td>
            <td class='c_n'>{$row['demand2']}</td>
            <td class='c_n'>{$row['demand3']}</td>
            <td class='c_n'>{$row['tar_val']}</td>
            <td class='c_n'>{$summ_txt}</td>
            <td class='c_n'>{$tar1}</td>
            <td class='c_n'>{$tar2}</td>
            <td class='c_n'>{$tar3}</td>            
            <td class='c_n'>{$summ_zon_txt}</td>            
            <td class='c_n'>{$summ_delta_txt}</td>            
            </tr>  ";        
            $i++;
        }
        
       $demand = $sum_dem_z1+$sum_dem_z2+$sum_dem_z3; 
       $summ_delta = $sum_summ_0 - $sum_summ_zon;
       $summ_delta_txt = number_format_ua ($summ_delta,2);
       $summ_txt = number_format_ua ($sum_summ_0,2);
       $summ_zon_txt = number_format_ua ($sum_summ_zon,2);
       
       
       echo "
       <tr class='table_footer'>
       <td>Всього</td>
       <td>&nbsp;</td>
       <td class='c_n'>{$demand}</td>
       <td class='c_n'>{$sum_dem_z1}</td>
       <td class='c_n'>{$sum_dem_z2}</td>
       <td class='c_n'>{$sum_dem_z3}</td>
       <td class='c_n'></td>
       <td class='c_n'>{$summ_txt}</td>
       <td class='c_n'>&nbsp;</td>
       <td class='c_n'>&nbsp;</td>
       <td class='c_n'>&nbsp;</td>            
       <td class='c_n'>{$summ_zon_txt}</td>            
       <td class='c_n'>{$summ_delta_txt}</td>            
       </tr>  ";        
        
    }

    $cnt_all=0;
    $cnt_town=0;
    $cnt_vil=0;
    $cnt_elp=0;
    $cnt_elh=0;
    $cnt_notgas=0;
    
    $SQL = "select count(distinct b.id_paccnt ) as cnt, 
		 count(distinct CASE WHEN t.ident ~ 'tgr7_1' and tt.ident not in ('tgr7_15','tgr7_25','tgr7_35','tgr7_53','tgr7_63') THEN b.id_paccnt END) as cnt_town, 
		 count(distinct CASE WHEN t.ident ~ 'tgr7_2' and tt.ident not in ('tgr7_15','tgr7_25','tgr7_35','tgr7_53','tgr7_63') THEN b.id_paccnt END) as cnt_vil, 
		 count(distinct CASE WHEN t.ident ~ 'tgr7_3' and tt.ident not in ('tgr7_15','tgr7_25','tgr7_35','tgr7_53','tgr7_63') THEN b.id_paccnt END) as cnt_elp, 
		 count(distinct CASE WHEN t.ident ~ 'tgr7_5' and tt.ident not in ('tgr7_15','tgr7_25','tgr7_35','tgr7_53','tgr7_63') THEN b.id_paccnt END) as cnt_elh, 
		 count(distinct CASE WHEN t.ident ~ 'tgr7_6' and tt.ident not in ('tgr7_15','tgr7_25','tgr7_35','tgr7_53','tgr7_63') THEN b.id_paccnt END) as cnt_notgas,
		 count(distinct CASE WHEN tt.ident in ('tgr7_15','tgr7_25','tgr7_35','tgr7_53','tgr7_63') THEN b.id_paccnt END) as cnt_m
    from acm_summ_tbl as s
    join acm_bill_tbl as b on (b.id_doc=s.id_doc)    
    join aqm_tarif_tbl as t on (s.id_tarif = t.id)
    join aqi_grptar_tbl as tt on (tt.id = t.id_grptar) 
    where b.mmgg = $p_mmgg and id_zone in (6,7,8) and b.id_pref = 10 $where ;";

    $result = pg_query($Link, $SQL);
    if ($result) {
        $row = pg_fetch_array($result);

        $cnt_all=$row['cnt'];
        $cnt_town=$row['cnt_town'];
        $cnt_vil=$row['cnt_vil'];
        $cnt_elp=$row['cnt_elp'];
        $cnt_elh=$row['cnt_elh'];
        $cnt_m=$row['cnt_m'];
        $cnt_notgas=$row['cnt_notgas'];
    }
    
    
    eval("\$footer_text_eval = \"$footer_text\";");
    echo $footer_text_eval;

    //return;
    
}

//-----------------------------------------------

if ($oper=='zon2')
{
    $p_mmgg = sql_field_val('dt_b', 'mmgg');
    $mmgg1_str = trim($_POST['period_str']);
    $p_id_region = sql_field_val('id_region', 'int');
    
    list($year, $month, $day  ) = split('[/.-]', $p_mmgg);
    $year = trim($year,"'");
    
    $where='';
    $params_caption ='';
    
    $SQL = "select * from eqk_zone_tbl;";

    $result = pg_query($Link,$SQL);

    if ($result) {
     
        while ($row = pg_fetch_array($result)) {
            
            if ($row['id']==6)
                $k31=round($row['koef'],2);
            if ($row['id']==7)
                $k32=round($row['koef'],2);
            if ($row['id']==8)
                $k33=round($row['koef'],2);
            
            if ($row['id']==9)
                $k21=round($row['koef'],2);
            if ($row['id']==10)
                $k22=round($row['koef'],2);
        } 
    }    

    if ($p_id_region!='null')
    {
        
        $where.= "and exists (select rs.id from 
            prs_runner_paccnt as rp 
            join prs_runner_sectors as rs on (rs.id = rp.id_sector)
            where rp.id_paccnt = b.id_paccnt and rs.id_region =  $p_id_region ) ";

        $SQL_H="SELECT name from cli_region_tbl where id = $p_id_region ;";
    
        $result = pg_query($Link, $SQL_H) or die("SQL Error: " . pg_last_error($Link) . $SQL_H);
        $row = pg_fetch_array($result);
        
        $params_caption.= $row['name'];               
    }    
    
    
    
    eval("\$header_text = \"$header_text\";");
    
    echo $header_text;
    
    if (((($year+0)==2017) && (($month+0) >= 3 ))||(($year+0)>2017))    
    {

    $SQL = "select ssss.*, tt.name 
    from
    (
    select ident,
    sum(demand1) as demand1, sum(demand2) as demand2,
    sum(summ1) as summ1,sum(summ2) as summ2, max(tar_val) as tar_val
    from
    (
    select CASE WHEN ident = 'tgr7_2' THEN  'tgr7_11' 
        WHEN ident ~ 'tgr7_11' THEN  'tgr7_11' 
        WHEN ident ~ 'tgr7_12' THEN  'tgr7_12' 
        WHEN ident ~ 'tgr7_21' THEN  'tgr7_11' 
        WHEN ident ~ 'tgr7_22' THEN  'tgr7_12' 
        WHEN ident in ('tgr7_15','tgr7_25','tgr7_35','tgr7_53','tgr7_63') THEN  'tgr7_7' 
        ELSE ident END as ident, t.id,
    ss.*,
    (select round(value - value/6,4) as value from aqd_tarif_tbl as d 
		where d.id_tarif = t.id 
		and dt_begin <= $p_mmgg::date order by dt_begin desc limit 1) as tar_val
    from aqm_tarif_tbl as t 
    left join 
    (
      select id_tarif, 
		   sum(CASE WHEN id_zone in (9)  THEN s.demand END ) as demand1,
                   sum(CASE WHEN id_zone in (9)  THEN s.summ END) as summ1,
		   sum(CASE WHEN id_zone in (10)  THEN s.demand END ) as demand2,
                   sum(CASE WHEN id_zone in (10)  THEN s.summ END) as summ2
    from acm_summ_tbl as s
    join acm_bill_tbl as b on (b.id_doc=s.id_doc)     -----!
    where b.mmgg = $p_mmgg and id_zone in (9,10) and b.id_pref = 10
    $where
     group by id_tarif order by id_tarif
    ) as ss 
    on (ss.id_tarif = t.id)
    ) as sss
    group by ident
    ) as ssss
    right join rep_tarif_names_tbl as tt on (tt.ident = ssss.ident  )
    where tt.str_code is not null and grp_code = 2
    order by str_code, tt.ident;";        
        
        
 /*       
    $SQL = " select ssss.*, tt.name , tt.grp_code, tt.str_code 
    from
    (
    select ident,
    sum(demand1) as demand1, sum(demand2) as demand2,
    sum(summ1) as summ1,sum(summ2) as summ2, max(tar_val) as tar_val
    from
    (
    select CASE WHEN ident = 'tgr7_2' THEN  'tgr7_21' 
        WHEN ident in ('tgr7_15','tgr7_25','tgr7_35','tgr7_53','tgr7_63') THEN  'tgr7_7' 
        ELSE ident END as ident, t.id,
    ss.*,
    (select round(value - value/6,4) as value from aqd_tarif_tbl as d 
		where d.id_tarif = t.id 
		and dt_begin <= '2017-02-01'::date order by dt_begin desc limit 1) as tar_val
    from aqm_tarif_tbl as t 
    join 
    (
      select s.id_tarif, 
		   sum(CASE WHEN id_zone in (9)  THEN s.demand END ) as demand1,
                   sum(CASE WHEN id_zone in (9)  THEN s.summ END) as summ1,
		   sum(CASE WHEN id_zone in (10)  THEN s.demand END ) as demand2,
                   sum(CASE WHEN id_zone in (10)  THEN s.summ END) as summ2
    from acm_summ_tbl as s
    join acm_bill_tbl as b on (b.id_doc=s.id_doc)    
    join aqd_tarif_tbl as tv on (tv.id = s.id_summtarif)        
    where b.mmgg = $p_mmgg and id_zone in (9,10) and b.id_pref = 10
    and ( tv.dt_begin < '2017-03-01' )   
    $where    
     group by s.id_tarif order by id_tarif
    ) as ss 
    on (ss.id_tarif = t.id)
    ) as sss
    group by ident
    ) as ssss
    right join rep_tarif_names_tbl as tt on (tt.ident = ssss.ident )
    where grp_code = 1 
    and str_code is not null                
    union all
     select * from ( 
        select ssss.*, tt.name , tt.grp_code, tt.str_code
        from
        (
        select ident,
        sum(demand1) as demand1, sum(demand2) as demand2,
        sum(summ1) as summ1,sum(summ2) as summ2, max(tar_val) as tar_val
        from
        (
        select CASE WHEN ident = 'tgr7_2' THEN  'tgr7_21' 
            WHEN ident in ('tgr7_15','tgr7_25','tgr7_35','tgr7_53','tgr7_63') THEN  'tgr7_7' 
            ELSE ident END as ident, t.id,
        ss.*,
        (select round(value - value/6,4) as value from aqd_tarif_tbl as d 
                    where d.id_tarif = t.id 
                    and dt_begin <= $p_mmgg::date order by dt_begin desc limit 1) as tar_val
        from aqm_tarif_tbl as t 
        join 
        (
          select s.id_tarif, 
		   sum(CASE WHEN id_zone in (9)  THEN s.demand END ) as demand1,
                   sum(CASE WHEN id_zone in (9)  THEN s.summ END) as summ1,
		   sum(CASE WHEN id_zone in (10)  THEN s.demand END ) as demand2,
                   sum(CASE WHEN id_zone in (10)  THEN s.summ END) as summ2
        from acm_summ_tbl as s
        join acm_bill_tbl as b on (b.id_doc=s.id_doc)    
        join aqd_tarif_tbl as tv on (tv.id = s.id_summtarif)        
        where b.mmgg = $p_mmgg and id_zone in (9,10) and b.id_pref = 10
        and ( tv.dt_begin >= '2017-03-01' )        
        $where
         group by s.id_tarif order by id_tarif
        ) as ss 
        on (ss.id_tarif = t.id)
        ) as sss
        group by ident
        ) as ssss
        right join rep_tarif_names_tbl as tt on (tt.ident = ssss.ident )        
        where grp_code = 2 and str_code is not null        
   ) as ssssss
   order by grp_code, str_code, ident ;  ";        
      */  
    }
    else
    {        
    $SQL = "select ssss.*, tt.name 
    from
    (
    select ident,
    sum(demand1) as demand1, sum(demand2) as demand2,
    sum(summ1) as summ1,sum(summ2) as summ2, max(tar_val) as tar_val
    from
    (
    select CASE WHEN ident = 'tgr7_2' THEN  'tgr7_21' 
        WHEN ident in ('tgr7_15','tgr7_25','tgr7_35','tgr7_53','tgr7_63') THEN  'tgr7_7' 
        WHEN ident = 'tgr7_511' and $p_mmgg = '2016-03-01' THEN  'tgr7_11' 
        WHEN ident = 'tgr7_512' and $p_mmgg = '2016-03-01' THEN  'tgr7_12' 
        WHEN ident = 'tgr7_513' and $p_mmgg = '2016-03-01' THEN  'tgr7_121' 
        WHEN ident = 'tgr7_514' and $p_mmgg = '2016-03-01' THEN  'tgr7_21' 
        WHEN ident = 'tgr7_515' and $p_mmgg = '2016-03-01' THEN  'tgr7_22' 
        WHEN ident = 'tgr7_516' and $p_mmgg = '2016-03-01' THEN  'tgr7_221' 
        ELSE ident END as ident, t.id,
    ss.*,
    (select round(value - value/6,4) as value from aqd_tarif_tbl as d 
		where d.id_tarif = t.id 
		and dt_begin <= $p_mmgg::date order by dt_begin desc limit 1) as tar_val
    from aqm_tarif_tbl as t 
    join 
    (
      select id_tarif, 
		   sum(CASE WHEN id_zone in (9)  THEN s.demand END ) as demand1,
                   sum(CASE WHEN id_zone in (9)  THEN s.summ END) as summ1,
		   sum(CASE WHEN id_zone in (10)  THEN s.demand END ) as demand2,
                   sum(CASE WHEN id_zone in (10)  THEN s.summ END) as summ2
    from acm_summ_tbl as s
    join acm_bill_tbl as b on (b.id_doc=s.id_doc)     -----!
    where b.mmgg = $p_mmgg and id_zone in (9,10) and b.id_pref = 10
    $where
     group by id_tarif order by id_tarif
    ) as ss 
    on (ss.id_tarif = t.id)
    ) as sss
    group by ident
    ) as ssss
    right join rep_tarif_names_tbl as tt on (tt.ident = ssss.ident  )
    where tt.str_code is not null and grp_code = 1
    order by str_code, tt.ident;";
    };
    
    
   // throw new Exception(json_encode($SQL));
    $sum_dem_z1=0;
    $sum_dem_z2=0;
    $sum_summ_0=0;
    $sum_summ_zon=0;
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        $i=0;
        while ($row = pg_fetch_array($result)) {

            
            //$r = $baseRow + $i;
            $caption = htmlspecialchars($row['name']);
            $demand = $row['demand1']+$row['demand2'];
            $summ = round($demand *$row['tar_val'],2);
            $summ_txt = number_format_ua ($summ,2);
            $tar1 = $k21*$row['tar_val'];
            $tar2 = $k22*$row['tar_val'];

            $summ_zon = round($row['demand1']*$tar1+$row['demand2']*$tar2,2);
            $summ_zon_txt = number_format_ua ($summ_zon,2);
            $summ_delta = $summ - $summ_zon;
            $summ_delta_txt = number_format_ua ($summ_delta,2);
            
            
            $sum_dem_z1+=$row['demand1'];
            $sum_dem_z2+=$row['demand2'];

            $sum_summ_0+=$summ;
            $sum_summ_zon+=$summ_zon;
            
            
            //$caption = strtr($row['caption']," ",'&nbsp;');
            //$caption =str_replace(' ','&nbsp',$caption); 
            echo "
            <tr >
            <td>$caption</td>
            <td>2 клас </td>
            <td class='c_n'>{$demand}</td>
            <td class='c_n'>{$row['demand1']}</td>
            <td class='c_n'>{$row['demand2']}</td>
            <td class='c_n'>{$row['tar_val']}</td>
            <td class='c_n'>{$summ_txt}</td>
            <td class='c_n'>{$tar1}</td>
            <td class='c_n'>{$tar2}</td>
            <td class='c_n'>{$summ_zon_txt}</td>            
            <td class='c_n'>{$summ_delta_txt}</td>            
            </tr>  ";        
            $i++;
        }
        
       $demand = $sum_dem_z1+$sum_dem_z2; 
       $summ_delta = $sum_summ_0 - $sum_summ_zon;
       $summ_delta_txt = number_format_ua ($summ_delta,2);
       $summ_txt = number_format_ua ($sum_summ_0,2);
       $summ_zon_txt = number_format_ua ($sum_summ_zon,2);
       
       
       echo "
       <tr class='table_footer'>
       <td>Всього</td>
       <td>&nbsp;</td>
       <td class='c_n'>{$demand}</td>
       <td class='c_n'>{$sum_dem_z1}</td>
       <td class='c_n'>{$sum_dem_z2}</td>
       <td class='c_n'></td>
       <td class='c_n'>{$summ_txt}</td>
       <td class='c_n'>&nbsp;</td>
       <td class='c_n'>&nbsp;</td>
       <td class='c_n'>{$summ_zon_txt}</td>            
       <td class='c_n'>{$summ_delta_txt}</td>            
       </tr>  ";        
        
    }

    $cnt_all=0;
    $cnt_town=0;
    $cnt_vil=0;
    $cnt_elp=0;
    $cnt_elh=0;
    $cnt_notgas=0;
    
    $SQL = "select count(distinct b.id_paccnt ) as cnt, 
		 count(distinct CASE WHEN t.ident ~ 'tgr7_1' and tt.ident not in ('tgr7_15','tgr7_25','tgr7_35','tgr7_53','tgr7_63') THEN b.id_paccnt END) as cnt_town, 
		 count(distinct CASE WHEN t.ident ~ 'tgr7_2' and tt.ident not in ('tgr7_15','tgr7_25','tgr7_35','tgr7_53','tgr7_63') THEN b.id_paccnt END) as cnt_vil, 
		 count(distinct CASE WHEN t.ident ~ 'tgr7_3' and tt.ident not in ('tgr7_15','tgr7_25','tgr7_35','tgr7_53','tgr7_63') THEN b.id_paccnt END) as cnt_elp, 
		 count(distinct CASE WHEN t.ident ~ 'tgr7_5' and tt.ident not in ('tgr7_15','tgr7_25','tgr7_35','tgr7_53','tgr7_63') THEN b.id_paccnt END) as cnt_elh, 
		 count(distinct CASE WHEN t.ident ~ 'tgr7_6' and tt.ident not in ('tgr7_15','tgr7_25','tgr7_35','tgr7_53','tgr7_63') THEN b.id_paccnt END) as cnt_notgas,
		 count(distinct CASE WHEN tt.ident in ('tgr7_15','tgr7_25','tgr7_35','tgr7_53','tgr7_63') THEN b.id_paccnt END) as cnt_m
    from acm_summ_tbl as s
    join acm_bill_tbl as b on (b.id_doc=s.id_doc)    
    join aqm_tarif_tbl as t on (s.id_tarif = t.id)
    join aqi_grptar_tbl as tt on (tt.id = t.id_grptar) 
    where b.mmgg = $p_mmgg and id_zone in (9,10) and b.id_pref = 10 $where ;";

    $result = pg_query($Link, $SQL);
    if ($result) {
        $row = pg_fetch_array($result);

        $cnt_all=$row['cnt'];
        $cnt_town=$row['cnt_town'];
        $cnt_vil=$row['cnt_vil'];
        $cnt_elp=$row['cnt_elp'];
        $cnt_elh=$row['cnt_elh'];
        $cnt_m=$row['cnt_m'];
        $cnt_notgas=$row['cnt_notgas'];
    }
    
    
    eval("\$footer_text_eval = \"$footer_text\";");
    echo $footer_text_eval;

    //return;
    
}
//------------------------------------------------------------------------------
if (($oper=='dt_list')||($oper=='kt_list')||($oper=='kt_list_subs')) 
{
    $p_mmgg = sql_field_val('dt_b', 'mmgg');
    $p_dt = sql_field_val('dt_rep', 'date');
    $p_value = sql_field_val('sum_value', 'numeric');
    $p_direction = sql_field_val('sum_direction', 'int');
    $p_book = sql_field_val('book', 'string');
    $mmgg1_str = trim($_POST['period_str']);
    
    $p_id_sector = sql_field_val('id_sector', 'int');
    $p_id_region = sql_field_val('id_region', 'int');
    
    $p_debet_month = sql_field_val('debet_month', 'int');
    if ($p_debet_month=='null') $p_debet_month =0;
    
    $p_id_town = sql_field_val('addr_town', 'int');

    if (isset($_POST['town_detal']))
    {
        $p_town_detal = trim($_POST['town_detal']);
    }
    else 
    {
        $p_town_detal =0;
    }

    if ($p_town_detal ==1)
    {
       $order = "town, int_book, book, int_code, code";        
    }
    else
    {
       $order = "int_book, book, int_code, code";
    }
    
    $params_caption="" ;
    $where = ' ';
    $where2 = ' ';
    //$where_book = ' ';

    if ($p_id_town!='null')
    {
        //$where.= "and adr.id_town = $p_id_town ";
        $where.= " and ( adr.id_town = $p_id_town or adr.id_parent = $p_id_town )";
    }

    if ($p_book!='null')
    {
        $where.= " and book = $p_book ";
        $params_caption .= " книга : $p_book ";        
    }

    if ((isset($_POST['sector']))&&($_POST['sector']!=''))
        $params_caption.= ' Дiльниця: '. trim($_POST['sector']);

    if ($p_id_sector!='null')
    {
      $where.= "and exists (select id from prs_runner_paccnt as rp 
          where rp.id_paccnt = c.id and rp.id_sector = $p_id_sector ) ";
    }
    
    if ($p_id_region!='null')
    {
        
        $where.= "and exists (select rs.id from 
            prs_runner_paccnt as rp 
            join prs_runner_sectors as rs on (rs.id = rp.id_sector)
            where rp.id_paccnt = c.id and rs.id_region =  $p_id_region ) ";

        $SQL_H="SELECT name from cli_region_tbl where id = $p_id_region ;";
    
        $result = pg_query($Link, $SQL_H) or die("SQL Error: " . pg_last_error($Link) . $SQL_H);
        $row = pg_fetch_array($result);
        
        $params_caption.= $row['name'];        
       
    }
    
    if ((isset($_POST['note']))&&($_POST['note']!=''))
    {
        $where.= $_POST['note'];
        $params_caption .= $_POST['note'];
    }
    
    
    
    if ($p_value!='null')
    {
        if ($oper=='dt_list')
        {
            if ($p_direction==1) $where2= " where saldo_current > {$p_value}";
            if ($p_direction==2) $where2= " where saldo_current > 0 and saldo_current <= {$p_value}";
            if ($p_direction==3) $where2= " where saldo_current = {$p_value}";
        }
            
        else    
        {
            if ($p_direction==1)  $where2= " where saldo_current < -{$p_value}";
            if ($p_direction==2)  $where2= " where saldo_current <0 and saldo_current >= -{$p_value}";
            if ($p_direction==3)  $where2= " where saldo_current = -{$p_value}";
            
            
        }
        
        if ($p_direction==1) $p_dir_txt = 'більше';
        if ($p_direction==2) $p_dir_txt = 'менше';
        if ($p_direction==3) $p_dir_txt = 'дорівнює';
    }
    else
    {
        $p_dir_txt = 'більше';
        if ($oper=='dt_list')
            $where2= " where saldo_current > 0 ";
        else
            $where2= " where saldo_current < 0 ";
    }

    $old_month_sql='';
    if (($oper=='dt_list')&&($p_debet_month>0))
    {
        
      $old_month_sql = " join  ( select b.id_paccnt, sum(b.value - coalesce(bp.value,0)) as deb02
      from acm_bill_tbl b 
      left join 
          (select bp.id_bill,sum(bp.value) as value, sum(bp.value_tax) as value_tax 
           from acm_billpay_tbl as bp 
           join acm_bill_tbl as b on (b.id_doc=bp.id_bill)
           join acm_pay_tbl as p on (p.id_doc = bp.id_pay) 
           where b.mmgg_bill <= ( $p_mmgg::date -'$p_debet_month month'::interval)::date
           and p.id_pref = 10  
           group by bp.id_bill
          ) as bp on (b.id_doc=bp.id_bill)
      where
         b.mmgg_bill <= ( $p_mmgg::date -'$p_debet_month month'::interval)::date 
         and b.id_pref = 10 
      group by b.id_paccnt 
      having sum(b.value - coalesce(bp.value,0)) >0
     ) as b3 on (b3.id_paccnt = s.id_paccnt ) ";
        
    }
    
    $where_bill = '';
    if ($oldbill_only==1)
    {
        $where_bill = ' and b.idk_doc = 220 ';
        $params_caption .= " (без нарахувать поточного місяця) ";        
    }
    
    
    if ($oper=='kt_list_subs')
    {
        $params_caption .= " що мають субсидію у поточному періоді ";        
        $where2.= " and exists (select pp.id_doc from acm_pay_tbl as pp where pp.mmgg = $p_mmgg::date and pp.idk_doc in (110,111,193) and pp.id_paccnt = sss.id) ";
    }
    
    if ($rem_worker == 1)
    {
        $params_caption.= ' Працівники РЕМ ';
        $where.= " and c.rem_worker = true ";
    }    
    
    $SQL = "select * from 
    (
        select c.id, c.code, c.book,s.id_pref, s.b_val, s.b_valtax,
        b.value as bill_sum, p.value as pay_sum, 
    
        coalesce(s.b_val,0)+coalesce(b.value,0)-coalesce(p.value,0) as saldo_current,
    
        CASE WHEN coalesce(s.b_val,0)+coalesce(b.value,0)-coalesce(p.value,0) < 0 THEN  
            -(coalesce(s.b_val,0)+coalesce(b.value,0)-coalesce(p.value,0)) ELSE
            coalesce(s.b_val,0)+coalesce(b.value,0)-coalesce(p.value,0) END  as saldo_print,
    
        adr.town, adr.street, address_print(c.addr) as house,
        (ab.last_name||' '||coalesce(ab.name,'')||' '||coalesce(ab.patron_name,''))::varchar as abon,
        ('0'||substring(c.code FROM '[0-9]+'))::int as int_code,
        ('0'||substring(c.book FROM '[0-9]+'))::int as int_book,
        sw.sw_action, (coalesce(ab.home_phone||'; ','')|| coalesce(c.note,'') ) as comment, ap.name as add_param,
        to_char((select max(p.reg_date) from acm_pay_tbl as p 
        	where p.id_pref = 10 and p.value<> 0 and  p.id_paccnt = s.id_paccnt), 'DD.MM.YYYY') as date_pay
    
        from acm_saldo_tbl as s
        join clm_paccnt_tbl as c on (c.id = s.id_paccnt)
        join clm_abon_tbl as ab on (ab.id = c.id_abon) 
        join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class)
        $old_month_sql
        left join cli_addparam_tbl as ap on (ap.id = c.id_cntrl)        
        left join (
        	select b.id_paccnt, sum(b.value) as value
                from acm_bill_tbl as b 
                where b.mmgg = $p_mmgg::date and b.id_pref = 10 $where_bill
                group by b.id_paccnt
        ) as b
        on (b.id_paccnt = s.id_paccnt)
        left join (
        	select p.id_paccnt, sum(p.value) as value
        	from acm_pay_tbl as p 
        	where p.mmgg = $p_mmgg and p.id_pref = 10 and p.reg_date <= $p_dt::date
        	group by p.id_paccnt
        ) as p
        on (p.id_paccnt = s.id_paccnt)
        left join 
        (
            select csw.id_paccnt, sn.name||' '||to_char(csw.dt_action, 'DD.MM.YYYY') as sw_action
            from clm_switching_tbl as csw 
            join (select id_paccnt, max(dt_action) as maxdt from clm_switching_tbl where action in (1,2) group by id_paccnt) as csdt 
            on (csw.id_paccnt = csdt.id_paccnt and csw.dt_action = csdt.maxdt) 
            join cli_switch_action_tbl as sn on (sn.id = csw.action)
            left join clm_switching_tbl as cswc on (csw.id_paccnt = cswc.id_paccnt and cswc.dt_action >= csw.dt_action and ((cswc.action =3 and csw.action =1) or (cswc.action =4  and csw.action =2) ) ) 
            where csw.action not in (3,4) and cswc.id_paccnt is null 
        ) as sw on (sw.id_paccnt = s.id_paccnt)    
        where s.mmgg = $p_mmgg::date and s.id_pref = 10 $where
     ) as sss
    $where2
    order by $order;";

   // throw new Exception(json_encode($SQL));
   //-- and b.reg_date <= $p_dt::date
   /*    
        join (select id, max(dt_b) as dt_b from clm_paccnt_h  where 
            ((dt_b < ($p_mmgg::date+'1 month'::interval) and dt_e is null)
            or 
            tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
        ) group by id order by id) as c2 
        on (c.id = c2.id and c.dt_b = c2.dt_b)
    */
    
    $current_town='';
    $np=0;
    $print_flag = 1;    
    $print_h_flag=1;
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        $sum_town_cnt=0;
        $sum_town_summ=0;

        $sum_all_cnt=0;
        $sum_all_summ=0;
        $cnt_headers=0;
        
        while ($row = pg_fetch_array($result)) {

            if ($current_town!=$row['town'])
            {
                if ($p_town_detal == 1) {
                    if (($cnt_headers == $grp_num - 1) || ($show_pager == 0))
                        $print_h_flag = 1;
                    else
                        $print_h_flag = 0;

                    if (($cnt_headers == $grp_num) || ($show_pager == 0))
                        $print_h2_flag = 1;
                    else
                        $print_h2_flag = 0;
                }
                //-------------------------------------
                
                $current_town=$row['town'];
                
                $town_str='';
                if (($p_id_town!='null')||($p_town_detal==1))
                {
                    $town_str= "по населеному пункту $current_town ";
                }
                
                if ($p_value!='null')
                    $p_valuestr = number_format_ua ($p_value,2);
                else
                    $p_valuestr = '0';
                
                $bookstr='';
                if ($p_book!='null')
                    $bookstr=" ,книга $p_book";

                $dt_str = $_POST['dt_rep'];

                if ($cnt_headers == 0)
                {
                    eval("\$header_text_eval = \"$header_text\";");
                    echo_html($header_text_eval, $print_h_flag);
                }
                else
                {
                    if($p_town_detal==1)
                    {
                        $sum_town_summ_str =  number_format_ua ($sum_town_summ,2);

                        echo_html( "
                        <tr class='tab_head'>
                            <td>Всього &nbsp;&nbsp;  -  &nbsp;&nbsp;&nbsp;&nbsp;  $sum_town_cnt </td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>                     
                            <td class='c_n'>{$sum_town_summ_str}</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>  ",$print_h2_flag*$print_flag);        
                     

                        echo_html(  '</table> <br/>',$print_h_flag*$print_flag);
                    
                        $sum_town_cnt=0;
                        $sum_town_summ=0;
                    
                        eval("\$header_text_eval = \"$header_text\";");
                        echo_html($header_text_eval, $print_h_flag);
                    }

                }
                
                $cnt_headers++;
            }
            if ($print_h_flag==1) { $np++;  };
            
            if ((($np> ($page_num-1)* $page_size )&& ($np<= $page_num* $page_size )) || ($show_pager ==0))
                $print_flag = 1;
            else
                $print_flag = 0;
            
            
            if (($p_id_town!='null')||($p_town_detal==1))
                $addr = $row['street'].' '.$row['house'];
            else
                $addr = $row['town'].' '.$row['street'].' '.$row['house'];
                
                $book=$row['book'].'/'.$row['code'];
                
                $sum_str =  number_format_ua ($row['saldo_print'],2);
                $add_param = htmlspecialchars($row['add_param']);

                if ($p_sum_only!=1)
                {

                    echo_html( "
                    <tr>
                     <td>{$addr}</td>
                     <td >{$row['abon']}</td>
                     <td class='c_t'>{$book}</td>                     
                     <td class='c_n'>{$sum_str}</td>
                     <td class='c_t'>{$row['sw_action']}</td>
                     <td class='c_t'>{$row['date_pay']}</td>
                     <td class='c_t'>{$add_param}</td>
                     <td class='c_t'>{$row['comment']}</td>
                    </tr>  ",$print_h_flag*$print_flag);        
                }            
            $sum_town_cnt++;
            $sum_town_summ+=$row['saldo_print'];

            $sum_all_cnt++;
            $sum_all_summ+=$row['saldo_print'];

        }
    }


    if ($current_town != '') {
        if ($p_town_detal == 1) {
            
            $sum_town_summ_str = number_format_ua ($sum_town_summ, 2);

            echo_html(  "
                        <tr class='tab_head'>
                            <td>Всього &nbsp;&nbsp;  -  &nbsp;&nbsp;&nbsp;&nbsp;  $sum_town_cnt </td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td class='c_n'>{$sum_town_summ_str}</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>  ",$print_h_flag*$print_flag);


//            echo '</table> <br/>';

        }
    }

    $sum_all_summ_str = number_format_ua ($sum_all_summ, 2);
    
    eval("\$footer_text_eval = \"$footer_text\";");
    
    echo_html( $footer_text_eval,$print_h_flag*$print_flag);
   
    if ($print_flag==0) echo "</tbody> </table> ";
    
}
//-----------------------------------------------
if (($oper=='nas_p_all')||($oper=='nas_p_3')||($oper=='nas_p_p')||($oper=='nas_p_h')||($oper=='nas_p_off'))
{
    $p_mmgg = sql_field_val('dt_b', 'mmgg');
    $mmgg1_str = trim($_POST['period_str']);
    $p_id_region = sql_field_val('id_region', 'int');
    $params_caption = '';
    $mode_caption = ' населених пунктiв ';
    
    rep_abon_dt($Link, $p_mmgg,  2, 0);    
        
    $where = '';
    if ($oper=='nas_p_p') 
        {$where = " where tar.ident ~'tgr7_3' ";
         $params_caption = '<BR/> Електроплити';  }

    if ($oper=='nas_p_h') 
        {$where = " where tar.ident ~'tgr7_5' ";
         $params_caption = '<BR/> Електроопалення';     }

    if ($oper=='nas_p_off') 
        {$where = " where c.activ = false ";
         $params_caption = '<BR/> Відключені';     }
         
    if ($oper=='nas_p_3') 
    {
        $where = "   where c.id in  (
        select m.id_paccnt from 
	clm_meterpoint_h as m 
        join (select id, max(dt_b) as dt_b from clm_meterpoint_h  where 
        ((dt_b < ($p_mmgg::date+'1 month'::interval) and dt_e is null)
            or 
            tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
        )
        group by id order by id) as m2 
	on (m.id = m2.id and m.dt_b = m2.dt_b)
	join eqi_meter_tbl as im on (im.id = id_type_meter)
	where im.phase=2 ) ";

        $params_caption = '<BR/> Трифазні';   
    }

    if ($p_id_region!='null')
    {
        if ($where=='') 
             {$where = ' where ';}
        else { $where.= ' and ' ;}
        
        $where.= " exists (select rs.id from 
            prs_runner_paccnt as rp 
            join prs_runner_sectors as rs on (rs.id = rp.id_sector)
            where rp.id_paccnt = c.id and rs.id_region =  $p_id_region ) ";

        $SQL_H="SELECT name from cli_region_tbl where id = $p_id_region ;";
    
        $result = pg_query($Link, $SQL_H) or die("SQL Error: " . pg_last_error($Link) . $SQL_H);
        $row = pg_fetch_array($result);
        
        $params_caption.= $row['name'];               
    }    
    
    
    eval("\$header_text = \"$header_text\";");
    echo $header_text;
    
    // Субсидия !!!!
    //        sum(dt_val - coalesce(value_subs,0) ) as dt_val, sum(kt_val - coalesce(value_subs,0)) as kt_val,sum(e_val) as e_val,
    
    $SQL = "select town,  count(distinct CASE WHEN archive = 0 THEN id END ) as cnt_all,
        count(distinct CASE WHEN coalesce(demand,0)<>0 THEN id END) as cnt_bill,
	count(distinct CASE WHEN coalesce(kt_val,0)<>0 THEN id END) as cnt_pay,
        sum(dt_val- coalesce(value_subs,0)) as dt_val, sum(kt_val - coalesce(value_subs,0)) as kt_val,
        sum(e_val) as e_val,
        sum(CASE WHEN e_val >0 THEN e_val END) as e_dtval,
        sum(value_subs) as subs_val,
	sum(demand) as demand,
	count(distinct CASE WHEN coalesce(old_sum,0)<>0 and e_val >0 THEN id END) as cnt_pay3m,
	sum(CASE WHEN coalesce(old_sum,0)<>0 and e_val >0 THEN coalesce(old_sum,0) END) as sum_pay3m
from
(
select c.id, adr.town,
       s.dt_val, s.dt_valtax, s.kt_val, s.kt_valtax, s.e_val as e_val, s.e_valtax as e_valtax ,
       b.demand , ps.value_subs,d_old.summ as old_sum,coalesce(c.archive,0) as archive
        from  clm_paccnt_h as c 
        join (select id, max(dt_b) as dt_b from clm_paccnt_h  where 
         ((dt_b < ($p_mmgg::date+'1 month'::interval) and dt_e is null)
             or 
             tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
         )  -- and archive =0 
         group by id order by id) as c2 
        on (c.id = c2.id and c.dt_b = c2.dt_b)
        join acm_saldo_tbl as s on (s.id_paccnt = c.id and s.mmgg = $p_mmgg and s.id_pref=10) 
	join aqi_grptar_tbl as tar on (tar.id = c.id_gtar)
	join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class)
        left join rep_abon_dt_tbl as d_old on (d_old.id_paccnt = c.id)
	left join (select id_paccnt, sum(demand) as demand
           from acm_bill_tbl where mmgg = $p_mmgg and id_pref=10 
            group by id_paccnt order by id_paccnt) as b
             on (b.id_paccnt = c.id)
        left join (
            select p.id_paccnt, sum(p.value) as value_subs
            from acm_pay_tbl as p 
            where p.mmgg = $p_mmgg::date and p.id_pref = 10 and p.idk_doc in (110,111,193,194)
       	    group by p.id_paccnt
	     order by id_paccnt
         ) as ps on (ps.id_paccnt = c.id)
        $where
 ) as ss       
group by town
order by town        
";

   // throw new Exception(json_encode($SQL));
    $sum_cnt_all=0;
    $sum_cnt_bill=0;
    $sum_cnt_pay=0;
    
    $sum_dem_bill=0;
    $sum_summ_bill=0;
    
    $sum_summ_pay=0;
    $sum_summ_subs=0;
    
    $sum_saldo=0;
    $sum_dt_saldo=0;
    
    $sum_cnt_3=0;
    $sum_saldo_3=0;
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        $i=0;
        while ($row = pg_fetch_array($result)) {

            $town = htmlspecialchars($row['town']);

            $dt_val_txt = number_format_ua ($row['dt_val'],2);
            $kt_val_txt = number_format_ua ($row['kt_val'],2);
            $subs_val_txt = number_format_ua ($row['subs_val'],2);
            
            $e_val_txt = number_format_ua ($row['e_val'],2);  
            $e_dtval_txt = number_format_ua ($row['e_dtval'],2);  
            $sum_pay3m_txt = number_format_ua ($row['sum_pay3m'],2);  
            
            if ($row['cnt_bill']!=0)
                $avg_dem_sum = round($row['dt_val']/$row['cnt_bill'],2);
            else
                $avg_dem_sum =0;
            
            $avg_dem_sum_txt = number_format_ua ($avg_dem_sum,2);
            
            if ($row['cnt_pay']!=0)
                $avg_pay_sum = round($row['kt_val']/$row['cnt_pay'],2);
            else
                $avg_pay_sum =0;
            
            $avg_pay_sum_txt = number_format_ua ($avg_pay_sum,2);
            
            if ($row['dt_val']!=0)
                $pay_perc = round($row['kt_val']*100/$row['dt_val'],2);
            else
                $pay_perc =0;
            
            $pay_perc_txt = number_format_ua ($pay_perc,2);
            
            $sum_cnt_all+=$row['cnt_all'];
            $sum_cnt_bill+=$row['cnt_bill'];
            $sum_cnt_pay+=$row['cnt_pay'];
            
            $sum_dem_bill+=$row['demand'];
            $sum_summ_bill+=$row['dt_val'];
    
            $sum_summ_pay+=$row['kt_val'];
            $sum_summ_subs+=$row['subs_val'];
    
            $sum_saldo+=$row['e_val'];
    
            $sum_cnt_3+=$row['cnt_pay3m'];
            $sum_saldo_3+=$row['sum_pay3m'];
            $sum_dt_saldo+=$row['e_dtval'];
            
            
            echo "
            <tr >
            <td>$town</td>
            <td class='c_i'>{$row['cnt_all']}</td>
            <td class='c_i'>{$row['cnt_bill']}</td>
            <td class='c_i'>{$row['demand']}</td>
            <td class='c_n'>{$dt_val_txt}</td>
            <td class='c_n'>{$avg_dem_sum_txt}</td>
            <td class='c_i'>{$row['cnt_pay']}</td>
            <td class='c_n'>{$kt_val_txt}</td>
            <td class='c_n'>{$subs_val_txt}</td>
            <td class='c_n'>{$avg_pay_sum_txt}</td>            
            <td class='c_n'>{$pay_perc_txt}</td>            
            <td class='c_n'>{$e_val_txt}</td>            
            <td class='c_n'>{$e_dtval_txt}</td>            
            <td class='c_i'>{$row['cnt_pay3m']}</td>            
            <td class='c_n'>{$sum_pay3m_txt}</td>            
            </tr>  ";        
            $i++;
        }
        
        
    }

    $dt_val_txt = number_format_ua ($sum_summ_bill, 2);
    $kt_val_txt = number_format_ua ($sum_summ_pay, 2);
    $subs_val_txt = number_format_ua ($sum_summ_subs, 2);
    $e_val_txt = number_format_ua ($sum_saldo, 2);
    $e_dtval_txt = number_format_ua ($sum_dt_saldo, 2);
    $sum_pay3m_txt = number_format_ua ($sum_saldo_3, 2);

    if ($sum_cnt_bill!=0)
        $avg_dem_sum = round($sum_summ_bill / $sum_cnt_bill, 2);
    else
        $avg_dem_sum =0;
    
    $avg_dem_sum_txt = number_format_ua ($avg_dem_sum, 2);

    if ($sum_cnt_pay!=0)
        $avg_pay_sum = round($sum_summ_pay / $sum_cnt_pay, 2);
    else
        $avg_pay_sum=0;
    
    $avg_pay_sum_txt = number_format_ua ($avg_pay_sum, 2);

    if ($sum_summ_bill!=0)
        $pay_perc = round($sum_summ_pay * 100 / $sum_summ_bill, 2);
    else
        $pay_perc = 0;
    
    $pay_perc_txt = number_format_ua ($pay_perc, 2);
    
    eval("\$footer_text_eval = \"$footer_text\";");
    echo $footer_text_eval;
   
}
//----------------------------------------------------------------------------
if (($oper=='nas_p_kur')||($oper=='nas_p_kontrol'))
{
    $p_mmgg = sql_field_val('dt_b', 'mmgg');
    $mmgg1_str = trim($_POST['period_str']);
    $p_id_region = sql_field_val('id_region', 'int');
    
    $where = '';
    $params_caption ='';
    
    
    if ($oper=='nas_p_kur')
    {
      $mode_caption = "дільниць кур'єрів ";
      $field = 'id_runner';
    }
    
    if ($oper=='nas_p_kontrol')
    {
      $mode_caption = "дільниць контролерів ";
      $field = 'id_kontrol';
    }
    
    if ($p_id_region!='null')
    {
        
        $where.= " where exists (select rs.id from 
            prs_runner_paccnt as rp 
            join prs_runner_sectors as rs on (rs.id = rp.id_sector)
            where rp.id_paccnt = c.id and rs.id_region =  $p_id_region ) ";

        $SQL_H="SELECT name from cli_region_tbl where id = $p_id_region ;";
    
        $result = pg_query($Link, $SQL_H) or die("SQL Error: " . pg_last_error($Link) . $SQL_H);
        $row = pg_fetch_array($result);
        
        $params_caption.= $row['name'];               
    }    
    
    eval("\$header_text = \"$header_text\";");
    
    echo $header_text;
    
    rep_abon_dt($Link, $p_mmgg,  2, 0);
    
    $SQL = "select coalesce(represent_name,'000') as represent_name, coalesce(sector,'--абоненти без дільниці') as sector,
        town, count(distinct CASE WHEN archive = 0 THEN id END ) as cnt_all,
        count(distinct CASE WHEN coalesce(demand,0)<>0 THEN id END) as cnt_bill,
	count(distinct CASE WHEN coalesce(kt_val,0)<>0 THEN id END) as cnt_pay,
        sum(dt_val- coalesce(value_subs,0)) as dt_val, sum(kt_val - coalesce(value_subs,0)) as kt_val, sum(e_val) as e_val,
	sum(demand) as demand, sum(value_subs) as subs_val,
	count(distinct CASE WHEN coalesce(old_sum,0)<>0 and e_val <0 THEN id END) as cnt_pay3m,
	sum(CASE WHEN coalesce(old_sum,0)<>0 and e_val <0 THEN coalesce(old_sum,0) END) as sum_pay3m
from
( select c.id, adr.town, represent_name, sector,
       s.dt_val, s.dt_valtax, s.kt_val, s.kt_valtax, -s.e_val as e_val, -s.e_valtax as e_valtax ,
       b.demand , ps.value_subs, d_old.summ as old_sum, coalesce(c.archive,0) as archive

        from  clm_paccnt_h as c 
        join (select id, max(dt_b) as dt_b from clm_paccnt_h  where 
        ((dt_b < ($p_mmgg::date+'1 month'::interval) and dt_e is null)
            or 
            tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
          ) -- and archive =0 
         group by id order by id) as c2 
        on (c.id = c2.id and c.dt_b = c2.dt_b)
	join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class)
	join aqi_grptar_tbl as tar on (tar.id = c.id_gtar)
	join acm_saldo_tbl as s on (s.id_paccnt = c.id and s.mmgg = $p_mmgg and s.id_pref=10)
	left join (select id_paccnt, sum(demand) as demand from acm_bill_tbl where mmgg = $p_mmgg and demand <>0 and id_pref=10 
            group by id_paccnt order by id_paccnt) as b
             on (b.id_paccnt = c.id)
        left join (
            select p.id_paccnt, sum(p.value) as value_subs
            from acm_pay_tbl as p 
            where p.mmgg = $p_mmgg::date and p.id_pref = 10 and p.idk_doc in (110,111,193,194)
       	    group by p.id_paccnt
	    order by id_paccnt
        ) as ps on (ps.id_paccnt = c.id)
        left join rep_abon_dt_tbl as d_old on (d_old.id_paccnt = c.id)
       	left join (
                select rs.id, rs.{$field} as id_runner,rs.name,rp.id_paccnt,coalesce(p.represent_name,'111') as represent_name,
		CASE WHEN p.represent_name is not null THEN p.represent_name ELSE '--'||rs.name END::varchar as sector--COALESCE(p.represent_name,'--відсутній')
		from prs_runner_sectors as rs 
		join prs_runner_paccnt as rp on (rp.id_sector = rs.id)
		left join prs_persons as p on (p.id = rs.{$field})
		order by represent_name,sector) as rr on (rr.id_paccnt = c.id)
         $where       
 ) as ss       
group by represent_name, sector, town
order by represent_name, sector, town;      
";

   // throw new Exception(json_encode($SQL));
    $sum_cnt_all=0;
    $sum_cnt_bill=0;
    $sum_cnt_pay=0;
    $sum_summ_subs=0;
    $sum_dem_bill=0;
    $sum_summ_bill=0;
    $sum_summ_pay=0;
    $sum_saldo=0;
    $sum_cnt_3=0;
    $sum_saldo_3=0;

    $kur_cnt_all=0;
    $kur_cnt_bill=0;
    $kur_cnt_pay=0;
    $kur_summ_subs=0;
    $kur_dem_bill=0;
    $kur_summ_bill=0;
    $kur_summ_pay=0;
    $kur_saldo=0;
    $kur_cnt_3=0;
    $kur_saldo_3=0;
    
    
    $sector='';
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        $i=0;
        while ($row = pg_fetch_array($result)) {

            if ($row['sector']!=$sector)
            {
                
                if ($sector!='')
                {
                    $dt_val_txt = number_format_ua($kur_summ_bill, 2);
                    $kt_val_txt = number_format_ua($kur_summ_pay, 2);
                    $e_val_txt = number_format_ua($kur_saldo, 2);
                    $subs_val_txt = number_format_ua($kur_summ_subs, 2);
                    $sum_pay3m_txt = number_format_ua($kur_saldo_3, 2);
                    $subs_val_txt = number_format_ua($kur_summ_subs, 2);

                    if ($kur_cnt_bill != 0)
                        $avg_dem_sum = round($kur_summ_bill / $kur_cnt_bill, 2);
                    else
                        $avg_dem_sum = 0;

                    $avg_dem_sum_txt = number_format_ua($avg_dem_sum, 2);

                    if ($kur_cnt_pay != 0)
                        $avg_pay_sum = round($kur_summ_pay / $kur_cnt_pay, 2);
                    else
                        $avg_pay_sum = 0;

                    $avg_pay_sum_txt = number_format_ua($avg_pay_sum, 2);

                    if ($kur_summ_bill != 0)
                        $pay_perc = round($kur_summ_pay * 100 / $kur_summ_bill, 2);
                    else
                        $pay_perc = 0;

                    $pay_perc_txt = number_format_ua($pay_perc, 2);
                    
                    echo "                    
                        <TR class='tab_head'>
                        <TD>Всього по $sector </td>
                        <TD class='c_i'>{$kur_cnt_all}</td>
                        <TD class='c_i'>{$kur_cnt_bill}</td>
                        <TD class='c_i'>{$kur_dem_bill}</td>
                        <TD class='c_n'>{$dt_val_txt}</td>
                        <TD class='c_n'>{$avg_dem_sum_txt}</td>
                        <TD class='c_i'>{$kur_cnt_pay}</td>
                        <TD class='c_n'>{$kt_val_txt}</td>
                        <TD class='c_n'>{$subs_val_txt}</td>
                        <TD class='c_n'>{$avg_pay_sum_txt}</td>
                        <TD class='c_n'>{$pay_perc_txt}</td>
                        <TD class='c_n'>{$e_val_txt}</td>
                        <TD class='c_i'>{$kur_cnt_3}</td>
                        <TD class='c_n'>{$sum_pay3m_txt}</td>
                        </tr> ";                      
                        
                    $kur_cnt_all=0;
                    $kur_cnt_bill=0;
                    $kur_cnt_pay=0;
                    $kur_summ_subs=0;
                    $kur_dem_bill=0;
                    $kur_summ_bill=0;
                    $kur_summ_pay=0;
                    $kur_saldo=0;
                    $kur_cnt_3=0;
                    $kur_saldo_3=0;                        
                }
                
                $sector=$row['sector'];
                
                echo "
                <tr >
                <td COLSPAN=14>$sector</td>
                </tr>  ";        
            }
            
            $town = htmlspecialchars($row['town']);

            $dt_val_txt = number_format_ua ($row['dt_val'],2);
            $kt_val_txt = number_format_ua ($row['kt_val'],2);
            $e_val_txt = number_format_ua ($row['e_val'],2);  
            $subs_val_txt = number_format_ua ($row['subs_val'],2);
            $sum_pay3m_txt = number_format_ua ($row['sum_pay3m'],2);  
            
            if ($row['cnt_bill']!=0)
                $avg_dem_sum = round($row['dt_val']/$row['cnt_bill'],2);
            else
                $avg_dem_sum =0;
            
            $avg_dem_sum_txt = number_format_ua ($avg_dem_sum,2);
            
            if ($row['cnt_pay']!=0)
                $avg_pay_sum = round($row['kt_val']/$row['cnt_pay'],2);
            else
                $avg_pay_sum =0;
            
            $avg_pay_sum_txt = number_format_ua ($avg_pay_sum,2);
            
            if ($row['dt_val']!=0)
                $pay_perc = round($row['kt_val']*100/$row['dt_val'],2);
            else
                $pay_perc =0;
            
            $pay_perc_txt = number_format_ua ($pay_perc,2);
            
            $sum_cnt_all+=$row['cnt_all'];
            $sum_cnt_bill+=$row['cnt_bill'];
            $sum_cnt_pay+=$row['cnt_pay'];
            
            $sum_dem_bill+=$row['demand'];
            $sum_summ_bill+=$row['dt_val'];
    
            $sum_summ_pay+=$row['kt_val'];
            $sum_summ_subs+=$row['subs_val'];
            $sum_saldo+=$row['e_val'];
    
            $sum_cnt_3+=$row['cnt_pay3m'];
            $sum_saldo_3+=$row['sum_pay3m'];
            

            $kur_cnt_all+=$row['cnt_all'];
            $kur_cnt_bill+=$row['cnt_bill'];
            $kur_cnt_pay+=$row['cnt_pay'];
            
            $kur_dem_bill+=$row['demand'];
            $kur_summ_bill+=$row['dt_val'];
    
            $kur_summ_pay+=$row['kt_val'];
            $kur_summ_subs+=$row['subs_val'];
            $kur_saldo+=$row['e_val'];
    
            $kur_cnt_3+=$row['cnt_pay3m'];
            $kur_saldo_3+=$row['sum_pay3m'];
            
            
            echo "
            <tr >
            <td>&nbsp;&nbsp;&nbsp;{$town}</td>
            <td class='c_i'>{$row['cnt_all']}</td>
            <td class='c_i'>{$row['cnt_bill']}</td>
            <td class='c_i'>{$row['demand']}</td>
            <td class='c_n'>{$dt_val_txt}</td>
            <td class='c_n'>{$avg_dem_sum_txt}</td>
            <td class='c_i'>{$row['cnt_pay']}</td>
            <td class='c_n'>{$kt_val_txt}</td>
            <td class='c_n'>{$subs_val_txt}</td>
            <td class='c_n'>{$avg_pay_sum_txt}</td>            
            <td class='c_n'>{$pay_perc_txt}</td>            
            <td class='c_n'>{$e_val_txt}</td>            
            <td class='c_i'>{$row['cnt_pay3m']}</td>            
            <td class='c_n'>{$sum_pay3m_txt}</td>            
            </tr>  ";        
            $i++;
        }
        
        
    }

    
   if ($sector != '') {
        $dt_val_txt = number_format_ua($kur_summ_bill, 2);
        $kt_val_txt = number_format_ua($kur_summ_pay, 2);
        $e_val_txt = number_format_ua($kur_saldo, 2);
        $subs_val_txt = number_format_ua($kur_summ_subs, 2);
        $sum_pay3m_txt = number_format_ua($kur_saldo_3, 2);
        $subs_val_txt = number_format_ua($kur_summ_subs, 2);

        if ($kur_cnt_bill != 0)
            $avg_dem_sum = round($kur_summ_bill / $kur_cnt_bill, 2);
        else
            $avg_dem_sum = 0;

        $avg_dem_sum_txt = number_format_ua($avg_dem_sum, 2);

        if ($kur_cnt_pay != 0)
            $avg_pay_sum = round($kur_summ_pay / $kur_cnt_pay, 2);
        else
            $avg_pay_sum = 0;

        $avg_pay_sum_txt = number_format_ua($avg_pay_sum, 2);

        if ($kur_summ_bill != 0)
            $pay_perc = round($kur_summ_pay * 100 / $kur_summ_bill, 2);
        else
            $pay_perc = 0;

        $pay_perc_txt = number_format_ua($pay_perc, 2);

        echo "                    
                        <TR class='tab_head'>
                        <TD>Всього по $sector </td>
                        <TD class='c_i'>{$kur_cnt_all}</td>
                        <TD class='c_i'>{$kur_cnt_bill}</td>
                        <TD class='c_i'>{$kur_dem_bill}</td>
                        <TD class='c_n'>{$dt_val_txt}</td>
                        <TD class='c_n'>{$avg_dem_sum_txt}</td>
                        <TD class='c_i'>{$kur_cnt_pay}</td>
                        <TD class='c_n'>{$kt_val_txt}</td>
                        <TD class='c_n'>{$subs_val_txt}</td>
                        <TD class='c_n'>{$avg_pay_sum_txt}</td>
                        <TD class='c_n'>{$pay_perc_txt}</td>
                        <TD class='c_n'>{$e_val_txt}</td>
                        <TD class='c_i'>{$kur_cnt_3}</td>
                        <TD class='c_n'>{$sum_pay3m_txt}</td>
                        </tr> ";
    }
    
    
    
    $dt_val_txt = number_format_ua ($sum_summ_bill, 2);
    $kt_val_txt = number_format_ua ($sum_summ_pay, 2);
    $e_val_txt = number_format_ua ($sum_saldo, 2);
    $subs_val_txt = number_format_ua ($sum_summ_subs,2);
    $sum_pay3m_txt = number_format_ua ($sum_saldo_3, 2);
    $subs_val_txt = number_format_ua ($sum_summ_subs, 2);

    if ($sum_cnt_bill!=0)
        $avg_dem_sum = round($sum_summ_bill / $sum_cnt_bill, 2);
    else
        $avg_dem_sum =0;
    
    $avg_dem_sum_txt = number_format_ua ($avg_dem_sum, 2);

    if ($sum_cnt_pay!=0)
        $avg_pay_sum = round($sum_summ_pay / $sum_cnt_pay, 2);
    else
        $avg_pay_sum=0;
    
    $avg_pay_sum_txt = number_format_ua ($avg_pay_sum, 2);

    if ($sum_summ_bill!=0)
        $pay_perc = round($sum_summ_pay * 100 / $sum_summ_bill, 2);
    else
        $pay_perc = 0;
    
    $pay_perc_txt = number_format_ua ($pay_perc, 2);
    
    eval("\$footer_text_eval = \"$footer_text\";");
    echo $footer_text_eval;

    //return;
    
}
//----------------------------------------------------------------------------
if ($oper=='warning_build')
{
    $p_mmgg = sql_field_val('dt_b', 'mmgg');
    $mmgg1_str = trim($_POST['period_str']);
    $p_dt = sql_field_val('dt_rep', 'date');
    $p_value = sql_field_val('sum_value', 'numeric');
    $p_id_sector = sql_field_val('id_sector', 'int');
    $p_id_region = sql_field_val('id_region', 'int');
    $p_id_town = sql_field_val('addr_town', 'int');
    $p_book = sql_field_val('book', 'string');
    $p_id_paccnt = sql_field_val('id_paccnt', 'int');
    
    $SQL = "select warning_off_fun();";
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    
    
    $SQL = "select warning_create_all($p_mmgg,$p_dt,$p_value,$p_id_sector,$p_id_region,$p_id_town,$p_book, $session_user) as cnt;";

    $result = pg_query($Link,$SQL);

    if ($result) {
     
        $row = pg_fetch_array($result);
            
        $warning_cnt=$row['cnt'];
    }    
    
    $where ='';
    $params_caption='';
    if ($p_id_sector!='null')
    {
        $where= " and rp.id_sector = $p_id_sector ";
        $params_caption.= ' Дiльниця: '. trim($_POST['sector']);
    }

    if ($p_id_paccnt!='null')
    {
        if ($where!='where') $where.= " and ";        
        $where.= " acc.id = $p_id_paccnt ";
        
        $abon_name = trim($_POST['paccnt_name']);
        $params_caption .= " абонент : $abon_name ";
    }      
    else
    {
        if ($p_book!='null')
        {
            $where.= " and acc.book = $p_book ";
            $params_caption .= " книга : $p_book ";
        }       
    }
    
    if ($p_id_region!='null')
    {
        $where.= " and rs.id_region =  $p_id_region ";
        
        //if ($p_id_region==1) $params_caption=" Деснянський район";
        //if ($p_id_region==2) $params_caption=" Новозаводський район";
        
        $SQL_H="SELECT name from cli_region_tbl where id = $p_id_region ;";
    
        $result = pg_query($Link, $SQL_H) or die("SQL Error: " . pg_last_error($Link) . $SQL_H);
        $row = pg_fetch_array($result);
        
        $params_caption.= $row['name'];        
        
    }
    
    if ($p_id_town!='null')
    {
        $where.= "and adr.id_town = $p_id_town ";
    }
    
    
    if ((isset($_POST['addr_town_name']))&&($_POST['addr_town_name']!=''))
        $params_caption .= 'населений пункт: '. trim($_POST['addr_town_name']);
    
    
    eval("\$header_text = \"$header_text\";");
    
    echo $header_text;
    
    $SQL = " select * from (select s.*, acc.book, acc.code, 
 (adr.town||', '||adr.street||' '||
   (coalesce('буд.'||(acc.addr).house||'','')||
		coalesce('/'||(acc.addr).slash||' ','')||
			coalesce(' корп.'||(acc.addr).korp||'','')||
				coalesce(', кв. '||(acc.addr).flat,'')||
					coalesce('/'||(acc.addr).f_slash||' ',''))::varchar
)::varchar as addr,
(c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon,
 ('0'||substring(acc.code FROM '[0-9]+'))::int as int_code,
 ('0'||substring(acc.book FROM '[0-9]+'))::int as int_book  ,
 to_char(s.dt_create, 'DD.MM.YYYY') as dt_create_txt,
 to_char(s.dt_sum, 'DD.MM.YYYY') as dt_sum_txt
from clm_switching_tbl as s
join clm_paccnt_tbl as acc on (acc.id = s.id_paccnt)
join clm_abon_tbl as c on (c.id = acc.id_abon) 
join prs_runner_paccnt as rp on (rp.id_paccnt = acc.id)
join prs_runner_sectors as rs on (rs.id = rp.id_sector)
join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class)
where s.action=2 and s.dt_create = $p_dt $where
) as ss     
order by doc_num, int_book, int_code;";


   // throw new Exception(json_encode($SQL));
    $sum_cnt_all=0;
    $sum_saldo=0;
    
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        $i=0;
        while ($row = pg_fetch_array($result)) {

           
            $abon = htmlspecialchars($row['abon']);
            $addr = htmlspecialchars($row['addr']);
            $book=$row['book'].'/'.$row['code'];
            $sum_cnt_all++;
            $sum_saldo+=$row['sum_warning'];
            
            $sum_warning_txt = number_format_ua ($row['sum_warning'], 2);
            
            echo_html( "
            <tr >
            <td class='c_i'>{$row['doc_num']}</td>
            <td class='c_t'>{$book}</td>
            <td>{$abon}</td>
            <td>{$addr}</td>
            <td class='c_r'>{$row['dt_create_txt']}</td>
            <td class='c_n'>{$sum_warning_txt}</td>
            <td class='c_r'>{$row['dt_sum_txt']}</td>
            </tr>  ");        
            $i++;
        }
        
        
    }

    $sum_warning_txt = number_format_ua ($sum_saldo, 2);
    
    eval("\$footer_text_eval = \"$footer_text\";");
    echo $footer_text_eval;

    //return;
    
}
//----------------------------------------------------------------------------
if ($oper=='warning_print_old')
{
    $p_mmgg = sql_field_val('dt_b', 'mmgg');
    $mmgg1_str = sql_field_val('period_str','str');

    $p_id_town = sql_field_val('addr_town', 'int');
    $p_id_sector = sql_field_val('id_sector', 'int');
    $p_book = sql_field_val('book', 'string');
    $p_id_warning = sql_field_val('id_warning', 'int');
    $p_dt = sql_field_val('dt_rep', 'date');
    $p_id_region = sql_field_val('id_region', 'int');
    
    $where = ' ';

    if ($p_mmgg!='null')
    {
        $where.= "and s.mmgg = $p_mmgg ";
    }
    
    if ($p_id_town!='null')
    {
        $where.= "and adr.id_town = $p_id_town ";
    }
    
    if ($p_book!='null')
    {
        $where.= " and acc.book = $p_book ";
    }

    if ($p_id_sector!='null') {
        $where.= " and r.id_sector = $p_id_sector ";
    }
    
    if ($p_id_warning!='null')
    {
        $where.= " and s.id = $p_id_warning ";
    }

    if ($p_id_region!='null')
    {
        $where.= " and rs.id_region =  $p_id_region ";
        //if ($p_id_region==1) $params_caption=" Деснянський район";
        //if ($p_id_region==2) $params_caption=" Новозаводський район";
    }
    
    
    if ($p_dt!='null')
    {
        $where.= " and s.dt_create = $p_dt ";
    }
    
    //eval("\$header_text = \"$header_text\";");
    //echo $header_text;
    
    $SQL = "select warning_off_fun();";
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    
    
    $SQL = "select * from ( select s.*, acc.book, acc.code, 
    adr.town, adr.street, address_print(acc.addr) as house, acc.id_gtar, 
   (c.last_name||' '||coalesce(substr(c.name,1,1),'')||'.'||coalesce(substr(c.patron_name,1,1),''))||'.'::varchar as abon,
    
   coalesce(date_part('year', s.dt_action),0) as w_year_action , 
    
   coalesce(date_part('year', s.dt_create),0) as w_year , 
   date_part('month', s.dt_create) as w_month, 
   date_part('day', s.dt_create) as w_day,     

   date_part('year', s.dt_warning) as w_year_off , 
   date_part('month', s.dt_warning) as w_month_off, 
   date_part('day', s.dt_warning) as w_day_off,     
    
    to_char(s.dt_sum, 'DD.MM.YYYY') as dt_sum_str,

    date_part('year', s.mmgg_debet) as w_year_debet , 
    date_part('month', s.mmgg_debet) as w_month_debet, 

   ('0'||substring(acc.code FROM '[0-9]+'))::int as int_code,
   ('0'||substring(acc.book FROM '[0-9]+'))::int as int_book
from clm_switching_tbl as s
join clm_paccnt_tbl as acc on (acc.id = s.id_paccnt)
join clm_abon_tbl as c on (c.id = acc.id_abon) 
join prs_runner_paccnt as r on (r.id_paccnt = acc.id)            
join prs_runner_sectors as rs on (rs.id = r.id_sector)    
join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class)
where s.action=2 $where
) as ss     
order by int_book, int_code;";
    
   // echo $SQL;
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        $i=0;
        while ($row = pg_fetch_array($result)) {

            $dt_sum  ="'{$row['dt_sum']}'";
            $id_paccnt = $row['id_paccnt'];
            $id_gtar = $row['id_gtar'];
            
            $indic_str='';
            $tarif_str='';
            
            $SQL2 = " select  trim(sum(indic||','),',')::varchar as indic_str from 
             (
               select i.id_paccnt, i.id_zone, CASE WHEN z.id = 0 THEN (i.value::int)::varchar ELSE z.nm||':'||((i.value::int)::varchar) END::varchar as indic
               from acm_indication_tbl as i 
               join (select id_paccnt, max(dat_ind) as min_dat from acm_indication_tbl 
               where dat_ind <= $dt_sum and id_operation <> 5 and id_paccnt = $id_paccnt
               group by id_paccnt
             ) as mi
             on (i.id_paccnt = mi.id_paccnt and i.dat_ind = mi.min_dat)
             join eqk_zone_tbl as z on (z.id = i.id_zone)
             order by i.id_zone
             ) as ss ;";
            $result2 = pg_query($Link, $SQL2) or die("SQL Error: " . pg_last_error($Link) . $SQL);
            if ($result2) {
                $row2 = pg_fetch_array($result2);
                $indic_str=$row2['indic_str'];
            }

         
            $SQL2 = " 
             select trim(sum(  CASE WHEN coalesce(lim_min,0) <>0 THEN 'понад '||(lim_min::varchar)||
                CASE WHEN coalesce(lim_max,0) <>0 THEN '-' ELSE ' кВтг' END
                ELSE ''END ||
                CASE WHEN coalesce(lim_max,0) <>0 THEN (lim_max::varchar)||' кВтг' ELSE ''END 
                ||'-'||to_char(round(tar_val*100,2),'0,00')||' грн.,'),',- ')
                as tar_str

             from (
             select t.id,t.name,t.lim_min, t.lim_max,
	      (select value from aqd_tarif_tbl as d 
		where d.id_tarif = t.id 
		and dt_begin <=$dt_sum::date order by dt_begin desc limit 1) as tar_val 
	     from aqm_tarif_tbl as t 
             where ((dt_e is null ) or (dt_e > $dt_sum::date )) and (dt_b <= $dt_sum::date)
             and (per_min <= $dt_sum  and  per_max >= $dt_sum  or per_min is null )
             and id_grptar = $id_gtar
             order by ident  
               limit 3  ) as ss ; ";
            
            
            $result2 = pg_query($Link, $SQL2) or die("SQL Error: " . pg_last_error($Link) . $SQL);
            if ($result2) {
                $row2 = pg_fetch_array($result2);
                $tarif_str=$row2['tar_str'];
            }            
            
            
            $abon = htmlspecialchars($row['abon']);
            $addr = htmlspecialchars($row['town'].' '.$row['street'].' '.$row['house']);
            $book=$row['book'].'/'.$row['code'];

            if ($row['w_year']!=0)
            {
              $warning_month = ukr_month($row['w_month'],1);
              $dt_warning = $row['w_day'].' '.$warning_month.' '.$row['w_year'].' р.'; 
            }
            else
            {
                $dt_warning = '_____________';
            }

            if ($row['w_year_action']!=0)
            {
              $warning_off_month = ukr_month($row['w_month_off'],1);
              $dt_warning_off = $row['w_day_off'].' '.$warning_off_month.' '.$row['w_year_off'].' р.'; 
            }
            else
            {
                $dt_warning_off = '_____________';
            }
                
            if ($row['w_year_debet']!=0)
            {
              $warning_debet_month = ukr_month($row['w_month_debet'],0);
              $mmgg_warning_debet = 'Період виникнення заборгованості-'.$warning_debet_month.' '.$row['w_year_debet'].' р.'; 
            }
            else
            {
                $mmgg_warning_debet = '';
            }

            
            $year_warning = $row['w_year'];
            $dt_sum_str = $row['dt_sum_str'];
            $sum_warning_txt = number_format_ua ($row['sum_warning'], 2);
            $num_warning = $row['doc_num'];
            
            if ($row['demand_varning']!='')
               $demand_varning_txt = $row['demand_varning'].' кВтг';
            else
               $demand_varning_txt = '';
            
            
            eval("\$header_text_eval = \"$header_text\";");
            echo_html( $header_text_eval);

            if($res_code!='310')
            {
               eval("\$footer_text_eval = \"$footer_text\";");
               echo_html( $footer_text_eval);
            }
            
            $i++;
            
    
            if ($i>=4)
            {
                echo_html('<br style="page-break-after: always">' );
                $i = 0;
            }
            else
            {
                echo_html('<HR style="margin-bottom: 10px" SIZE="1" WIDTH="100%" NOSHADE> ');
            }
        }
        
    }

    //eval("\$footer_text_eval = \"$footer_text\";");
    //echo $footer_text_eval;

    //return;
   
}
//----------------------------------------------------------------------------
if ($oper=='warning_print')
{
    $p_mmgg = sql_field_val('dt_b', 'mmgg');
    $mmgg1_str = sql_field_val('period_str','str');

    $p_id_town = sql_field_val('addr_town', 'int');
    $p_id_sector = sql_field_val('id_sector', 'int');
    $p_book = sql_field_val('book', 'string');
    $p_id_warning = sql_field_val('id_warning', 'int');
    $p_dt = sql_field_val('dt_rep', 'date');
    $p_id_region = sql_field_val('id_region', 'int');
    $p_id_paccnt = sql_field_val('id_paccnt', 'int');
    
    $p_value = sql_field_val('sum_value', 'numeric');
    
    $where = ' ';
   
    if ($p_id_town!='null')
    {
        $where.= "and adr.id_town = $p_id_town ";
    }
    
    if ($p_id_paccnt!='null')
    {
        $where.= " and acc.id = $p_id_paccnt ";
    }    
    else
    {
        if ($p_book!='null')
        {
            $where.= " and acc.book = $p_book ";
        }
    }

    if ($p_id_sector!='null') {
        $where.= " and r.id_sector = $p_id_sector ";
    }
    
    if ($p_id_warning!='null')
    {
        $where.= " and s.id = $p_id_warning ";
    }


    if ($p_id_region!='null')
    {
        $where.= " and rs.id_region =  $p_id_region ";
        //if ($p_id_region==1) $params_caption=" Деснянський район";
        //if ($p_id_region==2) $params_caption=" Новозаводський район";
    }
    
    
    if ($p_dt!='null')
    {
        $where.= " and s.dt_create = $p_dt and not exists 
        ( select cswc.id from clm_switching_tbl as cswc where 
           s.id_paccnt = cswc.id_paccnt and s.dt_action <= cswc.dt_action and cswc.action in (3,4) ) ";
    }
    else
    {
        if ($p_mmgg!='null')
        {
            $where.= "and s.mmgg = $p_mmgg ";
        }
        
    }

    if ($p_value!='null')
    {
        $where.= " and s.sum_warning >= $p_value ";
    }
    
    //eval("\$header_text = \"$header_text\";");
    //echo $header_text;
    
    $SQL = "select warning_off_fun();";
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
  /*  
    $SQL = "select fun_mmgg() as mmgg_current ;";
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    $row = pg_fetch_array($result);
    $mmgg_current = "'{$row['mmgg_current']}'";
  */  
    
    $SQL = "select * from ( select s.*, acc.book, acc.code, 
    adr.town, adr.street, address_print(acc.addr) as house, acc.id_gtar, 
   (c.last_name||' '||coalesce(substr(c.name,1,1),'')||'.'||coalesce(substr(c.patron_name,1,1),''))||'.'::varchar as abon,
   coalesce(s.mmgg_debet,s.mmgg) as  mmgg_debetc,
   coalesce(date_part('year', s.dt_action),0) as w_year_action , 
    
   coalesce(date_part('year', s.dt_create),0) as w_year , 
   date_part('month', s.dt_create) as w_month, 
   date_part('day', s.dt_create) as w_day,     

   date_part('year', s.dt_warning) as w_year_off , 
   date_part('month', s.dt_warning) as w_month_off, 
   date_part('day', s.dt_warning) as w_day_off,     
    
    to_char(s.dt_sum, 'DD.MM.YYYY') as dt_sum_str,

    date_part('year', coalesce(s.mmgg_debet,s.mmgg)) as w_year_debet , 
    date_part('month', coalesce(s.mmgg_debet,s.mmgg)) as w_month_debet, 
    to_char(coalesce(s.mmgg_debet,s.mmgg), 'DD.MM.YYYY') as mmgg_debet_str,
    to_char(fun_mmgg(), 'DD.MM.YYYY') as mmgg_str,
    saldo_current,
   ('0'||substring(acc.code FROM '[0-9]+'))::int as int_code,
   ('0'||substring(acc.book FROM '[0-9]+'))::int as int_book
from clm_switching_tbl as s
join clm_paccnt_tbl as acc on (acc.id = s.id_paccnt)
join clm_abon_tbl as c on (c.id = acc.id_abon) 
join (  select id_paccnt, sum(e_dtval-e_ktval) as saldo_current 
        from seb_saldo where mmgg = ($mmgg_current::date-'1 month'::interval)::date
        group by id_paccnt order by id_paccnt
        ) as sal on (sal.id_paccnt = acc.id)    
join prs_runner_paccnt as r on (r.id_paccnt = acc.id)            
join prs_runner_sectors as rs on (rs.id = r.id_sector)    
join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class)
where s.action=2 $where
) as ss     
order by int_book, int_code;";
    
   // echo $SQL;
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        $i=0;
        while ($row = pg_fetch_array($result)) {

            $dt_sum  ="'{$row['dt_sum']}'";
            $id_paccnt = $row['id_paccnt'];
            $id_gtar = $row['id_gtar'];
            $mmgg_debet  ="'{$row['mmgg_debetc']}'";
            $mmgg_debet_str = $row['mmgg_debet_str'];
            $mmgg_str   = $row['mmgg_str'];
            $saldo_current = $row['saldo_current'];
            $sum_current_txt = number_format_ua ($saldo_current, 2);
            
            $indic_str='';
            $bill_str='';
            
            $SQL2 = " select  trim(sum(indic||','),',')::varchar as indic_str from 
             (
               select i.id_paccnt, i.id_zone, CASE WHEN z.id = 0 THEN (i.value::int)::varchar ELSE z.nm||':'||((i.value::int)::varchar) END::varchar as indic
               from acm_indication_tbl as i 
               join (select id_paccnt, max(dat_ind) as min_dat from acm_indication_tbl 
               where dat_ind < fun_mmgg() and id_operation <> 5 and id_paccnt = $id_paccnt
               group by id_paccnt
             ) as mi
             on (i.id_paccnt = mi.id_paccnt and i.dat_ind = mi.min_dat)
             join eqk_zone_tbl as z on (z.id = i.id_zone)
             order by i.id_zone
             ) as ss ;";
            $result2 = pg_query($Link, $SQL2) or die("SQL Error: " . pg_last_error($Link) . $SQL);
            if ($result2) {
                $row2 = pg_fetch_array($result2);
                $indic_str = $row2['indic_str'];
            }


            $SQL3 = "  select bs.*, bt.grptar ,to_char(bs.mmgg_bill, 'DD.MM.YYYY') as mmgg_txt,
                (select trim(sum(  CASE WHEN coalesce(lim_min,0) <>0 THEN 'понад '||(lim_min::varchar)||
                    CASE WHEN coalesce(lim_max,0) <>0 THEN '-' ELSE ' кВтг' END
                    ELSE ''END ||
                    CASE WHEN coalesce(lim_max,0) <>0 THEN (lim_max::varchar)||' кВтг' ELSE ''END 
                    ||'-'||to_char(tar_val,'0.0000')||' грн.,'),',- ')
                    as tar_str
                from (
                    select t.id,t.name,t.lim_min, t.lim_max,
                    (select value from aqd_tarif_tbl as d 
                            where d.id_tarif = t.id 
                            and dt_begin <=bs.mmgg_bill order by dt_begin desc limit 1) as tar_val 
                    from aqm_tarif_tbl as t 
                    where ((dt_e is null ) or (dt_e > bs.mmgg_bill::date )) and (dt_b <= bs.mmgg_bill::date)
                    and (per_min <= bs.mmgg_bill  and  per_max >= bs.mmgg_bill  or per_min is null )
                    and id_grptar = bt.grptar
                    order by ident  
                    limit 3
                ) as ss  ) as tar_str 
                    
               from 
            (
            select b.id_paccnt, b.mmgg_bill, sum(b.demand) as demand, sum(b.value) as summ
                    from acm_bill_tbl as b 
                    where b.id_pref = 10
                    and ( b.mmgg >= $mmgg_debet or b.idk_doc = 1000)
                    and b.mmgg_bill >= $mmgg_debet 
                    and b.mmgg < $mmgg_current
                    and id_paccnt = $id_paccnt
                        group by  b.id_paccnt, b.mmgg_bill
            order by  b.mmgg_bill
            ) as bs
            join 
            (
                    select b.mmgg_bill, max(t.id_grptar) as grptar
                    from acm_bill_tbl as b 
                    join (select max(mmgg) as max_mmggm , mmgg_bill from acm_bill_tbl where id_pref = 10
                     --   and mmgg >= $mmgg_debet
                        and mmgg_bill >= $mmgg_debet   
                      --  and mmgg <= $mmgg_current
                        and id_paccnt = $id_paccnt group by mmgg_bill) as bb on ( b.mmgg = bb.max_mmggm and b.mmgg_bill = bb.mmgg_bill)
                    join acm_summ_tbl as bs on (b.id_doc= bs.id_doc) 
                    join aqm_tarif_tbl as t on (t.id = bs.id_tarif)
                    where  b.id_pref = 10
                  --  and b.mmgg >= $mmgg_debet
                    and b.mmgg_bill >= $mmgg_debet 
                 --   and b.mmgg <= $mmgg_current
                    and b.id_paccnt = $id_paccnt
                    group by b.mmgg_bill
            ) as bt on (bs.mmgg_bill = bt.mmgg_bill) ";
            //  echo $SQL3;
            $result3 = pg_query($Link, $SQL3) or die("SQL Error: " . pg_last_error($Link) . $SQL);
            if ($result3) {

                $rows_count3 = pg_num_rows($result3);

                $i = 0;
                while ($row3 = pg_fetch_array($result3)) {
                    
                  $bill_str.= $row3['mmgg_txt']." р. - ";
                  if ($row3['demand']<>0)
                  {
                      $bill_str.= $row3['demand']."кВтг ";
                  }
                  $bill_str.= 'на суму '.$row3['summ']."грн. ";
                  $bill_str.= '(по тарифу '.$row3['tar_str'].") <br/> ";
                }
            }


            $abon = htmlspecialchars($row['abon']);
            $addr = htmlspecialchars($row['town'].' '.$row['street'].' '.$row['house']);
            $book=$row['book'].'/'.$row['code'];

            if ($row['w_year']!=0)
            {
              $warning_month = ukr_month($row['w_month'],1);
              $dt_warning = $row['w_day'].' '.$warning_month.' '.$row['w_year'].' р.'; 
            }
            else
            {
                $dt_warning = '_____________';
            }

            if ($row['w_year_action']!=0)
            {
              $warning_off_month = ukr_month($row['w_month_off'],1);
              $dt_warning_off = $row['w_day_off'].' '.$warning_off_month.' '.$row['w_year_off'].' р.'; 
            }
            else
            {
                $dt_warning_off = '_____________';
            }
            /*    
            if ($row['w_year_debet']!=0)
            {
              $warning_debet_month = ukr_month($row['w_month_debet'],0);
              $mmgg_warning_debet = 'Період виникнення заборгованості-'.$warning_debet_month.' '.$row['w_year_debet'].' р.'; 
            }
            else
            {
                $mmgg_warning_debet = '';
            }
            */
            
            $year_warning = $row['w_year'];
            $dt_sum_str = $row['dt_sum_str'];
            $sum_warning_txt = number_format_ua ($row['sum_warning'], 2);
            $num_warning = $row['doc_num'];
            
            /*
            if ($row['demand_varning']!='')
               $demand_varning_txt = $row['demand_varning'].' кВтг';
            else
               $demand_varning_txt = '';
            */
            
            eval("\$header_text_eval = \"$header_text\";");
            echo_html( $header_text_eval);

            if($res_code!='310')
            {
               eval("\$footer_text_eval = \"$footer_text\";");
               echo_html( $footer_text_eval);
            }
            
            $i++;
            
    
            if ($i>=3)
            {
                echo_html('<br style="page-break-after: always">' );
                $i = 0;
            }
            else
            {
                echo_html('<HR style="margin-bottom: 10px" SIZE="1" WIDTH="100%" NOSHADE> ');
            }
        }
        
    }

    //eval("\$footer_text_eval = \"$footer_text\";");
    //echo $footer_text_eval;

    //return;
   
}

//-----------------------------------------------------------------------------
if ($oper=='warning_list')
{
    $p_mmgg = sql_field_val('dt_b', 'mmgg');
    $mmgg1_str = trim($_POST['period_str']);
    
    $p_id_town = sql_field_val('addr_town', 'int');
    $p_id_sector = sql_field_val('id_sector', 'int');
    $p_book = sql_field_val('book', 'string');
    $p_dt = sql_field_val('dt_rep', 'date');    
    $p_id_region = sql_field_val('id_region', 'int');
    $p_value = sql_field_val('sum_value', 'numeric');
    
    $print_flag=1;
    
    if ((isset($_POST['addr_town_name']))&&($_POST['addr_town_name']!=''))
        $params_caption = 'населений пункт: '. trim($_POST['addr_town_name']);
    else 
        $params_caption ='';
    
    if ((isset($_POST['sector']))&&($_POST['sector']!=''))
        $params_caption.= ' Дільниця: '. trim($_POST['sector']);
    
    $where = ' ';

    if ($p_id_town!='null')
    {
        $where.= "and adr.id_town = $p_id_town ";
    }
    
    if ($p_book!='null')
    {
        $where.= " and acc.book = $p_book ";
        
        $params_caption.= 'книга: '.$p_book;
    }
    
    if ($p_id_sector!='null') {
        $where.= " and r.id_sector = $p_id_sector ";
    }
    
    if ($p_id_region!='null')
    {
        $where.= " and rs.id_region =  $p_id_region ";
         
        //if ($p_id_region==1) $params_caption=" Деснянський район";
        //if ($p_id_region==2) $params_caption=" Новозаводський район";
        $SQL_H="SELECT name from cli_region_tbl where id = $p_id_region ;";
    
        $result = pg_query($Link, $SQL_H) or die("SQL Error: " . pg_last_error($Link) . $SQL_H);
        $row = pg_fetch_array($result);
        
        $params_caption.= $row['name'];        

    }
    
    
    if ($p_dt!='null')
    {
        $where.= " and s.dt_create = $p_dt ";
    }
    
    if ($p_value!='null')
    {
        $where.= " and s.sum_warning >= $p_value ";
        $params_caption.= " сума більше  $p_value грн.";
    }    
    
    eval("\$header_text_eval = \"$header_text\";");
    echo $header_text_eval;
    
    $SQL = " select * from (select s.*, acc.book, acc.code, 
    adr.town, adr.street, address_print(acc.addr) as house,
    (c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon,
    ('0'||substring(acc.code FROM '[0-9]+'))::int as int_code,
    ('0'||substring(acc.book FROM '[0-9]+'))::int as int_book,
    to_char(s.dt_create, 'DD.MM.YYYY') as dt_create_txt,
    to_char(s.dt_sum, 'DD.MM.YYYY') as dt_sum_txt
from clm_switching_tbl as s
join clm_paccnt_tbl as acc on (acc.id = s.id_paccnt)
join clm_abon_tbl as c on (c.id = acc.id_abon) 
join prs_runner_paccnt as r on (r.id_paccnt = acc.id)        
join prs_runner_sectors as rs on (rs.id = r.id_sector)        
join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class)
where s.action=2 and s.mmgg = $p_mmgg $where
) as sss    
order by int_book, book,  int_code, code;";

   //echo $SQL; 
   // throw new Exception(json_encode($SQL));
    $sum_cnt_all=0;
    $sum_saldo=0;
    
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        $i=0;
        $np=0;        
        while ($row = pg_fetch_array($result)) {

            $np++;
            if ((($np> ($page_num-1)* $page_size )&& ($np<= $page_num* $page_size )) || ($show_pager ==0))
                $print_flag = 1;
            else
                $print_flag = 0;
           
            $abon = htmlspecialchars($row['abon']);
            //$addr = htmlspecialchars($row['town'].' '.$row['street'].' '.$row['house']);
            
            if($res_code!='310')
              $addr = htmlspecialchars($row['town'].' '.$row['street'].' '.$row['house']);
            else
              $addr = htmlspecialchars($row['street'].' '.$row['house']);
            
            
            $abon = str_replace('і','i',$abon);
            $abon = str_replace('І','I',$abon);

            $addr = str_replace('і','i',$addr);
            $addr = str_replace('І','I',$addr);
            
            $book=$row['book'].'/'.$row['code'];
            $sum_cnt_all++;
            $sum_saldo+=$row['sum_warning'];
            $num_warning = $row['doc_num'];
            
            $sum_warning_txt = number_format_ua ($row['sum_warning'], 2);
            
            echo_html( "
            <tr >
            <td>&nbsp;&nbsp;&nbsp;</td>
            <td class='c_t'>{$book}</td>
            <td>{$abon}</td>
            <td>{$addr}</td>
            <td class='c_r'>{$row['dt_create_txt']}</td>
            <td class='c_n'>{$sum_warning_txt}</td>
            <td class='c_r'>{$row['dt_sum_txt']}</td>
            <TD >&nbsp;</td>
            <TD >&nbsp;</td>
            </tr>  ",$print_flag);        
            $i++;
        }
        
        
    }

    $sum_warning_txt = number_format_ua ($sum_saldo, 2);
    
    eval("\$footer_text_eval = \"$footer_text\";");
    echo_html( $footer_text_eval,$print_flag);

    echo '</tbody> </table> ';
    //return;
    
}
//----------------------------------------------------------------------------
if ($oper=='indic_summary')
{
    $p_mmgg = sql_field_val('dt_b', 'mmgg');
    $mmgg1_str = trim($_POST['period_str']);
    $p_id_region = sql_field_val('id_region', 'int');
    $p_id_sector = sql_field_val('id_sector', 'int');
    $p_book = sql_field_val('book', 'string');
    
    $params_caption ='';
    $where = ' ';    
    
    if ($p_book!='null')
    {
        $where.= " and c.book = $p_book ";
        
        $params_caption.= 'книга: '.$p_book;
    }
    
    
    /*
    $p_id_town = sql_field_val('addr_town', 'int');
    $p_book = sql_field_val('book', 'string');
    
    if ((isset($_POST['addr_town_name']))&&($_POST['addr_town_name']!=''))
        $params_caption = 'населений пункт: '. trim($_POST['addr_town_name']);
    else 
        $params_caption ='';
    
    $where = ' ';

    if ($p_id_town!='null')
    {
        $where.= "and adr.id_town = $p_id_town ";
    }
    
    if ($p_book!='null')
    {
        $where.= " and acc.book = $p_book ";
        
        $params_caption.= 'книга: '.$p_book;
    }
*/    

    if ($p_id_region!='null')
    {
        
        $where.= " and exists (select rs.id from 
            prs_runner_paccnt as rp 
            join prs_runner_sectors as rs on (rs.id = rp.id_sector)
            where rp.id_paccnt = i.id_paccnt and rs.id_region =  $p_id_region ) ";

        $SQL_H="SELECT name from cli_region_tbl where id = $p_id_region ;";
    
        $result = pg_query($Link, $SQL_H) or die("SQL Error: " . pg_last_error($Link) . $SQL_H);
        $row = pg_fetch_array($result);
        
        $params_caption.= $row['name'];               
    }    
    

    if ((isset($_POST['sector']))&&($_POST['sector']!=''))
        $params_caption.= ' Дiльниця: '. trim($_POST['sector']);
    

    if ($p_id_sector!='null')
    {
        if ($where!='where') $where.= " and ";        
        $where.= " exists (select id from prs_runner_paccnt as rp 
            where rp.id_paccnt = c.id and rp.id_sector = $p_id_sector ) ";
        
    }    
    
    
    $caption1 = 'Оператор';
    eval("\$header_text_eval = \"$header_text\";");
    echo $header_text_eval;
    /*
    $SQL = " select id_runner,COALESCE(sector,'--відсутній')as sector,represent_name,
count(distinct id) as cnt_all,
count(distinct CASE WHEN id_operation=1 THEN id END) as cnt_list,
to_char(min(dt_indic), 'DD.MM.YYYY') as min_dt,   
to_char(max(dt_indic), 'DD.MM.YYYY') as max_dt   
from (
select i.id, i.id_ind,id_operation, i.id_paccnt, i.value, coalesce(ind1.id_runner,st2.id_runner) as id_runner,ind1.dt_indic,
coalesce(ind1.name,st2.name) as name,coalesce(ind1.sector,st2.sector) as sector,
coalesce(ind1.represent_name,st2.represent_name) as represent_name
from acm_indication_tbl as i 
left join 
(
        select p.id, p.id_paccnt,st.id_runner,st.name,  p.dt_indic,
	CASE WHEN pr.represent_name is not null THEN pr.represent_name ELSE '--'||st.name END::varchar as sector,
	coalesce(pr.represent_name,'111') as represent_name
        from ind_pack_data as p 
	join ind_pack_header as h on (h.id_pack = p.id_pack)
	join 
	(	select rs.id, rs.id_runner,rs.name 
			from  prs_runner_sectors_h as rs 
			join (select id, max(dt_b) as dt_b from prs_runner_sectors_h  where dt_b <= $p_mmgg and coalesce(dt_e,$p_mmgg) >=$p_mmgg
			group by id order by id) as rs2 
			on (rs.id = rs2.id and rs.dt_b = rs2.dt_b)
	) as st on (st.id = h.id_sector)
	left join  prs_persons as pr on (pr.id = coalesce(h.id_runner,st.id_runner))
	where h.work_period = $p_mmgg
) as ind1 on (ind1.id = i.id_ind )
left join
(
select rs.id, rs.id_runner,rs.name,rp.id_paccnt,coalesce(p.represent_name,'111') as represent_name,
		CASE WHEN p.represent_name is not null THEN p.represent_name ELSE '--'||rs.name END::varchar as sector
		from  prs_runner_sectors_h as rs 
		join (select id, max(dt_b) as dt_b from prs_runner_sectors_h  where dt_b <= $p_mmgg and coalesce(dt_e,$p_mmgg) >=$p_mmgg
		group by id order by id) as rs2 
		on (rs.id = rs2.id and rs.dt_b = rs2.dt_b)

		join prs_runner_paccnt_h as rp on (rp.id_sector = rs.id)
		join (select id, max(dt_b) as dt_b from prs_runner_paccnt_h  where dt_b <= $p_mmgg and coalesce(dt_e,$p_mmgg) >=$p_mmgg
		group by id order by id) as rp2 
		on (rp.id = rp2.id and rp.dt_b = rp2.dt_b)
		left join prs_persons as p on (p.id = rs.id_runner)
		order by represent_name,sector
) as st2 on (st2.id_paccnt = i.id_paccnt)
where i.mmgg = $p_mmgg and i.value is not null 
)as ss
group by id_runner,sector,represent_name
order by represent_name,sector";
*/
    
$SQL="select u1.name as user_name,
coalesce(count(distinct i.id ),0) as cnt_all,
coalesce(count(distinct CASE WHEN coalesce(i.id_operation,0) in (1,24) THEN i.id END),0) as cnt_list
from acm_indication_tbl as i
 join clm_paccnt_tbl as c on (c.id = i.id_paccnt)   
 left join syi_user as u1 on (u1.id = i.id_person)
 left join cli_indic_type_tbl as it on (it.id = i.id_operation)
where i.mmgg = $p_mmgg and i.dat_ind >= $p_mmgg
$where    
group by u1.name
order by u1.name ";
   // throw new Exception(json_encode($SQL));
    $sum_cnt_all=0;
    $sum_cnt_list=0;
    
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        $i=0;
        while ($row = pg_fetch_array($result)) {

            $sector = htmlspecialchars($row['user_name']);
            //$period=$row['min_dt'].' - '.$row['max_dt'];
            $cnt_all=number_format_ua ($row['cnt_all'],0);
            $cnt_list=number_format_ua ($row['cnt_list'],0);
            
            
            $sum_cnt_all+=$row['cnt_all'];
            $sum_cnt_list+=$row['cnt_list'];

            echo "
            <tr >
            <td >{$sector}</td>
            <td class='c_i'>{$cnt_all}</td>
            <td class='c_i'>{$cnt_list}</td>
            </tr>  ";        
            $i++;
        }
        
        
    }
/*
    $cnt_i1=0;
    $sum_i1=0;
    $cnt_i2=0;
    $sum_i2=0;
    $cnt_i3=0;
    $sum_i3=0;
    $cnt_i4=0;
    $sum_i4=0;
    $cnt_i5=0;
    $sum_i5=0;
    $cnt_i6=0;
    $sum_i6=0;
  
    $SQL = "select count (distinct CASE WHEN t.ident ~ 'iother1' THEN i.id END ) as cnt_i1,
 count (distinct CASE WHEN t.ident ~ 'iother2' THEN i.id END ) as cnt_i2,
 count (distinct CASE WHEN t.ident ~ 'iother3' THEN i.id END ) as cnt_i3,
 count (distinct CASE WHEN t.ident ~ 'iother4' THEN i.id END ) as cnt_i4,
 count (distinct CASE WHEN t.ident ~ 'iother5' THEN i.id END ) as cnt_i5,
 count (distinct CASE WHEN t.ident ~ 'iother6' THEN i.id END ) as cnt_i6,
 coalesce(sum ( CASE WHEN t.ident ~ 'iother1' THEN value_cons END ),0) as sum_i1,
 coalesce(sum ( CASE WHEN t.ident ~ 'iother2' THEN value_cons END ),0) as sum_i2,
 coalesce(sum ( CASE WHEN t.ident ~ 'iother3' THEN value_cons END ),0) as sum_i3,
 coalesce(sum ( CASE WHEN t.ident ~ 'iother4' THEN value_cons END ),0) as sum_i4,
 coalesce(sum ( CASE WHEN t.ident ~ 'iother5' THEN value_cons END ),0) as sum_i5,
 coalesce(sum ( CASE WHEN t.ident ~ 'iother6' THEN value_cons END ),0) as sum_i6
from 
acm_indication_tbl as i 
join cli_indic_type_tbl as t on (t.id = i.id_operation)
where i.mmgg = $p_mmgg and t.ident <>'ikur';";
*/
    
    $SQL = "select t.id ,t.ident, coalesce(t.name,'-не вказано' ) as name,
    count (distinct i.id ) as cnt,
    coalesce(sum(value_cons),0) as sum_dem
from 
acm_indication_tbl as i 
join clm_paccnt_tbl as c on (c.id = i.id_paccnt)   
left join cli_indic_type_tbl as t on (t.id = i.id_operation)
where i.mmgg = $p_mmgg and i.dat_ind >= $p_mmgg
$where    
group by t.id ,t.ident, t.name 
order by t.ident;";
    
    $summary_txt ='';
    $demand_all = 0;
    $result = pg_query($Link, $SQL);
    if ($result) {
      while ($row = pg_fetch_array($result)) {

//        $row = pg_fetch_array($result);

        $cnt=$row['cnt'];
        $sum=number_format_ua ($row['sum_dem'],0);
        $name = htmlspecialchars($row['name']);
        
        $demand_all += $row['sum_dem'];

        $summary_txt.="    <tr>
        <td>&nbsp;&nbsp;$name</td>
        <td class='c_r'>{$cnt}</td> 
        <td class='c_r'>{$sum} кВтг</td>
        </tr>";
      }
    }

    // Общее количество абонентов, у которых реально снимали показания
    $SQL = "select count(distinct id_paccnt) as cnt_all
    from acm_indication_tbl 
    where mmgg = $p_mmgg
    and dat_ind >=$p_mmgg
    and id_operation not in (23,14);";
    
    $result = pg_query($Link, $SQL);
    $abon_cnt_txt='';
    
    if ($result) {
      while ($row = pg_fetch_array($result)) {

        $abon_cnt_txt=number_format_ua ($row['cnt_all'],0);
      }
    }
    
    
    //---------------- операторы -------------------
    $operators='';
    $oper_cnt=0;
    /*
    $SQL = "select represent_name
            from prs_persons as pr 
            join prs_posts as p on (p.id = pr.id_post) 
            where p.ident = 'oper' ;";

    $result = pg_query($Link, $SQL);
    if ($result) {
        while ($row = pg_fetch_array($result)) {

            //if ($oper_cnt!=0)
            //{
                $operators.='<br/>';
            //}
            $operators.=$row['represent_name'];
            $oper_cnt++;
        }
    }
    */
    
    $demand_all_txt=number_format_ua ($demand_all,0);
    
    
    
    eval("\$footer_text_eval = \"$footer_text\";");
    echo $footer_text_eval;

    //return;
    
}
//----------------------------------------------------------------------------
if (($oper=='indic_summary_insp')||($oper=='indic_summary_insp_pack'))
{
    $p_mmgg = sql_field_val('dt_b', 'mmgg');
    $mmgg1_str = trim($_POST['period_str']);
    $p_id_region = sql_field_val('id_region', 'int');
    
    if($res_code!='310')
      $caption1 = 'Кур`єр';
    else
      $caption1 = 'Контролер';    
        
    $params_caption ='';
    $where = ' ';    

    if ($p_id_region!='null')
    {
        
        $where.= "and exists (select rs.id from 
            prs_runner_paccnt as rp 
            join prs_runner_sectors as rs on (rs.id = rp.id_sector)
            where rp.id_paccnt = i.id_paccnt and rs.id_region =  $p_id_region ) ";

        $SQL_H="SELECT name from cli_region_tbl where id = $p_id_region ;";
    
        $result = pg_query($Link, $SQL_H) or die("SQL Error: " . pg_last_error($Link) . $SQL_H);
        $row = pg_fetch_array($result);
        
        $params_caption.= $row['name'];               
    }    
    
    
    eval("\$header_text_eval = \"$header_text\";");
    echo $header_text_eval;
    
$SQL="select
coalesce(count(distinct i.id ),0) as cnt_all,
coalesce(count(distinct CASE WHEN coalesce(i.id_operation,0) in (1,24) THEN i.id END),0) as cnt_list,
coalesce(ind1.represent_name,st2.represent_name) as represent_name  ,";

if ($oper=='indic_summary_insp_pack')
{
    $SQL.='ind1.num_pack  ,';
}
else
{
    $SQL.=" '' as num_pack  ,";
}

$SQL.="to_char(min(CASE WHEN coalesce(i.id_operation,0) in (1,24) THEN i.dat_ind END ), 'DD.MM.YYYY') as min_date,    
to_char(max(CASE WHEN coalesce(i.id_operation,0) in (1,24) THEN i.dat_ind END), 'DD.MM.YYYY') as max_date
from acm_indication_tbl as i
join
(
     select rp.id_paccnt, 
      coalesce(p.represent_name,'- невідомий') as represent_name
     from prs_runner_sectors as rs 
     join prs_runner_paccnt as rp on (rp.id_sector = rs.id)
     left join prs_persons as p on (p.id = rs.id_runner)
     order by rp.id_paccnt
) as st2 on (st2.id_paccnt = i.id_paccnt)

left join 
(
        select p.id, h.num_pack,
	coalesce(pr.represent_name,'- невідомий') as represent_name
        from ind_pack_data as p 
	join ind_pack_header as h on (h.id_pack = p.id_pack)
	left join prs_persons as pr on (pr.id = h.id_runner)
	where h.work_period = $p_mmgg
        order by p.id
) as ind1 on (ind1.id = i.id_ind )
left join cli_indic_type_tbl as it on (it.id = i.id_operation)
where i.mmgg = $p_mmgg and i.dat_ind >= $p_mmgg $where ";

if ($oper=='indic_summary_insp_pack')
{
    $SQL.="
    group by coalesce(ind1.represent_name,st2.represent_name), ind1.num_pack 
    order by coalesce(ind1.represent_name,st2.represent_name), ind1.num_pack  ;";
}
else
{
    $SQL.="
    group by coalesce(ind1.represent_name,st2.represent_name)
    order by coalesce(ind1.represent_name,st2.represent_name) ;";
}

   // throw new Exception(json_encode($SQL));
    $sum_cnt_all=0;
    $sum_cnt_list=0;
    
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        $i=0;
        while ($row = pg_fetch_array($result)) {

            $sector = htmlspecialchars($row['represent_name']);
            $pack = htmlspecialchars($row['num_pack']);
            //$period=$row['min_dt'].' - '.$row['max_dt'];
            $cnt_all=number_format_ua ($row['cnt_all'],0);
            $cnt_list=number_format_ua ($row['cnt_list'],0);
            
            $dates = $row['min_date'].'-'.$row['max_date'];
            
            $sum_cnt_all+=$row['cnt_all'];
            $sum_cnt_list+=$row['cnt_list'];

            echo "
            <tr >
            <td class='c_t'>{$sector}</td>
            <td class='c_t'>{$pack}</td>
            <td class='c_i'>{$cnt_all}</td>
            <td class='c_i'>{$cnt_list}</td>
            <td class='c_t'>{$dates}</td>
            </tr>  ";        
            $i++;
        }
        
        
    }
    
    $SQL = "select t.id ,t.ident, coalesce(t.name,'-не вказано' ) as name,
    count (distinct i.id ) as cnt,
    coalesce(sum(value_cons),0) as sum_dem
from 
acm_indication_tbl as i 
left join cli_indic_type_tbl as t on (t.id = i.id_operation)
where i.mmgg = $p_mmgg and i.dat_ind >= $p_mmgg
$where    
group by t.id ,t.ident, t.name 
order by t.ident;";
    
    $summary_txt ='';
    $demand_all = 0;
    $result = pg_query($Link, $SQL);
    if ($result) {
      while ($row = pg_fetch_array($result)) {

        $cnt=$row['cnt'];
        $sum=number_format_ua ($row['sum_dem'],0);
        $name = htmlspecialchars($row['name']);
        
        $demand_all += $row['sum_dem'];

        $summary_txt.="    <tr>
        <td>&nbsp;&nbsp;$name</td>
        <td class='c_r'>{$cnt}</td> 
        <td class='c_r'>{$sum} кВтг</td>
        </tr>";
      }
    }
    
    // Общее количество абонентов, у которых реально снимали показания
    $SQL = "select count(distinct id_paccnt) as cnt_all
    from acm_indication_tbl 
    where mmgg = $p_mmgg
    and dat_ind >=$p_mmgg
    and id_operation not in (23,14);";
    
    $result = pg_query($Link, $SQL);
    $abon_cnt_txt='';
    
    if ($result) {
      while ($row = pg_fetch_array($result)) {

        $abon_cnt_txt=number_format_ua ($row['cnt_all'],0);
      }
    }

    //---------------- операторы -------------------
    $operators='';
    $oper_cnt=0;
    
    $demand_all_txt=number_format_ua ($demand_all,0);
    
    eval("\$footer_text_eval = \"$footer_text\";");
    echo $footer_text_eval;
    
}



if ($oper=='indic_summary_insp_period_new')
{
    
    $p_mmgg = trim(sql_field_val('dt_b', 'mmgg'));
    $mmgg1_str = trim($_POST['period_str']);
    $year_n = substr($p_mmgg,1,4);
    $month_n = substr($p_mmgg,6,2);
    $param_date = "'".$year_n.'-'.$month_n.'-01'."'";
    
    $d_begin = "'".$pp_dt_b."'";
    $d_end   = "'".$pp_dt_e."'";
    $month_n=substr($pp_dt_e,3,2);
    $yearn_n = substr($pp_dt_e,6,4);        
    $param_date = "'".$year_n.'-'.$month_n.'-01'."'";
            
    $p_id_region = sql_field_val('id_region', 'int');
    
    if($res_code!='310')
      $caption1 = 'Кур`єр';
    else
      $caption1 = 'Контролер';    
        
    $params_caption ='';
    $where = ' ';    

   
$SQL="SELECT * from job_controler_period(".$param_date.','.$d_begin.','.$d_end.')';
//echo $SQL;

   // throw new Exception(json_encode($SQL));
    $sum_cnt_all=0;
    $sum_cnt_list=0;
    
    echo '</br>';
    echo $mmgg1_str;
    echo '<table cellpadding="0" cellspacing="0" border=1>';
    echo '<th>Кур’єр</th>';
    echo '<th>Кільк.(прим.)</th>';
    echo '<th>Кільк.(С.К.)</th>';
    echo '<th>Кільк.(винос. конт.)</th>';
    echo '<th>Кільк.(ВБШ)</th>';
    echo '<th>Кільк.(У кв.)</th>';
    echo '<th>Кільк.(буд.)</th>';
     
    echo '<th>Вартicть(прим.)</th>';
    echo '<th>Вартicть(С.К.)</th>';
    echo '<th>Вартicть(винос. конт.)</th>';
    echo '<th>Вартicть(ВБШ)</th>';
    echo '<th>Вартicть(У кв.)</th>';
    echo '<th>Вартicть(буд.)</th>';
    echo '<th>Вартicть усього</th>';
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        $i=0;
        while ($row = pg_fetch_array($result)) {

            $runner = htmlspecialchars($row['runner']);
            //echo $runner;
            //return;
            
            $place1 = htmlspecialchars($row['place1']);
            if($place1==0) $place1='';
            $place2 = htmlspecialchars($row['place2']);
            if($place2==0) $place2='';
            $place3 = htmlspecialchars($row['place3']);
            if($place3==0) $place3='';
            $place4 = htmlspecialchars($row['place4']);
            if($place4==0) $place4='';
            $place5 = htmlspecialchars($row['place5']);
            if($place5==0) $place5='';
            $place6 = htmlspecialchars($row['place6']);
            if($place6==0) $place6='';
            
            $cost1 = htmlspecialchars($row['cost1']);
            if($cost1==0) $cost1='';
            $cost2 = htmlspecialchars($row['cost2']);
            if($cost2==0) $cost2='';
            $cost3 = htmlspecialchars($row['cost3']);
            if($cost3==0) $cost3='';
            $cost4 = htmlspecialchars($row['cost4']);
            if($cost4==0) $cost4='';
            $cost5 = htmlspecialchars($row['cost5']);
            if($cost5==0) $cost5='';
            $cost6 = htmlspecialchars($row['cost6']);
            if($cost6==0) $cost6='';
            $cost_all = htmlspecialchars($row['cost_all']);
            if($cost_all==0) $cost_all='';            
           
            echo "
            <tr >
            <td class='c_i'>{$runner}</td>
            <td class='c_i'>{$place1}</td>
            <td class='c_i'>{$place2}</td>
            <td class='c_i'>{$place3}</td>
            <td class='c_i'>{$place4}</td>
            <td class='c_i'>{$place5}</td>
            <td class='c_i'>{$place6}</td>
            <td class='c_i'>{$cost1}</td>  
            <td class='c_i'>{$cost2}</td>     
            <td class='c_i'>{$cost3}</td>
            <td class='c_i'>{$cost4}</td> 
            <td class='c_i'>{$cost5}</td>  
            <td class='c_i'>{$cost6}</td>     
            <td class='c_i'>{$cost_all}</td>     
            </tr>  ";        
            $i++;
        }
       
    }
    
     echo "</table>";
}



if ($oper=='indic_summary_insp_new')
{
    $p_mmgg = trim(sql_field_val('dt_b', 'mmgg'));
    $mmgg1_str = trim($_POST['period_str']);
    $year_n = substr($p_mmgg,1,4);
    $month_n = substr($p_mmgg,6,2);
    
    $d_begin = "'".date("Y-m-d", strtotime($pp_dt_b))."'";
    $d_end   = "'".date("Y-m-d", strtotime($pp_dt_e))."'";
    
    $month_n=substr($pp_dt_e,3,2);
    $yearn_n = substr($pp_dt_e,6,4);        
    $param_date = "'".$year_n.'-'.$month_n.'-01'."'";
    
    $p_id_region = sql_field_val('id_region', 'int');
    
    if($res_code!='310')
      $caption1 = 'Кур`єр';
    else
      $caption1 = 'Контролер';    
        
    $params_caption ='';
    $where = ' ';    

   
$SQL="SELECT * from job_controler(".$param_date.','.$d_begin.','.$d_end.')';
//echo $SQL;


   // throw new Exception(json_encode($SQL));
    $sum_cnt_all=0;
    $sum_cnt_list=0;
    
    echo '</br>';
    echo $mmgg1_str;
    echo '<table cellpadding="0" cellspacing="0" border=1>';
    echo '<th>Кур’єр</th>';
    echo '<th>Кільк.(прим.)</th>';
    echo '<th>Кільк.(С.К.)</th>';
    echo '<th>Кільк.(винос. конт.)</th>';
    echo '<th>Кільк.(ВБШ)</th>';
    echo '<th>Кільк.(У кв.)</th>';
    echo '<th>Кільк.(буд.)</th>';
     
    echo '<th>Вартicть(прим.)</th>';
    echo '<th>Вартicть(С.К.)</th>';
    echo '<th>Вартicть(винос. конт.)</th>';
    echo '<th>Вартicть(ВБШ)</th>';
    echo '<th>Вартicть(У кв.)</th>';
    echo '<th>Вартicть(буд.)</th>';
    echo '<th>Вартicть усього</th>';
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        $i=0;
        while ($row = pg_fetch_array($result)) {

            $runner = htmlspecialchars($row['runner']);
            //echo $runner;
            //return;
            
            $place1 = htmlspecialchars($row['place1']);
            if($place1==0) $place1='';
            $place2 = htmlspecialchars($row['place2']);
            if($place2==0) $place2='';
            $place3 = htmlspecialchars($row['place3']);
            if($place3==0) $place3='';
            $place4 = htmlspecialchars($row['place4']);
            if($place4==0) $place4='';
            $place5 = htmlspecialchars($row['place5']);
            if($place5==0) $place5='';
            $place6 = htmlspecialchars($row['place6']);
            if($place6==0) $place6='';
            
            $cost1 = htmlspecialchars($row['cost1']);
            if($cost1==0) $cost1='';
            $cost2 = htmlspecialchars($row['cost2']);
            if($cost2==0) $cost2='';
            $cost3 = htmlspecialchars($row['cost3']);
            if($cost3==0) $cost3='';
            $cost4 = htmlspecialchars($row['cost4']);
            if($cost4==0) $cost4='';
            $cost5 = htmlspecialchars($row['cost5']);
            if($cost5==0) $cost5='';
            $cost6 = htmlspecialchars($row['cost6']);
            if($cost6==0) $cost6='';
            $cost_all = htmlspecialchars($row['cost_all']);
            if($cost_all==0) $cost_all='';            
           
            echo "
            <tr >
            <td class='c_i'>{$runner}</td>
            <td class='c_i'>{$place1}</td>
            <td class='c_i'>{$place2}</td>
            <td class='c_i'>{$place3}</td>
            <td class='c_i'>{$place4}</td>
            <td class='c_i'>{$place5}</td>
            <td class='c_i'>{$place6}</td>
            <td class='c_i'>{$cost1}</td>  
            <td class='c_i'>{$cost2}</td>     
            <td class='c_i'>{$cost3}</td>
            <td class='c_i'>{$cost4}</td> 
            <td class='c_i'>{$cost5}</td>  
            <td class='c_i'>{$cost6}</td>     
            <td class='c_i'>{$cost_all}</td>     
            </tr>  ";        
            $i++;
        }
       
    }
    
     echo "</table>";
}



//if(1==2) {
if ($oper=='indic_summary_counter_new')
{
    $p_mmgg = trim(sql_field_val('dt_b', 'mmgg'));
    $mmgg1_str = trim($_POST['period_str']);
    
    $year_n = substr($p_mmgg,1,4);
    $month_n = substr($p_mmgg,6,2);
    

    $param_date = "'".$year_n.'-'.$month_n.'-01'."'";
    $p_id_region = sql_field_val('id_region', 'int');
    
   
    if($res_code!='310')
      $caption1 = 'Кур`єр';
    else
      $caption1 = 'Контролер';    
        
    $params_caption ='';
    $where = ' ';    
   
   
$SQL="SELECT * from job_counter_detail(".$param_date.",'".$pp_person."'".')';
//$SQL="SELECT * from job_counter_detail()";

$f=fopen('aaa','w+');
fputs($f,$SQL);


   // throw new Exception(json_encode($SQL));
    $sum_cnt_all=0;
    $sum_cnt_list=0;
    
    echo '</br>';
    echo $mmgg1_str.'.';
    echo '  [Звіт по кількості лічильників по місцям установки]';
    //echo $SQL;
    echo '<table cellpadding="0" cellspacing="0" border=1>';
    echo '<th>Дільниця</th>';
    echo '<th>Кур’єр</th>';
    echo '<th>Кільк.(прим.)</th>';
    echo '<th>Кільк.(С.К.)</th>';
    echo '<th>Кільк.(винос. конт.)</th>';
    echo '<th>Кільк.(ВБШ)</th>';
    echo '<th>Кільк.(У кв.)</th>';
     echo '<th>Кільк.(буд.)</th>';
     
    echo '<th>Днів(прим.)</th>';
    echo '<th>Днів(С.К.)</th>';
    echo '<th>Днів(винос. конт.)</th>';
    echo '<th>Днів(ВБШ)</th>';
    echo '<th>Днів(У кв.)</th>';
    echo '<th>Днів(буд.)</th>';
    echo '<th>Днів усього</th>';
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        $i=0;
        $splace1=0;
        $splace2=0;
        $splace3=0;
        $splace4=0;
        $splace5=0;
        $splace6=0;
        $scost1=0;
        $scost2=0;
        $scost3=0;
        $scost4=0;
        $scost5=0;
        $scost6=0;
        $scost_all=0;
        
        while ($row = pg_fetch_array($result)) {
            $sector = htmlspecialchars($row['sector']);
            $runner = htmlspecialchars($row['runner']);
            $place1 = htmlspecialchars($row['place1']);
            if($place1==0) $place1='';
            $place2 = htmlspecialchars($row['place2']);
            if($place2==0) $place2='';
            $place3 = htmlspecialchars($row['place3']);
            if($place3==0) $place3='';
            $place4 = htmlspecialchars($row['place4']);
            if($place4==0) $place4='';
            $place5 = htmlspecialchars($row['place5']);
            if($place5==0) $place5='';
            $place6 = htmlspecialchars($row['place6']);
            if($place6==0) $place6='';
            
            $cost1 = htmlspecialchars($row['cost1']);
            if($cost1==0) $cost1='';
            $cost2 = htmlspecialchars($row['cost2']);
            if($cost2==0) $cost2='';
            $cost3 = htmlspecialchars($row['cost3']);
            if($cost3==0) $cost3='';
            $cost4 = htmlspecialchars($row['cost4']);
            if($cost4==0) $cost4='';
            $cost5 = htmlspecialchars($row['cost5']);
            if($cost5==0) $cost5='';
            $cost6 = htmlspecialchars($row['cost6']);
            if($cost6==0) $cost6='';
            $cost_all = htmlspecialchars($row['cost_all']);
            if($cost_all==0) $cost_all='';  
            
            $splace1+=$place1;
            $splace2+=$place2;
            $splace3+=$place3;
            $splace4+=$place4;
            $splace5+=$place5;
            $splace6+=$place6;
            $scost1+=$cost1;
            $scost2+=$cost2;
            $scost3+=$cost3;
            $scost4+=$cost4;
            $scost5+=$cost5;
            $scost6+=$cost6;
            $scost_all+=$cost_all;
           
            echo "
            <tr >
            <td class='c_i'>{$sector}</td>
            <td class='c_i'>{$runner}</td>
            <td class='c_i'>{$place1}</td>
            <td class='c_i'>{$place2}</td>
            <td class='c_i'>{$place3}</td>
            <td class='c_i'>{$place4}</td>
            <td class='c_i'>{$place5}</td>
            <td class='c_i'>{$place6}</td>
            <td class='c_i'>{$cost1}</td>  
            <td class='c_i'>{$cost2}</td>     
            <td class='c_i'>{$cost3}</td>
            <td class='c_i'>{$cost4}</td> 
            <td class='c_i'>{$cost5}</td>  
            <td class='c_i'>{$cost6}</td>
            <td class='c_i'>{$cost_all}</td>     
            </tr>  ";        
            $i++;
        }
         echo "
         <tr >
            <td class='c_i_all'>Усього:</td>
            <td class='c_i_all'></td>
            <td class='c_i_all'>{$splace1}</td>
            <td class='c_i_all'>{$splace2}</td>
            <td class='c_i_all'>{$splace3}</td>
            <td class='c_i_all'>{$splace4}</td>
            <td class='c_i_all'>{$splace5}</td>
            <td class='c_i_all'>{$splace6}</td>
            <td class='c_i_all'>{$scost1}</td>  
            <td class='c_i_all'>{$scost2}</td>     
            <td class='c_i_all'>{$scost3}</td>
            <td class='c_i_all'>{$scost4}</td> 
            <td class='c_i_all'>{$scost5}</td> 
            <td class='c_i_all'>{$scost6}</td>     
            <td class='c_i_all'>{$scost_all}</td>     
        </tr>  ";
        
    }
    
     echo "</table>";
}
//}

// Новая версия ведомости по количеству счетчиков
//if ($oper=='indic_summary_counter_new')
//{
//    $p_mmgg = trim(sql_field_val('dt_b', 'mmgg'));
//    $mmgg1_str = trim($_POST['period_str']);
//    
//    $year_n = substr($p_mmgg,1,4);
//    $month_n = substr($p_mmgg,6,2);
//    $param_date = "'".$year_n.'-'.$month_n.'-01'."'";
//    $p_id_region = sql_field_val('id_region', 'int');
//    
//    $app_version='20171019';

//    print('<link rel="stylesheet" type="text/css" href="css/ded_styles.css" /> ');
//    //print("<link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"css/ui.jqgrid.css\" /> \n ");
//
//    print('<script type="text/javascript" src="js/jquery-ui-1.8.20.custom.min.js"></script> ');
//    print('<script type="text/javascript" src="js/jquery.form.js"></script>');
//    print('<script type="text/javascript" src="js/jquery.validate.min.js?version='.$app_version.'" ></script>');
//    print('<script type="text/javascript" src="js/jquery.ui.datepicker-uk.js"></script> ');
//    print('<script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script> ');
//    print('<script type="text/javascript" src="js/jquery.layout.min-latest.js"></SCRIPT>');
//    print('<script type="text/javascript" src="js/jquery.layout.resizeTabLayout.min-1.2.js"></SCRIPT>');
//    print('<script type="text/javascript" src="js/date-uk-UA.js"></script> ');  
    //print("<script type=\"text/javascript\" src=\"js/i18n/grid.locale-ua.js\"></script> \n");
    //print("<script type=\"text/javascript\" src=\"js/jquery.jqGrid.min.js\"></script> \n");
//    print('<script type="text/javascript" src="controlers_counters.js?version='.$app_version.'"></script> ');
//}    

if ($oper=='indic_summary_insp_detail_new')
{
    $p_mmgg = trim(sql_field_val('dt_b', 'mmgg'));
    $mmgg1_str = trim($_POST['period_str']);
    $year_n = substr($p_mmgg,1,4);
    $month_n = substr($p_mmgg,6,2);
    //$param_date = "'".$year_n.'-'.$month_n.'-01'."'";
    $p_id_region = sql_field_val('id_region', 'int');
    
    $d_begin = "'".date("Y-m-d", strtotime($pp_dt_b))."'";
    $d_end   = "'".date("Y-m-d", strtotime($pp_dt_e))."'";
    $month_n=substr($pp_dt_e,3,2);
    $yearn_n = substr($pp_dt_e,6,4);        
    $param_date = "'".$year_n.'-'.$month_n.'-01'."'";
    
    if($res_code!='310')
      $caption1 = 'Кур`єр';
    else
      $caption1 = 'Контролер';    
        
    $params_caption ='';
    $where = ' ';    

   
$SQL="SELECT * from job_controler_detail(".$param_date.','.$d_begin.','.$d_end.','."'".$pp_person."'".')';


   // throw new Exception(json_encode($SQL));
    $sum_cnt_all=0;
    $sum_cnt_list=0;
    
    echo '</br>';
    echo $mmgg1_str;
    echo '<table cellpadding="0" cellspacing="0" border=1>';
    echo '<th>Дільниця</th>';
    echo '<th>Кур’єр</th>';
    echo '<th>Кільк.(прим.)</th>';
    echo '<th>Кільк.(С.К.)</th>';
    echo '<th>Кільк.(винос. конт.)</th>';
    echo '<th>Кільк.(ВБШ)</th>';
    echo '<th>Кільк.(У кв.)</th>';
    echo '<th>Кільк.(буд.)</th>';
     
//    echo '<th>Вартicть(прим.)</th>';
//    echo '<th>Вартicть(С.К.)</th>';
//    echo '<th>Вартicть(винос. конт.)</th>';
//    echo '<th>Вартicть(ВБШ)</th>';
//    echo '<th>Вартicть(У кв.)</th>';
//    echo '<th>Вартicть(буд.)</th>';
//    echo '<th>Вартicть усього</th>';
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        $i=0;
        $splace1=0;
        $splace2=0;
        $splace3=0;
        $splace4=0;
        $splace5=0;
        $splace6=0;
        $scost1=0;
        $scost2=0;
        $scost3=0;
        $scost4=0;
        $scost5=0;
        $scost6=0;
        $scost_all=0;
        
        while ($row = pg_fetch_array($result)) {
            $sector = htmlspecialchars($row['sector']);
            $runner = htmlspecialchars($row['runner']);
            $place1 = htmlspecialchars($row['place1']);
            if($place1==0) $place1='';
            $place2 = htmlspecialchars($row['place2']);
            if($place2==0) $place2='';
            $place3 = htmlspecialchars($row['place3']);
            if($place3==0) $place3='';
            $place4 = htmlspecialchars($row['place4']);
            if($place4==0) $place4='';
            $place5 = htmlspecialchars($row['place5']);
            if($place5==0) $place5='';
            $place6 = htmlspecialchars($row['place6']);
            if($place6==0) $place6='';
            
            $cost1 = htmlspecialchars($row['cost1']);
            if($cost1==0) $cost1='';
            $cost2 = htmlspecialchars($row['cost2']);
            if($cost2==0) $cost2='';
            $cost3 = htmlspecialchars($row['cost3']);
            if($cost3==0) $cost3='';
            $cost4 = htmlspecialchars($row['cost4']);
            if($cost4==0) $cost4='';
            $cost5 = htmlspecialchars($row['cost5']);
            if($cost5==0) $cost5='';
            $cost6 = htmlspecialchars($row['cost6']);
            if($cost6==0) $cost6='';
            $cost_all = htmlspecialchars($row['cost_all']);
            if($cost_all==0) $cost_all='';  
            
            $splace1+=$place1;
            $splace2+=$place2;
            $splace3+=$place3;
            $splace4+=$place4;
            $splace5+=$place5;
            $splace6+=$place6;
            $scost1+=$cost1;
            $scost2+=$cost2;
            $scost3+=$cost3;
            $scost4+=$cost4;
            $scost5+=$cost5;
            $scost6+=$cost6;
            $scost_all+=$cost_all;
           
            echo "
            <tr >
            <td class='c_i'>{$sector}</td>
            <td class='c_i'>{$runner}</td>
            <td class='c_i'>{$place1}</td>
            <td class='c_i'>{$place2}</td>
            <td class='c_i'>{$place3}</td>
            <td class='c_i'>{$place4}</td>
            <td class='c_i'>{$place5}</td>
            <td class='c_i'>{$place6}</td>

            </tr>  ";        
            $i++;
        }
         echo "
         <tr >
            <td class='c_i_all'>Усього:</td>
            <td class='c_i_all'></td>
            <td class='c_i_all'>{$splace1}</td>
            <td class='c_i_all'>{$splace2}</td>
            <td class='c_i_all'>{$splace3}</td>
            <td class='c_i_all'>{$splace4}</td>
            <td class='c_i_all'>{$splace5}</td>
            <td class='c_i_all'>{$splace6}</td>

        </tr>  ";
        
    }
    
     echo "</table>";
}


//----------------------------------------------------------------------------

if (($oper=='abon_list')||($oper=='abon_list_3f')||
    ($oper=='abon_list_reswork')||
    ($oper=='abon_list_vip')||
    ($oper=='abon_list_dach')||
    ($oper=='abon_list_nometer')||        
    ($oper=='abon_list_2z')||                
    ($oper=='abon_list_3z')||                        
    ($oper=='abon_list_notlive') ||
    ($oper=='abon_list_nosector') ||
    ($oper=='abon_list_dogovor')||        
    ($oper=='abon_list_nodogovor')||
    ($oper=='abon_list_noagreement')||
    ($oper=='abon_list_agreement')||
    ($oper=='abon_list_tmp_dogovor')    )
{
    $p_dt = sql_field_val('dt_rep', 'date');
    $dt_str = $_POST['dt_rep'];
    $p_book = sql_field_val('book', 'string');
    $p_id_town = sql_field_val('addr_town', 'int');
    $p_id_tar = sql_field_val('id_gtar', 'int');    
    $p_id_type_meter = sql_field_val('id_type_meter', 'int');    
    $p_id_type_meter_array = sql_field_val('id_type_meter_array', 'array');    
    $p_id_region = sql_field_val('id_region', 'int');
    $p_id_dod = sql_field_val('id_cntrl', 'int');
    $p_name_dod = sql_field_val('cntrl', 'string');
    
    $p_id_sector = sql_field_val('id_sector', 'int');
    
    if (isset($_POST['town_detal']))
        $p_town_detal = trim($_POST['town_detal']);
    else 
        $p_town_detal =0;

    if ((isset($_POST['addr_town_name']))&&($_POST['addr_town_name']!=''))
        $params_caption = 'Населений пункт: '. trim($_POST['addr_town_name']);
    else 
        $params_caption ='';

    if ((isset($_POST['gtar']))&&($_POST['gtar']!=''))
        $params_caption .= ' Тарифна група : '. trim($_POST['gtar']);

    if ((isset($_POST['type_meter']))&&($_POST['type_meter']!=''))
        $params_caption .= ' Лічильник : '. trim($_POST['type_meter']);
    
    if ((isset($_POST['sector']))&&($_POST['sector']!=''))
        $params_caption.= ' Дільниця: '. trim($_POST['sector']);
    
    
    if ($params_caption !='') $params_caption .='<br/>';
        
    $where = ' ';
    $left = '';
    
    if ((isset($_POST['note']))&&($_POST['note']!=''))
    {
        $where.= $_POST['note'];
        $params_caption .= $_POST['note'];
    }
    
    
    if ($p_book!='null')
    {
        $where.= " and book = $p_book ";
        $params_caption .= " книга : $p_book ";
        
    }
    
    if ($p_id_town!='null')
    {
        // $where.= "and adr.id_town = $p_id_town ";
        $where.= " and ( adr.id_town = $p_id_town or adr.id_parent = $p_id_town )";
    }

    if ($p_id_tar!='null')
    {
        $where.= "and c.id_gtar = $p_id_tar ";
    }

    if ($p_id_dod!='null')
    {
        $where.= "and c.id_cntrl = $p_id_dod ";
        $params_caption .= " Додаткова ознака : $p_name_dod ";
    }
    
    if ($p_id_type_meter_array!='null')
    {
        
     $json_str = stripslashes($p_id_type_meter_array); 
     $meter_id_list = json_decode($json_str,true);
    
     $where.= " and m.id_type_meter in ( ";
    
     $i = 0;
     foreach($meter_id_list as $id_meter) {
    
        if ($i>0) $where.=",";
        
        $where.="$id_meter";
        $i++;
     }
     $where.=") ";
        
    }
    else
    {            
      if ($p_id_type_meter!='null')
      {
        $where.= "and m.id_type_meter = $p_id_type_meter ";
      }
    }
    
    if ($p_id_region!='null')
    {
        
        $where.= "and exists (select rs.id from 
            prs_runner_paccnt as rp 
            join prs_runner_sectors as rs on (rs.id = rp.id_sector)
            where rp.id_paccnt = c.id and rs.id_region =  $p_id_region ) "; 
        
        //$where.= " and rs.id_region =  $p_id_region ";

        $SQL_H="SELECT name from cli_region_tbl where id = $p_id_region ;";
    
        $result = pg_query($Link, $SQL_H) or die("SQL Error: " . pg_last_error($Link) . $SQL_H);
        $row = pg_fetch_array($result);
        
        $params_caption.= $row['name'];        
       
    }
    
    
    if ($oper=='abon_list_3f')
    {
        $where.= "and m.phase = 2 ";
        $params_caption.=' споживачи з трифазними електроустановками ';        
    }
    
    
    if ($oper=='abon_list_reswork')   //-- сотрудники РЕС
    {
        $where.= "and c.rem_worker = true ";
        $params_caption.=' працівники РЕМ ';
    }

    if ($oper=='abon_list_vip')   //-- особый контроль
    {
        $where.= "and c.pers_cntrl = true ";
        $params_caption.=' спожівачи з особливим наглядом ';
    }
    
    if ($oper=='abon_list_dach') //--дача
    {
        $where.= "and c.idk_house = 3 ";
        $params_caption.=' дачники ';
    }

    if ($oper=='abon_list_noagreement') 
    {
        $where.= "and coalesce(c.dt_dod,'2001-01-01'::date) = '2001-01-01'  and c.book <> '2000' ";
        $params_caption.=' не маючі дати додоткової угоди ';
    }

    if ($oper=='abon_list_agreement') 
    {
        $where.= " and (( coalesce(c.dt_dod,'2001-01-01'::date) > '2001-01-01') and (coalesce(c.dt_dod,'2001-01-01'::date) <= $p_dt)) ";
        $params_caption.=' мають дійсні додоткової угоди ';
    }
    
    if ($oper=='abon_list_nometer') //--нет счетчика
    {
        $where.= "and m.id_paccnt is null ";
        $left = 'left';
        $params_caption.=' немає лічильників ';
    }

    if ($oper=='abon_list_nosector') //--нет участка
    {
        $where.= "and not exists (select id from prs_runner_paccnt as rp 
            where rp.id_paccnt = c.id and rp.id_sector is not null ) ";

        $params_caption.=' не призначена дільниця ';
    }
    else
    {
      if ($p_id_sector!='null')
      {
        $where.= "and exists (select id from prs_runner_paccnt as rp 
            where rp.id_paccnt = c.id and rp.id_sector = $p_id_sector ) ";
        
      }
        
    }
    
    if ($oper=='abon_list_2z') //-- двухзонные
    {
        $where.= " and  exists 
        ( select z.id_meter
         from clm_meter_zone_h as z 
        where z.dt_b <= $p_dt and coalesce(z.dt_e,$p_dt) >=$p_dt
        and z.id_meter = m.id and z.id_zone in (9,10)
        ) ";
        $params_caption.=' Двозонні обліки ';
    }

    if ($oper=='abon_list_3z') //-- 3-зонные
    {
        $where.= " and  exists 
        ( select z.id_meter
         from clm_meter_zone_h as z 
        where z.dt_b <= $p_dt and coalesce(z.dt_e,$p_dt) >=$p_dt
        and z.id_meter = m.id and z.id_zone in (6,7,8)
        ) ";
        $params_caption.=' Тризонні обліки ';
    }
    
    $notlive_select='';
    $notlive_dt = '';
    
    if ($oper=='abon_list_notlive') //-- временно непроживающие
    {
        $notlive_select = " join (
        select n.id_paccnt, n.dt_b , n.dt_e from 
	clm_notlive_tbl as n 
        where dt_b<=$p_dt and ((dt_e is null) or (dt_e>{$p_dt}::date))
        order by n.id_paccnt
        ) as nn on (nn.id_paccnt = c.id)";    
        

        $notlive_dt = " to_char(nn.dt_b, 'DD.MM.YYYY') as dt_b , to_char(nn.dt_e , 'DD.MM.YYYY') as dt_e, ";
        
        $params_caption.=' тимчасово непроживаючі абоненти ';
    }   

    if ($oper=='abon_list_nodogovor') //--нет договора
    {
        $where.= "and not exists (select id from clm_agreem_tbl as d 
            where d.id_paccnt = c.id ) and c.book <> '2000' ";

        $params_caption.=' відсутній договір ';
    }

    if ($oper=='abon_list_tmp_dogovor') 
    {
        
        $notlive_select = " join (
         select d.id_paccnt, d.dt_b , d.dt_e from 
	 clm_agreem_tbl as d 
         where d.id_iagreem = 2
         and (d.dt_e is null or d.dt_e >= $p_dt ) and d.dt_b <= $p_dt
         order by d.id_paccnt
        ) as nn on (nn.id_paccnt = c.id)";    
        
        $notlive_dt = " to_char(nn.dt_b, 'DD.MM.YYYY') as dt_b , to_char(nn.dt_e , 'DD.MM.YYYY') as dt_e, ";        

        $params_caption.=' з тимчасовими договорами ';
    }
    
    
    if ($p_town_detal ==1)
    {
        $order ='town, int_book, book, int_code,code';
    }
    else
    {
        $order ='int_book, book, int_code,code';
    }
    
    eval("\$header_text = \"$header_text\";");
    
    echo_html( $header_text);
    
   
    
    $SQL = "select c.id,c.code,c.book, c.note, ap.name as add_param, $notlive_dt
    adr.town, adr.street,     
    address_print(c.addr) as house,
   (a.last_name||' '||coalesce(substr(a.name,1,1),'')||'.'||coalesce(substr(a.patron_name,1,1),''))||'.'::varchar as abon,
   m.power,m.num_meter,m.coef_comp,
    to_char(m.dt_start, 'DD.MM.YYYY') as dt_start,
    to_char(c.dt_dod, 'DD.MM.YYYY') as dt_dod,
    m.type_meter, ilg.ident, 
    (zzz.indic_str||' на '||to_char(zzz.dat_ind, 'DD.MM.YYYY'))::varchar as last_ind, 
    ('0'||substring(c.code FROM '[0-9]+'))::int as int_code,
    ('0'||substring(c.book FROM '[0-9]+'))::int as int_book
from 
    clm_paccnt_h as c
    join (select id, max(dt_b) as dt_b from clm_paccnt_h  where dt_b <= $p_dt and coalesce(dt_e,$p_dt) >=$p_dt group by id order by id) as c2 
    on (c.id = c2.id and c.dt_b = c2.dt_b)
    join clm_abon_tbl as a on (a.id = c.id_abon) 
    join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class)
    left join cli_addparam_tbl as ap on (ap.id = c.id_cntrl)
    $notlive_select
    $left join ( select m.*, im.name as type_meter, im.phase  from
      clm_meterpoint_h as m 
      join (select id, max(dt_b) as dt_b from clm_meterpoint_h  where dt_b <= $p_dt and coalesce(dt_e,$p_dt) >=$p_dt group by id order by id) as m2 
       on (m.id = m2.id and m.dt_b = m2.dt_b)
      join eqi_meter_tbl as im on (im.id = m.id_type_meter)
      order by m.id_paccnt
    ) as m on (m.id_paccnt = c.id) 
   left join (
    select id_paccnt, max(la.id_grp_lgt) as id_grp_lgt
    from lgm_abon_tbl  as la
    where (dt_end is null) or (dt_end > $p_dt::date) group by id_paccnt order by id_paccnt
   )  as lg on (lg.id_paccnt = c.id)
   left join lgi_group_tbl as ilg  on (ilg.id = id_grp_lgt)
   left join ( select id_paccnt, dat_ind,  trim(sum(indic||','),',')::varchar as indic_str from 
     (
      select i.id_paccnt, i.id_zone, i.dat_ind, i.num_eqp, CASE WHEN z.id = 0 THEN (i.value::int)::varchar ELSE z.nm||':'||((i.value::int)::varchar) END::varchar as indic
      from acm_indication_tbl as i 
      join (select id_paccnt, max(dat_ind) as max_dat from acm_indication_tbl 
      group by id_paccnt
      ) as mi
      on (i.id_paccnt = mi.id_paccnt and i.dat_ind = mi.max_dat)
      join eqk_zone_tbl as z on (z.id = i.id_zone)
       order by i.id_zone
     ) as ss
    group by id_paccnt, dat_ind
    order by id_paccnt
    ) as zzz on (zzz.id_paccnt = c.id)    
where c.archive=0 $where 
order by $order ;";

  
    $cur_town='';
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        $i=1;
        $np=0;        
        while ($row = pg_fetch_array($result)) {
            $np++;
            if ((($np> ($page_num-1)* $page_size )&& ($np<= $page_num* $page_size )) || ($show_pager ==0))
                $print_flag = 1;
            else
                $print_flag = 0;
            
            
            if (($row['town']!=$cur_town)&&($p_town_detal==1))
            {
                $cur_town=$row['town'];
                
                $town_str = htmlspecialchars($cur_town);
                echo_html( "
                <tr >
                <td COLSPAN=10 class = 'tab_head'>{$town_str}</td>
                </tr>  ",$print_flag);        
                $i=1;
            }
            
            $abon = htmlspecialchars($row['abon']);
            $note = htmlspecialchars($row['note']);
            $add_param = htmlspecialchars($row['add_param']);
            $addr = htmlspecialchars($row['town'].' '.$row['street'].' '.$row['house']);
            $book=$row['book'];
            $code=$row['code'];
            $power_txt = number_format_ua ($row['power'],1);  
           
            // <tr >
            if ($p_sum_only!=1) {
              echo_html( " <tr>
              <td>{$i}</td>
              <td class='c_t'>{$book}</td>
              <td class='c_t'>{$code}</td>
              <td>{$abon}</td>
              <td>{$addr}</td>
              <td class='c_n'>{$power_txt}</td>
              <td class='c_t'>{$row['num_meter']}</td>
              <td>{$row['type_meter']}</td>
              <td >{$row['dt_start']}</td> 
              <td >{$row['dt_dod']}</td> 
              <td class='c_t'>{$add_param}</td>
              <td class='c_n'>{$row['ident']}</td>
              <td class='c_t'>{$row['last_ind']}</td>
              <td class='c_t'>{$note}</td>
              ",$print_flag);
            
              if (($oper=='abon_list_notlive')||($oper=='abon_list_tmp_dogovor'))
              {
                echo_html( "
                  <td >{$row['dt_b']}</td> 
                  <td >{$row['dt_e']}</td> ",$print_flag);
              }
            
              echo " </tr>  ";     
            }
            $i++;
        }
        
    }
    
    $count_txt = number_format_ua ($i-1,0);  
    
    eval("\$footer_text_eval = \"$footer_text\";");
    echo_html( $footer_text_eval);

    //return;
    
}
//----------------------------------------------------------------------------
if (($oper=='abon_saldo_list')||($oper=='abon_saldo_list_reswork')||
    ($oper=='abon_saldo_list_vip')||
    ($oper=='abon_saldo_list_dach')||
    ($oper=='abon_saldo_list_heat')||
    ($oper=='abon_saldo_list_3')||
    ($oper=='abon_saldo_list_notlive'))
{
    $p_mmgg = sql_field_val('dt_b', 'mmgg');
    $mmgg1_str = trim($_POST['period_str']);

    $p_id_town = sql_field_val('addr_town', 'int');
    $p_id_tar = sql_field_val('id_gtar', 'int');        
    
    $p_id_sector = sql_field_val('id_sector', 'int');
    $p_book = sql_field_val('book', 'string');
    $p_id_region = sql_field_val('id_region', 'int');
    
    $p_id_type_meter = sql_field_val('id_type_meter', 'int');    
    $p_id_type_meter_array = sql_field_val('id_type_meter_array', 'array');    
    
    $params_caption =' спожівачів ';
    $where = ' ';

    if ($p_id_town!='null')
    {
        $where.= "and adr.id_town = $p_id_town ";
    }
  
    if (isset($_POST['town_detal']))
        $p_town_detal = trim($_POST['town_detal']);
    else 
        $p_town_detal =0;

    if ((isset($_POST['sector']))&&($_POST['sector']!=''))
        $params_caption.= ' Дiльниця: '. trim($_POST['sector']);
    
    if ($p_book!='null')
    {
        $where.= " and book = $p_book ";
        $params_caption .= " книга : $p_book ";
        
    }

    if ($p_id_sector!='null')
    {
       $where.= "and exists (select id from prs_runner_paccnt as rp 
           where rp.id_paccnt = c.id and rp.id_sector = $p_id_sector ) ";
        
    }
    
    if ($p_id_type_meter_array!='null')
    {
        
     $json_str = stripslashes($p_id_type_meter_array); 
     $meter_id_list = json_decode($json_str,true);
    
     $where.= " and m.id_type_meter in ( ";
    
     $i = 0;
     foreach($meter_id_list as $id_meter) {
    
        if ($i>0) $where.=",";
        
        $where.="$id_meter";
        $i++;
     }
     $where.=") ";
        
    }
    else
    {            
      if ($p_id_type_meter!='null')
      {
        $where.= "and m.id_type_meter = $p_id_type_meter ";
      }
    }
    
    
    if ($oper=='abon_saldo_list_reswork')   //-- сотрудники РЕС
    {
        $where.= "and c.rem_worker = true ";
        $params_caption=' працівників РЕМ ';
    }

    if ($oper=='abon_saldo_list_vip')   //-- особый контроль
    {
        $where.= "and c.pers_cntrl = true ";
        $params_caption=' спожівачів з особливин наглядом ';
    }
    
    if ($oper=='abon_saldo_list_dach') //--дача
    {
        $where.= "and c.idk_house = 3 ";
        $params_caption=' дачників ';
    }
    
    if ($oper=='abon_saldo_list_heat') //--отопление
    {
        $where.= " and tar.ident ~'tgr7_5' ";
        $params_caption=' спожівачів з електроопаленням ';
    }
    
    if ($oper=='abon_saldo_list_3') //-- 3-фазные
    {        
    /*
        $where = "  and c.id in  (
        select m.id_paccnt from 
	clm_meterpoint_h as m 
        join (select id, max(dt_b) as dt_b from clm_meterpoint_h  where
        ((dt_b < ($p_mmgg::date+'1 month'::interval) and dt_e is null)
            or 
            tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
        )
        group by id order by id) as m2 
	on (m.id = m2.id and m.dt_b = m2.dt_b)
	join eqi_meter_tbl as im on (im.id = id_type_meter)
	where im.phase=2 ) ";    
      */
        $where.= "  and m.phase=2 ";
        
        $params_caption=' споживачiв з трифазними електроустановками ';
    }
    if ($oper=='abon_saldo_list_notlive') //-- временно непроживающие
    {
        $where.= "  and c.id in  (
        select n.id_paccnt from 
	clm_notlive_tbl as n 
        where dt_b<=$p_mmgg and ((dt_e is null) or (dt_e>={$p_mmgg}::date+'1 month - 1 day'::interval))
        ) ";    

        $params_caption=' тимчасово непроживаючих абонентiв ';
    }   
    
    if ($p_id_tar!='null')
    {
        $where.= " and c.id_gtar = $p_id_tar ";
    }
    
    if ((isset($_POST['addr_town_name']))&&($_POST['addr_town_name']!=''))
        $params_caption.= ' населений пункт: '. trim($_POST['addr_town_name']);

    if ((isset($_POST['gtar']))&&($_POST['gtar']!=''))
        $params_caption .= ' Тарифна група : '. trim($_POST['gtar']);
    
    
    if ($p_id_region!='null')
    {
         
        $where.= "and exists (select rs.id from 
            prs_runner_paccnt as rp 
            join prs_runner_sectors as rs on (rs.id = rp.id_sector)
            where rp.id_paccnt = c.id and rs.id_region =  $p_id_region ) ";

        $SQL_H="SELECT name from cli_region_tbl where id = $p_id_region ;";
    
        $result = pg_query($Link, $SQL_H) or die("SQL Error: " . pg_last_error($Link) . $SQL_H);
        $row = pg_fetch_array($result);
        
        $params_caption.= $row['name'];        
       
    }    
    
    if ($p_town_detal==0)
        $order ="c.book, int_code, c.code";
    else
        $order ="town, c.book, int_code, c.code";
    
    eval("\$header_text = \"$header_text\";");
    
    echo $header_text;
    
   $b_val_sum = 0;  
   $e_val_sum = 0;  
   
   $e_dt_sum = 0;  
   $e_kt_sum = 0;  
   
   $demand_sum = 0;
   $dt_val_sum = 0;  
   $kt_val_sum = 0;  

   $b_val_town = 0;  
   $e_val_town = 0;  
   
   $e_dt_town = 0;  
   $e_kt_town = 0;  
   
   $demand_town = 0;
   $dt_val_town = 0;  
   $kt_val_town = 0;  
   
   $print_flag =0;
   $abon_count=0;
    
    $SQL = "  select c.id, c.code, c.book,
        b.demand as bill_demand, 
        s.b_val , s.e_val, s.dt_val, s.kt_val,
        adr.town, adr.street, address_print(c.addr) as house,
        (ab.last_name||' '||coalesce(substr(ab.name,1,1),'')||'.'||coalesce(substr(ab.patron_name,1,1),''))||'.'::varchar as abon,
        CASE WHEN s.e_val >0 THEN DateDiff('m',bp.min_bill,$p_mmgg::date) END as month_debt,
        ('0'||substring(c.code FROM '[0-9]+'))::int as int_code,
        m.num_meter,m.power,m.type_meter
        from acm_saldo_tbl as s
        join clm_paccnt_h as c on (c.id = s.id_paccnt)
        join (select id, max(dt_b) as dt_b from clm_paccnt_h  where 
            ((dt_b < ($p_mmgg::date+'1 month'::interval) and dt_e is null)
                or 
                tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
        )  group by id order by id) as c2 
        on (c.id = c2.id and c.dt_b = c2.dt_b)
        join clm_abon_tbl as ab on (ab.id = c.id_abon) 
        join aqi_grptar_tbl as tar on (tar.id = c.id_gtar)
        left join ( select m.id_paccnt, m.power, m.num_meter, m.id_type_meter, 
           im.name as type_meter,im.phase   from
          clm_meterpoint_tbl as m 
          join eqi_meter_tbl as im on (im.id = m.id_type_meter)
          order by m.id_paccnt
        ) as m on (m.id_paccnt = c.id) 
        left join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class)
        left join (
        	select b.id_paccnt, sum(b.value) as value, sum(b.demand) as demand
                from acm_bill_tbl as b 
                where b.mmgg = {$p_mmgg}::date and b.id_pref = 10
                group by b.id_paccnt order by b.id_paccnt
        ) as b
        on (b.id_paccnt = s.id_paccnt)
        left join (
            select b.id_paccnt, min(CASE WHEN b.idk_doc = 1000 THEN b.mmgg_bill ELSE b.mmgg END) as min_bill 
            from acv_billpay as bp
            join acm_bill_tbl as b on (b.id_doc = bp.id_doc)
            where bp.rest >0
            and b.mmgg <= {$p_mmgg}::date and b.id_pref = 10     
            group by b.id_paccnt                
        ) as bp
        on (bp.id_paccnt = s.id_paccnt)
        where s.mmgg = {$p_mmgg}::date  and s.id_pref = 10 $where
        order by $order ;";

/*
        left join (
        	select p.id_paccnt, max(mmgg) as last_mmgg
        	from acm_pay_tbl as p 
        	where p.mmgg <= {$p_mmgg}::date and p.id_pref = 10 
        	group by p.id_paccnt
        ) as p
 
 */        
  
    $cur_town='';
    $np=0;
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        $i=1;
        while ($row = pg_fetch_array($result)) {

            $np++;
            if ((($np> ($page_num-1)* $page_size )&& ($np<= $page_num* $page_size )) || ($show_pager ==0))
                $print_flag = 1;
            else
                $print_flag = 0;
            
            if (($row['town']!=$cur_town)&&($p_town_detal==1))
            {

                if ($cur_town!="")
                {
                    
                    $b_val_txt = number_format_ua ($b_val_town,2);  
                    $e_val_txt = number_format_ua ($e_val_town,2);  
                    $demand_txt = number_format_ua ($demand_town,0);  
                    $dt_val_txt = number_format_ua ($dt_val_town,2);  
                    $kt_val_txt = number_format_ua ($kt_val_town,2);  
    
                    $e_dt_txt = number_format_ua ($e_dt_town,2);  
                    $e_kt_txt = number_format_ua ($e_kt_town,2);  
    
                    echo_html( "
                        <tr class='table_footer'>
                        <td colspan='6'> Всього по {$cur_town}</td>
                        <td class='c_n'>{$b_val_txt}</td>
                        <td class='c_i'>{$demand_txt}</td>
                        <td class='c_n'>{$dt_val_txt}</td>
                        <td class='c_n'>{$kt_val_txt}</td>
                        <td class='c_n'></td>
                        <td class='c_n'>{$e_val_txt}</td>
                        <td class='c_n'>{$e_dt_txt}</td>
                        <td class='c_n'>{$e_kt_txt}</td>
                    </tr>  ",$print_flag);        
                    
                    
                   $b_val_town = 0;  
                   $e_val_town = 0;  
   
                   $e_dt_town = 0;  
                   $e_kt_town = 0;  
   
                   $demand_town = 0;
                   $dt_val_town = 0;  
                   $kt_val_town = 0;  
                    
                }
                
                
                $town_str = htmlspecialchars($row['town']);
                if ($p_sum_only!=1) {
                echo_html( "
                <tr >
                <td COLSPAN=14 class = 'tab_head'>{$town_str}</td>
                </tr>  ",$print_flag);         
                }
                $i=1;
                
                    
                $cur_town=$row['town'];                    
            }
            
            $abon = htmlspecialchars($row['abon']);
            $addr = htmlspecialchars($row['town'].' '.$row['street'].' '.$row['house']);
            $book=$row['book'].'/'.$row['code'];
            
            $num_meter = htmlspecialchars($row['num_meter']);
            $meter = htmlspecialchars($row['type_meter']);
            $power = number_format_ua($row['power'],0);  
            
            $b_val_txt = number_format_ua (-$row['b_val'],2);  
            $e_val_txt = number_format_ua (-$row['e_val'],2);  
            $e_dt=0;
            $e_kt=0;
            if ($row['e_val']>=0)
            {
                $e_dt = $row['e_val'];
                $e_dt_txt = number_format_ua ($e_dt,2);  
                $e_kt_txt ='';
            }
            else
            {
                $e_kt = -$row['e_val'];
                $e_kt_txt = number_format_ua ($e_kt,2);  
                $e_dt_txt ='';
                
            }
            
            $dt_val_txt = number_format_ua ($row['dt_val'],2);  
            $kt_val_txt = number_format_ua ($row['kt_val'],2);  
            
            if (($row['month_debt']!=0)&& ($row['e_val']>0))
            {
                $month_debt=$row['month_debt'];
            }
            else
            {
                $month_debt='';
            }
            if ($p_sum_only!=1) {
            echo_html( "
            <tr >
            <td>{$addr}</td>
            <td>{$abon}</td>
            <td class='c_t'>{$book}</td>
            <td class='c_n'>{$power}</td>
            <td class='c_t'>{$meter}</td>
            <td class='c_t'>{$num_meter}</td>
            <td class='c_n'>{$b_val_txt}</td>
            <td class='c_i'>{$row['bill_demand']}</td>
            <td class='c_n'>{$dt_val_txt}</td>
            <td class='c_n'>{$kt_val_txt}</td>
            <td class='c_r'>{$month_debt}</td>
            <td class='c_n'>{$e_val_txt}</td>
            <td class='c_n'>{$e_dt_txt}</td>
            <td class='c_n'>{$e_kt_txt}</td>
            </tr>  ",$print_flag); 
            }
            $i++;
            
            $b_val_sum += (-$row['b_val']);  
            $e_val_sum += (-$row['e_val']);  
            $demand_sum += $row['bill_demand'];
            $dt_val_sum += $row['dt_val'];  
            $kt_val_sum += $row['kt_val'];  
            
            $e_dt_sum +=$e_dt;  
            $e_kt_sum +=$e_kt;

            $b_val_town += (-$row['b_val']);  
            $e_val_town += (-$row['e_val']);  
            $demand_town += $row['bill_demand'];
            $dt_val_town += $row['dt_val'];  
            $kt_val_town += $row['kt_val'];  
            
            $e_dt_town +=$e_dt;  
            $e_kt_town +=$e_kt;
            
            
            $abon_count++;
            
        }
        
    }
    
    
    if ($p_town_detal==1) {

        $b_val_txt = number_format_ua($b_val_town, 2);
        $e_val_txt = number_format_ua($e_val_town, 2);
        $demand_txt = number_format_ua($demand_town, 0);
        $dt_val_txt = number_format_ua($dt_val_town, 2);
        $kt_val_txt = number_format_ua($kt_val_town, 2);

        $e_dt_txt = number_format_ua($e_dt_town, 2);
        $e_kt_txt = number_format_ua($e_kt_town, 2);

        echo_html( "
                        <tr class='table_footer'>
                        <td colspan='6'> Всього по {$cur_town}</td>
                        <td class='c_n'>{$b_val_txt}</td>
                        <td class='c_i'>{$demand_txt}</td>
                        <td class='c_n'>{$dt_val_txt}</td>
                        <td class='c_n'>{$kt_val_txt}</td>
                        <td class='c_n'></td>
                        <td class='c_n'>{$e_val_txt}</td>
                        <td class='c_n'>{$e_dt_txt}</td>
                        <td class='c_n'>{$e_kt_txt}</td>
                    </tr>  ",$print_flag); 

    }
    
    
    $b_val_txt = number_format_ua ($b_val_sum,2);  
    $e_val_txt = number_format_ua ($e_val_sum,2);  
    $demand_txt = number_format_ua ($demand_sum,0);  
    $dt_val_txt = number_format_ua ($dt_val_sum,2);  
    $kt_val_txt = number_format_ua ($kt_val_sum,2);  
    
    $e_dt_txt = number_format_ua ($e_dt_sum,2);  
    $e_kt_txt = number_format_ua ($e_kt_sum,2);  
    
    echo_html( "
    <tr class='table_footer'>
    <td colspan='6'> Всього - {$abon_count}</td>
    <td class='c_n'>{$b_val_txt}</td>
    <td class='c_i'>{$demand_txt}</td>
    <td class='c_n'>{$dt_val_txt}</td>
    <td class='c_n'>{$kt_val_txt}</td>
    <td class='c_n'></td>
    <td class='c_n'>{$e_val_txt}</td>
    <td class='c_n'>{$e_dt_txt}</td>
    <td class='c_n'>{$e_kt_txt}</td>
    </tr>  ",$print_flag); 
    
    
    eval("\$footer_text_eval = \"$footer_text\";");
    echo $footer_text_eval;

    //return;
    
}
//-----------------------------------------------------------------------------
if (($oper=='meter_change')||($oper=='meter_change_zone'))
{
    //$p_mmgg = sql_field_val('dt_b', 'mmgg');
    //$mmgg1_str = trim($_POST['period_str']);
    
    $p_mmgg1 = sql_field_val('dt_b', 'mmgg');
    $p_mmgg2 = sql_field_val('dt_e', 'mmgg');
    
    $mmgg1_str = ukr_date($_POST['dt_b'],0,1);
    $mmgg2_str = ukr_date($_POST['dt_e'],0,1);

    $mmgg1_str = "$mmgg1_str - $mmgg2_str";    
    $p_id_tar = sql_field_val('id_gtar', 'int');        
    
    $p_id_town = sql_field_val('addr_town', 'int');
    //$p_id_sector = sql_field_val('id_sector', 'int');
    $p_book = sql_field_val('book', 'string');
    $p_id_sector = sql_field_val('id_sector', 'int');
    $p_id_region = sql_field_val('id_region', 'int');
    
    if ((isset($_POST['addr_town_name']))&&($_POST['addr_town_name']!=''))
        $params_caption = 'населений пункт: '. trim($_POST['addr_town_name']);
    else 
        $params_caption ='';
      
    if ((isset($_POST['gtar']))&&($_POST['gtar']!=''))
        $params_caption .= ' Тарифна група : '. trim($_POST['gtar']);

    $where = ' ';

    if ($p_id_tar!='null')
    {
        $where.= " and acc.id_gtar = $p_id_tar ";
    }    
    
    if ($p_id_town!='null')
    {
        $where.= "and adr.id_town = $p_id_town ";
    }
    
    if ($p_book!='null')
    {
        $where.= " and acc.book = $p_book ";
        
        $params_caption.= 'книга: '.$p_book;
    }
    
    if ((isset($_POST['sector']))&&($_POST['sector']!=''))
        $params_caption.= ' Дiльниця: '. trim($_POST['sector']);

   if ($p_id_sector!='null')
   {
      $where.= "and exists (select id from prs_runner_paccnt as rp 
          where rp.id_paccnt = acc.id and rp.id_sector = $p_id_sector ) ";
   }

   if ($p_id_region!='null')
   {
        $where.= "and exists (select rs.id from 
            prs_runner_paccnt as rp 
            join prs_runner_sectors as rs on (rs.id = rp.id_sector)
            where rp.id_paccnt = acc.id and rs.id_region =  $p_id_region ) ";

        $SQL_H="SELECT name from cli_region_tbl where id = $p_id_region ;";
    
        $result = pg_query($Link, $SQL_H) or die("SQL Error: " . pg_last_error($Link) . $SQL_H);
        $row = pg_fetch_array($result);
        
        $params_caption.= $row['name'];               
   }   
   
   if($oper=='meter_change_zone')
   {
       $params_caption.= '<br/> зміна зон лічильника';
   }
   
    eval("\$header_text_eval = \"$header_text\";");
    echo $header_text_eval;
    
    $SQL = " select w.*, acc.book, acc.code, tar.sh_nm as tarif_name,
    adr.town, adr.street, address_print(acc.addr) as house,
    (c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon,
    to_char(w.dt_work, 'DD.MM.YYYY') as dt_work_str
    from clm_works_tbl as w
join clm_paccnt_tbl as acc on (acc.id = w.id_paccnt)
join clm_abon_tbl as c on (c.id = acc.id_abon) 
join aqi_grptar_tbl as tar on (tar.id = acc.id_gtar)    
left join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class)
where w.idk_work in (1,2,3) and w.work_period >= $p_mmgg1 and w.work_period <= $p_mmgg2 $where
order by w.dt_work, acc.book, acc.code;";

   // throw new Exception(json_encode($SQL));
    
    $i=0;
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        while ($row = pg_fetch_array($result)) {

         $id_work=$row['id'] ;
            
         $SQL_old = " select i.*, m.name as type_meter, z.nm as zone
         from clm_work_indications_tbl as i
         left join eqi_meter_tbl as m on (m.id = i.id_type_meter)
         left join eqk_zone_tbl as z on (z.id = i.id_zone)
         where id_work = $id_work and idk_oper = 2
         order by z.id";   
            
            
         $SQL_new = " select i.*, m.name as type_meter, z.nm as zone
         from clm_work_indications_tbl as i
         left join eqi_meter_tbl as m on (m.id = i.id_type_meter)
         left join eqk_zone_tbl as z on (z.id = i.id_zone)
         where id_work = $id_work and idk_oper = 1
         order by z.id";   
            
         $result_old = pg_query($Link, $SQL_old) or die("SQL Error: " . pg_last_error($Link) . $SQL);   
         $result_new = pg_query($Link, $SQL_new) or die("SQL Error: " . pg_last_error($Link) . $SQL);               
            
         $rows_new_count = pg_num_rows($result_new);
         $rows_old_count = pg_num_rows($result_old);   
           
         $nn=0;
         while (($rows_new_count>0)||($rows_old_count>0))
         {
            if($row_new = pg_fetch_array($result_new))
            {
             $type_new = $row_new["type_meter"];
             $num_new  = $row_new["num_meter"];
             $zone_new = $row_new["zone"];
             $id_zone_new = $row_new["id_zone"];
             $ind_new  = $row_new["indic"];
            }
            else
            {
             $type_new = "";
             $num_new  = "";
             $zone_new = "";
             $ind_new  = "";
             $id_zone_new = "";
            }
         
            if($row_old = pg_fetch_array($result_old))
            {
             $type_old = $row_old["type_meter"];
             $num_old  = $row_old["num_meter"];
             $zone_old = $row_old["zone"];
             $id_zone_old = $row_old["id_zone"];
             $ind_old  = $row_old["indic"];
                
            }
            else
            {
             $type_old = "";
             $num_old  = "";
             $zone_old = "";
             $ind_old  = "";
             $id_zone_old = "";
            }

            $rows_new_count--;
            $rows_old_count--;
            
            if (($oper!='meter_change_zone')||(($id_zone_old !=$id_zone_new)&&
                    (($id_zone_old!='' && $id_zone_old!='0')||($id_zone_new!='' && $id_zone_new!='0' )) ))
            {
             $abon = htmlspecialchars($row['abon']);
             $addr = htmlspecialchars($row['town'].' '.$row['street'].' '.$row['house']);
             $book=$row['book'].'/'.$row['code'];
             $dt_work_str = $row['dt_work_str'];
             $tarif = htmlspecialchars($row['tarif_name']);
            
             echo_html( "
             <tr >
             <td class='c_t'>{$book}</td>
             <td>{$abon}</td>
             <td>{$addr}</td>
             <td>{$tarif}</td>
             <td>{$dt_work_str}</td>
            
             <td>{$type_new}</td>
             <td class='c_t'>{$num_new}</td>
             <td>{$zone_new}</td>
             <td class='c_r'>{$ind_new}</td>
            
             <td>{$type_old}</td>
             <td class='c_t'>{$num_old}</td>
             <td>{$zone_old}</td>
             <td class='c_r'>{$ind_old}</td>
             </tr>  ");        
             
             $nn=1;
            }
         }
         $i+=$nn;
        }
        
        
    }

    $cnt_txt = $i;    
    eval("\$footer_text_eval = \"$footer_text\";");
    echo $footer_text_eval;

    //return;
    
}
//----------------------------------------------------------------------------
if ($oper=='indic_summary_small')
{
    $p_mmgg = sql_field_val('dt_b', 'mmgg');
    $mmgg1_str = trim($_POST['period_str']);

    $p_id_sector = sql_field_val('id_sector', 'int');
    $p_book = sql_field_val('book', 'string');
    $p_id_region = sql_field_val('id_region', 'int');
    
    $params_caption= '';
    $where= '';
    
    if ((isset($_POST['sector']))&&($_POST['sector']!=''))
        $params_caption.= ' Дiльниця: '. trim($_POST['sector']);

    if ($p_book!='null')
    {
        $where.= " where c.book = $p_book ";
        $params_caption .= " книга : $p_book ";
        
    }

   if ($p_id_sector!='null')
   {
       
      if ($where =='') $where = ' where '; else $where = ' and ';
      
      $where.= " exists (select id from prs_runner_paccnt as rp 
          where rp.id_paccnt = c.id and rp.id_sector = $p_id_sector ) ";
   }
    
    if ($p_id_region!='null')
    {
        
        if ($where =='') $where = ' where '; else $where = ' and ';        
        
        $where.= " exists (select rs.id from 
            prs_runner_paccnt as rp 
            join prs_runner_sectors as rs on (rs.id = rp.id_sector)
            where rp.id_paccnt = c.id and rs.id_region =  $p_id_region ) ";

        $SQL_H="SELECT name from cli_region_tbl where id = $p_id_region ;";
    
        $result = pg_query($Link, $SQL_H) or die("SQL Error: " . pg_last_error($Link) . $SQL_H);
        $row = pg_fetch_array($result);
        
        $params_caption.= $row['name'];               
    }
    
    eval("\$header_text_eval = \"$header_text\";");
    echo $header_text_eval;
    
    //все учеты
    $SQL1 = " select count( distinct CASE WHEN coalesce(idk_house,1) = 1 and coalesce(id_extra,1)=1 THEN id END  ) as cnt_t_in,
	count( distinct CASE WHEN coalesce(idk_house,1) = 1 and coalesce(id_extra,1) in (2,3) THEN id END  ) as cnt_t_out,
	count( distinct CASE WHEN coalesce(idk_house,1) in (2,3,4,5) and coalesce(id_extra,1)=1 THEN id END  ) as cnt_v_in,
	count( distinct CASE WHEN coalesce(idk_house,1) in (2,3,4,5) and coalesce(id_extra,1) in (2,3) THEN id END  ) as cnt_v_out
    from (
    SELECT m.id_paccnt, c.idk_house, c.book, c.code, m.id, m.id_extra
    FROM clm_paccnt_h as c
    join (select id, max(dt_b) as dt_b from clm_paccnt_h  where 
    ((dt_b < ($p_mmgg::date+'1 month'::interval) and dt_e is null)
    or 
    tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
    ) and archive=0 and activ = true 
    group by id order by id) as c2 
    on (c.id = c2.id and c.dt_b = c2.dt_b)
    join clm_meterpoint_h as m on (m.id_paccnt=c.id)
    join (select id, max(dt_b) as dt_b from clm_meterpoint_h  where 
    ((dt_b < ($p_mmgg::date+'1 month'::interval) and dt_e is null)
    or 
    tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
    )
    group by id order by id) as m2 on (m.id = m2.id and m.dt_b = m2.dt_b)
    $where
    ) as sss;";
    
    // в том числе есть показания
    $SQL2 = " select count( distinct CASE WHEN coalesce(idk_house,1) = 1 and coalesce(id_extra,1)=1 THEN id END  ) as cnt_t_in,
	count( distinct CASE WHEN coalesce(idk_house,1) = 1 and coalesce(id_extra,1) in (2,3) THEN id END  ) as cnt_t_out,
	count( distinct CASE WHEN coalesce(idk_house,1) in (2,3,4,5) and coalesce(id_extra,1)=1 THEN id END  ) as cnt_v_in,
	count( distinct CASE WHEN coalesce(idk_house,1) in (2,3,4,5) and coalesce(id_extra,1) in (2,3) THEN id END  ) as cnt_v_out
    from (
    SELECT m.id_paccnt, c.idk_house, c.book, c.code, m.id, m.id_extra, ind.id_meter
    FROM clm_paccnt_h as c
    join (select id, max(dt_b) as dt_b from clm_paccnt_h  where 
    ((dt_b < ($p_mmgg::date+'1 month'::interval) and dt_e is null)
    or 
    tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
    ) and archive=0 and activ = true 
    group by id order by id) as c2 
    on (c.id = c2.id and c.dt_b = c2.dt_b)
    join clm_meterpoint_h as m on (m.id_paccnt=c.id)
    join (select id, max(dt_b) as dt_b from clm_meterpoint_h  where 
    ((dt_b < ($p_mmgg::date+'1 month'::interval) and dt_e is null)
    or 
    tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
    )
    group by id order by id) as m2 on (m.id = m2.id and m.dt_b = m2.dt_b)
    join (select distinct id_meter from acm_indication_tbl where mmgg = $p_mmgg order by id_meter) as ind on (ind.id_meter = m.id)
    $where
    ) as sss;";
    
    
    $result1 = pg_query($Link, $SQL1) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    $result2 = pg_query($Link, $SQL2) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    
    if ($result1&&$result2) {

        while ($row = pg_fetch_array($result1)) {

            $t_in_all=$row['cnt_t_in'];
            $t_out_all=$row['cnt_t_out'];

            $v_in_all=$row['cnt_v_in'];
            $v_out_all=$row['cnt_v_out'];
        }
 
        while ($row = pg_fetch_array($result2)) {

            $t_in_ind=$row['cnt_t_in'];
            $t_out_ind=$row['cnt_t_out'];

            $v_in_ind=$row['cnt_v_in'];
            $v_out_ind=$row['cnt_v_out'];
            
        }
        
        $cnt_all = $t_in_all+$t_out_all+$v_in_all+$v_out_all;
        
        if ($t_in_all!=0)
        {
            $t_in_proc=round($t_in_ind*100/$t_in_all,0);
        }
        ELSE
        {
            $t_in_proc='-';
        }

        if ($t_out_all!=0)
        {
            $t_out_proc=round($t_out_ind*100/$t_out_all,0);
        }
        ELSE
        {
            $t_out_proc='-';
        }


        
        if ($v_in_all!=0)
        {
            $v_in_proc=round($v_in_ind*100/$v_in_all,0);
        }
        ELSE
        {
            $v_in_proc='-';
        }

        if ($v_out_all!=0)
        {
            $v_out_proc=round($v_out_ind*100/$v_out_all,0);
        }
        ELSE
        {
            $v_out_proc='-';
        }
        
    }

    eval("\$footer_text_eval = \"$footer_text\";");
    echo $footer_text_eval;

    //return;
    
}
//------------------------------------------------------------------------------
if (($oper=='ktp_all')||($oper=='ktp_3')||($oper=='ktp_p')||($oper=='ktp_h')) 
{
    $p_mmgg = sql_field_val('dt_b', 'mmgg');
    $mmgg1_str = trim($_POST['period_str']);
    $p_id_region = sql_field_val('id_region', 'int');
    
    $params_caption = '';
    $mode_caption = ' ТП ';
    
    $where = '';
    $where2 ='';
    
    rep_abon_dt($Link, $p_mmgg,  2, 0);        
    
    if ($oper=='ktp_p') 
        {$where = " where tar.ident ~'tgr7_3' ";
         $params_caption = '<BR/> Електроплити ';  }
    if ($oper=='ktp_h') 
        {$where = " where tar.ident ~'tgr7_5' ";
         $params_caption = '<BR/> Електроопалення ';     }
    
    if ($oper=='ktp_3') 
    {
        $where2 = " where im.phase=2 ";
        $params_caption = '<BR/> Трифазні ';   
    }

    $p_id_town = sql_field_val('addr_town', 'int');
    
    if ((isset($_POST['addr_town_name']))&&($_POST['addr_town_name']!=''))
        $params_caption.= 'населений пункт: '. trim($_POST['addr_town_name']);
      
    if ($p_id_town!='null')
    {
        if ($where=='') $where.=' where ';
        else $where.=' and ';
        $where.= " adr.id_town = $p_id_town ";
    }
    
    if ($p_id_region!='null')
    {

        if ($where=='') $where.=' where ';
        else $where.=' and ';
        
        $where.= " exists (select rs.id from 
            prs_runner_paccnt as rp 
            join prs_runner_sectors as rs on (rs.id = rp.id_sector)
            where rp.id_paccnt = c.id and rs.id_region =  $p_id_region ) ";

        $SQL_H="SELECT name from cli_region_tbl where id = $p_id_region ;";
    
        $result = pg_query($Link, $SQL_H) or die("SQL Error: " . pg_last_error($Link) . $SQL_H);
        $row = pg_fetch_array($result);
        
        $params_caption.= $row['name'];               
    }    
    
    
    eval("\$header_text = \"$header_text\";");
    echo $header_text;
    
    $SQL = "select coalesce(tp_name,'- ТП не вказана') as tp_name, tp_power, 
        count(distinct CASE WHEN archive = 0 THEN id END ) as cnt_all,
        count(distinct CASE WHEN coalesce(demand,0)<>0 THEN id END) as cnt_bill,
	count(distinct CASE WHEN coalesce(kt_val,0)<>0 THEN id END) as cnt_pay,
        sum(dt_val- coalesce(value_subs,0)) as dt_val, sum(kt_val - coalesce(value_subs,0)) as kt_val,sum(e_val) as e_val,
	sum(demand) as demand,
        sum(abon_power) as abon_power, 
        sum(value_subs) as subs_val,
	count(distinct CASE WHEN coalesce(old_sum,0)<>0 and e_val <0 THEN id END) as cnt_pay3m,
	sum(CASE WHEN coalesce(old_sum,0)<>0 and e_val <0 THEN coalesce(old_sum,0) END) as sum_pay3m
    from
(
select c.id, tp.name as tp_name, tp.power as tp_power, abon_power, 
       s.dt_val, s.dt_valtax, s.kt_val, s.kt_valtax, -s.e_val as e_val, -s.e_valtax as e_valtax ,
       b.demand , ps.value_subs,  d_old.summ as old_sum,coalesce(c.archive,0) as archive
       from  clm_paccnt_h as c 
       join (select id, max(dt_b) as dt_b from clm_paccnt_h  where 
        ((dt_b < ($p_mmgg::date+'1 month'::interval) and dt_e is null)
            or 
            tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
        )
         group by id order by id) as c2 
        on (c.id = c2.id and c.dt_b = c2.dt_b)
	join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class)
	join aqi_grptar_tbl as tar on (tar.id = c.id_gtar)
        join acm_saldo_tbl as s on (s.id_paccnt = c.id and s.mmgg = $p_mmgg and s.id_pref=10)
       left join
       (  select m.id_paccnt, max(m.id_station) as id_station, max(m.power) as abon_power
          from clm_meterpoint_h as m 
          join (select id, max(dt_b) as dt_b from clm_meterpoint_h  where 
          ((dt_b < ($p_mmgg::date+'1 month'::interval) and dt_e is null)
            or 
            tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
          )
          group by id order by id) as m2 
             on (m.id = m2.id and m.dt_b = m2.dt_b)
          left join eqi_meter_tbl as im on (im.id = m.id_type_meter)
          $where2
          group by m.id_paccnt
        ) as mm on (mm.id_paccnt = c.id) 
    
        left join rep_abon_dt_tbl as d_old on (d_old.id_paccnt = c.id)
        left join eqm_tp_tbl as tp on (tp.id = mm.id_station)    
	left join (select id_paccnt, sum(demand) as demand from acm_bill_tbl where mmgg = $p_mmgg and demand <>0 and id_pref=10 
            group by id_paccnt order by id_paccnt) as b
             on (b.id_paccnt = c.id)
        left join (
            select p.id_paccnt, sum(p.value) as value_subs
            from acm_pay_tbl as p 
            where p.mmgg = $p_mmgg::date and p.id_pref = 10 and p.idk_doc in (110,111,193,194)
       	group by p.id_paccnt
	order by id_paccnt
       ) as ps on (ps.id_paccnt = c.id)
        $where
 ) as ss       
group by tp_name,tp_power
order by tp_name
";

   // throw new Exception(json_encode($SQL));
    $sum_cnt_all=0;
    $sum_cnt_bill=0;
    $sum_cnt_pay=0;
    
    $sum_dem_bill=0;
    $sum_summ_bill=0;
    
    $sum_summ_pay=0;
    $sum_summ_subs=0;
    
    $sum_saldo=0;
    
    $sum_cnt_3=0;
    $sum_saldo_3=0;
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        $i=0;
        while ($row = pg_fetch_array($result)) {

            $town = htmlspecialchars($row['tp_name']);

            $dt_val_txt = number_format_ua ($row['dt_val'],2);
            $kt_val_txt = number_format_ua ($row['kt_val'],2);
            $subs_val_txt = number_format_ua ($row['subs_val'],2);
            $e_val_txt = number_format_ua ($row['e_val'],2);  
            $sum_pay3m_txt = number_format_ua ($row['sum_pay3m'],2);  
            
            if ($row['cnt_bill']!=0)
                $avg_dem_sum = round($row['dt_val']/$row['cnt_bill'],2);
            else
                $avg_dem_sum =0;
            
            $avg_dem_sum_txt = number_format_ua ($avg_dem_sum,2);
            
            if ($row['cnt_pay']!=0)
                $avg_pay_sum = round($row['kt_val']/$row['cnt_pay'],2);
            else
                $avg_pay_sum =0;
            
            $avg_pay_sum_txt = number_format_ua ($avg_pay_sum,2);
            
            if ($row['dt_val']!=0)
                $pay_perc = round($row['kt_val']*100/$row['dt_val'],2);
            else
                $pay_perc =0;
            
            $pay_perc_txt = number_format_ua ($pay_perc,2);
            
            $abon_power_txt = number_format_ua ($row['abon_power'],2);
            
            $sum_cnt_all+=$row['cnt_all'];
            $sum_cnt_bill+=$row['cnt_bill'];
            $sum_cnt_pay+=$row['cnt_pay'];
            
            $sum_dem_bill+=$row['demand'];
            $sum_summ_bill+=$row['dt_val'];
    
            $sum_summ_pay+=$row['kt_val'];
            $sum_summ_subs+=$row['subs_val'];
    
            $sum_saldo+=$row['e_val'];
    
            $sum_cnt_3+=$row['cnt_pay3m'];
            $sum_saldo_3+=$row['sum_pay3m'];
            
            
            echo "
            <tr >
            <td>$town</td>
            <td class='c_i'>{$row['tp_power']}</td>
            <td class='c_i'>{$row['cnt_all']}</td>
            <td class='c_i'>{$abon_power_txt}</td>
            <td class='c_i'>{$row['cnt_bill']}</td>
            <td class='c_i'>{$row['demand']}</td>
            <td class='c_n'>{$dt_val_txt}</td>
            <td class='c_n'>{$avg_dem_sum_txt}</td>
            <td class='c_i'>{$row['cnt_pay']}</td>
            <td class='c_n'>{$kt_val_txt}</td>
            <td class='c_n'>{$subs_val_txt}</td>
            <td class='c_n'>{$avg_pay_sum_txt}</td>            
            <td class='c_n'>{$pay_perc_txt}</td>            
            <td class='c_n'>{$e_val_txt}</td>            
            <td class='c_i'>{$row['cnt_pay3m']}</td>            
            <td class='c_n'>{$sum_pay3m_txt}</td>            
            </tr>  ";        
            $i++;
        }
        
        
    }

    $dt_val_txt = number_format_ua ($sum_summ_bill, 2);
    $kt_val_txt = number_format_ua ($sum_summ_pay, 2);
    $e_val_txt = number_format_ua ($sum_saldo, 2);
    $sum_pay3m_txt = number_format_ua ($sum_saldo_3, 2);
    $subs_val_txt = number_format_ua ($sum_summ_subs, 2);

    if ($sum_cnt_bill!=0)
        $avg_dem_sum = round($sum_summ_bill / $sum_cnt_bill, 2);
    else
        $avg_dem_sum =0;
    
    $avg_dem_sum_txt = number_format_ua ($avg_dem_sum, 2);

    if ($sum_cnt_pay!=0)
        $avg_pay_sum = round($sum_summ_pay / $sum_cnt_pay, 2);
    else
        $avg_pay_sum=0;
    
    $avg_pay_sum_txt = number_format_ua ($avg_pay_sum, 2);

    if ($sum_summ_bill!=0)
        $pay_perc = round($sum_summ_pay * 100 / $sum_summ_bill, 2);
    else
        $pay_perc = 0;
    
    $pay_perc_txt = number_format_ua ($pay_perc, 2);
    
    eval("\$footer_text_eval = \"$footer_text\";");
    echo $footer_text_eval;
   
}
//------------------------------------------------------------------------------
if ($oper=='lgt_dod3')
{
    $p_mmgg = sql_field_val('dt_b', 'mmgg');
    $mmgg1_str = trim($_POST['period_str']);
    $p_id_sector = sql_field_val('id_sector', 'int');
    $p_book = sql_field_val('book', 'string');
    $p_id_region = sql_field_val('id_region', 'int');
    
    $params_caption = '';

    $where = '';

    $p_id_town = sql_field_val('addr_town', 'int');
    
    if ((isset($_POST['addr_town_name']))&&($_POST['addr_town_name']!=''))
        $params_caption.= 'населений пункт: '. trim($_POST['addr_town_name']);
      
    if ((isset($_POST['sector']))&&($_POST['sector']!=''))
        $params_caption.= ' Дiльниця: '. trim($_POST['sector']);

    if ($p_book!='null')
    {
        $where.= " and book = $p_book ";
        $params_caption .= " книга : $p_book ";
        
    }

   if ($p_id_sector!='null')
   {
      $where.= "and exists (select id from prs_runner_paccnt as rp 
          where rp.id_paccnt = a.id and rp.id_sector = $p_id_sector ) ";
   }
    
    if ($p_id_region!='null')
    {
        
        $where.= "and exists (select rs.id from 
            prs_runner_paccnt as rp 
            join prs_runner_sectors as rs on (rs.id = rp.id_sector)
            where rp.id_paccnt = a.id and rs.id_region =  $p_id_region ) ";

        $SQL_H="SELECT name from cli_region_tbl where id = $p_id_region ;";
    
        $result = pg_query($Link, $SQL_H) or die("SQL Error: " . pg_last_error($Link) . $SQL_H);
        $row = pg_fetch_array($result);
        
        $params_caption.= $row['name'];        
       
    }   
    
    if ($p_id_town!='null')
    {
        $where.=' and ';
        $where.= " ( adr.id_town = $p_id_town or adr.id_parent = $p_id_town ) ";
    }
    
    eval("\$header_text = \"$header_text\";");
    echo $header_text;
    
    $SQL = "select g.name, g.ident,
sum(demand_lgt) as demand_lgt, 
 sum( CASE WHEN gt.ident~'tgr7_1' or (adr.is_town = 1 and gt.ident~'tgr7_6') THEN demand_lgt END) as town,
 sum( CASE WHEN (adr.is_town = 1 and gt.ident~'tgr7_3') or ((gt.ident~'tgr7_51') or (gt.ident~'tgr7_53')) THEN demand_lgt END) as town_h,
 sum( CASE WHEN gt.ident~'tgr7_2' or (adr.is_town = 0 and gt.ident~'tgr7_6') THEN demand_lgt END) as village,
 sum( CASE WHEN (adr.is_town = 0 and gt.ident~'tgr7_3') or (gt.ident~'tgr7_52')  THEN demand_lgt END) as village_h,
count(distinct s.id_paccnt) as cnt,
 count(distinct  CASE WHEN gt.ident~'tgr7_1' or (adr.is_town = 1 and gt.ident~'tgr7_6') THEN s.id_paccnt END) as town_cnt,
 count(distinct  CASE WHEN (adr.is_town = 1 and gt.ident~'tgr7_3') or ((gt.ident~'tgr7_51') or (gt.ident~'tgr7_53'))  THEN s.id_paccnt END) as town_h_cnt,
 count(distinct  CASE WHEN gt.ident~'tgr7_2' or (adr.is_town = 0 and gt.ident~'tgr7_6') THEN s.id_paccnt END) as village_cnt,
 count(distinct  CASE WHEN (adr.is_town = 0 and gt.ident~'tgr7_3') or (gt.ident~'tgr7_52')  THEN s.id_paccnt END) as village_h_cnt
    from acm_lgt_summ_tbl as s 
    join acm_bill_tbl as b on (b.id_doc = s.id_doc)
    join lgi_group_tbl as g on (s.id_grp_lgt = g.id)

    join clm_paccnt_h as a on (a.id = s.id_paccnt)
    join (select id, max(dt_b) as dt_b from clm_paccnt_h  where 
            ((dt_b < ($p_mmgg::date+'1 month'::interval) and dt_e is null)
            or 
            tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
        ) group by id order by id) as a2 
       on (a.id = a2.id and a.dt_b = a2.dt_b)
    
    join adt_addr_town_street_tbl as adr on (adr.id = (a.addr).id_class)
    
    left join aqm_tarif_tbl as t on (t.id = s.id_tarif)
    left join aqi_grptar_tbl as gt on (gt.id = coalesce(t.id_grptar, a.id_gtar ))
    
    where s.mmgg = $p_mmgg and b.id_pref = 10 and b.idk_doc in (200,220)
    $where
     group by g.name, g.ident
 order by g.ident::int, g.name ;";
/*
    // отдельно доп льготы 
    $SQL2 = "select g.name, g.ident,
sum(b.demand) as demand_lgt, 
 sum( CASE WHEN gt.ident~'tgr7_1' THEN b.demand END) as town,
 sum( CASE WHEN adr.is_town = 1 and gt.ident in ('tgr7_3','tgr7_5','tgr7_35','tgr7_53','tgr7_6','tgr7_63')  THEN b.demand END) as town_h,
 sum( CASE WHEN gt.ident~'tgr7_2' THEN b.demand END) as village,
 sum( CASE WHEN adr.is_town = 0 and gt.ident in ('tgr7_3','tgr7_5','tgr7_35','tgr7_53','tgr7_6','tgr7_63')  THEN b.demand END) as village_h,
count(distinct s.id_paccnt) as cnt,
 count(distinct  CASE WHEN gt.ident~'tgr7_1' THEN s.id_paccnt END) as town_cnt,
 count(distinct  CASE WHEN adr.is_town = 1 and gt.ident in ('tgr7_3','tgr7_5','tgr7_35','tgr7_53','tgr7_6','tgr7_63')  THEN s.id_paccnt END) as town_h_cnt,
 count(distinct  CASE WHEN gt.ident~'tgr7_2' THEN s.id_paccnt END) as village_cnt,
 count(distinct  CASE WHEN adr.is_town = 0 and gt.ident in ('tgr7_3','tgr7_5','tgr7_35','tgr7_53','tgr7_6','tgr7_63')  THEN s.id_paccnt END) as village_h_cnt
    from acm_dop_lgt_tbl as s 
    join clm_paccnt_h as a on (a.id = s.id_paccnt)
    join (select id, max(dt_b) as dt_b from clm_paccnt_h  where 
            ((dt_b < ($p_mmgg::date+'1 month'::interval) and dt_e is null)
            or 
            tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
        ) group by id order by id) as a2 
       on (a.id = a2.id and a.dt_b = a2.dt_b)
    join adt_addr_town_street_tbl as adr on (adr.id = (a.addr).id_class)
    join acm_bill_tbl as b on (b.id_paccnt = a.id)
    join lgi_group_tbl as g on (s.id_grp_lgt = g.id)
    join aqi_grptar_tbl as gt on (gt.id = a.id_gtar)
    where s.mmgg = $p_mmgg and b.id_pref = 10 $where
     group by g.name, g.ident
 order by g.ident::int, g.name ;";
*/    
    
   // throw new Exception(json_encode($SQL));
    $sum_cnt_all=0;
    $sum_cnt_town=0;
    $sum_cnt_town_h=0;
    $sum_cnt_vil=0;
    $sum_cnt_vil_h=0;

    $sum_dem_all=0;
    $sum_dem_town=0;
    $sum_dem_town_h=0;
    $sum_dem_vil=0;
    $sum_dem_vil_h=0;
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        $i=0;
        while ($row = pg_fetch_array($result)) {

            $name = htmlspecialchars($row['name']);
            
            $sum_cnt_all+=$row['cnt'];
            $sum_cnt_town+=$row['town_cnt'];
            $sum_cnt_town_h+=$row['town_h_cnt'];
            $sum_cnt_vil+=$row['village_cnt'];
            $sum_cnt_vil_h+=$row['village_h_cnt'];

            $sum_dem_all+=$row['demand_lgt'];
            $sum_dem_town+=$row['town'];
            $sum_dem_town_h+=$row['town_h'];
            $sum_dem_vil+=$row['village'];
            $sum_dem_vil_h+=$row['village_h'];
            
            echo "
            <tr >
            <td>$name</td>
            <td class='c_i'>{$row['cnt']}</td>
            <td class='c_i'>{$row['town_cnt']}</td>
            <td class='c_i'>{$row['town_h_cnt']}</td>
            <td class='c_i'>{$row['village_cnt']}</td>
            <td class='c_i'>{$row['village_h_cnt']}</td>
            
            <td class='c_r'>{$row['demand_lgt']}</td>
            <td class='c_r'>{$row['town']}</td>
            <td class='c_r'>{$row['town_h']}</td>
            <td class='c_r'>{$row['village']}</td>
            <td class='c_r'>{$row['village_h']}</td>
            </tr>  ";        
            $i++;
        }
    }
/*
    $result = pg_query($Link, $SQL2) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count += pg_num_rows($result);
        
        $i=0;
        while ($row = pg_fetch_array($result)) {

            $name = htmlspecialchars($row['name']);
            
            $sum_cnt_all+=$row['cnt'];
            $sum_cnt_town+=$row['town_cnt'];
            $sum_cnt_town_h+=$row['town_h_cnt'];
            $sum_cnt_vil+=$row['village_cnt'];
            $sum_cnt_vil_h+=$row['village_h_cnt'];

            $sum_dem_all+=$row['demand_lgt'];
            $sum_dem_town+=$row['town'];
            $sum_dem_town_h+=$row['town_h'];
            $sum_dem_vil+=$row['village'];
            $sum_dem_vil_h+=$row['village_h'];
            
            echo "
            <tr >
            <td>$name</td>
            <td class='c_i'>{$row['cnt']}</td>
            <td class='c_i'>{$row['town_cnt']}</td>
            <td class='c_i'>{$row['town_h_cnt']}</td>
            <td class='c_i'>{$row['village_cnt']}</td>
            <td class='c_i'>{$row['village_h_cnt']}</td>
            
            <td class='c_r'>{$row['demand_lgt']}</td>
            <td class='c_r'>{$row['town']}</td>
            <td class='c_r'>{$row['town_h']}</td>
            <td class='c_r'>{$row['village']}</td>
            <td class='c_r'>{$row['village_h']}</td>
            </tr>  ";        
            $i++;
        }
    }
    */
    eval("\$footer_text_eval = \"$footer_text\";");
    echo $footer_text_eval;
   
}
//------------------------------------------------------------------------------
if ($oper=='lgt_dod2')
{
    $p_mmgg = sql_field_val('dt_b', 'mmgg');
    $mmgg1_str = trim($_POST['period_str']);
    $p_id_sector = sql_field_val('id_sector', 'int');
    $p_book = sql_field_val('book', 'string');
    $p_id_region = sql_field_val('id_region', 'int');
    
    $params_caption = '';

    $where = '';

    $p_id_town = sql_field_val('addr_town', 'int');
    
    if ((isset($_POST['addr_town_name']))&&($_POST['addr_town_name']!=''))
        $params_caption.= 'населений пункт: '. trim($_POST['addr_town_name']);
      
    if ($p_id_town!='null')
    {
        $where.= " and ( adr.id_town = $p_id_town or adr.id_parent = $p_id_town ) ";
    }
    
    if ((isset($_POST['sector']))&&($_POST['sector']!=''))
        $params_caption.= ' Дiльниця: '. trim($_POST['sector']);

    if ($p_book!='null')
    {
        $where.= " and book = $p_book ";
        $params_caption .= " книга : $p_book ";
        
    }

   if ($p_id_sector!='null')
   {
      $where.= "and exists (select id from prs_runner_paccnt as rp 
          where rp.id_paccnt = c.id and rp.id_sector = $p_id_sector ) ";
   }
   
    if ($p_id_region!='null')
    {
        
        $where.= "and exists (select rs.id from 
            prs_runner_paccnt as rp 
            join prs_runner_sectors as rs on (rs.id = rp.id_sector)
            where rp.id_paccnt = c.id and rs.id_region =  $p_id_region ) ";

        $SQL_H="SELECT name from cli_region_tbl where id = $p_id_region ;";
    
        $result = pg_query($Link, $SQL_H) or die("SQL Error: " . pg_last_error($Link) . $SQL_H);
        $row = pg_fetch_array($result);
        
        $params_caption.= $row['name'];        
       
    }   
    
    eval("\$header_text = \"$header_text\";");
    echo $header_text;
    
 //численность всего   
$SQL = " select 
 count(distinct  CASE WHEN gt.ident~'tgr7_1' or (adr.is_town = 1 and (gt.ident~'tgr7_6' or gt.ident~'tgr7_3')) THEN c.id END) as town_cnt,
 count(distinct  CASE WHEN (gt.ident~'tgr7_51') or (gt.ident~'tgr7_53') THEN c.id END) as town_h_cnt,
 count(distinct  CASE WHEN gt.ident~'tgr7_2' or (adr.is_town = 0 and (gt.ident~'tgr7_6' or gt.ident~'tgr7_3')) THEN c.id END) as village_cnt,
 count(distinct  CASE WHEN (gt.ident~'tgr7_52') THEN c.id END) as village_h_cnt
 from 
 clm_paccnt_h as c
    join (select id, max(dt_b) as dt_b from clm_paccnt_h  where 
    ((dt_b < ($p_mmgg::date+'1 month'::interval) and dt_e is null)
    or 
    tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
    ) and archive=0 
    group by id order by id) as c2 
    on (c.id = c2.id and c.dt_b = c2.dt_b)
 join aqi_grptar_tbl as gt on (gt.id = c.id_gtar)
 join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class) where c.archive=0 $where ; ";   
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        while ($row = pg_fetch_array($result)) {

            $cnt_t=$row['town_cnt'];
            $cnt_th=$row['town_h_cnt'];
            $cnt_v=$row['village_cnt'];
            $cnt_vh=$row['village_h_cnt'];
            
            $cnt_all = $cnt_t+$cnt_th+$cnt_v+$cnt_vh;
        }
        
    }
    
//численность оплативших
$SQL = " select 
 count(distinct  CASE WHEN gt.ident~'tgr7_1' or (adr.is_town = 1 and (gt.ident~'tgr7_6' or gt.ident~'tgr7_3')) THEN c.id END) as town_cnt,
 count(distinct  CASE WHEN (gt.ident~'tgr7_51') or (gt.ident~'tgr7_53')   THEN c.id END) as town_h_cnt,
 count(distinct  CASE WHEN gt.ident~'tgr7_2' or (adr.is_town = 0 and (gt.ident~'tgr7_6' or gt.ident~'tgr7_3')) THEN c.id END) as village_cnt,
 count(distinct  CASE WHEN (gt.ident~'tgr7_52')  THEN c.id END) as village_h_cnt
 from 
 clm_paccnt_h as c
    join (select id, max(dt_b) as dt_b from clm_paccnt_h  where 
    ((dt_b < ($p_mmgg::date+'1 month'::interval) and dt_e is null)
    or 
    tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
    ) and archive=0 
    group by id order by id) as c2 
    on (c.id = c2.id and c.dt_b = c2.dt_b)
 join (select distinct id_paccnt from acm_pay_tbl where mmgg = $p_mmgg and value <>0 and id_pref=10 
      order by id_paccnt) as p on (p.id_paccnt = c.id)
 join aqi_grptar_tbl as gt on (gt.id = c.id_gtar)
 join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class) where c.archive=0 $where ; ";   
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        while ($row = pg_fetch_array($result)) {

            $cnt_pay_t=$row['town_cnt'];
            $cnt_pay_th=$row['town_h_cnt'];
            $cnt_pay_v=$row['village_cnt']; 
            $cnt_pay_vh=$row['village_h_cnt'];
            
            $cnt_pay_all = $cnt_pay_t+$cnt_pay_th+$cnt_pay_v+$cnt_pay_vh;
        }
        
    }
    
//численность льготников
$SQL = " select 
 count(distinct  CASE WHEN gt.ident~'tgr7_1' or (adr.is_town = 1 and (gt.ident~'tgr7_6' or gt.ident~'tgr7_3')) THEN c.id END) as town_cnt,
 count(distinct  CASE WHEN (gt.ident~'tgr7_51') or (gt.ident~'tgr7_53')  THEN c.id END) as town_h_cnt,
 count(distinct  CASE WHEN gt.ident~'tgr7_2' or (adr.is_town = 0 and (gt.ident~'tgr7_6' or gt.ident~'tgr7_3')) THEN c.id END) as village_cnt,
 count(distinct  CASE WHEN (gt.ident~'tgr7_52')  THEN c.id END) as village_h_cnt
 from 
 clm_paccnt_h as c
    join (select id, max(dt_b) as dt_b from clm_paccnt_h  where 
    ((dt_b < ($p_mmgg::date+'1 month'::interval) and dt_e is null)
    or 
    tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
    ) and archive=0 
    group by id order by id) as c2 
    on (c.id = c2.id and c.dt_b = c2.dt_b)
 join aqi_grptar_tbl as gt on (gt.id = c.id_gtar)
 join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class) 
    
 left join ( select distinct id_paccnt from acm_dop_lgt_tbl where mmgg = $p_mmgg::date) as dlg on (c.id = dlg.id_paccnt)
 left join 
    ( select distinct id_paccnt from acm_lgt_summ_tbl as ls where mmgg = $p_mmgg::date ) as lg on (lg.id_paccnt = c.id)    
 where c.archive=0 and ((dlg.id_paccnt is not null) or (lg.id_paccnt is not null))
    $where ; ";   


/*
  left join 
    ( select distinct id_paccnt
     from lgm_abon_h as lg 
     join (select id, max(dt_b) as dt_b from lgm_abon_h  where
            ((dt_b < ($p_mmgg::date+'1 month'::interval) and dt_e is null)
            or 
            tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
        )   group by id order by id) as lg2 
     on (lg.id = lg2.id and lg.dt_b = lg2.dt_b)    
    ) as lg on (lg.id_paccnt = c.id)    

 */

    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        while ($row = pg_fetch_array($result)) {

            $cnt_lgt_t=$row['town_cnt'];
            $cnt_lgt_th=$row['town_h_cnt'];
            $cnt_lgt_v=$row['village_cnt']; 
            $cnt_lgt_vh=$row['village_h_cnt'];
            
            $cnt_lgt_all = $cnt_lgt_t+$cnt_lgt_th+$cnt_lgt_v+$cnt_lgt_vh;
        }
        
    }
/*
//численность льготников (доп льгота)
$SQL = " select 
 count(distinct  CASE WHEN gt.ident~'tgr7_1' THEN c.id END) as town_cnt,
 count(distinct  CASE WHEN adr.is_town = 1 and gt.ident in ('tgr7_3','tgr7_5','tgr7_35','tgr7_53','tgr7_6','tgr7_63')  THEN c.id END) as town_h_cnt,
 count(distinct  CASE WHEN gt.ident~'tgr7_2' THEN c.id END) as village_cnt,
 count(distinct  CASE WHEN adr.is_town = 0 and gt.ident in ('tgr7_3','tgr7_5','tgr7_35','tgr7_53','tgr7_6','tgr7_63')  THEN c.id END) as village_h_cnt
 from 
 clm_paccnt_h as c
    join (select id, max(dt_b) as dt_b from clm_paccnt_h  where 
    ((dt_b < ($p_mmgg::date+'1 month'::interval) and dt_e is null)
    or 
    tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
    ) and archive=0 
    group by id order by id) as c2 
    on (c.id = c2.id and c.dt_b = c2.dt_b)
 join ( select distinct id_paccnt from acm_dop_lgt_tbl where mmgg = $p_mmgg::date) as lg on (c.id = lg.id_paccnt)        
 join aqi_grptar_tbl as gt on (gt.id = c.id_gtar)
 join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class) 
 left join 
    ( select distinct id_paccnt
     from lgm_abon_h as lg 
     join (select id, max(dt_b) as dt_b from lgm_abon_h  where
            ((dt_b < ($p_mmgg::date+'1 month'::interval) and dt_e is null)
            or 
            tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
        )   group by id order by id) as lg2 
     on (lg.id = lg2.id and lg.dt_b = lg2.dt_b)    
    ) as lg on (lg.id_paccnt = bs.id_paccnt)    
    
    
    $where ; ";   
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        while ($row = pg_fetch_array($result)) {

            $cnt_lgt_t+=$row['town_cnt'];
            $cnt_lgt_th+=$row['town_h_cnt'];
            $cnt_lgt_v+=$row['village_cnt']; 
            $cnt_lgt_vh+=$row['village_h_cnt'];
            
            $cnt_lgt_all+= $row['town_cnt']+$row['town_h_cnt']+$row['village_cnt']+$row['village_h_cnt'];
        }
        
    }
    */
 //Потребление всего   
$SQL = " select 
 sum( CASE WHEN gt.ident~'tgr7_1' or (adr.is_town = 1 and (gt.ident~'tgr7_6' or gt.ident~'tgr7_3')) THEN demand END) as town,
 sum( CASE WHEN (gt.ident~'tgr7_51') or (gt.ident~'tgr7_53')  THEN demand END) as town_h,
 sum( CASE WHEN gt.ident~'tgr7_2' or (adr.is_town = 0 and (gt.ident~'tgr7_6' or gt.ident~'tgr7_3')) THEN demand END) as village,
 sum( CASE WHEN (gt.ident~'tgr7_52') THEN demand END) as village_h
 from 
 clm_paccnt_h as c
    join (select id, max(dt_b) as dt_b from clm_paccnt_h  where 
    ((dt_b < ($p_mmgg::date+'1 month'::interval) and dt_e is null)
    or 
    tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
    ) and archive=0 
    group by id order by id) as c2 
    on (c.id = c2.id and c.dt_b = c2.dt_b)
 join ( 
   select b.id_doc , b.id_paccnt, t.id_grptar , bs.demand
        from acm_bill_tbl as b 
	join acm_summ_tbl as bs on (b.id_doc= bs.id_doc) 
	join aqm_tarif_tbl as t on (t.id = bs.id_tarif)
	where b.idk_doc in (200,220) and b.id_pref = 10 
        and b.mmgg = $p_mmgg 
 ) as bb on (bb.id_paccnt = c.id)
 join aqi_grptar_tbl as gt on (gt.id = bb.id_grptar)
 join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class) where c.archive=0 $where ; ";
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        while ($row = pg_fetch_array($result)) {

            $dem_t=$row['town'];
            $dem_th=$row['town_h'];
            $dem_v=$row['village'];
            $dem_vh=$row['village_h'];
            
            $dem_all = $dem_t+$dem_th+$dem_v+$dem_vh;
        }
        
    }
    
 //Потребление льготников   
$SQL = " select 
 sum( CASE WHEN gt.ident~'tgr7_1' or (adr.is_town = 1 and (gt.ident~'tgr7_6' or gt.ident~'tgr7_3')) THEN demand END) as town,
 sum( CASE WHEN (gt.ident~'tgr7_51') or (gt.ident~'tgr7_53')  THEN demand END) as town_h,
 sum( CASE WHEN gt.ident~'tgr7_2' or (adr.is_town = 0 and (gt.ident~'tgr7_6' or gt.ident~'tgr7_3')) THEN demand END) as village,
 sum( CASE WHEN (gt.ident~'tgr7_52')  THEN demand END) as village_h
 from 
 clm_paccnt_h as c
    join (select id, max(dt_b) as dt_b from clm_paccnt_h  where 
    ((dt_b < ($p_mmgg::date+'1 month'::interval) and dt_e is null)
    or 
    tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
    ) and archive=0 
    group by id order by id) as c2 
    on (c.id = c2.id and c.dt_b = c2.dt_b)
 join ( 
   select b.id_doc , b.id_paccnt, t.id_grptar , bs.demand
        from acm_bill_tbl as b 
	join acm_summ_tbl as bs on (b.id_doc= bs.id_doc) 
	join aqm_tarif_tbl as t on (t.id = bs.id_tarif)
	where b.idk_doc in (200,220) and b.id_pref = 10 
        and exists (select id_doc from acm_lgt_summ_tbl as ls where ls.id_doc = b.id_doc)
        and b.mmgg = $p_mmgg 
 ) as bb on (bb.id_paccnt = c.id)
 join aqi_grptar_tbl as gt on (gt.id = bb.id_grptar)
 join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class) where c.archive=0 $where ; ";   
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        while ($row = pg_fetch_array($result)) {

            $dem_lgt_t=$row['town'];
            $dem_lgt_th=$row['town_h'];
            $dem_lgt_v=$row['village'];
            $dem_lgt_vh=$row['village_h'];
            
            $dem_lgt_all = $dem_lgt_t+$dem_lgt_th+$dem_lgt_v+$dem_lgt_vh;
        }
        
    }
/*
 //Потребление льготников (доп льгота)  
$SQL = " select 
 sum( CASE WHEN gt.ident~'tgr7_1' THEN demand END) as town,
 sum( CASE WHEN adr.is_town = 1 and gt.ident in ('tgr7_3','tgr7_5','tgr7_35','tgr7_53','tgr7_6','tgr7_63')  THEN demand END) as town_h,
 sum( CASE WHEN gt.ident~'tgr7_2' THEN demand END) as village,
 sum( CASE WHEN adr.is_town = 0 and gt.ident in ('tgr7_3','tgr7_5','tgr7_35','tgr7_53','tgr7_6','tgr7_63')  THEN demand END) as village_h
 from 
 clm_paccnt_h as c
    join (select id, max(dt_b) as dt_b from clm_paccnt_h  where 
    ((dt_b < ($p_mmgg::date+'1 month'::interval) and dt_e is null)
    or 
    tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
    ) and archive=0 
    group by id order by id) as c2 
    on (c.id = c2.id and c.dt_b = c2.dt_b)
 join ( select distinct id_paccnt from acm_dop_lgt_tbl where mmgg = $p_mmgg::date) as lg on (c.id = lg.id_paccnt)            
 join ( 
   select b.id_doc , b.id_paccnt, t.id_grptar , bs.demand
        from acm_bill_tbl as b 
	join acm_summ_tbl as bs on (b.id_doc= bs.id_doc) 
	join aqm_tarif_tbl as t on (t.id = bs.id_tarif)
	where b.idk_doc = 200 and bs.demand <>0 and b.id_pref = 10 
        and b.mmgg = $p_mmgg 
 ) as bb on (bb.id_paccnt = c.id)
 join aqi_grptar_tbl as gt on (gt.id = bb.id_grptar)
 join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class) $where ; ";   
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        while ($row = pg_fetch_array($result)) {

            $dem_lgt_t+=$row['town'];
            $dem_lgt_th+=$row['town_h'];
            $dem_lgt_v+=$row['village'];
            $dem_lgt_vh+=$row['village_h'];
            
            $dem_lgt_all += $row['town']+$row['town_h']+$row['village']+$row['village_h'];
        }
        
    }
   */ 
    eval("\$footer_text_eval = \"$footer_text\";");
    echo $footer_text_eval;   
}
//------------------------------------------------------------------------------

if (($oper=='lgt_dod4')||($oper=='lgt_dod4_tar'))
{
    $p_mmgg = sql_field_val('dt_b', 'mmgg');
    $mmgg1_str = trim($_POST['period_str']);
    $p_id_sector = sql_field_val('id_sector', 'int');
    $p_book = sql_field_val('book', 'string');
    $p_id_region = sql_field_val('id_region', 'int');
    
    list($year, $month, $day  ) = split('[/.-]', $p_mmgg);
    $year = trim($year,"'");

    if (((($year+0)==2017) && (($month+0) >= 3 ))||(($year+0)>2017))    
    {
        $time_ident=1;
    }
    else
    {
        $time_ident=0;
    }
    
    $params_caption = '';

    $where = '';

    $p_id_town = sql_field_val('addr_town', 'int');
    
    if ((isset($_POST['addr_town_name']))&&($_POST['addr_town_name']!=''))
        $params_caption.= 'населений пункт: '. trim($_POST['addr_town_name']);
      
    if ($p_id_town!='null')
    {
        $where.=' and ';
        $where.= " ( adr.id_town = $p_id_town or adr.id_parent = $p_id_town ) ";
    }
    
    if ((isset($_POST['sector']))&&($_POST['sector']!=''))
        $params_caption.= ' Дiльниця: '. trim($_POST['sector']);

    if ($p_book!='null')
    {
        $where.= " and book = $p_book ";
        $params_caption .= " книга : $p_book ";
        
    }

   if ($p_id_sector!='null')
   {
      $where.= "and exists (select id from prs_runner_paccnt as rp 
          where rp.id_paccnt = a.id and rp.id_sector = $p_id_sector ) ";
   }
    
   if ($p_id_region!='null')
   {
        
        $where.= "and exists (select rs.id from 
            prs_runner_paccnt as rp 
            join prs_runner_sectors as rs on (rs.id = rp.id_sector)
            where rp.id_paccnt = a.id and rs.id_region =  $p_id_region ) ";

        $SQL_H="SELECT name from cli_region_tbl where id = $p_id_region ;";
    
        $result = pg_query($Link, $SQL_H) or die("SQL Error: " . pg_last_error($Link) . $SQL_H);
        $row = pg_fetch_array($result);
        
        $params_caption.= $row['name'];        
       
   }   
    
    eval("\$header_text = \"$header_text\";");
    echo $header_text;
    
    $SQL = " 
     select    coalesce(ss1.name,ss2.name) as name, coalesce(ss1.ident, ss2.ident) as ident,
     coalesce(ss1.summ_lgt ,0)+coalesce(ss2.summ_lgt ,0) as summ_lgt,
     coalesce(ss1.town ,0)+coalesce(ss2.town ,0) as town,     
     coalesce(ss1.town_s ,0)+coalesce(ss2.town_s ,0) as town_s,     
     coalesce(ss1.town_h ,0)+coalesce(ss2.town_h ,0) as town_h,
     coalesce(ss1.village ,0)+coalesce(ss2.village ,0) as village,
     coalesce(ss1.village_s ,0)+coalesce(ss2.village_s ,0) as village_s,
     coalesce(ss1.village_h ,0)+coalesce(ss2.village_h ,0) as village_h,
     coalesce(ss1.no_gas ,0)+coalesce(ss2.no_gas ,0) as no_gas,
     coalesce(ss1.ful_summ ,0)+coalesce(ss2.summ_lgt ,0) as ful_summ,
     coalesce(ss1.ful_town ,0)+coalesce(ss2.town ,0) as ful_town,     
     coalesce(ss1.ful_town_s ,0)+coalesce(ss2.town_s ,0) as ful_town_s,     
     coalesce(ss1.ful_town_h ,0)+coalesce(ss2.town_h ,0) as ful_town_h,
     coalesce(ss1.ful_village ,0)+coalesce(ss2.village ,0) as ful_village,
     coalesce(ss1.ful_village_s ,0)+coalesce(ss2.village_s ,0) as ful_village_s,
     coalesce(ss1.ful_village_h ,0)+coalesce(ss2.village_h ,0) as ful_village_h,
     coalesce(ss1.ful_no_gas ,0)+coalesce(ss2.no_gas ,0) as ful_no_gas
     from (select g.name, g.ident,
 sum(summ_lgt) as summ_lgt, 
 --sum( CASE WHEN gt.ident~'tgr7_1' or (adr.is_town = 1 and gt.ident~'tgr7_6') THEN summ_lgt END) as town,
 sum( CASE WHEN gt.ident~'tgr7_1' THEN summ_lgt END) as town,
 sum( CASE WHEN adr.is_town = 1 and gt.ident~'tgr7_3'  THEN summ_lgt END) as town_s,
 sum( CASE WHEN ((gt.ident~'tgr7_51') or (gt.ident~'tgr7_53'))  THEN summ_lgt END) as town_h,

 --sum( CASE WHEN gt.ident~'tgr7_2' or (adr.is_town = 0 and gt.ident~'tgr7_6') THEN summ_lgt END) as village,
 sum( CASE WHEN gt.ident~'tgr7_2' THEN summ_lgt END) as village,    
 sum( CASE WHEN adr.is_town = 0 and gt.ident~'tgr7_3'  THEN summ_lgt END) as village_s,
 sum( CASE WHEN gt.ident~'tgr7_52'  THEN summ_lgt END) as village_h,
    
 sum( CASE WHEN gt.ident~'tgr7_6' THEN summ_lgt END) as no_gas,

 sum(round(demand_lgt*td.value,2)) as ful_summ, 
    
 --sum( CASE WHEN gt.ident~'tgr7_1' or (adr.is_town = 1 and gt.ident~'tgr7_6') THEN round(demand_lgt*td.value,2) END) as ful_town,
 sum( CASE WHEN gt.ident~'tgr7_1' THEN round(demand_lgt*td.value,2) END) as ful_town,    
 sum( CASE WHEN adr.is_town = 1 and gt.ident~'tgr7_3'  THEN round(demand_lgt*td.value,2) END) as ful_town_s,
 sum( CASE WHEN ((gt.ident~'tgr7_51') or (gt.ident~'tgr7_53'))  THEN round(demand_lgt*td.value,2) END) as ful_town_h,

 --sum( CASE WHEN gt.ident~'tgr7_2' or (adr.is_town = 0 and gt.ident~'tgr7_6') THEN round(demand_lgt*td.value,2) END) as ful_village,
 sum( CASE WHEN gt.ident~'tgr7_2' THEN round(demand_lgt*td.value,2) END) as ful_village,    
 sum( CASE WHEN adr.is_town = 0 and gt.ident~'tgr7_3'  THEN round(demand_lgt*td.value,2) END) as ful_village_s,
 sum( CASE WHEN gt.ident~'tgr7_52'  THEN round(demand_lgt*td.value,2) END) as ful_village_h,
    
 sum( CASE WHEN gt.ident~'tgr7_6' THEN round(demand_lgt*td.value,2) END) as ful_no_gas

    from acm_lgt_summ_tbl as s 
    join acm_bill_tbl as b on (b.id_doc = s.id_doc)
    join lgi_group_tbl as g on (s.id_grp_lgt = g.id)
    join lgi_calc_header_tbl as h on (h.id = g.id_calc)

    join clm_paccnt_h as a on (a.id = s.id_paccnt)
    join (select id, max(dt_b) as dt_b from clm_paccnt_h  where 
            ((dt_b < ($p_mmgg::date+'1 month'::interval) and dt_e is null)
            or 
            tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
        ) group by id order by id) as a2 
       on (a.id = a2.id and a.dt_b = a2.dt_b)
    join adt_addr_town_street_tbl as adr on (adr.id = (a.addr).id_class)
    left join aqm_tarif_tbl as t on (t.id = s.id_tarif)
    --left join aqi_grptar_tbl as gt on (gt.id = coalesce(t.id_grptar,a.id_gtar))
    left join aqi_grptar_tbl as gt on (gt.id = a.id_gtar )
    left join aqd_tarif_tbl as td on (td.id = s.id_summtarif)
    where s.mmgg = $p_mmgg 
    and (coalesce(s.demand_lgt,0) <>0 or coalesce(s.demand_add_lgt,0) <>0 )
    and g.id_budjet <> 3 
    and b.id_pref = 10 $where
     group by g.name, g.ident
    ) as ss1     
    full outer  join 
    ( 
   select g.name, g.ident,
    sum(s.sum_val) as summ_lgt,
    --sum( CASE WHEN gt.ident~'tgr7_1' or (adr.is_town = 1 and gt.ident~'tgr7_6')  THEN s.sum_val END) as town,
    sum( CASE WHEN gt.ident~'tgr7_1' THEN s.sum_val END) as town,    
    sum( CASE WHEN adr.is_town = 1 and gt.ident~'tgr7_3'  THEN s.sum_val END) as town_s,
    sum( CASE WHEN ((gt.ident~'tgr7_51') or (gt.ident~'tgr7_53'))  THEN s.sum_val END) as town_h,
    --sum( CASE WHEN gt.ident~'tgr7_2' or (adr.is_town = 0 and gt.ident~'tgr7_6') THEN s.sum_val END) as village,
    sum( CASE WHEN gt.ident~'tgr7_2' THEN s.sum_val END) as village,    
    sum( CASE WHEN adr.is_town = 0 and gt.ident~'tgr7_3'  THEN s.sum_val END) as village_s,
    sum( CASE WHEN gt.ident~'tgr7_52'  THEN s.sum_val END) as village_h,
    sum( CASE WHEN gt.ident~'tgr7_6' THEN s.sum_val END) as no_gas
   from acm_dop_lgt_tbl as s
   join lgi_group_tbl as g on (s.id_grp_lgt = g.id)
   join lgi_calc_header_tbl as h on (h.id = g.id_calc)    
   join clm_paccnt_h as a on (a.id = s.id_paccnt)
   join (select id, max(dt_b) as dt_b from clm_paccnt_h  where 
           ((dt_b < ($p_mmgg::date+'1 month'::interval) and dt_e is null)
           or 
           tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
       ) group by id order by id) as a2 
   on (a.id = a2.id and a.dt_b = a2.dt_b)
   join adt_addr_town_street_tbl as adr on (adr.id = (a.addr).id_class)       
   left join aqi_grptar_tbl as gt on (gt.id = a.id_gtar)
   where s.mmgg = $p_mmgg 
   and g.id_budjet <>3 
    $where
   group by g.name, g.ident
    ) as ss2 on (ss2.name = ss1.name and ss2.ident = ss1.ident)
 order by coalesce(ss1.ident, ss2.ident,'0')::int ,coalesce(ss1.name,ss2.name) ;";

if($oper=='lgt_dod4_tar')
{
    
    $SQL2 = "
     select  coalesce(ss1.time_ident, ss2.time_ident) as time_ident,
     coalesce(ss1.ident, ss2.ident) as ident,
     coalesce(ss1.lim_min, ss2.lim_min,0) as lim_min,
     coalesce(ss1.lim_max, ss2.lim_max,0) as lim_max,
     coalesce(ss1.summ_lgt ,0)+coalesce(ss2.summ_lgt ,0) as summ_lgt,
     coalesce(ss1.town ,0)+coalesce(ss2.town ,0) as town,     
     coalesce(ss1.town_s ,0)+coalesce(ss2.town_s ,0) as town_s,     
     coalesce(ss1.town_h ,0)+coalesce(ss2.town_h ,0) as town_h,
     coalesce(ss1.village ,0)+coalesce(ss2.village ,0) as village,
     coalesce(ss1.village_s ,0)+coalesce(ss2.village_s ,0) as village_s,
     coalesce(ss1.village_h ,0)+coalesce(ss2.village_h ,0) as village_h,
     coalesce(ss1.no_gas ,0)+coalesce(ss2.no_gas ,0) as no_gas,
     coalesce(ss1.ful_summ ,0)+coalesce(ss2.summ_lgt ,0) as ful_summ,
     coalesce(ss1.ful_town ,0)+coalesce(ss2.town ,0) as ful_town,     
     coalesce(ss1.ful_town_s ,0)+coalesce(ss2.town_s ,0) as ful_town_s,     
     coalesce(ss1.ful_town_h ,0)+coalesce(ss2.town_h ,0) as ful_town_h,
     coalesce(ss1.ful_village ,0)+coalesce(ss2.village ,0) as ful_village,
     coalesce(ss1.ful_village_s ,0)+coalesce(ss2.village_s ,0) as ful_village_s,
     coalesce(ss1.ful_village_h ,0)+coalesce(ss2.village_h ,0) as ful_village_h,
     coalesce(ss1.ful_no_gas ,0)+coalesce(ss2.no_gas ,0) as ful_no_gas
     from (select g.ident, t.lim_min,t.lim_max, 
 sum(summ_lgt) as summ_lgt, 
    
 --sum( CASE WHEN gt.ident~'tgr7_1' or (adr.is_town = 1 and gt.ident~'tgr7_6') THEN summ_lgt END) as town,
 sum( CASE WHEN gt.ident~'tgr7_1' THEN summ_lgt END) as town,    
 sum( CASE WHEN adr.is_town = 1 and gt.ident~'tgr7_3'  THEN summ_lgt END) as town_s,
 sum( CASE WHEN ((gt.ident~'tgr7_51') or (gt.ident~'tgr7_53'))  THEN summ_lgt END) as town_h,

 --sum( CASE WHEN gt.ident~'tgr7_2' or (adr.is_town = 0 and gt.ident~'tgr7_6') THEN summ_lgt END) as village,
 sum( CASE WHEN gt.ident~'tgr7_2' THEN summ_lgt END) as village,    
 sum( CASE WHEN adr.is_town = 0 and gt.ident~'tgr7_3'  THEN summ_lgt END) as village_s,
 sum( CASE WHEN gt.ident~'tgr7_52'  THEN summ_lgt END) as village_h,
 sum( CASE WHEN gt.ident~'tgr7_6' THEN summ_lgt END) as no_gas,

 sum(round(demand_lgt*td.value,2)) as ful_summ, 
    
-- sum( CASE WHEN gt.ident~'tgr7_1' or (adr.is_town = 1 and gt.ident~'tgr7_6') THEN round(demand_lgt*td.value,2) END) as ful_town,
 sum( CASE WHEN gt.ident~'tgr7_1' THEN round(demand_lgt*td.value,2) END) as ful_town,    
 sum( CASE WHEN adr.is_town = 1 and gt.ident~'tgr7_3'  THEN round(demand_lgt*td.value,2) END) as ful_town_s,
 sum( CASE WHEN ((gt.ident~'tgr7_51') or (gt.ident~'tgr7_53'))  THEN round(demand_lgt*td.value,2) END) as ful_town_h,

-- sum( CASE WHEN gt.ident~'tgr7_2' or (adr.is_town = 0 and gt.ident~'tgr7_6') THEN round(demand_lgt*td.value,2) END) as ful_village,
 sum( CASE WHEN gt.ident~'tgr7_2'  THEN round(demand_lgt*td.value,2) END) as ful_village,    
 sum( CASE WHEN adr.is_town = 0 and gt.ident~'tgr7_3'  THEN round(demand_lgt*td.value,2) END) as ful_village_s,
 sum( CASE WHEN gt.ident~'tgr7_52'  THEN round(demand_lgt*td.value,2) END) as ful_village_h,
    
 sum( CASE WHEN gt.ident~'tgr7_6' THEN round(demand_lgt*td.value,2) END) as ful_no_gas,
    
    CASE WHEN dt_begin < '2017-03-01' THEN 0 ELSE 1 END as time_ident

    from acm_lgt_summ_tbl as s 
    join acm_bill_tbl as b on (b.id_doc = s.id_doc)
    join lgi_group_tbl as g on (s.id_grp_lgt = g.id)
    join lgi_calc_header_tbl as h on (h.id = g.id_calc)

    join clm_paccnt_h as a on (a.id = s.id_paccnt)
    join (select id, max(dt_b) as dt_b from clm_paccnt_h  where 
            ((dt_b < ($p_mmgg::date+'1 month'::interval) and dt_e is null)
            or 
            tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
        ) group by id order by id) as a2 
       on (a.id = a2.id and a.dt_b = a2.dt_b)
    join adt_addr_town_street_tbl as adr on (adr.id = (a.addr).id_class)

    join ( 
    select min(t.id) as id_tar, t.id_grptar
	from aqm_tarif_tbl as t 
        where ((dt_e is null ) or (dt_e > $p_mmgg )) and (dt_b <= $p_mmgg)
        and (t.per_min is null or (t.per_min <= $p_mmgg and t.per_max >= $p_mmgg))
        group by id_grptar
     ) as tt on (tt.id_grptar = a.id_gtar)
    join aqm_tarif_tbl as t2 on (t2.id = coalesce(s.id_tarif,tt.id_tar))
    join aqi_grptar_tbl as gt on (gt.id = a.id_gtar)

    join aqm_tarif_tbl as t on (t.id = CASE WHEN coalesce(t2.id_grptar,0)<>coalesce(a.id_gtar,0) and (coalesce(a.id_gtar,0) not in (5,6,8,9,12,13)) and (coalesce(t2.id_grptar,0) not in (5,6,8,9,12,13))
             THEN tt.id_tar ELSE s.id_tarif END)
    left join aqd_tarif_tbl as td on (td.id = s.id_summtarif)
    where s.mmgg = $p_mmgg 
    and (coalesce(s.demand_lgt,0) <>0 or coalesce(s.demand_add_lgt,0) <>0 )
    and g.id_budjet <> 3
    and b.id_pref = 10 $where
     group by g.ident, t.lim_min,t.lim_max, CASE WHEN dt_begin < '2017-03-01' THEN 0 ELSE 1 END 
    ) as ss1     
    full outer  join 
    ( 
   select g.ident, t.lim_min,t.lim_max, 
    sum(s.sum_val) as summ_lgt,
--    sum( CASE WHEN gt.ident~'tgr7_1' or (adr.is_town = 1 and gt.ident~'tgr7_6')  THEN s.sum_val END) as town,
    sum( CASE WHEN gt.ident~'tgr7_1'   THEN s.sum_val END) as town,    
    sum( CASE WHEN adr.is_town = 1 and gt.ident~'tgr7_3'  THEN s.sum_val END) as town_s,
    sum( CASE WHEN ((gt.ident~'tgr7_51') or (gt.ident~'tgr7_53'))  THEN s.sum_val END) as town_h,
--    sum( CASE WHEN gt.ident~'tgr7_2' or (adr.is_town = 0 and gt.ident~'tgr7_6') THEN s.sum_val END) as village,
    sum( CASE WHEN gt.ident~'tgr7_2'  THEN s.sum_val END) as village,    
    sum( CASE WHEN adr.is_town = 0 and gt.ident~'tgr7_3'  THEN s.sum_val END) as village_s,
    sum( CASE WHEN gt.ident~'tgr7_52'  THEN s.sum_val END) as village_h,
    sum( CASE WHEN gt.ident~'tgr7_6' THEN s.sum_val END) as no_gas,
    $time_ident as time_ident
   from acm_dop_lgt_tbl as s
   join lgi_group_tbl as g on (s.id_grp_lgt = g.id)
   join lgi_calc_header_tbl as h on (h.id = g.id_calc)    
   join clm_paccnt_h as a on (a.id = s.id_paccnt)
   join (select id, max(dt_b) as dt_b from clm_paccnt_h  where 
           ((dt_b < ($p_mmgg::date+'1 month'::interval) and dt_e is null)
           or 
           tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
       ) group by id order by id) as a2 
   on (a.id = a2.id and a.dt_b = a2.dt_b)
   join adt_addr_town_street_tbl as adr on (adr.id = (a.addr).id_class)       
   join aqi_grptar_tbl as gt on (gt.id = a.id_gtar)
   join ( 
   select min(t.id) as id_tar, t.id_grptar
	from aqm_tarif_tbl as t 
        where ((dt_e is null ) or (dt_e > $p_mmgg )) and (dt_b <= $p_mmgg)
        and (t.per_min is null or (t.per_min <= $p_mmgg and t.per_max >= $p_mmgg))
        group by id_grptar
    ) as tt on (tt.id_grptar = a.id_gtar)
   join aqm_tarif_tbl as t on (t.id = tt.id_tar)    
   where s.mmgg = $p_mmgg  
   and g.id_budjet <> 3
    $where
   group by g.ident, t.lim_min,t.lim_max 
    ) as ss2 on (ss2.lim_min = ss1.lim_min and coalesce(ss2.lim_max,0) = coalesce(ss1.lim_max,0) 
      and ss2.ident = ss1.ident and ss2.time_ident = ss1.time_ident)
 order by coalesce(ss1.ident, ss2.ident,'0')::int ,
     coalesce(ss1.time_ident, ss2.time_ident)::int, 
     coalesce(ss1.lim_min, ss2.lim_min,0), coalesce(ss1.lim_max, ss2.lim_max,0)  ;";
    
  
    $result2 = pg_query($Link, $SQL2) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result2) {
        $rows_count2 = pg_num_rows($result2);
        $arr_tar = pg_fetch_all($result2);
    }
  }    
    
   // throw new Exception(json_encode($SQL));

    $sum_all_full=0;
    $sum_town_full=0;
    $sum_town_s_full=0;
    $sum_town_h_full=0;

    $sum_village_full=0;
    $sum_village_s_full=0;
    $sum_village_h_full=0;

    $sum_nogas_full=0;

    $sum_all_lgt=0;
    $sum_town_lgt=0;
    $sum_town_s_lgt=0;
    $sum_town_h_lgt=0;

    $sum_village_lgt=0;
    $sum_village_s_lgt=0;
    $sum_village_h_lgt=0;

    $sum_nogas_lgt=0;
    
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    
    
    class LgtFilter {
        private $ident;
 
        function __construct($reference) {
                $this->ident = $reference;
        }
 
        function isEqual($row) {
                return $row['ident'] == $this->ident;
        }
}
    
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        $i=0;
        while ($row = pg_fetch_array($result)) {

            $name = htmlspecialchars($row['name']);
            $ident = $row['ident'];
            
            $sum_town_full+=$row['ful_town'];
            $sum_town_s_full+=$row['ful_town_s'];
            $sum_town_h_full+=$row['ful_town_h'];

            $sum_village_full+=$row['ful_village'];
            $sum_village_s_full+=$row['ful_village_s'];
            $sum_village_h_full+=$row['ful_village_h'];
            
            $sum_nogas_full+=$row['ful_no_gas'];

            $sum_town_lgt+=$row['town'];
            $sum_town_s_lgt+=$row['town_s'];
            $sum_town_h_lgt+=$row['town_h'];

            $sum_village_lgt+=$row['village'];
            $sum_village_s_lgt+=$row['village_s'];
            $sum_village_h_lgt+=$row['village_h'];
            
            $sum_nogas_lgt+=$row['no_gas'];
            
//-------
            $all_full_txt=number_format_ua($row['ful_town']+$row['ful_town_s']+
                    $row['ful_town_h']+$row['ful_village']+$row['ful_village_s']+
                    $row['ful_village_h']+$row['ful_no_gas'],2);
            
            $town_full_txt=number_format_ua($row['ful_town'],2);
            $town_s_full_txt=number_format_ua($row['ful_town_s'],2);
            $town_h_full_txt=number_format_ua($row['ful_town_h'],2);

            $village_full_txt=number_format_ua($row['ful_village'],2);
            $village_s_full_txt=number_format_ua($row['ful_village_s'],2);
            $village_h_full_txt=number_format_ua($row['ful_village_h'],2);
            
            $nogas_full_txt=number_format_ua($row['ful_no_gas'],2);

            $all_lgt_txt=number_format_ua($row['town']+$row['town_s']+
                    $row['town_h']+$row['village']+$row['village_s']+
                    $row['village_h']+$row['no_gas'],2);
            
            $town_lgt_txt=number_format_ua($row['town'],2);
            $town_s_lgt_txt=number_format_ua($row['town_s'],2);
            $town_h_lgt_txt=number_format_ua($row['town_h'],2);

            $village_lgt_txt=number_format_ua($row['village'],2);
            $village_s_lgt_txt=number_format_ua($row['village_s'],2);
            $village_h_lgt_txt=number_format_ua($row['village_h'],2);
            
            $nogas_lgt_txt=number_format_ua($row['no_gas'],2);
            
//-------            

            $all_delta_txt=number_format_ua($row['ful_town']+$row['ful_town_s']+
                    $row['ful_town_h']+$row['ful_village']+$row['ful_village_s']+
                    $row['ful_village_h']+$row['ful_no_gas']-
                    ($row['town']+$row['town_s']+
                    $row['town_h']+$row['village']+$row['village_s']+
                    $row['village_h']+$row['no_gas']) ,2);
            
            $town_delta_txt=number_format_ua($row['ful_town']-$row['town'],2);
            $town_s_delta_txt=number_format_ua($row['ful_town_s']-$row['town_s'],2);
            $town_h_delta_txt=number_format_ua($row['ful_town_h']-$row['town_h'],2);

            $village_delta_txt=number_format_ua($row['ful_village']-$row['village'],2);
            $village_s_delta_txt=number_format_ua($row['ful_village_s']-$row['village_s'],2);
            $village_h_delta_txt=number_format_ua($row['ful_village_h']-$row['village_h'],2);
            
            $nogas_delta_txt=number_format_ua($row['ful_no_gas']-$row['no_gas'],2);
            
            if($oper=='lgt_dod4_tar')
                $str_class = 'table_str2';
            else    
                $str_class = 'table_str';

            echo "
            <tr class = '$str_class'>
            <td>$ident</td>
            <td>$name</td>
            <td class='c_n'>{$all_full_txt}</td>
            <td class='c_n'>{$town_full_txt}</td>
            <td class='c_n'>{$town_s_full_txt}</td>
            <td class='c_n'>{$town_h_full_txt}</td>
            <td class='c_n'>{$village_full_txt}</td>
            <td class='c_n'>{$village_s_full_txt}</td>
            <td class='c_n'>{$village_h_full_txt}</td>            
            <td class='c_n'>{$nogas_full_txt}</td>

            <td class='c_n'>{$all_delta_txt}</td>
            <td class='c_n'>{$town_delta_txt}</td>
            <td class='c_n'>{$town_s_delta_txt}</td>
            <td class='c_n'>{$town_h_delta_txt}</td>
            <td class='c_n'>{$village_delta_txt}</td>
            <td class='c_n'>{$village_s_delta_txt}</td>
            <td class='c_n'>{$village_h_delta_txt}</td>            
            <td class='c_n'>{$nogas_delta_txt}</td>

            <td class='c_n'>{$all_lgt_txt}</td>
            <td class='c_n'>{$town_lgt_txt}</td>
            <td class='c_n'>{$town_s_lgt_txt}</td>
            <td class='c_n'>{$town_h_lgt_txt}</td>
            <td class='c_n'>{$village_lgt_txt}</td>
            <td class='c_n'>{$village_s_lgt_txt}</td>
            <td class='c_n'>{$village_h_lgt_txt}</td>            
            <td class='c_n'>{$nogas_lgt_txt}</td>
            
            </tr>  ";        
            $i++;
            
            
            if($oper=='lgt_dod4_tar')
            {
                $tarif_arr = array_filter($arr_tar, array(new LgtFilter($ident), 'isEqual'));
                
                foreach($tarif_arr as $row_tar) {
    
                    $name = '-- в т.ч.';
                    if ($row_tar['lim_min']!=0)
                        $name.=' з '.$row_tar['lim_min'];
                    if (($row_tar['lim_max']!=0)&&($row_tar['lim_max']!=''))
                        $name.=' до '.$row_tar['lim_max'];
                    $name.=' кВтг';
                    
                    if ($name =='-- в т.ч. кВтг') $name ="- тариф Багатодітні";
                    
                    if ($row_tar['time_ident']== 1 )
                    {
                        $name.=' (з 01.03.2017р)';
                    }
                    
                    $name=htmlspecialchars($name);    
                    
                    $all_full_txt = number_format_ua($row_tar['ful_town'] + $row_tar['ful_town_s'] +
                            $row_tar['ful_town_h'] + $row_tar['ful_village'] + $row_tar['ful_village_s'] +
                            $row_tar['ful_village_h'] + $row_tar['ful_no_gas'], 2);

                    $town_full_txt = number_format_ua($row_tar['ful_town'], 2);
                    $town_s_full_txt = number_format_ua($row_tar['ful_town_s'], 2);
                    $town_h_full_txt = number_format_ua($row_tar['ful_town_h'], 2);

                    $village_full_txt = number_format_ua($row_tar['ful_village'], 2);
                    $village_s_full_txt = number_format_ua($row_tar['ful_village_s'], 2);
                    $village_h_full_txt = number_format_ua($row_tar['ful_village_h'], 2);

                    $nogas_full_txt = number_format_ua($row_tar['ful_no_gas'], 2);

                    $all_lgt_txt = number_format_ua($row_tar['town'] + $row_tar['town_s'] +
                            $row_tar['town_h'] + $row_tar['village'] + $row_tar['village_s'] +
                            $row_tar['village_h'] + $row_tar['no_gas'], 2);

                    $town_lgt_txt = number_format_ua($row_tar['town'], 2);
                    $town_s_lgt_txt = number_format_ua($row_tar['town_s'], 2);
                    $town_h_lgt_txt = number_format_ua($row_tar['town_h'], 2);

                    $village_lgt_txt = number_format_ua($row_tar['village'], 2);
                    $village_s_lgt_txt = number_format_ua($row_tar['village_s'], 2);
                    $village_h_lgt_txt = number_format_ua($row_tar['village_h'], 2);

                    $nogas_lgt_txt = number_format_ua($row_tar['no_gas'], 2);

       

                    $all_delta_txt = number_format_ua($row_tar['ful_town'] + $row_tar['ful_town_s'] +
                            $row_tar['ful_town_h'] + $row_tar['ful_village'] + $row_tar['ful_village_s'] +
                            $row_tar['ful_village_h'] + $row_tar['ful_no_gas'] -
                            ($row_tar['town'] + $row_tar['town_s'] +
                            $row_tar['town_h'] + $row_tar['village'] + $row_tar['village_s'] +
                            $row_tar['village_h'] + $row_tar['no_gas']), 2);

                    $town_delta_txt = number_format_ua($row_tar['ful_town'] - $row_tar['town'], 2);
                    $town_s_delta_txt = number_format_ua($row_tar['ful_town_s'] - $row_tar['town_s'], 2);
                    $town_h_delta_txt = number_format_ua($row_tar['ful_town_h'] - $row_tar['town_h'], 2);

                    $village_delta_txt = number_format_ua($row_tar['ful_village'] - $row_tar['village'], 2);
                    $village_s_delta_txt = number_format_ua($row_tar['ful_village_s'] - $row_tar['village_s'], 2);
                    $village_h_delta_txt = number_format_ua($row_tar['ful_village_h'] - $row_tar['village_h'], 2);

                    $nogas_delta_txt = number_format_ua($row_tar['ful_no_gas'] - $row_tar['no_gas'], 2);


                    echo "
                    <tr class='table_str' >
                    <td></td>
                    <td>$name</td>
                    <td class='c_n'>{$all_full_txt}</td>
                    <td class='c_n'>{$town_full_txt}</td>
                    <td class='c_n'>{$town_s_full_txt}</td>
                    <td class='c_n'>{$town_h_full_txt}</td>
                    <td class='c_n'>{$village_full_txt}</td>
                    <td class='c_n'>{$village_s_full_txt}</td>
                    <td class='c_n'>{$village_h_full_txt}</td>            
                    <td class='c_n'>{$nogas_full_txt}</td>

                    <td class='c_n'>{$all_delta_txt}</td>
                    <td class='c_n'>{$town_delta_txt}</td>
                    <td class='c_n'>{$town_s_delta_txt}</td>
                    <td class='c_n'>{$town_h_delta_txt}</td>
                    <td class='c_n'>{$village_delta_txt}</td>
                    <td class='c_n'>{$village_s_delta_txt}</td>
                    <td class='c_n'>{$village_h_delta_txt}</td>            
                    <td class='c_n'>{$nogas_delta_txt}</td>

                    <td class='c_n'>{$all_lgt_txt}</td>
                    <td class='c_n'>{$town_lgt_txt}</td>
                    <td class='c_n'>{$town_s_lgt_txt}</td>
                    <td class='c_n'>{$town_h_lgt_txt}</td>
                    <td class='c_n'>{$village_lgt_txt}</td>
                    <td class='c_n'>{$village_s_lgt_txt}</td>
                    <td class='c_n'>{$village_h_lgt_txt}</td>            
                    <td class='c_n'>{$nogas_lgt_txt}</td>
            
                    </tr>  ";
                }

            }

        }
        
        
    }

    $all_full_txt = number_format_ua($sum_town_full + $sum_town_s_full +
            $sum_town_h_full + $sum_village_full + $sum_village_s_full +
            $sum_village_h_full + $sum_nogas_full, 2);

    $town_full_txt = number_format_ua($sum_town_full, 2);
    $town_s_full_txt = number_format_ua($sum_town_s_full, 2);
    $town_h_full_txt = number_format_ua($sum_town_h_full, 2);

    $village_full_txt = number_format_ua($sum_village_full, 2);
    $village_s_full_txt = number_format_ua($sum_village_s_full, 2);
    $village_h_full_txt = number_format_ua($sum_village_h_full, 2);

    $nogas_full_txt = number_format_ua($sum_nogas_full, 2);

    $all_lgt_txt = number_format_ua($sum_town_lgt + $sum_town_s_lgt +
            $sum_town_h_lgt + $sum_village_lgt + $sum_village_s_lgt +
           $sum_village_h_lgt + $sum_nogas_lgt, 2);

    $town_lgt_txt = number_format_ua($sum_town_lgt, 2);
    $town_s_lgt_txt = number_format_ua($sum_town_s_lgt, 2);
    $town_h_lgt_txt = number_format_ua($sum_town_h_lgt, 2);

    $village_lgt_txt = number_format_ua($sum_village_lgt, 2);
    $village_s_lgt_txt = number_format_ua($sum_village_s_lgt, 2);
    $village_h_lgt_txt = number_format_ua($sum_village_h_lgt, 2);

    $nogas_lgt_txt = number_format_ua($sum_nogas_lgt, 2);


    $all_delta_txt = number_format_ua($sum_town_full + $sum_town_s_full +
            $sum_town_h_full + $sum_village_full + $sum_village_s_full +
            $sum_village_h_full + $sum_nogas_full-
            ($sum_town_lgt + $sum_town_s_lgt +
            $sum_town_h_lgt + $sum_village_lgt + $sum_village_s_lgt +
           $sum_village_h_lgt + $sum_nogas_lgt) , 2);

    $town_delta_txt = number_format_ua($sum_town_full - $sum_town_lgt, 2);
    $town_s_delta_txt = number_format_ua($sum_town_s_full - $sum_town_s_lgt, 2);
    $town_h_delta_txt = number_format_ua($sum_town_h_full - $sum_town_h_lgt, 2);

    $village_delta_txt = number_format_ua($sum_village_full - $sum_village_lgt, 2);
    $village_s_delta_txt = number_format_ua($sum_village_s_full - $sum_village_s_lgt, 2);
    $village_h_delta_txt = number_format_ua($sum_village_h_full - $sum_village_h_lgt, 2);

    $nogas_delta_txt = number_format_ua($sum_nogas_full - $sum_nogas_lgt,2);
    
    
    eval("\$footer_text_eval = \"$footer_text\";");
    echo $footer_text_eval;
   
}
//----------------------------------------------------------------------------
if (($oper=='plombs_in')||($oper=='plombs_out'))
{
    $p_dtb = sql_field_val('dt_b', 'date');
    $p_dte = sql_field_val('dt_e', 'date');
    
    $p_dtb_str = trim($_POST['dt_b']);
    $p_dte_str = trim($_POST['dt_e']);

    $p_id_town = sql_field_val('addr_town', 'int');
    $p_id_person = sql_field_val('id_person', 'int'); 
    
    $p_id_sector = sql_field_val('id_sector', 'int');
    $p_book = sql_field_val('book', 'string');
    $p_id_region = sql_field_val('id_region', 'int');

    $params_caption1 = '';
    $params_caption2 = '';

    if($oper=='plombs_in')
    {
        $where = " where pl.dt_on between $p_dtb and $p_dte ";
        $params_caption1 = " встановлених з $p_dtb_str по $p_dte_str ";
        
        if ($p_id_person!='null')
        {
            $where.= " and pl.id_person_on = $p_id_person ";
        }
    }

    if($oper=='plombs_out')
    {
        $where = " where pl.dt_off between $p_dtb and $p_dte ";
        $params_caption1 = " демонтованих з $p_dtb_str по $p_dte_str ";
        
        if ($p_id_person!='null')
        {
            $where.= " and pl.id_person_off = $p_id_person ";
        }
    }
    
    if ($p_id_town!='null')
    {
        $where.= " and adr.id_town = $p_id_town ";
    }

    if ((isset($_POST['sector']))&&($_POST['sector']!=''))
        $params_caption2.= ' Дiльниця: '. trim($_POST['sector']);

    if ($p_book!='null')
    {
        $where.= " and book = $p_book ";
        $params_caption2 .= " книга : $p_book ";
        
    }

   if ($p_id_sector!='null')
   {
      $where.= "and exists (select id from prs_runner_paccnt as rp 
          where rp.id_paccnt = c.id and rp.id_sector = $p_id_sector ) ";
   }
    
    if ($p_id_region!='null')
    {
        
        $where.= "and exists (select rs.id from 
            prs_runner_paccnt as rp 
            join prs_runner_sectors as rs on (rs.id = rp.id_sector)
            where rp.id_paccnt = c.id and rs.id_region =  $p_id_region ) ";

        $SQL_H="SELECT name from cli_region_tbl where id = $p_id_region ;";
    
        $result = pg_query($Link, $SQL_H) or die("SQL Error: " . pg_last_error($Link) . $SQL_H);
        $row = pg_fetch_array($result);
        
        $params_caption.= $row['name'];               
    }   
    
    if ((isset($_POST['addr_town_name']))&&($_POST['addr_town_name']!=''))
        $params_caption2.= ' населений пункт: '. trim($_POST['addr_town_name']);

    if ((isset($_POST['person']))&&($_POST['person']!=''))
        $params_caption2.= ' Працівник: '. trim($_POST['person']);
    
    
    eval("\$header_text = \"$header_text\";");
    echo $header_text;
    
    $SQL = "SELECT c.id,c.code,c.book, 
    adr.town, adr.street, address_print(c.addr) as house,
   (a.last_name||' '||coalesce(substr(a.name,1,1),'')||'.'||coalesce(substr(a.patron_name,1,1),''))||'.'::varchar as abon,
    coalesce(pl.num_meter,m.num_meter) as num_meter,
    to_char(pl.dt_on, 'DD.MM.YYYY') as dt_on,to_char(pl.dt_off, 'DD.MM.YYYY') as dt_off,
    m.type_meter,
    cn_on.represent_name as  person_on, cn_off.represent_name as person_off,
    pp.name as place_name,pt.name as type_name,pl.plomb_num
    FROM clm_paccnt_h as c
    join (select id, max(dt_b) as dt_b from clm_paccnt_h  where 
    ((dt_b < $p_dte and dt_e is null)
    or 
    tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_dtb::timestamp::abstime,$p_dte::timestamp::abstime))
    ) and archive=0 and activ = true
    group by id order by id) as c2 
        on (c.id = c2.id and c.dt_b = c2.dt_b)
    join clm_abon_tbl as a on (a.id = c.id_abon) 
    join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class)
    join clm_plomb_tbl as pl on (pl.id_paccnt = c.id)
    left join (
      select m.id, m.id_paccnt, m.num_meter, im.name as type_meter 
      from clm_meterpoint_h as m
      join eqi_meter_tbl as im on (im.id = m.id_type_meter)
      join (select id, max(dt_b) as dt_b from clm_meterpoint_h  where 
      ((dt_b < $p_dte and dt_e is null)
      or 
      tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_dtb::timestamp::abstime,$p_dte::timestamp::abstime))
      )
      group by id order by id) as m2 on (m.id = m2.id and m.dt_b = m2.dt_b)
    )  as m on (m.id_paccnt=c.id and m.id = pl.id_meter )
    left join prs_persons as cn_on on (cn_on.id = pl.id_person_on)     
    left join prs_persons as cn_off on (cn_off.id = pl.id_person_off)     
    left join plomb_type as pt on (pt.id = pl.id_type)
    left join plomb_places as pp on (pp.id = pl.id_place)
    $where
    order by c.book,c.code";

   // throw new Exception(json_encode($SQL));
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        $i=0;
        while ($row = pg_fetch_array($result)) {
            $i++;
            $abon = htmlspecialchars($row['abon']);
            $addr = htmlspecialchars($row['town'].' '.$row['street'].' '.$row['house']);
            $book=$row['book'].'/'.$row['code'];
            
            echo "
            <tr >
            <td class='c_i'>{$i}</td>
            <td class='c_t'>$book</td>
            <td>$abon</td>
            <td>$addr</td>
            <td>{$row['type_meter']}</td>
            <td class='c_t'>{$row['num_meter']}</td>
            <td>{$row['place_name']}</td>
            <td>{$row['type_name']}</td>
            <td class='c_t'>{$row['plomb_num']}</td>
            <td>{$row['dt_on']}</td>
            <td>{$row['person_on']}</td>
            <td>{$row['dt_off']}</td>
            <td>{$row['person_off']}</td>
            </tr>  ";        

        }
        
    }

    eval("\$footer_text_eval = \"$footer_text\";");
    echo $footer_text_eval;
   
}
////----------------------------------------------------------------------------
if ($oper=='plomb_list_now')
{
    $p_dt = sql_field_val('dt_rep', 'date');    
    $p_dt_str = trim($_POST['dt_rep']);

    $p_id_town = sql_field_val('addr_town', 'int');
    $p_id_person = sql_field_val('id_person', 'int');
    $p_id_sector = sql_field_val('id_sector', 'int');
    $p_book = sql_field_val('book', 'string');
    $p_id_region = sql_field_val('id_region', 'int');
    
    $params_caption = '';

    $where = " where pl.dt_on <= $p_dt and coalesce(pl.dt_off, $p_dt)>= $p_dt ";
       
    if ($p_id_person!='null')
    {
       $where.= " and pl.id_person_on = $p_id_person ";
    }

    
    if ($p_id_town!='null')
    {
        $where.= " and adr.id_town = $p_id_town ";
    }

    if ((isset($_POST['addr_town_name']))&&($_POST['addr_town_name']!=''))
        $params_caption.= ' населений пункт: '. trim($_POST['addr_town_name']);

    if ((isset($_POST['person']))&&($_POST['person']!=''))
        $params_caption.= ' Працівник: '. trim($_POST['person']);
    
    if ((isset($_POST['sector']))&&($_POST['sector']!=''))
        $params_caption.= ' Дiльниця: '. trim($_POST['sector']);

    if ($rem_worker == 1)
    {
        $params_caption.= ' Працівники РЕМ ';
        $where.= " and c.rem_worker = true ";
    }
    
    if ($p_book!='null')
    {
        $where.= " and book = $p_book ";
        $params_caption .= " книга : $p_book ";
    }
    
   if ($p_id_sector!='null')
   {
      $where.= "and exists (select id from prs_runner_paccnt as rp 
          where rp.id_paccnt = c.id and rp.id_sector = $p_id_sector ) ";
   }
    
    if ($p_id_region!='null')
    {
        
        $where.= "and exists (select rs.id from 
            prs_runner_paccnt as rp 
            join prs_runner_sectors as rs on (rs.id = rp.id_sector)
            where rp.id_paccnt = c.id and rs.id_region =  $p_id_region ) ";

        $SQL_H="SELECT name from cli_region_tbl where id = $p_id_region ;";
    
        $result = pg_query($Link, $SQL_H) or die("SQL Error: " . pg_last_error($Link) . $SQL_H);
        $row = pg_fetch_array($result);
        
        $params_caption.= $row['name'];               
    }   
    
    eval("\$header_text = \"$header_text\";");
    echo $header_text;
    
    $SQL = "SELECT c.id,c.code,c.book, 
    adr.town, adr.street, address_print(c.addr) as house,
   (a.last_name||' '||coalesce(substr(a.name,1,1),'')||'.'||coalesce(substr(a.patron_name,1,1),''))||'.'::varchar as abon,
    coalesce(pl.num_meter,m.num_meter) as num_meter,
    to_char(pl.dt_on, 'DD.MM.YYYY') as dt_on,to_char(pl.dt_off, 'DD.MM.YYYY') as dt_off,
    m.type_meter,
    cn_on.represent_name as  person_on, cn_off.represent_name as person_off,
    pp.name as place_name,pt.name as type_name,pl.plomb_num,pl.comment,
    ('0'||substring(c.code FROM '[0-9]+'))::int as int_code,
    ('0'||substring(c.book FROM '[0-9]+'))::int as int_book   
    
        FROM clm_paccnt_h as c
    join (select id, max(dt_b) as dt from clm_paccnt_h  where dt_b <= $p_dt and coalesce(dt_e,$p_dt) >= $p_dt 
       and archive=0 and activ = true group by id order by id) as c2 
       on (c.id = c2.id and c2.dt = c.dt_b)
    join clm_abon_tbl as a on (a.id = c.id_abon) 
    join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class)
    join clm_plomb_tbl as pl on (pl.id_paccnt = c.id)
    left join (
      select m.id, m.id_paccnt, m.num_meter, im.name as type_meter 
      from clm_meterpoint_h as m
      join eqi_meter_tbl as im on (im.id = m.id_type_meter)
      join (select id, max(dt_b) as dt_b from clm_meterpoint_h  
      where  dt_b <= $p_dt and coalesce(dt_e,$p_dt) >= $p_dt 
      group by id order by id) as m2 on (m.id = m2.id and m.dt_b = m2.dt_b)
    )  as m on (m.id_paccnt=c.id and m.id = pl.id_meter )
    left join prs_persons as cn_on on (cn_on.id = pl.id_person_on)     
    left join prs_persons as cn_off on (cn_off.id = pl.id_person_off)     
    left join plomb_type as pt on (pt.id = pl.id_type)
    left join plomb_places as pp on (pp.id = pl.id_place)
    $where
    order by int_book,c.book,int_code,c.code,place_name;";

   // throw new Exception(json_encode($SQL));
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        $i=0;
        while ($row = pg_fetch_array($result)) {
            $i++;
            $abon = htmlspecialchars($row['abon']);
            $comment = htmlspecialchars($row['comment']);
            $addr = htmlspecialchars($row['town'].' '.$row['street'].' '.$row['house']);
            $book=$row['book'].'/'.$row['code'];
            
            echo "
            <tr >
            <td class='c_i'>$i</td>
            <td class='c_t'>$book</td>
            <td>$abon</td>
            <td>$addr</td>
            <td class='c_t'>{$row['type_meter']}</td>
            <td class='c_t'>{$row['num_meter']}</td>
            <td>{$row['place_name']}</td>
            <td>{$row['type_name']}</td>
            <td class='c_t'>{$row['plomb_num']}</td>
            <td>{$row['dt_on']}</td>
            <td>{$row['person_on']}</td>
            <td>{$comment}</td>

            </tr>  ";        
            
        }
        
    }

    eval("\$footer_text_eval = \"$footer_text\";");
    echo $footer_text_eval;
   
}
//------------------------- оборот по 1 абоненту ----------------------
if ($oper=='oborab')
{
    $p_mmgg1 = sql_field_val('dt_b', 'mmgg');
    $p_mmgg2 = sql_field_val('dt_e', 'mmgg');
    
    //$mmgg1 = strtotime(str_replace("'",'',$p_mmgg1));
    //$mmgg1_str = date('m.Y',$mmgg1);
    $mmgg1_str = ukr_date($_POST['dt_b'],0,2);

    //$mmgg2 = DateTime::createFromFormat('Y-m-d', $p_mmgg2);
    //$mmgg2 = strtotime(str_replace("'",'',$p_mmgg2));
    //$mmgg2_str = $mmgg2->format('m.Y');
    //$mmgg2_str = date('m.Y',$mmgg2);
    $mmgg2_str = ukr_date($_POST['dt_e'],0,1);
    
    $p_id_paccnt = sql_field_val('id_paccnt', 'int');
    
    if ($p_id_paccnt=='null')
    {
        print ('<p> Вкажіть абонента, по якому формувати звіт!</p>');
        print ('</body> </html>');
        return;
    }
    
    $SQL = "select acc.id, acc.book, acc.code,address_print_full(acc.addr,4) as addr_str,
    (c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon,
    -round(s.b_val,2) as saldo_b,acc.id_gtar, tar.id_lgt_group
     from clm_paccnt_tbl as acc 
    join clm_abon_tbl as c on (c.id = acc.id_abon) 
    join adt_addr_tbl as adr on (adr.id = (acc.addr).id_class)
    join aqi_grptar_tbl as tar on (tar.id = acc.id_gtar)
    left join acm_saldo_tbl as s on (s.id_paccnt = acc.id and s.mmgg = $p_mmgg1 and s.id_pref = 10)
    where acc.id = $p_id_paccnt ";

   // throw new Exception(json_encode($SQL));
    //$callStartTime = microtime(true);
     
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    
    if ($result) {
        $row = pg_fetch_array($result);
    
        $abon_name =  $row['abon'];
        $book =  $row['book'];
        $code =  $row['code'];
        $addr =  $row['addr_str'];
        $saldo_b =  $row['saldo_b'];
        $id_lgt_group =  $row['id_lgt_group'];        
     }

    $SQL = "select g.name,l.family_cnt, n.norm_min,n.norm_one, 
        int4smaller(n.norm_min+n.norm_one*l.family_cnt,n.norm_max) as norm_abon
    from lgm_abon_tbl as l
    join lgi_group_tbl as g on (l.id_grp_lgt = g.id)
    join lgi_norm_tbl as  n on (n.id_calc = g.id_calc and (n.id_tar_grp = $id_lgt_group or n.id_tar_grp is null))
    where l.id_paccnt = $p_id_paccnt 
     and (l.dt_end is null or dt_end >= $p_mmgg2 ) and n.dt_e is null; ";

   // throw new Exception(json_encode($SQL));
    //$callStartTime = microtime(true);
     
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    
    if ($result) {
        $row = pg_fetch_array($result);
    
        $lgt_name =  $row['name'];
        $lgt_norm =  $row['norm_abon'];
     } 
    
     
    eval("\$header_text = \"$header_text\";");
    echo $header_text;
     
    $SQL = "select to_char(s.mmgg, 'DD.MM.YYYY') as mmgg, -b_val as b_val, -b_valtax as b_valtax, dt_val, dt_valtax, 
       kt_val, kt_valtax, -e_val as e_val, -e_valtax as e_valtax ,ps.subs_pay, br.sum_recalc,
       blg.summ_lgt, blg.kvt_lgt
     from acm_saldo_tbl as s
     left join (
       select mmgg, round(sum(value) ,2) as subs_pay
       from acm_pay_tbl as p
       where p.id_paccnt = $p_id_paccnt
       and p.id_pref = 10 and p.idk_doc in ( 110,111, 193,194) 
       group by mmgg
     ) as ps on (ps.mmgg = s.mmgg)
     left join 
     (
        select b.mmgg, sum(value) as sum_recalc
        from acm_bill_tbl as b 
        where b.id_paccnt = $p_id_paccnt 
        and b.mmgg_bill < b.mmgg
        and b.id_pref = 10                                
        group by b.mmgg
     ) as br on (br.mmgg = s.mmgg)
     left join 
    (
      select b.mmgg, sum(ls.summ_lgt) as summ_lgt, sum(ls.demand_lgt) as kvt_lgt
      from acm_bill_tbl as b 
      join acm_lgt_summ_tbl as ls on (b.id_doc= ls.id_doc) 
      where b.id_paccnt = $p_id_paccnt  and b.idk_doc in (200,220) and b.id_pref = 10
      group by b.mmgg
    ) as blg on (blg.mmgg = s.mmgg)
     where s.id_paccnt = $p_id_paccnt
     and s.mmgg >= $p_mmgg1 and s.mmgg <= $p_mmgg2  and s.id_pref = 10
     order by s.mmgg;";
     
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);    
    
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        
        
        //echo 'Start row inserting';
        
        while ($row = pg_fetch_array($result)) {

            $i = 1;
            //    ---------------------------------------------------------------
            $tmp_data = array(); // array of columns
            for($c=1; $c<=22; $c++)
            {
                $tmp_data[$c] = array(); // array of cells for column $c
                for($r=1; $r<=10; $r++){
                    $tmp_data[$c][$r] = '';
                }
            }    
            //    ---------------------------------------------------------------
            
            $month = $row['mmgg'];
            
            $r =  $i++;
            $str_month = $r;
            //$Sheet->insertNewRowBefore($str_month, 1);
            
            $kvt_all_mmgg = 0;
            $summ_all_mmgg = 0;

            $kvt_lgt_mmgg = 0;
            $summ_lgt_mmgg = 0;

            $kvt_norm_mmgg = 0;
            $summ_norm_mmgg = 0;

            $kvt_over_mmgg = 0;
            $summ_over_mmgg = 0;
            
            //$Sheet->setCellValueByColumnAndRow(1, $str_month, $month);
            $tmp_data[1][$str_month] = $month;

            $SQLz = "select i.id_zone,z.nm, max(source) as source
            from (select i.id_zone, 0 as source  from acm_indication_tbl as i where i.id_paccnt = $p_id_paccnt
                   and i.mmgg = '{$month}' 
                  union  
                   select i2.id_zone, 1 as source from acm_inpdemand_tbl as i2 where i2.id_paccnt = $p_id_paccnt
                   and i2.mmgg = '{$month}'                   
            ) as i
            join eqk_zone_tbl as z on (z.id = i.id_zone)
            group by i.id_zone,z.nm       
            order by i.id_zone ";

            $result_z = pg_query($Link, $SQLz) or die("SQL Error: " . pg_last_error($Link) . $SQL);
            $rows_z = pg_num_rows($result_z);

            while ($row_z = pg_fetch_array($result_z)) {

                if($rows_z>1)
                {
                    $r =  $i++;
                    //$Sheet->insertNewRowBefore($r, 1);
                }    
                
                $id_zone = $row_z['id_zone'];
                $demand_source = $row_z['source'];
                $name_zone = $row_z['nm'];
                
                $str_zone = $r;
                
                
                if ($demand_source==0)
                {
                  $SQL2 = "select i.dat_ind, i.id_meter,i.num_eqp,i.id_zone,z.nm,
                  i.value,  ip.value as value_prev, i.value_cons
                  from acm_indication_tbl as i
                  join eqk_zone_tbl as z on (z.id = i.id_zone)
                  left join acm_indication_tbl as ip on (i.id_prev = ip.id)
                  where i.id_paccnt = $p_id_paccnt and z.id = $id_zone
                  and i.mmgg = '{$month}'
                  order by z.nm,i.num_eqp, i.dat_ind; ";
                }

                if ($demand_source==1)
                {
                  $SQL2 = "select null as dat_ind, i.id_meter,i.id_zone,z.nm,
                  null as value, null as value_prev, sum(demand) as value_cons
                  from acm_inpdemand_tbl as i
                  join eqk_zone_tbl as z on (z.id = i.id_zone)
                  where i.id_paccnt = $p_id_paccnt and z.id = $id_zone
                  and i.mmgg = '{$month}'
                  group by i.id_meter,i.id_zone,z.nm ";
                }
                
                
                $result_ind = pg_query($Link, $SQL2) or die("SQL Error: " . pg_last_error($Link) . $SQL);
                $rows_ind = pg_num_rows($result_ind);

                //$Sheet->setCellValueByColumnAndRow(1, $r, $month);

                if ($rows_ind <= 1) {
                    $row_ind = pg_fetch_array($result_ind);

                    //$Sheet->setCellValueByColumnAndRow(2, $r, $row_ind['value'])
                    //        ->setCellValueByColumnAndRow(3, $r, $row_ind['value_prev'])
                    //        ->setCellValueByColumnAndRow(4, $r, $row_ind['nm'])
                    //        ->setCellValueByColumnAndRow(6, $r, $row_ind['value_cons']);
                    
                    $tmp_data[2][$r] = number_format_ua($row_ind['value'],0);
                    $tmp_data[3][$r] = number_format_ua($row_ind['value_prev'],0);
                    $tmp_data[4][$r] = $row_ind['nm'];
                    $tmp_data[6][$r] = number_format_ua($row_ind['value_cons'],0);
                    
                    $kvt_all_mmgg+=$row_ind['value_cons'];
                } else {
                    $kvt_all_ind = 0;
                    //$r = $baseRow + $i++;
                    //$Sheet->insertNewRowBefore($r, 1);
                    
                    while ($row_ind = pg_fetch_array($result_ind)) {

                        $r =  $i++;
                        //$Sheet->insertNewRowBefore($r, 1);
                        
                        //$Sheet->setCellValueByColumnAndRow(2, $r, $row_ind['value'])
                        //        ->setCellValueByColumnAndRow(3, $r, $row_ind['value_prev'])
                        //        ->setCellValueByColumnAndRow(4, $r, $row_ind['nm'])
                        //        ->setCellValueByColumnAndRow(6, $r, $row_ind['value_cons']);

                        $tmp_data[2][$r] = number_format_ua($row_ind['value'],0);
                        $tmp_data[3][$r] = number_format_ua($row_ind['value_prev'],0);
                        $tmp_data[4][$r] = $row_ind['nm'];
                        $tmp_data[6][$r] = number_format_ua($row_ind['value_cons'],0);

                        
                        $kvt_all_ind+=$row_ind['value_cons'];
                        //$i++;
                    }
                    //$Sheet->setCellValueByColumnAndRow(6, $str_zone, $kvt_all_ind);
                    $tmp_data[6][$str_zone] = $kvt_all_ind;
                    $kvt_all_mmgg+=$kvt_all_ind;
                }

                $SQL3 = "select bs.id_zone,bs.id_summtarif, round(td.value*z.koef,4) as tarif_val,
                sum(bs.demand) as demand, sum(bs.summ) as summ 
                --, sum(lg.demand_lgt) as demand_lgt  , sum(lg.summ_lgt) as summ_lgt, max(lg.id_grp_lgt) as grp_lgt
                
                from acm_bill_tbl as b 
                join acm_summ_tbl as bs on (b.id_doc= bs.id_doc) 
                join eqk_zone_tbl as z on (z.id = bs.id_zone)
                join aqd_tarif_tbl as td on (td.id = bs.id_summtarif)
                -- left join acm_lgt_summ_tbl as lg on (lg.id_doc = bs.id_doc and lg.id_zone = bs.id_zone and lg.id_summtarif = bs.id_summtarif)
                where b.id_paccnt = $p_id_paccnt and z.id = $id_zone
                and b.mmgg = '{$month}' and b.mmgg = b.mmgg_bill
                and b.id_pref = 10                                
                group by bs.id_zone,bs.id_summtarif, tarif_val
                order by tarif_val ;";
                
                $result_tar = pg_query($Link, $SQL3) or die("SQL Error: " . pg_last_error($Link) . $SQL);
                $rows_tar = pg_num_rows($result_tar);
                
                if ($rows_tar <= 1) {
                    $row_tar = pg_fetch_array($result_tar);
/*
                    if ($row_tar['grp_lgt']==NULL) 
                    {
                        $kvt_norm = $row_tar['demand'];
                        $summ_norm = $row_tar['summ'];
                    }
                    else
                    {
                        //также надо учесть субсидию!
                        $kvt_norm = $row_tar['demand_lgt'];
                        $summ_norm = $row_tar['summ_lgt'];
                    }   
                        
                    $kvt_over = $row_tar['demand']-$kvt_norm;
                    $summ_over= $row_tar['summ']  -$summ_norm;
 * 
 */
                    /*
                    $Sheet->setCellValueByColumnAndRow(5, $str_zone, $row_tar['tarif_val'])
                                                  ->setCellValueByColumnAndRow(7, $str_zone, $row_tar['summ'])
                                                  ->setCellValueByColumnAndRow(8, $str_zone, $kvt_norm)
                                                  ->setCellValueByColumnAndRow(9, $str_zone, $summ_norm)
                                                  ->setCellValueByColumnAndRow(10, $str_zone,$kvt_over)
                                                  ->setCellValueByColumnAndRow(11, $str_zone,$summ_over )                            
                                                  ->setCellValueByColumnAndRow(12, $str_zone, $row_tar['demand_lgt'])
                                                  ->setCellValueByColumnAndRow(13, $str_zone, $row_tar['summ_lgt']);
                    */
                    $tmp_data[5][$str_zone] = $row_tar['tarif_val'];
                    $tmp_data[7][$str_zone] = number_format_ua($row_tar['summ'],2);
                    //$tmp_data[8][$str_zone] = number_format_ua($kvt_norm,0);
                    //$tmp_data[9][$str_zone] = number_format_ua($summ_norm,2);
                    //$tmp_data[10][$str_zone] = number_format_ua($kvt_over,0);
                    //$tmp_data[11][$str_zone] = number_format_ua($summ_over,2);
                    //$tmp_data[12][$str_zone] = number_format_ua($row_tar['demand_lgt'],0);
                    //$tmp_data[13][$str_zone] = number_format_ua($row_tar['summ_lgt'],2);
                    
                    
                    $summ_all_mmgg+=$row_tar['summ'];
                   // $kvt_lgt_mmgg+= $row_tar['demand_lgt'];
                   // $summ_lgt_mmgg+=$row_tar['summ_lgt'];
                    
//                    $kvt_norm_mmgg+=$kvt_norm;
//                    $summ_norm_mmgg+=$summ_norm;

//                    $kvt_over_mmgg+=$kvt_over;
//                    $summ_over_mmgg+= $summ_over;
                    
                    
                } else {
                    $summ_all_tar = 0;
                    
                    $summ_lgt_tar = 0;
                    $kvt_lgt_tar = 0;
                    
                    $kvt_norm_tar = 0;
                    $summ_norm_tar = 0;
                    
                    $kvt_over_tar = 0;
                    $summ_over_tar = 0;
                    
                    
                    while ($row_tar = pg_fetch_array($result_tar)) {

                        //$i++;
                        $r =  $i++;
                        //$Sheet->insertNewRowBefore($r, 1);
/*                        
                        if ($row_tar['grp_lgt']==NULL) 
                        {
                            $kvt_norm = $row_tar['demand'];
                            $summ_norm = $row_tar['summ'];
                        }
                        else
                        {
                            //также надо учесть субсидию!
                            $kvt_norm = $row_tar['demand_lgt'];
                            $summ_norm = $row_tar['summ_lgt'];
                        }   
                        
                        $kvt_over = $row_tar['demand']-$kvt_norm;
                        $summ_over= $row_tar['summ']  -$summ_norm;
*/                        
                        /*
                        $Sheet->setCellValueByColumnAndRow(4, $r, 'в т.ч.')
                                                     ->setCellValueByColumnAndRow(5, $r, $row_tar['tarif_val'])
                                                     ->setCellValueByColumnAndRow(6, $r, $row_tar['demand'])
                                                     ->setCellValueByColumnAndRow(7, $r, $row_tar['summ'])
                                                     ->setCellValueByColumnAndRow(8, $r, $kvt_norm)
                                                     ->setCellValueByColumnAndRow(9, $r, $summ_norm)
                                                     ->setCellValueByColumnAndRow(10, $r,$kvt_over)
                                                     ->setCellValueByColumnAndRow(11, $r,$summ_over )                            
                                                     ->setCellValueByColumnAndRow(12, $r, $row_tar['demand_lgt'])
                                                     ->setCellValueByColumnAndRow(13, $r, $row_tar['summ_lgt']);
                         */

                        $tmp_data[4][$r] = 'в т.ч.';
                        $tmp_data[5][$r] = $row_tar['tarif_val'];
                        $tmp_data[6][$r] = number_format_ua($row_tar['demand'],0);
                        $tmp_data[7][$r] = number_format_ua($row_tar['summ'],2);
                        //$tmp_data[8][$r] = number_format_ua($kvt_norm,0);
                        //$tmp_data[9][$r] = number_format_ua($summ_norm,2);
                        //$tmp_data[10][$r] = number_format_ua($kvt_over,0);
                        //$tmp_data[11][$r] = number_format_ua($summ_over,2);
                        //$tmp_data[12][$r] = number_format_ua($row_tar['demand_lgt'],0);
                        //$tmp_data[13][$r] = number_format_ua($row_tar['summ_lgt'],2);
                        
                        
                        
                        $summ_all_tar+=$row_tar['summ'];
                        //$summ_lgt_tar+=$row_tar['summ_lgt'];
                        //$kvt_lgt_tar+=$row_tar['demand_lgt'];
                        
//                        $kvt_norm_tar+=$kvt_norm;
//                        $summ_norm_tar+=$summ_norm;

//                        $kvt_over_tar+=$kvt_over;
//                        $summ_over_tar+= $summ_over;

                    }
                    /*
                    $Sheet->setCellValueByColumnAndRow(7, $str_zone, $summ_all_tar) // $r-$rows_tar
                                                  ->setCellValueByColumnAndRow(8, $str_zone, $kvt_norm_tar)
                                                  ->setCellValueByColumnAndRow(9, $str_zone, $summ_norm_tar)
                                                  ->setCellValueByColumnAndRow(10, $str_zone,$kvt_over_tar)
                                                  ->setCellValueByColumnAndRow(11, $str_zone,$summ_over_tar )                            
                                                  ->setCellValueByColumnAndRow(12, $str_zone, $kvt_lgt_tar)
                                                  ->setCellValueByColumnAndRow(13, $str_zone, $summ_lgt_tar);
                    */
                    $tmp_data[7][$str_zone] = number_format_ua($summ_all_tar,2);
                    //$tmp_data[8][$str_zone] = number_format_ua($kvt_norm_tar,0);
                    //$tmp_data[9][$str_zone] = number_format_ua($summ_norm_tar,2);
                    //$tmp_data[10][$str_zone] = number_format_ua($kvt_over_tar,0);
                    //$tmp_data[11][$str_zone] = number_format_ua($summ_over_tar,2);
                    //$tmp_data[12][$str_zone] = number_format_ua($kvt_lgt_tar,0);
                    //$tmp_data[13][$str_zone] = number_format_ua($summ_lgt_tar,2);
                    
                    $summ_all_mmgg+=$summ_all_tar;
                    //$kvt_lgt_mmgg+= $kvt_lgt_tar;
                    //$summ_lgt_mmgg+=$summ_lgt_tar;
                    
                    //$kvt_norm_mmgg+=$kvt_norm_tar;
                    //$summ_norm_mmgg+=$summ_norm_tar;

                    //$kvt_over_mmgg+=$kvt_over_tar;
                    //$summ_over_mmgg+= $summ_over_tar;
                    
                }
                
                
            }
            /*
            $Sheet->setCellValueByColumnAndRow(6, $str_month, $kvt_all_mmgg)
                                          ->setCellValueByColumnAndRow(7, $str_month, $summ_all_mmgg)
                                          ->setCellValueByColumnAndRow(8,  $str_month, $kvt_norm_mmgg)
                                          ->setCellValueByColumnAndRow(9,  $str_month, $summ_norm_mmgg)
                                          ->setCellValueByColumnAndRow(10, $str_month,$kvt_over_mmgg)
                                          ->setCellValueByColumnAndRow(11, $str_month,$summ_over_mmgg )                            
                                          ->setCellValueByColumnAndRow(12, $str_month, $kvt_lgt_mmgg)
                                          ->setCellValueByColumnAndRow(13, $str_month, $summ_lgt_mmgg)
                                          ->setCellValueByColumnAndRow(18, $str_month, $row['kt_val'])
                                          ->setCellValueByColumnAndRow(20, $str_month, $row['e_val'])
                                          ->setCellValueByColumnAndRow(22, $str_month, $row['e_val']); 
            */
            $tmp_data[6][$str_month] = number_format_ua($kvt_all_mmgg,0);
            $tmp_data[7][$str_month] = number_format_ua($summ_all_mmgg,2);
            //$tmp_data[8][$str_month] = number_format_ua($kvt_norm_mmgg,0);
            //$tmp_data[9][$str_month] = number_format_ua($summ_norm_mmgg,2);
            //$tmp_data[10][$str_month] = number_format_ua($kvt_over_mmgg,0);
            //$tmp_data[11][$str_month] = number_format_ua($summ_over_mmgg,2);
            $tmp_data[12][$str_month] = number_format_ua($row['kvt_lgt'],0);
            $tmp_data[13][$str_month] = number_format_ua($row['summ_lgt'],2); 

            $tmp_data[14][$str_month] = number_format_ua($row['subs_pay'],2);             
            //$tmp_data[15][$str_month] = number_format_ua($row['subs_pay'],2); 
            //$tmp_data[16][$str_month] = number_format_ua($row['subs_month']-$row['subs_pay'],2); 
            //$tmp_data[16][$str_month] = number_format_ua(0,2); 
            
            //$tmp_data[17][$str_month] = number_format_ua($row['subs_recalc'],2);            
            $tmp_data[18][$str_month] = number_format_ua(round($row['kt_val']-$row['subs_pay'] ,2),2);
            $tmp_data[20][$str_month] = number_format_ua($row['sum_recalc'],2);
            $tmp_data[22][$str_month] = number_format_ua($row['e_val'],2);
            //$i++;
            
            
            for($r=1; $r<=10; $r++)
            {
              if(($tmp_data[1][$r]!='')|| 
                  ($tmp_data[2][$r]!='')|| 
                  ($tmp_data[3][$r]!='')|| 
                  ($tmp_data[4][$r]!='')|| 
                  ($tmp_data[5][$r]!='')||
                  ($tmp_data[6][$r]!='')||
                  ($tmp_data[7][$r]!='')||
                  ($tmp_data[8][$r]!='')||
                  ($tmp_data[9][$r]!='')||
                  ($tmp_data[10][$r]!='')||
                  ($tmp_data[11][$r]!='')||
                  ($tmp_data[12][$r]!='')||
                  ($tmp_data[13][$r]!='')||
                  ($tmp_data[14][$r]!='')||
                  ($tmp_data[15][$r]!='')||
                  ($tmp_data[16][$r]!='')||
                  ($tmp_data[17][$r]!='')||
                  ($tmp_data[18][$r]!='')||
                  ($tmp_data[19][$r]!='')||
                  ($tmp_data[20][$r]!='')||
                  ($tmp_data[21][$r]!='')||
                  ($tmp_data[22][$r]!=''))
                {   
                echo "
                <tr >
                <td class='c_r'>{$tmp_data[1][$r]}</td>
                <td class='c_i'>{$tmp_data[2][$r]}</td>
                <td class='c_i'>{$tmp_data[3][$r]}</td>
                <td class='c_r'>{$tmp_data[4][$r]}</td>
                <td class='c_r'>{$tmp_data[5][$r]}</td>
                <td class='c_i'>{$tmp_data[6][$r]}</td>
                <td class='c_n'>{$tmp_data[7][$r]}</td>

                <td class='c_i'>{$tmp_data[12][$r]}</td>
                <td class='c_n'>{$tmp_data[13][$r]}</td>
                <td class='c_n'>{$tmp_data[14][$r]}</td>
                <td class='c_n'>{$tmp_data[18][$r]}</td>
                <td class='c_n'>{$tmp_data[20][$r]}</td>
                <td class='c_n'>{$tmp_data[22][$r]}</td>
                </tr>  ";        
               }
            }
            
        }
    }

    eval("\$footer_text_eval = \"$footer_text\";");
    echo $footer_text_eval;
     
}

//-----------------------------------------------------------------------------
if (($oper=='abon_subs1')||($oper=='abon_subs_return'))
{
    $p_mmgg = sql_field_val('dt_b', 'mmgg');
    
    $mmgg1_str = trim($_POST['period_str']);
    
    $p_id_town = sql_field_val('addr_town', 'int');
    $p_id_sector = sql_field_val('id_sector', 'int');
    $p_book = sql_field_val('book', 'string');
    $p_id_region = sql_field_val('id_region', 'int'); 
    $p_id_tar = sql_field_val('id_gtar', 'int');    

    if (isset($_POST['town_detal']))
        $p_town_detal = trim($_POST['town_detal']);
    else 
        $p_town_detal =0;

    $params_caption="";
    $where = '  ';

    if ($p_id_town!='null')
    {
        $where.= " and adr.id_town = $p_id_town ";
    }
    if ((isset($_POST['sector']))&&($_POST['sector']!=''))
        $params_caption.= ' Дiльниця: '. trim($_POST['sector']);

    if ($p_book!='null')
    {
        $where.= " and book = $p_book ";
        $params_caption .= " книга : $p_book ";
        
    }

   if ($p_id_sector!='null')
   {
      $where.= "and exists (select id from prs_runner_paccnt as rp 
          where rp.id_paccnt = c.id and rp.id_sector = $p_id_sector ) ";
   }
    
    if ($p_id_region!='null')
    {
        
        $where.= "and exists (select rs.id from 
            prs_runner_paccnt as rp 
            join prs_runner_sectors as rs on (rs.id = rp.id_sector)
            where rp.id_paccnt = c.id and rs.id_region =  $p_id_region ) ";

        $SQL_H="SELECT name from cli_region_tbl where id = $p_id_region ;";
    
        $result = pg_query($Link, $SQL_H) or die("SQL Error: " . pg_last_error($Link) . $SQL_H);
        $row = pg_fetch_array($result);
        
        $params_caption.= $row['name'];        
       
    }
    
    if ($p_id_tar!='null')
    {
        $where.= " and c.id_gtar = $p_id_tar ";

        if ((isset($_POST['gtar']))&&($_POST['gtar']!=''))
            $params_caption .= ' Тарифна група : '. trim($_POST['gtar']);
        
    }    
    
    $where_subs='';
    
    if ($oper=='abon_subs1')
    {
      $where_subs= " and p.idk_doc in (110,111,193) ";
    }

    if ($oper=='abon_subs_return')
    {
      $where_subs= " and p.idk_doc = 194 ";
    
      $params_caption.= " невикористана субсидія ";
    }
    
    $np=0;
    $print_flag = 1;
    $print_h_flag =1;
  
    $SQL = "select c.id, c.code, c.book, 
    adr.town, adr.street, address_print(c.addr) as house,
   (a.last_name||' '||coalesce(substr(a.name,1,1),'')||'.'||coalesce(substr(a.patron_name,1,1),''))||'.'::varchar as abon,
   p.value as sum_subs ,b.demand, t.sh_nm as tarname,
   CASE WHEN coalesce(sb.kol_subs,0) <=2 THEN 75 ELSE 75+(sb.kol_subs-2)*15 END as demand_norm ,
    ('0'||substring(c.code FROM '[0-9]+'))::int as int_code,
    ('0'||substring(c.book FROM '[0-9]+'))::int as int_book   
    
   from 
    clm_paccnt_h as c
    join (select id, max(dt_b) as dt_b from clm_paccnt_h  where 
    ((dt_b < ($p_mmgg::date+'1 month'::interval) and dt_e is null)
            or 
            tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
        )    
    group by id order by id) as c2 
    on (c.id = c2.id and c.dt_b = c2.dt_b)
    join clm_abon_tbl as a on (a.id = c.id_abon) 
    join aqi_grptar_tbl as t on (t.id = c.id_gtar)
    join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class)
    join (
        	select p.id_paccnt, sum(p.value) as value
        	from acm_pay_tbl as p 
        	where p.mmgg = $p_mmgg::date and p.id_pref = 10 
                $where_subs
        	group by p.id_paccnt
                order by p.id_paccnt
    ) as p  on (p.id_paccnt = c.id)
    left join (
        select id_paccnt, max(kol_subs) as kol_subs
        from acm_subs_tbl as s 
        where s.mmgg = $p_mmgg 
       	group by s.id_paccnt
        order by s.id_paccnt
    ) as sb on (sb.id_paccnt = c.id)
   left join (
        	select b.id_paccnt, sum(b.demand) as demand
                from acm_bill_tbl as b 
                where b.mmgg = $p_mmgg::date and b.id_pref = 10
                group by b.id_paccnt
                order by b.id_paccnt
   ) as b on (b.id_paccnt = c.id)
    
    where c.archive =0 
    $where
    order by town, int_book,book, int_code, code;";

   // throw new Exception(json_encode($SQL));
/*
     join (
        select distinct id_paccnt, kol_subs
        from acm_subs_tbl as s 
        join (select max(mmgg) as mmgg from acm_subs_tbl where mmgg <= $p_mmgg ) as mm on (mm.mmgg = s.mmgg)
         where  tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime)) and
         s.id_paccnt is not null
    ) as sb on (sb.id_paccnt = c.id)
 
 */    
    
    
    $current_town='';
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        //throw new Exception("Try to save file $rows_count ....");    
        
        $cnt_headers = 0;
        $nm=1;

        $sum_demand=0;
        $sum_subs_demand=0;
        $sum_subs_summ=0;

        $sum_town_demand=0;
        $sum_town_subs_demand=0;
        $sum_town_subs_summ=0;
        
        $sum_all_cnt=0;

            
        while ($row = pg_fetch_array($result)) {

            
            if ($current_town!=$row['town'])
            {
             
               if ($p_town_detal == 1) {

                    if (($cnt_headers == $grp_num - 1) || ($show_pager == 0))
                        $print_h_flag = 1;
                    else
                        $print_h_flag = 0;

                    if (($cnt_headers == $grp_num) || ($show_pager == 0))
                        $print_h2_flag = 1;
                    else
                        $print_h2_flag = 0;
                }

                if (($p_id_town!='null')||($p_town_detal==1))
                {
                    $current_town=$row['town'];
                }
                else
                {
                    $current_town='-Всі-';
                }
                    
                if ($cnt_headers == 0)
                {
                    eval("\$header_text_eval = \"$header_text\";");
                    echo_html($header_text_eval, $print_h_flag);
                }
                else
                {
                    if ($p_town_detal==1)
                    {
                        $nn = $nm-1;
                    
                        $sum_town_subs_summ_str = number_format_ua($sum_town_subs_summ,2);
                    
                        echo_html( "
                        <tr class='table_footer'>
                        <td colspan='2'>Всього - {$nn}</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td class='c_i'>{$sum_town_subs_demand}</td>
                        <td class='c_n'>{$sum_town_subs_summ_str}</td>
                        <td class='c_i'>{$sum_town_demand}</td>
                        </tr>  ",$print_h2_flag*$print_flag);
                    
                        //throw new Exception("Try to save file $rows_count ....");    

                        echo_html( $footer_text,$print_h2_flag);    
                    
                        $sum_town_demand=0;
                        $sum_town_subs_demand=0;
                        $sum_town_subs_summ=0;
                        $nm=1;   

                        eval("\$header_text_eval = \"$header_text\";");
                        echo_html($header_text_eval, $print_h_flag);

//                    echo $current_town;
                    }  
                }
                
                $cnt_headers++;
            }
            
                if ($print_h_flag==1) { $np++;  };
            
                if ((($np> ($page_num-1)* $page_size )&& ($np<= $page_num* $page_size )) || ($show_pager ==0))
                    $print_flag = 1;
                else
                    $print_flag = 0;
            
                if ($p_town_detal==1)
                    $addr = htmlspecialchars($row['street'].' '.$row['house']);
                else
                    $addr = htmlspecialchars($row['town'].' '.$row['street'].' '.$row['house']);
                
                $book=$row['book'].'/'.$row['code'];
                $abon = htmlspecialchars($row['abon']);
                $tar= htmlspecialchars($row['tarname']);
                $sum_subs_summ_str = number_format_ua($row['sum_subs'],2);
                
                 echo_html( "
                    <tr class='table_str'>
                     <td class='c_i'>{$nm}</td>                
                     <td>{$addr}</td>
                     <td>{$abon}</td>            
                     <td class='c_t'>{$book}</td>
                     <td class='c_t'>{$tar}</td>
                     <td class='c_i'>{$row['demand_norm']}</td>
                     <td class='c_n'>{$sum_subs_summ_str}</td>                     
                     <td class='c_i'>{$row['demand']}</td>
                    </tr>  ",$print_h_flag*$print_flag);        
            
                     $nm++;


            $sum_demand+=$row['demand'];
            $sum_subs_demand+=$row['demand_norm'];
            $sum_subs_summ+=$row['sum_subs'];

            $sum_town_demand+=$row['demand'];
            $sum_town_subs_demand+=$row['demand_norm'];
            $sum_town_subs_summ+=$row['sum_subs'];

            $sum_all_cnt++;
            
        }
    }
    if ($p_town_detal==1)
    {    
      $nn = $nm-1;
      $sum_town_subs_summ_str = number_format_ua($sum_town_subs_summ,2);
      echo_html( "
          <tr class='table_footer'>
          <td colspan='2'>Всього - {$nn}</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td class='c_i'>{$sum_town_subs_demand}</td>
          <td class='c_n'>{$sum_town_subs_summ_str}</td>
          <td class='c_i'>{$sum_town_demand}</td>
          </tr>  ",$print_h_flag*$print_flag);
    }
      $sum_subs_summ_str = number_format_ua($sum_subs_summ,2);
      echo_html( "
          <tr class='table_footer'>
          <td colspan='2'>Всього - {$sum_all_cnt}</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td class='c_i'>{$sum_subs_demand}</td>
          <td class='c_n'>{$sum_subs_summ_str}</td>
          <td class='c_i'>{$sum_demand}</td>
          </tr>  ",$print_h_flag*$print_flag);

    echo $footer_text;    
    
}

//-----------------------------------------------------------------------------
if ($oper=='abon_zerodem')
{
    $p_mmgg = sql_field_val('dt_b', 'date');
    
    $mmgg1_str = trim($_POST['period_str']);
    
    $p_id_town = sql_field_val('addr_town', 'int');
    $p_book = sql_field_val('book', 'string');
    $p_id_type_meter = sql_field_val('id_type_meter', 'int');    
    $p_id_sector = sql_field_val('id_sector', 'int');
    $p_id_region = sql_field_val('id_region', 'int');
    
    $p_id_dod = sql_field_val('id_cntrl', 'int');
    $p_name_dod = sql_field_val('cntrl', 'string');
    
    $params_caption='';

    if (isset($_POST['town_detal']))
        $p_town_detal = trim($_POST['town_detal']);
    else 
        $p_town_detal =0;

  /*  
    if ((isset($_POST['type_meter']))&&($_POST['type_meter']!=''))
        $params_caption .= ' Лічильник : '. trim($_POST['type_meter']);
*/    
    if ((isset($_POST['sector']))&&($_POST['sector']!=''))
        $params_caption.= ' Дiльниця: '. trim($_POST['sector']);
    
    
    $where = '  ';

    if ($p_id_town!='null')
    {
        $where.= " and adr.id_town = $p_id_town ";
    }
    
    if ($p_book!='null')
    {
        $where.= " and book = $p_book ";
        $params_caption .= " книга : $p_book ";
        
    }
    /*
    if ($p_id_type_meter!='null')
    {
        $where.= "and m.id_type_meter = $p_id_type_meter ";
    }
    */
    if ($p_id_sector!='null')
    {
        $where.= "and exists (select id from prs_runner_paccnt as rp 
            where rp.id_paccnt = c.id and rp.id_sector = $p_id_sector ) ";
        
    }
    

    if ($p_id_region!='null')
    {
        
        $where.= "and exists (select rs.id from 
            prs_runner_paccnt as rp 
            join prs_runner_sectors as rs on (rs.id = rp.id_sector)
            where rp.id_paccnt = c.id and rs.id_region =  $p_id_region ) ";

        $SQL_H="SELECT name from cli_region_tbl where id = $p_id_region ;";
    
        $result = pg_query($Link, $SQL_H) or die("SQL Error: " . pg_last_error($Link) . $SQL_H);
        $row = pg_fetch_array($result);
        
        $params_caption.= $row['name'];        
       
    }
  
    if ($p_id_dod!='null')
    {
        $where.= "and c.id_cntrl = $p_id_dod ";
        $params_caption .= " Додаткова ознака : $p_name_dod ";
    }
    
    
    $SQL = "select c.id, c.code, c.book, 
    adr.town, adr.street, address_print(c.addr) as house,
   ((a.last_name||' '||coalesce(substr(a.name,1,1),'')||'.'||coalesce(substr(a.patron_name,1,1),''))||'.')::varchar as abon,
   coalesce(sw_name||' '||sw_dt, 'Не проживає '||nolive_dt_b||'-'||nolive_dt_e,CASE WHEN c.not_live THEN 'Не проживає' END, ht.name,'' ) as info,
   ('0'||substring(c.code FROM '[0-9]+'))::int as int_code,
   ('0'||substring(c.book FROM '[0-9]+'))::int as int_book
   from 
    clm_paccnt_h as c
    join (select id, max(dt_b) as dt_b from clm_paccnt_h  where
    ((dt_b < ($p_mmgg::date+'1 month'::interval) and dt_e is null)
            or 
            tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
        )
    group by id order by id) as c2 
    on (c.id = c2.id and c.dt_b = c2.dt_b)

    join clm_abon_tbl as a on (a.id = c.id_abon) 
    --join (select id, max(dt_b) as dt_b from clm_abon_h  where  
    --((dt_b < ($p_mmgg::date+'1 month'::interval) and dt_e is null)
    --        or 
    --        tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
    --    )
    --   group by id order by id) as a2 on (a.id = a2.id and a.dt_b = a2.dt_b)
   left join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class)
   left join cli_house_type_tbl as ht on (ht.id = c.idk_house) 
  left join (
       select csw.id_paccnt, to_char(coalesce(csw.dt_action,csw.dt_create), 'DD.MM.YYYY') as sw_dt , csw.action, a.name as sw_name
       from clm_switching_tbl as csw 
        join cli_switch_action_tbl as a on (a.id = csw.action )
        join (select id_paccnt, max(dt_action) as maxdt from clm_switching_tbl where action in (1,2) group by id_paccnt) as csdt 
       on (csw.id_paccnt = csdt.id_paccnt and csw.dt_action = csdt.maxdt) 
       left join clm_switching_tbl as cswc on (csw.id_paccnt = cswc.id_paccnt and cswc.dt_action >= csw.dt_action and ((cswc.action =3 and csw.action =1) or (cswc.action =4  and csw.action =2) ) ) 
       where csw.action =1 and cswc.id_paccnt is null 
       order by csw.id_paccnt
  ) as sw on (sw.id_paccnt = c.id)
   left join (
      	select n.id_paccnt, 
        to_char(n.dt_b, 'DD.MM.YYYY') as nolive_dt_b, 
        to_char(n.dt_e, 'DD.MM.YYYY') as nolive_dt_e    
        from clm_notlive_tbl as n 
       where n.dt_b<= $p_mmgg::date+'1 month -1 day'::interval and ((n.dt_e is null) or (n.dt_e>= $p_mmgg::date))
       order by n.id_paccnt
   ) as n on (n.id_paccnt = c.id)
    where c.archive =0 
    and not exists
    (
        select b.id_paccnt   from acm_bill_tbl as b 
        where b.mmgg = $p_mmgg::date and b.id_pref = 10 
        and b.demand <> 0 and b.id_paccnt = c.id
    )
    $where
    order by town, int_book, int_code;";

   // throw new Exception(json_encode($SQL));
    
    $current_town='null';
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        //throw new Exception("Try to save file $rows_count ....");    
        
        $cnt_headers = 0;
        $nm=1;
        
        $sum_all_cnt=0;

        $np=0;
        
        while ($row = pg_fetch_array($result)) {

            
            if ($current_town!=$row['town'])
            {
            //
                if (($cnt_headers == $grp_num-1) || ($show_pager ==0)||($p_town_detal==0) )
                   $print_h_flag = 1;
                else
                   $print_h_flag = 0;

                if (($cnt_headers == $grp_num) || ($show_pager ==0)||($p_town_detal==0))
                   $print_h2_flag = 1;
                else
                   $print_h2_flag = 0;
            //    
                
                if (($p_id_town!='null')||($p_town_detal==1))
                {
                    $current_town=$row['town'];
                }
                else
                {
                    $current_town='-Всі-';
                }
                

                if ($cnt_headers == 0)
                {
                    eval("\$header_text_eval = \"$header_text\";");
                    echo_html( $header_text_eval,$print_h_flag);
                }
                else
                {
                    if ($p_town_detal==1)
                    {
                        $nnn = $nm-1;
                    
                        echo_html( "
                        <tr class='table_footer'>
                            <td colspan='2'>Всього - {$nnn}</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>                     
                        </tr>  ",$print_h2_flag);        
                    
                        echo_html( $footer_text,$print_h2_flag);    
                    
                        $nm=1;   

                        eval("\$header_text_eval = \"$header_text\";");
                        echo_html( $header_text_eval,$print_h_flag);

                    }
                }
                
                $cnt_headers++;
            }
            
            if ($print_h_flag==1) { $np++;  };
            
            if ((($np> ($page_num-1)* $page_size )&& ($np<= $page_num* $page_size )) || ($show_pager ==0))
                $print_flag = 1;
            else
                $print_flag = 0;

            
                if ($p_town_detal==1)
                    $addr = htmlspecialchars($row['street'].' '.$row['house']);
                else
                    $addr = htmlspecialchars($row['town'].' '.$row['street'].' '.$row['house']);
                
                $book=$row['book'].'/'.$row['code'];
                $abon = htmlspecialchars($row['abon']);
                $info = htmlspecialchars($row['info']);                
                
                 echo_html( "
                    <tr class='table_str'>
                     <td class='c_i'>{$nm}</td>                
                     <td>{$addr}</td>
                     <td>{$abon}</td>            
                     <td class='c_t'>{$book}</td>
                     <td>{$info}</td>                     
                    </tr>  ",$print_h_flag*$print_flag);        
            
                     $nm++;

            $sum_all_cnt++;
            
        }
    }

      $nnn = $nm-1;

     if ($p_town_detal==1)
     {
      
      echo_html( "
          <tr class='table_footer'>
          <td colspan='2'>Всього - {$nnn}</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          </tr>  ",$print_h_flag*$print_flag);
     }
     
      echo_html( "
          <tr class='table_footer'>
          <td colspan='2'>Всього - {$sum_all_cnt}</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          </tr>  ",$print_h_flag*$print_flag);

    echo_html( $footer_text , $print_h_flag);
    
}

//----------------------------------------------------------------------------
if ($oper=='abon_list_max')
 {
        
    $p_mmgg = sql_field_val('dt_b', 'mmgg');
    $p_dt = sql_field_val('dt_rep', 'date');
    $dt_str = $_POST['dt_rep'];
    $p_book = sql_field_val('book', 'string');
    $p_id_town = sql_field_val('addr_town', 'int');
    $p_id_tar = sql_field_val('id_gtar', 'int');    
    $p_id_type_meter = sql_field_val('id_type_meter', 'int');    
    $p_id_type_meter_array = sql_field_val('id_type_meter_array', 'array');    
    $p_id_sector = sql_field_val('id_sector', 'int');
    $p_id_lgt = sql_field_val('id_grp_lgt', 'int');    
    $p_id_region = sql_field_val('id_region', 'int');
    $p_id_dod = sql_field_val('id_cntrl', 'int');
    $p_name_dod = sql_field_val('cntrl', 'string');
        
    
    if (isset($_POST['town_detal']))
        $p_town_detal = trim($_POST['town_detal']);
    else 
        $p_town_detal =0;

    if ((isset($_POST['addr_town_name']))&&($_POST['addr_town_name']!=''))
        $params_caption = 'Населений пункт: '. trim($_POST['addr_town_name']);
    else 
        $params_caption ='';

    if ((isset($_POST['gtar']))&&($_POST['gtar']!=''))
        $params_caption .= ' Тарифна група : '. trim($_POST['gtar']);
    
    if ((isset($_POST['type_meter']))&&($_POST['type_meter']!=''))
        $params_caption .= ' Лічильник : '. trim($_POST['type_meter']);
    
    if ((isset($_POST['sector']))&&($_POST['sector']!=''))
        $params_caption.= ' Дiльниця: '. trim($_POST['sector']);

    if ((isset($_POST['grp_lgt']))&&($_POST['grp_lgt']!=''))
        $params_caption.= ' Що мають пільгу: '. trim($_POST['grp_lgt']);
    
    if ($params_caption !='') $params_caption .='<br/>';
        
    $where = ' ';

    if ($rem_worker == 1)
    {
        $params_caption.= ' Працівники РЕМ ';
        $where.= " and c.rem_worker = true ";
    }
    
    if ((isset($_POST['note']))&&($_POST['note']!=''))
    {
        $where.= $_POST['note'];
        $params_caption .= $_POST['note'];
    }
    
    
    if ($p_id_town!='null')
    {
        $where.= "and adr.id_town = $p_id_town ";
    }

    if ($p_id_tar!='null')
    {
        $where.= "and c.id_gtar = $p_id_tar ";
    }
    
    if ($p_book!='null')
    {
        $where.= " and book = $p_book ";
        $params_caption .= " книга : $p_book ";
        
    }

    if ($p_id_dod!='null')
    {
        $where.= "and c.id_cntrl = $p_id_dod ";
        $params_caption .= " Додаткова ознака : $p_name_dod ";
    }
    
    if ($p_id_lgt!='null')
    {
        if ($where!='where') $where.= " and ";
        
        $where.= " exists (select id from lgm_abon_h as hh where id_grp_lgt = $p_id_lgt 
        and hh.id_paccnt = c.id and 
         (hh.dt_b < ($p_mmgg::date+'1 month'::interval)) and 
        
         ( ( hh.dt_e is null and (hh.dt_end is null or hh.dt_end >= $p_mmgg )  )
             or hh.dt_e >= $p_mmgg)
        ) ";
    }


    
    
    if ($p_id_type_meter_array!='null')
    {
        
     $json_str = stripslashes($p_id_type_meter_array); 
     $meter_id_list = json_decode($json_str,true);
    
     $where.= " and m.id_type_meter in ( ";
    
     $i = 0;
     foreach($meter_id_list as $id_meter) {
    
        if ($i>0) $where.=",";
        
        $where.="$id_meter";
        $i++;
     }
     $where.=") ";
        
    }
    else
    {            
      if ($p_id_type_meter!='null')
      {
        $where.= "and m.id_type_meter = $p_id_type_meter ";
      }
    }
    
    if ($p_id_sector!='null')
    {
        $where.= "and exists (select id from prs_runner_paccnt as rp 
            where rp.id_paccnt = c.id and rp.id_sector = $p_id_sector ) ";
        
    }
    

    if ($p_id_region!='null')
    {
        
        $where.= "and exists (select rs.id from 
            prs_runner_paccnt as rp 
            join prs_runner_sectors as rs on (rs.id = rp.id_sector)
            where rp.id_paccnt = c.id and rs.id_region =  $p_id_region ) ";

        $SQL_H="SELECT name from cli_region_tbl where id = $p_id_region ;";
    
        $result = pg_query($Link, $SQL_H) or die("SQL Error: " . pg_last_error($Link) . $SQL_H);
        $row = pg_fetch_array($result);
        
        $params_caption.= $row['name'];        
       
    }    
    if ($p_town_detal ==1)
    {
        $order ='town, int_book,book, int_code,code';
    }
    else
    {
        $order ='int_book, book, int_code,code';
    }
    
    eval("\$header_text = \"$header_text\";");
    
    echo_html($header_text);
    
    
    $SQL = "select c.id,c.code,c.book, ap.name as add_param, 
    adr.town, adr.street, 
    (coalesce((c.addr).house,'')||
	coalesce('/'||(c.addr).slash||' ','')||
            coalesce(' корп.'||(c.addr).korp||'',''))::varchar as house,
        (coalesce((c.addr).flat,'')||coalesce('/'||(c.addr).f_slash,''))::varchar as flat,
    
   (a.last_name||' '||coalesce(substr(a.name,1,1),'')||'.'||coalesce(substr(a.patron_name,1,1),''))||'.'::varchar as abon,
   CASE WHEN adr.is_town = 1 THEN 'Місто' ELSE 'Село' END::varchar as is_town ,
   tar.sh_nm as tarif, ht.name as k_house, c.heat_area, c.n_subs, m.power,m.num_meter,m.coef_comp,m.carry, m.id_type_meter,
    to_char(m.dt_start, 'DD.MM.YYYY') as dt_start, to_char(m.dt_control, 'DD.MM.YYYY') as dt_control,
    im.name as type_meter,zzz.zones,
    CASE WHEN im.phase = 1 THEN '1' WHEN im.phase = 2 THEN '3' END::varchar as phase,
    mp.name as meter_place,prs.represent_name, tp.name as tp_name, 
    CASE WHEN c.activ THEN 1 END as activ,CASE WHEN c.rem_worker THEN 1 END as rem_worker,
    CASE WHEN c.not_live THEN 1 END as not_live,  CASE WHEN c.pers_cntrl THEN 1 END as pers_cntrl,c.note,
    ('0'||substring(c.code FROM '[0-9]+'))::int as int_code,
    ('0'||substring(c.book FROM '[0-9]+'))::int as int_book
from 
    clm_paccnt_h as c
    join (select id, max(dt_b) as dt_b from clm_paccnt_h  where dt_b <= $p_dt and coalesce(dt_e,$p_dt) >=$p_dt group by id order by id) as c2 
    on (c.id = c2.id and c.dt_b = c2.dt_b)

    join clm_meterpoint_h as m on (m.id_paccnt = c.id) 
    join (select id, max(dt_b) as dt_b from clm_meterpoint_h  where dt_b <= $p_dt and coalesce(dt_e,$p_dt) >=$p_dt group by id order by id) as m2 
    on (m.id = m2.id and m.dt_b = m2.dt_b)

    join clm_abon_tbl as a on (a.id = c.id_abon) 
    --join (select id, max(dt_b) as dt_b from clm_abon_h  where dt_b <= $p_dt and coalesce(dt_e,$p_dt) >=$p_dt group by id order by id) as a2 
    --on (a.id = a2.id and a.dt_b = a2.dt_b)
    
    join ( select z.id_meter, trim(sum(iz.nm||','),',')::varchar as zones
     from clm_meter_zone_h as z 
     join eqk_zone_tbl as iz on (iz.id = z.id_zone)
     where z.dt_b <= $p_dt and coalesce(z.dt_e,$p_dt) >=$p_dt
      group by z.id_meter order by z.id_meter
    ) as zzz on (zzz.id_meter = m.id)
    
    join eqi_meter_tbl as im on (im.id = m.id_type_meter)
    join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class)
    left join aqi_grptar_tbl as tar on (tar.id = c.id_gtar)
    left join cli_house_type_tbl as ht on (ht.id = c.idk_house)
    left join eqk_meter_places_tbl as mp on (mp.id = m.id_extra)
    left join prs_runner_paccnt as rp on (rp.id_paccnt = c.id)
    left join prs_runner_sectors as rs on (rp.id_sector = rs.id) 
    left join prs_persons as prs on (prs.id = rs.id_kontrol)
    left join eqm_tp_tbl as tp on (tp.id = m.id_station)
    left join cli_addparam_tbl as ap on (ap.id = c.id_cntrl)
where c.archive=0 $where 
order by $order ;";

  
    $cur_town='';
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        $np=0;
        $i=1;
        while ($row = pg_fetch_array($result)) {

            $np++;
            
            if ((($np> ($page_num-1)* $page_size )&& ($np<= $page_num* $page_size )) || ($show_pager ==0))
                $print_flag = 1;
            else
                $print_flag = 0;
                
            if (($row['town']!=$cur_town)&&($p_town_detal==1))
            {
                $cur_town=$row['town'];
                
                $town_str = htmlspecialchars($cur_town);
                echo_html("
                <tr >
                <td COLSPAN=27 class = 'tab_head'>{$town_str}</td>
                </tr>  ",$print_flag);        
                $i=1;
            }
            
            $abon = htmlspecialchars($row['abon']);
            //$addr = htmlspecialchars($row['town'].' '.$row['street'].' '.$row['house']);
            $town = htmlspecialchars($row['town']);
            $street = htmlspecialchars($row['street']);
            $book=$row['book'];
            $code=$row['code'];
            $power_txt = number_format_ua ($row['power'],1);  
            $tar = htmlspecialchars($row['tarif']);
            $zon = htmlspecialchars($row['zones']);
            $insp = htmlspecialchars($row['represent_name']);
            $tp = htmlspecialchars($row['tp_name']);
            $note = htmlspecialchars($row['note']);
            $add_param = htmlspecialchars($row['add_param']);
            
            echo_html("
            <tr >
            <td>{$i}</td>
            <td class='c_t'>{$book}</td>
            <td class='c_t'>{$code}</td>
            <td>{$abon}</td>
            <td>{$town}</td>
            <td>{$street}</td>
            <td class='c_t'>{$row['house']}</td>
            <td class='c_t'>{$row['flat']}</td>
            <td class='c_t'>{$row['is_town']}</td>
            <td class='c_t'>{$tar}</td>
            <td class='c_t'>{$row['k_house']}</td>
            <td class='c_n'>{$row['heat_area']}</td>
            <td class='c_t'>{$row['n_subs']}</td>
            <td class='c_n'>{$power_txt}</td>
            <td class='c_t'>{$row['num_meter']}</td>
            <td class='c_t'>{$row['type_meter']}</td>
            <td class='c_i'>{$row['carry']}</td>
            <td class='c_i'>{$row['coef_comp']}</td>
            <td >{$row['dt_start']}</td>
            <td >{$row['dt_control']}</td>
            <td class='c_t'>{$zon}</td>
            <td class='c_i'>{$row['phase']}</td>
            <td class='c_t'>{$row['meter_place']}</td>
            
            <td class='c_t'>{$insp}</td>            
            <td class='c_t'>{$tp}</td>                        
            
            <td class='c_r'>{$row['activ']}</td>                        
            <td class='c_r'>{$row['rem_worker']}</td>                        
            <td class='c_r'>{$row['not_live']}</td>                        
            <td class='c_r'>{$row['pers_cntrl']}</td>                        
            <td class='c_t'>{$add_param}</td>
            <td class='c_t'>{$note}</td>
            </tr>  ",$print_flag);         
            $i++;
        }
        
    }
    
    eval("\$footer_text_eval = \"$footer_text\";");
    echo_html($footer_text_eval);

    //return;
    
}
//----------------------------------------------------------------------------
if (($oper=='abon_list_sector')||($oper=='abon_indic_null'))
 {
    $p_dt = sql_field_val('dt_rep', 'date');
    $dt_str = $_POST['dt_rep'];
    $params_caption ='';
    $p_book = sql_field_val('book', 'string');
    $p_mmgg = sql_field_val('dt_b', 'mmgg');
    list($year, $month, $day  ) = split('[/.-]', $p_mmgg);
    //$period = "за $year р.";
    $period = "за ".$_POST['period_str'];
    
    $p_id_region = sql_field_val('id_region', 'int'); 
    $p_id_town = sql_field_val('addr_town', 'int');
    $p_id_sector = sql_field_val('id_sector', 'int');
    $p_id_person = sql_field_val('id_person', 'int');    
    $p_id_type_meter = sql_field_val('id_type_meter', 'int');    
    $p_id_type_meter_array = sql_field_val('id_type_meter_array', 'array');    
    
    if (isset($_POST['town_detal']))
        $p_town_detal = trim($_POST['town_detal']);
    else 
        $p_town_detal =0;

    if ((isset($_POST['addr_town_name']))&&($_POST['addr_town_name']!=''))
        $params_caption.= 'Населений пункт: '. trim($_POST['addr_town_name']);

    if ((isset($_POST['person']))&&($_POST['person']!=''))
        $params_caption.= ' Інспектор: '. trim($_POST['person']);

    if ((isset($_POST['sector']))&&($_POST['sector']!=''))
        $params_caption.= ' Дільниця: '. trim($_POST['sector']);
    
    if ((isset($_POST['type_meter']))&&($_POST['type_meter']!=''))
        $params_caption .= ' Лічильник : '. trim($_POST['type_meter']);

    $where = ' ';
    
    if ($p_book!='null')
    {
        $where.= " and c.book = $p_book ";
        $params_caption .= " книга : $p_book ";
    }
    
    if ($params_caption !='') $params_caption .='<br/>';

    if ($p_id_town!='null')
    {
        $where.= "and adr.id_town = $p_id_town ";
    }

    if ($p_id_sector!='null')
    {
        $where.= "and rr.id = $p_id_sector ";
    }

    if ($p_id_region!='null')
    {
        
        $where.= " and rr.id_region =  $p_id_region ";
        
        $SQL_H="SELECT name from cli_region_tbl where id = $p_id_region ;";
    
        $result = pg_query($Link, $SQL_H) or die("SQL Error: " . pg_last_error($Link) . $SQL_H);
        $row = pg_fetch_array($result);
        
        $params_caption.= $row['name'];               
    }    
    
    if ($p_id_person!='null')
    {
        $where.= "and rr.id_runner = $p_id_person ";
    }
    
    if ($p_id_type_meter_array!='null')
    {
        
     $json_str = stripslashes($p_id_type_meter_array); 
     $meter_id_list = json_decode($json_str,true);
    
     $where.= " and m.id_type_meter in ( ";
    
     $i = 0;
     foreach($meter_id_list as $id_meter) {
    
        if ($i>0) $where.=",";
        
        $where.="$id_meter";
        $i++;
     }
     $where.=") ";
        
    }
    else
    {            
      if ($p_id_type_meter!='null')
      {
        $where.= "and m.id_type_meter = $p_id_type_meter ";
      }
    }
  
    if ((isset($_POST['note']))&&($_POST['note']!=''))
    {
        $where.= $_POST['note'];
        $params_caption .= $_POST['note'];
    }    
    
    
    $where_ind='';
    if($oper=='abon_indic_null')
    {
        $where_ind=" where mmgg = $p_mmgg";
        $where.= " and ind.id_meter is null  ";
        $params_caption.=" не мають показників $period ";
    }
    
    $np=0;
    $print_flag = 1;
    
    eval("\$header_text = \"$header_text\";");
    
    echo_html( $header_text);
    
    $SQL = "select c.id,c.code,c.book, 
    adr.town, adr.street, address_print(c.addr) as house,
   (a.last_name||' '||coalesce(substr(a.name,1,1),'')||'.'||coalesce(substr(a.patron_name,1,1),''))||'.'::varchar as abon,
    ht.name as k_house, m.num_meter, ind.dat_ind,  to_char(ind.dat_ind, 'DD.MM.YYYY') as dat_ind_txt, m.id_type_meter, 
    im.name as type_meter, mp.name as meter_place,
    coalesce(represent_name,'000') as represent_name, coalesce(sector,'--абоненти без дільниці') as sector,
    coalesce(sw_name||' '||sw_dt, 'Не проживає '||nolive_dt_b||'-'||nolive_dt_e,CASE WHEN c.not_live THEN 'Не проживає' END,c.note,'' ) as info ,   
    regexp_replace(regexp_replace(c.code, '-.*?$', '') , '[^0-9]', '','g')::int as int_code
from 
    clm_paccnt_tbl as c
    join clm_meterpoint_tbl as m on (m.id_paccnt = c.id) 
    join clm_abon_tbl as a on (a.id = c.id_abon) 
    join eqi_meter_tbl as im on (im.id = m.id_type_meter)
    join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class)
    left join cli_house_type_tbl as ht on (ht.id = c.idk_house)
    left join eqk_meter_places_tbl as mp on (mp.id = m.id_extra)
  left join (
                select rs.id, rs.id_runner,rs.name,rp.id_paccnt,rs.id_region, coalesce(p.represent_name,'111') as represent_name,
		rs.name::varchar as sector
		from  prs_runner_sectors as rs 
		join prs_runner_paccnt as rp on (rp.id_sector = rs.id)
		left join prs_persons as p on (p.id = rs.id_runner)
		order by rp.id_paccnt
  ) as rr on (rr.id_paccnt = c.id)
    
  left join (
    select id_meter, max(dat_ind) as dat_ind
    from acm_indication_tbl as i 
    $where_ind
    group by id_meter order by id_meter
    ) as  ind on (ind.id_meter = m.id)
    
  left join (
       select csw.id_paccnt, to_char(coalesce(csw.dt_action,csw.dt_create), 'DD.MM.YYYY') as sw_dt , csw.action, a.name as sw_name
       from clm_switching_tbl as csw 
        join cli_switch_action_tbl as a on (a.id = csw.action )
        join (select id_paccnt, max(dt_action) as maxdt from clm_switching_tbl where action in (1,2) group by id_paccnt) as csdt 
       on (csw.id_paccnt = csdt.id_paccnt and csw.dt_action = csdt.maxdt) 
       left join clm_switching_tbl as cswc on (csw.id_paccnt = cswc.id_paccnt and cswc.dt_action >= csw.dt_action and ((cswc.action =3 and csw.action =1) or (cswc.action =4  and csw.action =2) ) ) 
       where csw.action =1 and cswc.id_paccnt is null 
       order by csw.id_paccnt
  ) as sw on (sw.id_paccnt = c.id)
   left join (
      	select n.id_paccnt, 
        to_char(n.dt_b, 'DD.MM.YYYY') as nolive_dt_b, 
        to_char(n.dt_e, 'DD.MM.YYYY') as nolive_dt_e    
        from clm_notlive_tbl as n 
       where n.dt_b<= $p_dt and ((n.dt_e is null) or (n.dt_e>= $p_dt::date))
       order by n.id_paccnt
   ) as n on (n.id_paccnt = c.id)
where c.archive=0 $where 
order by represent_name,sector, book, int_code, code ;";

  
    $cur_sector='';
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        $i=1;
        $nn=0;
        $nnn=0;
        while ($row = pg_fetch_array($result)) {

            
            $np++;
            
            if ((($np> ($page_num-1)* $page_size )&& ($np<= $page_num* $page_size )) || ($show_pager ==0))
                $print_flag = 1;
            else
                $print_flag = 0;
            
            if ($row['sector']!=$cur_sector)
            {
                if ($cur_sector!='')
                {
                    echo_html( "
                    <tr class='table_footer'>
                    <td colspan='10'>Всього по {$cur_sector} - {$nn}</td>
                    </tr>  ",$print_flag);
                    $nn=0;
                }
                
                $cur_sector=$row['sector'];
                $cur_inspector=$row['represent_name'];
                
                $sector_str = htmlspecialchars($cur_sector);
                
                echo_html( "
                <tr >
                <td COLSPAN=3 class = 'tab_head'>{$sector_str}</td>
                <td COLSPAN=7 class = 'tab_head'>{$cur_inspector}</td>
                </tr>  ",$print_flag);        
                $i=1;
            }
            
            $abon = htmlspecialchars($row['abon']);
            $addr = htmlspecialchars($row['town'].' '.$row['street'].' '.$row['house']);
            $book=$row['book'].'/'.$row['code'];
            $note = htmlspecialchars($row['info']);
           
            
            echo_html( "
            <tr >
            <td>{$i}</td>
            <td class='c_t'>{$book}</td>
            <td>{$abon}</td>
            <td>{$addr}</td>
            <td class='c_t'>{$row['k_house']}</td>
            <td class='c_t'>{$row['num_meter']}</td>
            <td class='c_t'>{$row['type_meter']}</td>
            <td class='c_t'>{$row['meter_place']}</td>
            <td >{$row['dat_ind_txt']}</td>
            <td class='c_t'>{$note}</td>
            </tr>  ",$print_flag);        
            $i++;
            $nn++;
            $nnn++;
        }
        
    }
    
    echo_html( "
      <tr class='table_footer'>
      <td colspan='10'>Всього по {$cur_sector} - {$nn}</td>
      </tr>  ",$print_flag);

    echo_html( "
      <tr class='table_footer'>
      <td colspan='10'>ВСЬОГО - {$nnn}</td>
      </tr>  ",$print_flag);
      
    eval("\$footer_text_eval = \"$footer_text\";");
    echo $footer_text_eval;

    //return;
    
}
////----------------------------------------------------------------------------
if ($oper=='bill_list')
{
    $p_dtb = sql_field_val('dt_b', 'date');
    $p_dte = sql_field_val('dt_e', 'date');
    $p_mmgg = sql_field_val('dt_b', 'mmgg');
    
    $p_dtb_str = trim($_POST['dt_b']);
    $p_dte_str = trim($_POST['dt_e']);

    $p_id_tar = sql_field_val('id_gtar', 'int');        
    $period_str = " з $p_dtb_str до $p_dte_str";

    $p_id_sector = sql_field_val('id_sector', 'int');
    $p_book = sql_field_val('book', 'string');
    $p_id_region = sql_field_val('id_region', 'int');
    
    $p_value = sql_field_val('sum_value', 'numeric');  
    $p_direction = sql_field_val('sum_direction', 'int');
    
    $p_id_dod = sql_field_val('id_cntrl', 'int');
    $p_name_dod = sql_field_val('cntrl', 'string');
    
    
    //$where="where b.mmgg = $p_mmgg::date and b.reg_date >= $p_dtb::date and b.reg_date <= $p_dte::date ";
    $where="where b.mmgg = $p_mmgg::date  ";
    
    if ($p_id_tar!='null')
    {
        if ($where!='where') $where.= " and ";
        $where.= " id_gtar = $p_id_tar ";
    }
    
    $params_caption = '';
    if ((isset($_POST['gtar']))&&($_POST['gtar']!=''))
        $params_caption .= ' Тарифна група : '. trim($_POST['gtar']);
    
    
    if ((isset($_POST['sector']))&&($_POST['sector']!=''))
        $params_caption.= ' Дiльниця: '. trim($_POST['sector']);

    if ($p_book!='null')
    {
        if ($where!='where') $where.= " and ";        
        $where.= " book = $p_book ";
        $params_caption .= " книга : $p_book ";
        
    }

   if ($p_id_sector!='null')
   {
      if ($where!='where') $where.= " and ";       
      $where.= " exists (select id from prs_runner_paccnt as rp 
          where rp.id_paccnt = c.id and rp.id_sector = $p_id_sector ) ";
   }
    
    if ($p_id_region!='null')
    {
        if ($where!='where') $where.= " and ";               
        $where.= " exists (select rs.id from 
            prs_runner_paccnt as rp 
            join prs_runner_sectors as rs on (rs.id = rp.id_sector)
            where rp.id_paccnt = c.id and rs.id_region =  $p_id_region ) ";

        $SQL_H="SELECT name from cli_region_tbl where id = $p_id_region ;";
    
        $result = pg_query($Link, $SQL_H) or die("SQL Error: " . pg_last_error($Link) . $SQL_H);
        $row = pg_fetch_array($result);
        
        $params_caption.= $row['name'];        
       
    }   
    
    $order = " b.reg_date,int_book, c.book,int_code,c.code ";
    
    if ($p_value!='null')
    {
        if ($p_direction==1) $where.= "and b.demand >= $p_value ";
        if ($p_direction==2) $where.= "and b.demand <= $p_value ";
        if ($p_direction==3) $where.= "and b.demand = $p_value ";
        if ($p_direction==4) $where.= "and b.demand <= $p_value and b.demand >0 ";
        
        $order = "demand, int_book, book,int_code,code ";
        
        if ($p_direction==1) $params_caption .= " де споживання більше за  $p_value кВтг";
        if ($p_direction==2) $params_caption .= " де споживання менше за  $p_value кВтг";
        if ($p_direction==3) $params_caption .= " де споживання дорівнює $p_value кВтг";        
        if ($p_direction==4) $params_caption .= " де споживання більше 0 та менше за $p_value кВтг";        
    }    
   
    if ($p_id_dod!='null')
    {
        $where.= "and c.id_cntrl = $p_id_dod ";
        $params_caption .= " Додаткова ознака : $p_name_dod ";
    }
    
    
    if ((isset($_POST['note']))&&($_POST['note']!=''))
    {
        if ($where!='where') $where.= " and ";                
        $where.= $_POST['note'];
        $params_caption .= $_POST['note'];
    }

       
    $summ_calc_all =0;
    $summ_lgt_all  =0;
    $summ_subs_all =0;
    $summ_all      =0;
    $summ_tax_all  =0;
    $demand_all    =0;
    $i=1;
    
    $np=0;
    $print_flag = 1;
    
    eval("\$header_text = \"$header_text\";");
    echo_html( $header_text);
    
    $SQL = " select  c.id,c.code,c.book, 
    adr.town, adr.street, address_print(c.addr) as house,
   (a.last_name||' '||coalesce(substr(a.name,1,1),'')||'.'||coalesce(substr(a.patron_name,1,1),''))||'.'::varchar as abon,
    b.reg_num,
    to_char(b.reg_date, 'DD.MM.YYYY') as reg_date,
    b.value_calc,b.value_lgt, b.value_subs, 
    b.value, b.value_tax,b.demand, di.name as kind_doc,
    ('0'||substring(c.code FROM '[0-9]+'))::int as int_code,
    ('0'||substring(c.book FROM '[0-9]+'))::int as int_book   
    from acm_bill_tbl as b 
    join clm_paccnt_h as c on (c.id =b.id_paccnt )
    join (select id, max(dt_b) as dt_b from clm_paccnt_h  where 
    ((dt_b < $p_dte and dt_e is null)
    or 
    tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_dtb::timestamp::abstime,$p_dte::timestamp::abstime))
    ) 
    group by id order by id) as c2 
    on (c.id = c2.id and c.dt_b = c2.dt_b)    
    join clm_abon_tbl as a on (a.id = c.id_abon) 
    join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class)
    left join dci_doc_tbl as di on (di.id = b.idk_doc)    
    $where and b.id_pref = 10
    -- and b.mmgg_bill <= '2016-01-01' and b.value<>0 
    order by $order ;";

   // throw new Exception(json_encode($SQL));
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        //$i=0;
        while ($row = pg_fetch_array($result)) {

            $np++;
            
            if ((($np> ($page_num-1)* $page_size )&& ($np<= $page_num* $page_size )) || ($show_pager ==0))
                $print_flag = 1;
            else
                $print_flag = 0;
            
            $abon = htmlspecialchars($row['abon']);
            $addr = htmlspecialchars($row['town'].' '.$row['street'].' '.$row['house']);
            $book=$row['book'].'/'.$row['code'];

            $summ_calc_txt = number_format_ua ($row['value_calc'],2);
            $summ_lgt_txt = number_format_ua ($row['value_lgt'],2);
            $summ_subs_txt = number_format_ua ($row['value_subs'],2);
            $summ_txt = number_format_ua ($row['value'],2);
            $summ_tax_txt = number_format_ua ($row['value_tax'],2);
            
            if ($p_sum_only!=1)    {
            echo_html( "
            <tr >
            <td>{$i}</td>            
            <td class='c_t'>$book</td>
            <td>$abon</td>
            <td>$addr</td>

            <td class='c_t'>{$row['reg_num']}</td>
            <td >{$row['reg_date']}</td>
            <td >{$row['kind_doc']}</td>
            <td class='c_i'>{$row['demand']}</td>
            <td class='c_n'>{$summ_calc_txt}</td>
            <td class='c_n'>{$summ_lgt_txt}</td>
            <td class='c_n'>{$summ_subs_txt}</td>
            <td class='c_n'>{$summ_txt}</td>
            <td class='c_n'>{$summ_tax_txt}</td>
            </tr>  ",$print_flag); 
            }
            $i++;
            
            $summ_calc_all += $row['value_calc'];
            $summ_lgt_all += $row['value_lgt'];
            $summ_subs_all += $row['value_subs'];
            $summ_all += $row['value'];
            $summ_tax_all += $row['value_tax'];
            $demand_all += $row['demand'];
            
        }
        
    }
    
    $summ_calc_txt = number_format_ua ($summ_calc_all,2);
    $summ_lgt_txt = number_format_ua ($summ_lgt_all,2);
    $summ_subs_txt = number_format_ua ($summ_subs_all,2);
    $summ_txt = number_format_ua ($summ_all,2);
    $summ_tax_txt = number_format_ua ($summ_tax_all,2);
    

    eval("\$footer_text_eval = \"$footer_text\";");

    echo_html( $footer_text_eval,$print_flag);
   
    if ($print_flag==0) echo "</tbody> </table> ";
   
}
////----------------------------------------------------------------------------
if (($oper=='pay_list')||($oper=='corpay_list')||($oper=='pay_list_archive'))
{
    $p_dtb = sql_field_val('dt_b', 'date');
    $p_dte = sql_field_val('dt_e', 'date');
    $p_mmgg = sql_field_val('dt_b', 'mmgg');
    
    $p_id_sector = sql_field_val('id_sector', 'int');
    $p_id_region = sql_field_val('id_region', 'int');

    $p_book = sql_field_val('book', 'string');
    $p_code = sql_field_val('code', 'string');
    
    $p_dtb_str = trim($_POST['dt_b']);
    $p_dte_str = trim($_POST['dt_e']);

    $period_str = " з $p_dtb_str до $p_dte_str";
    
    $params_caption = '';
    $where="";
    
    $summ_all      =0;
    $summ_tax_all  =0;

    $i=1;
    
    if ((isset($_POST['sector']))&&($_POST['sector']!=''))
        $params_caption.= ' Дiльниця: '. trim($_POST['sector']);

    if ($p_book!='null')
    {
        $where.= " and book = $p_book ";
        $params_caption .= " книга : $p_book ";
        
    }

    if ($p_code!='null')
    {
        $where.= " and code = $p_code ";
        $params_caption .= " рах. : $p_code ";
        
    }

    
   if ($p_id_sector!='null')
   {
      $where.= "and exists (select id from prs_runner_paccnt as rp 
          where rp.id_paccnt = c.id and rp.id_sector = $p_id_sector ) ";
   }
    
   if ($p_id_region!='null')
   {
        
        $where.= "and exists (select rs.id from 
            prs_runner_paccnt as rp 
            join prs_runner_sectors as rs on (rs.id = rp.id_sector)
            where rp.id_paccnt = c.id and rs.id_region =  $p_id_region ) ";

        $SQL_H="SELECT name from cli_region_tbl where id = $p_id_region ;";
    
        $result = pg_query($Link, $SQL_H) or die("SQL Error: " . pg_last_error($Link) . $SQL_H);
        $row = pg_fetch_array($result);
        
        $params_caption.= $row['name'];        
       
   }   
   
   if ($oper=='corpay_list')
   {
      $where.= " and p.idk_doc not in ( 110, 111, 193,194, 100) and p.mmgg =  $p_mmgg ";       
      $params_caption .= " Коригування оплати "; 
   }
   else
   {       
        $SQL="select ((date_trunc('month',$p_dtb::date)=$p_dtb) 
            and (date_trunc('month',$p_dtb::date)=date_trunc('month',$p_dte::date)) 
            and ((date_trunc('month',$p_dte::date)+'1 month - 1 day'::interval)::date=$p_dte))::int as check;";
    
        $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
        $row = pg_fetch_array($result);
        if ($row['check']==0)
        {
         $where.= " and p.reg_date >= $p_dtb::date and p.reg_date <= $p_dte::date ";
        }
        else
        {
            $where.= " and p.mmgg =  $p_mmgg ";
        }
        
   }
   
   if ($oper=='pay_list_archive')
   {
       $params_caption .= " (абоненти в архіві) "; 
       $where.= " and c.archive = 1 ";
   }
       
    if ((isset($_POST['note']))&&($_POST['note']!=''))
    {
        $where.= $_POST['note'];
        $params_caption .= $_POST['note'];
    }
   
   
    
    $np=0;
    $print_flag = 1;
     
    eval("\$header_text = \"$header_text\";");
    echo_html( $header_text);
    
    $SQL = " select  c.id,c.code,c.book, 
    adr.town, adr.street, address_print(c.addr) as house,
   (a.last_name||' '||coalesce(substr(a.name,1,1),'')||'.'||coalesce(substr(a.patron_name,1,1),''))||'.'::varchar as abon,
    hp.reg_num as hreg_num,to_char(hp.reg_date, 'DD.MM.YYYY') as hreg_date, 
    p.reg_num ,to_char(p.reg_date, 'DD.MM.YYYY') as reg_date, to_char(p.pay_date, 'DD.MM.YYYY') as pay_date, 
    po.name as pay_origin,u1.name as user_name,
    to_char(p.mmgg, 'DD.MM.YYYY') as mmgg,
    to_char(p.dt, 'DD.MM.YYYY HH24:MI') as dt_txt,
    p.value, p.value_tax,p.note, di.name as kind_doc,
    ('0'||substring(c.code FROM '[0-9]+'))::int as int_code,
    ('0'||substring(c.book FROM '[0-9]+'))::int as int_book   

    from acm_pay_tbl as p 
    join acm_headpay_tbl as hp on (hp.id = p.id_headpay)
    join clm_paccnt_h as c on (c.id =p.id_paccnt )
    join (select id, max(dt_b) as dt_b from clm_paccnt_h  where  
    ((dt_b < $p_dte and dt_e is null)
    or 
    tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_dtb::timestamp::abstime,$p_dte::timestamp::abstime))
    ) 
    group by id order by id) as c2 
    on (c.id = c2.id and c.dt_b = c2.dt_b)    
    join clm_abon_h as a on (a.id = c.id_abon) 
    join (select id, max(dt_b) as dt_b from clm_abon_h  where 
    ((dt_b < $p_dte and dt_e is null)
    or 
    tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_dtb::timestamp::abstime,$p_dte::timestamp::abstime))
    )
    group by id order by id) as a2 on (a.id = a2.id and a.dt_b = a2.dt_b)
    join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class)
    left join aci_pay_origin_tbl as po on (po.id = hp.id_origin )
    left join dci_doc_tbl as di on (di.id = p.idk_doc)
    left join syi_user as u1 on (u1.id = p.id_person)
    where p.id_pref = 10 and p.idk_doc not in ( 110,111, 193,194) 
    $where
    order by p.reg_date,int_book,c.book,int_code,c.code;";

    //echo($SQL);
   // throw new Exception(json_encode($SQL));
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        $i=1;
        while ($row = pg_fetch_array($result)) {

            $np++;
            
            if ((($np> ($page_num-1)* $page_size )&& ($np<= $page_num* $page_size )) || ($show_pager ==0))
                $print_flag = 1;
            else
                $print_flag = 0;
            
            $abon = htmlspecialchars($row['abon']);
            $addr = htmlspecialchars($row['town'].' '.$row['street'].' '.$row['house']);
            $book=$row['book'].'/'.$row['code'];
            $user = htmlspecialchars($row['user_name']);

            $summ_txt = number_format_ua ($row['value'],2);
            $summ_tax_txt = number_format_ua ($row['value_tax'],2);
            
            if ($p_sum_only!=1)    {
            echo_html( "
            <tr >
            <td>{$i}</td>            
            <td class='c_t'>$book</td>
            <td>$abon</td>
            <td>$addr</td>

            <td class='c_t'>{$row['hreg_num']}</td>
            <td >{$row['hreg_date']}</td>
            
            <td class='c_t'>{$row['reg_num']}</td>
            <td >{$row['reg_date']}</td>
            <td >{$row['pay_date']}</td>
            <td >{$row['kind_doc']}</td>
            <td class='c_n'>{$summ_txt}</td>
            <td class='c_n'>{$summ_tax_txt}</td>
            
            <td class='c_t'>{$row['pay_origin']}</td>
            <td >{$row['mmgg']}</td>
            <td class='c_t'>{$row['note']}</td>
            <td class='c_t'>{$user}</td>
            <td class='c_t'>{$row['dt_txt']}</td>
            </tr>  ",$print_flag);
            }
            $i++;
            
            $summ_all += round($row['value'],2);
            $summ_tax_all += round($row['value_tax'],2);
            
        }
        
    }
    
    $summ_txt = number_format_ua (round($summ_all,2),2);
    $summ_tax_txt = number_format_ua (round($summ_tax_all,2),2);
    

    eval("\$footer_text_eval = \"$footer_text\";");
    echo_html( $footer_text_eval,$print_flag);
   
    if ($print_flag==0) echo "</tbody> </table> ";

   
}
//----------------------------------------------------------------------------
if (($oper=='abon_plan_demand')||($oper=='abon_plan_demand_empty'))
 {
    $p_mmgg = sql_field_val('dt_b', 'mmgg');

    list($year, $month, $day  ) = split('[/.-]', $p_mmgg);
    $p_id_sector = sql_field_val('id_sector', 'int');
    $p_book = sql_field_val('book', 'string');

    $period = "за $year р.";
    
    $p_id_town = sql_field_val('addr_town', 'int');
    $p_id_region = sql_field_val('id_region', 'int');
    
    if (isset($_POST['town_detal']))
        $p_town_detal = trim($_POST['town_detal']);
    else 
        $p_town_detal =0;

    if ((isset($_POST['addr_town_name']))&&($_POST['addr_town_name']!=''))
        $params_caption = 'Населений пункт: '. trim($_POST['addr_town_name']);
    else 
        $params_caption ='';
   
    if ($params_caption !='') $params_caption .='<br/>';
        
    $where = ' ';
    $where2 = ' ';
    
    if ($p_id_town!='null')
    {
        $where.= "and adr.id_town = $p_id_town ";
    }

    if ($p_book!='null')
    {
        $where.= " and book = $p_book ";
        $params_caption .= " книга : $p_book ";
    }
    

    if ((isset($_POST['sector']))&&($_POST['sector']!=''))
        $params_caption.= ' Дiльниця: '. trim($_POST['sector']);


   if ($p_id_sector!='null')
   {
      $where.= "and exists (select id from prs_runner_paccnt as rp 
          where rp.id_paccnt = c.id and rp.id_sector = $p_id_sector ) ";
      
      $where2.= "and exists (select id from prs_runner_paccnt as rp 
          where rp.id_paccnt = pd.id_paccnt and rp.id_sector = $p_id_sector ) ";
   }
    
    if ($p_id_region!='null')
    {
        
        $where.= "and exists (select rs.id from 
            prs_runner_paccnt as rp 
            join prs_runner_sectors as rs on (rs.id = rp.id_sector)
            where rp.id_paccnt = c.id and rs.id_region =  $p_id_region ) ";
        
        $where2.= "and exists (select rs.id from 
            prs_runner_paccnt as rp 
            join prs_runner_sectors as rs on (rs.id = rp.id_sector)
            where rp.id_paccnt = pd.id_paccnt and rs.id_region =  $p_id_region ) ";
        

        $SQL_H="SELECT name from cli_region_tbl where id = $p_id_region ;";
    
        $result = pg_query($Link, $SQL_H) or die("SQL Error: " . pg_last_error($Link) . $SQL_H);
        $row = pg_fetch_array($result);
        
        $params_caption.= $row['name'];               
    }   
   
    
    if ($oper=='abon_plan_demand_empty')
    {
        $params_caption.= 'не вказано планове споживання на поточний місяць <br/>';
        $where.= " and CASE WHEN date_part('month',$p_mmgg::date) =1 THEN dem1
                            WHEN date_part('month',$p_mmgg::date) =2 THEN dem2
                            WHEN date_part('month',$p_mmgg::date) =3 THEN dem3
                            WHEN date_part('month',$p_mmgg::date) =4 THEN dem4
                            WHEN date_part('month',$p_mmgg::date) =5 THEN dem5
                            WHEN date_part('month',$p_mmgg::date) =6 THEN dem6
                            WHEN date_part('month',$p_mmgg::date) =7 THEN dem7
                            WHEN date_part('month',$p_mmgg::date) =8 THEN dem8
                            WHEN date_part('month',$p_mmgg::date) =9 THEN dem9
                            WHEN date_part('month',$p_mmgg::date) =10 THEN dem10
                            WHEN date_part('month',$p_mmgg::date) =11 THEN dem11
                            WHEN date_part('month',$p_mmgg::date) =12 THEN dem12 END is null ";
    }
    
    if ($p_town_detal ==1)
    {
        $order ='town, book, int_code,code,id_zone';
    }
    else
    {
        $order ='book, int_code,code,id_zone';
    }
    
    
    $np=0;
    $print_flag = 1;
    
    eval("\$header_text = \"$header_text\";");
    echo_html( $header_text);
   
    
    $SQL = "select c.id,c.code,c.book, zzz.zone,zzz.id_zone, 
    adr.town, adr.street, address_print(c.addr) as house,
   (a.last_name||' '||coalesce(substr(a.name,1,1),'')||'.'||coalesce(substr(a.patron_name,1,1),''))||'.'::varchar as abon,
    regexp_replace(regexp_replace(c.code, '-.*?$', '') , '[^0-9]', '','g')::int as int_code,
    pp.dem1,pp.dem2,pp.dem3,pp.dem4,pp.dem5,pp.dem6,pp.dem7,pp.dem8,pp.dem9,pp.dem10,pp.dem11,pp.dem12
from 
    clm_paccnt_h as c
    join (select id, max(dt_b) as dt_b from clm_paccnt_h  where 
    ((dt_b < ($p_mmgg::date+'1 month'::interval) and dt_e is null)
            or 
            tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
        )
    group by id order by id) as c2 
    on (c.id = c2.id and c.dt_b = c2.dt_b)

    join clm_meterpoint_h as m on (m.id_paccnt = c.id) 
    join (select id, max(dt_b) as dt_b from clm_meterpoint_h  where 
    ((dt_b < ($p_mmgg::date+'1 month'::interval) and dt_e is null)
            or 
            tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
        )
    group by id order by id) as m2 
    on (m.id = m2.id and m.dt_b = m2.dt_b)

    join clm_abon_h as a on (a.id = c.id_abon) 
    join (select id, max(dt_b) as dt_b from clm_abon_h  where 
    ((dt_b < ($p_mmgg::date+'1 month'::interval) and dt_e is null)
            or 
            tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
        )
    group by id order by id) as a2 
    on (a.id = a2.id and a.dt_b = a2.dt_b)
    
    join ( select z.id_meter, z.id_zone, iz.nm as zone
     from clm_meter_zone_h as z 
     join eqk_zone_tbl as iz on (iz.id = z.id_zone)
     where z.dt_b <= $p_mmgg and coalesce(z.dt_e,$p_mmgg) >=$p_mmgg
     order by z.id_meter
    ) as zzz on (zzz.id_meter = m.id)
    join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class)
    left join 
    (
     select id_paccnt, id_zone, 
     sum(CASE WHEN date_part('month',mmgg)=1 THEN demand  END) as dem1, 
     sum(CASE WHEN date_part('month',mmgg)=2 THEN demand  END) as dem2, 
     sum(CASE WHEN date_part('month',mmgg)=3 THEN demand  END) as dem3, 
     sum(CASE WHEN date_part('month',mmgg)=4 THEN demand  END) as dem4, 
     sum(CASE WHEN date_part('month',mmgg)=5 THEN demand  END) as dem5, 
     sum(CASE WHEN date_part('month',mmgg)=6 THEN demand  END) as dem6, 
     sum(CASE WHEN date_part('month',mmgg)=7 THEN demand  END) as dem7, 
     sum(CASE WHEN date_part('month',mmgg)=8 THEN demand  END) as dem8, 
     sum(CASE WHEN date_part('month',mmgg)=9 THEN demand  END) as dem9, 
     sum(CASE WHEN date_part('month',mmgg)=10 THEN demand END) as dem10, 
     sum(CASE WHEN date_part('month',mmgg)=11 THEN demand END) as dem11, 
     sum(CASE WHEN date_part('month',mmgg)=12 THEN demand END) as dem12 
    from
     clm_plandemand_tbl as pd 
    where date_part('year',mmgg) = date_part('year',$p_mmgg::date)
    $where2 
    group by id_paccnt, id_zone
    order by id_paccnt, id_zone
    ) as pp on (pp.id_paccnt = c.id and pp.id_zone = zzz.id_zone)
where c.archive=0 $where 
order by $order ;";

  
    $cur_town='';
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        $i=1;
        while ($row = pg_fetch_array($result)) {

            $np++;
            
            if ((($np> ($page_num-1)* $page_size )&& ($np<= $page_num* $page_size )) || ($show_pager ==0))
                $print_flag = 1;
            else
                $print_flag = 0;
            
            
            if (($row['town']!=$cur_town)&&($p_town_detal==1))
            {
                $cur_town=$row['town'];
                
                $town_str = htmlspecialchars($cur_town);
                echo_html( "
                <tr >
                <td COLSPAN=17 class = 'tab_head'>{$town_str}</td>
                </tr>  ",$print_flag);        
                $i=1;
            }

            $abon = htmlspecialchars($row['abon']);
            $zone = htmlspecialchars($row['zone']);
            $addr = htmlspecialchars($row['town'].' '.$row['street'].' '.$row['house']);
            $book=$row['book'].'/'.$row['code'];
          
            $dem1 = number_format_ua ($row['dem1'],0);
            $dem2 = number_format_ua ($row['dem2'],0);
            $dem3 = number_format_ua ($row['dem3'],0);
            $dem4 = number_format_ua ($row['dem4'],0);
            $dem5 = number_format_ua ($row['dem5'],0);
            $dem6 = number_format_ua ($row['dem6'],0);
            $dem7 = number_format_ua ($row['dem7'],0);
            $dem8 = number_format_ua ($row['dem8'],0);
            $dem9 = number_format_ua ($row['dem9'],0);
            $dem10 = number_format_ua ($row['dem10'],0);
            $dem11 = number_format_ua ($row['dem11'],0);
            $dem12 = number_format_ua ($row['dem12'],0);
            
            echo_html( "
            <tr >
            <td>{$i}</td>
            <td class='c_t'>{$book}</td>
            <td>{$abon}</td>
            <td>{$addr}</td>
            <td>{$zone}</td>
            <td class='c_i'>{$dem1}</td>
            <td class='c_i'>{$dem2}</td>
            <td class='c_i'>{$dem3}</td>
            <td class='c_i'>{$dem4}</td>
            <td class='c_i'>{$dem5}</td>
            <td class='c_i'>{$dem6}</td>
            <td class='c_i'>{$dem7}</td>
            <td class='c_i'>{$dem8}</td>
            <td class='c_i'>{$dem9}</td>
            <td class='c_i'>{$dem10}</td>
            <td class='c_i'>{$dem11}</td>
            <td class='c_i'>{$dem12}</td>
            </tr>  ",$print_flag);        
            $i++;
        }
        
    }
    
    eval("\$footer_text_eval = \"$footer_text\";");
    
    echo_html( $footer_text_eval,$print_flag);
   
    if ($print_flag==0) echo "</tbody> </table> ";


    //return;
    
}
//----------------------------------------------------------------------------
if ($oper=='saldo_years')
{
    $p_mmgg = sql_field_val('dt_b', 'mmgg');

    list($year, $month, $day  ) = split('[/.-]', $p_mmgg);
    
    $year = str_replace("'","",$year);
    $year_p = $year -1;
    $month_str = ukr_month((int)$month,0);
    $period = "за $month_str $year р.";
    
    $p_id_region = sql_field_val('id_region', 'int');
    $p_id_town = sql_field_val('addr_town', 'int');
    
    $params_caption = '';
    $where='';
    
    $sum_dt_b =0;
    $sum_kt_b =0;
    $sum_bill =0;
    $sum_pay  =0;
    
    $sum_bill_kt =0;
    $sum_pay_kt  =0;
    $sum_pay_subs  =0;
    
    $sum_dt_e =0;
    $sum_kt_e =0;
    $i=1;
    $name = '';
    
    $SQL = "select crt_ttbl();";
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    
   
    $table_name = 'seb_saldo_tmp';
//    $SQL = "select all_repayment();";
//    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);

    $SQL = "select count(*) as cnt from seb_saldo where mmgg = $p_mmgg::date;";
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        $row = pg_fetch_array($result);
        if ($row['cnt']>0) 
        {
            $table_name = 'seb_saldo';
        }
        else
        {
          //$SQL = "select seb_saldo($p_mmgg,0,null);";
            $SQL = "select rep_month_saldo_fun($p_mmgg,0);";
            
          $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
        }
    }
    
    $SQL = "select rep_month_saldo_year_fun($p_mmgg::date,(date_trunc('year',$p_mmgg::date)-'1 year'::interval)::date,1);";
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);

    $SQL = "select rep_month_saldo_year_fun($p_mmgg::date,date_trunc('year',$p_mmgg::date)::date,0);";
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    
    
   if ($p_id_region!='null')
   {
        
        $where.= " and exists (select rs.id from 
            prs_runner_paccnt as rp 
            join prs_runner_sectors as rs on (rs.id = rp.id_sector)
            where rp.id_paccnt = t.id_paccnt and rs.id_region =  $p_id_region ) ";

        $SQL_H="SELECT name from cli_region_tbl where id = $p_id_region ;";
    
        $result = pg_query($Link, $SQL_H) or die("SQL Error: " . pg_last_error($Link) . $SQL_H);
        $row = pg_fetch_array($result);
        
        $params_caption.= $row['name'];        
       
   }      
    
    if ((isset($_POST['addr_town_name']))&&($_POST['addr_town_name']!=''))
        $params_caption.= 'Населений пункт: '. trim($_POST['addr_town_name']);
   
    if ($p_id_town!='null')
    {
        if ($p_id_town==40854)
          $where.= " and ( adr.id_parent = $p_id_town or adr.id_parent = 40016 )";
        else
          $where.= " and ( adr.id_town = $p_id_town or adr.id_parent = $p_id_town )";
    }
   
   
    eval("\$header_text = \"$header_text\";");
    echo $header_text;
   

    $SQL = "   
    select hmmgg, to_char(hmmgg, 'YYYY') as mmgg_h,
    sum(b_dtval) as dt_b,  -- sum(b_dtval_tax) as dt_b_tax,
    sum(b_ktval) as kt_b,  -- sum(b_ktval_tax) as kt_b_tax,

    sum(dtval) + sum(dtval_kt) - sum(ktval_subs) as bill, --sum(dtval_tax)+sum(dtval_kt_tax) - sum(ktval_subs_tax) as bill_tax,  
    sum(ktval) + sum(ktval_kt) - sum(ktval_subs) as pay, --sum(ktval_tax)+sum(ktval_kt_tax) - sum(ktval_subs_tax) as pay_tax,  
     
    sum(dtval_kt) as bill_kt, -- sum(dtval_kt_tax) as bill_kt_tax,      
    sum(ktval_kt) as pay_kt, -- sum(ktval_kt_tax) as pay_kt_tax,      
    sum(ktval_subs) as pay_subs, -- sum(ktval_subs_tax) as pay_subs_tax,      
    
    sum(e_dtval) as dt_e, -- sum(e_dtval_tax) as dt_e_tax,
    sum(e_ktval) as kt_e -- ,  sum(e_ktval_tax) as kt_e_tax
    
    from $table_name as t
     join clm_paccnt_tbl as c on (t.id_paccnt = c.id)
     join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class)    
    where t.id_pref = 10 and t.mmgg = $p_mmgg::date
    $where
    group by t.hmmgg order by t.hmmgg  ;";
    
   
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        $i=0;
        while ($row = pg_fetch_array($result)) {

            $mmgg_h = $row['mmgg_h'];
            $hmmgg  = $row['hmmgg'];
            
            $name = '';
            
            $dt_b = $row['dt_b'];
            $kt_b = $row['kt_b'];
            $bill = $row['bill'];
            $pay  = $row['pay'];
            
            $bill_kt = $row['bill_kt'];
            $pay_kt  = $row['pay_kt'];
            $pay_subs  = $row['pay_subs'];
            
            $dt_e = $row['dt_e'];
            $kt_e = $row['kt_e'];
            
            $sum_dt_b +=$dt_b;
            $sum_kt_b +=$kt_b;
            $sum_bill +=$bill;
            $sum_pay  +=$pay;

            $sum_bill_kt +=$bill_kt;
            $sum_pay_kt  +=$pay_kt;
            $sum_pay_subs  +=$pay_subs;
            
            $sum_dt_e +=$dt_e;
            $sum_kt_e +=$kt_e;
            

            $SQL2 = "   
            select name,auto, 
            b_dtval as dt_b,  -- sum(b_dtval_tax) as dt_b_tax,
            b_ktval as kt_b,  -- sum(b_ktval_tax) as kt_b_tax,
            dtval + dtval_kt - ktval_subs as bill, --sum(dtval_tax)+sum(dtval_kt_tax) - sum(ktval_subs_tax) as bill_tax,  
            ktval + ktval_kt - ktval_subs as pay, --sum(ktval_tax)+sum(ktval_kt_tax) - sum(ktval_subs_tax) as pay_tax,  
            dtval_kt as bill_kt, -- sum(dtval_kt_tax) as bill_kt_tax,      
            ktval_kt as pay_kt, -- sum(ktval_kt_tax) as pay_kt_tax,      
            ktval_subs as pay_subs, -- sum(ktval_subs_tax) as pay_subs_tax,      
            e_dtval as dt_e, -- sum(e_dtval_tax) as dt_e_tax,
            e_ktval as kt_e -- ,  sum(e_ktval_tax) as kt_e_tax
    
            from seb_saldo_special
            where id_pref = 10 and mmgg = $p_mmgg::date and hmmgg = '$hmmgg'
            order by date_start, auto  ;";
            
            $result2 = pg_query($Link, $SQL2) or die("SQL Error: " . pg_last_error($Link) . $SQL);
            if ($result2) {
        
                $rows_count2 = pg_num_rows($result2);
                while ($row2 = pg_fetch_array($result2)) {

                    $name = $row2['name'];
                    
                    if ($row2['auto']==0 )
                    {
                        $dt_b2 = $row2['dt_b'];
                        $kt_b2 = $row2['kt_b'];
                        $bill2 = $row2['bill'];
                        $pay2  = $row2['pay'];
            
                        $bill_kt2 = $row2['bill_kt'];
                        $pay_kt2  = $row2['pay_kt'];
                        $pay_subs2  = $row2['pay_subs'];
            
                        $dt_e2 = $row2['dt_e'];
                        $kt_e2 = $row2['kt_e'];
                        
                        $dt_b_txt = number_format_ua ($dt_b2,2);
                        $kt_b_txt = number_format_ua ($kt_b2,2);
                        $bill_txt = number_format_ua ($bill2,2);
                        $pay_txt  = number_format_ua ($pay2,2);
            
                        $bill_kt_txt = number_format_ua ($bill_kt2,2);
                        $pay_kt_txt  = number_format_ua ($pay_kt2,2);
                        $pay_subs_txt  = number_format_ua ($pay_subs2,2);
            
                        $dt_e_txt = number_format_ua ($dt_e2,2);
                        $kt_e_txt = number_format_ua ($kt_e2,2);
                     
                        echo "
                        <tr >
                        <td>$mmgg_h</td>
                        <td>$name</td>
                        <TD class='c_n'>{$dt_b_txt}</td>
                        <TD class='c_n'>{$kt_b_txt}</td>
                        <TD class='c_n'>{$bill_txt}</td>
                        <TD class='c_n'>{$bill_kt_txt}</td>
                        <TD class='c_n'>{$pay_txt}</td>
                        <TD class='c_n'>{$pay_kt_txt}</td>
                        <TD class='c_n'>{$pay_subs_txt}</td>
                        <TD class='c_n'>{$dt_e_txt}</td>
                        <TD class='c_n'>{$kt_e_txt}</td>
                        </tr>  ";        
                        $i++;
                        
                        
                        $dt_b -=$dt_b2;
                        $kt_b -=$kt_b2;
                        $bill -=$bill2;
                        $pay  -=$pay2;

                        $bill_kt -=$bill_kt2;
                        $pay_kt  -=$pay_kt2;
                        $pay_subs  -=$pay_subs2;
            
                        $dt_e -=$dt_e2;
                        $kt_e -=$kt_e2;
                        
                    }
                    
                }
            }
                
            
            $dt_b_txt = number_format_ua ($dt_b,2);
            $kt_b_txt = number_format_ua ($kt_b,2);
            $bill_txt = number_format_ua ($bill,2);
            $pay_txt  = number_format_ua ($pay,2);
            
            $bill_kt_txt = number_format_ua ($bill_kt,2);
            $pay_kt_txt  = number_format_ua ($pay_kt,2);
            $pay_subs_txt  = number_format_ua ($pay_subs,2);
            
            $dt_e_txt = number_format_ua ($dt_e,2);
            $kt_e_txt = number_format_ua ($kt_e,2);
            
            echo "
            <tr >
            <td>$mmgg_h</td>
            <td>$name</td>
            <TD class='c_n'>{$dt_b_txt}</td>
            <TD class='c_n'>{$kt_b_txt}</td>
            <TD class='c_n'>{$bill_txt}</td>
            <TD class='c_n'>{$bill_kt_txt}</td>
            <TD class='c_n'>{$pay_txt}</td>
            <TD class='c_n'>{$pay_kt_txt}</td>
            <TD class='c_n'>{$pay_subs_txt}</td>
            <TD class='c_n'>{$dt_e_txt}</td>
            <TD class='c_n'>{$kt_e_txt}</td>
            </tr>  ";        
            $i++;
            
            
        }
        
    }
    
    $dt_b_txt = number_format_ua ($sum_dt_b,2);
    $kt_b_txt = number_format_ua ($sum_kt_b,2);
    $bill_txt = number_format_ua ($sum_bill,2);
    $pay_txt  = number_format_ua ($sum_pay, 2);
    
    $bill_kt_txt = number_format_ua ($sum_bill_kt,2);
    $pay_kt_txt  = number_format_ua ($sum_pay_kt,2);
    $pay_subs_txt  = number_format_ua ($sum_pay_subs,2);
    
    $dt_e_txt = number_format_ua ($sum_dt_e,2);
    $kt_e_txt = number_format_ua ($sum_kt_e,2);


    $deb01m = 0;
    $deb02m = 0;
    $deb03m = 0;
    $deb04m = 0;
    $deb05m = 0;
    $deb06m = 0;
    $deb07m = 0;
    $deb08m = 0;
    $deb09m = 0;
    $deb10m = 0;
    $deb11m = 0;
    $deb12m = 0;
    
    
    $SQL3 = " select sum(dt_all) as dt_all, sum(deb01m) as deb01m, sum(deb02m) as deb02m, sum(deb03m) as deb03m, sum(deb04m) as deb04m, sum(deb05m) as deb05m, 
            sum(deb06m) as deb06m, sum(deb07m) as deb07m, sum(deb08m) as deb08m, sum(deb09m) as deb09m, sum(deb10m) as deb10m, sum(deb11m) as deb11m, sum(deb12m) as deb12m
           from  rep_month_dt_tbl as t 
           join clm_paccnt_tbl as c on (t.id_paccnt = c.id)
           join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class)    
           where t.mmgg = $p_mmgg::date $where ; ";
            
    $result3 = pg_query($Link, $SQL3) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result3) {    
        
        $row3 = pg_fetch_array($result3);
        
        $deb01m = $row3['deb01m'];
        $deb02m = $row3['deb02m'];
        $deb03m = $row3['deb03m'];
        $deb04m = $row3['deb04m'];
        $deb05m = $row3['deb05m'];
        $deb06m = $row3['deb06m'];
        $deb07m = $row3['deb07m'];
        $deb08m = $row3['deb08m'];
        $deb09m = $row3['deb09m'];
        $deb10m = $row3['deb10m'];
        $deb11m = $row3['deb11m'];
        $deb12m = $row3['deb12m'];
    }
    
    $deb01m_txt = number_format_ua ($deb01m,2);
    $deb02m_txt = number_format_ua ($deb02m,2);
    $deb03m_txt = number_format_ua ($deb03m,2);
    $deb04m_txt = number_format_ua ($deb04m,2);
    $deb05m_txt = number_format_ua ($deb05m,2);
    $deb06m_txt = number_format_ua ($deb06m,2);
    $deb07m_txt = number_format_ua ($deb07m,2);
    $deb08m_txt = number_format_ua ($deb08m,2);
    $deb09m_txt = number_format_ua ($deb09m,2);
    $deb10m_txt = number_format_ua ($deb10m,2);
    $deb11m_txt = number_format_ua ($deb11m,2);
    $deb12m_txt = number_format_ua ($deb12m,2);

    // previous year
    $SQL3 = " select sum(dt_all) as dt_all, sum(deb01m) as deb01m, sum(deb02m) as deb02m, sum(deb03m) as deb03m, sum(deb04m) as deb04m, sum(deb05m) as deb05m, 
            sum(deb06m) as deb06m, sum(deb07m) as deb07m, sum(deb08m) as deb08m, sum(deb09m) as deb09m, sum(deb10m) as deb10m, sum(deb11m) as deb11m, sum(deb12m) as deb12m
           from  rep_month_year_dt_tbl as t 
           join clm_paccnt_tbl as c on (t.id_paccnt = c.id)
           join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class)    
           where t.hmmgg = '2016-01-01' and  t.mmgg = $p_mmgg::date $where ; ";
            
    $result3 = pg_query($Link, $SQL3) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result3) {    
        
        $row3 = pg_fetch_array($result3);
        
        $deb01mp = $row3['deb01m'];
        $deb02mp = $row3['deb02m'];
        $deb03mp = $row3['deb03m'];
        $deb04mp = $row3['deb04m'];
        $deb05mp = $row3['deb05m'];
        $deb06mp = $row3['deb06m'];
        $deb07mp = $row3['deb07m'];
        $deb08mp = $row3['deb08m'];
        $deb09mp = $row3['deb09m'];
        $deb10mp = $row3['deb10m'];
        $deb11mp = $row3['deb11m'];
        $deb12mp = $row3['deb12m'];
    }
    $deb01mp_txt = number_format_ua ($deb01mp,2);
    $deb02mp_txt = number_format_ua ($deb02mp,2);
    $deb03mp_txt = number_format_ua ($deb03mp,2);
    $deb04mp_txt = number_format_ua ($deb04mp,2);
    $deb05mp_txt = number_format_ua ($deb05mp,2);
    $deb06mp_txt = number_format_ua ($deb06mp,2);
    $deb07mp_txt = number_format_ua ($deb07mp,2);
    $deb08mp_txt = number_format_ua ($deb08mp,2);
    $deb09mp_txt = number_format_ua ($deb09mp,2);
    $deb10mp_txt = number_format_ua ($deb10mp,2);
    $deb11mp_txt = number_format_ua ($deb11mp,2);
    $deb12mp_txt = number_format_ua ($deb12mp,2);
    
    
    // кредит
    
    $SQL3 = " select sum(kt_all) as kt_all, sum(kt01m) as kt01m, sum(kt02m) as kt02m, sum(kt03m) as kt03m, sum(kt04m) as kt04m, sum(kt05m) as kt05m, 
            sum(kt06m) as kt06m, sum(kt07m) as kt07m, sum(kt08m) as kt08m, sum(kt09m) as kt09m, sum(kt10m) as kt10m, sum(kt11m) as kt11m, sum(kt12m) as kt12m
           from  rep_month_year_kt_tbl as t 
           join clm_paccnt_tbl as c on (t.id_paccnt = c.id)
           join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class)    
           where t.hmmgg = '2017-01-01' and t.mmgg = $p_mmgg::date $where ; ";
            
    $result3 = pg_query($Link, $SQL3) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result3) {    
        
        $row3 = pg_fetch_array($result3);
        
        $kt01m = $row3['kt01m'];
        $kt02m = $row3['kt02m'];
        $kt03m = $row3['kt03m'];
        $kt04m = $row3['kt04m'];
        $kt05m = $row3['kt05m'];
        $kt06m = $row3['kt06m'];
        $kt07m = $row3['kt07m'];
        $kt08m = $row3['kt08m'];
        $kt09m = $row3['kt09m'];
        $kt10m = $row3['kt10m'];
        $kt11m = $row3['kt11m'];
        $kt12m = $row3['kt12m'];
    }
    
    $kt01m_txt = number_format_ua ($kt01m,2);
    $kt02m_txt = number_format_ua ($kt02m,2);
    $kt03m_txt = number_format_ua ($kt03m,2);
    $kt04m_txt = number_format_ua ($kt04m,2);
    $kt05m_txt = number_format_ua ($kt05m,2);
    $kt06m_txt = number_format_ua ($kt06m,2);
    $kt07m_txt = number_format_ua ($kt07m,2);
    $kt08m_txt = number_format_ua ($kt08m,2);
    $kt09m_txt = number_format_ua ($kt09m,2);
    $kt10m_txt = number_format_ua ($kt10m,2);
    $kt11m_txt = number_format_ua ($kt11m,2);
    $kt12m_txt = number_format_ua ($kt12m,2);
    
    
    $SQL3 = " select sum(kt_all) as kt_all, sum(kt01m) as kt01m, sum(kt02m) as kt02m, sum(kt03m) as kt03m, sum(kt04m) as kt04m, sum(kt05m) as kt05m, 
            sum(kt06m) as kt06m, sum(kt07m) as kt07m, sum(kt08m) as kt08m, sum(kt09m) as kt09m, sum(kt10m) as kt10m, sum(kt11m) as kt11m, sum(kt12m) as kt12m
           from  rep_month_year_kt_tbl as t 
           join clm_paccnt_tbl as c on (t.id_paccnt = c.id)
           join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class)    
           where t.hmmgg = '2016-01-01' and t.mmgg = $p_mmgg::date $where ; ";
            
    $result3 = pg_query($Link, $SQL3) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result3) {    
        
        $row3 = pg_fetch_array($result3);
        
        $kt01m = $row3['kt01m'];
        $kt02m = $row3['kt02m'];
        $kt03m = $row3['kt03m'];
        $kt04m = $row3['kt04m'];
        $kt05m = $row3['kt05m'];
        $kt06m = $row3['kt06m'];
        $kt07m = $row3['kt07m'];
        $kt08m = $row3['kt08m'];
        $kt09m = $row3['kt09m'];
        $kt10m = $row3['kt10m'];
        $kt11m = $row3['kt11m'];
        $kt12m = $row3['kt12m'];
    }
    
    $kt01mp_txt = number_format_ua ($kt01m,2);
    $kt02mp_txt = number_format_ua ($kt02m,2);
    $kt03mp_txt = number_format_ua ($kt03m,2);
    $kt04mp_txt = number_format_ua ($kt04m,2);
    $kt05mp_txt = number_format_ua ($kt05m,2);
    $kt06mp_txt = number_format_ua ($kt06m,2);
    $kt07mp_txt = number_format_ua ($kt07m,2);
    $kt08mp_txt = number_format_ua ($kt08m,2);
    $kt09mp_txt = number_format_ua ($kt09m,2);
    $kt10mp_txt = number_format_ua ($kt10m,2);
    $kt11mp_txt = number_format_ua ($kt11m,2);
    $kt12mp_txt = number_format_ua ($kt12m,2);
    
    
    eval("\$footer_text_eval = \"$footer_text\";");
    echo $footer_text_eval;
   
}
////----------------------------------------------------------------------------
if ($oper=='saldo_years_abon')
{
    $p_mmgg = sql_field_val('dt_b', 'mmgg');

    list($year, $month, $day  ) = split('[/.-]', $p_mmgg);
    $month_str = ukr_month((int)$month,0);
    $period = "за $month_str $year р.";
    $p_id_sector = sql_field_val('id_sector', 'int');
    $p_id_region = sql_field_val('id_region', 'int');
    $p_book = sql_field_val('book', 'string');
    $p_code = sql_field_val('code', 'string'); 
    
    $p_value = sql_field_val('sum_value', 'numeric');
    $p_direction = sql_field_val('sum_direction', 'int');
    $p_year_rep = sql_field_val('year_rep', 'int');
    
    
    $params_caption = '';
    $where="where";

    $sum_dt_b =0;
    $sum_kt_b =0;
    $sum_bill =0;
    $sum_pay  =0;
    $sum_dt_e =0;
    $sum_kt_e =0;

    $sum_bill_kt =0;
    $sum_pay_kt  =0;
    $sum_pay_subs  =0;
    
    $year_dt_b =0;
    $year_kt_b =0;
    $year_bill =0;
    $year_pay  =0;
    $year_dt_e =0;
    $year_kt_e =0;
    
    $year_bill_kt =0;
    $year_pay_kt  =0;
    $year_pay_subs =0;
    
    $i=1;
    
    $np=0;
    $print_flag = 1;
    $mmgg_h='';
    
    if ((isset($_POST['sector']))&&($_POST['sector']!=''))
        $params_caption.= ' Дiльниця: '. trim($_POST['sector']);

    if ($p_book!='null')
    {
        if ($where!="where")  $where.= " and ";                        
        $where.= "  book = $p_book ";
        $params_caption .= " книга : $p_book ";
    }

    if ($p_code!='null')
    {
        if ($where!="where")  $where.= " and ";               
        $where.= "  code = $p_code ";
        $params_caption .= " Рахунок : $p_code ";
    }
    
   if ($p_id_sector!='null')
   {
      if ($where!="where")  $where.= " and ";       
      $where.= " exists (select id from prs_runner_paccnt as rp 
          where rp.id_paccnt = c.id and rp.id_sector = $p_id_sector ) ";
   }
    
   
   if ($p_id_region!='null')
   {
        if ($where!="where")  $where.= " and ";                        
        $where.= " exists (select rs.id from 
            prs_runner_paccnt as rp 
            join prs_runner_sectors as rs on (rs.id = rp.id_sector)
            where rp.id_paccnt = c.id and rs.id_region =  $p_id_region ) ";

        $SQL_H="SELECT name from cli_region_tbl where id = $p_id_region ;";
    
        $result = pg_query($Link, $SQL_H) or die("SQL Error: " . pg_last_error($Link) . $SQL_H);
        $row = pg_fetch_array($result);
        
        $params_caption.= $row['name'];        
       
   }   
   
   $where_old_years = ''; 
   if ($old_years==1)
   {
       $where_old_years = " and date_part('year',hmmgg) < date_part('year',mmgg) "; 
   }
   
   if ($p_year_rep!='null')
   {
       $where_old_years = " and date_part('year',hmmgg) = $p_year_rep "; 
       
       $params_caption .= " заборгованість за $p_year_rep рік ";
   }  
       
   
    if ($p_value!='null')
    {
            if ($p_direction==1) 
            {
                if ($where!="where")  $where.= " and ";       
                $where.= " ss.dt_e > $p_value ";
                $params_caption.=' Дебетори ';
                
            }

            if ($p_direction==2) 
            {
                if ($where!="where")  $where.= " and ";       
                $where.= " ss.kt_e > $p_value ";
                $params_caption.=' Кредитори ';
            }
    }
   
   
    eval("\$header_text = \"$header_text\";");
    echo_html( $header_text);
    
    $table_name = 'seb_saldo_tmp';

    $SQL = "select count(*) as cnt from seb_saldo where mmgg = $p_mmgg::date;";
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        $row = pg_fetch_array($result);
        if ($row['cnt']>0) 
        {
            $table_name = 'seb_saldo';
        }
/*        else
        {
          $SQL = "select seb_saldo($p_mmgg,0,null);";
          $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
        }
 */
    }
    

    
    if ($where=="where") $where="";
    
    $SQL = " 
    select  c.code,c.book, adr.town, adr.street, address_print(c.addr) as house,
   (a.last_name||' '||coalesce(substr(a.name,1,1),'')||'.'||coalesce(substr(a.patron_name,1,1),''))||'.'::varchar as abon,
    regexp_replace(regexp_replace(c.code, '-.*?$', '') , '[^0-9]', '','g')::int as int_code,
    ss.* from
    ( 
      select id_paccnt, to_char(hmmgg, 'YYYY') as mmgg_h,
      sum(b_dtval) as dt_b, -- sum(b_dtval_tax) as dt_b_tax,
      sum(b_ktval) as kt_b, -- sum(b_ktval_tax) as kt_b_tax,

      sum(dtval) + sum(dtval_kt) - sum(ktval_subs) as bill, --sum(dtval_tax)+sum(dtval_kt_tax) - sum(ktval_subs_tax) as bill_tax,  
      sum(ktval) + sum(ktval_kt) - sum(ktval_subs) as pay, --sum(ktval_tax)+sum(ktval_kt_tax) - sum(ktval_subs_tax) as pay_tax,  
    
      sum(dtval_kt) as bill_kt,-- sum(dtval_kt_tax) as bill_kt_tax,      
      sum(ktval_kt) as pay_kt, --sum(ktval_kt_tax) as pay_kt_tax,      
      sum(ktval_subs) as pay_subs, --sum(ktval_subs_tax) as pay_subs_tax,      
    
      sum(e_dtval) as dt_e, -- sum(e_dtval_tax) as dt_e_tax,
      sum(e_ktval) as kt_e -- ,  sum(e_ktval_tax) as kt_e_tax
      from $table_name
      where id_pref = 10 and mmgg = $p_mmgg::date 
      $where_old_years
      group by  id_paccnt,hmmgg order by  id_paccnt,hmmgg     
    ) as ss
join clm_paccnt_h as c on (c.id =ss.id_paccnt )
join (select id, max(dt_b) as dt_b from clm_paccnt_h  where 
  ((dt_b < ($p_mmgg::date+'1 month'::interval) and dt_e is null)
  or 
  tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
  ) group by id order by id) as c2 
on (c.id = c2.id and c.dt_b = c2.dt_b)
join clm_abon_h as a on (a.id = c.id_abon) 
join (select id, max(dt_b) as dt_b from clm_abon_h  where 
 ((dt_b < ($p_mmgg::date+'1 month'::interval) and dt_e is null)
  or 
  tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
)
group by id order by id) as a2 on (a.id = a2.id and a.dt_b = a2.dt_b)
join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class)
 $where    
order by mmgg_h,c.book,int_code,c.code;";    
    
    /*
    $SQL = " 
    select  c.code,c.book, adr.town, adr.street, address_print(c.addr) as house,
   (a.last_name||' '||coalesce(substr(a.name,1,1),'')||'.'||coalesce(substr(a.patron_name,1,1),''))||'.'::varchar as abon,
    regexp_replace(regexp_replace(c.code, '-.*?$', '') , '[^0-9]', '','g')::int as int_code,
    ss.* from
    (select id_paccnt, mmgg_h, 
 CASE WHEN coalesce(bb1.value,0) - coalesce(pp1.value,0) > 0 THEN coalesce(bb1.value,0) - coalesce(pp1.value,0) ELSE 0 END as dt_b,
 CASE WHEN coalesce(bb1.value_tax,0) - coalesce(pp1.value_tax,0) > 0 THEN coalesce(bb1.value_tax,0) - coalesce(pp1.value_tax,0) ELSE 0 END as dt_b_tax,
 -CASE WHEN coalesce(bb1.value,0) - coalesce(pp1.value,0) < 0 THEN coalesce(bb1.value,0) - coalesce(pp1.value,0) ELSE 0 END as kt_b,
 -CASE WHEN coalesce(bb1.value_tax,0) - coalesce(pp1.value_tax,0) < 0 THEN coalesce(bb1.value_tax,0) - coalesce(pp1.value_tax,0) ELSE 0 END as kt_b_tax,
 coalesce(bb2.value,0) as bill,coalesce(bb2.value_tax,0) as bill_tax,
 coalesce(pp2.value,0) as pay,coalesce(pp2.value_tax,0) as pay_tax,
 CASE WHEN coalesce(bb3.value,0) - coalesce(pp3.value,0) > 0 THEN coalesce(bb3.value,0) - coalesce(pp3.value,0) ELSE 0 END as dt_e,
 CASE WHEN coalesce(bb3.value_tax,0) - coalesce(pp3.value_tax,0) > 0 THEN coalesce(bb3.value_tax,0) - coalesce(pp3.value_tax,0) ELSE 0 END as dt_e_tax,
 -CASE WHEN coalesce(bb3.value,0) - coalesce(pp3.value,0) < 0 THEN coalesce(bb3.value,0) - coalesce(pp3.value,0) ELSE 0 END as kt_e,
 -CASE WHEN coalesce(bb3.value_tax,0) - coalesce(pp3.value_tax,0) < 0 THEN coalesce(bb3.value_tax,0) - coalesce(pp3.value_tax,0) ELSE 0 END as kt_e_tax
from 
(select sum(value) as value , sum (value_tax) as value_tax, id_paccnt, date_part('year',mmgg_bill) as mmgg_h from acm_bill_tbl 
where mmgg< $p_mmgg::date  and id_pref = 10
group by date_part('year',mmgg_bill), id_paccnt) as bb1
full join
(select sum(value) as value , sum (value_tax) as value_tax, id_paccnt,date_part('year',mmgg_pay) as mmgg_h from acm_pay_tbl 
where mmgg< $p_mmgg::date  and id_pref = 10
group by date_part('year',mmgg_pay),id_paccnt) as pp1
using (id_paccnt, mmgg_h)
full join
(select sum(value) as value , sum (value_tax) as value_tax, id_paccnt, date_part('year',mmgg_bill) as mmgg_h from acm_bill_tbl 
where mmgg= $p_mmgg::date  and id_pref = 10
group by date_part('year',mmgg_bill), id_paccnt) as bb2
using (id_paccnt, mmgg_h)
full join
(select sum(value) as value , sum (value_tax) as value_tax, id_paccnt,date_part('year',mmgg_pay) as mmgg_h from acm_pay_tbl 
where mmgg= $p_mmgg::date  and id_pref = 10
group by date_part('year',mmgg_pay),id_paccnt) as pp2
using (id_paccnt, mmgg_h)
full join
(select sum(value) as value , sum (value_tax) as value_tax, id_paccnt, date_part('year',mmgg_bill) as mmgg_h from acm_bill_tbl 
where mmgg<= $p_mmgg::date  and id_pref = 10
group by date_part('year',mmgg_bill), id_paccnt) as bb3
using (id_paccnt, mmgg_h)
full join
(select sum(value) as value , sum (value_tax) as value_tax, id_paccnt,date_part('year',mmgg_pay) as mmgg_h from acm_pay_tbl 
where mmgg<= $p_mmgg::date  and id_pref = 10
group by date_part('year',mmgg_pay),id_paccnt) as pp3
using (id_paccnt, mmgg_h)
) as ss
join clm_paccnt_h as c on (c.id =ss.id_paccnt )
join (select id, max(dt_b) as dt_b from clm_paccnt_h  where 
  ((dt_b < ($p_mmgg::date+'1 month'::interval) and dt_e is null)
  or 
  tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
  ) group by id order by id) as c2 
on (c.id = c2.id and c.dt_b = c2.dt_b)
join clm_abon_h as a on (a.id = c.id_abon) 
join (select id, max(dt_b) as dt_b from clm_abon_h  where 
 ((dt_b < ($p_mmgg::date+'1 month'::interval) and dt_e is null)
  or 
  tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
)
group by id order by id) as a2 on (a.id = a2.id and a.dt_b = a2.dt_b)
join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class)
where $where    
order by mmgg_h,c.book,int_code,c.code;";
*/
   // throw new Exception(json_encode($SQL));
    $current_year ='';
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        $i=1;
        while ($row = pg_fetch_array($result)) {

            $np++;
            
            if ((($np> ($page_num-1)* $page_size )&& ($np<= $page_num* $page_size )) || ($show_pager ==0))
                $print_flag = 1;
            else
                $print_flag = 0;
            
            $mmgg_h = $row['mmgg_h'];
            
            
            if ($current_year!=$mmgg_h)
            {
    
                if ($current_year!='')
                {
                    
                        $dt_b_txt = number_format_ua ($year_dt_b,2);
                        $kt_b_txt = number_format_ua ($year_kt_b,2);
                        $bill_txt = number_format_ua ($year_bill,2);
                        $pay_txt  = number_format_ua ($year_pay, 2);
                        $dt_e_txt = number_format_ua ($year_dt_e,2);
                        $kt_e_txt = number_format_ua ($year_kt_e,2);
                    
                        $bill_kt_txt = number_format_ua ($year_bill_kt,2);
                        $pay_kt_txt  = number_format_ua ($year_pay_kt,2);
                        $pay_subs_txt  = number_format_ua ($year_pay_subs,2);
                        
                        
                        echo_html( "<TR class='tab_head'>
                        <TD COLSPAN=4>ВСЬОГО по {$current_year}</td>
                        <TD class='c_n'>{$dt_b_txt}</td>
                        <TD class='c_n'>{$kt_b_txt}</td>
                        <TD class='c_n'>{$bill_txt}</td>
                        <TD class='c_n'>{$bill_kt_txt}</td>
                        <TD class='c_n'>{$pay_txt}</td>
                        <TD class='c_n'>{$pay_kt_txt}</td>
                        <TD class='c_n'>{$pay_subs_txt}</td>
                        <TD class='c_n'>{$dt_e_txt}</td>
                        <TD class='c_n'>{$kt_e_txt}</td>
                        </tr> ",$print_flag);        
                        
                        $year_dt_b =0;
                        $year_kt_b =0;
                        $year_bill =0;
                        $year_pay  =0;
                        $year_dt_e =0;
                        $year_kt_e =0;
                        
                        $year_bill_kt=0;
                        $year_pay_kt =0;
                        $year_pay_subs=0;
                        
                        $i=1;   
                        
                }   

                $current_year=$mmgg_h;
                
                echo_html( "<TR class='tab_head'>
                <TD COLSPAN=13>{$mmgg_h}</td>
                </tr> ",$print_flag);        
                
                //$cnt_headers++;
            }            
            

            $abon = htmlspecialchars($row['abon']);
            $addr = htmlspecialchars($row['town'].' '.$row['street'].' '.$row['house']);
            $book=$row['book'].'/'.$row['code'];
            
            $dt_b_txt = number_format_ua ($row['dt_b'],2);
            $kt_b_txt = number_format_ua ($row['kt_b'],2);
            $bill_txt = number_format_ua ($row['bill'],2);
            $pay_txt  = number_format_ua ($row['pay'],2);
            $dt_e_txt = number_format_ua ($row['dt_e'],2);
            $kt_e_txt = number_format_ua ($row['kt_e'],2);
            
            $bill_kt_txt = number_format_ua ($row['bill_kt'],2);
            $pay_kt_txt  = number_format_ua ($row['pay_kt'],2);
            $pay_subs_txt  = number_format_ua ($row['pay_subs'],2);
            
            
            echo_html( "
            <tr >
            <td>$i</td>
            <td class='c_t'>$book</td>
            <td>$abon</td>
            <td>$addr</td>

            <TD class='c_n'>{$dt_b_txt}</td>
            <TD class='c_n'>{$kt_b_txt}</td>
            <TD class='c_n'>{$bill_txt}</td>
            <TD class='c_n'>{$bill_kt_txt}</td>
            <TD class='c_n'>{$pay_txt}</td>
            <TD class='c_n'>{$pay_kt_txt}</td>
            <TD class='c_n'>{$pay_subs_txt}</td>
            <TD class='c_n'>{$dt_e_txt}</td>
            <TD class='c_n'>{$kt_e_txt}</td>
            </tr>  ",$print_flag);        
            $i++;
            
            $sum_dt_b +=$row['dt_b'];
            $sum_kt_b +=$row['kt_b'];
            $sum_bill +=$row['bill'];
            $sum_pay  +=$row['pay'];
            $sum_dt_e +=$row['dt_e'];
            $sum_kt_e +=$row['kt_e'];

            $sum_bill_kt +=$row['bill_kt'];
            $sum_pay_kt  +=$row['pay_kt'];
            $sum_pay_subs+=$row['pay_subs'];
            
            $year_dt_b +=$row['dt_b'];
            $year_kt_b +=$row['kt_b'];
            $year_bill +=$row['bill'];
            $year_pay  +=$row['pay'];
            $year_dt_e +=$row['dt_e'];
            $year_kt_e +=$row['kt_e'];

            $year_bill_kt +=$row['bill_kt'];
            $year_pay_kt  +=$row['pay_kt'];
            $year_pay_subs+=$row['pay_subs'];
            
        }
        
    }
    
    $dt_b_txt = number_format_ua($year_dt_b, 2);
    $kt_b_txt = number_format_ua($year_kt_b, 2);
    $bill_txt = number_format_ua($year_bill, 2);
    $pay_txt = number_format_ua($year_pay, 2);
    $dt_e_txt = number_format_ua($year_dt_e, 2);
    $kt_e_txt = number_format_ua($year_kt_e, 2);
    
    $bill_kt_txt = number_format_ua ($year_bill_kt,2);
    $pay_kt_txt  = number_format_ua ($year_pay_kt,2);
    $pay_subs_txt  = number_format_ua ($year_pay_subs,2);
    

    echo_html( "<TR class='tab_head'>
          <TD COLSPAN=4>ВСЬОГО по {$mmgg_h}</td>
          <TD class='c_n'>{$dt_b_txt}</td>
          <TD class='c_n'>{$kt_b_txt}</td>
          <TD class='c_n'>{$bill_txt}</td>
          <TD class='c_n'>{$bill_kt_txt}</td>
          <TD class='c_n'>{$pay_txt}</td>
          <TD class='c_n'>{$pay_kt_txt}</td>
          <TD class='c_n'>{$pay_subs_txt}</td>
          <TD class='c_n'>{$dt_e_txt}</td>
          <TD class='c_n'>{$kt_e_txt}</td>
          </tr> ",$print_flag);


    $dt_b_txt = number_format_ua ($sum_dt_b,2);
    $kt_b_txt = number_format_ua ($sum_kt_b,2);
    $bill_txt = number_format_ua ($sum_bill,2);
    $pay_txt  = number_format_ua ($sum_pay, 2);
    $dt_e_txt = number_format_ua ($sum_dt_e,2);
    $kt_e_txt = number_format_ua ($sum_kt_e,2);

    $bill_kt_txt = number_format_ua ($sum_bill_kt,2);
    $pay_kt_txt  = number_format_ua ($sum_pay_kt,2);
    $pay_subs_txt  = number_format_ua ($sum_pay_subs,2);
    
    eval("\$footer_text_eval = \"$footer_text\";");

    echo_html( $footer_text_eval,$print_flag);
   
    if ($print_flag==0) echo "</tbody> </table> ";

   
}
////----------------------------------------------------------------------------
if ($oper=='pay_summary')
{
    $p_mmgg = sql_field_val('dt_b', 'mmgg');

    list($year, $month, $day  ) = split('[/.-]', $p_mmgg);
    $month_str = ukr_month((int)$month,0);
    $period = "за $month_str $year р.";
    $period= str_replace("'",'',$period);
    $p_id_region = sql_field_val('id_region', 'int');
    
    $params_caption = '';
    $where = '';

    $summ_all =0;
    $cnt_all  =0;

    $summ_bank =0;
    $cnt_bank  =0;
    
    $current_bank = '';
    $current_bank_id = '';
    $i=1;
    
    if ($p_id_region!='null')
    {
        
        $where.= " and exists (select rs.id from 
            prs_runner_paccnt as rp 
            join prs_runner_sectors as rs on (rs.id = rp.id_sector)
            where rp.id_paccnt = p.id_paccnt and rs.id_region =  $p_id_region ) ";

        $SQL_H="SELECT name from cli_region_tbl where id = $p_id_region ;";
    
        $result = pg_query($Link, $SQL_H) or die("SQL Error: " . pg_last_error($Link) . $SQL_H);
        $row = pg_fetch_array($result);
        
        $params_caption.= $row['name'];               
    }    
    
    eval("\$header_text = \"$header_text\";");
    echo $header_text;
    
    $SQL = " select hp.id,hp.reg_num, ('0'||substring(hp.reg_num FROM '[0-9]+'))::int as int_num, 
    to_char(hp.reg_date, 'DD.MM.YYYY') as hreg_date,
    po.id as id_origin, coalesce(po.name, '- не вказаний -') as name_origin,
    count(p.id_doc) as pay_cnt, sum(p.value) as pay_sum from 
    acm_pay_tbl as p 
    join acm_headpay_tbl as hp on (hp.id = p.id_headpay)
    left join aci_pay_origin_tbl as po on (po.id = hp.id_origin)
    where p.mmgg = $p_mmgg::date and p.id_pref = 10 and p.idk_doc =  100 
    $where 
    group by hp.id,hp.reg_num, ('0'||substring(hp.reg_num FROM '[0-9]+'))::int ,     hreg_date,po.id, name_origin
    order by po.id,('0'||substring(hp.reg_num FROM '[0-9]+'))::int, hreg_date";

   // throw new Exception(json_encode($SQL));
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        $i=1;
        while ($row = pg_fetch_array($result)) {

            $bank = htmlspecialchars($row['name_origin']);
            $id_bank = $row['id_origin'];
            
            if ($bank != $current_bank)
            {
               if($current_bank!='')
               {

                  $summ_txt = number_format_ua ($summ_bank,2);
            
                  echo "
                    <tr class='tab_head' >
                    <td colspan=3> Всього {$current_bank}({$current_bank_id})</td>            
                    <td class='c_i'>{$cnt_bank}</td>
                    <td class='c_n'>{$summ_txt}</td>
                    </tr>  ";        
               }

               $summ_bank =0;
               $cnt_bank  =0;
               
               $current_bank = $bank;
               $current_bank_id = $id_bank;
            }
            
            $summ_txt = number_format_ua ($row['pay_sum'],2);
            
            echo "
            <tr >
            <td>{$row['hreg_date']}</td>
            <td>{$row['reg_num']}({$current_bank_id})</td>
            <td>{$current_bank}</td>
            <td class='c_i'>{$row['pay_cnt']}</td>
            <td class='c_n'>{$summ_txt}</td>
            </tr>  ";        
            $i++;
            
            $summ_all +=$row['pay_sum'];
            $cnt_all  +=$row['pay_cnt'];

            $summ_bank +=$row['pay_sum'];
            $cnt_bank  +=$row['pay_cnt'];
            
        }
        
    }
    
    $summ_txt = number_format_ua ($summ_bank,2);
            
    echo "
      <tr class='tab_head' >
      <td colspan=3> Всього {$current_bank}({$current_bank_id})</td>            
      <td class='c_i'>{$cnt_bank}</td>
      <td class='c_n'>{$summ_txt}</td>
      </tr>  ";        
   
    $summ_txt = number_format_ua ($summ_all,2);

    eval("\$footer_text_eval = \"$footer_text\";");
    echo $footer_text_eval;
   
}
//----------------------------------------------------------------------------

if ($oper=='lgt_f10')
{
    $p_mmgg = sql_field_val('dt_b', 'mmgg');
    $p_id_sector = sql_field_val('id_sector', 'int');
    $p_book = sql_field_val('book', 'string');
    $p_id_region = sql_field_val('id_region', 'int');
    $p_id_town = sql_field_val('addr_town', 'int');
    
    list($year, $month, $day  ) = split('[/.-]', $p_mmgg);
    $month_str = ukr_month((int)$month,0);
    $period_str = "$month_str $year р.";
    
    $params_caption = '';

    $sum_all_cnt =0;
    $sum_town_cnt =0;
    $sum_town_h_cnt =0;
    $sum_village_cnt =0;
    $sum_village_h_cnt =0;
    
    $sum_family_cnt=0;
    $sum_lgt_demand=0;
    
    $sum_demand =0;
    $sum_val =0;

    $where ="";
    $params_caption="";
    $i=1;
    
    if ((isset($_POST['sector']))&&($_POST['sector']!=''))
        $params_caption.= ' Дiльниця: '. trim($_POST['sector']);

    if ((isset($_POST['addr_town_name']))&&($_POST['addr_town_name']!=''))
        $params_caption .= 'населений пункт: '. trim($_POST['addr_town_name']);
    
    
    if ($p_book!='null')
    {
        $where.= " and book = $p_book ";
        $params_caption .= " книга : $p_book ";
    }
    
    if ($p_id_town!='null')
    {
        if ($where!='where') $where.= " and ";
        $where.= " (adr.id_town = $p_id_town or adr.id_parent = $p_id_town ) ";
    }    

   if ($p_id_sector!='null')
   {
      $where.= "and exists (select id from prs_runner_paccnt as rp 
          where rp.id_paccnt = c.id and rp.id_sector = $p_id_sector ) ";
   }

   if ($p_id_region!='null')
   {
        
        $where.= "and exists (select rs.id from 
            prs_runner_paccnt as rp 
            join prs_runner_sectors as rs on (rs.id = rp.id_sector)
            where rp.id_paccnt = c.id and rs.id_region =  $p_id_region ) ";

        $SQL_H="SELECT name from cli_region_tbl where id = $p_id_region ;";
    
        $result = pg_query($Link, $SQL_H) or die("SQL Error: " . pg_last_error($Link) . $SQL_H);
        $row = pg_fetch_array($result);
        
        $params_caption.= $row['name'];        
   }   
    
    eval("\$header_text = \"$header_text\";");
    echo $header_text;
    
    $SQL = " select grp2.ident, grp2.name,grp2.lvl,
sum(all_cnt) as all_cnt,
sum(town_cnt) as town_cnt,
sum(town_h_cnt) as town_h_cnt,
sum(village_cnt) as village_cnt,
sum(village_h_cnt) as village_h_cnt,
sum(demand) as demand, sum(sum_val) as sum_val,
sum(family_cnt) as family_cnt ,sum(demand_lgt) as demand_lgt      
 from 
(select k1.id as id1,k2.id as id2,k3.id as id3, k1.name,k1.ident  from
lgi_kategor_tbl as k1
left join lgi_kategor_tbl as k2 on (k2.id = k1.id_parent)
left join lgi_kategor_tbl as k3 on (k3.id = k2.id_parent)
where coalesce(k1.ident,'') <>''
order by k1.ident
) as grp1
left join
(
select k_id as id_kategory,
 count(distinct id) as all_cnt,
 count(distinct  CASE WHEN ident~'tgr7_1' or (is_town = 1 and ident~'tgr7_6') THEN id END) as town_cnt,
 count(distinct  CASE WHEN (is_town = 1 and ident~'tgr7_3') or ((ident~'tgr7_51') or (ident~'tgr7_53'))  THEN id END) as town_h_cnt,
 count(distinct  CASE WHEN ident~'tgr7_2' or (is_town = 0 and ident~'tgr7_6')  THEN id END) as village_cnt,
 count(distinct  CASE WHEN (is_town = 0 and ident~'tgr7_3') or (ident~'tgr7_52')  THEN id END) as village_h_cnt,
 sum(value) as sum_val, sum(demand) as demand,sum(demand_lgt) as demand_lgt,
 sum(family_cnt) as family_cnt   
 from 
( select c.id,gt.ident, adr.is_town,b.value , b.demand ,lg.demand_lgt, lgc.family_cnt,  k.id as k_id from     
 clm_paccnt_h as c
    join (select id, max(dt_b) as dt_b from clm_paccnt_h  where 
    ((dt_b < ($p_mmgg::date+'1 month'::interval) and dt_e is null)
    or 
    tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
    ) and archive=0 
    group by id order by id) as c2 
    on (c.id = c2.id and c.dt_b = c2.dt_b)
 join aqi_grptar_tbl as gt on (gt.id = c.id_gtar)
 join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class)
 join 
    (
      select id_paccnt, id_grp_lgt , sum(demand_lgt) as demand_lgt 
      from 
      (
       select b.id_paccnt, ls.id_grp_lgt , ls.demand_lgt
       from acm_bill_tbl as b 
       join acm_lgt_summ_tbl as ls on (b.id_doc= ls.id_doc) 
       where b.idk_doc in (200,220) and b.id_pref = 10 
       and (coalesce(ls.demand_lgt,0) <>0 or coalesce(ls.demand_add_lgt,0) <>0 )
       and b.mmgg = $p_mmgg  
       union all
       select distinct lg.id_paccnt, lg.id_grp_lgt,lg.demand_val
       from acm_dop_lgt_tbl as lg
       join lgi_group_tbl as g on(g.id = lg.id_grp_lgt)
       where mmgg = $p_mmgg  and g.id_budjet =1  
      ) as ss
      group by id_paccnt, id_grp_lgt        
    ) as lg on (lg.id_paccnt = c.id)    
 join lgi_group_tbl as gr on (gr.id = lg.id_grp_lgt)
 left join lgi_kategor_tbl k on (k.id = gr.id_kategor)
 left join (
      	select b.id_paccnt, sum(b.value) as value, sum(demand) as demand
        from acm_bill_tbl as b 
               where b.mmgg = date_trunc('month', $p_mmgg::date) and b.id_pref = 10
             group by b.id_paccnt
        ) as b
 on (b.id_paccnt = c.id)

 left join 
    ( select lg.id_paccnt, lg.id_grp_lgt, lg.id_calc ,lg.family_cnt
     from lgm_abon_h as lg  join
      (select lg.id_paccnt, lg.id_grp_lgt, max(lg.id_key) as id_key 
       from lgm_abon_h as lg 
        join (select id, max(dt_b) as dt_b from lgm_abon_h  where
            ((dt_b < ($p_mmgg::date+'1 month'::interval) 
            and ( dt_e is null and (dt_end is null or dt_end >= $p_mmgg )  )
            )
            or 
            tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
        )   group by id order by id) as lg2 
        on (lg.id = lg2.id and lg.dt_b = lg2.dt_b)
      group by lg.id_paccnt, lg.id_grp_lgt
      ) as gg
      on(lg.id_key = gg.id_key)
  ) as lgc on (lgc.id_paccnt = c.id and lgc.id_grp_lgt = lg.id_grp_lgt)
    
    
 where gr.id_budjet = 1  
    $where  
 ) as ss
group by k_id
) as sss
on (sss.id_kategory = grp1.id1)
left join
(
 select id, ident, lvl ,name 
 from lgi_kategor_tbl
) as grp2
on (grp2.id in (grp1.id1,grp1.id2,grp1.id3))
group by  grp2.ident, grp2.name,grp2.lvl order by grp2.ident;";

    /*
union all
select c.id,gt.ident, adr.is_town,b.value , b.demand , k.id as k_id from 
 clm_paccnt_h as c
    join (select id, max(dt_b) as dt_b from clm_paccnt_h  where 
    ((dt_b < ($p_mmgg::date+'1 month'::interval) and dt_e is null)
    or 
    tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
    ) and archive=0 
    group by id order by id) as c2 
    on (c.id = c2.id and c.dt_b = c2.dt_b)
 join ( select * from acm_dop_lgt_tbl where mmgg = $p_mmgg::date ) as lg on (c.id = lg.id_paccnt)    
 join aqi_grptar_tbl as gt on (gt.id = c.id_gtar)
 join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class)
 join lgi_group_tbl as gr on (gr.id = lg.id_grp_lgt)
 join lgi_kategor_tbl k on (k.id = gr.id_kategor)
 left join (
      	select b.id_paccnt, sum(b.value_calc) as value, sum(demand) as demand
        from acm_bill_tbl as b 
               where b.mmgg = date_trunc('month', $p_mmgg::date) and b.id_pref = 10
             group by b.id_paccnt
        ) as b
 on (b.id_paccnt = c.id)
 where gr.id_budjet = 1  $where 
     */
    
   // throw new Exception(json_encode($SQL));
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        $i=0;
        while ($row = pg_fetch_array($result)) {

            $sum_val_txt = number_format_ua ($row['sum_val'],2);
            
            echo "
            <tr >
            <td>{$row['ident']} {$row['name']}</td>
            <TD class='c_i'>{$row['all_cnt']}</td>
            <TD class='c_i'>{$row['town_cnt']}</td>
            <TD class='c_i'>{$row['town_h_cnt']}</td>
            <TD class='c_i'>{$row['village_cnt']}</td>
            <TD class='c_i'>{$row['village_h_cnt']}</td>
            <TD class='c_i'>{$row['demand']}</td>
            <TD class='c_n'>{$sum_val_txt}</td>
            <TD class='c_i'>{$row['family_cnt']}</td>            
            <TD class='c_i'>{$row['demand_lgt']}</td>            
            </tr>  ";        
            $i++;
            
            if ($row['lvl']==1)
            {
                $sum_all_cnt +=$row['all_cnt'];
                $sum_town_cnt +=$row['town_cnt'];
                $sum_town_h_cnt +=$row['town_h_cnt'];
                $sum_village_cnt +=$row['village_cnt'];
                $sum_village_h_cnt +=$row['village_h_cnt'];
                
                $sum_demand +=$row['demand'];
                $sum_val +=$row['sum_val'];
                
                $sum_family_cnt +=$row['family_cnt'];
                $sum_lgt_demand +=$row['demand_lgt'];
            }
            
            
        }
        
    }
    
    $sum_val_txt = number_format_ua ($sum_val,2);
    
    echo "
     <tr class='tab_head'>
     <td>ВСЬОГО</td>
     <TD class='c_i'>{$sum_all_cnt}</td>
     <TD class='c_i'>{$sum_town_cnt}</td>
     <TD class='c_i'>{$sum_town_h_cnt}</td>
     <TD class='c_i'>{$sum_village_cnt}</td>
     <TD class='c_i'>{$sum_village_h_cnt}</td>
     <TD class='c_i'>{$sum_demand}</td>
     <TD class='c_n'>{$sum_val_txt}</td>
     <TD class='c_i'>{$sum_family_cnt}</td>            
     <TD class='c_i'>{$sum_lgt_demand}</td>            
     
     </tr>  ";        
    
//----АПК
    $SQL = "  select count(distinct c.id) as all_cnt,
 sum(b.value) as sum_val, sum(b.demand) as demand
 from 
 clm_paccnt_h as c
    join (select id, max(dt_b) as dt_b from clm_paccnt_h  where 
    ((dt_b < ($p_mmgg::date+'1 month'::interval) and dt_e is null)
    or 
    tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
    ) and archive=0 
    group by id order by id) as c2 
    on (c.id = c2.id and c.dt_b = c2.dt_b)
    
 join aqi_grptar_tbl as gt on (gt.id = c.id_gtar)
 join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class)     
 left join (
      	select b.id_paccnt, sum(b.value_calc) as value, sum(demand) as demand
        from acm_bill_tbl as b 
               where b.mmgg = date_trunc('month', $p_mmgg::date) and b.id_pref = 10
             group by b.id_paccnt
        ) as b
 on (b.id_paccnt = c.id)
 where gt.id =11  $where ;";

   // throw new Exception(json_encode($SQL));
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        while ($row = pg_fetch_array($result)) {

            $sum_val_txt = number_format_ua ($row['sum_val'],2);
            
            echo "
            <tr >
            <td>АПК</td>
            <TD class='c_i'>{$row['all_cnt']}</td>
            <TD class='c_i'>{$row['all_cnt']}</td>
            <TD class='c_i'>0</td>
            <TD class='c_i'>0</td>
            <TD class='c_i'>0</td>
            <TD class='c_i'>{$row['demand']}</td>
            <TD class='c_n'>{$sum_val_txt}</td>
            <TD class='c_i'>0</td>            
            <TD class='c_i'>0</td>            
            </tr>  ";        
            
        }
        
    }
     
//----местные льготы
    $SQL = " 
select 
 count(distinct c.id) as all_cnt,
 count(distinct  CASE WHEN gt.ident~'tgr7_1' or (adr.is_town = 1 and gt.ident~'tgr7_6') THEN c.id END) as town_cnt,
 count(distinct  CASE WHEN (adr.is_town = 1 and gt.ident~'tgr7_3') or ((gt.ident~'tgr7_51') or (gt.ident~'tgr7_53'))  THEN c.id END) as town_h_cnt,
 count(distinct  CASE WHEN gt.ident~'tgr7_2' or (adr.is_town = 0 and gt.ident~'tgr7_6')  THEN c.id END) as village_cnt,
 count(distinct  CASE WHEN (adr.is_town = 0 and gt.ident~'tgr7_3') or (gt.ident~'tgr7_52')  THEN c.id END) as village_h_cnt,
 sum(b.value) as sum_val, sum(b.demand) as demand
 from 
 clm_paccnt_h as c
    join (select id, max(dt_b) as dt_b from clm_paccnt_h  where 
    ((dt_b < ($p_mmgg::date+'1 month'::interval) and dt_e is null)
    or 
    tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
    ) and archive=0 
    group by id order by id) as c2 
    on (c.id = c2.id and c.dt_b = c2.dt_b)
 join aqi_grptar_tbl as gt on (gt.id = c.id_gtar)
 join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class)
 left join ( 
    select distinct id_paccnt,id_grp_lgt 
    from acm_dop_lgt_tbl 
    where mmgg = $p_mmgg::date) as dlg on (c.id = dlg.id_paccnt)
 left join 
    ( select distinct id_paccnt, id_grp_lgt
     from lgm_abon_h as lg 
     join (select id, max(dt_b) as dt_b from lgm_abon_h  where
            ((dt_b < ($p_mmgg::date+'1 month'::interval) 
            and ( dt_e is null and (dt_end is null or dt_end >= $p_mmgg )  )
            )
            or 
            tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
        )   group by id order by id) as lg2 
     on (lg.id = lg2.id and lg.dt_b = lg2.dt_b)    
    ) as lg on (lg.id_paccnt = c.id)    
 left join lgi_group_tbl as gr on (gr.id = coalesce(lg.id_grp_lgt,dlg.id_grp_lgt))
  left join (
      	select b.id_paccnt, sum(b.value_calc) as value, sum(demand) as demand
        from acm_bill_tbl as b 
               where b.mmgg = date_trunc('month', $p_mmgg::date) and b.id_pref = 10
             group by b.id_paccnt
        ) as b
 on (b.id_paccnt = c.id)
where gr.id_budjet = 2 and ((dlg.id_paccnt is not null) or (lg.id_paccnt is not null))  $where  ;";

   // throw new Exception(json_encode($SQL));
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        while ($row = pg_fetch_array($result)) {

            $sum_val_txt = number_format_ua ($row['sum_val'],2);
            
            echo "
            <tr >
            <td>Місцеві пільги</td>
            <TD class='c_i'>{$row['all_cnt']}</td>
            <TD class='c_i'>{$row['town_cnt']}</td>
            <TD class='c_i'>{$row['town_h_cnt']}</td>
            <TD class='c_i'>{$row['village_cnt']}</td>
            <TD class='c_i'>{$row['village_h_cnt']}</td>
            <TD class='c_i'>{$row['demand']}</td>
            <TD class='c_n'>{$sum_val_txt}</td>
            <TD class='c_i'>0</td>            
            <TD class='c_i'>0</td>            
            </tr>  ";        
            
        }
        
    }
     
     
    eval("\$footer_text_eval = \"$footer_text\";");
    echo $footer_text_eval;
   
}
////----------------------------------------------------------------------------

if (($oper=='subs_list')||($oper=='subs_bad'))
{
    $p_mmgg = sql_field_val('dt_b', 'mmgg');
    $p_id_region = sql_field_val('id_region', 'int');    
    
    list($year, $month, $day  ) = split('[/.-]', $p_mmgg);
    $month_str = ukr_month((int)$month,0);
    $period = "за $month_str $year р.";

    //разбор и обработка строки параметров jqgrid
    $qWhere='';
    if (isset($_POST['grid_params'])) {
          $grid_params_json = $_POST['grid_params'];
          $json_str = stripslashes($grid_params_json); 
          $grid_params = json_decode($json_str,true);
          
          $fildsArray =DbGetFieldsArray($Link,'acm_subs_tbl');
          $fildsArray['addr'] = array('f_name' => 'addr', 'f_type' => 'character varying');
          $fildsArray['abon'] = array('f_name' => 'abon', 'f_type' => 'character varying');
          $fildsArray['p_mmgg'] = array('f_name' => 'mmgg', 'f_type' => 'date');

          $qWhere= DbBuildWhere($grid_params,$fildsArray);
          
    }
    
    if ($qWhere=='')
    {
        $qWhere = " where mmgg = $p_mmgg";
    }
    
    if ($oper=='subs_bad')    
        $qWhere.= " and id_paccnt is null";
        
            //$qWhere.= " and book is null";
        
    $params_caption = '';
 
    if ($p_id_region!='null')
    {
        if ($p_id_region!=999)
        {
            $qWhere.= " and exists (select rs.id from 
            prs_runner_paccnt as rp 
            join prs_runner_sectors as rs on (rs.id = rp.id_sector)
            where rp.id_paccnt = ss.id_paccnt and rs.id_region =  $p_id_region ) ";

            $SQL_H="SELECT name from cli_region_tbl where id = $p_id_region ;";
    
            $result = pg_query($Link, $SQL_H) or die("SQL Error: " . pg_last_error($Link) . $SQL_H);
            $row = pg_fetch_array($result);
        
            $params_caption.= $row['name'];            
        }
        else
        {
            $qWhere.= " and ss.id_paccnt is null";
            
            $params_caption.= 'Нерозпізнані особові рахунки';                        
        }
    }    
    
    
    $sum_subs_month = 0;
    $sum_ob_pay = 0;
    $sum_subs_all = 0;
    $sum_subs_recalc = 0;

    $i=1;
    
    eval("\$header_text = \"$header_text\";");
    echo $header_text;
    
    
    $SQL = "select * from (
 select s.*, 
 CASE WHEN val_month <> 0 THEN subs_all END as subs_all,
 CASE WHEN val_month = 0 THEN subs_all END as subs_recalc,
 to_char(s.dt_b, 'DD.MM.YYYY') as dt_b_txt,   
 to_char(s.dt_e, 'DD.MM.YYYY') as dt_e_txt,       
 (c.name_town||' '||c.name_street||' '||
   (coalesce('буд.'||c.house||'','')||
			coalesce(c.corp||'','')||
				coalesce(', кв. '||c.flat,'') ))::varchar as addr, c.fio as abon,
('0'||substring(s.code FROM '[0-9]+'))::int as int_code,
CASE WHEN coalesce(lg.lgt_cnt,0)>0 THEN '1' ELSE '' END as lg_flag    
from acm_subs_tbl as s
left join aci_subs_tbl as c on (c.id = s.id_subs)
left join (
  select id_paccnt, count(*) as lgt_cnt from lgm_abon_tbl 
     where ((dt_start < ($p_mmgg::date+'1 month'::interval) and (dt_end is null or dt_end >= $p_mmgg ))
            or 
            tintervalov(tinterval(dt_start::timestamp::abstime,dt_end::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime)))
     group by id_paccnt order by id_paccnt
 )  as lg on (lg.id_paccnt = s.id_paccnt)
    
) as ss
  $qWhere Order by book ,int_code ,code ;";

   // throw new Exception(json_encode($SQL));
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        $i=1;
        while ($row = pg_fetch_array($result)) {

            $abon = htmlspecialchars($row['abon']);
            $addr = htmlspecialchars($row['addr']);
            $bookcod = htmlspecialchars($row['bookcod']);

            $norma_pay = number_format_ua ($row['norma_pay'],2);
            $ob_pay = number_format_ua ($row['ob_pay']+$row['ob_zon'],2);
            $subs_all = number_format_ua ($row['subs_all'],2);
            $subs_month = number_format_ua ($row['subs_month'],2);
            $subs_recalc = number_format_ua ($row['subs_recalc'],2);
            $norma_kwt = number_format_ua ($row['norma_kwt'],5);
            $ob_kwt = number_format_ua ($row['ob_kwt'],5);
            $subs_kwt = number_format_ua ($row['subs_kwt'],5);
            
            echo "
            <tr >
            <td>{$i}</td>            
            <td class='c_t'>{$row['book']}</td>
            <td class='c_t'>{$row['code']}</td>
            <td class='c_t'>$abon</td>
            <td class='c_t'>$addr</td>
            <td class='c_t'>{$row['lg_flag']}</td>
            <td>{$row['num_subs']}</td>
            <td class='c_t'>$bookcod</td>
            <td>{$row['dt_b_txt']}</td>
            <td>{$row['dt_e_txt']}</td>
            <td class='c_i'>{$row['val_month']}</td>
            <td class='c_n'>{$norma_pay}</td>
            <td class='c_i'>{$row['norma_subskwt']}</td>
            <td>{$norma_kwt}</td>
            <td class='c_i'>{$row['kol_subs']}</td>
            <td class='c_n'>{$ob_pay}</td>
            <td>{$ob_kwt}</td>
            <td class='c_n'>{$subs_all}</td>
            <td class='c_n'>{$subs_month}</td>
            <td class='c_n'>{$subs_recalc}</td>
            <td>{$subs_kwt}</td>
            </tr>  ";        
            $i++;
            
            $sum_subs_month += $row['subs_month'];
            $sum_ob_pay += $row['ob_pay']+$row['ob_zon'];
            $sum_subs_all += $row['subs_all'];
            $sum_subs_recalc += $row['subs_recalc'];
            
        }
        
    }
    
    $ob_pay = number_format_ua ($sum_ob_pay,2);
    $subs_all = number_format_ua ($sum_subs_all,2);
    $subs_month = number_format_ua ($sum_subs_month,2);
    $subs_recalc = number_format_ua ($sum_subs_recalc,2);

    eval("\$footer_text_eval = \"$footer_text\";");
    echo $footer_text_eval;
   
}
////----------------------------------------------------------------------------
if ($oper=='tarif_abon_sum')
{
    $p_mmgg = sql_field_val('dt_b', 'mmgg');

    list($year, $month, $day  ) = split('[/.-]', $p_mmgg);
    $month_str = ukr_month((int)$month,0);
    $period = "за $month_str $year р.";
    $period_str= str_replace("'",'',$period);
    
    $p_id_tar = sql_field_val('id_gtar', 'int');        
    
    $p_id_sector = sql_field_val('id_sector', 'int');

    $p_book = sql_field_val('book', 'string');
    $p_id_region = sql_field_val('id_region', 'int');
    $params_caption = '';
    $where = '';

    if ((isset($_POST['sector']))&&($_POST['sector']!=''))
        $params_caption.= ' Дiльниця: '. trim($_POST['sector']);
    
    
    if ($p_id_tar!='null')
    {
        $where.= " and t.id_grptar = $p_id_tar ";
    }
    
    if ($p_book!='null')
    {
        $where.= " and book = $p_book ";
        $params_caption .= " книга : $p_book ";
        
    }
    if ($p_id_sector!='null')
    {
       $where.= "and exists (select id from prs_runner_paccnt as rp 
            where rp.id_paccnt = c.id and rp.id_sector = $p_id_sector ) ";
    }
    
    if ($p_id_region!='null')
    {
        $where.= "and exists (select rs.id from 
            prs_runner_paccnt as rp 
            join prs_runner_sectors as rs on (rs.id = rp.id_sector)
            where rp.id_paccnt = c.id and rs.id_region =  $p_id_region ) ";

        $SQL_H="SELECT name from cli_region_tbl where id = $p_id_region ;";
    
        $result = pg_query($Link, $SQL_H) or die("SQL Error: " . pg_last_error($Link) . $SQL_H);
        $row = pg_fetch_array($result);
        
        $params_caption.= $row['name'];               
    }    
    
    
    if ((isset($_POST['gtar']))&&($_POST['gtar']!=''))
        $params_caption .= ' Тарифна група : '. trim($_POST['gtar']);
    

    $demand_all =0;
    $summ_all =0;
    $summ_lgt_all  =0;

    $demand_tar =0;
    $summ_tar =0;
    $summ_lgt_tar  =0;
    
    $current_tar = '';
    $current_tar_id = '';
    $i=1;
    $nn = 1;
    
    $np=0;
    $print_flag = 1;
    
    eval("\$header_text = \"$header_text\";");
    echo_html( $header_text);
    
    $SQL = "delete from rep_zvit_lgt_tbl;";
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);

    $SQL = "insert into rep_zvit_lgt_tbl (id_paccnt,id_grptar,id_tarif,is_town,summ_lgt, id_grp_lgt)
  select b.id_paccnt,t.id_grptar,ls.id_tarif,adr.is_town, sum(ls.summ_lgt) as sum_lgt, 1
  from acm_bill_tbl as b 
  join acm_lgt_summ_tbl as ls on (b.id_doc= ls.id_doc) 
  join aqm_tarif_tbl as t on (t.id = ls.id_tarif)

  join clm_paccnt_h as a on (a.id = b.id_paccnt)
  join (select id, max(dt_b) as dt from clm_paccnt_h  where 
    ((dt_b < ($p_mmgg::date+'1 month'::interval) and dt_e is null)
    or 
    tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime)))
  group by id order by id) as a2 on (a.id = a2.id and a2.dt = a.dt_b)
  join adt_addr_tbl as adr on (adr.id = (a.addr).id_class) 
 where b.idk_doc in (200,220) and b.id_pref = 10
 and b.mmgg = $p_mmgg          
 and a.archive =0
 group by b.id_paccnt,t.id_grptar,ls.id_tarif,adr.is_town;";

    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    
    
    $SQL = " select c.id, c.book, c.code, adr.town, adr.street, address_print(c.addr) as house,t.name as tarif_name,
(a.last_name||' '||coalesce(substr(a.name,1,1),'')||'.'||coalesce(substr(a.patron_name,1,1),''))||'.'::varchar as abon,
ss2.*, lg.summ_lgt,
regexp_replace(regexp_replace(c.code, '-.*?$', '') , '[^0-9]', '','g')::int as int_code,
regexp_replace(regexp_replace(c.book, '-.*?$', '') , '[^0-9]', '','g')::int as int_book
 from 
(
   select b.id_paccnt, bs.id_tarif,  sum(bs.demand) as demand, sum(summ) as sum_val
        from acm_bill_tbl as b 
	join acm_summ_tbl as bs on (b.id_doc= bs.id_doc) 
	join aqm_tarif_tbl as t on (t.id = bs.id_tarif)
	where b.idk_doc  in (200,220) and b.id_pref = 10
        and b.mmgg = $p_mmgg
   group by b.id_paccnt, bs.id_tarif
) as ss2
 join clm_paccnt_h as c on (c.id = ss2.id_paccnt)
 join (select id, max(dt_b) as dt from clm_paccnt_h  where 
    ((dt_b < ($p_mmgg::date+'1 month'::interval) and dt_e is null)
    or 
    tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime)))
 group by id order by id) as c2 on (c.id = c2.id and c2.dt = c.dt_b)
 join clm_abon_h as a on (a.id = c.id_abon) 
 join (select id, max(dt_b) as dt_b from clm_abon_h  where 
 ((dt_b < ($p_mmgg::date+'1 month'::interval) and dt_e is null)
  or 
  tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
)
 group by id order by id) as a2 on (a.id = a2.id and a.dt_b = a2.dt_b)
 join aqm_tarif_tbl as t on (t.id = ss2.id_tarif)
 join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class)
 left join rep_zvit_lgt_tbl as lg on (lg.id_paccnt = c.id and lg.id_tarif = ss2.id_tarif)
where c.archive =0 $where
order by t.id_grptar, t.ident, int_book,int_code;";

   // throw new Exception(json_encode($SQL));
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        $i=1;
        while ($row = pg_fetch_array($result)) {

            $np++;
            
            if ((($np> ($page_num-1)* $page_size )&& ($np<= $page_num* $page_size )) || ($show_pager ==0))
                $print_flag = 1;
            else
                $print_flag = 0;
            
            $tarif = htmlspecialchars($row['tarif_name']);
            $id_tarif = $row['id_tarif'];
            
            if ($tarif != $current_tar)
            {
               if($current_tar!='')
               {

                  $demand_tar_txt = number_format_ua ($demand_tar,0);
                  $summ_tar_txt = number_format_ua ($summ_tar,2);
                  $summ_lgt_tar_txt = number_format_ua ($summ_lgt_tar,2);
            
                  echo_html( "
                    <tr class='tab_head' >
                    <Td colspan='4'>Всього {$current_tar} </td>
                    <td class='c_i'>{$demand_tar_txt}</td>
                    <td class='c_n'>{$summ_tar_txt}</td>
                    <td class='c_n'>{$summ_lgt_tar_txt}</td>
                    </tr>  ",$print_flag);        
               }

               $demand_tar =0;
               $summ_tar  =0;
               $summ_lgt_tar  =0;
               $nn = 1;
               
               $current_tar = $tarif;
               $current_tar_id = $id_tarif;
               
               if ($p_sum_only!=1)
               {
               echo_html( "
                  <tr class='tab_head' >
                  <Td colspan='7'>{$current_tar} </td>
                  </tr>  ",$print_flag);        
               }
            }
            
            $abon = htmlspecialchars($row['abon']);
            $addr = htmlspecialchars($row['town'].' '.$row['street'].' '.$row['house']);
            $book=$row['book'].'/'.$row['code'];
            
            $demand_txt = number_format_ua ($row['demand'],0);
            $summ_txt = number_format_ua ($row['sum_val'],2);
            $summ_lgt_txt = number_format_ua ($row['summ_lgt'],2);

            if ($p_sum_only!=1)    {
            echo_html( "
            <tr >
            <td class ='c_i'>$nn</td>
            <td class='c_t'>{$book}</td>
            <td>{$abon}</td>
            <td>{$addr}</td>
            <td class='c_i'>{$demand_txt}</td>
            <td class='c_n'>{$summ_txt}</td>
            <td class='c_n'>{$summ_lgt_txt}</td>
            </tr>  ",$print_flag);        };
                
            $i++;
            $nn++;

            $demand_all +=$row['demand'];
            $summ_all   +=$row['sum_val'];
            $summ_lgt_all  +=$row['summ_lgt'];

            $demand_tar +=$row['demand'];
            $summ_tar   +=$row['sum_val'];
            $summ_lgt_tar  +=$row['summ_lgt'];
            
        }
        
    }
    
    $demand_tar_txt = number_format_ua ($demand_tar,0);
    $summ_tar_txt = number_format_ua ($summ_tar,2);
    $summ_lgt_tar_txt = number_format_ua ($summ_lgt_tar,2);
            
    echo_html( "
      <tr class='tab_head' >
      <td colspan='4'>Всього {$current_tar} </td>
      <td class='c_i'>{$demand_tar_txt}</td>
      <td class='c_n'>{$summ_tar_txt}</td>
      <td class='c_n'>{$summ_lgt_tar_txt}</td>
     </tr>  ",$print_flag);        

    
    $demand_all_txt = number_format_ua ($demand_all,0);
    $summ_all_txt = number_format_ua ($summ_all,2);
    $summ_lgt_all_txt = number_format_ua ($summ_lgt_all,2);


    eval("\$footer_text_eval = \"$footer_text\";");
    echo_html( $footer_text_eval,$print_flag);
   
    if ($print_flag==0) echo "</tbody> </table> ";
}
//----------------------------------------------------------------------------
if ($oper=='ind_blank')
{
    $p_mmgg = sql_field_val('dt_b', 'mmgg');
    
  //  sleep(20) ;    

    list($year, $month, $day  ) = split('[/.-]', $p_mmgg);
    $month_str = ukr_month((int)$month,0);
    $period = "за $month_str $year р.";
    $period_str= str_replace("'",'',$period);

    $p_book = sql_field_val('book', 'string');    
    $p_id_sector = sql_field_val('id_sector', 'int');
    
    $params_caption = '';
    $where= '';
    
    if ($p_book!='null')
    {
        $where= " and acc.book = $p_book ";
        
        $params_caption.= ' Книга: '. $p_book;
    }    
    if ((isset($_POST['sector']))&&($_POST['sector']!=''))
    {
        $params_caption.= ' Дільниця: '. trim($_POST['sector']);
        
        $where.= " and r.id_sector = $p_id_sector ";
    }

    
    $i=1;
    
    $np=0;
    $print_flag = 1;
    
    eval("\$header_text = \"$header_text\";");
    echo_html( $header_text);
    
    $SQL = " select acc.book, acc.code, adr.adr as street,
    address_print(acc.addr)||coalesce('&nbsp;&nbsp;&nbsp;&nbsp;(&nbsp;відкл.'||to_char(sw.dt_action , 'DD.MM.YYYY')||')','') as adr,
    CASE WHEN length(m.num_meter)> 9 THEN substr(m.num_meter,1,8)||'<br/>'||substr(m.num_meter,9,100) ELSE m.num_meter END as num_meter,
 ( CASE WHEN acc.idk_house = 3 THEN 'Д&nbsp;' ELSE '' END||
   CASE WHEN coalesce(lg.lgt_cnt,0)>0 THEN '+&nbsp;' ELSE '' END||
   CASE WHEN sb.dt_b is not null THEN 'S&nbsp;' ELSE '' END|| 
   substr((c.last_name||'&nbsp;'||coalesce(c.name,'')||'&nbsp;'||coalesce(c.patron_name,''))::varchar,1,42))::varchar as abon,
 m.carry ,
 (CASE WHEN z.id = 0 THEN '' ELSE z.nm END)||(CASE WHEN im.phase = 1 THEN '' ELSE ' 3f' END)::varchar as zone_phase,
('0'||coalesce(regexp_replace(regexp_replace((acc.addr).house, '-.*?$', '') , '[^0-9]', '','g'),''))::int as int_house,
('0'||coalesce(regexp_replace(regexp_replace((acc.addr).flat, '-.*?$', '') , '[^0-9]', '','g'),''))::int as int_flat,
 regexp_replace(regexp_replace(acc.code, '-.*?$', '') , '[^0-9]', '','g')::int as int_code,
i.value as p_indic, to_char(i.dat_ind, 'DD.MM.YYYY') as dt_p_indic
from 
 clm_paccnt_tbl as acc 
join clm_abon_tbl as c on (c.id = acc.id_abon) 
join clm_meterpoint_tbl as m on (m.id_paccnt=acc.id)
join clm_meter_zone_tbl as mz on (mz.id_meter = m.id)
join eqi_meter_tbl as im on (im.id = m.id_type_meter)
join eqk_zone_tbl as z on (z.id = mz.id_zone)
join adt_addr_tbl as adr on (adr.id = (acc.addr).id_class)
join prs_runner_paccnt as r on (r.id_paccnt = acc.id)    
left join (
  select id_paccnt, count(*) as lgt_cnt from lgm_abon_tbl 
     where ((dt_start < ($p_mmgg::date+'1 month'::interval) and (dt_end is null or dt_end >= $p_mmgg ))
            or 
            tintervalov(tinterval(dt_start::timestamp::abstime,dt_end::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime)))
     group by id_paccnt order by id_paccnt
 )  as lg on (lg.id_paccnt = acc.id)
left join (
 select distinct id_paccnt, dt_b, dt_e 
 from acm_subs_tbl as s 
 join (select max(mmgg) as mmgg from acm_subs_tbl where mmgg <= $p_mmgg ) as mm on (mm.mmgg = s.mmgg)
 where tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
 ) as sb on (sb.id_paccnt = acc.id)
  left join (
    select id_meter, id_zone, max(dat_ind) as dat_ind
    from acm_indication_tbl as i 
    group by id_meter,id_zone order by id_meter,id_zone
    ) as  ind on (ind.id_meter = m.id and ind.id_zone = mz.id_zone)
 left join acm_indication_tbl as i on (ind.id_meter = i.id_meter and ind.id_zone = i.id_zone and ind.dat_ind = i.dat_ind) 
left join 
(
       select csw.id_paccnt, csw.dt_action, csw.action
       from clm_switching_tbl as csw 
       join (select id_paccnt, max(dt_action) as maxdt from clm_switching_tbl where action in (1,2) group by id_paccnt) as csdt 
       on (csw.id_paccnt = csdt.id_paccnt and csw.dt_action = csdt.maxdt) 
       left join clm_switching_tbl as cswc on (csw.id_paccnt = cswc.id_paccnt and cswc.dt_action >= csw.dt_action and ((cswc.action =3 and csw.action =1) or (cswc.action =4  and csw.action =2) ) ) 
       where csw.action =1 and cswc.id_paccnt is null 

) as sw on (sw.id_paccnt = acc.id)    
  where acc.archive =0 
  $where
 order by adr.adr, int_house, (acc.addr).house,(acc.addr).korp , int_flat, int_code, acc.code, z.id;";

    //throw new Exception(json_encode($where));
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        $i=1;
        
        $prn_nn = 6;
        $prn_max = 54;
        $table_text= "";
        $cur_street= "";
        
        while ($row = pg_fetch_array($result)) {

            $np++;
            
            if ((($np> ($page_num-1)* $page_size )&& ($np<= $page_num* $page_size )) || ($show_pager ==0))
                $print_flag = 1;
            else
                $print_flag = 0;
            
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
                    echo_html ("
                    <tr>
                    <td class = 'tab_street' colspan = '11'>$cur_street</td>
                    </tr> ",$print_flag);
            }

            $prn_nn=$prn_nn+2;
    
            if ($prn_nn>=$prn_max)
            {
                $row_style = "style = 'page-break-before: always;' ";
                $prn_nn = 1;
            }
            else
            {
                $row_style ='';
            } 
            
            
            echo_html("  <tr height=15 $row_style >
                <td rowspan='2'>$i</td>
                <td>$abon</td>
                <td rowspan='2'>$book/$code</td>                    
                <td rowspan='2' class='num_met'>$num_meter</td>
                <td rowspan='2' class='carry' >$carry</td>
                <td rowspan='2'>$zone_phase</td>
                <td rowspan='2'>$p_indic</td>
                <td rowspan='2'>$dt_p_indic</td>

                <td rowspan='2'>&nbsp;</td>
                <td rowspan='2'>&nbsp;</td>
                </tr>
                    <tr height=15 style='page-break-before:avoid;'>
                <td>$adr</td>
                </tr> ", $print_flag);

            $i++;
            
            
        }
        
    }
    

    eval("\$footer_text_eval = \"$footer_text\";");
    echo_html( $footer_text_eval,$print_flag);
   
    if ($print_flag==0) echo "</tbody> </table> ";

  
}
//----------------------------------------------------------------------------
if ($oper=='ind_blank_res')
{
    $p_mmgg = sql_field_val('dt_b', 'mmgg');
    
    list($year, $month, $day  ) = split('[/.-]', $p_mmgg);
    $month_str = ukr_month((int)$month,0);
    $period = "за $month_str $year р.";
    $period_str= str_replace("'",'',$period);

    $p_book = sql_field_val('book', 'string');    
    $p_id_sector = sql_field_val('id_sector', 'int');
    
    $params_caption = '';
    $where= '';
    $represent_name= '';
    
    if ($p_book!='null')
    {
        $where= " and acc.book = $p_book ";
        
        $params_caption.= ' Книга: '. $p_book;
    }    
    if ((isset($_POST['sector']))&&($_POST['sector']!=''))
    {
        
        $where.= " and r.id_sector = $p_id_sector ";
        
        
        $SQL = "select pr.represent_name, coalesce(p2.represent_name,'') as operator 
          from prs_runner_sectors as rs
          join prs_persons as pr on (rs.id_runner = pr.id)
          left join prs_persons as p2 on (p2.id = rs.id_operator)
        where rs.id = $p_id_sector ;";
        
        $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);    
    
        if ($result) {
            $row = pg_fetch_array($result); 
            $represent_name=$row['represent_name'];
            $operator=$row['operator'];
        }    

        if ($operator!='')
           $params_caption.= ' Дільниця: '. trim($_POST['sector']).' &nbsp;&nbsp;&nbsp; Кур`єр : '.$represent_name.' &nbsp;&nbsp;&nbsp; Оператор : '.$operator;
        else
           $params_caption.= ' Дільниця: '. trim($_POST['sector']).' &nbsp;&nbsp;&nbsp; Кур`єр : '.$represent_name;
        
    }

    
    $i=1;
    
    $np=0;
    $print_flag = 1;
     
    eval("\$header_text = \"$header_text\";");
    echo_html( $header_text);
    
    $SQL = " select acc.book, acc.code, adr.adr as street, 
    address_print(acc.addr)||coalesce('&nbsp;&nbsp;&nbsp;&nbsp;(&nbsp;відкл.'||to_char(sw.dt_action , 'DD.MM.YYYY')||')','') as adr,
    CASE WHEN length(m.num_meter)> 9 THEN substr(m.num_meter,1,8)||'<br/>'||substr(m.num_meter,9,100) ELSE m.num_meter END as num_meter,
 ( CASE WHEN acc.idk_house = 3 THEN 'Д&nbsp;' ELSE '' END||
   CASE WHEN coalesce(lg.lgt_cnt,0)>0 THEN '+&nbsp;' ELSE '' END||
   CASE WHEN sb.dt_b is not null THEN 'S&nbsp;' ELSE '' END|| 
   substr((c.last_name||'&nbsp;'||coalesce(c.name,'')||'&nbsp;'||coalesce(c.patron_name,''))::varchar,1,38))::varchar as abon,
 m.carry ,
 (CASE WHEN z.id = 0 THEN '' ELSE z.nm END)||(CASE WHEN im.phase = 1 THEN '' ELSE ' 3f' END)::varchar as zone_phase,
('0'||coalesce(regexp_replace(regexp_replace((acc.addr).house, '-.*?$', '') , '[^0-9]', '','g'),''))::int as int_house,
('0'||coalesce(regexp_replace(regexp_replace((acc.addr).flat, '-.*?$', '') , '[^0-9]', '','g'),''))::int as int_flat,
 regexp_replace(regexp_replace(acc.code, '-.*?$', '') , '[^0-9]', '','g')::int as int_code,
i.value as p_indic, to_char(i.dat_ind, 'DD.MM.YYYY') as dt_p_indic
from 
 clm_paccnt_tbl as acc 
join clm_abon_tbl as c on (c.id = acc.id_abon) 
join clm_meterpoint_tbl as m on (m.id_paccnt=acc.id)
join clm_meter_zone_tbl as mz on (mz.id_meter = m.id)
join eqi_meter_tbl as im on (im.id = m.id_type_meter)
join eqk_zone_tbl as z on (z.id = mz.id_zone)
join adt_addr_tbl as adr on (adr.id = (acc.addr).id_class)
join prs_runner_paccnt as r on (r.id_paccnt = acc.id)    
left join (
  select id_paccnt, count(*) as lgt_cnt from lgm_abon_tbl 
     where ((dt_start < ($p_mmgg::date+'1 month'::interval) and (dt_end is null or dt_end >= $p_mmgg ))
            or 
            tintervalov(tinterval(dt_start::timestamp::abstime,dt_end::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime)))
     group by id_paccnt order by id_paccnt
 )  as lg on (lg.id_paccnt = acc.id)
 left join (
 select distinct id_paccnt, dt_b, dt_e 
 from acm_subs_tbl as s 
 join (select max(mmgg) as mmgg from acm_subs_tbl where mmgg <= $p_mmgg ) as mm on (mm.mmgg = s.mmgg)
 where tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
 ) as sb on (sb.id_paccnt = acc.id)
  left join (
    select id_meter, id_zone, max(dat_ind) as dat_ind
    from acm_indication_tbl as i 
    group by id_meter,id_zone order by id_meter,id_zone
    ) as  ind on (ind.id_meter = m.id and ind.id_zone = mz.id_zone)
 left join acm_indication_tbl as i on (ind.id_meter = i.id_meter and ind.id_zone = i.id_zone and ind.dat_ind = i.dat_ind) 
left join 
(
       select csw.id_paccnt, csw.dt_action, csw.action
       from clm_switching_tbl as csw 
       join (select id_paccnt, max(dt_action) as maxdt from clm_switching_tbl where action in (1,2) group by id_paccnt) as csdt 
       on (csw.id_paccnt = csdt.id_paccnt and csw.dt_action = csdt.maxdt) 
       left join clm_switching_tbl as cswc on (csw.id_paccnt = cswc.id_paccnt and cswc.dt_action >= csw.dt_action and ((cswc.action =3 and csw.action =1) or (cswc.action =4  and csw.action =2) ) ) 
       where csw.action =1 and cswc.id_paccnt is null 

) as sw on (sw.id_paccnt = acc.id)    
  where acc.archive =0   
  $where
 order by adr.adr, int_house, (acc.addr).house,(acc.addr).korp , int_flat, int_code, acc.code, z.id;";

    //throw new Exception(json_encode($where));
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        $i=1;
        
        $prn_nn = 6;
        $prn_max = 55;
        $table_text= "";
        $cur_street= "";
        
        while ($row = pg_fetch_array($result)) {

            $np++;
            
            if ((($np> ($page_num-1)* $page_size )&& ($np<= $page_num* $page_size )) || ($show_pager ==0))
                $print_flag = 1;
            else
                $print_flag = 0;
            
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
                    echo_html ("
                    <tr>
                    <td class = 'tab_street' colspan = '11'>$cur_street</td>
                    </tr> ",$print_flag);
            }

            $prn_nn=$prn_nn+2;
    
            if ($prn_nn>=$prn_max)
            {
                $row_style = "style = 'page-break-before: always;' ";
                $prn_nn = 1;
            }
            else
            {
                $row_style ='';
            } 
            
            
            echo_html("  <tr height=15 $row_style >
                <td rowspan='2'>$i</td>
                <td>$abon</td>
                <td rowspan='2'>$book/$code</td>                    
                <td rowspan='2' class='num_met'>$num_meter</td>
                <td rowspan='2' class='carry' >$carry</td>
                <td rowspan='2'>$zone_phase</td>
                <td rowspan='2'>$p_indic</td>
                <td rowspan='2'>$dt_p_indic</td>

                <td rowspan='2'>&nbsp;</td>
                <td rowspan='2'>&nbsp;</td>
                <td rowspan='2'>&nbsp;</td>
                </tr>
                    <tr height=15 style='page-break-before:avoid;'>
                <td>$adr</td>
                </tr> ", $print_flag);

            $i++;
            
            
        }
        
    }
    

    eval("\$footer_text_eval = \"$footer_text\";");
    echo_html( $footer_text_eval,$print_flag);
   
    if ($print_flag==0) echo "</tbody> </table> ";

  
}
//----------------------------------------------------------------------------

if (($oper=='bank_load_list')||($oper=='bank_load_bad'))
{
    $p_mmgg = sql_field_val('dt_b', 'mmgg');
    $params_caption = sql_field_val('report_caption', 'str');
    
    list($year, $month, $day  ) = split('[/.-]', $p_mmgg);
    $month_str = ukr_month((int)$month,0);
    $period = "за $month_str $year р.";

    //разбор и обработка строки параметров jqgrid
    $qWhere='';
    if (isset($_POST['grid_params'])) {
          $grid_params_json = $_POST['grid_params'];
          $json_str = stripslashes($grid_params_json); 
          $grid_params = json_decode($json_str,true);

          $fildsArray =DbGetFieldsArray($Link,'acm_pay_load_tbl');
          $fildsArray['p_id'] = array('f_name' => 'id_headpay', 'f_type' => 'int');
          $fildsArray['abon'] = array('f_name' => 'abon', 'f_type' => 'character varying');
          $fildsArray['book'] = array('f_name' => 'book', 'f_type' => 'char');
          $fildsArray['code'] = array('f_name' => 'code', 'f_type' => 'char');
          //$fildsArray['name_file'] = array('f_name' => 'name_file', 'f_type' => 'character varying');
          //$fildsArray['source'] = array('f_name' => 'source', 'f_type' => 'character varying');
          //$fildsArray['reg_num'] = array('f_name' => 'reg_num', 'f_type' => 'character varying');
          
          $qWhere= DbBuildWhere($grid_params,$fildsArray,1);
          
    }
    
    if ($qWhere=='')
    {
        $qWhere = " where mmgg = $p_mmgg";
    }
    
    if ($oper=='bank_load_bad')
    {
        $qWhere.= " and book is null";
    }
    
    //$params_caption = '';

    $sum_pay = 0;

    $i=1;
    
    eval("\$header_text = \"$header_text\";");
    echo_html ($header_text);
    
    $SQL = "select * from (
select p.*,
(c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon,
a.book,a.code, h.name_file,s.name as source, h.reg_num,
 (adr.adr||' '||
   (coalesce('буд.'||(a.addr).house||'','')||
		coalesce('/'||(a.addr).slash||' ','')||
			coalesce(' корп.'||(a.addr).korp||'','')||
				coalesce(', кв. '||(a.addr).flat,'')||
					coalesce('/'||(a.addr).f_slash,''))::varchar
)::varchar as addr,
regexp_replace(regexp_replace(a.code, '-.*?$', '') , '[^0-9]', '','g')::int as int_code,
to_char(p.date_ob, 'DD.MM.YYYY') as date_ob_txt,
to_char(p.pdate, 'DD.MM.YYYY') as pdate_txt,
to_char(p.dateb, 'DD.MM.YYYY') as dateb_txt,
to_char(p.datee, 'DD.MM.YYYY') as datee_txt    
from acm_pay_load_tbl as p
join acm_headpay_tbl as h on (h.id = p.id_headpay)
join aci_pay_origin_tbl as s on (s.id = h.id_origin)
left join clm_paccnt_tbl as a on (a.id = p.id_paccnt)
left join clm_abon_tbl as c on (c.id = a.id_abon) 
left join adt_addr_tbl as adr on (adr.id = (a.addr).id_class)        
    ) as ss
  $qWhere Order by book ,int_code ,code,abcount  ;";

   // throw new Exception(json_encode($SQL));
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        $i=1;
        while ($row = pg_fetch_array($result)) {

            $abon = htmlspecialchars($row['abon']);
            $addr = htmlspecialchars($row['addr']);

            $abcount = htmlspecialchars($row['abcount']);
            $name_citi = htmlspecialchars($row['name_citi']);
            $name_strit = htmlspecialchars($row['name_strit']);
            $fio = htmlspecialchars($row['fio']);

            $pay = number_format_ua ($row['summ'],2);
            
            echo_html( "
            <tr >
            <td>{$i}</td>            
            <td class='c_t'>{$row['book']}</td>
            <td class='c_t'>{$row['code']}</td>
            <td class='c_t'>$abon</td>
            <td class='c_t'>$addr</td>
            
            <td class='c_t'>$abcount</td>
            <td class='c_t'>$fio</td>
            <td>{$row['date_ob_txt']}</td>
            <td>{$row['pdate_txt']}</td>
            <td class='c_n'>{$pay}</td>
            
            <td class='c_t'>$name_citi</td>
            <td class='c_t'>$name_strit</td>
            
            <td class='c_t'>{$row['house']}</td>
            <td class='c_t'>{$row['korpus']}</td>
            <td class='c_t'>{$row['bukva']}</td>
            <td class='c_t'>{$row['kvartira']}</td>

            <td>{$row['dateb_txt']}</td>
            <td>{$row['datee_txt']}</td>
            
            <td class='c_i'>{$row['countb']}</td>
            <td class='c_i'>{$row['counte']}</td>
            <td class='c_i'>{$row['countd']}</td>
            
            </tr>  ");        
            $i++;

            //<td class='c_t'>{$row['source']}</td>
            //<td class='c_t'>{$row['name_file']}</td>
            //<td class='c_t'>{$row['reg_num']}</td>

            
            $sum_pay += $row['summ'];
            
        }
        
    }
    
    $sum_pay_txt = number_format_ua ($sum_pay,2);

    eval("\$footer_text_eval = \"$footer_text\";");
    echo_html( $footer_text_eval);

}
////----------------------------------------------------------------------------
if ($oper=='check_indic_err')
{
    $p_dtb = sql_field_val('dt_b', 'date');
    $p_dte = sql_field_val('dt_e', 'date');
    
    $p_dtb_str = trim($_POST['dt_b']);
    $p_dte_str = trim($_POST['dt_e']);

    $p_id_sector = sql_field_val('id_sector', 'int');
    $p_book = sql_field_val('book', 'string');
    $p_id_region = sql_field_val('id_region', 'int');
    
    $period_str = " з $p_dtb_str до $p_dte_str";
    
    $params_caption = '';
    $where = '';
    
    if ((isset($_POST['sector']))&&($_POST['sector']!=''))
        $params_caption.= ' Дiльниця: '. trim($_POST['sector']);

    if ($p_book!='null')
    {
        $where.= " and book = $p_book ";
        $params_caption .= " книга : $p_book ";
        
    }

   if ($p_id_sector!='null')
   {
      $where.= "and exists (select id from prs_runner_paccnt as rp 
          where rp.id_paccnt = c.id and rp.id_sector = $p_id_sector ) ";
   }
    
   if ($p_id_region!='null')
   {
        
        $where.= "and exists (select rs.id from 
            prs_runner_paccnt as rp 
            join prs_runner_sectors as rs on (rs.id = rp.id_sector)
            where rp.id_paccnt = c.id and rs.id_region =  $p_id_region ) ";

        $SQL_H="SELECT name from cli_region_tbl where id = $p_id_region ;";
    
        $result = pg_query($Link, $SQL_H) or die("SQL Error: " . pg_last_error($Link) . $SQL_H);
        $row = pg_fetch_array($result);
        
        $params_caption.= $row['name'];               
   }    
    $i=1;
    
    $np=0;
    $print_flag = 1;
    
    eval("\$header_text = \"$header_text\";");
    echo_html( $header_text);

    $SQL = "select check_indic_fun();";
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    
    
    $SQL = " select err.*, c.book,c.code,
(ab.last_name||' '||coalesce(ab.name,'')||' '||coalesce(ab.patron_name,''))::varchar as abon,
im.name as type_meter, z.nm as zone_name,
to_char(dat_ind, 'DD.MM.YYYY') as dat_ind_txt,
 ('0'||substring(c.code FROM '[0-9]+'))::int as int_code,
 ('0'||substring(c.book FROM '[0-9]+'))::int as int_book    
 from
rep_indic_errors_tbl as err
join clm_paccnt_tbl as c on (c.id = err.id_paccnt)
join clm_abon_tbl as ab on (ab.id = c.id_abon) 
left join eqi_meter_tbl as im on (im.id = err.id_type_meter)
left join eqk_zone_tbl as z on (z.id = err.id_zone)
where err.dat_ind >= $p_dtb::date and err.dat_ind <= $p_dte::date 
$where    
order by int_book,int_code,dat_ind;";
    
   // throw new Exception(json_encode($SQL));
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        //$i=0;
        while ($row = pg_fetch_array($result)) {

            $np++;
            
            if ((($np> ($page_num-1)* $page_size )&& ($np<= $page_num* $page_size )) || ($show_pager ==0))
                $print_flag = 1;
            else
                $print_flag = 0;
            
            $abon = htmlspecialchars($row['abon']);
            $type_meter = htmlspecialchars($row['type_meter']);
            $book=$row['book'].'/'.$row['code'];
            

            echo_html( "
            <tr >
            <td>{$i}</td>            
            <td class='c_t'>$book</td>
            <td>$abon</td>
            <td >{$row['dat_ind_txt']}</td>
            <td class='c_t'>{$row['num_eqp']}</td>
            <td class='c_t'>$type_meter</td>
            <td class='c_t'>{$row['zone_name']}</td>
            </tr>  ",$print_flag); 
            $i++;
            
        }
        
    }
    

    eval("\$footer_text_eval = \"$footer_text\";");

    echo_html( $footer_text_eval,$print_flag);
   
    if ($print_flag==0) echo "</tbody> </table> ";
   
}
//----------------------------------------------------------------------------

if ($oper=='lgtload_list')
{
    $p_filename = sql_field_val('filename', 'str');
    $p_file = sql_field_val('id_file', 'int');

    //list($year, $month, $day  ) = split('[/.-]', $p_mmgg);
    //$month_str = ukr_month((int)$month,0);
    $period = "файл  $p_filename ";

    //разбор и обработка строки параметров jqgrid
    $qWhere='';
    if (isset($_POST['grid_params'])) {
          $grid_params_json = $_POST['grid_params'];
          $json_str = stripslashes($grid_params_json); 
          $grid_params = json_decode($json_str,true);
          
          $fildsArray =DbGetFieldsArray($Link,'lgm_abon_load_tbl');
          $fildsArray['book'] = array('f_name' => 'book', 'f_type' => 'char');
          $fildsArray['code'] = array('f_name' => 'code', 'f_type' => 'char');
          $fildsArray['addr'] = array('f_name' => 'addr', 'f_type' => 'character varying');
          $fildsArray['abon'] = array('f_name' => 'abon', 'f_type' => 'character varying');
          $fildsArray['name_lgt'] = array('f_name' => 'name_lgt', 'f_type' => 'character varying');
          
          $qWhere= DbBuildWhere($grid_params,$fildsArray);
          
    }
    if ($qWhere=='')
    {
        $qWhere = " where id_file = $p_file";
    }
    else
    {
        $qWhere.= " and id_file = $p_file";
    }
        
    
    $params_caption = '';

    $sum_pay = 0;

    $i=1;
    
    eval("\$header_text = \"$header_text\";");
    echo_html ($header_text);
    
    $SQL = "select * from (
 select ld.*, acc.book, acc.code, lg.name as name_lgt, coalesce(s.name, text(ld.street_code))::varchar as street,
tt.index_town ,
('0'||substring(acc.code FROM '[0-9]+'))::int as int_code,
 (adr.adr||' '||
   (coalesce('буд.'||(acc.addr).house||'','')||
		coalesce('/'||(acc.addr).slash||' ','')||
			coalesce(' корп.'||(acc.addr).korp||'','')||
				coalesce(', кв. '||(acc.addr).flat,'')||
					coalesce('/'||(acc.addr).f_slash||' ',''))::varchar
)::varchar as addr,
 (c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon,
to_char(ld.date_b, 'DD.MM.YYYY') as date_b_txt,    
to_char(ld.date_e, 'DD.MM.YYYY') as date_e_txt,    
to_char(ld.work_period, 'DD.MM.YYYY') as work_period_txt,
st.name as status_name    
from lgm_abon_load_tbl as ld
left join clm_paccnt_tbl as acc on (ld.id_paccnt = acc.id)
left join clm_abon_tbl as c on (c.id = acc.id_abon) 
left join adt_addr_tbl as adr on (adr.id = (acc.addr).id_class)
left join lgi_group_tbl as lg on (lg.id = ld.id_lgt)
left join lgi_load_state_tbl as st on (st.id = ld.status)    
left join lgk_street_upszn_tbl as s on (s.id = ld.street_code)
left join 
(
 select cc.indx, 
 (COALESCE(k.short_prefix::text || ' '::text, ''::text) || cc.name::text) || COALESCE(' '::text || k.short_postfix::text, ''::text) as index_town
 from
 (
  select c.indx, min(c.id) as id from 
  (
   select indx, min(idk_class) as idk from adi_class_tbl
    group by indx
   ) as k
   join adi_class_tbl as c on (c.indx = k.indx and c.idk_class = k.idk)
   group by c.indx
 ) as aa
 join adi_class_tbl as cc on (cc.id = aa.id)
 JOIN adk_class_tbl as k ON k.id = cc.idk_class

 ) as tt on (tt.indx = ld.indx )
         
    
    ) as ss
  $qWhere Order by book ,int_code ,code ;";

   // throw new Exception(json_encode($SQL));
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        $i=1;
        while ($row = pg_fetch_array($result)) {

            $abon = htmlspecialchars($row['abon']);
            $addr = htmlspecialchars($row['addr']);
            $fio_lgt = htmlspecialchars($row['fio_lgt']); 

            $bookcod = htmlspecialchars($row['bookcod']);
            $n_doc = htmlspecialchars($row['n_doc']);            
            $ident_cod_l = htmlspecialchars($row['ident_cod_l']);
            $name_lgt = htmlspecialchars($row['name_lgt']);
            
            $name_street = htmlspecialchars($row['street']);
            $name_town = htmlspecialchars($row['index_town']);
            
            echo_html( "
            <tr >
            <td>{$i}</td>            

            <td class='c_t'>$bookcod</td>
            <td class='c_t'>$fio_lgt</td>
            
            <td class='c_t'>{$row['book']}</td>
            <td class='c_t'>{$row['code']}</td>
            <td class='c_t'>$abon</td>
            <td class='c_t'>$addr</td>
            
            <td class='c_t'>{$row['code_lgt']}</td>
            <td class='c_t'>$name_lgt</td>
            
            <td class='c_t'>{$row['indx']}</td>
            <td class='c_t'>{$name_town}</td>
            <td class='c_t'>{$name_street}</td>
            <td class='c_t'>{$row['house']}</td>
            <td class='c_t'>{$row['korp']}</td>
            <td class='c_t'>{$row['flat']}</td>            
            
            <td class='c_t'>$ident_cod_l</td>
            <td class='c_t'>$n_doc</td>

            <td>{$row['date_b_txt']}</td>
            <td>{$row['date_e_txt']}</td>
            <td>{$row['work_period_txt']}</td>
            <td class='c_t'>{$row['status_name']}</td>
            
            </tr>  ");        
            $i++;
            
        }
        
    }
    
    eval("\$footer_text_eval = \"$footer_text\";");
    echo_html( $footer_text_eval);

}
//-------------------------------------------------------------
if ($oper=='badsubs_list')
{
    //$p_filename = sql_field_val('filename', 'str');
    //$p_file = sql_field_val('id_file', 'int');

    //list($year, $month, $day  ) = split('[/.-]', $p_mmgg);
    //$month_str = ukr_month((int)$month,0);
    //$period = "файл  $p_filename ";
    $p_id_region = sql_field_val('id_region', 'int');
    
    //разбор и обработка строки параметров jqgrid
    $qWhere='';
    if (isset($_POST['grid_params'])) {
          $grid_params_json = $_POST['grid_params'];
          $json_str = stripslashes($grid_params_json); 
          $grid_params = json_decode($json_str,true);
          
          $fildsArray['id'] = array('f_name' => 'id', 'f_type' => 'integer');
          $fildsArray['id_paccnt'] = array('f_name' => 'id_paccnt', 'f_type' => 'integer');
          $fildsArray['book'] = array('f_name' => 'book', 'f_type' => 'char');
          $fildsArray['code'] = array('f_name' => 'code', 'f_type' => 'char');
          $fildsArray['num_subs'] = array('f_name' => 'num_subs', 'f_type' => 'character varying');
          $fildsArray['bookcod'] = array('f_name' => 'bookcod', 'f_type' => 'character varying');
          $fildsArray['calc_book'] = array('f_name' => 'calc_book', 'f_type' => 'character varying');
          $fildsArray['calc_code'] = array('f_name' => 'calc_code', 'f_type' => 'character varying');
          $fildsArray['acc_subs_num'] = array('f_name' => 'acc_subs_num', 'f_type' => 'character varying');
          $fildsArray['fio'] = array('f_name' => 'fio', 'f_type' => 'character varying');
          $fildsArray['addr'] = array('f_name' => 'addr', 'f_type' => 'character varying');
          
          $fildsArray['abon_name'] = array('f_name' => 'abon_name', 'f_type' => 'character varying');
          $fildsArray['ident'] = array('f_name' => 'ident', 'f_type' => 'integer');
          $fildsArray['fcurrent'] = array('f_name' => 'fcurrent', 'f_type' => 'integer');
          
          $qWhere= DbBuildWhere($grid_params,$fildsArray);
          
    }
    
    $params_caption = '';

    if (($p_id_region!='null')&&($p_id_region!=0))
    {
        if ($qWhere=='') $qWhere=' where ';
        else $qWhere.=' and ';
        
        if ($p_id_region!=999)
        {
            $qWhere.= " exists (select rs.id from 
            prs_runner_paccnt as rp 
            join prs_runner_sectors as rs on (rs.id = rp.id_sector)
            where rp.id_paccnt = ss.id_paccnt and rs.id_region =  $p_id_region ) ";

            $SQL_H="SELECT name from cli_region_tbl where id = $p_id_region ;";
    
            $result = pg_query($Link, $SQL_H) or die("SQL Error: " . pg_last_error($Link) . $SQL_H);
            $row = pg_fetch_array($result);
        
            $params_caption.= $row['name'];            
        }
        else
        {
            $qWhere.= " ss.id_paccnt is null";
            
            $params_caption.= 'Нерозпізнані особові рахунки';                        
        }
    }    
    
    $i=1;
    
    eval("\$header_text = \"$header_text\";"); 
    echo_html ($header_text);
    
    $Query=" select to_char(fun_mmgg(), 'DD.MM.YYYY') as mmgg;";
    $result = pg_query($Link,$Query) or die("SQL Error: " .pg_last_error($Link) );
    $row = pg_fetch_array($result);
    $mmgg = $row['mmgg'];    
    
    $SQL = "select * from (
    select  a.id,a.book,a.code,a.num_subs,a.bookcod,a.calc_book,a.calc_code,a.acc_subs_num,a.fio , 
    (coalesce(a.name_town,'')||coalesce(' вул.'||a.name_street,'')||coalesce(' буд.'||a.house,'')||coalesce(' корп.'||a.corp,'')||coalesce(' кв.'||a.flat,'') )::varchar as addr,
    coalesce(p.last_name,'')||' '||coalesce(p.name,'')||' '||coalesce(p.patron_name,'') as abon_name, ident,
    p.id as id_paccnt, p.addr_acc, 
    ('0'||substring(a.code FROM '[0-9]+'))::int as int_code,
    ('0'||substring(a.book FROM '[0-9]+'))::int as int_book,
    CASE WHEN s.id_subs is not null THEN 1 END as fcurrent
    from aci_subs_tbl as a
    left join (select c.*,a.name,a.last_name,a.patron_name ,
      (adr.adr||' '||
       (coalesce('буд.'||(c.addr).house||'','')||
		coalesce('/'||(c.addr).slash||' ','')||
			coalesce(' корп.'||(c.addr).korp||'','')||
				coalesce(', кв. '||(c.addr).flat,'')||
					coalesce('/'||(c.addr).f_slash||' ',''))::varchar
        )::varchar as addr_acc
        from clm_paccnt_tbl c
        join clm_abon_tbl a on (c.id_abon=a.id) 
        join adt_addr_tbl as adr on (adr.id = (c.addr).id_class)
    order by c.id) as p
      on (a.id_paccnt = p.id )  
    left join (select distinct id_subs from acm_subs_tbl where mmgg = '$mmgg' and subs_all <>0 and id_subs is not null order by id_subs) as s 
      on (a.id = s.id_subs)    
    ) as ss
  $qWhere Order by int_book, book ,int_code, code , bookcod ;";

    //throw new Exception(json_encode($SQL));
   // echo $SQL;
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        $i=1;
        while ($row = pg_fetch_array($result)) {

            $abon = htmlspecialchars($row['abon_name']);
            $fio = htmlspecialchars($row['fio']); 
            $addr = htmlspecialchars($row['addr']); 
            $addr_acc = htmlspecialchars($row['addr_acc']); 
            $bookcod = htmlspecialchars($row['bookcod']);
            
            $num_subs = htmlspecialchars($row['num_subs']);            
            $acc_subs_num = htmlspecialchars($row['acc_subs_num']);
            
            echo_html( "
            <tr >
            <td>{$i}</td>            
            <td class='c_t'>$bookcod</td>
            <td class='c_t'>{$row['calc_book']}</td>
            <td class='c_t'>{$row['calc_code']}</td>
            <td class='c_t'>$fio</td>
            <td class='c_t'>$addr</td>
            <td class='c_t'>$num_subs</td>
            <td class='c_t'>{$row['book']}</td>
            <td class='c_t'>{$row['code']}</td>
            <td class='c_t'>$abon</td>
            <td class='c_t'>$addr_acc</td>
            <td class='c_t'>$acc_subs_num</td>
            <td class='c_t'>{$row['ident']}</td>
            <td class='c_t'>{$row['fcurrent']}</td>
            </tr>  ");        
            $i++;
            
        }
        
    }
    
    eval("\$footer_text_eval = \"$footer_text\";");
    echo_html( $footer_text_eval);

}
//---------------------------------------------
if ($oper=='zvit_detal')
{
    $p_mmgg = sql_field_val('dt_b', 'mmgg');
    
    list($year, $month, $day  ) = split('[/.-]', $p_mmgg);
    $year = trim($year,"'");
    
    $p_id_town = sql_field_val('addr_town', 'int');
    $p_id_region = sql_field_val('id_region', 'int'); 
  
    
    $mmgg1_str = trim($_POST['period_str']);
    $params_caption ="";
    $where_paccnt = "";
    if ((isset($_POST['addr_town_name']))&&($_POST['addr_town_name']!=''))
    {
        $params_caption = trim($_POST['addr_town_name']);
        $where_paccnt = "where adr.id_town = $p_id_town";
    }
    else 
    {
      if ($p_id_region!='null')
      {
        

        $SQL_H="SELECT name from cli_region_tbl where id = $p_id_region ;";
    
        $result = pg_query($Link, $SQL_H) or die("SQL Error: " . pg_last_error($Link) . $SQL_H);
        $row = pg_fetch_array($result);
        
        $params_caption.= $row['name'];               
        
        $where_paccnt = " where rs.id_region = $p_id_region";
      }
        
    }
    
    $p_row = sql_field_val('grid_params', 'str');
    $p_column = sql_field_val('grid_params2', 'int');
    
    $where ="";
    $where_num ="";
    $where_ident="";
    $where_grpident="";
    
    if ($p_column == 1 ) {
        $where_ident = "  t.ident~'tgr7_3'";  $where = " and adr.is_town = 1";
        $params_caption.= 'місто - ел.плити';
        }
        
    if ($p_column == 2 ) { 
        //$where_ident = "  t.ident~'tgr7_5'"; $where = " and adr.is_town = 1";
        $where_ident = "  ((t.ident~'tgr7_51') or (t.ident~'tgr7_53')) "; 
        $params_caption.= 'місто - ел.опален.';
        }
    if ($p_column == 3 ) {
        $where_ident = " (( t.ident~'tgr7_1') or ( t.ident~'tgr7_61')) ";
        $params_caption.= 'місто - інше спож.';
        }
    
    if ($p_column == 4 ) { 
        $where_ident = "  t.ident~'tgr7_3'";  $where = " and adr.is_town = 0";
        $params_caption.= 'село - ел.плити';        
        }
    if ($p_column == 5 ) { 
        $where_ident = "  t.ident~'tgr7_52'"; // $where = " and adr.is_town = 0";
        $params_caption.= 'село - ел.опален.';        
        }
    if ($p_column == 6 ) {
        $where_ident = " (( t.ident~'tgr7_2') or ( t.ident~'tgr7_62')) ";
        $params_caption.= 'село - інше спож.';
        }
    
    $codes_old = array('1', '2', '3', '4', '5', '6', '7','m');    
    $codes_new0 = array('01', '02', '03', '04','0m');    
    $codes_new1 = array('11', '12', '13', '14', '15', '16', '17','1m');    
    
    $params_caption.= ' - '.$p_row;        
    
    if (((($year+0)==2017) && (($month+0) >= 3 ))||(($year+0)>2017))
      $grp_code =  substr($p_row,-2);
    else
      $grp_code =  substr($p_row,-1);
    
    echo $grp_code;
    
    if (is_numeric($grp_code))
    {
        
      if (((($year+0)==2017) && (($month+0) >= 3 ))||(($year+0)>2017))
      {
        $row_type =  substr($p_row, 0, -2);
        $where_num = " and p.ident||p.num = '$grp_code'";  
      }
      else
      {
        //$grp_code = $grp_code+0;    
        $row_type =  substr($p_row, 0, -1);
        $where_num = " and p.ident='1' and p.num::int = $grp_code::int";  
      }
      
    }
    else
    {
      $row_type =  $p_row;
    }
    
    $print_flag = 1;
    
    eval("\$header_text_eval = \"$header_text\";");
    echo $header_text_eval;
    
    $SQL = " delete from rep_zvit_tmpbill_tbl; ";
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    
    $SQL = "delete from clm_paccnt_tmp;";
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    
    if (in_array($grp_code, $codes_new1,true)) 
    {
     $p_mmgg_tar = "'2017-02-01'";    
    }
    else
    {
     $p_mmgg_tar = $p_mmgg;       
    }
    
    $where_lgt = '';
    $pattert_ident = "'1'";
    $where_period='';

    if (in_array($grp_code, $codes_new0,TRUE)) 
    {
       $where_lgt = " and (tv.id is null or tv.dt_begin >='2017-03-01') ";
       $pattert_ident = "'0'";
       $where_period = " and dat_b >='2017-03-01' ";
    }
    
    if (in_array($grp_code, $codes_new1,TRUE)) 
    {
       $where_lgt = " and ( tv.dt_begin < '2017-03-01' )";
       $pattert_ident = "'1'";
       $where_period = " and dat_b <'2017-03-01' ";       
    }

    
    $SQL = " insert into clm_paccnt_tmp
 (id, book, code, archive, id_cntrl, id_abon, id_agreem, id_tree, 
       activ, id_gtar, n_subs, rem_worker, not_live, idk_house, pers_cntrl, 
       note, id_dep, dt_b, period_open, dt_open, id_person_open, dt_e,
    period_close, dt_close, id_person_close, id_key, addr, heat_area, id_tarif_min)
    select a.id, a.book, a.code, a.archive, a.id_cntrl, a.id_abon, a.id_agreem, a.id_tree,
    a.activ, a.id_gtar, a.n_subs, a.rem_worker, a.not_live, a.idk_house, a.pers_cntrl, 
       a.note, a.id_dep, a.dt_b, a.period_open, a.dt_open, a.id_person_open, a.dt_e,
       a.period_close, a.dt_close, a.id_person_close, a.id_key, a.addr, a.heat_area, tt.id_tar from
 clm_paccnt_h as a
    join (select id, max(dt_b) as dt from clm_paccnt_h  where 
    ((dt_b < ($p_mmgg::date+'1 month'::interval) and dt_e is null)
    or 
    tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime)))
 group by id order by id) as a2 on (a.id = a2.id and a2.dt = a.dt_b)
 join adt_addr_town_street_tbl as adr on (adr.id = (a.addr).id_class)    
 join prs_runner_paccnt as rp on (rp.id_paccnt = a.id)
 join prs_runner_sectors as rs on (rs.id = rp.id_sector)
 join ( 
   select min(t.id) as id_tar, t.id_grptar
	from aqm_tarif_tbl as t 
        where ((dt_e is null ) or (dt_e > $p_mmgg_tar )) and (dt_b <= $p_mmgg_tar)
        and (t.per_min is null or (t.per_min <= $p_mmgg_tar and t.per_max >= $p_mmgg_tar))
        group by id_grptar
 ) as tt on (tt.id_grptar = a.id_gtar) $where_paccnt ;";
    
    
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);    

    //if ($p_mmgg!=$mmgg_current)
    {
      $SQL = "delete from rep_zvit_lgt_tbl;";
      $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);

      $SQL = "delete from rep_zvit_subs_tbl;";
      $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    
      $SQL = " insert into rep_zvit_lgt_tbl (id_paccnt,id_grp_lgt,id_grptar,id_tarif,is_town,summ_lgt)
        select ls.id_paccnt, ls.id_grp_lgt,  a.id_gtar, 
        CASE WHEN coalesce(t.id_grptar,0)<>coalesce(a.id_gtar,0) THEN tt.id_tar ELSE ls.id_tarif END , --coalesce(ls.id_tarif,tt.id_tar),
        adr.is_town, sum(ls.summ_lgt) as sum_lgt
        from 
        (
            select b.id_paccnt, ls.id_grp_lgt, ls.summ_lgt, ls.id_tarif
            from acm_bill_tbl as b 
            join acm_lgt_summ_tbl as ls on (b.id_doc= ls.id_doc) 
            join lgi_group_tbl as g on(g.id = ls.id_grp_lgt)
            left join aqd_tarif_tbl as tv on (tv.id = ls.id_summtarif)
            where b.idk_doc in (200,220,209,291) and b.id_pref = 10
            and b.mmgg = $p_mmgg    
            and g.id_budjet <> 3  
            $where_lgt
        ) as ls
        join clm_paccnt_tmp as a on (a.id = ls.id_paccnt)
        join adt_addr_tbl as adr on (adr.id = (a.addr).id_class) 
        join ( 
        select min(t.id) as id_tar, t.id_grptar
            from aqm_tarif_tbl as t 
            where ((dt_e is null ) or (dt_e > $p_mmgg_tar )) and (dt_b <= $p_mmgg_tar)
            and (t.per_min is null or (t.per_min <= $p_mmgg_tar and t.per_max >= $p_mmgg_tar))
            group by id_grptar
        ) as tt on (tt.id_grptar = a.id_gtar)
        left join aqm_tarif_tbl as t on (t.id = ls.id_tarif)
        where coalesce(t.id_grptar,0) not in (5,6,8,9,12,13) 
        and   coalesce(a.id_gtar,0) not in (5,6,8,9,12,13) 
        group by ls.id_paccnt,ls.id_grp_lgt,a.id_gtar, CASE WHEN coalesce(t.id_grptar,0)<>coalesce(a.id_gtar,0) THEN tt.id_tar ELSE ls.id_tarif END ,adr.is_town;";
      
        $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);      
        //echo $SQL;

      $SQL = " insert into rep_zvit_lgt_tbl (id_paccnt,id_grp_lgt,id_grptar,id_tarif,is_town,summ_lgt)
     select ls.id_paccnt, ls.id_grp_lgt, 
     coalesce(t.id_grptar,a.id_gtar), 
     coalesce(ls.id_tarif,tt.id_tar),
     adr.is_town, sum(ls.summ_lgt) as sum_lgt
     from 
     (
      select b.id_paccnt, ls.id_grp_lgt, ls.summ_lgt, ls.id_tarif
      from acm_bill_tbl as b 
      join acm_lgt_summ_tbl as ls on (b.id_doc= ls.id_doc) 
      join lgi_group_tbl as g on(g.id = ls.id_grp_lgt)
      left join aqd_tarif_tbl as tv on (tv.id = ls.id_summtarif)        
      where b.idk_doc in (200,220,209,291) and b.id_pref = 10
      and b.mmgg = $p_mmgg    
      and g.id_budjet <> 3  
      $where_lgt  
    ) as ls
      join clm_paccnt_tmp as a on (a.id = ls.id_paccnt)
      join adt_addr_tbl as adr on (adr.id = (a.addr).id_class) 
      join ( 
       select min(t.id) as id_tar, t.id_grptar
    	from aqm_tarif_tbl as t 
        where ((dt_e is null ) or (dt_e > $p_mmgg_tar )) and (dt_b <= $p_mmgg_tar)
        and (t.per_min is null or (t.per_min <= $p_mmgg_tar and t.per_max >= $p_mmgg_tar))
        group by id_grptar
    ) as tt on (tt.id_grptar = a.id_gtar)
      left join aqm_tarif_tbl as t on (t.id = ls.id_tarif)
      where (coalesce(t.id_grptar,0) in (5,6,8,9,12,13) 
         or  coalesce(a.id_gtar,0)   in (5,6,8,9,12,13) )
     group by ls.id_paccnt,ls.id_grp_lgt,coalesce(t.id_grptar,a.id_gtar),coalesce(ls.id_tarif,tt.id_tar),adr.is_town;";
      
        $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);      
        
     if (!in_array($grp_code, $codes_new1,true))
     {
      $SQL = " insert into rep_zvit_subs_tbl (id_paccnt,id_grptar,id_tarif,is_town,summ_subs,summ_tax,summ_resubs,summ_retax)
        select a.id, a.id_gtar,tt.id_tar, adr.is_town, coalesce(subs_pay,0),coalesce(subs_tax,0), coalesce(subs_repay,0),coalesce(subs_retax,0)
         from 
        clm_paccnt_tmp as a
         join adt_addr_tbl as adr on (adr.id = (a.addr).id_class)
         join (
              select p.id_paccnt, 
              sum(CASE WHEN p.idk_doc in (110,111) THEN value END ) as subs_pay, 
              sum(CASE WHEN p.idk_doc in (110,111) THEN value_tax END ) as subs_tax, 
              sum(CASE WHEN p.idk_doc in (193,194) THEN value END ) as subs_repay, 
              sum(CASE WHEN p.idk_doc in (193,194) THEN value_tax END ) as subs_retax 
               from acm_pay_tbl as p
               where p.id_pref = 10 and p.idk_doc in (110, 111, 193,194) 
               and p.mmgg = $p_mmgg 
               group by p.id_paccnt
         ) as pp on (pp.id_paccnt = a.id)
         join ( 
           select min(t.id) as id_tar, t.id_grptar
           from aqm_tarif_tbl as t 
            where ((dt_e is null ) or (dt_e > $p_mmgg )) and (dt_b <= $p_mmgg)
            and (t.per_min is null or (t.per_min <= $p_mmgg and t.per_max >= $p_mmgg))
            group by id_grptar
        ) as tt on (tt.id_grptar = a.id_gtar); ";
      
        $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);      
        
        //echo $SQL;
     }  
    }
    
    if (($row_type =='tovar_sum')||($row_type =='bill_sum')||
        ($row_type =='lgt_sum')||
        ($row_type =='tovar_sum_ng')||($row_type =='bill_sum_ng')||
        ($row_type =='lgt_sum_ng')            
            //||($row_type =='subs_sum')
       )
    {
    
        if (($row_type =='tovar_sum')||($row_type =='bill_sum')||
            ($row_type =='tovar_sum_ng')||($row_type =='bill_sum_ng') )
        {
          $left_l='left';
          $left_s='left';
        }
        if (($row_type =='lgt_sum')||($row_type =='lgt_sum_ng'))
        {
          $left_l='';
          $left_s='left';
        }
        
        if (($row_type =='tovar_sum')||($row_type =='bill_sum')||($row_type =='lgt_sum'))
        {
         // $where_grpident = "t.ident not in ('tgr7_15','tgr7_25','tgr7_35','tgr7_53','tgr7_63') and t.ident !~'tgr7_6'";
             $where_grpident = " t.ident !~'tgr7_6'";
        }

        if (($row_type =='tovar_sum_ng')||($row_type =='bill_sum_ng')||($row_type =='lgt_sum_ng'))
        {
          $where_grpident = " t.ident ~'tgr7_6'";
        }
        
            
    $SQL = " insert into rep_zvit_tmpbill_tbl (id_paccnt,id_grptar,sum_val)
        select b.id_paccnt, 0, sum(bs.sum_tovar) as sum_val
        from acm_bill_tbl as b 
        join clm_paccnt_tmp as a2 on (a2.id = b.id_paccnt)        
	join (select id_doc, id_tarif, sum(summ) as sum_tovar from acm_summ_tbl 
           where mmgg = $p_mmgg 
           $where_period
           group by id_doc, id_tarif order by id_doc, id_tarif) as bs on (b.id_doc= bs.id_doc) 
	join aqm_tarif_tbl as t2 on (t2.id = bs.id_tarif)
        join aqm_tarif_tbl as tm on (tm.id = CASE WHEN coalesce(t2.id_grptar,0)<>coalesce(a2.id_gtar,0) and (coalesce(a2.id_gtar,0) not in (5,6,8,9,12,13))
                                                THEN a2.id_tarif_min ELSE bs.id_tarif END)
        join aqi_grptar_tbl as t on (t.id = tm.id_grptar)
        join rep_zvit_pattern_tbl as p on (p.ident = $pattert_ident and p.lim_min = coalesce(tm.lim_min,0) and p.lim_max = coalesce(tm.lim_max,0))
	where b.idk_doc in (200,220,209,291) and 
        t2.ident not in ('tgr7_15','tgr7_25','tgr7_35','tgr7_53','tgr7_63') and 
        $where_ident
        $where_num
        
        and b.mmgg = $p_mmgg  and b.id_pref = 10
        group by b.id_paccnt --,tm.id_grptar
        order by b.id_paccnt; ";
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
  //  echo $SQL;
    
    
$SQL = " select a.book, a.code,
    ('0'||substring(a.code FROM '[0-9]+'))::int as int_code,
    ('0'||substring(a.book FROM '[0-9]+'))::int as int_book,
(c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon,  lz.lgt_name,
 ss2.id_paccnt, adr.is_town, sum_val,coalesce(lz.sum_lgt,0) as sum_lgt, coalesce(sz.sum_subs,0) as sum_subs,
 (adr.adr||' '||
        (coalesce('буд.'||(a.addr).house||'','')||
		coalesce('/'||(a.addr).slash||' ','')|| 
			coalesce(' корп.'||(a.addr).korp||'','')||
				coalesce(', кв. '||(a.addr).flat,'')||
					coalesce('/'||(a.addr).f_slash,''))::varchar
    )::varchar as addr    
 from 
 rep_zvit_tmpbill_tbl as ss2
 join clm_paccnt_tmp as a on (a.id = ss2.id_paccnt)
 join clm_abon_tbl as c on (c.id = a.id_abon) 
 join aqi_grptar_tbl as t on (t.id = a.id_gtar)
 join adt_addr_tbl as adr on (adr.id = (a.addr).id_class)
 $left_l join 
  (
  select z.id_paccnt, z.id_grptar, sum(z.summ_lgt) as sum_lgt, sum(gg.name||';')::varchar as lgt_name
  from rep_zvit_lgt_tbl as z 
  join lgi_group_tbl as gg on (gg.id = z.id_grp_lgt)
  join aqi_grptar_tbl as t on (t.id = z.id_grptar) 
  join aqm_tarif_tbl as tm on (tm.id = z.id_tarif)
  join rep_zvit_pattern_tbl as p on (p.ident = $pattert_ident and p.lim_min = coalesce(tm.lim_min,0) and p.lim_max = coalesce(tm.lim_max,0))
  where 
       $where_ident
       $where_num
  group by z.id_paccnt, z.id_grptar order by z.id_paccnt, z.id_grptar
) as lz on (lz.id_paccnt = ss2.id_paccnt and lz.id_grptar = t.id)
 $left_s join 
  (
  select z.id_paccnt, z.id_grptar, sum(z.summ_subs+z.summ_resubs) as sum_subs
  from rep_zvit_subs_tbl as z 
  join aqi_grptar_tbl as t on (t.id = z.id_grptar) 
  join aqm_tarif_tbl as tm on (tm.id = z.id_tarif)
  join rep_zvit_pattern_tbl as p on (p.ident = $pattert_ident and p.lim_min = coalesce(tm.lim_min,0) and p.lim_max = coalesce(tm.lim_max,0))
  where 
       $where_ident
       $where_num
  group by z.id_paccnt, z.id_grptar order by z.id_paccnt, z.id_grptar
) as sz on (sz.id_paccnt = ss2.id_paccnt and sz.id_grptar = t.id )
where 
    $where_grpident  $where    
order by int_book, int_code; ";

//echo '</br>';
//echo $SQL;
    }
    
//  ---- многодетные -------
    
    if (
        ($row_type =='tovar_sum_m')||($row_type =='bill_sum_m')||
        ($row_type =='lgt_sum_m')||
        ($row_type =='tovar_sum_0m')||($row_type =='bill_sum_0m')||
        ($row_type =='lgt_sum_0m')||
        ($row_type =='tovar_sum_1m')||($row_type =='bill_sum_1m')||
        ($row_type =='lgt_sum_1m')
            //||($row_type =='subs_m')
            )
    {
    
        if (($row_type =='tovar_sum_m')||($row_type =='bill_sum_m')||
            ($row_type =='tovar_sum_0m')||($row_type =='bill_sum_0m')||
            ($row_type =='tovar_sum_1m')||($row_type =='bill_sum_1m')  )
        {
          $left_l='left';
          $left_s='left';
        }
        if (($row_type =='lgt_sum_m')||($row_type =='lgt_sum_0m')||($row_type =='lgt_sum_1m'))
        {
          $left_l='';
          $left_s='left';
        }
            
    $SQL = " insert into rep_zvit_tmpbill_tbl (id_paccnt,id_grptar,sum_val)
        select b.id_paccnt, tm.id_grptar, sum(bs.sum_tovar) as sum_val
        --select b.id_paccnt, 0, sum(bs.sum_tovar) as sum_val
        from acm_bill_tbl as b 
	join (select id_doc, id_tarif, sum(summ) as sum_tovar from acm_summ_tbl 
          where mmgg = $p_mmgg 
          $where_period
          group by id_doc, id_tarif order by id_doc, id_tarif) as bs on (b.id_doc= bs.id_doc) 
	join aqm_tarif_tbl as tm on (tm.id = bs.id_tarif)
        join aqi_grptar_tbl as t on (t.id = tm.id_grptar)
	where b.idk_doc in (200,220) and 
        tm.ident in ('tgr7_15','tgr7_25','tgr7_35','tgr7_53','tgr7_63') and  
        $where_ident
        and b.mmgg = $p_mmgg  and b.id_pref = 10
        group by b.id_paccnt ,tm.id_grptar
        order by b.id_paccnt; ";
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);

    
    
$SQL = " select a.book, a.code,
    ('0'||substring(a.code FROM '[0-9]+'))::int as int_code,
    ('0'||substring(a.book FROM '[0-9]+'))::int as int_book,
(c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon,  lz.lgt_name,
 ss2.id_paccnt, adr.is_town, sum_val,coalesce(lz.sum_lgt,0) as sum_lgt, coalesce(sz.sum_subs,0) as sum_subs,
(adr.adr||' '||
        (coalesce('буд.'||(a.addr).house||'','')||
		coalesce('/'||(a.addr).slash||' ','')|| 
			coalesce(' корп.'||(a.addr).korp||'','')||
				coalesce(', кв. '||(a.addr).flat,'')||
					coalesce('/'||(a.addr).f_slash,''))::varchar
    )::varchar as addr    
 from 
 rep_zvit_tmpbill_tbl as ss2
 join clm_paccnt_tmp as a on (a.id = ss2.id_paccnt)
 join clm_abon_tbl as c on (c.id = a.id_abon) 
 -- join aqi_grptar_tbl as t on (t.id = a.id_gtar)
 join aqi_grptar_tbl as t on (t.id = ss2.id_grptar)
 join adt_addr_tbl as adr on (adr.id = (a.addr).id_class)
 $left_l join 
  (
  select z.id_paccnt, z.id_grptar, sum(z.summ_lgt) as sum_lgt, sum(gg.name||';')::varchar as lgt_name
  from rep_zvit_lgt_tbl as z 
  join lgi_group_tbl as gg on (gg.id = z.id_grp_lgt)
  join aqi_grptar_tbl as t on (t.id = z.id_grptar) 
  join aqm_tarif_tbl as tm on (tm.id = z.id_tarif)
  where 
      -- tm.ident in ('tgr7_15','tgr7_25','tgr7_35','tgr7_53','tgr7_63') and   
       $where_ident
  group by z.id_paccnt, z.id_grptar order by z.id_paccnt, z.id_grptar
) as lz on (lz.id_paccnt = ss2.id_paccnt and lz.id_grptar = t.id)
 $left_s join 
  (
  select z.id_paccnt, z.id_grptar, sum(z.summ_subs+z.summ_resubs) as sum_subs
  from rep_zvit_subs_tbl as z 
  join aqi_grptar_tbl as t on (t.id = z.id_grptar) 
  join aqm_tarif_tbl as tm on (tm.id = z.id_tarif)
  where 
      -- tm.ident in ('tgr7_15','tgr7_25','tgr7_35','tgr7_53','tgr7_63') and       
       $where_ident
  group by z.id_paccnt, z.id_grptar order by z.id_paccnt, z.id_grptar
) as sz on (sz.id_paccnt = ss2.id_paccnt and sz.id_grptar = t.id )
where t.ident in ('tgr7_15','tgr7_25','tgr7_35','tgr7_53','tgr7_63')  
$where    
order by int_book, int_code; ";
    }    

    // субсидия 
    if ($row_type =='subs_sum')
    {
  
     $SQL = " select a.book, a.code, null as lgt_name,
     ('0'||substring(a.code FROM '[0-9]+'))::int as int_code,
     ('0'||substring(a.book FROM '[0-9]+'))::int as int_book,
     (c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon,  
     z.id_paccnt, z.is_town, 0 as sum_val,0 as sum_lgt, z.summ_subs+z.summ_resubs as sum_subs,
     (adr.adr||' '||
        (coalesce('буд.'||(a.addr).house||'','')||
		coalesce('/'||(a.addr).slash||' ','')|| 
			coalesce(' корп.'||(a.addr).korp||'','')||
				coalesce(', кв. '||(a.addr).flat,'')||
					coalesce('/'||(a.addr).f_slash,''))::varchar
    )::varchar as addr    
 from 
 rep_zvit_subs_tbl as z 
 join clm_paccnt_tmp as a on (a.id = z.id_paccnt)
 join clm_abon_tbl as c on   (c.id = a.id_abon) 
 join aqi_grptar_tbl as t on (t.id = z.id_grptar) 
 join aqm_tarif_tbl as tm on (tm.id = z.id_tarif)
 join adt_addr_tbl as adr on (adr.id = (a.addr).id_class)        
 join rep_zvit_pattern_tbl as p on (p.ident = $pattert_ident and p.lim_min = coalesce(tm.lim_min,0) and p.lim_max = coalesce(tm.lim_max,0))        
 where  tm.ident not in ('tgr7_15','tgr7_25','tgr7_35','tgr7_53','tgr7_63') and tm.ident !~'tgr7_6' and
 $where_ident 
 $where_num        
 $where 
  order by int_book, int_code; ";        
        
    }
    
    
    // субсидия многодетным
    if ($row_type =='subs_m')
    {
  
     $SQL = " select a.book, a.code, null as lgt_name,
     ('0'||substring(a.code FROM '[0-9]+'))::int as int_code,
     ('0'||substring(a.book FROM '[0-9]+'))::int as int_book,
     (c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon,  
     z.id_paccnt, z.is_town, 0 as sum_val,0 as sum_lgt, z.summ_subs+z.summ_resubs as sum_subs,
     (adr.adr||' '||
        (coalesce('буд.'||(a.addr).house||'','')||
		coalesce('/'||(a.addr).slash||' ','')|| 
			coalesce(' корп.'||(a.addr).korp||'','')||
				coalesce(', кв. '||(a.addr).flat,'')||
					coalesce('/'||(a.addr).f_slash,''))::varchar
    )::varchar as addr    
 from 
 rep_zvit_subs_tbl as z 
 join clm_paccnt_tmp as a on (a.id = z.id_paccnt)
 join clm_abon_tbl as c on (c.id = a.id_abon) 
 join aqi_grptar_tbl as t on (t.id = z.id_grptar) 
 join aqm_tarif_tbl as tm on (tm.id = z.id_tarif)
 join adt_addr_tbl as adr on (adr.id = (a.addr).id_class)        
 where  tm.ident in ('tgr7_15','tgr7_25','tgr7_35','tgr7_53','tgr7_63') and 
 $where_ident 
 $where 
    order by int_book, int_code; ";        
        
    }
    // субсидия негаз
    if ($row_type =='subs_sum_ng')
    {
  
     $SQL = " select a.book, a.code, null as lgt_name,
     ('0'||substring(a.code FROM '[0-9]+'))::int as int_code,
     ('0'||substring(a.book FROM '[0-9]+'))::int as int_book,
     (c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon,  
     z.id_paccnt, z.is_town, 0 as sum_val,0 as sum_lgt, z.summ_subs+z.summ_resubs as sum_subs,
     (adr.adr||' '||
        (coalesce('буд.'||(a.addr).house||'','')||
		coalesce('/'||(a.addr).slash||' ','')|| 
			coalesce(' корп.'||(a.addr).korp||'','')||
				coalesce(', кв. '||(a.addr).flat,'')||
					coalesce('/'||(a.addr).f_slash,''))::varchar
    )::varchar as addr    
 from 
 rep_zvit_subs_tbl as z 
 join clm_paccnt_tmp as a on (a.id = z.id_paccnt)
 join clm_abon_tbl as c on   (c.id = a.id_abon) 
 join aqi_grptar_tbl as t on (t.id = z.id_grptar) 
 join aqm_tarif_tbl as tm on (tm.id = z.id_tarif)
 join adt_addr_tbl as adr on (adr.id = (a.addr).id_class)                
 join rep_zvit_pattern_tbl as p on (p.ident = $pattert_ident and p.lim_min = coalesce(tm.lim_min,0) and p.lim_max = coalesce(tm.lim_max,0))        
 where tm.ident ~'tgr7_6' and 
 $where_ident 
 $where_num        
 $where 
  order by int_book, int_code; ";        
        
    }
    
    //echo $SQL;
    //throw new Exception(json_encode($SQL));
    $sum_all=0;
    $sum_val_all=0;
    $sum_lgt_all=0;
    $sum_subs_all=0;
    
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        $i=0;
        $np=0;        
        while ($row = pg_fetch_array($result)) {

            $np++;
            if ((($np> ($page_num-1)* $page_size )&& ($np<= $page_num* $page_size )) || ($show_pager ==0))
                $print_flag = 1;
            else
                $print_flag = 0;
           
            $abon = htmlspecialchars($row['abon']);
            $addr = htmlspecialchars($row['addr']);
            $lgt = htmlspecialchars($row['lgt_name']);
            //$addr = htmlspecialchars($row['town'].' '.$row['street'].' '.$row['house']);
            $book=$row['book'].'/'.$row['code'];
            //$sum_cnt_all++;
            $sum_val_all+=$row['sum_val'];
            $sum_lgt_all+=$row['sum_lgt'];
            $sum_subs_all+=$row['sum_subs'];
            $sum_all+= $row['sum_val']-$row['sum_lgt'] -$row['sum_subs'];
            
            $sum_val_txt = number_format_ua ($row['sum_val'], 2);
            $sum_lgt_txt = number_format_ua ($row['sum_lgt'], 2);
            $sum_subs_txt = number_format_ua ($row['sum_subs'], 2);
            $sum_txt = number_format_ua ($row['sum_val']-$row['sum_lgt'] -$row['sum_subs'], 2);
            
            echo_html( "
            <tr >
            <td>$np</td>
            <td class='c_t'>$book</td>
            <td>{$abon}</td>
            <td>{$addr}</td>
            <td>{$lgt}</td>
            <td class='c_n'>{$sum_val_txt}</td>
            <td class='c_n'>{$sum_lgt_txt}</td>
            <td class='c_n'>{$sum_subs_txt}</td>
            <td class='c_n'>{$sum_txt}</td>
            </tr>  ",$print_flag);        
            $i++;
        }
        
    }

    $sum_val_txt = number_format_ua ($sum_val_all, 2);
    $sum_lgt_txt = number_format_ua ($sum_lgt_all, 2);
    $sum_subs_txt = number_format_ua ($sum_subs_all, 2);
    $sum_txt = number_format_ua ($sum_all, 2);
    
    eval("\$footer_text_eval = \"$footer_text\";");
    echo_html( $footer_text_eval,$print_flag);

    echo '</tbody> </table> ';
    //return;
    
}

//-----------------------------------------------------------------------------
if ($oper=='warning_post')
{
    $p_mmgg = sql_field_val('dt_b', 'mmgg');
    $mmgg1_str = trim($_POST['period_str']);
    
    $p_id_town = sql_field_val('addr_town', 'int');
    $p_id_sector = sql_field_val('id_sector', 'int');
    $p_book = sql_field_val('book', 'string');
    $p_dt = sql_field_val('dt_rep', 'date');    
    $p_dt_txt = $_POST['dt_rep'];
    $p_id_region = sql_field_val('id_region', 'int');
    
    $print_flag=1;
    
    
    if ((isset($_POST['addr_town_name']))&&($_POST['addr_town_name']!=''))
        $params_caption = 'населений пункт: '. trim($_POST['addr_town_name']);
    else 
        $params_caption ='';
    
    if ((isset($_POST['sector']))&&($_POST['sector']!=''))
        $params_caption.= ' Дільниця: '. trim($_POST['sector']);
    
    $where = ' ';

    if ($p_id_town!='null')
    {
        $where.= "and adr.id_town = $p_id_town ";
    }
    
    if ($p_book!='null')
    {
        $where.= " and acc.book = $p_book ";
        
        $params_caption.= 'книга: '.$p_book;
    }
    
    if ($p_id_sector!='null') {
        $where.= " and r.id_sector = $p_id_sector ";
    }
    
    if ($p_id_region!='null')
    {
        $where.= " and rs.id_region =  $p_id_region ";
        
        //if ($p_id_region==1) $params_caption=" Деснянський район";
        //if ($p_id_region==2) $params_caption=" Новозаводський район";
        $SQL_H="SELECT name from cli_region_tbl where id = $p_id_region ;";
    
        $result = pg_query($Link, $SQL_H) or die("SQL Error: " . pg_last_error($Link) . $SQL_H);
        $row = pg_fetch_array($result);
        
        $params_caption.= $row['name'];        

    }
    
    
    if ($p_dt!='null')
    {
        $where.= " and s.dt_create = $p_dt ";
    }
    
    
    eval("\$header_text_eval = \"$header_text\";");
    echo $header_text_eval;
    
    $SQL = " select * from (select s.*, acc.book, acc.code, 
    adr.town, adr.street, address_print(acc.addr) as house,
    (c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon,
    ('0'||substring(acc.code FROM '[0-9]+'))::int as int_code,
    ('0'||substring(acc.book FROM '[0-9]+'))::int as int_book
from clm_switching_tbl as s
join clm_paccnt_tbl as acc on (acc.id = s.id_paccnt)
join clm_abon_tbl as c on (c.id = acc.id_abon) 
join prs_runner_paccnt as r on (r.id_paccnt = acc.id) 
join prs_runner_sectors as rs on (rs.id = r.id_sector)    
join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class)
where s.action=2 and s.mmgg = $p_mmgg $where
) as sss    
order by int_book, book,  int_code, code;";

   // throw new Exception(json_encode($SQL));
    $sum_cnt_all=0;
    $sum_saldo=0;
    
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        $i=0;
        $np=0;        
        while ($row = pg_fetch_array($result)) {
            
            $np++;
            /*
            if ((($np> ($page_num-1)* $page_size )&& ($np<= $page_num* $page_size )) || ($show_pager ==0))
                $print_flag = 1;
            else
                $print_flag = 0;
           */
            
            $abon = htmlspecialchars($row['abon']);
            $addr = htmlspecialchars($row['town'].' '.$row['street'].' '.$row['house']);
            
            $abon = str_replace('і','i',$abon);
            $abon = str_replace('І','I',$abon);

            $addr = str_replace('і','i',$addr);
            $addr = str_replace('І','I',$addr);
            
            //$addr = htmlspecialchars($row['street'].' '.$row['house']);
            $book=$row['book'].'/'.$row['code'];
            $sum_cnt_all++;
            $sum_saldo+=$row['sum_warning'];
            $num_warning = $row['doc_num'];
            
            $sum_warning_txt = number_format_ua ($row['sum_warning'], 2);
            
            echo_html( "
            <tr >
            <td>$np</td>
            <td>&nbsp;</td>
            <td>{$addr}</td>                    
            <td>{$abon}</td>
            <td class='c_t'>{$book}</td>
            <td class='c_n'>{$sum_warning_txt}</td>
            </tr>  ",1);        
            $i++;
            
            if($np == 30 )
            {
                $sum_warning_txt = number_format_ua ($sum_saldo, 2);
    
                eval("\$footer_text_eval = \"$footer_text\";");
                echo_html( $footer_text_eval,$print_flag);
                
                $sum_saldo=0;
                $np=0;   
                
                echo $header_text_eval;
                
            }
            
       }

    }

    if ($np!=0 )
    {
      $sum_warning_txt = number_format_ua ($sum_saldo, 2);
    
      eval("\$footer_text_eval = \"$footer_text\";");
      echo_html( $footer_text_eval,$print_flag);

    }
    //return;
    
}

////----------------------------------------------------------------------------

if ($oper=='abon_meter_check')
 {
        
    $p_dt = sql_field_val('dt_rep', 'date');
    $dt_str = $_POST['dt_rep'];
    $p_book = sql_field_val('book', 'string');
    $p_id_town = sql_field_val('addr_town', 'int');
    $p_id_tar = sql_field_val('id_gtar', 'int');    
    $p_id_type_meter = sql_field_val('id_type_meter', 'int');    
    $p_id_sector = sql_field_val('id_sector', 'int');
    $p_id_type_meter_array = sql_field_val('id_type_meter_array', 'array');        
    $p_id_region = sql_field_val('id_region', 'int');
    
    if (isset($_POST['town_detal']))
        $p_town_detal = trim($_POST['town_detal']);
    else 
        $p_town_detal =0;

    if ((isset($_POST['addr_town_name']))&&($_POST['addr_town_name']!=''))
        $params_caption = 'Населений пункт: '. trim($_POST['addr_town_name']);
    else 
        $params_caption ='';

    if ((isset($_POST['gtar']))&&($_POST['gtar']!=''))
        $params_caption .= ' Тарифна група : '. trim($_POST['gtar']);
    
    if ((isset($_POST['type_meter']))&&($_POST['type_meter']!=''))
        $params_caption .= ' Лічильник : '. trim($_POST['type_meter']);
    
    if ((isset($_POST['sector']))&&($_POST['sector']!=''))
        $params_caption.= ' Дiльниця: '. trim($_POST['sector']);

    
    if ($params_caption !='') $params_caption .='<br/>';
        
    $where = ' ';

    if ($p_id_town!='null')
    {
        $where.= "and adr.id_town = $p_id_town ";
    }

    if ($p_id_tar!='null')
    {
        $where.= "and c.id_gtar = $p_id_tar ";
    }
    
    if ($p_book!='null')
    {
        $where.= " and book = $p_book ";
        $params_caption .= " книга : $p_book ";
        
    }
    
    
    if ($p_id_type_meter_array!='null')
    {
        
     $json_str = stripslashes($p_id_type_meter_array); 
     $meter_id_list = json_decode($json_str,true);
    
     $where.= " and m.id_type_meter in ( ";
    
     $i = 0;
     foreach($meter_id_list as $id_meter) {
    
        if ($i>0) $where.=",";
        
        $where.="$id_meter";
        $i++;
     }
     $where.=") ";
        
    }
    else
    {            
      if ($p_id_type_meter!='null')
      {
        $where.= "and m.id_type_meter = $p_id_type_meter ";
      }
    }
    
    
    if ($p_id_sector!='null')
    {
        $where.= "and rp.id_sector = $p_id_sector ";
        
    }
    
    if ($p_id_region!='null')
    {
        
        $where.= " and rs.id_region =  $p_id_region ";
        
        $SQL_H="SELECT name from cli_region_tbl where id = $p_id_region ;";
    
        $result = pg_query($Link, $SQL_H) or die("SQL Error: " . pg_last_error($Link) . $SQL_H);
        $row = pg_fetch_array($result);
        
        $params_caption.= $row['name'];               
    }    
    
    
    if ($p_town_detal ==1)
    {
        $order ='town, int_book, book, int_code,code';
    }
    else
    {
        $order ='int_book, book, int_code,code';
    }
    
    eval("\$header_text = \"$header_text\";");
    
    echo_html($header_text);
    
    
    $SQL = "select c.id,c.code,c.book,  
    adr.town, 
    (adr.street||' '||
        (coalesce('буд.'||(c.addr).house||'','')||
		coalesce('/'||(c.addr).slash||' ','')||  
			coalesce(' корп.'||(c.addr).korp||'','')||
				coalesce(', кв. '||(c.addr).flat,'')||
					coalesce('/'||(c.addr).f_slash,''))::varchar
    )::varchar as addr,
   (a.last_name||' '||coalesce(substr(a.name,1,1),'')||'.'||coalesce(substr(a.patron_name,1,1),''))||'.'::varchar as abon,
    coalesce(a.home_phone,'')||coalesce(','||a.mob_phone,'') as phone,
   tar.sh_nm as tarif, ht.name as k_house, c.heat_area, c.n_subs, m.power,m.num_meter,m.coef_comp,m.carry, m.id_type_meter,
    to_char(m.dt_start, 'DD.MM.YYYY') as dt_start,
    to_char(coalesce(m.dt_control), 'DD.MM.YYYY') as dt_control,
    im.name as type_meter,im.term_control, zzz.zones, 
    to_char(zz.dat_ind, 'DD.MM.YYYY') as last_dat_ind , zz.indic_str,
    CASE WHEN im.phase = 1 THEN '1' WHEN im.phase = 2 THEN '3' END::varchar as phase,
    mp.name as meter_place,rs.name as sector, 
    CASE WHEN c.activ THEN 1 END as activ,
    CASE WHEN c.not_live THEN 1 END as not_live,
    ('0'||substring(c.code FROM '[0-9]+'))::int as int_code,
    ('0'||substring(c.book FROM '[0-9]+'))::int as int_book,
    to_char(date_trunc('quarter',coalesce(m.dt_control,m.dt_start))::date + (text(im.term_control)||' year')::interval , 'DD.MM.YYYY')::varchar as newcontrol 
from 
    clm_paccnt_h as c
    join (select id, max(dt_b) as dt_b from clm_paccnt_h  where dt_b <= $p_dt and coalesce(dt_e,$p_dt) >=$p_dt group by id order by id) as c2 
    on (c.id = c2.id and c.dt_b = c2.dt_b)

    join clm_meterpoint_h as m on (m.id_paccnt = c.id) 
    join (select id, max(dt_b) as dt_b from clm_meterpoint_h  where dt_b <= $p_dt and coalesce(dt_e,$p_dt) >=$p_dt group by id order by id) as m2 
    on (m.id = m2.id and m.dt_b = m2.dt_b)
    join clm_abon_tbl as a on (a.id = c.id_abon) 
   
    join ( select z.id_meter, trim(sum(iz.nm||','),',')::varchar as zones
     from clm_meter_zone_h as z 
     join eqk_zone_tbl as iz on (iz.id = z.id_zone)
     where z.dt_b <= $p_dt and coalesce(z.dt_e,$p_dt) >=$p_dt
      group by z.id_meter order by z.id_meter
    ) as zzz on (zzz.id_meter = m.id)
    join prs_runner_paccnt as rp on (rp.id_paccnt = c.id)
    join prs_runner_sectors as rs on (rp.id_sector = rs.id)
    join eqi_meter_tbl as im on (im.id = m.id_type_meter)
    join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class)
    join aqi_grptar_tbl as tar on (tar.id = c.id_gtar)
    left join cli_house_type_tbl as ht on (ht.id = c.idk_house)
    left join eqk_meter_places_tbl as mp on (mp.id = m.id_extra)
    
    left join ( select id_paccnt, dat_ind, trim(sum(indic||','),',')::varchar as indic_str from 
     (
      select i.id_paccnt, i.id_zone, i.dat_ind, CASE WHEN z.id = 0 THEN (i.value::int)::varchar ELSE z.nm||':'||((i.value::int)::varchar) END::varchar as indic
      from acm_indication_tbl as i 
      join (select id_paccnt, max(dat_ind) as max_dat from acm_indication_tbl 
      group by id_paccnt
      ) as mi
      on (i.id_paccnt = mi.id_paccnt and i.dat_ind = mi.max_dat)
      join eqk_zone_tbl as z on (z.id = i.id_zone)
       order by i.id_zone
     ) as ss
    group by id_paccnt, dat_ind
    ) as zz on (zz.id_paccnt = c.id)    
    
where c.archive=0 -- and m.dt_control is not null 
    and (date_trunc('quarter',coalesce(m.dt_control,m.dt_start))::date + (text(im.term_control)||' year')::interval)::date <= $p_dt
    $where 
order by $order ;";

  
    $cur_town='';
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        $np=0;
        $i=1;
        while ($row = pg_fetch_array($result)) {

            $np++;
            
            if ((($np> ($page_num-1)* $page_size )&& ($np<= $page_num* $page_size )) || ($show_pager ==0))
                $print_flag = 1;
            else
                $print_flag = 0;
                
            if (($row['town']!=$cur_town)&&($p_town_detal==1))
            {
                $cur_town=$row['town'];
                
                $town_str = htmlspecialchars($cur_town);
                echo_html("
                <tr >
                <td COLSPAN=18 class = 'tab_head'>{$town_str}</td>
                </tr>  ",$print_flag);        
                $i=1;
            }
            
            $abon = htmlspecialchars($row['abon']);
            $addr = htmlspecialchars($row['addr']);
            $town = htmlspecialchars($row['town']);
            //$street = htmlspecialchars($row['street']);
            $book=$row['book'].'/'.$row['code'];
            //$power_txt = number_format_ua ($row['power'],1);  
            //$tar = htmlspecialchars($row['tarif']);
            $zon = htmlspecialchars($row['zones']);
            
            $sector = htmlspecialchars($row['sector']);
            
           
            
            echo_html("
            <tr >
            <td>{$i}</td>
            <td class='c_t'>{$book}</td>
            <td>{$abon}</td>
            <td>{$town}</td>
            <td>{$addr}</td>
            <td>{$sector}</td>
            <td class='c_t'>{$row['num_meter']}</td>
            <td class='c_t'>{$row['type_meter']}</td>
            <td class='c_i'>{$row['carry']}</td>
            <td class='c_t'>{$zon}</td>
            <td class='c_i'>{$row['phase']}</td>
            <td class='c_t'>{$row['meter_place']}</td>
            <td >{$row['dt_start']}</td>
            <td >{$row['dt_control']}</td>
            <td class='c_i'>{$row['term_control']}</td>
            <td >{$row['newcontrol']}</td>
            
            <td class='c_t'>{$row['indic_str']}</td>
            <td class='c_t'>{$row['last_dat_ind']}</td>
            
            <td class='c_r'>{$row['activ']}</td>                        
            <td class='c_r'>{$row['not_live']}</td>                        
            <td class='c_t'>{$row['phone']}</td>
            </tr>  ",$print_flag);        
            $i++;
        }
        
    }
    
    eval("\$footer_text_eval = \"$footer_text\";");
    echo_html($footer_text_eval);

    //return;
    
}
//----------------------------------------------------------------------------
if (($oper=='indic_list_all')||($oper=='indic_list_many'))
{
    $p_mmgg = sql_field_val('dt_b', 'mmgg');
    
    $p_dtb = sql_field_val('dt_b', 'date');
    $p_dte = sql_field_val('dt_e', 'date');
    
    $p_dtb_str = trim($_POST['dt_b']);
    $p_dte_str = trim($_POST['dt_e']);

    list($year, $month, $day  ) = split('[/.-]', $p_mmgg);
    $month_str = ukr_month((int)$month,0);

    $p_id_tar = sql_field_val('id_gtar', 'int');        
    

    $p_id_sector = sql_field_val('id_sector', 'int');
    $p_book = sql_field_val('book', 'string');
    $p_id_person = sql_field_val('id_person', 'int');
    $p_id_operation = sql_field_val('id_operation', 'int');
    $p_id_region = sql_field_val('id_region', 'int');
    
    $p_id_town = sql_field_val('addr_town', 'int');
    $p_id_tar = sql_field_val('id_gtar', 'int');    
    $p_id_type_meter = sql_field_val('id_type_meter', 'int');    
    $p_id_type_meter_array = sql_field_val('id_type_meter_array', 'array');    
    $p_value = sql_field_val('sum_value', 'numeric');  
    $p_direction = sql_field_val('sum_direction', 'int');

    $p_id_dod = sql_field_val('id_cntrl', 'int');
    $p_name_dod = sql_field_val('cntrl', 'string');
    
    
    if (isset($_POST['town_detal']))
        $p_town_detal = trim($_POST['town_detal']);
    else 
        $p_town_detal =0;
    
    
    //$where="where b.mmgg = $p_mmgg::date and b.reg_date >= $p_dtb::date and b.reg_date <= $p_dte::date ";
    $SQL="select ((date_trunc('month',$p_dtb::date)=$p_dtb) 
          and (date_trunc('month',$p_dtb::date)=date_trunc('month',$p_dte::date)) 
          and ((date_trunc('month',$p_dte::date)+'1 month - 1 day'::interval)::date=$p_dte))::int as check;";
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    $row = pg_fetch_array($result);
    if ($row['check']==0)
    {
       $where= " where i.dat_ind >= $p_dtb::date and i.dat_ind <= $p_dte::date ";
       $period = " з $p_dtb_str до $p_dte_str";
    }
    else
    {
       $where=" where i.mmgg = $p_mmgg::date  ";
       $period = "за $month_str $year р.";
    }
            
    
    
    if ($p_id_tar!='null')
    {
        $where.= " and c.id_gtar = $p_id_tar ";
    }
    
    $params_caption = '';
    if ((isset($_POST['gtar']))&&($_POST['gtar']!=''))
        $params_caption .= ' Тарифна група : '. trim($_POST['gtar']);
    
    
    if ((isset($_POST['sector']))&&($_POST['sector']!=''))
        $params_caption.= ' Дiльниця: '. trim($_POST['sector']);

    if ((isset($_POST['person']))&&($_POST['person']!=''))
        $params_caption.= ' Оператор: '. trim($_POST['person']);
    
    if ((isset($_POST['addr_town_name']))&&($_POST['addr_town_name']!=''))
        $params_caption.= 'Населений пункт: '. trim($_POST['addr_town_name']);
    
    if ((isset($_POST['type_meter']))&&($_POST['type_meter']!=''))
        $params_caption .= ' Лічильник : '. trim($_POST['type_meter']);

    if ((isset($_POST['id_operation']))&&($_POST['id_operation']!='null'))
    {
        
        $SQL = "select name from cli_indic_type_tbl where id = $p_id_operation ;";
       
        $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
        if ($result) 
        {
          $row = pg_fetch_array($result);
          $operation = $row['name'];
        }        
        $params_caption .= ' Походження показників : '. $operation;
    }
    
    if ($p_book!='null') 
    {
        $where.= " and c.book = $p_book ";
        $params_caption .= " книга : $p_book ";
    }

   if ($p_id_sector!='null')
   {
      $where.= " and  rp.id_sector = $p_id_sector  ";
   }

   if ($p_id_region!='null')
   {
        $where.= " and rs.id_region =  $p_id_region ";        

        $SQL_H="SELECT name from cli_region_tbl where id = $p_id_region ;";
    
        $result = pg_query($Link, $SQL_H) or die("SQL Error: " . pg_last_error($Link) . $SQL_H);
        $row = pg_fetch_array($result);
        
        $params_caption.= $row['name'];               
   }   
   
   
   if ($p_id_person!='null')
   {
      $where.= " and  u1.id_person = $p_id_person  ";
   }

   if ($p_id_operation!='null')
   {
      $where.= " and  i.id_operation = $p_id_operation  ";
   }
   
    if ($p_id_town!='null')
    {
        $where.= "and adr.id_town = $p_id_town ";
    }


    if ($p_id_dod!='null')
    {
        $where.= "and c.id_cntrl = $p_id_dod ";
        $params_caption .= " Додаткова ознака : $p_name_dod ";
    }
    
    
    if($oper=='indic_list_many')
    {
        $where.= "and exists (select count(distinct i2.dat_ind) from acm_indication_tbl as i2 
                  where i2.mmgg = $p_mmgg and i2.id_paccnt = i.id_paccnt having count(distinct i2.dat_ind) >1 ) ";

        $params_caption .= ' що мають декілька показників впродовж місяця ';
    }
    
    if ($p_town_detal==0)
        $order = 'int_book,book,int_code,code, dat_ind , id_zone';
    else
        $order ="town, int_book,book,int_code,code, dat_ind , id_zone ";    
    
    
    if ($p_value!='null')
    {
        if ($p_direction==1) $where.= "and i.value_diff >= $p_value ";
        if ($p_direction==2) $where.= "and i.value_diff <= $p_value ";
        if ($p_direction==3) $where.= "and i.value_diff = $p_value ";
        
        $order = "demand, int_book,book,int_code,code, dat_ind , id_zone";
        
        if ($p_direction==1) $params_caption .= " де споживання більше за  $p_value кВтг";
        if ($p_direction==2) $params_caption .= " де споживання менше за  $p_value кВтг";
        if ($p_direction==3) $params_caption .= " де споживання дорівнює $p_value кВтг";        
    }
    
    
    if ($p_id_type_meter_array!='null')
    {
        
     $json_str = stripslashes($p_id_type_meter_array); 
     $meter_id_list = json_decode($json_str,true);
    
     $where.= " and m.id in ( ";
    
     $i = 0;
     foreach($meter_id_list as $id_meter) {
    
        if ($i>0) $where.=",";
        
        $where.="$id_meter";
        $i++;
     }
     $where.=") ";
        
    }
    else
    {            
      if ($p_id_type_meter!='null')
      {
        $where.= "and m.id = $p_id_type_meter ";
      }
    }
    
    $demand_all    =0;
    $demand_town    =0;
    $i=1;
    $it=0;
    $np=1;
    $print_flag = 1;
    $current_town='';
    $cnt_headers = 0;
    
    eval("\$header_text = \"$header_text\";");
    echo_html( $header_text);
    
    $SQL = "select c.book,c.code,
    to_char(ip.dat_ind, 'DD.MM.YYYY') as dat_prev,
    ip.value::int as value_prev,i.value::int,
    to_char(i.dat_ind, 'DD.MM.YYYY') as dat_ind,
    i.value_diff::int as demand,i.num_eqp,
 ip.value_diff::int as demand_prev,i.id_operation, it.name as indicoper ,i.id_zone, z.nm as zone,
 CASE WHEN i.id_ind is null and i.id_work is null THEN 1 ELSE 0 END as is_manual,
 ph.num_pack, ww.id_work as id_hwork, w.idk_work, pd.indic_real::int,
(ab.last_name||' '||coalesce(ab.name,'')||' '||coalesce(ab.patron_name,''))::varchar as abon,
 adr.town, (adr.street||' '||
        (coalesce('буд.'||(c.addr).house||'','')||
		coalesce('/'||(c.addr).slash||' ','')|| 
			coalesce(' корп.'||(c.addr).korp||'','')||
				coalesce(', кв. '||(c.addr).flat,'')||
					coalesce('/'||(c.addr).f_slash,''))::varchar
    )::varchar as addr,
    regexp_replace(regexp_replace(c.code, '-.*?$', '') , '[^0-9]', '','g')::int as int_code,
    regexp_replace(regexp_replace(c.book, '-.*?$', '') , '[^0-9]', '','g')::int as int_book,
  rs.name as sector, u1.name as user_name
from acm_indication_tbl as i
join clm_paccnt_tbl c on (c.id=i.id_paccnt)
join clm_abon_tbl as ab on (ab.id = c.id_abon) 
join eqi_meter_tbl as m on (m.id = i.id_typemet)
join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class)
left join acm_indication_tbl as ip on (ip.id=i.id_prev)    
 left join ind_pack_data as pd on (i.id_ind = pd.id)
 left join ind_pack_header as ph on (pd.id_pack = ph.id_pack)
 left join clm_work_indications_tbl as ww on (ww.id = i.id_work)
 left join clm_works_tbl as w on (w.id = ww.id_work)
 left join syi_user as u1 on (u1.id = i.id_person)
 left join eqk_zone_tbl as z on (z.id = i.id_zone)
 left join cli_indic_type_tbl as it on (it.id = i.id_operation)
 left join prs_runner_paccnt as rp on (rp.id_paccnt = c.id)
 left join prs_runner_sectors as rs on (rp.id_sector = rs.id) 
$where
 order by $order ;";

    //echo $SQL;
    //throw new Exception(json_encode($SQL));
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        //$i=0;
        while ($row = pg_fetch_array($result)) {

            
            if ((($np> ($page_num-1)* $page_size )&& ($np<= $page_num* $page_size )) || ($show_pager ==0))
                $print_flag = 1;
            else
                $print_flag = 0;
            
            
            if (($current_town!=$row['town'])&&($p_town_detal==1))
            {
                
                if ($current_town!='')
                {

                    $np++;

                    echo_html( "
                    <tr class='table_footer'>
                     <td colspan='8'>Всього по нас.пункту $current_town</td>
                     <td class='c_i'>{$it}</td>       
                     <td class='c_i'>{$demand_town}</td>
                     <td colspan='8'>&nbsp;</td>
                    </tr>  ",$print_flag);        
                    
                    $demand_town =0;
                    $it = 0;
                    
                }
                $current_town=$row['town'];
                $cnt_headers++;
            }            
            
          
            
            $abon = htmlspecialchars($row['abon']);
            
            if($res_code!='310')
              $addr = htmlspecialchars($row['town'].' '.$row['addr']);
            else
              $addr = htmlspecialchars($row['addr']);
            
            $sector = htmlspecialchars($row['sector']);
            $num_pack = htmlspecialchars($row['num_pack']);
            $oper = htmlspecialchars($row['indicoper']);
            $user_name = htmlspecialchars($row['user_name']);
            
            $book=$row['book'];
            $code=$row['code'];

            if ($p_sum_only!=1)    {
            echo_html( "
            <tr >
            <td>{$i}</td>            
            <td class='c_t'>$book</td>
            <td>$code</td>
            <td>$abon</td>
            <td>$addr</td>
            <td class='c_t'>{$row['num_eqp']}</td>
            <td class='c_t'>{$row['zone']}</td>

            <td class='c_i'>{$row['value']}</td>
            <td class='c_t'>{$row['dat_ind']}</td>
            <td class='c_i' style='font-weight: bold;' >{$row['demand']}</td>

            <td class='c_i'>{$row['value_prev']}</td>
            <td class='c_t'>{$row['dat_prev']}</td>
            <td class='c_i'>{$row['demand_prev']}</td>
            
            <td class='c_t'>{$oper}</td>
            <td class='c_t'>{$num_pack}</td>
            <td class='c_t'>{$sector}</td>
            <td class='c_t'>{$user_name}</td>
            <td class='c_i'>{$row['indic_real']}</td>
            </tr>  ",$print_flag); 
            
             $np++;
            }
            $i++;
            $it++;
            
            $demand_all += $row['demand'];
            $demand_town += $row['demand'];
        }
        
    }
    
    if ($current_town!='')
    {
                    echo_html( "
                    <tr class='table_footer'>
                     <td colspan='9'>Всього по нас.пункту $current_town </td>
                     <td class='c_i'>{$demand_town}</td>
                     <td colspan='8'>&nbsp;</td>
                    </tr>  ",$print_flag);        
    }
    $i--;
    eval("\$footer_text_eval = \"$footer_text\";");

    echo_html( $footer_text_eval,$print_flag);
   
    if ($print_flag==0) echo "</tbody> </table> ";
   
}
//----------------------------------------------------------------------------
if ($oper=='subs_files')
{
    $p_mmgg = sql_field_val('dt_b', 'mmgg');
    
    list($year, $month, $day  ) = split('[/.-]', $p_mmgg);
    $month_str = ukr_month((int)$month,0);


    $period = "за $month_str $year р.";

    $p_id_region = sql_field_val('id_region', 'int');
    
   // $where="where i.mmgg = $p_mmgg::date  ";
    $where='';
    $params_caption ='';
    
    if ($p_id_region!='null')
    {
        
        $where.= " and exists (select rs.id from 
            prs_runner_paccnt as rp 
            join prs_runner_sectors as rs on (rs.id = rp.id_sector)
            where rp.id_paccnt = p.id_paccnt and rs.id_region =  $p_id_region ) ";

        $SQL_H="SELECT name from cli_region_tbl where id = $p_id_region ;";
    
        $result = pg_query($Link, $SQL_H) or die("SQL Error: " . pg_last_error($Link) . $SQL_H);
        $row = pg_fetch_array($result);
        
        $params_caption.= $row['name'];               
    }
    
    
    $sum_all    =0;
    $sum_desn   =0;
    $sum_nov    =0;
    $sum_cher   =0;
    $i=1;
    
    $np=0;
    $print_flag = 1;
    
    eval("\$header_text = \"$header_text\";");
    echo_html( $header_text);
    
    $SQL = "select name_file, sum(value) as value, sum(value_desn) as value_desn, 
                    sum(value_now) as value_now, sum(value_cher) as value_cher
    from
    (
	select p.id_paccnt, p.value, p.idk_doc, s.name_file, adr.town, adr.id_town,
        CASE WHEN adr.id_town = 39824 THEN p.value END as value_desn,
        CASE WHEN adr.id_town = 39825 THEN p.value END as value_now,
        CASE WHEN adr.id_town = 43351 THEN p.value END as value_cher

        	from acm_pay_tbl as p 
                join acm_subs_tbl as s on (s.id = p.id_subs)
		join clm_paccnt_tbl as c on (c.id = p.id_paccnt)
		join clm_abon_tbl as a on (a.id = c.id_abon) 
		left join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class)
        	where p.mmgg =  $p_mmgg::date and p.id_pref = 10 
                and p.idk_doc in (110,111,193,194)
                $where
                order by p.id_paccnt
    ) as ss
    group by name_file
    order by name_file;";

    //echo $SQL;
    //throw new Exception(json_encode($SQL));
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        //$i=0;
        while ($row = pg_fetch_array($result)) {

            $np++;
            
            $file = htmlspecialchars($row['name_file']);

            $sum_all_txt = number_format_ua ($row['value'], 2);
            $sum_desn_txt = number_format_ua ($row['value_desn'], 2);   
            $sum_nov_txt = number_format_ua ($row['value_now'], 2);   
            $sum_cher_txt = number_format_ua ($row['value_cher'], 2);   

            
            echo_html( "
            <tr >
            <td class='c_t'>{$file}</td>            
            <td class='c_n'>{$sum_all_txt}</td>
            <td class='c_n'>{$sum_desn_txt}</td>
            <td class='c_n'>{$sum_nov_txt}</td>
            <td class='c_n'>{$sum_cher_txt}</td>
            </tr>  ",1); 
            $i++;
            
            $sum_all    += $row['value'];
            $sum_desn   += $row['value_desn'];
            $sum_nov    += $row['value_now'];
            $sum_cher   += $row['value_cher'];

        }
        
    }

    $sum_all_txt = number_format_ua ($sum_all, 2);
    $sum_desn_txt = number_format_ua ($sum_desn, 2);   
    $sum_nov_txt = number_format_ua ($sum_nov, 2);   
    $sum_cher_txt = number_format_ua ($sum_cher, 2);   
    
    eval("\$footer_text_eval = \"$footer_text\";");

    echo_html( $footer_text_eval,1);
   
    //if ($print_flag==0) echo "</tbody> </table> ";
   
}

//------------------------------------------------------------------------------
if ($oper=='debetor_cnt')
{ 
    $p_mmgg = sql_field_val('dt_b', 'mmgg');
    $mmgg1_str = trim($_POST['period_str']);
    $p_dt = sql_field_val('dt_rep', 'date');
    $p_dt_txt = sql_field_val('dt_rep', 'text'); 
    $p_id_region = sql_field_val('id_region', 'int');
    
    $params_caption = '';

    $where = '';

    $SQL = "select ''''||to_char( value_ident::date  , 'YYYY-MM-DD')||'''' as start_mmgg 
        from syi_sysvars_tbl where ident = 'dt_start';";
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);    
    
    if ($result) {
        $row = pg_fetch_array($result); 
        $start_mmgg=$row['start_mmgg'];
    }
    
    
    $p_id_town = sql_field_val('addr_town', 'int');
    
    if ((isset($_POST['addr_town_name']))&&($_POST['addr_town_name']!=''))
        $params_caption.= 'населений пункт: '. trim($_POST['addr_town_name']);
      
    
    if ($p_id_town!='null')
    {
        $where.=' and ';
        $where.= " adr.id_town = $p_id_town ";
    }
    
    if ($p_id_region!='null')
    {
        $where.= " and ";
        $where.= " exists (select rs.id from 
            prs_runner_paccnt as rp 
            join prs_runner_sectors as rs on (rs.id = rp.id_sector)
            where rp.id_paccnt = acc.id and rs.id_region =  $p_id_region ) ";

        $SQL_H="SELECT name from cli_region_tbl where id = $p_id_region ;";
    
        $result = pg_query($Link, $SQL_H) or die("SQL Error: " . pg_last_error($Link) . $SQL_H);
        $row = pg_fetch_array($result);
        
        $params_caption.= $row['name'];        
       
    }        
    
    eval("\$header_text = \"$header_text\";");
    echo $header_text;
    
    if ($p_mmgg <= $start_mmgg)
    {
    $SQL = "select 
    round(sum(dt)::numeric/1000,2) as debet_sum, 
    count(id_paccnt) as debet_cnt, 
    round(sum (CASE WHEN dt > 100 THEN dt ELSE 0 END)::numeric/1000,2) as debet_sum_100, 
    sum (CASE WHEN dt > 100 THEN 1 ELSE 0 END) as debet_cnt_100,

    round(sum(CASE WHEN coalesce(action,0) = 1 THEN  dt END )::numeric/1000,2) as debet_sum_off, 
    sum(CASE WHEN coalesce(action,0) = 1 THEN 1 ELSE 0 END ) as debet_cnt_off, 
    
    round(sum (CASE WHEN dt > 100 and coalesce(action,0) = 1 THEN dt ELSE 0 END)::numeric/1000,2) as debet_sum_off_100, 
    sum (CASE WHEN dt > 100 and coalesce(action,0) = 1 THEN 1 ELSE 0 END) as debet_cnt_off_100 , 

    round(sum(CASE WHEN coalesce(action,0) = 2 THEN  dt END )::numeric/1000,2) as debet_sum_w, 
    sum(CASE WHEN coalesce(action,0) = 2 THEN 1 ELSE 0 END ) as debet_cnt_w, 
    
    round(sum (CASE WHEN dt > 100 and coalesce(action,0) = 2 THEN dt ELSE 0 END)::numeric/1000,2) as debet_sum_w_100, 
    sum (CASE WHEN dt > 100 and coalesce(action,0) = 2 THEN 1 ELSE 0 END) as debet_cnt_w_100  ,

    round(sum(CASE WHEN dt_warning is not null THEN  dt END )::numeric/1000,2) as debet_sum_ww, 
    sum(CASE WHEN dt_warning is not null THEN 1 ELSE 0 END ) as debet_cnt_ww, 
    
    round(sum (CASE WHEN dt > 100 and dt_warning is not null THEN dt ELSE 0 END)::numeric/1000,2) as debet_sum_ww_100, 
    sum (CASE WHEN dt > 100 and dt_warning is not null THEN 1 ELSE 0 END) as debet_cnt_ww_100  
    
from
(
  select ss.id_paccnt,dt_all,  
  (dt_all - coalesce(p2.pay,0)-  coalesce(bill_corr,0)) as dt , p2.pay, sw.action, sw.dt_action, sw2.dt_warning
   from 
   clm_paccnt_tbl as acc 
   join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class)        
   join acm_saldo_tbl as s on (s.id_paccnt = acc.id and s.mmgg = $p_mmgg and s.id_pref=10 and s.e_val >0 )                
   join
   (
     select id_paccnt, sum(value) as dt_all
     from acm_bill_tbl where id_pref = 10 
      and mmgg = ($start_mmgg::date - '1 month'::interval)::date  
      and mmgg_bill <= ($p_mmgg::date - '2 month'::interval)::date
     group by id_paccnt order by id_paccnt
   ) as ss
   on (ss.id_paccnt = acc.id )
   left join
   (
    select id_paccnt, sum(value) as pay from acm_pay_tbl where 
    mmgg >= $p_mmgg::date and id_pref = 10  and greatest(pay_date,reg_date) <= $p_dt
    group by id_paccnt order by id_paccnt
   ) as p2 on (p2.id_paccnt = ss.id_paccnt )
   left join 
   	(
   	select csw.id_paccnt, csw.dt_action, csw.action 
   		from clm_switching_tbl as csw 
   		join (select id_paccnt, max(dt_action) as maxdt from clm_switching_tbl where action in (1,2) group by id_paccnt) as csdt 
   		on (csw.id_paccnt = csdt.id_paccnt and csw.dt_action = csdt.maxdt) 
   		left join clm_switching_tbl as cswc on (csw.id_paccnt = cswc.id_paccnt and cswc.dt_action >= csw.dt_action and ((cswc.action =3 and csw.action =1) or (cswc.action =4  and csw.action =2) ) ) 
   		where (csw.action =1 or (csw.action =2 and csw.mmgg >= $p_mmgg ) )
                and cswc.id_paccnt is null 
   	) as sw on (sw.id_paccnt = ss.id_paccnt)
   left join 
   	(
   	select csw.id_paccnt, csw.dt_action, csw.action, csw.dt_warning
   		from clm_switching_tbl as csw 
   		join (select id_paccnt, max(dt_action) as maxdt from clm_switching_tbl where action in (1,2) group by id_paccnt) as csdt 
   		on (csw.id_paccnt = csdt.id_paccnt and csw.dt_action = csdt.maxdt) 
   		left join clm_switching_tbl as cswc on (csw.id_paccnt = cswc.id_paccnt and cswc.dt_action >= csw.dt_action and ((cswc.action =3 and csw.action =1) or (cswc.action =4  and csw.action =2) ) ) 
   		where csw.action =2 and csw.dt_warning <$p_dt
                and cswc.id_paccnt is null 
   	) as sw2 on (sw2.id_paccnt = ss.id_paccnt)   
    left join    	
       (
          select id_paccnt,  -sum(value) as bill_corr 
           from acm_bill_tbl as bb where 
           bb.mmgg >= ($p_mmgg::date - '1 month'::interval)  and 
          (bb.mmgg_bill < $p_mmgg::date - '1 month'::interval  or  ( bb.reg_num <>'' and bb.value <0 and coalesce(bb.demand,0) = 0)) and
           bb.id_pref = 10 and bb.idk_doc=220
           group by id_paccnt 
           having sum(value) <0
           order by id_paccnt
       ) as sum_recalc
       on (sum_recalc.id_paccnt = ss.id_paccnt)          
   where (dt_all - coalesce(p2.pay,0)-  coalesce(bill_corr,0)) > 0 
   and dt_all >0  $where
) as s ;";
        
        
    }
    else
    {
    $SQL = "select 
    round(sum(dt)::numeric/1000,2) as debet_sum, 
    count(id_paccnt) as debet_cnt, 
    round(sum (CASE WHEN dt > 100 THEN dt ELSE 0 END)::numeric/1000,2) as debet_sum_100, 
    sum (CASE WHEN dt > 100 THEN 1 ELSE 0 END) as debet_cnt_100,

    round(sum(CASE WHEN coalesce(action,0) = 1 THEN  dt END )::numeric/1000,2) as debet_sum_off, 
    sum(CASE WHEN coalesce(action,0) = 1 THEN 1 ELSE 0 END ) as debet_cnt_off, 
    
    round(sum (CASE WHEN dt > 100 and coalesce(action,0) = 1 THEN dt ELSE 0 END)::numeric/1000,2) as debet_sum_off_100, 
    sum (CASE WHEN dt > 100 and coalesce(action,0) = 1 THEN 1 ELSE 0 END) as debet_cnt_off_100 , 

    round(sum(CASE WHEN coalesce(action,0) = 2 THEN  dt END )::numeric/1000,2) as debet_sum_w, 
    sum(CASE WHEN coalesce(action,0) = 2 THEN 1 ELSE 0 END ) as debet_cnt_w, 
    
    round(sum (CASE WHEN dt > 100 and coalesce(action,0) = 2 THEN dt ELSE 0 END)::numeric/1000,2) as debet_sum_w_100, 
    sum (CASE WHEN dt > 100 and coalesce(action,0) = 2 THEN 1 ELSE 0 END) as debet_cnt_w_100  ,

    round(sum(CASE WHEN dt_warning is not null THEN  dt END )::numeric/1000,2) as debet_sum_ww, 
    sum(CASE WHEN dt_warning is not null THEN 1 ELSE 0 END ) as debet_cnt_ww, 
    
    round(sum (CASE WHEN dt > 100 and dt_warning is not null THEN dt ELSE 0 END)::numeric/1000,2) as debet_sum_ww_100, 
    sum (CASE WHEN dt > 100 and dt_warning is not null THEN 1 ELSE 0 END) as debet_cnt_ww_100  
    
from
(
  select ss.id_paccnt,dt_all,  
  (dt_all - coalesce(p1.pay,0)- coalesce(p2.pay,0)-  coalesce(bill_corr,0)) as dt , p1.pay,p2.pay, sw.action, sw.dt_action, sw2.dt_warning
   from 
   clm_paccnt_tbl as acc 
   join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class)        
   -- join acm_saldo_tbl as s on (s.id_paccnt = acc.id and s.mmgg = $p_mmgg and s.id_pref=10 and s.e_val >0 )                
   join 
   (
    select id_paccnt, sum(b_dtval)-coalesce(sum(b_ktval),0) as dt_all
    from seb_saldo where mmgg = ($p_mmgg::date - '1 month'::interval)::date
    and id_pref = 10 
    group by id_paccnt 

     union        
    select sb.id_paccnt, sum(sb.value) as dt_all
    from acm_bill_tbl as sb 
    join clm_paccnt_tbl as c on (c.id = sb.id_paccnt)
    where sb.id_pref = 10 
    and sb.mmgg = '2017-09-01'::date and c.id_dep = 260
    and sb.mmgg_bill <= ($p_mmgg::date - '2 month'::interval)::date
    and idk_doc = 1000   
    and not exists (select id_paccnt from seb_saldo as ss where ss.mmgg = ($p_mmgg::date - '1 month'::interval)::date and ss.id_pref = 10  and ss.id_paccnt = sb.id_paccnt  )
    group by sb.id_paccnt 
        
    order by id_paccnt
   ) as ss
   on (ss.id_paccnt = acc.id )
   left join
   (
    select id_paccnt, sum(value) as pay from acm_pay_tbl where 
    mmgg = ($p_mmgg::date - '1 month'::interval)::date and id_pref = 10 
    and idk_doc <> 1000 
    group by id_paccnt order by id_paccnt
   ) as p1 on (p1.id_paccnt = ss.id_paccnt )
   left join
   (
    select id_paccnt, sum(value) as pay from acm_pay_tbl where 
    mmgg >= $p_mmgg::date and id_pref = 10  and greatest(pay_date,reg_date) <= $p_dt
    and idk_doc <> 1000         
    group by id_paccnt order by id_paccnt
   ) as p2 on (p2.id_paccnt = ss.id_paccnt )
   left join 
   	(
   	select csw.id_paccnt, csw.dt_action, csw.action 
   		from clm_switching_tbl as csw 
   		join (select id_paccnt, max(dt_action) as maxdt from clm_switching_tbl where action in (1,2) group by id_paccnt) as csdt 
   		on (csw.id_paccnt = csdt.id_paccnt and csw.dt_action = csdt.maxdt) 
   		left join clm_switching_tbl as cswc on (csw.id_paccnt = cswc.id_paccnt and cswc.dt_action >= csw.dt_action and ((cswc.action =3 and csw.action =1) or (cswc.action =4  and csw.action =2) ) ) 
   		where (csw.action =1 or (csw.action =2 and csw.mmgg >= $p_mmgg ) )
                and cswc.id_paccnt is null 
   	) as sw on (sw.id_paccnt = ss.id_paccnt)
   left join 
   	(
   	select csw.id_paccnt, csw.dt_action, csw.action, csw.dt_warning
   		from clm_switching_tbl as csw 
   		join (select id_paccnt, max(dt_action) as maxdt from clm_switching_tbl where action in (1,2) group by id_paccnt) as csdt 
   		on (csw.id_paccnt = csdt.id_paccnt and csw.dt_action = csdt.maxdt) 
   		left join clm_switching_tbl as cswc on (csw.id_paccnt = cswc.id_paccnt and cswc.dt_action >= csw.dt_action and ((cswc.action =3 and csw.action =1) or (cswc.action =4  and csw.action =2) ) ) 
   		where csw.action =2 and csw.dt_warning <$p_dt
                and cswc.id_paccnt is null 
   	) as sw2 on (sw2.id_paccnt = ss.id_paccnt)   
    left join    	
       (
          select id_paccnt,  -sum(value) as bill_corr 
           from acm_bill_tbl as bb where 
           bb.mmgg >= ($p_mmgg::date - '1 month'::interval)  
           and bb.id_pref = 10  
           and bb.idk_doc <> 1000         
           and ((bb.mmgg_bill < ($p_mmgg::date - '1 month'::interval) and bb.idk_doc=220)
               or (bb.idk_doc=200 and value < 0 )
               or ( bb.reg_num <>'' and bb.value <0 and coalesce(bb.demand,0) = 0)                
           )
           group by id_paccnt 
           having sum(value) <0
           order by id_paccnt
       ) as sum_recalc
       on (sum_recalc.id_paccnt = ss.id_paccnt)          
   where (dt_all - coalesce(p1.pay,0)- coalesce(p2.pay,0)-  coalesce(bill_corr,0)) > 0 
   and dt_all >0  $where
) as s ;";
    };
    //echo $p_mmgg;
    //echo $start_mmgg;
    // echo $SQL;
    
    //throw new Exception(json_encode($SQL));
    $debet_sum=0;
    $debet_cnt=0;
    $debet_sum_off=0;
    $debet_cnt_off=0;
    $debet_sum_w=0;
    $debet_cnt_w=0;
    $debet_sum_ww=0;
    $debet_cnt_ww=0;

    $debet_sum_100=0;
    $debet_cnt_100=0;
    $debet_sum_off_100=0;
    $debet_cnt_off_100=0;
    $debet_sum_w_100=0;
    $debet_cnt_w_100=0;
    $debet_sum_ww_100=0;
    $debet_cnt_ww_100=0;
    
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        $i=0;
        while ($row = pg_fetch_array($result)) {

            $debet_sum+=$row['debet_sum'];
            $debet_cnt+=$row['debet_cnt'];
            $debet_sum_off+=$row['debet_sum_off'];
            $debet_cnt_off+=$row['debet_cnt_off'];
            $debet_sum_w+=$row['debet_sum_w'];
            $debet_cnt_w+=$row['debet_cnt_w'];
            $debet_sum_ww+=$row['debet_sum_ww'];
            $debet_cnt_ww+=$row['debet_cnt_ww'];
            
            $debet_sum_100+=$row['debet_sum_100'];
            $debet_cnt_100+=$row['debet_cnt_100'];
            $debet_sum_off_100+=$row['debet_sum_off_100'];
            $debet_cnt_off_100+=$row['debet_cnt_off_100'];
            $debet_sum_w_100+=$row['debet_sum_w_100'];
            $debet_cnt_w_100+=$row['debet_cnt_w_100'];
            $debet_sum_ww_100+=$row['debet_sum_ww_100'];
            $debet_cnt_ww_100+=$row['debet_cnt_ww_100'];
            
        }
    }

    eval("\$footer_text_eval = \"$footer_text\";");
    echo $footer_text_eval;
   
}
//------------------------------------------------------------------------------

if ($oper=='abon_cnt')
{
    $p_mmgg = sql_field_val('dt_b', 'mmgg');
    $mmgg1_str = trim($_POST['period_str']);
    $p_id_region = sql_field_val('id_region', 'int');
    
    $params_caption = '';

    $where = '';

    $p_id_town = sql_field_val('addr_town', 'int');
    
    if ((isset($_POST['addr_town_name']))&&($_POST['addr_town_name']!=''))
        $params_caption.= 'населений пункт: '. trim($_POST['addr_town_name']);
      
    
    if ($p_id_town!='null')
    {
        $where.=' and ';
        $where.= " adr.id_town = $p_id_town ";
    }
    
    if ($p_id_region!='null')
    {
        $SQL_H="SELECT name from cli_region_tbl where id = $p_id_region ;";
    
        $result = pg_query($Link, $SQL_H) or die("SQL Error: " . pg_last_error($Link) . $SQL_H);
        $row = pg_fetch_array($result);
        
        $params_caption.= $row['name'];        
    }     
    
    eval("\$header_text = \"$header_text\";");
    echo $header_text;
    
    $SQL = "select rep_count_fun($p_mmgg, $p_id_town , $p_id_region );";
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);

    $SQL = "select * from rep_count_tbl where mmgg =$p_mmgg  order by num; ";

    
   // throw new Exception(json_encode($SQL));
    
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        $i=0;
        while ($row = pg_fetch_array($result)) {

            
            $caption = htmlspecialchars($row['caption']);
            $sum_txt = number_format_ua ($row['sum_all'], 0);
            $sum_town_txt = number_format_ua ($row['sum_town'], 0);
            $sum_village_txt = number_format_ua ($row['sum_village'], 0);
            
            echo_html( "
            <tr >
            <td class='c_t'>{$caption}</td>            
            <td class='c_i'>{$sum_txt}</td>
            <td class='c_i'>{$sum_town_txt}</td>
            <td class='c_i'>{$sum_village_txt}</td>
            </tr>  ",1); 
            
        }
    }

    eval("\$footer_text_eval = \"$footer_text\";");
    echo $footer_text_eval;
   
}
//------------------------------------------------------------------------------
if ($oper=='lgt_dubl')
{
    $p_mmgg = sql_field_val('dt_b', 'mmgg');
    $p_id_region = sql_field_val('id_region', 'int');
        
    list($year, $month, $day  ) = split('[/.-]', $p_mmgg);
    $month_str = ukr_month((int)$month,0);

    $period = "за $month_str $year р.";
    $p_id_region = sql_field_val('id_region', 'int');
    
    $params_caption ='';
    $i=1;
    
    $np=0;
    $print_flag = 1;
    $where='';
    
    if ($p_id_region!='null')
    {
        
        $where= " and exists (select rs.id from 
            prs_runner_paccnt as rp 
            join prs_runner_sectors as rs on (rs.id = rp.id_sector)
            where rp.id_paccnt = c.id and rs.id_region =  $p_id_region ) "; 
        
        //$where.= " and rs.id_region =  $p_id_region ";

        $SQL_H="SELECT name from cli_region_tbl where id = $p_id_region ;";
    
        $result = pg_query($Link, $SQL_H) or die("SQL Error: " . pg_last_error($Link) . $SQL_H);
        $row = pg_fetch_array($result);
        
        $params_caption.= $row['name'];        
       
    }    
    
    eval("\$header_text = \"$header_text\";");
    echo_html( $header_text);
    
    $SQL = " select ssss.*, c.book, c.code , g.name as lgt_name, lm.*,
    to_char(lm.dt_start, 'DD.MM.YYYY') as dt_start_txt,
    to_char(lm.dt_end, 'DD.MM.YYYY') as dt_end_txt,
 (adr.adr||' '||
   (coalesce('буд.'||(c.addr).house||'','')||
		coalesce('/'||(c.addr).slash||' ','')||
			coalesce(' корп.'||(c.addr).korp||'','')||
				coalesce(', кв. '||(c.addr).flat,'')||
					coalesce('/'||(c.addr).f_slash||' ',''))::varchar
)::varchar as addr,
(a.last_name||' '||coalesce(a.name,'')||' '||coalesce(a.patron_name,''))::varchar as abon,
('0'||substring(c.code FROM '[0-9]+'))::int as int_code,
('0'||substring(c.book FROM '[0-9]+'))::int as int_book   
 from 
(
select id_paccnt, count(*), interval_intersect(iii) as inters from
(
select id_paccnt, dt_start, dt_end, start_end(dt_start::timestamptz,coalesce(dt_end,'2060-12-01')::timestamptz) as iii from (
select * from lgm_abon_tbl
where      ((dt_start < ($p_mmgg::date+'1 month'::interval) and (dt_end is null or dt_end >= $p_mmgg ) )
            or 
            tintervalov(tinterval(dt_start::timestamp::abstime,dt_end::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime)))
) as ss
) as sss
group by id_paccnt
having count(*) >1 
) as ssss
join clm_paccnt_tbl as c on (c.id = ssss.id_paccnt)
join clm_abon_tbl as a on (a.id = c.id_abon) 
join adt_addr_tbl as adr on (adr.id = (c.addr).id_class)
join lgm_abon_tbl as lm on (lm.id_paccnt = ssss.id_paccnt)
join lgi_group_tbl as g on (lm.id_grp_lgt = g.id)
where inters is not null
and 
(
     ((lm.dt_start < ($p_mmgg::date+'1 month'::interval) and (dt_end is null or dt_end >= $p_mmgg ) )
            or 
            tintervalov(tinterval(lm.dt_start::timestamp::abstime,lm.dt_end::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime)))
)
$where    
order by int_book, int_code, lm.dt_start, lm.dt_end ;";

    //echo $SQL;
    //throw new Exception(json_encode($SQL));
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        //$i=0;
        while ($row = pg_fetch_array($result)) {

            $np++;
            
            $abon = htmlspecialchars($row['abon']);
            $addr = htmlspecialchars($row['addr']);
            $lgt_name = htmlspecialchars($row['lgt_name']);
            $fio_lgt = htmlspecialchars($row['fio_lgt']);
            
            $book = $row['book'];
            $code = $row['code'];
            
            echo_html( "
            <tr >
            <td class='c_i'>{$i}</td>                                        
            <td class='c_t'>{$book}</td>            
            <td class='c_t'>{$code}</td>
            <td class='c_t'>{$abon}</td>
            <td class='c_t'>{$addr}</td>
            <td class='c_t'>{$lgt_name}</td>
            <td class='c_t'>{$fio_lgt}</td>
            <td class='c_t'>{$row['ident_cod_l']}</td>
            <td class='c_t'>{$row['dt_start_txt']}</td>
            <td class='c_t'>{$row['dt_end_txt']}</td>
            </tr>  ",1); 
            $i++;
            
        }
    }

    eval("\$footer_text_eval = \"$footer_text\";");

    echo_html( $footer_text_eval,1);
   
    //if ($print_flag==0) echo "</tbody> </table> ";
   
}
//-----------------------------------------------------------------------------
if ($oper=='lgt_dubl_inn')
{
    $p_mmgg = sql_field_val('dt_b', 'mmgg');
    $p_id_region = sql_field_val('id_region', 'int');
    
    list($year, $month, $day  ) = split('[/.-]', $p_mmgg);
    $month_str = ukr_month((int)$month,0);

    $period = "за $month_str $year р.";
    $p_id_region = sql_field_val('id_region', 'int');
    
    $params_caption ='';
    $i=1;
    
    $np=0;
    $print_flag = 1;
    $where='';
    if ($p_id_region!='null')
    {
        
        $where= " and exists (select rs.id from 
            prs_runner_paccnt as rp 
            join prs_runner_sectors as rs on (rs.id = rp.id_sector)
            where rp.id_paccnt = c.id and rs.id_region =  $p_id_region ) "; 
        
        //$where.= " and rs.id_region =  $p_id_region ";

        $SQL_H="SELECT name from cli_region_tbl where id = $p_id_region ;";
    
        $result = pg_query($Link, $SQL_H) or die("SQL Error: " . pg_last_error($Link) . $SQL_H);
        $row = pg_fetch_array($result);
        
        $params_caption.= $row['name'];        
       
    }    
    
    
    eval("\$header_text = \"$header_text\";");
    echo_html( $header_text);
    
    $SQL = " select ssss.*, c.book, c.code , g.name as lgt_name, lm.*,
    to_char(lm.dt_start, 'DD.MM.YYYY') as dt_start_txt,
    to_char(lm.dt_end, 'DD.MM.YYYY') as dt_end_txt,
 (adr.adr||' '||
   (coalesce('буд.'||(c.addr).house||'','')||
		coalesce('/'||(c.addr).slash||' ','')||
			coalesce(' корп.'||(c.addr).korp||'','')||
				coalesce(', кв. '||(c.addr).flat,'')||
					coalesce('/'||(c.addr).f_slash||' ',''))::varchar
)::varchar as addr,
(a.last_name||' '||coalesce(a.name,'')||' '||coalesce(a.patron_name,''))::varchar as abon,
('0'||substring(c.code FROM '[0-9]+'))::int as int_code,
('0'||substring(c.book FROM '[0-9]+'))::int as int_book   
 from 
(
select ident_cod_l, count(*), interval_intersect(iii) as inters from
(
select ident_cod_l, dt_start, dt_end, start_end(dt_start::timestamptz,coalesce(dt_end,'2060-12-01')::timestamptz) as iii from (
select * from lgm_abon_tbl as l
join clm_paccnt_tbl as c on (c.id = l.id_paccnt and c.archive = 0)

where  ident_cod_l is not null 
    and ((dt_start < ($p_mmgg::date+'1 month'::interval) and (dt_end is null or dt_end >= $p_mmgg ) )
            or 
            tintervalov(tinterval(dt_start::timestamp::abstime,dt_end::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime)))
) as ss
) as sss
group by ident_cod_l
having count(*) >1 
) as ssss
join lgm_abon_tbl as lm on (lm.ident_cod_l = ssss.ident_cod_l)
join clm_paccnt_tbl as c on (c.id = lm.id_paccnt and c.archive = 0)
join clm_abon_tbl as a on (a.id = c.id_abon) 
join adt_addr_tbl as adr on (adr.id = (c.addr).id_class)
join lgi_group_tbl as g on (lm.id_grp_lgt = g.id)
where inters is not null 
and 
(
     ((lm.dt_start < ($p_mmgg::date+'1 month'::interval) and (lm.dt_end is null or lm.dt_end >= $p_mmgg ))
            or 
            tintervalov(tinterval(lm.dt_start::timestamp::abstime,lm.dt_end::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime)))
)
$where    
order by lm.ident_cod_l, int_book, int_code, lm.dt_start, lm.dt_end ;";

    //echo $SQL;
    //throw new Exception(json_encode($SQL));
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        //$i=0;
        while ($row = pg_fetch_array($result)) {

            $np++;
            
            $abon = htmlspecialchars($row['abon']);
            $addr = htmlspecialchars($row['addr']);
            $lgt_name = htmlspecialchars($row['lgt_name']);
            $fio_lgt = htmlspecialchars($row['fio_lgt']);
            
            $book = $row['book'];
            $code = $row['code'];
            
            echo_html( "
            <tr >
            <td class='c_i'>{$i}</td>                    
            <td class='c_t'>{$row['ident_cod_l']}</td>
            <td class='c_t'>{$fio_lgt}</td>            
            <td class='c_t'>{$lgt_name}</td>            
            <td class='c_t'>{$book}</td>            
            <td class='c_t'>{$code}</td>
            <td class='c_t'>{$abon}</td>
            <td class='c_t'>{$addr}</td>
            <td class='c_t'>{$row['dt_start_txt']}</td>
            <td class='c_t'>{$row['dt_end_txt']}</td>
            </tr>  ",1); 
            $i++;
            
        }
    }

    eval("\$footer_text_eval = \"$footer_text\";");

    echo_html( $footer_text_eval,1);
   
    //if ($print_flag==0) echo "</tbody> </table> ";
   
}
//-----------------------------------------------------------------------------

if ($oper=='bill_corr')
{
    $p_mmgg = sql_field_val('dt_b', 'mmgg');
    
    list($year, $month, $day  ) = split('[/.-]', $p_mmgg);
    $month_str = ukr_month((int)$month,0);

    $p_id_tar = sql_field_val('id_gtar', 'int');        
    $period = "за $month_str $year р.";

    $p_id_sector = sql_field_val('id_sector', 'int');
    $p_book = sql_field_val('book', 'string');
    $p_id_region = sql_field_val('id_region', 'int');    

    $p_id_person = sql_field_val('id_person', 'int');

    $p_id_town = sql_field_val('addr_town', 'int');

   // $p_id_type_meter = sql_field_val('id_type_meter', 'int');    
   // $p_id_type_meter_array = sql_field_val('id_type_meter_array', 'array');    

    
    //$where="where b.mmgg = $p_mmgg::date and b.reg_date >= $p_dtb::date and b.reg_date <= $p_dte::date ";
    $where="";
    
    if ($p_id_tar!='null')
    {
        $where.= " and c.id_gtar = $p_id_tar ";
    }
    
    $params_caption = '';
    if ((isset($_POST['gtar']))&&($_POST['gtar']!=''))
        $params_caption .= ' Тарифна група : '. trim($_POST['gtar']);
    
    
    if ((isset($_POST['sector']))&&($_POST['sector']!=''))
        $params_caption.= ' Дiльниця: '. trim($_POST['sector']);

    if ((isset($_POST['person']))&&($_POST['person']!=''))
        $params_caption.= ' Оператор: '. trim($_POST['person']);
    
    if ((isset($_POST['addr_town_name']))&&($_POST['addr_town_name']!=''))
        $params_caption.= 'Населений пункт: '. trim($_POST['addr_town_name']);
    
   // if ((isset($_POST['type_meter']))&&($_POST['type_meter']!=''))
   //     $params_caption .= ' Лічильник : '. trim($_POST['type_meter']);

    if ($p_book!='null') 
    {
        $where.= " and c.book = $p_book ";
        $params_caption .= " книга : $p_book ";
    }

   if ($p_id_sector!='null')
   {
       $where.= "and exists (select id from prs_runner_paccnt as rp 
            where rp.id_paccnt = c.id and rp.id_sector = $p_id_sector ) ";
   }

   if ($p_id_region!='null')
   {
        
        $where.= "and exists (select rs.id from 
            prs_runner_paccnt as rp 
            join prs_runner_sectors as rs on (rs.id = rp.id_sector)
            where rp.id_paccnt = c.id and rs.id_region =  $p_id_region ) ";

        $SQL_H="SELECT name from cli_region_tbl where id = $p_id_region ;";
    
        $result = pg_query($Link, $SQL_H) or die("SQL Error: " . pg_last_error($Link) . $SQL_H);
        $row = pg_fetch_array($result);
        
        $params_caption.= $row['name'];               
   }
   
   if ($p_id_person!='null')
   {
      $where.= " and  u1.id_person = $p_id_person  ";
   }

  
    if ($p_id_town!='null')
    {
        $where.= "and adr.id_town = $p_id_town ";
    }

  /*  
    if ($p_id_type_meter_array!='null')
    {
        
     $json_str = stripslashes($p_id_type_meter_array); 
     $meter_id_list = json_decode($json_str,true);
    
     $where.= " and m.id_type_meter in ( ";
    
     $i = 0;
     foreach($meter_id_list as $id_meter) {
    
        if ($i>0) $where.=",";
        
        $where.="$id_meter";
        $i++;
     }
     $where.=") ";
        
    }
    else
    {            
      if ($p_id_type_meter!='null')
      {
        $where.= "and m.id_type_meter = $p_id_type_meter ";
      }
    }
   * 
   */
    
    $delta_all    =0;
    $delta_demand =0;
    $i=1;
    
    $np=0;
    $print_flag = 1;
    
    eval("\$header_text = \"$header_text\";");
    echo_html( $header_text);
    
    $SQL = "select c.book,c.code, to_char(b.mmgg_bill, 'MM.YYYY') as mmgg_bill,
    to_char(b.dt, 'DD.MM.YYYY') as dt,
 b.id_paccnt, b.value, b.demand, b.value_old, b.demand_old, 
 coalesce(b.value,0) - coalesce(b.value_old,0) as value_delta,
 (ab.last_name||' '||coalesce(ab.name,'')||' '||coalesce(ab.patron_name,''))::varchar as abon,
 adr.town, (adr.street||' '||
        (coalesce('буд.'||(c.addr).house||'','')||
		coalesce('/'||(c.addr).slash||' ','')|| 
			coalesce(' корп.'||(c.addr).korp||'','')||
				coalesce(', кв. '||(c.addr).flat,'')||
					coalesce('/'||(c.addr).f_slash,''))::varchar
    )::varchar as addr,
    ('0'||substring(c.code FROM '[0-9]+'))::int as int_code,
    ('0'||substring(c.book FROM '[0-9]+'))::int as int_book ,  
    u1.name as user_name

from 

( 

    select b.id_paccnt, b.id_person, b.mmgg, b.mmgg_bill, b.dt, b.value, b.demand, 
    bc.value as value_old, bc.demand as demand_old    
    from
    acm_bill_tbl as b 
    left join acm_bill_tbl as bc on (bc.id_doc  = b.id_corr_doc)        
  where b.id_pref = 10
  and b.mmgg = $p_mmgg
  and (( b.id_corr_doc is not null and b.value >0 ) or (b.id_corr_doc is null and b.idk_doc = 220 ))
    
  union all
    
   select b.id_paccnt, b.id_person, b.mmgg, b.mmgg_bill, b.dt, 0 as value, 0 as demand, 
    bc.value as value_old, bc.demand as demand_old    
    from
    acm_bill_tbl as b 
    join acm_bill_tbl as bc on (bc.id_doc  = b.id_corr_doc)        
    left join acm_bill_tbl as b2 on (bc.id_doc  = b2.id_corr_doc and b2.id_pref = 10 and b2.mmgg = $p_mmgg and b2.value >= 0 ) 
  where b.id_pref = 10
  and b.mmgg = $p_mmgg and b.idk_doc = 220 and b.id_corr_doc is not null
  and b.value < 0  and b2.id_doc is null
  
) as b    
  
join clm_paccnt_tbl c on (c.id=b.id_paccnt)
join clm_abon_tbl as ab on (ab.id = c.id_abon) 
join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class)
left join syi_user as u1 on (u1.id = b.id_person)
where b.mmgg = $p_mmgg and ((coalesce(b.value,0) <> coalesce(b.value_old,0)) or ( coalesce(b.demand,0) <> coalesce(b.demand_old,0) ))
$where     
order by int_book, c.book, int_code, c.code, b.dt  ;";

    //echo $SQL;
    //throw new Exception(json_encode($SQL));
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        //$i=0;
        while ($row = pg_fetch_array($result)) {

            $np++;
            
            if ((($np> ($page_num-1)* $page_size )&& ($np<= $page_num* $page_size )) || ($show_pager ==0))
                $print_flag = 1;
            else
                $print_flag = 0;
            
            $abon = htmlspecialchars($row['abon']);
            
            if($res_code!='310')
              $addr = htmlspecialchars($row['town'].' '.$row['addr']);
            else
              $addr = htmlspecialchars($row['addr']);            
            
            $user_name = htmlspecialchars($row['user_name']);
            
            $book=$row['book'];
            $code=$row['code'];
            
            $value_txt = number_format_ua ($row['value'], 2);
            $value_old_txt = number_format_ua ($row['value_old'], 2);
            $value_delta_txt = number_format_ua ($row['value_delta'], 2);
            
            $demand_txt = number_format_ua ($row['demand'], 0);
            $demand_old_txt = number_format_ua ($row['demand_old'], 0);
            $delta_demand_txt = number_format_ua ($row['demand'] - $row['demand_old'], 0);

            if ($p_sum_only!=1)
            {
           
              echo_html( "
              <tr >
              <td>{$i}</td>            
              <td class='c_t'>$book</td>
              <td>$code</td>
              <td class='c_t'>$addr</td>
              <td class='c_t'>$abon</td>
            
              <td class='c_t'>{$row['mmgg_bill']}</td>
              <td class='c_n'>{$demand_old_txt}</td>
              <td class='c_n'>{$demand_txt}</td>
              <td class='c_n'>{$delta_demand_txt}</td>
              <td class='c_n'>{$value_old_txt}</td>
              <td class='c_n'>{$value_txt}</td>
              <td class='c_n'>{$value_delta_txt}</td>

              <td class='c_t'>{$user_name}</td>
              <td class='c_i'>{$row['dt']}</td>
              </tr>  ",$print_flag); 
            }
            $i++;
            
            $delta_all += $row['value_delta'];
            $delta_demand += $row['demand'] - $row['demand_old'];
            
        }
        
    }

    $delta_all_txt = number_format_ua ($delta_all, 2);
    $delta_demand_txt = number_format_ua ($delta_demand, 0);
    
    eval("\$footer_text_eval = \"$footer_text\";");

    if ($p_sum_only==1) $print_flag=1;
    
    echo_html( $footer_text_eval,$print_flag);
   
    if ($print_flag==0) echo "</tbody> </table> ";
   
}
//------------------------------------------------------------------------------
require 'rep_main_build_second.php';

print ('</body> </html>');
?>
