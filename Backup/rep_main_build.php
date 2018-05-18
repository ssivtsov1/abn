<?php

header('Content-type: text/html; charset=utf-8');

require 'abon_en_func.php';
require 'abon_ded_func.php';
/** PHPExcel_IOFactory */
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
 
define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');

date_default_timezone_set('Europe/London');
set_time_limit(6000); 
ini_set('memory_limit', '500M');
require_once '../Classes/PHPExcel/IOFactory.php';
//require_once '../Classes/PHPExcel/Cell/AdvancedValueBinder.php';
/*
$cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
$cacheSettings = array( 'memoryCacheSize ' => '256MB');
PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
*/
session_name("session_kaa");
session_start();

//error_reporting(1);

$Link = get_database_link($_SESSION);
$session_user = $_SESSION['ses_usr_id'];


if (isset($_POST['submitButton'])) {
      $oper = $_POST['submitButton'];
}
 else {
    
    if (isset($_POST['oper'])) {
          $oper = $_POST['oper'];
    }
     
}
//------------------------------------------------------------
$SQL = "select r.*, address_print_full(r.addr,4) as addr_full,
 b.name as ae_bank_name, 
 boss.represent_name as boss_name,    
 buh.represent_name as buh_name,
 av.fulladr as addr_district_name 
    from syi_resinfo_tbl as r
    left join bank as b on (b.mfo = r.ae_mfo)
    left join prs_persons as boss on (boss.id = r.id_boss)
    left join prs_persons as buh on (buh.id = r.id_buh)
    left join adv_fulladdr_tbl as av on (av.id = r.addr_district)
    where r.id_department = syi_resid_fun() ;";

$result = pg_query($Link,$SQL);
 if ($result) {
    $row = pg_fetch_array($result);
    
    $res_name =  $row['name'];
    $res_short_name =  $row['short_name'];
    //echo $res_short_name;
    $res_addr =  $row['addr_full'];
    $res_code =  $row['code'];
    
    $boss_name =  $row['boss_name'];
    $buh_name =  $row['buh_name'];
 }

//-------------------------------------------------------------------



if ($oper=='rep1')
{
 
    $objReader = PHPExcel_IOFactory::createReader('Excel5');
    $objPHPExcel = $objReader->load("../XL/clientlist.xls");
    
    $Sheet=$objPHPExcel->getActiveSheet();
   // PHPExcel_Cell::setValueBinder( new PHPExcel_Cell_AdvancedValueBinder() );

    //to_char(acc.dt_b, 'DD.MM.YYYY') as dt_b,
    
    $SQL = "select acc.id, acc.book, acc.code, acc.note, acc.archive,to_char(acc.dt_b, 'DD.MM.YYYY') as dt_b,
    regexp_replace(regexp_replace(acc.code, '-.*?$', '') , '[^0-9]', '','g')::int as int_code, tar.sh_nm as gtar,
    (adr.adr||' '||
    (coalesce('буд.'||(acc.addr).house||'','')||
		coalesce('/'||(acc.addr).slash||' ','')||
			coalesce((acc.addr).korp||'','')||
				coalesce(', кв. '||(acc.addr).flat,'')||
					coalesce('/'||(acc.addr).f_slash||' ',''))::varchar
    )::varchar as addr,
    (c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon
    from clm_paccnt_tbl as acc 
    join clm_abon_tbl as c on (c.id = acc.id_abon) 
    left join adt_addr_tbl as adr on (adr.id = (acc.addr).id_class)
    left join aqi_grptar_tbl as tar on (tar.id = acc.id_gtar)         
    order by acc.book, int_code limit 100; ";

   // throw new Exception(json_encode($SQL));

    $callStartTime = microtime(true);
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        $i = 0;
        $baseRow = 7;
        //echo 'Start row inserting';
        $Sheet->insertNewRowBefore($baseRow,$rows_count);

        $callEndTime = microtime(true);
        $callTime = $callEndTime - $callStartTime;
        //echo 'Call time to insert rows ' , sprintf('%.4f',$callTime) , " seconds" , EOL;
        $callStartTime = microtime(true);
        
        while ($row = pg_fetch_array($result)) {

            
            $r = $baseRow + $i;
            //$Sheet->insertNewRowBefore($r,1);
            
       	    //$Sheet->setCellValue('B'.$r, $i+1)
	    //                              ->setCellValue('C'.$r, $row['book'])
	    //                              ->setCellValue('D'.$r, $row['code'])
            //                              ->setCellValue('E'.$r, $row['abon'])
            //                              ->setCellValue('F'.$r, $row['addr'])
            //                              ->setCellValue('H'.$r, $row['note']);

            $Sheet->setCellValueByColumnAndRow(1,$r,$i+1)
                                          ->setCellValueByColumnAndRow(2,$r,$row['book'])
                                          ->setCellValueByColumnAndRow(3,$r,$row['code'])
                                          ->setCellValueByColumnAndRow(4,$r,$row['abon'])
                                         // ->setCellValueByColumnAndRow(4,$r,'"'.$row['abon'].'"')
                                          ->setCellValueByColumnAndRow(5,$r,$row['addr'])
                                          ->setCellValueByColumnAndRow(6,$r,$row['gtar'])
                                          ->setCellValueByColumnAndRow(7,$r,$row['note'])
                                          ->setCellValueByColumnAndRow(8,$r,$row['dt_b']);

            $i++;
        }
    }
    $callEndTime = microtime(true);
    $callTime = $callEndTime - $callStartTime;
    //echo 'Call time to fill workbook ' , sprintf('%.4f',$callTime) , " seconds" , EOL;
    $callStartTime = microtime(true);
    
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    $objWriter->setPreCalculateFormulas(false);
    //$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save("../tmp/clientlist.xls");
    //$objWriter->save('php://output');
    $callEndTime = microtime(true);
    $callTime = $callEndTime - $callStartTime;
    //echo 'Call time to write workbook ' , sprintf('%.4f',$callTime) , " seconds" , EOL;

    echo_result(1, 'clientlist.xls');
    return;
    
}

if ($oper=='zvit')
{
    $p_mmgg = sql_field_val('dt_b', 'date');
    
    list($year, $month , $day ) = split('[/.-]', $p_mmgg);
    //$mmgg_date = strtotime($p_mmgg);
    //$dateInfo = date_parse_from_format('y-m-d', $p_mmgg);
    //echo $p_mmgg;
    //echo $mmgg_date;
    
    //$month=date_format($mmgg_date,"n");
    //$year=date_format($mmgg_date,"y");
    
    //$year=$dateInfo['year'];
    //$month=$dateInfo['month'];
    
    $month_str = ukr_month((int)$month,0);
    
    $objReader = PHPExcel_IOFactory::createReader('Excel5');
    $objPHPExcel = $objReader->load("../XL/zvit.xls");
    $Sheet=$objPHPExcel->getActiveSheet();
    //echo "за $month_str $year р.";
    $Sheet->setCellValueByColumnAndRow(1,3,"за $month_str $year р.");
    $Sheet->setCellValueByColumnAndRow(1,1,$res_short_name);
    
   // PHPExcel_Cell::setValueBinder( new PHPExcel_Cell_AdvancedValueBinder() );

    //to_char(acc.dt_b, 'DD.MM.YYYY') as dt_b,
    $SQL = "select rep_zvit_fun($p_mmgg);";
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    
    $SQL = "select * from rep_zvit_tbl where mmgg =$p_mmgg  order by num; ";

   // throw new Exception(json_encode($SQL));

    $callStartTime = microtime(true);
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        $i = 0;
        $baseRow = 9;
        //echo 'Start row inserting';
        $Sheet->insertNewRowBefore($baseRow,$rows_count);

        $callEndTime = microtime(true);
        $callTime = $callEndTime - $callStartTime;
        //echo 'Call time to insert rows ' , sprintf('%.4f',$callTime) , " seconds" , EOL;
        $callStartTime = microtime(true);
        
        while ($row = pg_fetch_array($result)) {

            
            $r = $baseRow + $i;
            //$Sheet->insertNewRowBefore($r,1);
            
            $Sheet->setCellValueByColumnAndRow(1,$r,$row['caption'])
                                          ->setCellValueByColumnAndRow(2,$r,$row['unit'])
                                          ->setCellValueByColumnAndRow(3,$r,$row['sum_all'])
                                          ->setCellValueByColumnAndRow(4,$r,$row['town_all'])
                                          ->setCellValueByColumnAndRow(5,$r,$row['town_stove'])
                                          ->setCellValueByColumnAndRow(6,$r,$row['town_heat'])
                                          ->setCellValueByColumnAndRow(7,$r,$row['town_other'])
                                          ->setCellValueByColumnAndRow(8,$r,$row['village_all'])
                                          ->setCellValueByColumnAndRow(9,$r,$row['village_stove'])
                                          ->setCellValueByColumnAndRow(10,$r,$row['village_heat'])
                                          ->setCellValueByColumnAndRow(11,$r,$row['village_other']);
                    
            $i++;
        }
    }
    $callEndTime = microtime(true);
    $callTime = $callEndTime - $callStartTime;
    //echo 'Call time to fill workbook ' , sprintf('%.4f',$callTime) , " seconds" , EOL;
    $callStartTime = microtime(true);
    
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    $objWriter->setPreCalculateFormulas(false);
    //$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save("../tmp/zvit.xls");
    //$objWriter->save('php://output');
    $callEndTime = microtime(true);
    $callTime = $callEndTime - $callStartTime;
    //echo 'Call time to write workbook ' , sprintf('%.4f',$callTime) , " seconds" , EOL;

    echo_result(1, 'zvit.xls');
    return;
    
}
//------------------------- оборот по 1 абоненту ----------------------
if ($oper=='oborab')
{
    $objReader = PHPExcel_IOFactory::createReader('Excel5');
    $objPHPExcel = $objReader->load("../XL/oborab.xls");
    $Sheet=$objPHPExcel->getActiveSheet();   
    $p_mmgg1 = sql_field_val('dt_b', 'date');
    $p_mmgg2 = sql_field_val('dt_e', 'date');
    
    $mmgg1 = strtotime(str_replace("'",'',$p_mmgg1));
    $mmgg1_str = date('m.Y',$mmgg1);

    //$mmgg2 = DateTime::createFromFormat('Y-m-d', $p_mmgg2);
    $mmgg2 = strtotime(str_replace("'",'',$p_mmgg2));
    //$mmgg2_str = $mmgg2->format('m.Y');
    $mmgg2_str = date('m.Y',$mmgg2);
    
    $p_id_paccnt = sql_field_val('id_paccnt', 'int');
    
    
    $SQL = "select acc.id, acc.book, acc.code,address_print_full(acc.addr,4) as addr_str,
    (c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon,
    -round(s.b_val,2) as saldo_b,acc.id_gtar, tar.id_lgt_group
     from clm_paccnt_tbl as acc 
    join clm_abon_tbl as c on (c.id = acc.id_abon) 
    left join adt_addr_tbl as adr on (adr.id = (acc.addr).id_class)
    left join aqi_grptar_tbl as tar on (tar.id = acc.id_gtar)
    left join acm_saldo_tbl as s on (s.id_paccnt = acc.id and s.mmgg = $p_mmgg1 and s.id_pref = 10)
    where acc.id = $p_id_paccnt ";

   // throw new Exception(json_encode($SQL));
    $callStartTime = microtime(true);
     
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
    where l.id_paccnt = $p_id_paccnt and l.dt_end is null and n.dt_e is null; ";

   // throw new Exception(json_encode($SQL));
    //$callStartTime = microtime(true);
     
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    
    if ($result) {
        $row = pg_fetch_array($result);
    
        $lgt_name =  $row['name'];
        $lgt_norm =  $row['norm_abon'];
     } 
    
    $Sheet->setCellValueByColumnAndRow(12,2,"з $mmgg1_str по $mmgg2_str"); 
    $Sheet->setCellValueByColumnAndRow(4,3,"$book/$code $abon_name"); 
    $Sheet->setCellValueByColumnAndRow(4,4,$addr); 
    $Sheet->setCellValueByColumnAndRow(4,6,$lgt_name); 
    $Sheet->setCellValueByColumnAndRow(13,6,$lgt_norm); 
    $Sheet->setCellValueByColumnAndRow(4,7,"$mmgg1_str абонента = $saldo_b , субсидії = "); 
     
     
    $SQL = "select s.mmgg, -b_val as b_val, -b_valtax as b_valtax, dt_val, dt_valtax, 
       kt_val, kt_valtax, -e_val as e_val, -e_valtax as e_valtax ,-e_valtax as e_valtax ,ps.subs_pay, ps.subs_recalc,
       sb.subs_month 
     from acm_saldo_tbl as s
     left join (
       select mmgg, round(sum( CASE WHEN p.idk_doc = 110 THEN value END ) ,2) as subs_pay,
                         round(sum( CASE WHEN p.idk_doc in (193,194) THEN value END ) ,2) as subs_recalc
       from acm_pay_tbl as p
       where p.id_paccnt = $p_id_paccnt
       and p.id_pref = 10 and p.idk_doc in ( 110, 193, 194) 
       group by mmgg
     ) as ps on (ps.mmgg = s.mmgg)
    left join acm_subs_tbl as sb on (sb.id_paccnt = s.id_paccnt and sb.mmgg = s.mmgg)
    
     where s.id_paccnt = $p_id_paccnt
     and s.mmgg >= $p_mmgg1 and s.mmgg <= $p_mmgg2
     order by s.mmgg;";
     
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);    
    
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        $i = 0;
        $baseRow = 13;
        //echo 'Start row inserting';
        //$Sheet->insertNewRowBefore($baseRow,$rows_count);

        $callEndTime = microtime(true);
        $callTime = $callEndTime - $callStartTime;
        //echo 'Call time to insert rows ' , sprintf('%.4f',$callTime) , " seconds" , EOL;
        $callStartTime = microtime(true);
        
        while ($row = pg_fetch_array($result)) {

            $month = $row['mmgg'];
            
            $r = $baseRow + $i++;
            $str_month = $r;
            $Sheet->insertNewRowBefore($str_month, 1);
            
            $kvt_all_mmgg = 0;
            $summ_all_mmgg = 0;

            $kvt_lgt_mmgg = 0;
            $summ_lgt_mmgg = 0;

            $kvt_norm_mmgg = 0;
            $summ_norm_mmgg = 0;

            $kvt_over_mmgg = 0;
            $summ_over_mmgg = 0;
            
            $Sheet->setCellValueByColumnAndRow(1, $r, $month);
            

            $SQLz = "select distinct i.id_zone,z.nm
            from acm_indication_tbl as i
            join eqk_zone_tbl as z on (z.id = i.id_zone)
            where i.id_paccnt = $p_id_paccnt
            and i.mmgg = '{$month}'
            order by i.id_zone ";

            $result_z = pg_query($Link, $SQLz) or die("SQL Error: " . pg_last_error($Link) . $SQL);
            $rows_z = pg_num_rows($result_z);

            while ($row_z = pg_fetch_array($result_z)) {

                if($rows_z>1)
                {
                    $r = $baseRow + $i++;
                    $Sheet->insertNewRowBefore($r, 1);
                }    
                
                $id_zone = $row_z['id_zone'];
                $name_zone = $row_z['nm'];
                
                $str_zone = $r;
                
                $SQL2 = "select i.dat_ind, i.id_meter,i.num_eqp,i.id_zone,z.nm,
                i.value,  ip.value as value_prev, i.value_cons
                from acm_indication_tbl as i
                join eqk_zone_tbl as z on (z.id = i.id_zone)
                left join acm_indication_tbl as ip on (i.id_prev = ip.id)
                where i.id_paccnt = $p_id_paccnt and z.id = $id_zone
                and i.mmgg = '{$month}'
                order by z.nm,i.num_eqp, i.dat_ind; ";

                $result_ind = pg_query($Link, $SQL2) or die("SQL Error: " . pg_last_error($Link) . $SQL);
                $rows_ind = pg_num_rows($result_ind);

                //$Sheet->setCellValueByColumnAndRow(1, $r, $month);

                if ($rows_ind <= 1) {
                    $row_ind = pg_fetch_array($result_ind);

                    $Sheet->setCellValueByColumnAndRow(2, $r, $row_ind['value'])
                            ->setCellValueByColumnAndRow(3, $r, $row_ind['value_prev'])
                            ->setCellValueByColumnAndRow(4, $r, $row_ind['nm'])
                            ->setCellValueByColumnAndRow(6, $r, $row_ind['value_cons']);
                    $kvt_all_mmgg+=$row_ind['value_cons'];
                } else {
                    $kvt_all_ind = 0;
                    //$r = $baseRow + $i++;
                    //$Sheet->insertNewRowBefore($r, 1);
                    
                    while ($row_ind = pg_fetch_array($result_ind)) {

                        $r = $baseRow + $i++;
                        $Sheet->insertNewRowBefore($r, 1);
                        
                        $Sheet->setCellValueByColumnAndRow(2, $r, $row_ind['value'])
                                ->setCellValueByColumnAndRow(3, $r, $row_ind['value_prev'])
                                ->setCellValueByColumnAndRow(4, $r, $row_ind['nm'])
                                ->setCellValueByColumnAndRow(6, $r, $row_ind['value_cons']);

                        $kvt_all_ind+=$row_ind['value_cons'];
                        //$i++;
                    }
                    $Sheet->setCellValueByColumnAndRow(6, $str_zone, $kvt_all_ind);
                    $kvt_all_mmgg+=$kvt_all_ind;
                }

                $SQL3 = "select bs.id_zone,bs.id_summtarif, round(td.value*z.koef,4) as tarif_val,
                sum(bs.demand) as demand, sum(bs.summ) as summ, 
                sum(lg.demand_lgt) as demand_lgt, sum(lg.summ_lgt) as summ_lgt, max(lg.id_grp_lgt) as grp_lgt
                from acm_summ_tbl as bs
                join eqk_zone_tbl as z on (z.id = bs.id_zone)
                join aqd_tarif_tbl as td on (td.id = bs.id_summtarif)
                left join acm_lgt_summ_tbl as lg on (lg.id_doc = bs.id_doc and lg.id_zone = bs.id_zone and lg.id_summtarif = bs.id_summtarif)
                where bs.id_paccnt = $id_paccnt and z.id = $id_zone
                and bs.mmgg = '{$month}'
                group by bs.id_zone,bs.id_summtarif, tarif_val
                order by tarif_val ;";
                
                $result_tar = pg_query($Link, $SQL3) or die("SQL Error: " . pg_last_error($Link) . $SQL);
                $rows_tar = pg_num_rows($result_tar);
                
                if ($rows_tar <= 1) {
                    $row_tar = pg_fetch_array($result_tar);

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
                    
                    $Sheet->setCellValueByColumnAndRow(5, $str_zone, $row_tar['tarif_val'])
                                                  ->setCellValueByColumnAndRow(7, $str_zone, $row_tar['summ'])
                                                  ->setCellValueByColumnAndRow(8, $str_zone, $kvt_norm)
                                                  ->setCellValueByColumnAndRow(9, $str_zone, $summ_norm)
                                                  ->setCellValueByColumnAndRow(10, $str_zone,$kvt_over)
                                                  ->setCellValueByColumnAndRow(11, $str_zone,$summ_over )                            
                                                  ->setCellValueByColumnAndRow(12, $str_zone, $row_tar['demand_lgt'])
                                                  ->setCellValueByColumnAndRow(13, $str_zone, $row_tar['summ_lgt']);
                    
                    $summ_all_mmgg+=$row_tar['summ'];
                    $kvt_lgt_mmgg+= $row_tar['demand_lgt'];
                    $summ_lgt_mmgg+=$row_tar['summ_lgt'];
                    
                    $kvt_norm_mmgg+=$kvt_norm;
                    $summ_norm_mmgg+=$summ_norm;

                    $kvt_over_mmgg+=$kvt_over;
                    $summ_over_mmgg+= $summ_over;
                    
                    
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
                        $r = $baseRow + $i++;
                        $Sheet->insertNewRowBefore($r, 1);
                        
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

                        $summ_all_tar+=$row_tar['summ'];
                        $summ_lgt_tar+=$row_tar['summ_lgt'];
                        $kvt_lgt_tar+=$row_tar['demand_lgt'];
                        
                        $kvt_norm_tar+=$kvt_norm;
                        $summ_norm_tar+=$summ_norm;

                        $kvt_over_tar+=$kvt_over;
                        $summ_over_tar+= $summ_over;

                    }
                    $Sheet->setCellValueByColumnAndRow(7, $str_zone, $summ_all_tar) // $r-$rows_tar
                                                  ->setCellValueByColumnAndRow(8, $str_zone, $kvt_norm_tar)
                                                  ->setCellValueByColumnAndRow(9, $str_zone, $summ_norm_tar)
                                                  ->setCellValueByColumnAndRow(10, $str_zone,$kvt_over_tar)
                                                  ->setCellValueByColumnAndRow(11, $str_zone,$summ_over_tar )                            
                                                  ->setCellValueByColumnAndRow(12, $str_zone, $kvt_lgt_tar)
                                                  ->setCellValueByColumnAndRow(13, $str_zone, $summ_lgt_tar);
                    
                    $summ_all_mmgg+=$summ_all_tar;
                    $kvt_lgt_mmgg+= $kvt_lgt_tar;
                    $summ_lgt_mmgg+=$summ_lgt_tar;
                    
                    $kvt_norm_mmgg+=$kvt_norm_tar;
                    $summ_norm_mmgg+=$summ_norm_tar;

                    $kvt_over_mmgg+=$kvt_over_tar;
                    $summ_over_mmgg+= $summ_over_tar;
                    
                }
                
                
            }
            $Sheet->setCellValueByColumnAndRow(6, $str_month, $kvt_all_mmgg)
                                          ->setCellValueByColumnAndRow(7, $str_month, $summ_all_mmgg)
                                          ->setCellValueByColumnAndRow(8,  $str_month, $kvt_norm_mmgg)
                                          ->setCellValueByColumnAndRow(9,  $str_month, $summ_norm_mmgg)
                                          ->setCellValueByColumnAndRow(10, $str_month,$kvt_over_mmgg)
                                          ->setCellValueByColumnAndRow(11, $str_month,$summ_over_mmgg )                            
                                          ->setCellValueByColumnAndRow(12, $str_month, $kvt_lgt_mmgg)
                                          ->setCellValueByColumnAndRow(13, $str_month, $summ_lgt_mmgg)
                                          ->setCellValueByColumnAndRow(14, $str_month, $row['subs_pay'])
                                          ->setCellValueByColumnAndRow(15, $str_month, $row['subs_month'])                    
                                          ->setCellValueByColumnAndRow(16, $str_month, $row['subs_month']-$row['subs_pay'])
                                          ->setCellValueByColumnAndRow(17, $str_month, $row['subs_recalc'])
                                          ->setCellValueByColumnAndRow(18, $str_month, $row['kt_val']-$row['subs_pay']-$row['subs_recalc'])
                                          ->setCellValueByColumnAndRow(20, $str_month, $row['e_val'])
                                          ->setCellValueByColumnAndRow(22, $str_month, $row['e_val']);                    
            //$i++;
        }
    }
    $callEndTime = microtime(true);
    $callTime = $callEndTime - $callStartTime;
    //echo 'Call time to fill workbook ' , sprintf('%.4f',$callTime) , " seconds" , EOL;
    $callStartTime = microtime(true);
    
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    $objWriter->setPreCalculateFormulas(false);
    //$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save("../tmp/oborab.xls");
    //$objWriter->save('php://output');
    $callEndTime = microtime(true);
    $callTime = $callEndTime - $callStartTime;
    //echo 'Call time to write workbook ' , sprintf('%.4f',$callTime) , " seconds" , EOL;

    echo_result(1, 'oborab.xls');
    return;
    
}
//=----------------------------абоненты с льготами ----------------------------

if ($oper=='abon_lgt1')
{
    
    $styleHeader1 = array(
	'font' => array(
		'bold' => true
	),
	'borders' => array(
		'outline' => array(
			'style' => PHPExcel_Style_Border::BORDER_THIN,
		),
		'top' => array(
			'style' => PHPExcel_Style_Border::BORDER_THICK
		),
	)
    );

    $styleFooter1 = array(
	'font' => array(
		'bold' => true
	),
	'borders' => array(
		'outline' => array(
			'style' => PHPExcel_Style_Border::BORDER_THIN
		)
	)
    );

    $styleStr = array(
	'borders' => array(
		'allborders' => array(
			'style' => PHPExcel_Style_Border::BORDER_THIN
		)
	)
    );
    
    
    $objReader = PHPExcel_IOFactory::createReader('Excel5');
    $objPHPExcel = $objReader->load("../XL/lgt_list1.xls");
    $Sheet=$objPHPExcel->getActiveSheet();   
    $p_mmgg = sql_field_val('dt_b', 'date');
    
    $mmgg1_str = trim($_POST['period_str']);
    
    $p_id_town = sql_field_val('addr_town', 'int');
    $p_id_lgt = sql_field_val('id_grp_lgt', 'int');

    $where = '  ';

    if ($p_id_town!='null')
    {
        $where.= "and adr.id_town = $p_id_town ";
    }

    if ($p_id_lgt!='null')
    {
//        if ($where != ' WHERE ')
//        {
//            $where.= " and ";
//        }
        $where.= "and  lg.id_grp_lgt = $p_id_lgt ";
    }
    
    //if ($where == ' WHERE ') $where ='';
    
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
    
    $SQL = "select c.id, c.code, c.book, lg.fio_lgt,lg.family_cnt,lg.s_doc, lg.n_doc,lg.ident_cod_l,
    to_char(lg.dt_reg, 'DD.MM.YYYY') as dt_reg,
     g.name as lgt_name, g.ident,adr.town, adr.street, address_print(c.addr) as house,
    tt.name as name_tar, n.percent, tt.value*100 as tar_value, round(tt.value*(100-n.percent),2) as tar_lgt_value,bs.*
     from 
    clm_paccnt_h as c
    join (select id, max(dt_b) as dt_b from clm_paccnt_h  where dt_b <= $p_mmgg and coalesce(dt_e,$p_mmgg) >=$p_mmgg group by id order by id) as c2 
    on (c.id = c2.id and c.dt_b = c2.dt_b)
    join lgm_abon_h as lg on (lg.id_paccnt = c.id) 
    join (select id, max(dt_b) as dt_b from lgm_abon_h  where dt_b <= $p_mmgg and coalesce(dt_e,$p_mmgg) >=$p_mmgg group by id order by id) as lg2 
    on (lg.id = lg2.id and lg.dt_b = lg2.dt_b)
    join aqi_grptar_tbl as tar on (tar.id = c.id_gtar)
    join lgi_group_tbl as g on (lg.id_grp_lgt = g.id)
    join lgi_norm_tbl as  n on (n.id_calc = g.id_calc and (n.id_tar_grp = tar.id_lgt_group or n.id_tar_grp is null))
    left join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class)
    left join aqm_tarif_tmp as tt on (tt.id_grptar = c.id_gtar and coalesce(tt.lim_min ,0)=0)
    left join 
    ( select id_paccnt,sum(demand) as demand, sum(demand_lgt) as demand_lgt, sum(summ_lgt) as summ_lgt
     from acm_lgt_summ_tbl where mmgg = $p_mmgg
     group by id_paccnt order by id_paccnt
    ) as bs
    on (bs.id_paccnt = c.id) where c.archive =0 and (lg.dt_end is null or lg.dt_end <= ($p_mmgg::date +'1 month -1 days'::interval)::date)
    $where
    order by town, g.ident, book, code;";

   // throw new Exception(json_encode($SQL));

    $callStartTime = microtime(true);
    
    $current_town='';
    $current_lgt='';
    
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

        while ($row = pg_fetch_array($result)) {

            if ($current_town!=$row['town'])
            {
                //-------------------------------------
                if ($current_lgt!='')
                {
                    $r = $i++;
                    $Sheet->setCellValueByColumnAndRow(1,$r,"Всього ({$current_lgt}) -")
                                                  ->setCellValueByColumnAndRow(3,$r,$sum_lgt_cnt) 
                                                  ->setCellValueByColumnAndRow(7,$r,$sum_lgt_demall)
                                                  ->setCellValueByColumnAndRow(8,$r,$sum_lgt_demlgt)
                                                  ->setCellValueByColumnAndRow(11,$r,$sum_lgt_summ);
                    
                    $Sheet->getStyle("B{$r}:M{$r}")->applyFromArray($styleFooter1);                                                  
                                                  
                    //$r = $i++;     
                    $sum_lgt_cnt=0;
                    $sum_lgt_demall=0;
                    $sum_lgt_demlgt=0;
                    $sum_lgt_summ=0;

                }
                $current_lgt='';
                //$current_lgt=$row['lgt_name'];
                //$r = $i++;
                //$Sheet->setCellValueByColumnAndRow(1,$r,$current_lgt); 
                //-------------------------------------
                
                
                $current_town=$row['town'];
                
                if ($cnt_headers == 0)
                {
                  $Sheet->setCellValueByColumnAndRow(2,2," Список абонентiв маючих пiльги за $mmgg1_str ($res_short_name за електроенергiю)"); 
                  $Sheet->setCellValueByColumnAndRow(2,3," по населеному пункту $current_town "); 
                }
                else
                {

                    $r = $i++;
                    $Sheet->setCellValueByColumnAndRow(1,$r,"Всього по нас.пункту -")
                                                  ->setCellValueByColumnAndRow(3,$r,$nm-1) 
                                                  ->setCellValueByColumnAndRow(7,$r,$sum_town_demall)
                                                  ->setCellValueByColumnAndRow(8,$r,$sum_town_demlgt)
                                                  ->setCellValueByColumnAndRow(11,$r,$sum_town_summ);
                    
                    $Sheet->getStyle("B{$r}:M{$r}")->applyFromArray($styleFooter1);
                    //throw new Exception("Try to save file $rows_count ....");    
                    $r = $i++;                    

                    $sum_town_demall=0;
                    $sum_town_demlgt=0;
                    $sum_town_summ=0;
                    $nm=1;   
                    
                    for($ir=$hdr_row_start;$ir<=$hdr_row_end;$ir++)
                    {
                        for($ic=$hdr_col_start;$ic<=$hdr_col_end;$ic++)
                        {
                            $Sheet->setCellValueByColumnAndRow($ic,$ir+$i-3,
                                    $Sheet->getCellByColumnAndRow($ic,$ir)->getValue());
                            
                            $coord_s = $Sheet->getCellByColumnAndRow($ic,$ir)->getCoordinate();
                            //throw new Exception(json_encode($coord_s));
                            //echo $coord_s;
                            $coord_d = $Sheet->getCellByColumnAndRow($ic,$ir+$i-3)->getCoordinate();
                            //$column=PHPExcel_Cell::stringFromColumnIndex($ic);
                            //$Sheet->duplicateStyle($Sheet->getStyle((string)$coord_s), $coord_d );            
                            //if(($column=='B')&&(($ir==5)))
                            //if($column=='C')
                            {
                                $style = $Sheet->getStyle($coord_s);
                                //$Sheet->duplicateStyle($Sheet->getStyle($column.$ir),$coord_d );
                                $Sheet->duplicateStyle($style,$coord_d );
                                
                            }    
                            //$Sheet->duplicateStyle($Sheet->getCellByColumnAndRow($ic,$ir)->getStyle(), $coord_d );
                        }
                    }

                    $Sheet->setCellValueByColumnAndRow(2,$r+1," Список абонентiв маючих пiльги за $mmgg1_str ($res_short_name за електроенергiю)"); 
                    $Sheet->setCellValueByColumnAndRow(2,$r+2," по населеному пункту $current_town "); 
//                    echo $current_town;
                    $i= $i+4;
                    $r =$i;
                    
                }
                $cnt_headers++;
            }
            
            if ($current_lgt!=$row['lgt_name'])
            {
                if ($current_lgt!='')
                {
                    $r = $i++;
                    $Sheet->setCellValueByColumnAndRow(1,$r,"Всього ({$current_lgt}) -")
                                                  ->setCellValueByColumnAndRow(3,$r,$sum_lgt_cnt) 
                                                  ->setCellValueByColumnAndRow(7,$r,$sum_lgt_demall)
                                                  ->setCellValueByColumnAndRow(8,$r,$sum_lgt_demlgt)
                                                  ->setCellValueByColumnAndRow(11,$r,$sum_lgt_summ);
                
                    
                    $Sheet->getStyle("B{$r}:M{$r}")->applyFromArray($styleFooter1);
                    
                    //$r = $i++;            
                    
                    $sum_lgt_cnt=0;
                    $sum_lgt_demall=0;
                    $sum_lgt_demlgt=0;
                    $sum_lgt_summ=0;
                }

                $current_lgt=$row['lgt_name'];
                $r = $i++;
                $Sheet->setCellValueByColumnAndRow(1,$r,$current_lgt); 
                
                $Sheet->getStyle("B{$r}:M{$r}")->applyFromArray($styleHeader1);
                
                
            }    

            
            $r = $i++;
            //$Sheet->insertNewRowBefore($r,1);
            
            $Sheet->setCellValueByColumnAndRow(1,$r,$nm++)
                                          ->setCellValueByColumnAndRow(2,$r,$row['street'].' '.$row['house'])
                                          ->setCellValueByColumnAndRow(3,$r,$row['fio_lgt'])
                                          ->setCellValueByColumnAndRow(4,$r,$row['book'].'/'.$row['code'])
                                          ->setCellValueByColumnAndRow(5,$r,$row['s_doc'].' '.$row['n_doc'])
                                          ->setCellValueByColumnAndRow(6,$r,$row['family_cnt'])
                                          ->setCellValueByColumnAndRow(7,$r,$row['demand'])
                                          ->setCellValueByColumnAndRow(8,$r,$row['demand_lgt'])
                                          ->setCellValueByColumnAndRow(9,$r,$row['tar_value'])
                                          ->setCellValueByColumnAndRow(10,$r,$row['tar_lgt_value'])
                                          ->setCellValueByColumnAndRow(11,$r,$row['summ_lgt'])
                                          ->setCellValueByColumnAndRow(12,$r,$row['dt_reg']);
            
            //$Sheet->getStyle("B{$r}:M{$r}")->applyFromArray($styleStr);

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
      $Sheet->setCellValueByColumnAndRow(1,$r,"Всього ({$current_lgt}) -")
                                    ->setCellValueByColumnAndRow(3,$r,$sum_lgt_cnt) 
                                    ->setCellValueByColumnAndRow(7,$r,$sum_lgt_demall)
                                    ->setCellValueByColumnAndRow(8,$r,$sum_lgt_demlgt)
                                    ->setCellValueByColumnAndRow(11,$r,$sum_lgt_summ);

      $Sheet->getStyle("B{$r}:M{$r}")->applyFromArray($styleFooter1);
      
    }
    
    $r = $i++;
    $Sheet->setCellValueByColumnAndRow(1,$r,"Всього по нас.пункту -")
                                  ->setCellValueByColumnAndRow(3,$r,$nm-1) 
                                  ->setCellValueByColumnAndRow(7,$r,$sum_town_demall)
                                  ->setCellValueByColumnAndRow(8,$r,$sum_town_demlgt)
                                  ->setCellValueByColumnAndRow(11,$r,$sum_town_summ);

    $Sheet->getStyle("B{$r}:M{$r}")->applyFromArray($styleFooter1);    

    
    $r = $i++;
    $Sheet->setCellValueByColumnAndRow(1,$r,"ВСЬОГО")
                                    ->setCellValueByColumnAndRow(3,$r,$sum_all_cnt) 
                                    ->setCellValueByColumnAndRow(7,$r,$sum_all_demall)
                                    ->setCellValueByColumnAndRow(8,$r,$sum_all_demlgt)
                                    ->setCellValueByColumnAndRow(11,$r,$sum_all_summ);

    $Sheet->getStyle("B{$r}:M{$r}")->applyFromArray($styleFooter1);
   
    //$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');    
    
    //throw new Exception('Try to save file ....');    
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    $objWriter->setPreCalculateFormulas(false);
   

    $objWriter->save("../tmp/lgt_list1.xls");
    //$objWriter->save('php://output');

    echo_result(1, 'lgt_list1.xls');
    return;
    
}

echo_result(0, 'nothing to do');
?> 