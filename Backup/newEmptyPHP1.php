<?php

if ($oper=='abon_f2')
{
    $p_mmgg = sql_field_val('dt_b', 'date');
    
    $mmgg1_str = trim($_POST['period_str']);
    
    $p_id_town = sql_field_val('addr_town', 'int');
    $p_id_lgt = sql_field_val('id_grp_lgt', 'int');

    if (isset($_POST['town_detal']))
        $p_town_detal = trim($_POST['town_detal']);
    else 
        $p_town_detal =0;
    

    
    $where = ' ';

    if ($p_id_town!='null')
    {
        $where.= "and adr.id_town = $p_id_town ";
    }

    if ($p_id_lgt!='null')
    {
        $where.= " and lg.id_grp_lgt = $p_id_lgt ";
    }
    
    //if ($where == ' WHERE ') $where ='';
    
    if ($p_town_detal==0)
        $order ="g.ident::int, int_book, int_code, code";
    else
        $order ="g.ident::int, town, int_book,int_code, code";
  
    
    $SQL = "select c.id, c.code, c.book, lg.fio_lgt,lg.family_cnt,lg.s_doc, lg.n_doc,lg.ident_cod_l,
    to_char(lg.dt_reg, 'DD.MM.YYYY') as dt_reg,
     g.name as lgt_name, g.ident,adr.town, adr.street, address_print(c.addr) as house,
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

   // throw new Exception(json_encode($SQL));

    
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
                    </tr> 
                    </table> <br/> ",$print_h2_flag*$print_flag);        
                     
                    //throw new Exception("Try to save file $rows_count ....");    

                    //echo $footer_text;    
                    // echo '</table> <br/>';
                    
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
                $addr = $row['town'].' '.$row['street'].' '.$row['house'];
                
                $book=$row['book'].'/'.$row['code'];
                $days = $row['days_lgt'];
                $days_all = $row['days_all'];
                if ($days ==$days_all) $days='';
                
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
                     <td class='c_i'>{$dem_all}</td>
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
    eval("\$footer_text_eval = \"$footer_text\";");
    echo_html($footer_text_eval,$print_h_flag*$print_flag);
    

?>
