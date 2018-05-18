<?php

set_time_limit(6000); 

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
 
 if ((isset($_POST['to_xls2']))&&($_POST['to_xls2']==1))
      $to_xls = 1;

 
//------------------------------------------------

if ($to_xls==1) 
{
    header('Content-type: application/vnd.ms-excel; charset=utf-8;');
    //header('Content-disposition: inline; filename="'.$oper.date('_ymd_his', time()).'.xls."');
    header('Content-disposition: attachment; filename="'.$oper.date('_ymd_his', time()).'.xls."');
}
else
    header('Content-type: text/html; charset=utf-8');



require 'abon_en_func.php';
require 'abon_ded_func.php';

$archive_print = sql_field_val('archive_print', 'int');

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
            $result ='0';
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

  $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);    
      
}


error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);

session_name("session_kaa");
session_start();

//error_reporting(1);

$Link = get_database_link($_SESSION);
$session_user = $_SESSION['ses_usr_id'];

session_write_close();

$Query=" select syi_resid_fun()::int as id_res;";
$result = pg_query($Link,$Query) or die("SQL Error: " .pg_last_error($Link) );
$row = pg_fetch_array($result);
$id_res = $row['id_res'];  

$p_id_paccnt = sql_field_val('id_paccnt', 'int');

//echo $p_id_paccnt;

if ($p_id_paccnt=='null')
{
    $SQL = " select id_paccnt from rep_spravka_queue_tmp where id_user = $session_user limit 1;"; 
    $result = pg_query($Link,$SQL) or die("SQL Error: " .pg_last_error($Link) );      
    $row = pg_fetch_array($result);
    if ($row['id_paccnt']!=0)
    {
       $p_id_paccnt = $row['id_paccnt'];  
    }  
    
    //echo $SQL; 
    //echo $p_id_paccnt;
}


if ($p_id_paccnt!='null')
{
    
    $SQL = " select rs.id_region , coalesce(r.code_res,0) as id_res
            from prs_runner_paccnt as rp 
            join prs_runner_sectors as rs on (rs.id = rp.id_sector)
            left join cli_region_tbl as r on (r.id = rs.id_region)
            where rp.id_paccnt = $p_id_paccnt ;"; 

    $result = pg_query($Link,$SQL) or die("SQL Error: " .pg_last_error($Link) );      
    $row = pg_fetch_array($result);
    
    //echo $SQL; 
    //echo $row['id_res'];

    if ($row['id_res']!=0)
    {
       $id_res = $row['id_res'];  
    }  
    
}


//------------------------------------------------------------
$SQL = "select r.*, address_print_full(r.addr,5) as addr_full,
 b.name as ae_bank_name, 
 boss.represent_name as boss_name,    
 buh.represent_name as buh_name,
 sbut.represent_name as sbutboss_name,    
 sprav.represent_name as spravboss_name,   
CASE WHEN warn.represent_name ~ '^(.+)\\\\s(.)\\\\.(.)\\\\.$' THEN 
     substr(trim(warn.represent_name),length(warn.represent_name)-3,4)||' '||substr(trim(warn.represent_name),0,length(warn.represent_name)-4) 
     ELSE warn.represent_name END as warningboss_name, 

 av.fulladr as addr_district_name ,
 p.name as warning_name_post,
 ps.name as sprav_name_post,
 pz.name as sbut_name_post
    from syi_resinfo_tbl as r
    left join bank as b on (b.mfo = r.ae_mfo)
    left join prs_persons as boss on (boss.id = r.id_boss)
    left join prs_persons as buh on (buh.id = r.id_buh) 
    left join prs_persons as sprav on (sprav.id = r.id_spravboss)
    left join prs_persons as sbut on (sbut.id = r.id_sbutboss)
    left join prs_persons as warn on (warn.id = r.id_warningboss)
    left join prs_posts as p on (p.id = warn.id_post)
    left join prs_posts as ps on (ps.id = sprav.id_post)
    left join prs_posts as pz on (pz.id = sbut.id_post)
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
    $spravboss_name =  $row['spravboss_name'];
    $spravboss_id = $row['id_spravboss'];
    $buh_name =  $row['buh_name'];
    $sbutboss_name =  $row['sbutboss_name'];
    $sbutboss_post = $row['sbut_name_post'];
    
    $warningboss_name =  $row['warningboss_name'];
    $warningboss_pos  =  $row['warning_name_post'];
    
    $res_warning_phone =  $row['phone_warning'];
    $res_warning_addr =  $row['warning_addr'];
    
 }

 if ($res_code==310)
 {
     $director_pos = 'Заступник директора';
 }
 else
 {
     $director_pos = $sbutboss_post;
 }

 
 
//-------------------------------------------------------------------

if ((isset($_POST['template_name']))&&($_POST['template_name']!='')) 
{
    $template_name = $_POST['template_name'];
}
else
{    
    $template_name = $oper;
}

//-------------------------------------------

$header_text = file_get_contents("html_templates/".$template_name."_header.htm");
$footer_text = file_get_contents("html_templates/".$template_name."_footer.htm");
 
start_mpage('Справка');
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
    
}    
print('</head> <body > ');

//---------------------------------------------------------------------------

if ($oper=='sprav1')
{
    $p_mmgg = sql_field_val('dt_sp', 'mmgg');
    
    if($archive_print==0)
      $mmgg1_str = trim($_POST['period_str']);
    else
      $mmgg1_str = ukr_date(trim($_POST['period_str']),0,1,'');
        
    $p_dt_b = sql_field_val('dt_b', 'mmgg');
    $p_dt_e = sql_field_val('dt_e', 'mmgg');
    

    $p_id_paccnt = sql_field_val('id_paccnt', 'int');
    
    $p_num_sp = sql_field_val('num_sp', 'int');
    $p_dt_sp = sql_field_val('dt_sp', 'text');

    $p_num_input = sql_field_val('num_input', 'text');
    $p_dt_input = sql_field_val('dt_input', 'text');
    
    $p_dt_sp_date = sql_field_val('dt_sp', 'date');
    $p_dt_input_date = sql_field_val('dt_input', 'date');    
    
    $p_people_count = sql_field_val('people_count', 'int');
    
    if ($p_people_count=='null') $p_people_count_txt="__";
    else $p_people_count_txt="$p_people_count";

    $p_heat_area = sql_field_val('heat_area', 'numeric');
    $p_social_norm = sql_field_val('social_norm', 'int');
    
    $p_id_person = sql_field_val('id_person', 'int');
    $p_person = sql_field_val('person', 'text');
    
    $p_hotw = sql_field_val('hotw', 'int');
    $p_hotw_gas = sql_field_val('hotw_gas', 'int');
    $p_coldw = sql_field_val('coldw', 'int');
    $p_plita = sql_field_val('plita', 'int');
    $p_show_dte = sql_field_val('show_dte', 'int');
    
    $p_protocol = sql_field_val('write_protocol', 'int');
    $p_show_norm = sql_field_val('show_norm', 'int');
    
    $params_caption = '';
    $where = '';
    
    $date = ukr_date($p_dt_sp);
    $dapartment = $res_print_name;
    //$director = 'Чернявський О.О.';
    $director = $sbutboss_name;
    
//===========
    rep_abon_dt($Link, $p_mmgg,  2, 0);
//=========
    
  if ($archive_print==0)
  {
    $SQL = "select coalesce(max(num_sp),0)+1 as next_num  from rep_spravka_tbl
            where date_trunc('year', mmgg::date) = date_trunc('year',$p_mmgg::date ) ;";
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) 
    {
        $row = pg_fetch_array($result);
        if ($row['next_num'] > $p_num_sp)
        {
            $p_num_sp = $row['next_num'];
        }
    }
  }

//---------------------------------------------------------------
    
   
        $SQL = "select c.id, c.code, c.book, 
            adr.town, adr.street, address_print(c.addr) as house,
            coalesce(subs_name,(ab.last_name||' '||coalesce(ab.name,'')||' '||coalesce(ab.patron_name,''))::varchar) as abon,
            lg2.*, n.percent, coalesce(n.norm_min,0) as norm_min, n.norm_one, n.norm_max, tar.ident,c.id_gtar, g.name as lgt_name,
            CASE WHEN lg2.id_grp_lgt is null THEN 'sprav1_nolgt' ELSE 'sprav1_lgt' END ::varchar as template,
            coalesce(d_old.summ,0) as old_sum
            from clm_paccnt_h as c 
            join (select id, max(dt_b) as dt_b from clm_paccnt_h  where 
                ((dt_b < ($p_mmgg::date+'1 month'::interval) and dt_e is null)
                or 
                tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
            ) group by id order by id) as c2 
            on (c.id = c2.id and c.dt_b = c2.dt_b)
            join clm_abon_h as ab on (ab.id = c.id_abon) 
            join (select id, max(dt_b) as dt_b from clm_abon_h  where 
                ((dt_b < ($p_mmgg::date+'1 month'::interval) and dt_e is null)
                or 
                tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
            )
            group by id order by id) as a2 on (ab.id = a2.id and ab.dt_b = a2.dt_b)
            left join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class)
      left join 
        ( select lg.id_paccnt, lg.fio_lgt, lg.ident_cod_l, lg.family_cnt, lg.id_grp_lgt, lg.id_calc 
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
        ) as lg2 on (lg2.id_paccnt = c.id)
        left join aqi_grptar_tbl as tar on (tar.id = c.id_gtar)
        left join lgi_group_tbl as g on (lg2.id_grp_lgt = g.id)
        left join lgi_norm_tbl as  n on (n.id_calc = lg2.id_calc and (n.id_tar_grp = tar.id_lgt_group or n.id_tar_grp is null))
        left join
            ( select id_paccnt, param_value as subs_name 
                from cli_ext_params_tbl where id_paccnt = $p_id_paccnt and id_param = 1 limit 1
            ) as sn on (sn.id_paccnt = c.id)
        left join rep_abon_dt_tbl as d_old on (d_old.id_paccnt = c.id)    
        where c.id = $p_id_paccnt; ";

    
   // throw new Exception(json_encode($SQL));
    
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        $i=0;
        while ($row = pg_fetch_array($result)) {

            
            $abn = htmlspecialchars($row['abon']);
            if ($res_code==310)
              $adr = htmlspecialchars($row['street'].' '.$row['house']);
            else
              $adr = htmlspecialchars($row['town'].', '.$row['street'].' '.$row['house']);
            
            $person_acc = $row['book'].'/'.$row['code'];
            
            $debet_2month_txt = number_format_ua($row['old_sum'],2);
            if (($debet_2month_txt =='0')||($debet_2month_txt ==''))
            {
                $debet_2month_txt='немає';
            }
            else
            {
                $debet_2month_txt.=' грн.';
            }
            
            $pilga = htmlspecialchars($row['lgt_name']);
            $perc = $row['percent'];
            $numb_lgperson = $row['family_cnt'];
            //$norma = $row['norm_min'];
           
            if ($numb_lgperson==1)
            {
                $norma = $row['norm_min'];
            }    
            else
            {
                $norma = $row['norm_min'] +($numb_lgperson-1)*$row['norm_one'];
                if ($norma>$row['norm_max']) $norma=$row['norm_max'];
            }
            
            $id_gtar = $row['id_gtar'];
            
            $executor = $p_person;
            
            $in_stock ='';
            $absent ='';
            
            $base_norm = $row['norm_min'];
            
            if (($base_norm!=0)&&($base_norm!=30))
            {
                if ($base_norm==130)
                {
                    $p_plita=1;
                    $p_hotw=1;
                    $p_coldw=1;
                }
                if ($base_norm==150)
                {
                    $p_plita=1;
                    //$p_hotw=0;
                    //$p_coldw=1;
                }
                if ($base_norm==120)
                {
                    //$p_plita=0;
                    //$p_hotw=0;
                    //$p_hotw_gas=0;
                    $p_coldw = 1;
                }

              $man_norm = 30;
              $max_norm = 210;
                
            }
            else
            {
              $base_norm = 90;
              $man_norm = 30;
              $max_norm = 210;
            }
            
            
            if ($p_hotw==1)
            {
               if($in_stock !='' ) $in_stock.=',';
               $in_stock.='централізоване постачання гарячої води'; 
            }
            else
            {
               if($absent !='' ) $absent.=',';                
               $absent.='централізоване постачання гарячої води'; 
            }

            if ($p_coldw==1)
            {
               if($in_stock !='' ) $in_stock.=',';
               $in_stock.='централізоване постачання холодної води'; 
            }
            else
            {
               if($absent !='' ) $absent.=',';
               $absent.='централізоване постачання холодної води'; 
            }
                
            if ($p_hotw_gas==1)
            {
               if($in_stock !='' ) $in_stock.=','; 
               $in_stock.='газові водонагрівальні прилади'; 
            }
            else
            {
               if($absent !='' ) $absent.=','; 
               $absent.='газові водонагрівальні прилади'; 
            }
            
            
            if (($id_gtar ==3)||($id_gtar ==5)||($id_gtar ==7)||($id_gtar ==12)||($id_gtar ==15))
            {
                $p_plita=1;
            }
            
            if ($p_plita==1)
            {
               if($in_stock !='' ) $in_stock.=','; 
               $in_stock.='стаціонарна електроплита'; 
            }
            else
            {
               if($absent !='' ) $absent.=','; 
               $absent.='стаціонарна електроплита'; 
            }
            
            $template_name = $row['template']; 
            
            
            if (($p_plita==1)&&($p_hotw==1))
            {
                $base_norm = 130;
                $max_norm = 250;
            }
            if (($p_plita==1)&&($p_hotw!=1))
            {
                $base_norm = 150;
                $max_norm = 270;
            }
            if (($p_plita!=1)&&($p_hotw!=1)&&($p_hotw_gas!=1)&&($p_coldw==1))
            {
                $base_norm = 120;
                $max_norm = 240;
            }
            $rozrahunok = '';
            $rozrahunok_txt= '';
            $rozrahunok_sum_txt= '';
            $calc_norm=0;
            
            if (($p_people_count!='null')&&($p_people_count!=0))
            {    
            
              if ($p_people_count==1)
              {
                  $rozrahunok = "електроенергія - {$base_norm} кВт/год";
                  $calc_norm = $base_norm;
              }    
              else
              {
                  $calc_norm = $base_norm +($p_people_count-1)*$man_norm;
                  $p_people_count_1 = $p_people_count-1;
                
                  if ($calc_norm<=$max_norm)
                  {
                      $rozrahunok = "електроенергія - {$base_norm} кВт/год + {$p_people_count_1} чол. * {$man_norm} кВт/год = {$calc_norm} кВт/год";
                  }
                  else
                  {
                      $rozrahunok = "електроенергія - {$base_norm} кВт/год + {$p_people_count_1} чол. * {$man_norm} кВт/год = {$calc_norm} кВт/год, але не більше {$max_norm} ";                    
                  }
              }
              
              if (($p_show_norm==1)&&($p_social_norm=='null'))
                 $rozrahunok_txt = "<span class='rows' style='width:630px'>Розрахунок в межах соціальних нормативів за місяць: </span><span class='rows' style='width:610px; margin-left:20px; '>{$rozrahunok}</span>";
                 
            }
        }
           
        $heat_area_txt='';
        if (($p_heat_area!='null')&&($p_heat_area!='')&&($p_heat_area!='0'))
        {
            $heat_area_txt = "<span class='rows' style='width:630px'>Опалювальна площа : {$p_heat_area} м2</span>";            
        }
        
        //----------------------------------
        $SQL_z = "select max(id_zone) as max_zone
          from clm_meterpoint_tbl as m join 
          clm_meter_zone_tbl as z on (z.id_meter = m.id)
          where m.id_paccnt = $p_id_paccnt;";
        $max_zone = 0;
       
        $result_z = pg_query($Link, $SQL_z) or die("SQL Error: " . pg_last_error($Link) . $SQL_z);
        if ($result_z) 
        {
          $row_z = pg_fetch_array($result_z);
          $max_zone = $row_z['max_zone'];
        }
        
        $result_z = pg_query($Link, 'select koef from eqk_zone_tbl where id = 6;');
        $row_z = pg_fetch_array($result_z);
        $k31 = $row_z['koef'];

        $result_z = pg_query($Link, 'select koef from eqk_zone_tbl where id = 7;');
        $row_z = pg_fetch_array($result_z);
        $k32 = $row_z['koef'];
        
        $result_z = pg_query($Link, 'select koef from eqk_zone_tbl where id = 8;');
        $row_z = pg_fetch_array($result_z);
        $k33 = $row_z['koef'];
        
        $result_z = pg_query($Link, 'select koef from eqk_zone_tbl where id = 9;');
        $row_z = pg_fetch_array($result_z);
        $k21 = $row_z['koef'];
        
        $result_z = pg_query($Link, 'select koef from eqk_zone_tbl where id = 10;');
        $row_z = pg_fetch_array($result_z);
        $k22 = $row_z['koef'];
        
        $template_suffix='';
        
        if ($max_zone==10) 
        {
            $template_suffix='_2z';
        }
        
        if ($max_zone==8) 
        {
            $template_suffix='_3z';
        }
            
        //    --------------------------
        
        // Репки 07.04.2017 - если тариф многодетные с электроотоплением, показываем 
        // как обычное электроотопление
        if ($id_gtar==6) $id_gtar=4; 
        
        $SQL_tar = "select t.id,t.name,t.lim_min, t.lim_max,
	(select value from aqd_tarif_tbl as d 
		where d.id_tarif = t.id 
		and dt_begin <=$p_mmgg::date order by dt_begin desc limit 1) as tar_val
	from aqm_tarif_tbl as t 
        where ((dt_e is null ) or (dt_e > $p_mmgg::date )) and (dt_b <= $p_mmgg::date)
        and (per_min <= $p_mmgg  and  per_max >= $p_mmgg  or per_min is null )
        and id_grptar = $id_gtar 
        order by ident  
        limit 2;";
  
        //echo $SQL_tar;
        
        $result_tar = pg_query($Link, $SQL_tar) or die("SQL Error: " . pg_last_error($Link) . $SQL);
        if ($result_tar) {
        
          $rows_count_tar = pg_num_rows($result_tar);
        
          $it=0;
          $tar_val1=0;
          $tar_val2=0;
          $tar_dem1=0;
          $tar_dem2=0;
          $tar_lgt_val1 = 0;
          $tar_lgt_val2 = 0;
          
          $tar_val21 = 0;
          $tar_val22 = 0;
          $tar_val23 = 0;
          $tar_lgt_val21 = 0;
          $tar_lgt_val22 = 0;
          $tar_lgt_val23 = 0;

          $tar_val11 = 0;
          $tar_val12 = 0;
          $tar_lgt_val11 = 0;
          $tar_lgt_val12 = 0;
          
          while ($row_tar = pg_fetch_array($result_tar)) {
         
             if ($it == 0 ) 
             {
                 $tar_dem1 = $row_tar['lim_max'];
                 $tar_val1 = round($row_tar['tar_val'],4);
                 $tar_lgt_val1 = round($tar_val1*$perc/100,4);
                 
                 if ($max_zone==10) 
                 {
                   $tar_val11 = round($tar_val1*$k21,4);
                   $tar_val12 = round($tar_val1*$k22,4);
                   
                   $tar_lgt_val11= round($tar_lgt_val1*$k21,4);
                   $tar_lgt_val12= round($tar_lgt_val1*$k22,4);
                 }
                 if ($max_zone==8) 
                 {
                   $tar_val11 = round($tar_val1*$k31,4);
                   $tar_val12 = round($tar_val1*$k32,4);
                   $tar_val13 = round($tar_val1*$k33,4);
                   
                   $tar_lgt_val11= round($tar_lgt_val1*$k31,4);
                   $tar_lgt_val12= round($tar_lgt_val1*$k32,4);
                   $tar_lgt_val13= round($tar_lgt_val1*$k33,4);                   
                 }

             }

             if ($it == 1 ) 
             {
                 $tar_dem2 = $row_tar['lim_max'];
                 $tar_val2 = round($row_tar['tar_val'],4);
                 $tar_lgt_val2 = round($tar_val2*$perc/100,4);

                 if ($max_zone==10) 
                 {
                   $tar_val21 = round($tar_val2*$k21,4);
                   $tar_val22 = round($tar_val2*$k22,4);
                   
                   $tar_lgt_val21= round($tar_lgt_val2*$k21,4);
                   $tar_lgt_val22= round($tar_lgt_val2*$k22,4);
                   
                 }
                 
                 if ($max_zone==8) 
                 {
                   $tar_val21 = round($tar_val2*$k31,4);
                   $tar_val22 = round($tar_val2*$k32,4);
                   $tar_val23 = round($tar_val2*$k33,4);
                   
                   $tar_lgt_val21= round($tar_lgt_val2*$k31,4);
                   $tar_lgt_val22= round($tar_lgt_val2*$k32,4);
                   $tar_lgt_val23= round($tar_lgt_val2*$k33,4);                   
                 }
                 
             }
             
             $it++; 
          }
        }
        $phone = '';
        $SQL_person = "select * from prs_persons where id = $p_id_person ;";
        $result_person = pg_query($Link, $SQL_person) or die("SQL Error: " . pg_last_error($Link) . $SQL);
        if ($result_person) {
        
          while ($row_person = pg_fetch_array($result_person)) {
         
                 $phone = $row_person['phone'];
          }
        }
        
      $header_text = file_get_contents("html_templates/".$template_name.$template_suffix."_header.htm");
      $footer_text = file_get_contents("html_templates/".$template_name."_footer.htm");
  
      $manual_param_info="<span class='rows' style='width:630px;'></span>";
      $manual_param_info_bottom="";
      
      if ($p_social_norm=='null')
      {
        $manual_param_info="
        <span class='rows' style='width:630px;'>Є в наявності: {$in_stock}. </span>
        <span class='rows' style='width:630px; margin-bottom:5px;'>Відсутньо: {$absent}. </span>";
        
        if (($calc_norm!=0)&&($p_show_norm==1)&&($res_code==170))
        {
            
            $social_norm_price =0;
        
            if (($tar_dem1!=0)&&($tar_dem1!='')&&($tar_dem1!='null'))
            {
                if($calc_norm>$tar_dem1)
                {
                    $social_norm_price = round(($tar_dem1*$tar_val1)+($calc_norm-$tar_dem1)*$tar_val2,2);
                    $dem2 = $calc_norm-$tar_dem1;
                    $social_norm_calc = "({$tar_dem1}*{$tar_val1})+{$dem2}*{$tar_val2}";
                }   
                else
                {
                    $social_norm_price = round($calc_norm*$tar_val1,2);
                    $social_norm_calc = " {$calc_norm}*{$tar_val1}";
                }
            }
            else
            {
                $social_norm_price = round($calc_norm*$tar_val1,2);
                $social_norm_calc = " {$calc_norm}*{$tar_val1}";
            }
            
            if ($max_zone==0)
            {
              $social_norm_price = number_format_ua($social_norm_price,2);
              $rozrahunok_txt.="<br/>
              <span class='rows' style='width:630px; margin-bottom:5px; margin-left:20px; '>вартість - {$social_norm_calc} = {$social_norm_price} грн. </span>";
            }
        }
        
      }
      else
      {
        $social_norm_price =0;
        
        if (($tar_dem1!=0)&&($tar_dem1!='')&&($tar_dem1!='null'))
        {
            if($p_social_norm>$tar_dem1)
            {
                $social_norm_price = round(($tar_dem1*$tar_val1)+($p_social_norm-$tar_dem1)*$tar_val2,2);
                $dem2 = $p_social_norm-$tar_dem1;
                $social_norm_calc = "({$tar_dem1}*{$tar_val1})+{$dem2}*{$tar_val2}";
            }   
            else
            {
                $social_norm_price = round($p_social_norm*$tar_val1,2);
                $social_norm_calc = " {$p_social_norm}*{$tar_val1}";
            }
        }
        else
        {
            $social_norm_price = round($p_social_norm*$tar_val1,2);
            $social_norm_calc = " {$p_social_norm}*{$tar_val1}";
        }

        $social_norm_price = number_format_ua($social_norm_price,2);
        $manual_param_info_bottom="
        <span class='rows' style='width:630px;'>Соціальний норматив користування послугами з електроопалення - {$p_social_norm} кВтг. </span> ";
        
        if ($max_zone==0)
        {
          $manual_param_info_bottom.="            
          <span class='rows' style='width:630px; margin-bottom:5px;'>Вартість послуги у межах соціального нормативу користування : <br/>
          {$social_norm_calc} = {$social_norm_price} грн. </span>";
        }
      }
      
      
      
      
        
      eval("\$header_text = \"$header_text\";");
      echo $header_text;

      eval("\$footer_text_eval = \"$footer_text\";");
      echo $footer_text_eval;
      
    }

    if ($p_protocol==1)
    {
            $SQL = " INSERT INTO rep_spravka_tbl(
            id_paccnt, doc_type, num_sp, dt_sp, date_start, date_end, 
            num_input, dt_input, people_count, heat_area, 
            hotw, hotw_gas,  coldw, plita, social_norm, show_norm, id_person)          
            values ($p_id_paccnt, 1, $p_num_sp, $p_dt_sp_date,$p_dt_b,$p_dt_e,
            '{$p_num_input}', $p_dt_input_date, $p_people_count, $p_heat_area, 
            $p_hotw, $p_hotw_gas,$p_coldw, $p_plita,$p_social_norm,$p_show_norm,$p_id_person ); ";
            
            $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);            
            
    }
    
   
}
//------------------------------------------------------------------------------
if ($oper=='sprav6')
{
    $p_mmgg = sql_field_val('dt_sp', 'mmgg');

    if($archive_print==0)
      $mmgg1_str = trim($_POST['period_str']);
    else
      $mmgg1_str = ukr_date(trim($_POST['period_str']),0,1,'');
    
    $p_dt_b = sql_field_val('dt_b', 'mmgg');
    $p_dt_e = sql_field_val('dt_e', 'mmgg');
    
    $p_dt_b_str = ukr_date(sql_field_val('dt_b', 'text'),0,2);
    $p_dt_e_str = ukr_date(sql_field_val('dt_e', 'text'),0,1);

    $p_id_paccnt = sql_field_val('id_paccnt', 'int');
    
    $p_num_sp = sql_field_val('num_sp', 'int');
    $p_dt_sp = sql_field_val('dt_sp', 'text');
    $p_dt_sp_date = sql_field_val('dt_sp', 'date');

    $p_num_input = sql_field_val('num_input', 'text');
    $p_dt_input = sql_field_val('dt_input', 'text');
    $p_dt_input_date = sql_field_val('dt_input', 'date');
    
    $p_people_count = sql_field_val('people_count', 'int');
    $p_heat_area = sql_field_val('heat_area', 'numeric');
    
    $p_id_person = sql_field_val('id_person', 'int');
    $p_person = sql_field_val('person', 'text');
    
    $p_hotw = sql_field_val('hotw', 'int');
    $p_hotw_gas = sql_field_val('hotw_gas', 'int');
    $p_coldw = sql_field_val('coldw', 'int');
    $p_plita = sql_field_val('plita', 'int');
    $p_show_dte = sql_field_val('show_dte', 'int');
    
    $params_caption = '';
    $where = '';
    
    $p_protocol = sql_field_val('write_protocol', 'int');
    
    $date = ukr_date($p_dt_sp);
    $dapartment = $res_print_name ;// 'Міські електричні мережі';
    //$director = 'Чернявський О.О.';
    $director = $sbutboss_name;
//===========
    rep_abon_dt($Link, $p_mmgg,  2, 0);
//=========
  if ($archive_print==0)
  {
    $SQL = "select coalesce(max(num_sp),0)+1 as next_num  from rep_spravka_tbl
            where date_trunc('year', mmgg::date) = date_trunc('year',$p_mmgg::date ) ;";
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) 
    {
        $row = pg_fetch_array($result);
        if ($row['next_num'] > $p_num_sp)
        {
            $p_num_sp = $row['next_num'];
        }
    }
  }    
//-----------------------------------------------------    
   
    $SQL = "select c.id, c.code, c.book,
        adr.town, adr.street, address_print(c.addr) as house,
        coalesce(subs_name,(ab.last_name||' '||coalesce(ab.name,'')||' '||coalesce(ab.patron_name,''))::varchar) as abon,
        lg2.*, n.percent, n.norm_min, n.norm_one, n.norm_max, tar.ident,c.id_gtar, 
        coalesce(g.name,'-') as lgt_name,
        CASE WHEN lg2.id_grp_lgt is not null and lg2.dt_end is null THEN  
          'надається з '|| to_char(lg2.dt_start, 'DD.MM.YYYY')||' р. розмір пільги: '||n.percent::varchar||' % ,'||'<br/> членів родини:'||family_cnt::varchar
        WHEN lg2.id_grp_lgt is not null and lg2.dt_end is not null THEN  
          'надається з '|| to_char(lg2.dt_start, 'DD.MM.YYYY')||' р.'||' по '||to_char(lg2.dt_end, 'DD.MM.YYYY')||', розмір пільги: '||n.percent::varchar||' % ,'||'<br/> членів родини:'||family_cnt::varchar
        ELSE '' END ::varchar as lgt_info,
            CASE WHEN coalesce(c.heat_area,0)>0 THEN 'Опалювальна площа :'||c.heat_area::varchar||' кв.м. <br/>' ELSE '' END ||    
        CASE WHEN c.id_gtar in (3,5,7,12,15) THEN 'Наявність стаціонарної електроплити: так' 
             WHEN c.id_gtar in (4,6,14) THEN 'Наявність стаціонарної електроплити: ' ||
             CASE WHEN (c.addr).id_class = 43508 and (c.addr).house ='15а' THEN 'так' ELSE 'ні' END ||
             '<br/> Є в наявності електроопалення' 
             ELSE 'Наявність стаціонарної електроплити: ні ' END||
             CASE WHEN c.id_gtar in (5,6,8,9,12,13) THEN '<br/>Тариф:багатодітна родина' ELSE '' END::varchar as additional_info,
             coalesce(d_old.summ,0) as old_sum
        from clm_paccnt_h as c 
        join (select id, max(dt_b) as dt_b from clm_paccnt_h  where 
            ((dt_b < ($p_mmgg::date+'1 month'::interval) and dt_e is null)
            or 
            tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
        ) group by id order by id) as c2 
        on (c.id = c2.id and c.dt_b = c2.dt_b)
        join clm_abon_h as ab on (ab.id = c.id_abon) 
        join (select id, max(dt_b) as dt_b from clm_abon_h  where 
            ((dt_b < ($p_mmgg::date+'1 month'::interval) and dt_e is null)
            or 
            tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
        )
        group by id order by id) as a2 on (ab.id = a2.id and ab.dt_b = a2.dt_b)
        left join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class)
  left join 
    ( select lg.id_paccnt, lg.fio_lgt, lg.ident_cod_l, lg.family_cnt, lg.id_grp_lgt, lg.id_calc , lg.dt_start,lg.dt_end
     from lgm_abon_h as lg  join
      (select lg.id_paccnt, max(lg.id_key) as id_key 
       from lgm_abon_h as lg 
        join (select id, max(dt_b) as dt_b from lgm_abon_h  where
            ((dt_b < ($p_dt_e::date+'1 month'::interval) 
             and ( dt_e is null and (dt_end is null or dt_end >= $p_dt_b )  )
            )
            or 
            tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_dt_b::timestamp::abstime,($p_dt_e::date+'1 month - 1 day'::interval)::timestamp::abstime))
        )   group by id order by id) as lg2 
        on (lg.id = lg2.id and lg.dt_b = lg2.dt_b)
      group by lg.id_paccnt
      ) as gg
      on(lg.id_key = gg.id_key)
    ) as lg2 on (lg2.id_paccnt = c.id)
    left join aqi_grptar_tbl as tar on (tar.id = c.id_gtar)
    left join lgi_group_tbl as g on (lg2.id_grp_lgt = g.id)
    left join lgi_norm_tbl as  n on (n.id_calc = lg2.id_calc and (n.id_tar_grp = tar.id_lgt_group or n.id_tar_grp is null))
    left join
            ( select id_paccnt, param_value as subs_name 
                from cli_ext_params_tbl where id_paccnt = $p_id_paccnt and id_param = 1 limit 1
            ) as sn on (sn.id_paccnt = c.id)
    
    left join rep_abon_dt_tbl as d_old on (d_old.id_paccnt = c.id)
    where c.id = $p_id_paccnt; ";

    
   // throw new Exception(json_encode($SQL));
    
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        $i=0;
        while ($row = pg_fetch_array($result)) {

            
            $abn = htmlspecialchars($row['abon']);
            
             if ($res_code==310)
              $adr = htmlspecialchars($row['street'].' '.$row['house']);
            else
              $adr = htmlspecialchars($row['town'].', '.$row['street'].' '.$row['house']);

            $person_acc = $row['book'].'/'.$row['code'];
            
            $pilga = htmlspecialchars($row['lgt_name']);
            $perc = $row['percent'];
            $numb_lgperson = $row['family_cnt'];
            $norma = $row['norm_min'];
            
            $id_gtar = $row['id_gtar'];
            
            $executor = $p_person;
            
            
            $lgt_info = $row['lgt_info']; 
            $additional_info = $row['additional_info']; 
            
            $debet_2month_txt = number_format_ua($row['old_sum'],2);
            if (($debet_2month_txt =='0')||($debet_2month_txt ==''))
            {
                $debet_2month_txt='немає';
            }
            else
            {
                $debet_2month_txt.=' грн.';
            }
                
            
        }
        $table_txt = '';
        $SQL_tar = "
        select *, to_char(mmgg_calend, 'DD.MM.YYYY') as mmgg_txt,
         round((select (select value from aqd_tarif_tbl as d 
		where d.id_tarif = t.id 
		and dt_begin <=mmgg_calend order by dt_begin desc limit 1) as tar_val 
	  from aqm_tarif_tbl as t 
          where ((dt_e is null ) or (dt_e > mmgg_calend::date )) and (dt_b <= mmgg_calend::date)
          and (per_min <= mmgg_calend  and  per_max >= mmgg_calend  or per_min is null )
          and id_grptar = $id_gtar
          order by ident  
          limit 1 offset 0
         )*coalesce(koef,1),4) as calc_tar1,   
         round((select (select value from aqd_tarif_tbl as d 
		where d.id_tarif = t.id 
		and dt_begin <=mmgg_calend order by dt_begin desc limit 1) as tar_val 
	  from aqm_tarif_tbl as t 
          where ((dt_e is null ) or (dt_e > mmgg_calend::date )) and (dt_b <= mmgg_calend::date)
          and (per_min <= mmgg_calend  and  per_max >= mmgg_calend  or per_min is null )
          and id_grptar = $id_gtar
          order by ident  
          limit 1 offset 1
         )*coalesce(koef,1),4) as calc_tar2   
        from 
        (
         select distinct date_trunc('month',c_date)::date as mmgg_calend from calendar
         where c_date>=$p_dt_b and c_date<=$p_dt_e
         order by mmgg_calend
         ) as cl
         left join
         (select b.mmgg_bill, s.id_tarif, t.ident,t.id_grptar,
		   sum(s.demand ) as demand, sum(s.summ ) as summ, 
                   z.id, z.koef, CASE WHEN z.id = 0 THEN ''
                                      WHEN z.id in (6,9) THEN 'ніч'
                                      WHEN z.id = 7 THEN 'напівпік'
                                      WHEN z.id = 8 THEN 'пік'  
                                      WHEN z.id = 10 THEN 'день' END as zone_name,
                   round((select value from aqd_tarif_tbl as d 
	           where d.id_tarif = t.id 
		   and dt_begin <= b.mmgg_bill order by dt_begin desc limit 1)*z.koef,4) as tar_val
    from acm_summ_tbl as s
    join acm_bill_tbl as b on (b.id_doc=s.id_doc)    
    join aqm_tarif_tbl as t on (t.id = s.id_tarif)
    join eqk_zone_tbl as z on (z.id = s.id_zone)        
    where
     b.id_pref = 10
    and b.id_paccnt = $p_id_paccnt
    and b.mmgg_bill >= $p_dt_b and b.mmgg_bill<= $p_dt_e
   -- and not exists (select c.id_doc from acm_bill_tbl as c where c.id_paccnt = b.id_paccnt and c.id_corr_doc = b.id_doc)
    group by b.mmgg_bill, s.id_tarif, t.ident,t.id, t.id_grptar,z.id, z.koef, CASE WHEN z.id = 0 THEN '' ELSE z.nm END
    ) as bb on (bb.mmgg_bill = cl.mmgg_calend)
    order by cl.mmgg_calend, bb.id, bb.ident; ";
       // echo $SQL_tar;
        $result_tar = pg_query($Link, $SQL_tar) or die("SQL Error: " . pg_last_error($Link) . $SQL);
        if ($result_tar) {
        
          $rows_count_tar = pg_num_rows($result_tar);
        
          $it=0;
          $im = 0;
          $demand_all=0;
          $demand_avg=0;
          
          $cur_mmgg = '';
          $calc_tar1=0;
          $calc_tar2=0;
          
          while ($row_tar = pg_fetch_array($result_tar)) {

              $calc_tar1_p = $calc_tar1;
              $calc_tar2_p = $calc_tar2;
              
              $calc_tar1 = $row_tar['calc_tar1'];
              $calc_tar2 = $row_tar['calc_tar2'];

              
              if ($cur_mmgg != $row_tar['mmgg_calend'].' '.$row_tar['zone_name'])
              {
                $im++;  
                if($cur_mmgg != '')
                {
                
                  if ($it<2)  
                  {
                   $table_txt.="
                    <td> $cur_mmgg_txt </td>
                    <td>0</td>
                    <td>{$calc_tar2_p}</td>";
                  }
                  $table_txt.= "</tr>";                  
                }  
                $table_txt.="<tr>";
                $cur_mmgg = $row_tar['mmgg_calend'].' '.$row_tar['zone_name'];
                $it=0;
                
              }
              
              $tar_val = $row_tar['tar_val'];
              
              if ($tar_val=='')
              {
                  if ($it==0) $tar_val= $calc_tar1;   
                  if ($it==1) $tar_val= $calc_tar2;   
              }
              
              $demand_val = $row_tar['demand'];
              $demand_val_txt = number_format_ua($demand_val,0);
              $id_grptar  = $row_tar['id_grptar'];
              $cur_mmgg_txt = ukr_date($row_tar['mmgg_txt'],0,1).' '.$row_tar['zone_name'];
              
              $demand_all+=$demand_val;
              
              $table_txt.="
                <td> $cur_mmgg_txt </td>
                <td> $demand_val_txt </td>
                <td> $tar_val </td> ";        
              
             $it++; 
          }
          
         if($cur_mmgg != '')
         {
           if ($it<2)  
           {
              $table_txt.="
              <td> $cur_mmgg_txt </td>
              <td>0</td>
              <td>{$calc_tar2}</td>";
           }
           $table_txt.= "</tr>";                  
         }  
          
        }
        if ($im!=0)
            $demand_avg = round($demand_all/$im,0);
        else
            $demand_avg = '-';
        
        $phone ='';
        $SQL_person = "select * from prs_persons where id = $p_id_person ;";
        $result_person = pg_query($Link, $SQL_person) or die("SQL Error: " . pg_last_error($Link) . $SQL);
        if ($result_person) {
        
          while ($row_person = pg_fetch_array($result_person)) {
         
                 $phone = $row_person['phone'];
          }
        }
        
        
      eval("\$header_text = \"$header_text\";");
      echo $header_text;

      echo $table_txt;
      
      eval("\$footer_text_eval = \"$footer_text\";");
      echo $footer_text_eval;
      
      
      if ($p_protocol==1)
      {
            $SQL = " INSERT INTO rep_spravka_tbl(
            id_paccnt, doc_type, num_sp, dt_sp, date_start, date_end, 
            num_input, dt_input, people_count, heat_area, hotw, hotw_gas, 
            coldw, plita, id_person)          
            values ($p_id_paccnt, 2, $p_num_sp, $p_dt_sp_date,$p_dt_b,$p_dt_e,
            '{$p_num_input}', $p_dt_input_date, $p_people_count, $p_heat_area, 
            $p_hotw, $p_hotw_gas,$p_coldw, $p_plita,$p_id_person ); ";
            
            $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);            
            
      }
      
    }
   
}
//------------------------------------------------------------------------------
if ($oper=='sprav12')
{
    $p_mmgg = sql_field_val('dt_sp', 'mmgg');
    list($year, $month, $day  ) = split('[/.-]', $p_mmgg);
    
    if($archive_print==0)
      $mmgg1_str = trim($_POST['period_str']);
    else
      $mmgg1_str = ukr_date(trim($_POST['period_str']),0,1,'');
    
    $p_dt_b = sql_field_val('dt_b', 'mmgg');
    $p_dt_e = sql_field_val('dt_e', 'mmgg');
    
    $p_dt_b_str = ukr_date(sql_field_val('dt_b', 'text'),0,2);
    $p_dt_e_str = ukr_date(sql_field_val('dt_e', 'text'),0,1);

    $p_dt_sp_date = sql_field_val('dt_sp', 'date');
    $p_dt_input_date = sql_field_val('dt_input', 'date');    
    
    
    $p_id_paccnt = sql_field_val('id_paccnt', 'int');
    
    $p_num_sp = sql_field_val('num_sp', 'int');
    $p_dt_sp = sql_field_val('dt_sp', 'text');

    $p_num_input = sql_field_val('num_input', 'text');
    $p_dt_input = sql_field_val('dt_input', 'text');
    
    $p_people_count = sql_field_val('people_count', 'int');
    $p_heat_area = sql_field_val('heat_area', 'numeric');
    
    $p_id_person = sql_field_val('id_person', 'int');
    $p_person = sql_field_val('person', 'text');
    
    $p_hotw = sql_field_val('hotw', 'int');
    $p_hotw_gas = sql_field_val('hotw_gas', 'int');
    $p_coldw = sql_field_val('coldw', 'int');
    $p_plita = sql_field_val('plita', 'int');
    $p_show_dte = sql_field_val('show_dte', 'int');
    
    $p_protocol = sql_field_val('write_protocol', 'int');
    
    $params_caption = '';
    $where = '';
    
    $date = ukr_date($p_dt_sp);
    $dapartment = $res_print_name ;// 'Міські електричні мережі';
    //$director = 'Чернявський О.О.';
    $director = $sbutboss_name;
    //$year = '2016';
//===========
  if ($archive_print==0)
  {
    
    $SQL = "select coalesce(max(num_sp),0)+1 as next_num  from rep_spravka_tbl
            where date_trunc('year', mmgg::date) = date_trunc('year',$p_mmgg::date ) ;";
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) 
    {
        $row = pg_fetch_array($result);
        if ($row['next_num'] > $p_num_sp)
        {
            $p_num_sp = $row['next_num'];
        }
    }
  }    
//-----------------------------------------------------  
    
    $SQL = "select c.id, c.code, c.book,
        adr.town, adr.street, address_print(c.addr) as house,
        (ab.last_name||' '||coalesce(ab.name,'')||' '||coalesce(ab.patron_name,''))::varchar as abon,
        lg2.*, n.percent, n.norm_min, n.norm_one, n.norm_max, tar.ident,c.id_gtar, 
        coalesce(g.name,'-') as lgt_name,
        CASE WHEN lg2.id_grp_lgt is not null THEN  
          'надається з '|| to_char(lg2.dt_start, 'DD.MM.YYYY')||' р. розмір пільги: '||n.percent::varchar||' %.'
        ELSE '' END ::varchar as lgt_info
        from clm_paccnt_h as c 
        join (select id, max(dt_b) as dt_b from clm_paccnt_h  where 
            ((dt_b < ($p_mmgg::date+'1 month'::interval) and dt_e is null)
            or 
            tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
        ) group by id order by id) as c2 
        on (c.id = c2.id and c.dt_b = c2.dt_b)
        join clm_abon_h as ab on (ab.id = c.id_abon) 
        join (select id, max(dt_b) as dt_b from clm_abon_h  where 
            ((dt_b < ($p_mmgg::date+'1 month'::interval) and dt_e is null)
            or 
            tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
        )
        group by id order by id) as a2 on (ab.id = a2.id and ab.dt_b = a2.dt_b)
        left join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class)
  left join 
    ( select lg.id_paccnt, lg.fio_lgt, lg.ident_cod_l, lg.family_cnt, lg.id_grp_lgt, lg.id_calc , lg.dt_start
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
    ) as lg2 on (lg2.id_paccnt = c.id)
    left join aqi_grptar_tbl as tar on (tar.id = c.id_gtar)
    left join lgi_group_tbl as g on (lg2.id_grp_lgt = g.id)
    left join lgi_norm_tbl as  n on (n.id_calc = lg2.id_calc and (n.id_tar_grp = tar.id_lgt_group or n.id_tar_grp is null))
    where c.id = $p_id_paccnt; ";

    
   // throw new Exception(json_encode($SQL));
    
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        $i=0;
        while ($row = pg_fetch_array($result)) {

            
            $abn = htmlspecialchars($row['abon']);
            
            if ($res_code==310)
              $adr = htmlspecialchars($row['street'].' '.$row['house']);
            else
              $adr = htmlspecialchars($row['town'].', '.$row['street'].' '.$row['house']);
            
            $person_acc = $row['book'].'/'.$row['code'];
            
            $pilga = htmlspecialchars($row['lgt_name']);
            $perc = $row['percent'];
            $numb_lgperson = $row['family_cnt'];
            $norma = $row['norm_min'];
            
            $id_gtar = $row['id_gtar'];
            
            $executor = $p_person;
           
            
            $lgt_info = $row['lgt_info']; 
            
        }
        $table_txt = '';
        $SQL_tar = " select *, to_char(mmgg_calend, 'DD.MM.YYYY') as mmgg_txt from 
        (
         select distinct date_trunc('month',c_date)::date as mmgg_calend from calendar
         where c_date>=$p_dt_b and c_date<=$p_dt_e
         order by mmgg_calend
         ) as cl
        left join (
         select b.mmgg_bill, sum(demand) as demand 
         from acm_bill_tbl as b 
         where
         b.id_pref = 10
         and b.id_paccnt = $p_id_paccnt
         and b.mmgg_bill >=$p_dt_b and b.mmgg_bill<=$p_dt_e and b.idk_doc in (200,220)
         --    and not exists (select c.id_doc from acm_bill_tbl as c where c.id_paccnt = b.id_paccnt and c.id_corr_doc = b.id_doc)
         group by b.mmgg_bill
        ) as bb on (bb.mmgg_bill = cl.mmgg_calend)
        left join 
        (
         select p.mmgg, sum(p.value) as sum_pay 
         from acm_pay_tbl as p where 
         p.mmgg >=$p_dt_b and p.mmgg<=$p_dt_e
         and id_pref = 10 and idk_doc = 100
         and p.id_paccnt = $p_id_paccnt
         group by p.mmgg
        ) as pp on ( pp.mmgg = cl.mmgg_calend)
        order by  cl.mmgg_calend; ";
  
        $result_tar = pg_query($Link, $SQL_tar) or die("SQL Error: " . pg_last_error($Link) . $SQL);
        if ($result_tar) {
        
          $rows_count_tar = pg_num_rows($result_tar);
        
          $it=0;
          $im = 0;
          $demand_all=0;
          $pay_all=0;
          
          $cur_mmgg = '';
          
          while ($row_tar = pg_fetch_array($result_tar)) {
         
            $im++;  
            
            if ($it == 2)
            {
               if($cur_mmgg != '')
                   $table_txt.= "</tr>";                  
                
               $table_txt.="<tr>"; 
               $it=0;     
                
            }
          
              $demand_val = $row_tar['demand'];
              $sum_pay = $row_tar['sum_pay'];              
              
              $demand_val_txt = number_format_ua($demand_val,0);
              $sum_pay_txt = number_format_ua($sum_pay,2);
              
              $cur_mmgg_txt = ukr_date($row_tar['mmgg_txt'],0,1);
              //$cur_mmgg_txt = $row_tar['mmgg_txt'];
              
              $demand_all+=$demand_val;
              $pay_all+=$sum_pay;
              
              $table_txt.="
                <td> $cur_mmgg_txt </td>
                <td> $demand_val_txt </td>
                <td> $sum_pay_txt </td> ";        
              
             $it++; 
          }
          
         if($cur_mmgg != '')
         {
           if ($it<2)  
           {
              $table_txt.="
              <td></td>
              <td></td>
              <td></td>";
           }
           $table_txt.= "</tr>";                  
         }  
          
        }
  
        $demand_avg = round($demand_all/$im,0);
        
        $SQL_person = "select * from prs_persons where id = $p_id_person ;";
        $result_person = pg_query($Link, $SQL_person) or die("SQL Error: " . pg_last_error($Link) . $SQL);
        if ($result_person) {
        
          while ($row_person = pg_fetch_array($result_person)) {
         
                 $phone = $row_person['phone'];
          }
        }
        
        
      eval("\$header_text = \"$header_text\";");
      echo $header_text;

      echo $table_txt;
      
      eval("\$footer_text_eval = \"$footer_text\";");
      echo $footer_text_eval;
      
    }

    if ($p_protocol==1)
    {
            $SQL = " INSERT INTO rep_spravka_tbl(
            id_paccnt, doc_type, num_sp, dt_sp, date_start, date_end, 
            num_input, dt_input, people_count, heat_area, hotw, hotw_gas, 
            coldw, plita, id_person)          
            values ($p_id_paccnt, 3, $p_num_sp, $p_dt_sp_date,$p_dt_b,$p_dt_e,
            '{$p_num_input}', $p_dt_input_date, $p_people_count, $p_heat_area, 
            $p_hotw, $p_hotw_gas,$p_coldw, $p_plita,$p_id_person ); ";
            
            $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);            
            
    }
    
   
}

//------------------------------------------------------------------------------
if ($oper=='sprav1new')
{
    $p_mmgg = sql_field_val('dt_sp', 'mmgg');

    if($archive_print==0)
      $mmgg1_str = trim($_POST['period_str']);
    else
      $mmgg1_str = ukr_date(trim($_POST['period_str']),0,1,'');

    
    $p_dt_b = sql_field_val('dt_b', 'mmgg');
    $p_dt_e = sql_field_val('dt_e', 'mmgg');
    
    $p_dt_b_str = ukr_date(sql_field_val('dt_b', 'text'),0,2);
    $p_dt_e_str = ukr_date(sql_field_val('dt_e', 'text'),0,1);

    $p_id_paccnt = sql_field_val('id_paccnt', 'int');
    
    $p_num_sp = sql_field_val('num_sp', 'int');
    $p_dt_sp = sql_field_val('dt_sp', 'text');
    $p_dt_sp_date = sql_field_val('dt_sp', 'date');

    $p_num_input = sql_field_val('num_input', 'text');
    $p_dt_input = sql_field_val('dt_input', 'text');
    $p_dt_input_date = sql_field_val('dt_input', 'date');
    
    $p_people_count = sql_field_val('people_count', 'int');
    
    if ($p_people_count=='null') $p_people_count_txt="__";
    else $p_people_count_txt="$p_people_count";
    
    $p_heat_area = sql_field_val('heat_area', 'numeric');
    
    $p_id_person = sql_field_val('id_person', 'int');
    $p_person = sql_field_val('person', 'text');
    
    $p_hotw = sql_field_val('hotw', 'int');
    $p_hotw_gas = sql_field_val('hotw_gas', 'int');
    $p_coldw = sql_field_val('coldw', 'int');
    $p_plita = sql_field_val('plita', 'int');
    $p_show_dte = sql_field_val('show_dte', 'int');
    
    $params_caption = '';
    $where = '';
    
    $p_protocol = sql_field_val('write_protocol', 'int');
    $p_show_norm = sql_field_val('show_norm', 'int');
    
    $date = ukr_date($p_dt_sp);
    $dapartment = $res_print_name ;// 'Міські електричні мережі';
    //$director = 'Чернявський О.О.';
    $director = $sbutboss_name;
//===========
    rep_abon_dt($Link, $p_mmgg,  2, 0);
//=========
  if ($archive_print==0)
  {
    
    $SQL = "select coalesce(max(num_sp),0)+1 as next_num  from rep_spravka_tbl
            where date_trunc('year', mmgg::date) = date_trunc('year',$p_mmgg::date ) ;";
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) 
    {
        $row = pg_fetch_array($result);
        if ($row['next_num'] > $p_num_sp)
        {
            $p_num_sp = $row['next_num'];
        }
    }
  }    
//-----------------------------------------------------    
   
    $SQL = "select c.id, c.code, c.book, coalesce(n_subs,'') as n_subs,
        adr.town, adr.street, address_print(c.addr) as house,
        (ab.last_name||' '||coalesce(ab.name,'')||' '||coalesce(ab.patron_name,''))::varchar as abon,
        lg2.*, n.percent, n.norm_min, n.norm_one, n.norm_max, tar.ident,c.id_gtar, 
        coalesce(g.name,'-') as lgt_name,
        CASE WHEN lg2.id_grp_lgt is not null and lg2.dt_end is null THEN  
          'надається з '|| to_char(lg2.dt_start, 'DD.MM.YYYY')||' р. розмір пільги: '||n.percent::varchar||' % '::varchar
        WHEN lg2.id_grp_lgt is not null and lg2.dt_end is not null THEN  
          'надається з '|| to_char(lg2.dt_start, 'DD.MM.YYYY')||' р.'||' по '||to_char(lg2.dt_end, 'DD.MM.YYYY')||', розмір пільги: '||n.percent::varchar||' % '::varchar
        ELSE '' END ::varchar as lgt_info,
        CASE WHEN c.id_gtar in (3,5,7,12,15) THEN 'Наявність стаціонарної електроплити: так' 
             WHEN c.id_gtar in (4,6,14) THEN 'Наявність стаціонарної електроплити: ні <br/> Є в наявності електроопалення' 
             ELSE 'Наявність стаціонарної електроплити: ні ' END::varchar as additional_info,
        CASE WHEN coalesce(n.norm_min,0)<>0 THEN 'Пільгова норма споживання: '||n.norm_min::varchar||'кВтг' ELSE '' END::varchar as norm_info,
        coalesce(d_old.summ,0) as old_sum
        from clm_paccnt_h as c 
        join (select id, max(dt_b) as dt_b from clm_paccnt_h  where 
            ((dt_b < ($p_mmgg::date+'1 month'::interval) and dt_e is null)
            or 
            tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
        ) group by id order by id) as c2 
        on (c.id = c2.id and c.dt_b = c2.dt_b)
        join clm_abon_h as ab on (ab.id = c.id_abon) 
        join (select id, max(dt_b) as dt_b from clm_abon_h  where 
            ((dt_b < ($p_mmgg::date+'1 month'::interval) and dt_e is null)
            or 
            tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
        )
        group by id order by id) as a2 on (ab.id = a2.id and ab.dt_b = a2.dt_b)
        left join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class)
  left join 
    ( select lg.id_paccnt, lg.fio_lgt, lg.ident_cod_l, lg.family_cnt, lg.id_grp_lgt, lg.id_calc , lg.dt_start,lg.dt_end
     from lgm_abon_h as lg  join
      (select lg.id_paccnt, max(lg.id_key) as id_key 
       from lgm_abon_h as lg 
        join (select id, max(dt_b) as dt_b from lgm_abon_h  where
            ((dt_b < ($p_dt_e::date+'1 month'::interval) 
             and ( dt_e is null and (dt_end is null or dt_end >= $p_dt_b )  )
            )
            or 
            tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_dt_b::timestamp::abstime,($p_dt_e::date+'1 month - 1 day'::interval)::timestamp::abstime))
        )   group by id order by id) as lg2 
        on (lg.id = lg2.id and lg.dt_b = lg2.dt_b)
      group by lg.id_paccnt
      ) as gg
      on(lg.id_key = gg.id_key)
    ) as lg2 on (lg2.id_paccnt = c.id)
    left join aqi_grptar_tbl as tar on (tar.id = c.id_gtar)
    left join lgi_group_tbl as g on (lg2.id_grp_lgt = g.id)
    left join lgi_norm_tbl as  n on (n.id_calc = lg2.id_calc and (n.id_tar_grp = tar.id_lgt_group or n.id_tar_grp is null))
    left join rep_abon_dt_tbl as d_old on (d_old.id_paccnt = c.id)
    where c.id = $p_id_paccnt; ";

   // throw new Exception(json_encode($SQL));
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        $i=0;
        while ($row = pg_fetch_array($result)) {

            
            $abn = htmlspecialchars($row['abon']);

            if ($res_code==310)
              $adr = htmlspecialchars($row['street'].' '.$row['house']);
            else
              $adr = htmlspecialchars($row['town'].', '.$row['street'].' '.$row['house']);
            
            
            if ($row['n_subs']!='')
               $person_acc = $row['book'].'/'.$row['code'].'('.$row['n_subs'].')';
            else
               $person_acc = $row['book'].'/'.$row['code'];
                
            $pilga = htmlspecialchars($row['lgt_name']);
            $perc = $row['percent'];
            $numb_lgperson = $row['family_cnt'];
            $norma = $row['norm_min'];
            
            $id_gtar = $row['id_gtar'];
            
            $executor = $p_person;
            
            $debet_2month_txt = number_format_ua($row['old_sum'],2);
            if (($debet_2month_txt =='0')||($debet_2month_txt ==''))
            {
                $debet_2month_txt='немає';
            }
            else
            {
                $debet_2month_txt.=' грн.';
            }
            
            
            $lgt_info = $row['lgt_info']; 
            $additional_info = $row['additional_info']; 
            
            $norm_info_text='';
            if (($row['norm_info'] !='')&&($p_show_norm==1))
            {
                $norm_info = $row['norm_info'];
                $norm_info_text =  "<span class='rows' style='width:600px'>{$norm_info } </span> <br/>";
            }
            
            
        }
        //-------------------------------------------------------------
        $SQL_z = "select max(id_zone) as max_zone
          from clm_meterpoint_tbl as m join 
          clm_meter_zone_tbl as z on (z.id_meter = m.id)
          where m.id_paccnt = $p_id_paccnt;";
        $max_zone = 0;
        $zone_info='';
       
        $result_z = pg_query($Link, $SQL_z) or die("SQL Error: " . pg_last_error($Link) . $SQL_z);
        if ($result_z) 
        {
          $row_z = pg_fetch_array($result_z);
          $max_zone = $row_z['max_zone'];
          
            if ($max_zone==10) 
            {
                $zone_info  ='Абонент розраховується за двозонними тарифами, диференційованими за періодами часу ';
            }
        
            if ($max_zone==8) 
            {
                $zone_info  ='Абонент розраховується за трьохзонними тарифами, диференційованими за періодами часу ';
            }
          
        }        
        //-------------------------------------------------------------
        
        $SQL_tar = "select t.id,t.name,t.lim_min, t.lim_max,
	(select value from aqd_tarif_tbl as d 
		where d.id_tarif = t.id 
		and dt_begin <=$p_mmgg::date order by dt_begin desc limit 1) as tar_val
	from aqm_tarif_tbl as t 
        where ((dt_e is null ) or (dt_e > $p_mmgg::date )) and (dt_b <= $p_mmgg::date)
        and (per_min <= $p_mmgg  and  per_max >= $p_mmgg  or per_min is null )
        and id_grptar = $id_gtar 
        order by ident  
        limit 2;";
  
        $result_tar = pg_query($Link, $SQL_tar) or die("SQL Error: " . pg_last_error($Link) . $SQL);
        if ($result_tar) {
        
          $rows_count_tar = pg_num_rows($result_tar);
        
          $it=0;
          $tar_val1=0;
          $tar_val2=0;
          $tar_dem1=0;
          $tar_dem2=0;
          
          while ($row_tar = pg_fetch_array($result_tar)) {
         
             if ($it == 0 ) 
             {
                 $tar_dem1 = $row_tar['lim_max'];
                 $tar_val1 = round($row_tar['tar_val'],4);
                 //$tar_lgt_val1 = round($tar_val1*$perc/100,4);
             }

             if ($it == 1 ) 
             {
                 $tar_dem2 = $row_tar['lim_max'];
                 $tar_val2 = round($row_tar['tar_val'],4);
                 //$tar_lgt_val2 = round($tar_val2*$perc/100,4);
             }
             
             $it++; 
          }
        }
        
        $tarif_info = "до $tar_dem1 кВтг – $tar_val1 грн за 1 кВтг,  понад $tar_dem1 кВт – $tar_val2 грн за 1 кВтг "; 
        
        //-------------------------------------------------------------
        
        $phone ='';
        $SQL_person = "select * from prs_persons where id = $p_id_person ;";
        $result_person = pg_query($Link, $SQL_person) or die("SQL Error: " . pg_last_error($Link) . $SQL);
        if ($result_person) {
        
          while ($row_person = pg_fetch_array($result_person)) {
         
                 $phone = $row_person['phone'];
          }
        }
        
        
      eval("\$header_text = \"$header_text\";");
      echo $header_text;

      //echo $table_txt;
      
      eval("\$footer_text_eval = \"$footer_text\";");
      echo $footer_text_eval;
      
      
      if ($p_protocol==1)
      {
            $SQL = " INSERT INTO rep_spravka_tbl(
            id_paccnt, doc_type, num_sp, dt_sp, date_start, date_end, 
            num_input, dt_input, people_count, heat_area, hotw, hotw_gas, 
            coldw, plita, show_norm,  id_person)          
            values ($p_id_paccnt, 4, $p_num_sp, $p_dt_sp_date,$p_dt_b,$p_dt_e,
            '{$p_num_input}', $p_dt_input_date, $p_people_count, $p_heat_area, 
            $p_hotw, $p_hotw_gas,$p_coldw, $p_plita,$p_show_norm, $p_id_person ); ";
            
            $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);            
            
      }
      
    }
   
}
//------------------------------------------------------------------------------

if ($oper=='sprav1_list')
{   
    
    $pp_mmgg = sql_field_val('dt_sp', 'mmgg');
    
    //===========
    rep_abon_dt($Link, $pp_mmgg,  2, 0);
    //=========
    
    
    $SQL = "select coalesce(max(num_sp),0)+1 as next_num  from rep_spravka_tbl
            where date_trunc('year', mmgg::date) = date_trunc('year',$pp_mmgg::date ) ;";
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) 
    {
        $row = pg_fetch_array($result);
        $p_num_sp = $row['next_num'];
    }
    //---------------------------------------------------
    
    $SQL_list = "select q.*, date_trunc('month', q.date_start) as pmmgg,
       to_char(date_trunc('month', q.date_start), 'DD.MM.YYYY') as pmmgg_str,
       date_part('month', q.date_start) as mmgg_month,
       date_part('year', q.date_start) as mmgg_year,
       to_char(q.dt_input, 'DD.MM.YYYY') as dt_input_txt,
       to_char(q.dt_sp, 'DD.MM.YYYY') as dt_sp_txt,
        p.represent_name as person,
        ('0'||substring(c.code FROM '[0-9]+'))::int as int_code,
        ('0'||substring(c.book FROM '[0-9]+'))::int as int_book   
    from rep_spravka_queue_tmp as q
    join clm_paccnt_tbl as c on (c.id = q.id_paccnt)
    left join prs_persons as p on (p.id = q.id_person)
    where id_user = $session_user order by int_book, book, int_code, code ;";
    
    
    $result_list = pg_query($Link, $SQL_list) or die("SQL Error: " . pg_last_error($Link) . $SQL_list);
    if ($result_list) {
        
        while ($row_list = pg_fetch_array($result_list)) {
    
    
    
    $p_mmgg = "'".$row_list['pmmgg']."'";
    $mmgg1_str = ukr_month($row_list['mmgg_month'],0).' '.$row_list['mmgg_year'];
    
    $p_dt_b = "'".$row_list['date_start']."'"; //sql_field_val('dt_b', 'mmgg');
    $p_dt_e = "'".$row_list['date_end']."'"; //sql_field_val('dt_e', 'mmgg');
    
    $p_id_paccnt = $row_list['id_paccnt']; // sql_field_val('id_paccnt', 'int');
    
    //$p_num_sp = sql_field_val('num_sp', 'int');
    $p_dt_sp = $row_list['dt_sp_txt'];// sql_field_val('dt_sp', 'text');

    $p_num_input = $row_list['num_input']; //sql_field_val('num_input', 'text');
    $p_dt_input = $row_list['dt_input_txt']; //sql_field_val('dt_input', 'text');
    
    $p_dt_sp_date = "'".$row_list['dt_sp']."'"; //sql_field_val('dt_sp', 'date');
    $p_dt_input_date = "'".$row_list['dt_input']."'"; //sql_field_val('dt_input', 'date');    
    
    $p_people_count = $row_list['people_count']; // sql_field_val('people_count', 'int');
    
    if (($p_people_count=='null')||($p_people_count=='')) $p_people_count_txt="__";
    else $p_people_count_txt="$p_people_count";

    $p_heat_area = $row_list['heat_area']; //sql_field_val('heat_area', 'numeric');
    $p_social_norm= $row_list['social_norm'];
    
    $p_id_person = $row_list['id_person']; //sql_field_val('id_person', 'int');
    $p_person = $row_list['person']; //sql_field_val('person', 'text');
    
    $p_hotw = $row_list['hotw']; //sql_field_val('hotw', 'int');
    $p_hotw_gas = $row_list['hotw_gas']; //sql_field_val('hotw_gas', 'int');
    $p_coldw = $row_list['coldw']; //sql_field_val('coldw', 'int');
    $p_plita = $row_list['plita']; //sql_field_val('plita', 'int');
    //$p_show_dte = sql_field_val('show_dte', 'int');
    
    $p_protocol = sql_field_val('write_protocol', 'int');
    $p_show_norm = sql_field_val('show_norm', 'int');
    
    $params_caption = '';
    $where = '';
    
    $date = ukr_date($p_dt_sp);
    $dapartment = $res_print_name ;// 'Міські електричні мережі';
    //$director = 'Чернявський О.О.';
    $director = $sbutboss_name;
//---------------------------------------------------------------
    
   
    $SQL = "select c.id, c.code, c.book,
        adr.town, adr.street, address_print(c.addr) as house,
        coalesce(subs_name,(ab.last_name||' '||coalesce(ab.name,'')||' '||coalesce(ab.patron_name,''))::varchar) as abon,
        lg2.*, n.percent, coalesce(n.norm_min,0) as norm_min, n.norm_one, n.norm_max, tar.ident,c.id_gtar, g.name as lgt_name,
        CASE WHEN lg2.id_grp_lgt is null THEN 'sprav1_nolgt' ELSE 'sprav1_lgt' END ::varchar as template,
        coalesce(d_old.summ,0) as old_sum
        from clm_paccnt_h as c 
        join (select id, max(dt_b) as dt_b from clm_paccnt_h  where 
            ((dt_b < ($p_mmgg::date+'1 month'::interval) and dt_e is null)
            or 
            tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
        ) group by id order by id) as c2 
        on (c.id = c2.id and c.dt_b = c2.dt_b)
        join clm_abon_h as ab on (ab.id = c.id_abon) 
        join (select id, max(dt_b) as dt_b from clm_abon_h  where 
            ((dt_b < ($p_mmgg::date+'1 month'::interval) and dt_e is null)
            or 
            tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
        )
        group by id order by id) as a2 on (ab.id = a2.id and ab.dt_b = a2.dt_b)
        left join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class)
  left join 
    ( select lg.id_paccnt, lg.fio_lgt, lg.ident_cod_l, lg.family_cnt, lg.id_grp_lgt, lg.id_calc 
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
    ) as lg2 on (lg2.id_paccnt = c.id)
    left join aqi_grptar_tbl as tar on (tar.id = c.id_gtar)
    left join lgi_group_tbl as g on (lg2.id_grp_lgt = g.id)
    left join lgi_norm_tbl as  n on (n.id_calc = lg2.id_calc and (n.id_tar_grp = tar.id_lgt_group or n.id_tar_grp is null))
    left join
        ( select id_paccnt, param_value as subs_name 
            from cli_ext_params_tbl where id_paccnt = $p_id_paccnt and id_param = 1 limit 1
        ) as sn on (sn.id_paccnt = c.id)
    left join rep_abon_dt_tbl as d_old on (d_old.id_paccnt = c.id)
    where c.id = $p_id_paccnt; ";

    
   // throw new Exception(json_encode($SQL));
    
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        $i=0;
        while ($row = pg_fetch_array($result)) {

            
            $abn = htmlspecialchars($row['abon']);
            
            if ($res_code==310)
              $adr = htmlspecialchars($row['street'].' '.$row['house']);
            else
              $adr = htmlspecialchars($row['town'].', '.$row['street'].' '.$row['house']);
            
            $person_acc = $row['book'].'/'.$row['code'];
            
            $pilga = htmlspecialchars($row['lgt_name']);
            $perc = $row['percent'];
            $numb_lgperson = $row['family_cnt'];
            //$norma = $row['norm_min'];
            if ($numb_lgperson==1)
            {
                $norma = $row['norm_min'];
            }    
            else
            {
                $norma = $row['norm_min'] +($numb_lgperson-1)*$row['norm_one'];
                if ($norma>$row['norm_max']) $norma=$row['norm_max'];
            }
            
            
            $id_gtar = $row['id_gtar'];
            
            $executor = $p_person;
            
            $debet_2month_txt = number_format_ua($row['old_sum'],2);
            if (($debet_2month_txt =='0')||($debet_2month_txt ==''))
            {
                $debet_2month_txt='немає';
            }
            else
            {
                $debet_2month_txt.=' грн.';
            }
            
            $in_stock ='';
            $absent ='';
            
            $base_norm = $row['norm_min'];
            
            if (($base_norm!=0)&&($base_norm!=30))
            {
                if ($base_norm==130)
                {
                    $p_plita=1;
                    $p_hotw=1;
                    $p_coldw=1;
                }
                if ($base_norm==150)
                {
                    $p_plita=1;
                    //$p_hotw=0;
                    //$p_coldw=1;
                }
                if ($base_norm==120)
                {
                    //$p_plita=0;
                    //$p_hotw=0;
                    //$p_hotw_gas=0;
                    $p_coldw = 1;
                }

              $man_norm = 30;
              $max_norm = 210;
                
            }
            else
            {
              $base_norm = 90;
              $man_norm = 30;
              $max_norm = 210;
            }
            
            
            if ($p_hotw==1)
            {
               if($in_stock !='' ) $in_stock.=',';
               $in_stock.='централізоване постачання гарячої води'; 
            }
            else
            {
               if($absent !='' ) $absent.=',';                
               $absent.='централізоване постачання гарячої води'; 
            }

            if ($p_coldw==1)
            {
               if($in_stock !='' ) $in_stock.=',';
               $in_stock.='централізоване постачання холодної води'; 
            }
            else
            {
               if($absent !='' ) $absent.=',';
               $absent.='централізоване постачання холодної води'; 
            }
                
            if ($p_hotw_gas==1)
            {
               if($in_stock !='' ) $in_stock.=','; 
               $in_stock.='газові водонагрівальні прилади'; 
            }
            else
            {
               if($absent !='' ) $absent.=','; 
               $absent.='газові водонагрівальні прилади'; 
            }
            
            
            if (($id_gtar ==3)||($id_gtar ==5)||($id_gtar ==7)||($id_gtar ==12)||($id_gtar ==15))
            {
                $p_plita=1;
            }
            
            if ($p_plita==1)
            {
               if($in_stock !='' ) $in_stock.=','; 
               $in_stock.='стаціонарна електроплита'; 
            }
            else
            {
               if($absent !='' ) $absent.=','; 
               $absent.='стаціонарна електроплита'; 
            }
            
            $template_name = $row['template']; 
            
            
            if (($p_plita==1)&&($p_hotw==1))
            {
                $base_norm = 130;
                $max_norm = 250;
            }
            if (($p_plita==1)&&($p_hotw!=1))
            {
                $base_norm = 150;
                $max_norm = 270;
            }
            if (($p_plita!=1)&&($p_hotw!=1)&&($p_hotw_gas!=1)&&($p_coldw==1))
            {
                $base_norm = 120;
                $max_norm = 240;
            }
            $rozrahunok = '';
            $rozrahunok_txt= '';
            $calc_norm=0;
            
            if (($p_people_count!='null')&&($p_people_count!=0))
            {    
            
              if ($p_people_count==1)
              {
                  $rozrahunok = "електроенергія - {$base_norm} кВт/год";
                  $calc_norm=$base_norm;
              }
              else
              {
                  $calc_norm = $base_norm +($p_people_count-1)*$man_norm;
                  $p_people_count_1 = $p_people_count-1;
                
                  if ($calc_norm<=$max_norm)
                  {
                      $rozrahunok = "електроенергія - {$base_norm} кВт/год + {$p_people_count_1} чол. * {$man_norm} кВт/год = {$calc_norm} кВт/год";
                  }
                  else
                  {
                      $rozrahunok = "електроенергія - {$base_norm} кВт/год + {$p_people_count_1} чол. * {$man_norm} кВт/год = {$calc_norm} кВт/год, але не більше {$max_norm} ";                    
                  }
              }
            
              if (($p_show_norm==1)&&(($p_social_norm=='null')||($p_social_norm=='')))
                  $rozrahunok_txt = "<span class='rows' style='width:630px'>Розрахунок в межах соціальних нормативів за місяць: </span><span class='rows' style='width:610px; margin-left:20px;'>{$rozrahunok}</span>";
            
            }
        }

        $heat_area_txt='';
        if (($p_heat_area!='null')&&($p_heat_area!='')&&($p_heat_area!='0'))
        {
            $heat_area_txt = "<span class='rows' style='width:630px'>Опалювальна площа : {$p_heat_area} м2</span>";            
        }
        
        //----------------------------------
        $SQL_z = "select max(id_zone) as max_zone
          from clm_meterpoint_tbl as m join 
          clm_meter_zone_tbl as z on (z.id_meter = m.id)
          where m.id_paccnt = $p_id_paccnt;";
        $max_zone = 0;
       
        $result_z = pg_query($Link, $SQL_z) or die("SQL Error: " . pg_last_error($Link) . $SQL_z);
        if ($result_z) 
        {
          $row_z = pg_fetch_array($result_z);
          $max_zone = $row_z['max_zone'];
        }
        
        $result_z = pg_query($Link, 'select koef from eqk_zone_tbl where id = 6;');
        $row_z = pg_fetch_array($result_z);
        $k31 = $row_z['koef'];

        $result_z = pg_query($Link, 'select koef from eqk_zone_tbl where id = 7;');
        $row_z = pg_fetch_array($result_z);
        $k32 = $row_z['koef'];
        
        $result_z = pg_query($Link, 'select koef from eqk_zone_tbl where id = 8;');
        $row_z = pg_fetch_array($result_z);
        $k33 = $row_z['koef'];
        
        $result_z = pg_query($Link, 'select koef from eqk_zone_tbl where id = 9;');
        $row_z = pg_fetch_array($result_z);
        $k21 = $row_z['koef'];
        
        $result_z = pg_query($Link, 'select koef from eqk_zone_tbl where id = 10;');
        $row_z = pg_fetch_array($result_z);
        $k22 = $row_z['koef'];
        
        $template_suffix='';
        
        if ($max_zone==10) 
        {
            $template_suffix='_2z';
        }
        
        if ($max_zone==8) 
        {
            $template_suffix='_3z';
        }
            
        //    --------------------------
        // Репки 07.04.2017 - если тариф многодетные с электроотоплением, показываем 
        // как обычное электроотопление
        if ($id_gtar==6) $id_gtar=4; 

        $SQL_tar = "select t.id,t.name,t.lim_min, t.lim_max,
	(select value from aqd_tarif_tbl as d 
		where d.id_tarif = t.id 
		and dt_begin <=$p_mmgg::date order by dt_begin desc limit 1) as tar_val
	from aqm_tarif_tbl as t 
        where ((dt_e is null ) or (dt_e > $p_mmgg::date )) and (dt_b <= $p_mmgg::date)
        and (per_min <= $p_mmgg  and  per_max >= $p_mmgg  or per_min is null )
        and id_grptar = $id_gtar 
        order by ident  
        limit 2;";
  
        $result_tar = pg_query($Link, $SQL_tar) or die("SQL Error: " . pg_last_error($Link) . $SQL);
        if ($result_tar) {
        
          $rows_count_tar = pg_num_rows($result_tar);
        
          $it=0;
          $tar_val1=0;
          $tar_val2=0;
          $tar_dem1=0;
          $tar_dem2=0;
          $tar_lgt_val1 = 0;
          $tar_lgt_val2 = 0;
          
          while ($row_tar = pg_fetch_array($result_tar)) {
         
             if ($it == 0 ) 
             {
                 $tar_dem1 = $row_tar['lim_max'];
                 $tar_val1 = round($row_tar['tar_val'],4);
                 $tar_lgt_val1 = round($tar_val1*$perc/100,4);
                 
                 if ($max_zone==10) 
                 {
                   $tar_val11 = round($tar_val1*$k21,4);
                   $tar_val12 = round($tar_val1*$k22,4);
                   
                   $tar_lgt_val11= round($tar_lgt_val1*$k21,4);
                   $tar_lgt_val12= round($tar_lgt_val1*$k22,4);
                 }
                 if ($max_zone==8) 
                 {
                   $tar_val11 = round($tar_val1*$k31,4);
                   $tar_val12 = round($tar_val1*$k32,4);
                   $tar_val13 = round($tar_val1*$k33,4);
                   
                   $tar_lgt_val11= round($tar_lgt_val1*$k31,4);
                   $tar_lgt_val12= round($tar_lgt_val1*$k32,4);
                   $tar_lgt_val13= round($tar_lgt_val1*$k33,4);                   
                 }

             }

             if ($it == 1 ) 
             {
                 $tar_dem2 = $row_tar['lim_max'];
                 $tar_val2 = round($row_tar['tar_val'],4);
                 $tar_lgt_val2 = round($tar_val2*$perc/100,4);

                 if ($max_zone==10) 
                 {
                   $tar_val21 = round($tar_val2*$k21,4);
                   $tar_val22 = round($tar_val2*$k22,4);
                   
                   $tar_lgt_val21= round($tar_lgt_val2*$k21,4);
                   $tar_lgt_val22= round($tar_lgt_val2*$k22,4);
                   
                 }
                 
                 if ($max_zone==8) 
                 {
                   $tar_val21 = round($tar_val2*$k31,4);
                   $tar_val22 = round($tar_val2*$k32,4);
                   $tar_val23 = round($tar_val2*$k33,4);
                   
                   $tar_lgt_val21= round($tar_lgt_val2*$k31,4);
                   $tar_lgt_val22= round($tar_lgt_val2*$k32,4);
                   $tar_lgt_val23= round($tar_lgt_val2*$k33,4);                   
                 }
                 
             }
             
             $it++; 
          }
        }
        
        $SQL_person = "select * from prs_persons where id = $p_id_person ;";
        $phone ='';
        $result_person = pg_query($Link, $SQL_person) or die("SQL Error: " . pg_last_error($Link) . $SQL);
        if ($result_person) {
        
          while ($row_person = pg_fetch_array($result_person)) {
         
                 $phone = $row_person['phone'];
          }
        }
        
        
      $manual_param_info="<span class='rows' style='width:630px;'></span>";
      $manual_param_info_bottom="";
      
      if (($p_social_norm=='null')||($p_social_norm==''))
      {
        $p_social_norm=0;    
        $manual_param_info="
        <span class='rows' style='width:630px;'>Є в наявності: {$in_stock}. </span>
        <span class='rows' style='width:630px; margin-bottom:5px;'>Відсутньо: {$absent}. </span>";
        
        if (($calc_norm!=0)&&($p_show_norm==1)&&($res_code==170))
        {
            
            $social_norm_price =0;
        
            if (($tar_dem1!=0)&&($tar_dem1!='')&&($tar_dem1!='null'))
            {
                if($calc_norm>$tar_dem1)
                {
                    $social_norm_price = round(($tar_dem1*$tar_val1)+($calc_norm-$tar_dem1)*$tar_val2,2);
                    $dem2 = $calc_norm-$tar_dem1;
                    $social_norm_calc = "({$tar_dem1}*{$tar_val1})+{$dem2}*{$tar_val2}";
                }   
                else
                {
                    $social_norm_price = round($calc_norm*$tar_val1,2);
                    $social_norm_calc = " {$calc_norm}*{$tar_val1}";
                }
            }
            else
            {
                $social_norm_price = round($calc_norm*$tar_val1,2);
                $social_norm_calc = " {$calc_norm}*{$tar_val1}";
            }
            
            if ($max_zone==0)            
            {
              $social_norm_price = number_format_ua($social_norm_price,2);
              $rozrahunok_txt.="<br/>
              <span class='rows' style='width:630px; margin-bottom:5px; margin-left:20px; '>вартість - {$social_norm_calc} = {$social_norm_price} грн. </span>";
            }
            
        }
        
      }
      else
      {
        $social_norm_price =0;
        
        if (($tar_dem1!=0)&&($tar_dem1!='')&&($tar_dem1!='null'))
        {
            if($p_social_norm>$tar_dem1)
            {
                $social_norm_price = round(($tar_dem1*$tar_val1)+($p_social_norm-$tar_dem1)*$tar_val2,2);
                $dem2 = $p_social_norm-$tar_dem1;
                $social_norm_calc = "({$tar_dem1}*{$tar_val1})+{$dem2}*{$tar_val2}";
            }   
            else
            {
                $social_norm_price = round($p_social_norm*$tar_val1,2);
                $social_norm_calc = " {$p_social_norm}*{$tar_val1}";
            }
        }
        else
        {
            $social_norm_price = round($p_social_norm*$tar_val1,2);
            $social_norm_calc = " {$p_social_norm}*{$tar_val1}";
        }

        $manual_param_info_bottom="
        <span class='rows' style='width:630px;'>Соціальний норматив користування послугами з електроопалення - {$p_social_norm} кВтг. </span>";
        
        
        if ($max_zone==0)        
        {
          $manual_param_info_bottom.="        
          <span class='rows' style='width:630px; margin-bottom:5px;'>Вартість послуги у межах соціального нормативу користування : <br/>
          {$social_norm_calc} = {$social_norm_price} грн. </span>";
        }
      }
        
        
      $header_text = file_get_contents("html_templates/".$template_name.$template_suffix."_header.htm");
      $footer_text = file_get_contents("html_templates/".$template_name."_footer.htm");
  
        
      eval("\$header_text = \"$header_text\";");
      echo $header_text;

      eval("\$footer_text_eval = \"$footer_text\";");
      echo $footer_text_eval;
      
      echo '<div style="clear:both;" > </div>';
      echo '<br style="page-break-after: always">';
    }

    if ($p_protocol==1)
    {
        
            if ($p_heat_area=='') $p_heat_area ='null';
            if ($p_people_count=='') $p_people_count ='null';
            if ($p_num_input=='') $p_num_input ='null';
            if ($p_dt_input_date=="''") $p_dt_input_date ='null';
            
            $SQL = " INSERT INTO rep_spravka_tbl(
            id_paccnt, doc_type, num_sp, dt_sp, date_start, date_end, 
            num_input, dt_input, people_count, heat_area, hotw, hotw_gas, 
            coldw, plita, social_norm, show_norm, id_person)          
            values ($p_id_paccnt, 1, $p_num_sp, $p_dt_sp_date,$p_dt_b,$p_dt_e,
            $p_num_input, $p_dt_input_date, $p_people_count, $p_heat_area, 
            $p_hotw, $p_hotw_gas,$p_coldw, $p_plita,$p_social_norm, $p_show_norm,  $p_id_person ); ";
            
            $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);            
            
    }
    $p_num_sp++;
   }
  }
}
//------------------------------------------------------------------------------

if ($oper=='sprav6_list')
{
    
    
    $pp_mmgg = sql_field_val('dt_sp', 'mmgg');
    
    //===========
    rep_abon_dt($Link, $pp_mmgg,  2, 0);
    //=========
    
    $SQL = "select coalesce(max(num_sp),0)+1 as next_num  from rep_spravka_tbl
            where date_trunc('year', mmgg::date) = date_trunc('year',$pp_mmgg::date ) ;";
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) 
    {
        $row = pg_fetch_array($result);
        $p_num_sp = $row['next_num'];
    }
    //---------------------------------------------------
    
    $SQL_list = "select q.*, date_trunc('month', q.dt_sp) as pmmgg,
       to_char(date_trunc('month', q.dt_sp), 'DD.MM.YYYY') as pmmgg_str,
       date_part('month', q.dt_sp) as mmgg_month,
       date_part('year', q.dt_sp) as mmgg_year,
       to_char(q.dt_input, 'DD.MM.YYYY') as dt_input_txt,
       to_char(q.dt_sp, 'DD.MM.YYYY') as dt_sp_txt,
       to_char(q.date_start, 'DD.MM.YYYY') as date_start_txt,
       to_char(q.date_end, 'DD.MM.YYYY') as date_end_txt,
        p.represent_name as person,
        ('0'||substring(c.code FROM '[0-9]+'))::int as int_code,
        ('0'||substring(c.book FROM '[0-9]+'))::int as int_book   
    from rep_spravka_queue_tmp as q
    join clm_paccnt_tbl as c on (c.id = q.id_paccnt)
    left join prs_persons as p on (p.id = q.id_person)
    where id_user = $session_user order by int_book, book, int_code, code ;";
    
    
    $result_list = pg_query($Link, $SQL_list) or die("SQL Error: " . pg_last_error($Link) . $SQL_list);
    if ($result_list) {
        
    while ($row_list = pg_fetch_array($result_list)) {
    
    
    
    $p_mmgg = "'".$row_list['pmmgg']."'";
    $mmgg1_str = ukr_month($row_list['mmgg_month'],0).' '.$row_list['mmgg_year'];
    
    $p_dt_b = "'".$row_list['date_start']."'"; //sql_field_val('dt_b', 'mmgg');
    $p_dt_e = "'".$row_list['date_end']."'"; //sql_field_val('dt_e', 'mmgg');
    
    $p_dt_b_str = ukr_date($row_list['date_start_txt'],0,2);
    $p_dt_e_str = ukr_date($row_list['date_end_txt'],0,1);
    
    $p_id_paccnt = $row_list['id_paccnt']; // sql_field_val('id_paccnt', 'int');
    
    //$p_num_sp = sql_field_val('num_sp', 'int');
    $p_dt_sp = $row_list['dt_sp_txt'];// sql_field_val('dt_sp', 'text');

    $p_num_input = $row_list['num_input']; //sql_field_val('num_input', 'text');
    $p_dt_input = $row_list['dt_input_txt']; //sql_field_val('dt_input', 'text');
    
    $p_dt_sp_date = "'".$row_list['dt_sp']."'"; //sql_field_val('dt_sp', 'date');
    $p_dt_input_date = "'".$row_list['dt_input']."'"; //sql_field_val('dt_input', 'date');    
    
    $p_people_count = $row_list['people_count']; // sql_field_val('people_count', 'int');
    
    if (($p_people_count=='null')||($p_people_count=='')) $p_people_count_txt="__";
    else $p_people_count_txt="$p_people_count";

    $p_heat_area = $row_list['heat_area']; //sql_field_val('heat_area', 'numeric');
    
    $p_id_person = $row_list['id_person']; //sql_field_val('id_person', 'int');
    $p_person = $row_list['person']; //sql_field_val('person', 'text');
    
    $p_hotw = $row_list['hotw']; //sql_field_val('hotw', 'int');
    $p_hotw_gas = $row_list['hotw_gas']; //sql_field_val('hotw_gas', 'int');
    $p_coldw = $row_list['coldw']; //sql_field_val('coldw', 'int');
    $p_plita = $row_list['plita']; //sql_field_val('plita', 'int');
    //$p_show_dte = sql_field_val('show_dte', 'int');
    
    $p_protocol = sql_field_val('write_protocol', 'int');
    
    $params_caption = '';
    $where = '';
    
    $date = ukr_date($p_dt_sp);
    $dapartment = $res_print_name ;// 'Міські електричні мережі';
    //$director = 'Чернявський О.О.';
    $director = $sbutboss_name;
//---------------------------------------------------------------
    
    
   // $p_mmgg = sql_field_val('dt_sp', 'mmgg');
   // $mmgg1_str = trim($_POST['period_str']);
    
   // $p_dt_b = sql_field_val('dt_b', 'mmgg');
   // $p_dt_e = sql_field_val('dt_e', 'mmgg');
    
   // $p_dt_b_str = ukr_date(sql_field_val('dt_b', 'rext'),0,2);
   // $p_dt_e_str = ukr_date(sql_field_val('dt_e', 'rext'),0,1);

   // $p_id_paccnt = sql_field_val('id_paccnt', 'int');
    
   // $p_num_sp = sql_field_val('num_sp', 'int');
   // $p_dt_sp = sql_field_val('dt_sp', 'text');
   // $p_dt_sp_date = sql_field_val('dt_sp', 'date');

   // $p_num_input = sql_field_val('num_input', 'text');
   // $p_dt_input = sql_field_val('dt_input', 'text');
   // $p_dt_input_date = sql_field_val('dt_input', 'date');
    
    //$p_people_count = sql_field_val('people_count', 'int');
    //$p_heat_area = sql_field_val('heat_area', 'numeric');
    
    //$p_id_person = sql_field_val('id_person', 'int');
    //$p_person = sql_field_val('person', 'text');
    
    //$p_hotw = sql_field_val('hotw', 'int');
    //$p_hotw_gas = sql_field_val('hotw_gas', 'int');
    //$p_coldw = sql_field_val('coldw', 'int');
    //$p_plita = sql_field_val('plita', 'int');
    //$p_show_dte = sql_field_val('show_dte', 'int');
    
//-----------------------------------------------------    
   
    $SQL = "select c.id, c.code, c.book,
        adr.town, adr.street, address_print(c.addr) as house,
        coalesce(subs_name,(ab.last_name||' '||coalesce(ab.name,'')||' '||coalesce(ab.patron_name,''))::varchar) as abon,
        lg2.*, n.percent, n.norm_min, n.norm_one, n.norm_max, tar.ident,c.id_gtar, 
        coalesce(g.name,'-') as lgt_name,
        CASE WHEN lg2.id_grp_lgt is not null and lg2.dt_end is null THEN  
          'надається з '|| to_char(lg2.dt_start, 'DD.MM.YYYY')||' р. розмір пільги: '||n.percent::varchar||' % ,'||'<br/> членів родини:'||family_cnt::varchar
        WHEN lg2.id_grp_lgt is not null and lg2.dt_end is not null THEN  
          'надається з '|| to_char(lg2.dt_start, 'DD.MM.YYYY')||' р.'||' по '||to_char(lg2.dt_end, 'DD.MM.YYYY')||', розмір пільги: '||n.percent::varchar||' % ,'||'<br/> членів родини:'||family_cnt::varchar
        ELSE '' END ::varchar as lgt_info,
        CASE WHEN coalesce(c.heat_area,0)>0 THEN 'Опалювальна площа :'||c.heat_area::varchar||' кв.м. <br/>' ELSE '' END ||    
        CASE WHEN c.id_gtar in (3,5,7,12,15) THEN 'Наявність стаціонарної електроплити: так' 
             WHEN c.id_gtar in (4,6,14) THEN 'Наявність стаціонарної електроплити: ' ||
             CASE WHEN (c.addr).id_class = 43508 and (c.addr).house ='15а' THEN 'так' ELSE 'ні' END ||
             '<br/> Є в наявності електроопалення' 
             ELSE 'Наявність стаціонарної електроплити: ні ' END||
             CASE WHEN c.id_gtar in (5,6,8,9,12,13) THEN '<br/>Тариф:багатодітна родина' ELSE '' END::varchar as additional_info,
             coalesce(d_old.summ,0) as old_sum
        from clm_paccnt_h as c 
        join (select id, max(dt_b) as dt_b from clm_paccnt_h  where 
            ((dt_b < ($p_mmgg::date+'1 month'::interval) and dt_e is null)
            or 
            tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
        ) group by id order by id) as c2 
        on (c.id = c2.id and c.dt_b = c2.dt_b)
        join clm_abon_h as ab on (ab.id = c.id_abon) 
        join (select id, max(dt_b) as dt_b from clm_abon_h  where 
            ((dt_b < ($p_mmgg::date+'1 month'::interval) and dt_e is null)
            or 
            tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
        )
        group by id order by id) as a2 on (ab.id = a2.id and ab.dt_b = a2.dt_b)
        left join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class)
  left join 
    ( select lg.id_paccnt, lg.fio_lgt, lg.ident_cod_l, lg.family_cnt, lg.id_grp_lgt, lg.id_calc , lg.dt_start,lg.dt_end
     from lgm_abon_h as lg  join
      (select lg.id_paccnt, max(lg.id_key) as id_key 
       from lgm_abon_h as lg 
        join (select id, max(dt_b) as dt_b from lgm_abon_h  where
            ((dt_b < ($p_dt_e::date+'1 month'::interval) 
             and ( dt_e is null and (dt_end is null or dt_end >= $p_dt_b )  )
            )
            or 
            tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_dt_b::timestamp::abstime,($p_dt_e::date+'1 month - 1 day'::interval)::timestamp::abstime))
        )   group by id order by id) as lg2 
        on (lg.id = lg2.id and lg.dt_b = lg2.dt_b)
      group by lg.id_paccnt
      ) as gg
      on(lg.id_key = gg.id_key)
    ) as lg2 on (lg2.id_paccnt = c.id)
    left join aqi_grptar_tbl as tar on (tar.id = c.id_gtar)
    left join lgi_group_tbl as g on (lg2.id_grp_lgt = g.id)
    left join lgi_norm_tbl as  n on (n.id_calc = lg2.id_calc and (n.id_tar_grp = tar.id_lgt_group or n.id_tar_grp is null))
    left join
            ( select id_paccnt, param_value as subs_name 
                from cli_ext_params_tbl where id_paccnt = $p_id_paccnt and id_param = 1 limit 1
            ) as sn on (sn.id_paccnt = c.id)
    left join rep_abon_dt_tbl as d_old on (d_old.id_paccnt = c.id)
    where c.id = $p_id_paccnt; ";

    
   // throw new Exception(json_encode($SQL));
    
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        $i=0;
        while ($row = pg_fetch_array($result)) {

            
            $abn = htmlspecialchars($row['abon']);

            if ($res_code==310)
              $adr = htmlspecialchars($row['street'].' '.$row['house']);
            else
              $adr = htmlspecialchars($row['town'].', '.$row['street'].' '.$row['house']);
            
            $person_acc = $row['book'].'/'.$row['code'];
            
            $pilga = htmlspecialchars($row['lgt_name']);
            $perc = $row['percent'];
            $numb_lgperson = $row['family_cnt'];
            $norma = $row['norm_min'];
            
            $id_gtar = $row['id_gtar'];
            
            $executor = $p_person;
            
            $additional_info = $row['additional_info']; 
            $lgt_info = $row['lgt_info']; 
            
            $debet_2month_txt = number_format_ua($row['old_sum'],2);
            if (($debet_2month_txt =='0')||($debet_2month_txt ==''))
            {
                $debet_2month_txt='немає';
            }
            else
            {
                $debet_2month_txt.=' грн.';
            }
           
            
        }
        
        
        $table_txt = '';
        $SQL_tar = "
        select *, to_char(mmgg_calend, 'DD.MM.YYYY') as mmgg_txt,
         round((select (select value from aqd_tarif_tbl as d 
		where d.id_tarif = t.id 
		and dt_begin <=mmgg_calend order by dt_begin desc limit 1) as tar_val 
	  from aqm_tarif_tbl as t 
          where ((dt_e is null ) or (dt_e > mmgg_calend::date )) and (dt_b <= mmgg_calend::date)
          and (per_min <= mmgg_calend  and  per_max >= mmgg_calend  or per_min is null )
          and id_grptar = $id_gtar
          order by ident  
          limit 1 offset 0
         )*coalesce(koef,1),4) as calc_tar1,   
         round((select (select value from aqd_tarif_tbl as d 
		where d.id_tarif = t.id 
		and dt_begin <=mmgg_calend order by dt_begin desc limit 1) as tar_val 
	  from aqm_tarif_tbl as t 
          where ((dt_e is null ) or (dt_e > mmgg_calend::date )) and (dt_b <= mmgg_calend::date)
          and (per_min <= mmgg_calend  and  per_max >= mmgg_calend  or per_min is null )
          and id_grptar = $id_gtar
          order by ident  
          limit 1 offset 1
         )*coalesce(koef,1),4) as calc_tar2   
        from 
        (
         select distinct date_trunc('month',c_date)::date as mmgg_calend from calendar
         where c_date>=$p_dt_b and c_date<=$p_dt_e
         order by mmgg_calend
         ) as cl
         left join
         (select b.mmgg_bill, s.id_tarif, t.ident,t.id_grptar,
		   sum(s.demand ) as demand, sum(s.summ ) as summ, 
                   z.id, z.koef, CASE WHEN z.id = 0 THEN ''
                                      WHEN z.id in (6,9) THEN 'ніч'
                                      WHEN z.id = 7 THEN 'напівпік'
                                      WHEN z.id = 8 THEN 'пік'  
                                      WHEN z.id = 10 THEN 'день' END as zone_name,
                   round((select value from aqd_tarif_tbl as d 
	           where d.id_tarif = t.id 
		   and dt_begin <= b.mmgg_bill order by dt_begin desc limit 1)*z.koef,4) as tar_val
    from acm_summ_tbl as s
    join acm_bill_tbl as b on (b.id_doc=s.id_doc)    
    join aqm_tarif_tbl as t on (t.id = s.id_tarif)
    join eqk_zone_tbl as z on (z.id = s.id_zone)        
    where
     b.id_pref = 10
    and b.id_paccnt = $p_id_paccnt
    and b.mmgg_bill >= $p_dt_b and b.mmgg_bill<= $p_dt_e
   -- and not exists (select c.id_doc from acm_bill_tbl as c where c.id_paccnt = b.id_paccnt and c.id_corr_doc = b.id_doc)
    group by b.mmgg_bill, s.id_tarif, t.ident,t.id, t.id_grptar,z.id, z.koef, CASE WHEN z.id = 0 THEN '' ELSE z.nm END
    ) as bb on (bb.mmgg_bill = cl.mmgg_calend)
    order by cl.mmgg_calend, bb.id, bb.ident; ";
        //echo $SQL_tar;
        $result_tar = pg_query($Link, $SQL_tar) or die("SQL Error: " . pg_last_error($Link) . $SQL);
        if ($result_tar) {
        
          $rows_count_tar = pg_num_rows($result_tar);
        
          $it=0;
          $im = 0;
          $demand_all=0;
          $demand_avg=0;
          
          $cur_mmgg = '';
          $calc_tar1=0;
          $calc_tar2=0;
          
          while ($row_tar = pg_fetch_array($result_tar)) {

              $calc_tar1_p = $calc_tar1;
              $calc_tar2_p = $calc_tar2;
              
              $calc_tar1 = $row_tar['calc_tar1'];
              $calc_tar2 = $row_tar['calc_tar2'];

              
              if ($cur_mmgg != $row_tar['mmgg_calend'].' '.$row_tar['zone_name'])
              {
                $im++;  
                if($cur_mmgg != '')
                {
                
                  if ($it<2)  
                  {
                   $table_txt.="
                    <td> $cur_mmgg_txt </td>
                    <td>0</td>
                    <td>{$calc_tar2_p}</td>";
                  }
                  $table_txt.= "</tr>";                  
                }  
                $table_txt.="<tr>";
                $cur_mmgg = $row_tar['mmgg_calend'].' '.$row_tar['zone_name'];
                $it=0;
                
              }
              
              $tar_val = $row_tar['tar_val'];
              
              if ($tar_val=='')
              {
                  if ($it==0) $tar_val= $calc_tar1;   
                  if ($it==1) $tar_val= $calc_tar2;   
              }
              
              $demand_val = $row_tar['demand'];
              $demand_val_txt = number_format_ua($demand_val,0);
              $id_grptar  = $row_tar['id_grptar'];
              $cur_mmgg_txt = ukr_date($row_tar['mmgg_txt'],0,1).' '.$row_tar['zone_name'];
              
              $demand_all+=$demand_val;
              
              $table_txt.="
                <td> $cur_mmgg_txt </td>
                <td> $demand_val_txt </td>
                <td> $tar_val </td> ";        
              
             $it++; 
          }
          
         if($cur_mmgg != '')
         {
           if ($it<2)  
           {
              $table_txt.="
              <td> $cur_mmgg_txt </td>
              <td>0</td>
              <td>{$calc_tar2}</td>";
           }
           $table_txt.= "</tr>";                  
         }  
          
        }        
        if ($im!=0)
            $demand_avg = round($demand_all/$im,0);
        else
            $demand_avg = '-';
        
        $phone ='';
        $SQL_person = "select * from prs_persons where id = $p_id_person ;";
        $result_person = pg_query($Link, $SQL_person) or die("SQL Error: " . pg_last_error($Link) . $SQL);
        if ($result_person) {
        
          while ($row_person = pg_fetch_array($result_person)) {
         
                 $phone = $row_person['phone'];
          }
        }
        
      $header_text = file_get_contents("html_templates/".$template_name."_header.htm");
      $footer_text = file_get_contents("html_templates/".$template_name."_footer.htm");
        
      eval("\$header_text = \"$header_text\";");
      echo $header_text;

      echo $table_txt;
      
      eval("\$footer_text_eval = \"$footer_text\";");
      echo $footer_text_eval;
      
      echo '<div style="clear:both;" > </div>';
      echo '<br style="page-break-after: always">';
      
      
      if ($p_protocol==1)
      { 
          
            if ($p_heat_area=='') $p_heat_area ='null';
            if ($p_people_count=='') $p_people_count ='null';
            if ($p_num_input=='') $p_num_input ='null';
            if ($p_dt_input_date=="''") $p_dt_input_date ='null';

            $SQL = " INSERT INTO rep_spravka_tbl(
            id_paccnt, doc_type, num_sp, dt_sp, date_start, date_end, 
            num_input, dt_input, people_count, heat_area, hotw, hotw_gas, 
            coldw, plita, id_person)          
            values ($p_id_paccnt, 2, $p_num_sp, $p_dt_sp_date,$p_dt_b,$p_dt_e,
            $p_num_input, $p_dt_input_date, $p_people_count, $p_heat_area, 
            $p_hotw, $p_hotw_gas,$p_coldw, $p_plita,$p_id_person ); ";
            
            $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);            
            
      }
      
    }
    $p_num_sp = $p_num_sp+1;     
   }
  }
  
}
//------------------------------------------------------------------------------
if ($oper=='sprav12_list')
{
    
    $pp_mmgg = sql_field_val('dt_b', 'mmgg');
    list($year, $month, $day  ) = split('[/.-]', $pp_mmgg);
    
    $SQL = "select coalesce(max(num_sp),0)+1 as next_num  from rep_spravka_tbl
            where date_trunc('year', mmgg::date) = date_trunc('year',$pp_mmgg::date ) ;";
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) 
    {
        $row = pg_fetch_array($result);
        $p_num_sp = $row['next_num'];
    }
    //---------------------------------------------------
    
    $SQL_list = "select q.*, date_trunc('month', q.dt_sp) as pmmgg,
       to_char(date_trunc('month', q.dt_sp), 'DD.MM.YYYY') as pmmgg_str,
       date_part('month', q.dt_sp) as mmgg_month,
       date_part('year', q.dt_sp) as mmgg_year,
       to_char(q.dt_input, 'DD.MM.YYYY') as dt_input_txt,
       to_char(q.dt_sp, 'DD.MM.YYYY') as dt_sp_txt,
       to_char(q.date_start, 'DD.MM.YYYY') as date_start_txt,
       to_char(q.date_end, 'DD.MM.YYYY') as date_end_txt,
        p.represent_name as person,
        ('0'||substring(c.code FROM '[0-9]+'))::int as int_code,
        ('0'||substring(c.book FROM '[0-9]+'))::int as int_book   
    from rep_spravka_queue_tmp as q
    join clm_paccnt_tbl as c on (c.id = q.id_paccnt)
    left join prs_persons as p on (p.id = q.id_person)
    where id_user = $session_user order by int_book, book, int_code, code ;";
    
    
    $result_list = pg_query($Link, $SQL_list) or die("SQL Error: " . pg_last_error($Link) . $SQL_list);
    if ($result_list) {
        
    while ($row_list = pg_fetch_array($result_list)) {
    
    
    
    $p_mmgg = "'".$row_list['pmmgg']."'";
    $mmgg1_str = ukr_month($row_list['mmgg_month'],0).' '.$row_list['mmgg_year'];
    
    $p_dt_b = "'".$row_list['date_start']."'"; //sql_field_val('dt_b', 'mmgg');
    $p_dt_e = "'".$row_list['date_end']."'"; //sql_field_val('dt_e', 'mmgg');
    
    $p_dt_b_str = ukr_date($row_list['date_start_txt'],0,2);
    $p_dt_e_str = ukr_date($row_list['date_end_txt'],0,1);
    
    $p_id_paccnt = $row_list['id_paccnt']; // sql_field_val('id_paccnt', 'int');
    
    //$p_num_sp = sql_field_val('num_sp', 'int');
    $p_dt_sp = $row_list['dt_sp_txt'];// sql_field_val('dt_sp', 'text');

    $p_num_input = $row_list['num_input']; //sql_field_val('num_input', 'text');
    $p_dt_input = $row_list['dt_input_txt']; //sql_field_val('dt_input', 'text');
    
    $p_dt_sp_date = "'".$row_list['dt_sp']."'"; //sql_field_val('dt_sp', 'date');
    $p_dt_input_date = "'".$row_list['dt_input']."'"; //sql_field_val('dt_input', 'date');    
    
    $p_people_count = $row_list['people_count']; // sql_field_val('people_count', 'int');
    
    if (($p_people_count=='null')||($p_people_count=='')) $p_people_count_txt="__";
    else $p_people_count_txt="$p_people_count";

    $p_heat_area = $row_list['heat_area']; //sql_field_val('heat_area', 'numeric');
    
    $p_id_person = $row_list['id_person']; //sql_field_val('id_person', 'int');
    $p_person = $row_list['person']; //sql_field_val('person', 'text');
    
    $p_hotw = $row_list['hotw']; //sql_field_val('hotw', 'int');
    $p_hotw_gas = $row_list['hotw_gas']; //sql_field_val('hotw_gas', 'int');
    $p_coldw = $row_list['coldw']; //sql_field_val('coldw', 'int');
    $p_plita = $row_list['plita']; //sql_field_val('plita', 'int');
    //$p_show_dte = sql_field_val('show_dte', 'int');
    
    $p_protocol = sql_field_val('write_protocol', 'int');
    
    $params_caption = '';
    $where = '';
    
    $date = ukr_date($p_dt_sp);
    $dapartment = $res_print_name ;// 'Міські електричні мережі';
    //$director = 'Чернявський О.О.';
    $director = $sbutboss_name;
    
    
    /*
    $p_mmgg = sql_field_val('dt_sp', 'mmgg');
    $mmgg1_str = trim($_POST['period_str']);
    
    $p_dt_b = sql_field_val('dt_b', 'mmgg');
    $p_dt_e = sql_field_val('dt_e', 'mmgg');
    
    $p_dt_b_str = ukr_date(sql_field_val('dt_b', 'text'),0,2);
    $p_dt_e_str = ukr_date(sql_field_val('dt_e', 'text'),0,1);

    $p_dt_sp_date = sql_field_val('dt_sp', 'date');
    $p_dt_input_date = sql_field_val('dt_input', 'date');    
    
    
    $p_id_paccnt = sql_field_val('id_paccnt', 'int');
    
    $p_num_sp = sql_field_val('num_sp', 'int');
    $p_dt_sp = sql_field_val('dt_sp', 'text');

    $p_num_input = sql_field_val('num_input', 'text');
    $p_dt_input = sql_field_val('dt_input', 'text');
    
    $p_people_count = sql_field_val('people_count', 'int');
    $p_heat_area = sql_field_val('heat_area', 'numeric');
    
    $p_id_person = sql_field_val('id_person', 'int');
    $p_person = sql_field_val('person', 'text');
    
    $p_hotw = sql_field_val('hotw', 'int');
    $p_hotw_gas = sql_field_val('hotw_gas', 'int');
    $p_coldw = sql_field_val('coldw', 'int');
    $p_plita = sql_field_val('plita', 'int');
    $p_show_dte = sql_field_val('show_dte', 'int');
    
    $p_protocol = sql_field_val('write_protocol', 'int');
    
    $params_caption = '';
    $where = '';
    
    $date = ukr_date($p_dt_sp);
    $dapartment = 'Міські електричні мережі';
    $director = 'Чернявський О.О.';
    */
    //$year = '2016';

//-----------------------------------------------------  
    
    $SQL = "select c.id, c.code, c.book,
        adr.town, adr.street, address_print(c.addr) as house,
        (ab.last_name||' '||coalesce(ab.name,'')||' '||coalesce(ab.patron_name,''))::varchar as abon,
        lg2.*, n.percent, n.norm_min, n.norm_one, n.norm_max, tar.ident,c.id_gtar, 
        coalesce(g.name,'-') as lgt_name,
        CASE WHEN lg2.id_grp_lgt is not null THEN  
          'надається з '|| to_char(lg2.dt_start, 'DD.MM.YYYY')||' р. розмір пільги: '||n.percent::varchar||' %.'
        ELSE '' END ::varchar as lgt_info
        from clm_paccnt_h as c 
        join (select id, max(dt_b) as dt_b from clm_paccnt_h  where 
            ((dt_b < ($p_mmgg::date+'1 month'::interval) and dt_e is null)
            or 
            tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
        ) group by id order by id) as c2 
        on (c.id = c2.id and c.dt_b = c2.dt_b)
        join clm_abon_h as ab on (ab.id = c.id_abon) 
        join (select id, max(dt_b) as dt_b from clm_abon_h  where 
            ((dt_b < ($p_mmgg::date+'1 month'::interval) and dt_e is null)
            or 
            tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
        )
        group by id order by id) as a2 on (ab.id = a2.id and ab.dt_b = a2.dt_b)
        left join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class)
  left join 
    ( select lg.id_paccnt, lg.fio_lgt, lg.ident_cod_l, lg.family_cnt, lg.id_grp_lgt, lg.id_calc , lg.dt_start
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
    ) as lg2 on (lg2.id_paccnt = c.id)
    left join aqi_grptar_tbl as tar on (tar.id = c.id_gtar)
    left join lgi_group_tbl as g on (lg2.id_grp_lgt = g.id)
    left join lgi_norm_tbl as  n on (n.id_calc = lg2.id_calc and (n.id_tar_grp = tar.id_lgt_group or n.id_tar_grp is null))
    where c.id = $p_id_paccnt; ";

    
   // throw new Exception(json_encode($SQL));
    
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        $i=0;
        while ($row = pg_fetch_array($result)) {

            
            $abn = htmlspecialchars($row['abon']);

            if ($res_code==310)
              $adr = htmlspecialchars($row['street'].' '.$row['house']);
            else
              $adr = htmlspecialchars($row['town'].', '.$row['street'].' '.$row['house']);
            
            $person_acc = $row['book'].'/'.$row['code'];
            
            $pilga = htmlspecialchars($row['lgt_name']);
            $perc = $row['percent'];
            $numb_lgperson = $row['family_cnt'];
            $norma = $row['norm_min'];
            
            $id_gtar = $row['id_gtar'];
            
            $executor = $p_person;
           
            
            $lgt_info = $row['lgt_info']; 
            
        }
        $table_txt = '';
        
        $SQL_tar = " select *, to_char(mmgg_calend, 'DD.MM.YYYY') as mmgg_txt from 
        (
         select distinct date_trunc('month',c_date)::date as mmgg_calend from calendar
         where c_date>=$p_dt_b and c_date<=$p_dt_e
         order by mmgg_calend
         ) as cl
        left join (
         select b.mmgg_bill, sum(demand) as demand 
         from acm_bill_tbl as b 
         where
         b.id_pref = 10
         and b.id_paccnt = $p_id_paccnt
         and b.mmgg_bill >=$p_dt_b and b.mmgg_bill<=$p_dt_e and b.idk_doc in (200,220)
         --    and not exists (select c.id_doc from acm_bill_tbl as c where c.id_paccnt = b.id_paccnt and c.id_corr_doc = b.id_doc)
         group by b.mmgg_bill
        ) as bb on (bb.mmgg_bill = cl.mmgg_calend)
        left join 
        (
         select p.mmgg, sum(p.value) as sum_pay 
         from acm_pay_tbl as p where 
         p.mmgg >=$p_dt_b and p.mmgg<=$p_dt_e
         and id_pref = 10 and idk_doc = 100
         and p.id_paccnt = $p_id_paccnt
         group by p.mmgg
        ) as pp on ( pp.mmgg = cl.mmgg_calend)
        order by  cl.mmgg_calend; ";
        

  
        $result_tar = pg_query($Link, $SQL_tar) or die("SQL Error: " . pg_last_error($Link) . $SQL);
        if ($result_tar) {
        
          $rows_count_tar = pg_num_rows($result_tar);
        
          $it=0;
          $im = 0;
          $demand_all=0;
          $pay_all=0;
          
          $cur_mmgg = '';
          
          while ($row_tar = pg_fetch_array($result_tar)) {
         
            $im++;  
            
            if ($it == 2)
            {
               if($cur_mmgg != '')
                   $table_txt.= "</tr>";                  
                
               $table_txt.="<tr>"; 
               $it=0;     
                
            }
          
              $demand_val = $row_tar['demand'];
              $sum_pay = $row_tar['sum_pay'];           
              
              $demand_val_txt = number_format_ua($demand_val,0);
              $sum_pay_txt = number_format_ua($sum_pay,2);
              
              $cur_mmgg_txt = ukr_date($row_tar['mmgg_txt'],0,1);
              //$cur_mmgg_txt = $row_tar['mmgg_txt'];
              
              $demand_all+=$demand_val;
              $pay_all+=$sum_pay;
              
              $table_txt.="
                <td> $cur_mmgg_txt </td>
                <td> $demand_val_txt </td>
                <td> $sum_pay_txt </td> ";        
              
             $it++; 
          }
          
         if($cur_mmgg != '')
         {
           if ($it<2)  
           {
              $table_txt.="
              <td></td>
              <td></td>
              <td></td>";
           }
           $table_txt.= "</tr>";                  
         }  
          
        }
  
        $demand_avg = round($demand_all/$im,0);
        
        $SQL_person = "select * from prs_persons where id = $p_id_person ;";
        $result_person = pg_query($Link, $SQL_person) or die("SQL Error: " . pg_last_error($Link) . $SQL);
        if ($result_person) {
        
          while ($row_person = pg_fetch_array($result_person)) {
         
                 $phone = $row_person['phone'];
          }
        }
        
      $header_text = file_get_contents("html_templates/".$template_name."_header.htm");
      $footer_text = file_get_contents("html_templates/".$template_name."_footer.htm");
        
      eval("\$header_text = \"$header_text\";");
      echo $header_text;

      echo $table_txt;
      
      eval("\$footer_text_eval = \"$footer_text\";");
      echo $footer_text_eval;
      
      echo '<div style="clear:both;" > </div>';
      echo '<br style="page-break-after: always">';
      
    }

    if ($p_protocol==1)
    {
            
            if ($p_heat_area=='') $p_heat_area ='null';
            if ($p_people_count=='') $p_people_count ='null';
            if ($p_num_input=='') $p_num_input ='null';
            if ($p_dt_input_date=="''") $p_dt_input_date ='null';
        
            $SQL = " INSERT INTO rep_spravka_tbl(
            id_paccnt, doc_type, num_sp, dt_sp, date_start, date_end, 
            num_input, dt_input, people_count, heat_area, hotw, hotw_gas, 
            coldw, plita, id_person)          
            values ($p_id_paccnt, 3, $p_num_sp, $p_dt_sp_date,$p_dt_b,$p_dt_e,
            $p_num_input, $p_dt_input_date, $p_people_count, $p_heat_area, 
            $p_hotw, $p_hotw_gas,$p_coldw, $p_plita,$p_id_person ); ";
            
            $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);            
            
    }
    
    $p_num_sp = $p_num_sp+1;     
   }
  }
   
}

//------------------------------------------------------------------------------
if ($oper=='sprav1new_list')
{
    
    $pp_mmgg = sql_field_val('dt_sp', 'mmgg');
    
    //===========
    rep_abon_dt($Link, $pp_mmgg,  2, 0);
    //=========
    
    $SQL = "select coalesce(max(num_sp),0)+1 as next_num  from rep_spravka_tbl
            where date_trunc('year', mmgg::date) = date_trunc('year',$pp_mmgg::date ) ;";
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) 
    {
        $row = pg_fetch_array($result);
        $p_num_sp = $row['next_num'];
    }
    //---------------------------------------------------
    
    $SQL_list = "select q.*, date_trunc('month', q.dt_sp) as pmmgg,
       to_char(date_trunc('month', q.dt_sp), 'DD.MM.YYYY') as pmmgg_str,
       date_part('month', q.dt_sp) as mmgg_month,
       date_part('year', q.dt_sp) as mmgg_year,
       to_char(q.dt_input, 'DD.MM.YYYY') as dt_input_txt,
       to_char(q.dt_sp, 'DD.MM.YYYY') as dt_sp_txt,
       to_char(q.date_start, 'DD.MM.YYYY') as date_start_txt,
       to_char(q.date_end, 'DD.MM.YYYY') as date_end_txt,
        p.represent_name as person,
        ('0'||substring(c.code FROM '[0-9]+'))::int as int_code,
        ('0'||substring(c.book FROM '[0-9]+'))::int as int_book   
    from rep_spravka_queue_tmp as q
    join clm_paccnt_tbl as c on (c.id = q.id_paccnt)
    left join prs_persons as p on (p.id = q.id_person)
    where id_user = $session_user order by int_book, book, int_code, code ;";
    
    
    $result_list = pg_query($Link, $SQL_list) or die("SQL Error: " . pg_last_error($Link) . $SQL_list);
    if ($result_list) {
        
    while ($row_list = pg_fetch_array($result_list)) {
    
    
    
    $p_mmgg = "'".$row_list['pmmgg']."'";
    $mmgg1_str = ukr_month($row_list['mmgg_month'],0).' '.$row_list['mmgg_year'];
    
    $p_dt_b = "'".$row_list['date_start']."'"; //sql_field_val('dt_b', 'mmgg');
    $p_dt_e = "'".$row_list['date_end']."'"; //sql_field_val('dt_e', 'mmgg');
    
    $p_dt_b_str = ukr_date($row_list['date_start_txt'],0,2);
    $p_dt_e_str = ukr_date($row_list['date_end_txt'],0,1);
    
    $p_id_paccnt = $row_list['id_paccnt']; // sql_field_val('id_paccnt', 'int');
    
    //$p_num_sp = sql_field_val('num_sp', 'int');
    $p_dt_sp = $row_list['dt_sp_txt'];// sql_field_val('dt_sp', 'text');

    $p_num_input = $row_list['num_input']; //sql_field_val('num_input', 'text');
    $p_dt_input = $row_list['dt_input_txt']; //sql_field_val('dt_input', 'text');
    
    $p_dt_sp_date = "'".$row_list['dt_sp']."'"; //sql_field_val('dt_sp', 'date');
    $p_dt_input_date = "'".$row_list['dt_input']."'"; //sql_field_val('dt_input', 'date');    
    
    $p_people_count = $row_list['people_count']; // sql_field_val('people_count', 'int');
    
    if (($p_people_count=='null')||($p_people_count=='')) $p_people_count_txt="__";
    else $p_people_count_txt="$p_people_count";

    $p_heat_area = $row_list['heat_area']; //sql_field_val('heat_area', 'numeric');
    
    $p_id_person = $row_list['id_person']; //sql_field_val('id_person', 'int');
    $p_person = $row_list['person']; //sql_field_val('person', 'text');
    
    $p_hotw = $row_list['hotw']; //sql_field_val('hotw', 'int');
    $p_hotw_gas = $row_list['hotw_gas']; //sql_field_val('hotw_gas', 'int');
    $p_coldw = $row_list['coldw']; //sql_field_val('coldw', 'int');
    $p_plita = $row_list['plita']; //sql_field_val('plita', 'int');
    
    $params_caption = '';
    $where = '';
    
    $p_protocol = sql_field_val('write_protocol', 'int');
    $p_show_norm = sql_field_val('show_norm', 'int');
    
    $date = ukr_date($p_dt_sp);
    $dapartment = $res_print_name ;// 'Міські електричні мережі';
    //$director = 'Чернявський О.О.';
    $director = $sbutboss_name;
//-----------------------------------------------------    
   
    $SQL = "select c.id, c.code, c.book, coalesce(n_subs,'') as n_subs,
        adr.town, adr.street, address_print(c.addr) as house,
        (ab.last_name||' '||coalesce(ab.name,'')||' '||coalesce(ab.patron_name,''))::varchar as abon,
        lg2.*, n.percent, n.norm_min, n.norm_one, n.norm_max, tar.ident,c.id_gtar, 
        coalesce(g.name,'-') as lgt_name,
        CASE WHEN lg2.id_grp_lgt is not null and lg2.dt_end is null THEN  
          'надається з '|| to_char(lg2.dt_start, 'DD.MM.YYYY')||' р. розмір пільги: '||n.percent::varchar||' % '::varchar
        WHEN lg2.id_grp_lgt is not null and lg2.dt_end is not null THEN  
          'надається з '|| to_char(lg2.dt_start, 'DD.MM.YYYY')||' р.'||' по '||to_char(lg2.dt_end, 'DD.MM.YYYY')||', розмір пільги: '||n.percent::varchar||' % '::varchar
        ELSE '' END ::varchar as lgt_info,
        CASE WHEN c.id_gtar in (3,5,7,12,15) THEN 'Наявність стаціонарної електроплити: так' 
             WHEN c.id_gtar in (4,6,14) THEN 'Наявність стаціонарної електроплити: ні <br/> Є в наявності електроопалення' 
             ELSE 'Наявність стаціонарної електроплити: ні ' END::varchar as additional_info,
        CASE WHEN coalesce(n.norm_min,0)<>0 THEN 'Пільгова норма споживання: '||n.norm_min::varchar||'кВтг' ELSE '' END::varchar as norm_info ,
        coalesce(d_old.summ,0) as old_sum
        from clm_paccnt_h as c 
        join (select id, max(dt_b) as dt_b from clm_paccnt_h  where 
            ((dt_b < ($p_mmgg::date+'1 month'::interval) and dt_e is null)
            or 
            tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
        ) group by id order by id) as c2 
        on (c.id = c2.id and c.dt_b = c2.dt_b)
        join clm_abon_h as ab on (ab.id = c.id_abon) 
        join (select id, max(dt_b) as dt_b from clm_abon_h  where 
            ((dt_b < ($p_mmgg::date+'1 month'::interval) and dt_e is null)
            or 
            tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
        )
        group by id order by id) as a2 on (ab.id = a2.id and ab.dt_b = a2.dt_b)
        left join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class)
  left join 
    ( select lg.id_paccnt, lg.fio_lgt, lg.ident_cod_l, lg.family_cnt, lg.id_grp_lgt, lg.id_calc , lg.dt_start,lg.dt_end
     from lgm_abon_h as lg  join
      (select lg.id_paccnt, max(lg.id_key) as id_key 
       from lgm_abon_h as lg 
        join (select id, max(dt_b) as dt_b from lgm_abon_h  where
            ((dt_b < ($p_dt_e::date+'1 month'::interval) 
             and ( dt_e is null and (dt_end is null or dt_end >= $p_dt_b )  )
            )
            or 
            tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_dt_b::timestamp::abstime,($p_dt_e::date+'1 month - 1 day'::interval)::timestamp::abstime))
        )   group by id order by id) as lg2 
        on (lg.id = lg2.id and lg.dt_b = lg2.dt_b)
      group by lg.id_paccnt
      ) as gg
      on(lg.id_key = gg.id_key)
    ) as lg2 on (lg2.id_paccnt = c.id)
    left join aqi_grptar_tbl as tar on (tar.id = c.id_gtar)
    left join lgi_group_tbl as g on (lg2.id_grp_lgt = g.id)
    left join lgi_norm_tbl as  n on (n.id_calc = lg2.id_calc and (n.id_tar_grp = tar.id_lgt_group or n.id_tar_grp is null))
    left join rep_abon_dt_tbl as d_old on (d_old.id_paccnt = c.id)
    where c.id = $p_id_paccnt; ";

   // throw new Exception(json_encode($SQL));
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        $i=0;
        while ($row = pg_fetch_array($result)) {

            
            $abn = htmlspecialchars($row['abon']);

            if ($res_code==310)
              $adr = htmlspecialchars($row['street'].' '.$row['house']);
            else
              $adr = htmlspecialchars($row['town'].', '.$row['street'].' '.$row['house']);
            
            if ($row['n_subs']!='')
               $person_acc = $row['book'].'/'.$row['code'].'('.$row['n_subs'].')';
            else
               $person_acc = $row['book'].'/'.$row['code'];
                
            $pilga = htmlspecialchars($row['lgt_name']);
            $perc = $row['percent'];
            $numb_lgperson = $row['family_cnt'];
            $norma = $row['norm_min'];
            
            $id_gtar = $row['id_gtar'];
            
            $executor = $p_person;
            
            $debet_2month_txt = number_format_ua($row['old_sum'],2);
            if (($debet_2month_txt =='0')||($debet_2month_txt ==''))
            {
                $debet_2month_txt='немає';
            }
            else
            {
                $debet_2month_txt.=' грн.';
            }
            
            $lgt_info = $row['lgt_info']; 
            $additional_info = $row['additional_info']; 
            
            $norm_info_text='';
            if (($row['norm_info'] !='')&&($p_show_norm==1))
            {
                $norm_info = $row['norm_info'];
                $norm_info_text =  "<span class='rows' style='width:600px'>{$norm_info } </span> <br/>";
            }
            
            
        }
        //-------------------------------------------------------------
        $SQL_z = "select max(id_zone) as max_zone
          from clm_meterpoint_tbl as m join 
          clm_meter_zone_tbl as z on (z.id_meter = m.id)
          where m.id_paccnt = $p_id_paccnt;";
        $max_zone = 0;
        $zone_info='';
       
        $result_z = pg_query($Link, $SQL_z) or die("SQL Error: " . pg_last_error($Link) . $SQL_z);
        if ($result_z) 
        {
          $row_z = pg_fetch_array($result_z);
          $max_zone = $row_z['max_zone'];
          
            if ($max_zone==10) 
            {
                $zone_info  ='Абонент розраховується за двозонними тарифами, диференційованими за періодами часу ';
            }
        
            if ($max_zone==8) 
            {
                $zone_info  ='Абонент розраховується за трьохзонними тарифами, диференційованими за періодами часу ';
            }
          
        }        
        //-------------------------------------------------------------
        
        $SQL_tar = "select t.id,t.name,t.lim_min, t.lim_max,
	(select value from aqd_tarif_tbl as d 
		where d.id_tarif = t.id 
		and dt_begin <=$p_mmgg::date order by dt_begin desc limit 1) as tar_val
	from aqm_tarif_tbl as t 
        where ((dt_e is null ) or (dt_e > $p_mmgg::date )) and (dt_b <= $p_mmgg::date)
        and (per_min <= $p_mmgg  and  per_max >= $p_mmgg  or per_min is null )
        and id_grptar = $id_gtar 
        order by ident  
        limit 2;";
  
        $result_tar = pg_query($Link, $SQL_tar) or die("SQL Error: " . pg_last_error($Link) . $SQL);
        if ($result_tar) {
        
          $rows_count_tar = pg_num_rows($result_tar);
        
          $it=0;
          $tar_val1=0;
          $tar_val2=0;
          $tar_dem1=0;
          $tar_dem2=0;
          
          while ($row_tar = pg_fetch_array($result_tar)) {
         
             if ($it == 0 ) 
             {
                 $tar_dem1 = $row_tar['lim_max'];
                 $tar_val1 = round($row_tar['tar_val'],4);
                 //$tar_lgt_val1 = round($tar_val1*$perc/100,4);
             }

             if ($it == 1 ) 
             {
                 $tar_dem2 = $row_tar['lim_max'];
                 $tar_val2 = round($row_tar['tar_val'],4);
                 //$tar_lgt_val2 = round($tar_val2*$perc/100,4);
             }
             
             $it++; 
          }
        }
        
        $tarif_info = "до $tar_dem1 кВтг – $tar_val1 грн за 1 кВтг,  понад $tar_dem1 кВт – $tar_val2 грн за 1 кВтг "; 
        
        //-------------------------------------------------------------
        
        $phone ='';
        $SQL_person = "select * from prs_persons where id = $p_id_person ;";
        $result_person = pg_query($Link, $SQL_person) or die("SQL Error: " . pg_last_error($Link) . $SQL);
        if ($result_person) {
        
          while ($row_person = pg_fetch_array($result_person)) {
         
                 $phone = $row_person['phone'];
          }
        }
        
      $header_text = file_get_contents("html_templates/".$template_name."_header.htm");
      $footer_text = file_get_contents("html_templates/".$template_name."_footer.htm");
        
      eval("\$header_text = \"$header_text\";");
      echo $header_text;

      //echo $table_txt;
      
      eval("\$footer_text_eval = \"$footer_text\";");
      echo $footer_text_eval;
      
      echo '<div style="clear:both;" > </div>';
      echo '<br style="page-break-after: always">';
      
      
      if ($p_protocol==1)
      {
            if ($p_heat_area=='') $p_heat_area ='null';
            if ($p_people_count=='') $p_people_count ='null';
            if ($p_num_input=='') $p_num_input ='null';
            if ($p_dt_input_date=="''") $p_dt_input_date ='null';
         
            $SQL = " INSERT INTO rep_spravka_tbl(
            id_paccnt, doc_type, num_sp, dt_sp, date_start, date_end, 
            num_input, dt_input, people_count, heat_area, hotw, hotw_gas, 
            coldw, plita, show_norm, id_person)          
            values ($p_id_paccnt, 4, $p_num_sp, $p_dt_sp_date,$p_dt_b,$p_dt_e,
            $p_num_input, $p_dt_input_date, $p_people_count, $p_heat_area, 
            $p_hotw, $p_hotw_gas,$p_coldw, $p_plita, $p_show_norm, $p_id_person ); ";
            
            $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);            
            
      }
      
    }
    $p_num_sp = $p_num_sp+1;     
   }
  }
   
}

//------------------------------------------------------------------------------
if ($oper=='spravlgt')
{
    $p_mmgg = sql_field_val('dt_sp', 'mmgg');
    $mmgg1_str = trim($_POST['period_str']);
    
    $p_dt_b = sql_field_val('dt_b', 'mmgg');
    $p_dt_e = sql_field_val('dt_e', 'mmgg');
    
    $p_dt_b_str = ukr_date(sql_field_val('dt_b', 'text'),0,2);
    $p_dt_e_str = ukr_date(sql_field_val('dt_e', 'text'),0,1);

    $p_dt_sp_date = sql_field_val('dt_sp', 'date');
    $p_dt_input_date = sql_field_val('dt_input', 'date');    
    
    $p_id_paccnt = sql_field_val('id_paccnt', 'int');
    
    $p_num_sp = sql_field_val('num_sp', 'int');
    $p_dt_sp = sql_field_val('dt_sp', 'text');

    $p_num_input = sql_field_val('num_input', 'text');
    $p_dt_input = sql_field_val('dt_input', 'text');
    
    $p_id_person = sql_field_val('id_person', 'int');
    $p_person = sql_field_val('person', 'text');
    
    $p_protocol = sql_field_val('write_protocol', 'int');
    
    $params_caption = '';
    $where = '';
    
    if ($res_code==310)
        $lgt_caption = 'сума компенсації:';
    else
        $lgt_caption = 'еквівалент пільги:';
            
    $date = ukr_date($p_dt_sp);
    $dapartment = $res_print_name ;// 'Міські електричні мережі';
    $boss = $warningboss_name;
    $dep_street = $res_addr; //'Проспект Перемоги, 126';
    //$dep_street ='Проспект Перемоги, 126';

//===========
  if ($archive_print==0)
  {
    
    $SQL = "select coalesce(max(num_sp),0)+1 as next_num  from rep_spravka_tbl
            where date_trunc('year', mmgg::date) = date_trunc('year',$p_mmgg::date ) and doc_type = 5 ;";
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) 
    {
        $row = pg_fetch_array($result);
        if ($row['next_num'] > $p_num_sp)
        {
            $p_num_sp = $row['next_num'];
        }
    }
  }    
//-----------------------------------------------------  
    
    $SQL = "select c.id, c.code, c.book,
        adr.town, adr.street, address_print(c.addr) as house,
        (ab.last_name||' '||coalesce(ab.name,'')||' '||coalesce(ab.patron_name,''))::varchar as abon,
        lg2.*, coalesce(lg2.id_lgt,0) as id_lgt0,  n.percent, n.norm_min, n.norm_one, n.norm_max, tar.ident,c.id_gtar, 
        coalesce(g.name,'-') as lgt_name,
        to_char(lg2.dt_start, 'DD.MM.YYYY')||' р.' as start_lgt,
        to_char(lg2.dt_end, 'DD.MM.YYYY')||' р.' as end_lgt,
        CASE WHEN c.id_gtar in (3,5,7,12,15) THEN 'Наявність стаціонарної електроплити: так' 
             WHEN c.id_gtar in (4,6,14) THEN 'Наявність стаціонарної електроплити: ні <br/> Є в наявності електроопалення' 
             ELSE 'Наявність стаціонарної електроплити: ні ' END::varchar as additional_info
        from clm_paccnt_h as c 
        join (select id, max(dt_b) as dt_b from clm_paccnt_h  where 
            ((dt_b < ($p_mmgg::date+'1 month'::interval) and dt_e is null)
            or 
            tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
        ) group by id order by id) as c2 
        on (c.id = c2.id and c.dt_b = c2.dt_b)
        join clm_abon_h as ab on (ab.id = c.id_abon) 
        join (select id, max(dt_b) as dt_b from clm_abon_h  where 
            ((dt_b < ($p_mmgg::date+'1 month'::interval) and dt_e is null)
            or 
            tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
        )
        group by id order by id) as a2 on (ab.id = a2.id and ab.dt_b = a2.dt_b)
        left join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class)
  left join 
    ( select lg.id as id_lgt, lg.id_paccnt, lg.fio_lgt, lg.ident_cod_l, lg.family_cnt, lg.id_grp_lgt, lg.id_calc , lg.dt_start, lg.dt_end
     from lgm_abon_h as lg  join
      (select lg.id_paccnt, max(lg.id_key) as id_key 
       from lgm_abon_h as lg 
        join (select id, max(dt_b) as dt_b from lgm_abon_h  where
            ((dt_b < ($p_dt_e::date+'1 month'::interval) 
             and ( dt_e is null and (dt_end is null or dt_end >= $p_dt_b )  )
            )
            or 
            tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_dt_b::timestamp::abstime,($p_dt_e::date+'1 month - 1 day'::interval)::timestamp::abstime))
        )   group by id order by id) as lg2 
        on (lg.id = lg2.id and lg.dt_b = lg2.dt_b)
      group by lg.id_paccnt
      ) as gg
      on(lg.id_key = gg.id_key)
    ) as lg2 on (lg2.id_paccnt = c.id)
    left join aqi_grptar_tbl as tar on (tar.id = c.id_gtar)
    left join lgi_group_tbl as g on (lg2.id_grp_lgt = g.id)
    left join lgi_norm_tbl as  n on (n.id_calc = lg2.id_calc and (n.id_tar_grp = tar.id_lgt_group or n.id_tar_grp is null))
    where c.id = $p_id_paccnt; ";

    
   // throw new Exception(json_encode($SQL));
    
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        $i=0;
        while ($row = pg_fetch_array($result)) {

            
            $abn = htmlspecialchars($row['abon']);

            if ($res_code==310)
              $adr = htmlspecialchars($row['street'].' '.$row['house']);
            else
              $adr = htmlspecialchars($row['town'].', '.$row['street'].' '.$row['house']);
            
            $person_acc = $row['book'].'/'.$row['code'];
            
            $fio_lgt = $row['fio_lgt']; 
            $additional_info = $row['additional_info']; 
            
            $pilga = htmlspecialchars($row['lgt_name']);
            $perc = $row['percent'];
            $numb_lgperson = $row['family_cnt'];
            
            $id_lgt = $row['id_lgt0'];
            
            $executor = $p_person;
           
            $start_lgt = $row['start_lgt']; 
            $end_lgt = $row['end_lgt'];             
            
        }
        
        $family="<span class='span_under' style='width:630px;'>$fio_lgt</span> <br/> ";        
        $rows_count_fam=0;
        $SQL_fam = " select * from lgm_family_tbl where id_lgt = $id_lgt order by fio;";
  
        $result_fam = pg_query($Link, $SQL_fam) or die("SQL Error: " . pg_last_error($Link) . $SQL);
        if ($result_fam) {
        
          $rows_count_fam = pg_num_rows($result_fam)+1;
          $im=0;
          while ($row_fam = pg_fetch_array($result_fam)) {
         
              $fio = $row_fam['fio'];

              $family.="<span class='span_under' style='width:630px;'>$fio</span> <br/> ";        
              
          }
         
          
        }        
        for ($i=$rows_count_fam;$i<=9;$i++){
            $family.= "<span class='span_under' style='width:630px;'>&nbsp;</span> <br/> ";
        }
        
        
        $table_txt = '';
        $SQL_dem = "   select coalesce(demand,0) as demand, coalesce(demand_lgt,0) as demand_lgt,
                       coalesce(summ_lgt,0)  as summ_lgt, mmgg_month, mmgg_year,
                       coalesce(summ_grn,0) as summ_grn,  coalesce(summ_cop,'0') as summ_cop,
                       to_char(mmgg_calend, 'DD.MM.YYYY') as mmgg_txt from
                (
         select distinct date_trunc('month',c_date)::date as mmgg_calend from calendar
         where c_date>=$p_dt_b and c_date<=$p_dt_e
         order by mmgg_calend
         ) as cl
         left join
         (
        select b.mmgg_bill, sum(b.demand) as demand ,
    coalesce(sum(bs.demand_lgt),0) as demand_lgt, sum(bs.summ_lgt) as summ_lgt,
    date_part('month',b.mmgg_bill) as mmgg_month, date_part('year',b.mmgg_bill) as mmgg_year ,
    coalesce(trunc(sum(bs.summ_lgt))::int,0) as summ_grn,
   to_char(coalesce(((sum(bs.summ_lgt) - trunc(sum(bs.summ_lgt)))*100)::int,0), '00') as summ_cop
    from acm_bill_tbl as b 
    left join
     (select s.id_doc, sum(s.demand) as demand, sum(s.demand_lgt) as demand_lgt, sum(s.summ_lgt) as summ_lgt
     from acm_lgt_summ_tbl as s 
     join acm_bill_tbl as b on (b.id_doc = s.id_doc)
     where b.mmgg_bill >=$p_dt_b and b.mmgg_bill<=$p_dt_e
     and b.id_pref = 10
     and b.id_paccnt = $p_id_paccnt
     group by s.id_doc
    ) as bs on (bs.id_doc = b.id_doc)
    where
     b.id_pref = 10
    and b.id_paccnt = $p_id_paccnt
    and b.mmgg_bill >=$p_dt_b and b.mmgg_bill<=$p_dt_e
    and b.idk_doc in (200,220)
    group by b.mmgg_bill
    ) as b 
    on ( b.mmgg_bill = cl.mmgg_calend)
    order by  cl.mmgg_calend; ";
 
        $result_dem = pg_query($Link, $SQL_dem) or die("SQL Error: " . pg_last_error($Link) . $SQL);
        if ($result_dem) {
        
          $rows_count_dem = pg_num_rows($result_dem);
        
          while ($row_dem = pg_fetch_array($result_dem)) {
         
              $demand_val = $row_dem['demand'];
              $demand_lgt = $row_dem['demand_lgt'];
              $summ_lgt_grn = $row_dem['summ_grn'];
              $summ_lgt_cop = $row_dem['summ_cop'];
              $cur_mmgg_txt = ukr_date($row_dem['mmgg_txt'],0,1);
              
              $table_txt.="
               <tr style='margin-top: 10px; height:20px'>
                <td>{$cur_mmgg_txt} </td>
                <td>{$demand_val} кВтг</td>                
                <td style='padding-left: 15px;'>{$demand_lgt} кВтг</td>
                <td style='padding-left: 15px;'>на суму {$summ_lgt_grn} грн {$summ_lgt_cop} коп</td>
                </tr> ";

          }
          
        }
  
        $phone='';
        $position='';
        $SQL_person = "select coalesce(p.phone,'') as phone, coalesce(lower(r.name),'') as pos_name,
           coalesce(CASE WHEN p.represent_name ~ '^(.+)\\\\s(.)\\\\.(.)\\\\.$' THEN 
             substr(trim(p.represent_name),length(p.represent_name)-3,4)||' '||substr(trim(p.represent_name),0,length(p.represent_name)-4) 
               ELSE p.represent_name END,'') as represent_name 
                       from prs_persons as p 
               left join prs_posts as r on (p.id_post = r.id)
        where p.id = $p_id_person ;";
        $result_person = pg_query($Link, $SQL_person) or die("SQL Error: " . pg_last_error($Link) . $SQL);
        if ($result_person) {
        
          while ($row_person = pg_fetch_array($result_person)) {
         
                 $phone = $row_person['phone'];
                 $position=$row_person['pos_name'];
                 $executor=$row_person['represent_name'];
          }
        }
        $boss='';
        if ($executor=='null') $executor='';
/*        
        $SQL_person = "select r.name as pos_name,
           CASE WHEN p.represent_name ~ '^(.+)\\\\s(.)\\\\.(.)\\\\.$' THEN 
             substr(trim(p.represent_name),length(p.represent_name)-3,4)||' '||substr(trim(p.represent_name),0,length(p.represent_name)-4) 
               ELSE p.represent_name END as represent_name 
                       from prs_persons as p 
               left join prs_posts as r on (p.id_post = r.id)
        where (r.ident = 'pobut_slav' and $res_code=330 ) or 
              (r.ident = 'pobut_boss' and $res_code=310 ) or
              (trim(r.ident) = 'zbut_boss' and $res_code not in (310,330) ) ;";
*/     
      if ($spravboss_id!='')
      {
        $SQL_person = "select r.name as pos_name,
           CASE WHEN p.represent_name ~ '^(.+)\\\\s(.)\\\\.(.)\\\\.$' THEN 
             substr(trim(p.represent_name),length(p.represent_name)-3,4)||' '||substr(trim(p.represent_name),0,length(p.represent_name)-4) 
               ELSE p.represent_name END as represent_name 
               from prs_persons as p 
               left join prs_posts as r on (p.id_post = r.id)
        where p.id = $spravboss_id  ;";
        
        $result_person = pg_query($Link, $SQL_person) or die("SQL Error: " . pg_last_error($Link) . $SQL);
        if ($result_person) {
        
          while ($row_person = pg_fetch_array($result_person)) {
         
                 $boss=$row_person['represent_name'];
                 $boss_pos = $row_person['pos_name'];
          }
        }
      }
      if ($res_code==310)
      {
       $boss_pos = 'Начальник відділу по роботі <br/>з побутовими споживачами';
      }
        
      eval("\$header_text = \"$header_text\";");
      echo $header_text;

      echo $table_txt;
      
      eval("\$footer_text_eval = \"$footer_text\";");
      echo $footer_text_eval;
      
    }

    if ($p_protocol==1)
    {
            $SQL = " INSERT INTO rep_spravka_tbl(
            id_paccnt, doc_type, num_sp, dt_sp, date_start, date_end, 
            num_input, dt_input, people_count, heat_area, hotw, hotw_gas, 
            coldw, plita, id_person)          
            values ($p_id_paccnt, 5, $p_num_sp, $p_dt_sp_date,$p_dt_b,$p_dt_e,
            '{$p_num_input}', $p_dt_input_date, null, null, 
            null, null,null, null,$p_id_person ); ";
            
            $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);            
            
    }
    
   
}
//------------------------------------------------------------------------------
if ($oper=='sprav_list')
{

    $p_dt_sp = sql_field_val('dt_sp', 'date');    
    $p_dt_txt = sql_field_val('dt_sp', 'text');    
    
    //разбор и обработка строки параметров jqgrid
    $qWhere='';
    if (isset($_POST['grid_params'])) {
          $grid_params_json = $_POST['grid_params'];
          $json_str = stripslashes($grid_params_json); 
          $grid_params = json_decode($json_str,true);
          
          
          $fildsArray =DbGetFieldsArray($Link,'rep_spravka_queue_tmp');
          $fildsArray['book'] = array('f_name' => 'book', 'f_type' => 'char');
          $fildsArray['code'] = array('f_name' => 'code', 'f_type' => 'char');
          $fildsArray['addr'] = array('f_name' => 'addr', 'f_type' => 'character varying');
          $fildsArray['abon'] = array('f_name' => 'abon', 'f_type' => 'character varying');
          $qWhere= DbBuildWhere($grid_params,$fildsArray);

    }
    if ($qWhere!='') $qWhere=$qWhere.' and ';
    else $qWhere=' where ';

    $qWhere=$qWhere." id_user = $session_user ";
    
    
    $params_caption = '';

    $sum_lgt = 0;

    $i=1;
    
    eval("\$header_text = \"$header_text\";");
    echo_html ($header_text);
    
    $SQL = "select * from ( select ch.*,  acc.book, acc.code, 
 (adr.street||' '||
   (coalesce('буд.'||(acc.addr).house||'','')||
		coalesce('/'||(acc.addr).slash||' ','')||
			coalesce(' корп.'||(acc.addr).korp||'','')||
				coalesce(', кв. '||(acc.addr).flat,'')||
					coalesce('/'||(acc.addr).f_slash||' ',''))::varchar
)::varchar as addr,
(c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon,
('0'||substring(acc.code FROM '[0-9]+'))::int as int_code,
('0'||substring(acc.book FROM '[0-9]+'))::int as int_book,
to_char(ch.dt_sp, 'DD.MM.YYYY') as dt_sp_txt,
to_char(ch.date_start, 'DD.MM.YYYY') as date_start_txt,    
to_char(ch.date_end, 'DD.MM.YYYY') as date_end_txt    
from rep_spravka_queue_tmp as ch
join clm_paccnt_tbl as acc on (acc.id = ch.id_paccnt)
join clm_abon_tbl as c on (c.id = acc.id_abon) 
join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class)
) as ss    
  $qWhere Order by int_book, book ,int_code ,code ;";    
    
   // throw new Exception(json_encode($SQL));
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        $i=1;
        while ($row = pg_fetch_array($result)) {

            $abon = htmlspecialchars($row['abon']);
            $addr = htmlspecialchars($row['addr']);
            $code = $row['book'].'/'.$row['code'];
           
            echo_html( "
            <tr >
            <td>{$i}</td>            
            <td class='c_t'>{$code}</td>
            <td class='c_t'>$addr</td>
            <td class='c_t'>$abon</td>
            <td class='c_t'>{$row['num_sp']}</td>
            <td class='c_t'>{$row['dt_sp_txt']}</td>
            <td class='c_t'>{$row['date_start_txt']}</td>
            <td class='c_t'>{$row['date_end_txt']}</td>
            </tr>  ");        
            $i++;
            
        }
        
    }
    
    eval("\$footer_text_eval = \"$footer_text\";");
    echo_html( $footer_text_eval);

}
//------------------------------------------------------------------
print ('</body> </html>');
?>
