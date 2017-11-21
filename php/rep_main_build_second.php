<?php
if (($oper=='lgt_sum_corr')||($oper=='lgt_sum_corr_plus')||($oper=='lgt_sum_corr_old'))
{
    $p_mmgg = sql_field_val('dt_b', 'mmgg');
    
    $period = trim($_POST['period_str']);
    
    $p_id_town = sql_field_val('addr_town', 'int');
    $p_id_lgt = sql_field_val('id_grp_lgt', 'int');
    $p_id_tar = sql_field_val('id_gtar', 'int');     

    $p_id_sector = sql_field_val('id_sector', 'int');
    $p_book = sql_field_val('book', 'string');
    $p_id_region = sql_field_val('id_region', 'int');
    
    $where = 'where';
    $params_caption ='';
    $print_flag = 1;
    
    $director = $sbutboss_name; 
        
    
    if ($p_id_town!='null')
    {
        if ($where!='where') $where.= " and ";        
        
        if ($p_id_town==40854)
          $where.= " ( adr.id_parent = $p_id_town or adr.id_parent = 40016 )";
        else
          $where.= " ( adr.id_town = $p_id_town or adr.id_parent = $p_id_town )";
        
        $params_caption.= $_POST['addr_town_name'] ;        
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
            where rp.id_paccnt = ss.id_paccnt and rp.id_sector = $p_id_sector ) ";
    }
    
    if ($p_id_region!='null')
    {
        
        if ($where!='where') $where.= " and ";                
        $where.= " exists (select rs.id from 
            prs_runner_paccnt as rp 
            join prs_runner_sectors as rs on (rs.id = rp.id_sector)
            where rp.id_paccnt = ss.id_paccnt and rs.id_region =  $p_id_region ) ";

        $SQL_H="SELECT name from cli_region_tbl where id = $p_id_region ;";
    
        $result = pg_query($Link, $SQL_H) or die("SQL Error: " . pg_last_error($Link) . $SQL_H);
        $row = pg_fetch_array($result);
        
        $params_caption.= $row['name'];               
    }
    
    
    if ((isset($_POST['gtar']))&&($_POST['gtar']!=''))
        $params_caption .= ' Тарифна група : '. trim($_POST['gtar']);
    
    if ($oper=='lgt_sum_corr_plus') 
    {
        if ($where!='where') $where.= " and ";
        $where.= " summ > 0  ";

        $params_caption.=' донарахування по пільгах ';
    }

    if ($oper=='lgt_sum_corr_old') 
    {
        if ($where!='where') $where.= " and ";
        $where.= " date_trunc('year', mmgg_lgt) < date_trunc('year', $p_mmgg::date )  ";

        $params_caption.=' корегування за минулі роки ';
    }
    
    
    if ($where=='where') $where= '';
    
    
    $SQL = " select c.book,c.code,
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
    regexp_replace(regexp_replace(g.ident, '-.*?$', '') , '[^0-9]', '','g')::int as int_ident, 
    g.name as lgt_name, g.ident, 
    ss.*, u1.name as user_name, to_char(ss.mmgg_lgt, 'DD.MM.YYYY') as mmgg_lgt_str 
from
(
select b.id_paccnt, b.id_person, ls.id_grp_lgt, date_trunc('month',ls.dt_fin)::date as mmgg_lgt,
sum(demand_lgt) as demand, sum(summ_lgt) as summ
from acm_bill_tbl as b 
join acm_lgt_summ_tbl as ls on (ls.id_doc = b.id_doc)
where  b.id_pref = 10
and b.mmgg = $p_mmgg
and ls.dt_fin < b.mmgg and ls.dt_fin is not null
group by b.id_paccnt, b.id_person, ls.id_grp_lgt, date_trunc('month',ls.dt_fin)::date
having sum(summ_lgt) <>0
union all
select id_paccnt, id_person, id_grp_lgt, mmgg_lgt,
sum(demand_val) as demand, sum(sum_val) as summ
from acm_dop_lgt_tbl as dl
where dl.mmgg = $p_mmgg
and dl.mmgg_lgt < dl.mmgg 
group by id_paccnt, id_person, id_grp_lgt, mmgg_lgt
) as ss
join clm_paccnt_tbl c on (c.id=ss.id_paccnt)
join clm_abon_tbl as ab on (ab.id = c.id_abon) 
join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class)
join lgi_group_tbl as g on (ss.id_grp_lgt = g.id)
left join syi_user as u1 on (u1.id = ss.id_person)
$where    
order by int_ident, int_book, book, int_code,code, mmgg_lgt ; ";

   // throw new Exception(json_encode($SQL));
    
    $current_lgt='';

    eval("\$header_text_eval = \"$header_text\";");
    echo $header_text_eval;

    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        //throw new Exception("Try to save file $rows_count ....");    
        
        $baseRow = 8;
        
        $i = $baseRow;
        $cnt_headers = 0;
        $nm=1;

        //$hdr_row_start=5;
        //$hdr_row_end=7;

        //$hdr_col_start=1;
        //$hdr_col_end=12;
        
        $sum_lgt_cnt = 0;
        $sum_all_cnt = 0;
        $sum_lgt_summ=0;
        $sum_all_summ=0;
        $sum_lgt_dem=0;
        $sum_all_dem=0;

        
        //echo 'Start row inserting';
        //$Sheet->insertNewRowBefore($baseRow,$rows_count);
        $np=0;
        while ($row = pg_fetch_array($result)) {

            $np++;
            if ((($np> ($page_num-1)* $page_size )&& ($np<= $page_num* $page_size )) || ($show_pager ==0))
                $print_flag = 1;
            else
                $print_flag = 0;

            
            if ($current_lgt!=$row['lgt_name'])
            {
                if ($current_lgt!='')
                {
                    $r = $i++;
                    $sum_lgt_summ_txt = number_format_ua ($sum_lgt_summ, 2);
                    $sum_lgt_dem_txt = number_format_ua ($sum_lgt_dem, 0);
                    
                    echo_html( "
                    <tr class='tab_head'>
                     <td colspan='5'>Всього ({$current_lgt}) </td>
                     <td class='c_i'>{$sum_lgt_cnt}</td>
                     <td class='c_i'>{$sum_lgt_dem_txt}</td>
                     <td class='c_n'>{$sum_lgt_summ_txt}</td>
                    </tr>  ",$print_flag);        
         
                    $sum_lgt_cnt=0;
                    $sum_lgt_summ=0;
                    $sum_lgt_dem=0;
                }

                $current_lgt=$row['lgt_name'];
                $current_lgt_ident=$row['ident'];
                
                $r = $i++;

                 echo_html( "
                    <tr class='tab_head'>
                     <td colspan='8'>{$current_lgt} &nbsp;({$current_lgt_ident}) </td>
                    </tr>  ",$print_flag);
                
            }    
            
            $r = $i++;
            
                if($res_code=='310')
                    $addr = $row['addr'];
                else
                    $addr = $row['town'].' '.$row['addr'];
                
                $book =$row['book'];
                $code =$row['code'];
                
                $demand_txt = number_format_ua ($row['demand'], 0);
                $summ_txt = number_format_ua ($row['summ'], 2);
                
                 echo_html( "
                    <tr class='table_str'>
                     <td class='c_i'>{$nm}</td>                
                     <td class='c_t'>{$book}</td>
                     <td class='c_t'>{$code}</td>                     
                     <td class='c_t'>{$addr}</td>
                     <td class='c_t'>{$row['abon']}</td>            
                     <td class='c_t'>{$row['mmgg_lgt_str']}</td>
                     <td class='c_i'>{$demand_txt}</td>
                     <td class='c_n'>{$summ_txt}</td>
                    </tr>  ",$print_flag);
            
                     $nm++;

            $sum_lgt_cnt++;
            $sum_lgt_summ+=$row['summ'];
            $sum_lgt_dem+=$row['demand'];
            
            $sum_all_cnt++;
            $sum_all_summ+=$row['summ'];
            $sum_all_dem+=$row['demand'];
            
            //$i++;
        }
    }


    if ($current_lgt!='')
    {
      $r = $i++;
      $sum_lgt_summ_txt = number_format_ua ($sum_lgt_summ, 2);
      $sum_lgt_dem_txt = number_format_ua ($sum_lgt_dem, 0);
      echo_html( "
                    <tr class='tab_head'>
                     <td colspan='5'>Всього ({$current_lgt}) </td>
                     <td class='c_i'>{$sum_lgt_cnt}</td>
                     <td class='c_i'>{$sum_lgt_dem_txt}</td>
                     <td class='c_n'>{$sum_lgt_summ_txt}</td>
                    </tr>  ",$print_flag);        
    
    }

    $r = $i++;

    $sum_all_summ_txt = number_format_ua ($sum_all_summ, 2);
    $sum_all_dem_txt = number_format_ua ($sum_all_dem, 0);
    
    eval("\$footer_text_eval = \"$footer_text\";");
    echo_html( $footer_text_eval,$print_flag);

    
}
//-----------------------------------
if ($oper=='lgt_sum_abon_corr')
{
    $p_mmgg = sql_field_val('dt_b', 'mmgg');
    
    $period = trim($_POST['period_str']);
    
    $p_id_town = sql_field_val('addr_town', 'int');
    $p_id_lgt = sql_field_val('id_grp_lgt', 'int');
    $p_id_tar = sql_field_val('id_gtar', 'int');    

    $p_id_sector = sql_field_val('id_sector', 'int');
    $p_book = sql_field_val('book', 'string');
    $p_id_region = sql_field_val('id_region', 'int');
    
    $where = 'where';
    $params_caption ='';
    $print_flag = 1;
    
    $director = $sbutboss_name; 
        
    
    if ($p_id_town!='null')
    {
        if ($where!='where') $where.= " and ";        
        
        if ($p_id_town==40854)
          $where.= " ( adr.id_parent = $p_id_town or adr.id_parent = 40016 )";
        else
          $where.= " ( adr.id_town = $p_id_town or adr.id_parent = $p_id_town )";
        
        $params_caption.= $_POST['addr_town_name'] ;        
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
            where rp.id_paccnt = ss.id_paccnt and rp.id_sector = $p_id_sector ) ";
    }
    
    if ($p_id_region!='null')
    {
        if ($where!='where') $where.= " and ";        
        $where.= " exists (select rs.id from 
            prs_runner_paccnt as rp 
            join prs_runner_sectors as rs on (rs.id = rp.id_sector)
            where rp.id_paccnt = ss.id_paccnt and rs.id_region =  $p_id_region ) ";

        $SQL_H="SELECT name from cli_region_tbl where id = $p_id_region ;";
    
        $result = pg_query($Link, $SQL_H) or die("SQL Error: " . pg_last_error($Link) . $SQL_H);
        $row = pg_fetch_array($result);
        
        $params_caption.= $row['name'];               
    }
    
    
    if ((isset($_POST['gtar']))&&($_POST['gtar']!=''))
        $params_caption .= ' Тарифна група : '. trim($_POST['gtar']);
    
    if ($oper=='lgt_sum_corr_plus') 
    {
        if ($where!='where') $where.= " and ";
        $where.= " summ > 0  ";

        $params_caption.=' донарахування по пільгах ';
    }

    if ($oper=='lgt_sum_corr_old') 
    {
        if ($where!='where') $where.= " and ";
        $where.= " date_trunc('year', mmgg_lgt) < date_trunc('year', $p_mmgg::date )  ";

        $params_caption.=' корегування за минулі роки ';
    }
    
    
    if ($where=='where') $where= '';
    
    
    $SQL = " select book, code, abon, town, addr, int_code, int_book, int_ident, lgt_name, ident, id_grp_lgt,
      CASE WHEN min(mmgg_lgt) = max(mmgg_lgt) THEN to_char(min(mmgg_lgt), 'DD.MM.YYYY') ELSE
       to_char(min(mmgg_lgt), 'DD.MM.YYYY')||'-'||to_char(max(mmgg_lgt), 'DD.MM.YYYY') END as mmgg_lgt_str ,
    sum(demand) as demand, sum(summ) as summ 
    from (
    select c.book,c.code,
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
    regexp_replace(regexp_replace(g.ident, '-.*?$', '') , '[^0-9]', '','g')::int as int_ident, 
    g.name as lgt_name, g.ident, 
    ss.*, u1.name as user_name, to_char(ss.mmgg_lgt, 'DD.MM.YYYY') as mmgg_lgt_str 
from
(
select b.id_paccnt, b.id_person, ls.id_grp_lgt, date_trunc('month',ls.dt_fin)::date as mmgg_lgt,
sum(demand_lgt) as demand, sum(summ_lgt) as summ
from acm_bill_tbl as b 
join acm_lgt_summ_tbl as ls on (ls.id_doc = b.id_doc)
where  b.id_pref = 10
and b.mmgg = $p_mmgg
and ls.dt_fin < b.mmgg and ls.dt_fin is not null
group by b.id_paccnt, b.id_person, ls.id_grp_lgt, date_trunc('month',ls.dt_fin)::date
having sum(summ_lgt) <>0
union all
select id_paccnt, id_person, id_grp_lgt, mmgg_lgt,
sum(demand_val) as demand, sum(sum_val) as summ
from acm_dop_lgt_tbl as dl
where dl.mmgg = $p_mmgg
and dl.mmgg_lgt < dl.mmgg 
group by id_paccnt, id_person, id_grp_lgt, mmgg_lgt
) as ss
join clm_paccnt_tbl c on (c.id=ss.id_paccnt)
join clm_abon_tbl as ab on (ab.id = c.id_abon) 
join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class)
join lgi_group_tbl as g on (ss.id_grp_lgt = g.id)
left join syi_user as u1 on (u1.id = ss.id_person)
$where    
) as sss
group by book, code, abon, town, addr, int_code, int_book, int_ident, lgt_name, ident, id_grp_lgt    
order by int_ident, int_book, book, int_code,code ; ";

   // throw new Exception(json_encode($SQL));
    
    $current_lgt='';

    eval("\$header_text_eval = \"$header_text\";");
    echo $header_text_eval;

    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        //throw new Exception("Try to save file $rows_count ....");    
        
        $baseRow = 8;
        
        $i = $baseRow;
        $cnt_headers = 0;
        $nm=1;

        $sum_lgt_cnt = 0;
        $sum_all_cnt = 0;
        $sum_lgt_summ=0;
        $sum_all_summ=0;
        $sum_lgt_dem=0;
        $sum_all_dem=0;

        
        //echo 'Start row inserting';
        //$Sheet->insertNewRowBefore($baseRow,$rows_count);
        $np=0;
        while ($row = pg_fetch_array($result)) {

            $np++;
            if ((($np> ($page_num-1)* $page_size )&& ($np<= $page_num* $page_size )) || ($show_pager ==0))
                $print_flag = 1;
            else
                $print_flag = 0;

            
            if ($current_lgt!=$row['lgt_name'])
            {
                if ($current_lgt!='')
                {
                    $r = $i++;
                    $sum_lgt_summ_txt = number_format_ua ($sum_lgt_summ, 2);
                    $sum_lgt_dem_txt = number_format_ua ($sum_lgt_dem, 0);
                    
                    echo_html( "
                    <tr class='tab_head'>
                     <td colspan='5'>Всього ({$current_lgt}) </td>
                     <td class='c_i'>{$sum_lgt_cnt}</td>
                     <td class='c_i'>{$sum_lgt_dem_txt}</td>
                     <td class='c_n'>{$sum_lgt_summ_txt}</td>
                    </tr>  ",$print_flag);        
         
                    $sum_lgt_cnt=0;
                    $sum_lgt_summ=0;
                    $sum_lgt_dem=0;
                }

                $current_lgt=$row['lgt_name'];
                $current_lgt_ident=$row['ident'];
                
                $r = $i++;

                 echo_html( "
                    <tr class='tab_head'>
                     <td colspan='8'>{$current_lgt} &nbsp;({$current_lgt_ident}) </td>
                    </tr>  ",$print_flag);
                
            }    
            
            $r = $i++;
            
                if($res_code=='310')
                    $addr = $row['addr'];
                else
                    $addr = $row['town'].' '.$row['addr'];
                
                $book =$row['book'];
                $code =$row['code'];
                
                $demand_txt = number_format_ua ($row['demand'], 0);
                $summ_txt = number_format_ua ($row['summ'], 2);
                
                 echo_html( "
                    <tr class='table_str'>
                     <td class='c_i'>{$nm}</td>                
                     <td class='c_t'>{$book}</td>
                     <td class='c_t'>{$code}</td>                     
                     <td class='c_t'>{$addr}</td>
                     <td class='c_t'>{$row['abon']}</td>            
                     <td class='c_t'>{$row['mmgg_lgt_str']}</td>
                     <td class='c_i'>{$demand_txt}</td>
                     <td class='c_n'>{$summ_txt}</td>
                    </tr>  ",$print_flag);
            
                     $nm++;

            $sum_lgt_cnt++;
            $sum_lgt_summ+=$row['summ'];
            $sum_lgt_dem+=$row['demand'];
            
            $sum_all_cnt++;
            $sum_all_summ+=$row['summ'];
            $sum_all_dem+=$row['demand'];
            
            //$i++;
        }
    }


    if ($current_lgt!='')
    {
      $r = $i++;
      $sum_lgt_summ_txt = number_format_ua ($sum_lgt_summ, 2);
      $sum_lgt_dem_txt = number_format_ua ($sum_lgt_dem, 0);
      echo_html( "
                    <tr class='tab_head'>
                     <td colspan='5'>Всього ({$current_lgt}) </td>
                     <td class='c_i'>{$sum_lgt_cnt}</td>
                     <td class='c_i'>{$sum_lgt_dem_txt}</td>
                     <td class='c_n'>{$sum_lgt_summ_txt}</td>
                    </tr>  ",$print_flag);        
    
    }

    $r = $i++;

    $sum_all_summ_txt = number_format_ua ($sum_all_summ, 2);
    $sum_all_dem_txt = number_format_ua ($sum_all_dem, 0);
    
    eval("\$footer_text_eval = \"$footer_text\";");
    echo_html( $footer_text_eval,$print_flag);
   
}

//-----------------------------------------------------------------------------

if ($oper=='abon_zerodem_year')
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
    
    $SQL = "select to_char( value_ident::date  , 'DD.MM.YYYY') as start_mmgg 
        from syi_sysvars_tbl where ident = 'dt_start';";
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);    
    
    if ($result) {
        $row = pg_fetch_array($result); 
        $start_mmgg_txt=$row['start_mmgg'];
    }    
    
    $params_caption='';

    if (isset($_POST['town_detal']))
        $p_town_detal = trim($_POST['town_detal']);
    else 
        $p_town_detal =0;

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

    if ($p_id_sector!='null')
    {
        $where.= "and rp.id_sector = $p_id_sector ";
        
    }
    
    if ($p_id_region!='null')
    {
        
        $where.= "and rs.id_region =  $p_id_region  ";

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
    s.e_val,  sw_dt, 
   (coalesce('Не проживає '||nolive_dt_b||'-'||nolive_dt_e,CASE WHEN c.not_live THEN 'Не проживає' END, ht.name ,'' )||' '||c.note)::varchar  as info,
   ('0'||substring(c.code FROM '[0-9]+'))::int as int_code,
   ('0'||substring(c.book FROM '[0-9]+'))::int as int_book,
    p.represent_name 
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
   join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class)
   join acm_saldo_tbl as s on (s.id_paccnt = c.id and s.mmgg = $p_mmgg and s.id_pref=10)
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
   join prs_runner_paccnt as rp on (rp.id_paccnt = c.id)
   join prs_runner_sectors as rs on (rp.id_sector = rs.id)
   left join prs_persons as p on (p.id = rs.id_kontrol)       
    where c.archive =0 
    and not exists
    (
        select b.id_paccnt   from acm_bill_tbl as b 
        where b.mmgg_bill <= $p_mmgg::date and b.mmgg_bill > $p_mmgg::date-'1 year'::interval and
        b.id_pref = 10 and b.idk_doc in (200,220)
        and coalesce(b.demand,0) <> 0 and b.id_paccnt = c.id
    )
    $where
    order by town, int_book,book, int_code, code;";

   // throw new Exception(json_encode($SQL));
    
    $current_town='null';
    $print_h_flag =1;
    $print_flag = 1;
    
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
                $kontrol = htmlspecialchars($row['represent_name']);                
                
                $saldo_txt=number_format_ua($row['e_val'],2);
                
                 echo_html( "
                    <tr class='table_str'>
                     <td class='c_i'>{$nm}</td>                
                     <td>{$abon}</td>            
                     <td>{$addr}</td>
                     <td class='c_t'>{$book}</td>
                     <td class='c_n'>{$saldo_txt}</td>
                     <td class='c_t'>{$row['sw_dt']}</td>
                     <td class='c_t'>{$kontrol}</td>
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
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          </tr>  ",$print_h_flag*$print_flag);

    echo_html( $footer_text , $print_h_flag);
    
}

//----------------------------------------------------------------------------
if ($oper=='doplgt_list')
{

    //list($year, $month, $day  ) = split('[/.-]', $p_mmgg);
    //$month_str = ukr_month((int)$month,0);
    //$period = "файл  $p_filename ";
    $p_mmgg = sql_field_val('dt_b', 'mmgg');    
    $period = trim($_POST['period_str']);
    //разбор и обработка строки параметров jqgrid
    $qWhere='';
    if (isset($_POST['grid_params'])) {
          $grid_params_json = $_POST['grid_params'];
          $json_str = stripslashes($grid_params_json); 
          $grid_params = json_decode($json_str,true);
          
          
          $fildsArray =DbGetFieldsArray($Link,'acm_dop_lgt_tbl');
          $fildsArray['book'] = array('f_name' => 'book', 'f_type' => 'char');
          $fildsArray['code'] = array('f_name' => 'code', 'f_type' => 'char');
          $fildsArray['addr'] = array('f_name' => 'addr', 'f_type' => 'character varying');
          $fildsArray['abon'] = array('f_name' => 'abon', 'f_type' => 'character varying');
          $fildsArray['user_name'] = array('f_name' => 'user_name', 'f_type' => 'character varying');
          $fildsArray['alt_code'] = array('f_name' => 'alt_code', 'f_type' => 'integer');
          $qWhere= DbBuildWhere($grid_params,$fildsArray);

    }
    if ($qWhere!='') $qWhere=$qWhere.' and ';
    else $qWhere=' where ';

    $qWhere=$qWhere." mmgg = $p_mmgg::date ";
    
    
    $params_caption = '';

    $sum_lgt = 0;

    $i=1;
    
    eval("\$header_text = \"$header_text\";");
    echo_html ($header_text);
    
    $SQL = "select * from ( select lg.*, i.name as lgt_name, i.ident, i.alt_code,u1.name as user_name,
 acc.book, acc.code, 
 (adr.adr||' '||
   (coalesce('буд.'||(acc.addr).house||'','')||
		coalesce('/'||(acc.addr).slash||' ','')||
			coalesce(' корп.'||(acc.addr).korp||'','')||
				coalesce(', кв. '||(acc.addr).flat,'')||
					coalesce('/'||(acc.addr).f_slash||' ',''))::varchar
)::varchar as addr,
(c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon,
('0'||substring(acc.code FROM '[0-9]+'))::int as int_code,
('0'||substring(acc.book FROM '[0-9]+'))::int as int_book,
to_char(lg.dt, 'DD.MM.YYYY') as dt_txt,
to_char(lg.mmgg_lgt, 'MM.YYYY') as mmgg_lgt_txt    
from acm_dop_lgt_tbl as lg
join clm_paccnt_tbl as acc on (acc.id = lg.id_paccnt)
join clm_abon_tbl as c on (c.id = acc.id_abon) 
join adt_addr_tbl as adr on (adr.id = (acc.addr).id_class)
left join lgi_group_tbl as i on (i.id = lg.id_grp_lgt)
left join syi_user as u1 on (u1.id = lg.id_person)
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
            $name_lgt = htmlspecialchars($row['lgt_name']);
            $name_user = htmlspecialchars($row['user_name']);
            
            $sum_txt=number_format_ua($row['sum_val'],2);
            $demand_txt=number_format_ua($row['demand_val'],0);
            
            $sum_lgt+= $row['sum_val'];
            
            echo_html( "
            <tr >
            <td>{$i}</td>            
            <td class='c_t'>{$row['book']}</td>
            <td class='c_t'>{$row['code']}</td>
            <td class='c_t'>$abon</td>
            <td class='c_t'>$addr</td>
            
            <td class='c_t'>{$row['alt_code']}</td>
            <td class='c_t'>$name_lgt</td>
            
            <td class='c_n'>$sum_txt</td>
            <td class='c_i'>$demand_txt</td>
            
            <td class='c_t'>{$row['mmgg_lgt_txt']}</td>
            <td class='c_t'>{$row['is_corr']}</td>
            
            <td class='c_t'>$name_user</td>
            <td class='c_t'>{$row['dt_txt']}</td>
            </tr>  ");        
            $i++;
            
        }
        
    }
    
    $sum_lgt_txt = number_format_ua ($sum_lgt, 2);
    eval("\$footer_text_eval = \"$footer_text\";");
    echo_html( $footer_text_eval);

}

//----------------------------------------------------------------------------

if ($oper=='switch_list')
{

    //list($year, $month, $day  ) = split('[/.-]', $p_mmgg);
    //$month_str = ukr_month((int)$month,0);
    //$period = "файл  $p_filename ";
    $p_mmgg = sql_field_val('dt_b', 'mmgg');    
    $p_mode = sql_field_val('list_mode', 'int');    
    $period = trim($_POST['period_str']);
    //разбор и обработка строки параметров jqgrid
    $qWhere='';
    if (isset($_POST['grid_params'])) {
          $grid_params_json = $_POST['grid_params'];
          $json_str = stripslashes($grid_params_json); 
          $grid_params = json_decode($json_str,true);
          
          $fildsArray =DbGetFieldsArray($Link,'clm_switching_tbl');
          $fildsArray['book'] = array('f_name' => 'book', 'f_type' => 'integer');
          $fildsArray['code'] = array('f_name' => 'code', 'f_type' => 'integer');
          $fildsArray['addr'] = array('f_name' => 'addr', 'f_type' => 'character varying');
          $fildsArray['abon'] = array('f_name' => 'abon', 'f_type' => 'character varying');
          $fildsArray['position'] = array('f_name' => 'position', 'f_type' => 'character varying');
          $fildsArray['sector'] = array('f_name' => 'sector', 'f_type' => 'character varying');
          $fildsArray['user_name'] =   array('f_name'=>'user_name','f_type'=>'character varying');
          
          $qWhere= DbBuildWhere($grid_params,$fildsArray);

    }

    $params_caption = '';
    if ($p_mode == 0) {
        if ($qWhere != '')
            $qWhere = $qWhere . ' and ';
        else
            $qWhere = ' where ';

        $qWhere = $qWhere . " mmgg = $p_mmgg::date ";

        $order = 'dt_action, int_book, book ,int_code ,code';
    }

    $join_sql = '';
    if ($p_mode == 1) {
        $join_sql = ' join (select id_paccnt, max(dt_action) as maxdt from clm_switching_tbl group by id_paccnt) as csdt 
       on (n.id_paccnt = csdt.id_paccnt and n.dt_action = csdt.maxdt) ';

        $order = 'int_book, book ,int_code ,code';
        $params_caption = 'попереджені/відключені на поточний момент';
    }


    $sum_lgt = 0;

    $i=1;
    
    eval("\$header_text = \"$header_text\";");
    echo_html ($header_text);
    
    $SQL = "select * from (
 select n.*, acc.book, acc.code,  
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
cn.represent_name as position, sp.name as place, sa.name as action_name, 
rp.id_sector, coalesce(rs.name,'')::varchar as sector, u1.name as user_name ,
to_char(n.dt, 'DD.MM.YYYY') as dt_txt,        
to_char(n.dt_action, 'DD.MM.YYYY') as dt_action_txt,
to_char(n.dt_create, 'DD.MM.YYYY') as dt_create_txt,    
to_char(n.dt_sum, 'DD.MM.YYYY') as dt_sum_txt,    
to_char(n.dt_warning, 'DD.MM.YYYY') as dt_warning_txt    
from clm_switching_tbl as n $join_sql
join clm_paccnt_tbl as acc on (acc.id = n.id_paccnt)
join clm_abon_tbl as c on (c.id = acc.id_abon) 
join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class) 
join cli_switch_action_tbl as sa on (sa.id = n.action )    
left join cli_switch_place_tbl as sp on (sp.id = n.id_switch_place )    
left join prs_persons as cn on (cn.id = n.id_position)   
left join prs_runner_paccnt as rp on (rp.id_paccnt = acc.id)
left join prs_runner_sectors as rs on (rs.id = rp.id_sector)
left join syi_user as u1 on (u1.id = n.id_person)
) as ss
  $qWhere Order by $order ";    
    
    
   // throw new Exception(json_encode($SQL));
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        $i=1;
        while ($row = pg_fetch_array($result)) {

            $abon = htmlspecialchars($row['abon']);
            $addr = htmlspecialchars($row['addr']);
            $sector = htmlspecialchars($row['sector']);
            $executor = htmlspecialchars($row['position']);
            $name_user = htmlspecialchars($row['user_name']);
            $comment = htmlspecialchars($row['comment']);
            
            $sum_txt=number_format_ua($row['sum_warning'],2);
            
            echo_html( "
            <tr >
            <td>{$i}</td>            
            <td class='c_t'>{$row['book']}</td>
            <td class='c_t'>{$row['code']}</td>
            <td class='c_t'>$abon</td>
            <td class='c_t'>$addr</td>
                        
            <td class='c_t'>{$row['action_name']}</td>
            <td class='c_t'>{$row['dt_action_txt']}</td>
            <td class='c_t'>$sector</td>
            <td class='c_t'>{$row['dt_create_txt']}</td>
            <td class='c_n'>$sum_txt</td>
            <td class='c_t'>{$row['dt_sum_txt']}</td>
            <td class='c_t'>{$row['dt_warning_txt']}</td>
            <td class='c_t'>{$row['place']}</td>
            
            <td class='c_t'>$comment</td>
            <td class='c_t'>$executor</td>
            <td class='c_t'>{$row['act_num']}</td>
            
            <td class='c_t'>$name_user</td>
            <td class='c_t'>{$row['dt_txt']}</td>
            </tr>  ");        
            $i++;
            
        }
        
    }
    
    eval("\$footer_text_eval = \"$footer_text\";");
    echo_html( $footer_text_eval);

}
//-------------------------------------------------
if ($oper=='works_list')
{

    //list($year, $month, $day  ) = split('[/.-]', $p_mmgg);
    //$month_str = ukr_month((int)$month,0);
    //$period = "файл  $p_filename ";
    $p_mmgg = sql_field_val('dt_b', 'mmgg');    
    $period = trim($_POST['period_str']);
    //разбор и обработка строки параметров jqgrid
    $qWhere='';
    if (isset($_POST['grid_params'])) {
          $grid_params_json = $_POST['grid_params'];
          $json_str = stripslashes($grid_params_json); 
          $grid_params = json_decode($json_str,true);
          
          $fildsArray =DbGetFieldsArray($Link,'clm_works_tbl');

          $fildsArray['position'] =   array('f_name'=>'position','f_type'=>'character varying');
          $fildsArray['book'] = array('f_name' => 'book', 'f_type' => 'integer');
          $fildsArray['code'] = array('f_name' => 'code', 'f_type' => 'integer');
          $fildsArray['addr'] = array('f_name' => 'addr', 'f_type' => 'character varying');
          $fildsArray['abon'] = array('f_name' => 'abon', 'f_type' => 'character varying');
          $fildsArray['user_name'] =   array('f_name'=>'user_name','f_type'=>'character varying');          
          
          $qWhere= DbBuildWhere($grid_params,$fildsArray);

    }
    if ($qWhere!='') $qWhere=$qWhere.' and ';
    else $qWhere=' where ';

    $qWhere=$qWhere." work_period = $p_mmgg::date ";
    
    
    $params_caption = '';

    $sum_lgt = 0;

    $i=1;
    
    eval("\$header_text = \"$header_text\";");
    echo_html ($header_text);
    
    
$SQL="SELECT * FROM 
( select p.*, cn.represent_name as position,
 acc.book, acc.code, 
 (adr.adr||' '||
   (coalesce('буд.'||(acc.addr).house||'','')||
		coalesce('/'||(acc.addr).slash||' ','')||
			coalesce(' корп.'||(acc.addr).korp||'','')||
				coalesce(', кв. '||(acc.addr).flat,'')||
					coalesce('/'||(acc.addr).f_slash||' ',''))::varchar
)::varchar as addr,
(c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon,
u1.name as user_name ,
('0'||substring(acc.code FROM '[0-9]+'))::int as int_code,
('0'||substring(acc.book FROM '[0-9]+'))::int as int_book,
to_char(p.dt_input, 'DD.MM.YYYY') as dt_txt,
to_char(p.dt_work, 'DD.MM.YYYY') as dt_work_txt ,   
wi.name as work_name    
from clm_works_tbl as p
join cli_works_tbl as wi on (wi.id = p.idk_work)
join clm_paccnt_tbl as acc on (acc.id = p.id_paccnt)
join clm_abon_tbl as c on (c.id = acc.id_abon) 
left join adt_addr_tbl as adr on (adr.id = (acc.addr).id_class)
left join prs_persons as cn on (cn.id = p.id_position)   
left join syi_user as u1 on (u1.id = p.id_person)
) as ss $qWhere Order by dt_work, int_book, book ,int_code ,code ;";    
    
   // throw new Exception(json_encode($SQL));
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        $i=1;
        while ($row = pg_fetch_array($result)) {

            $abon = htmlspecialchars($row['abon']);
            $addr = htmlspecialchars($row['addr']);

            $executor = htmlspecialchars($row['position']);
            $name_user = htmlspecialchars($row['user_name']);
            $comment = htmlspecialchars($row['note']);
            
            echo_html( "
            <tr >
            <td>{$i}</td>            
            <td class='c_t'>{$row['book']}</td>
            <td class='c_t'>{$row['code']}</td>
            <td class='c_t'>$abon</td>
            <td class='c_t'>$addr</td>
            
            <td class='c_t'>{$row['work_name']}</td>
            <td class='c_t'>{$row['dt_work_txt']}</td>

            <td class='c_t'>$executor</td>
            <td class='c_t'>{$row['act_num']}</td>                        
            <td class='c_t'>$comment</td>
            
            <td class='c_t'>$name_user</td>
            <td class='c_t'>{$row['dt_txt']}</td>
            </tr>  ");        
            $i++;
            
        }
        
    }
    
    eval("\$footer_text_eval = \"$footer_text\";");
    echo_html( $footer_text_eval);

}
//-----------------------------------------------------------------------------

if ($oper=='pay_pack_print')
{

    //list($year, $month, $day  ) = split('[/.-]', $p_mmgg);
    //$month_str = ukr_month((int)$month,0);
    //$period = "файл  $p_filename ";
    $p_mmgg = sql_field_val('dt_b', 'mmgg');    
    $period_str = trim($_POST['period_str']);
    
    $p_id_pack = sql_field_val('id_pack', 'int');
    //разбор и обработка строки параметров jqgrid
    $qWhere='';
    if (isset($_POST['grid_params'])) {
          $grid_params_json = $_POST['grid_params'];
          $json_str = stripslashes($grid_params_json); 
          $grid_params = json_decode($json_str,true);
          
          $fildsArray =DbGetFieldsArray($Link,'acm_pay_tbl');

          $fildsArray['book'] = array('f_name' => 'book', 'f_type' => 'char');
          $fildsArray['code'] = array('f_name' => 'code', 'f_type' => 'char');
          $fildsArray['addr'] = array('f_name' => 'addr', 'f_type' => 'character varying');
          $fildsArray['abon'] = array('f_name' => 'abon', 'f_type' => 'character varying');
          $fildsArray['user_name'] =   array('f_name'=>'user_name','f_type'=>'character varying');          
          
          $qWhere= DbBuildWhere($grid_params,$fildsArray);

    }
    if ($qWhere == '')
    $qWhere =' where p.id_pref = 10 ';
    else
    $qWhere .=' and p.id_pref = 10 ';        
    
    $qWhere=$qWhere." and p.id_headpay = $p_id_pack ";
    
    
    $params_caption = '';

    $summ_all      =0;
    $summ_tax_all  =0;

    $i=1;
    
    $SQL_H="SELECT reg_num, to_char(reg_date, 'DD.MM.YYYY') as reg_date, r.name as bank_name  
     FROM acm_headpay_tbl as h
     left join aci_pay_origin_tbl as r on (r.id = h.id_origin)
    where h.id = $p_id_pack ;";
    
    $result = pg_query($Link, $SQL_H) or die("SQL Error: " . pg_last_error($Link) . $SQL_H);
    $row = pg_fetch_array($result);
    $params_caption = "Пачка № ".$row['reg_num']." від ".$row['reg_date']." (".$row['bank_name'].")";
    
    
    eval("\$header_text = \"$header_text\";");
    echo_html ($header_text);
    
    
    $SQL = " select  c.id,c.code,c.book, 
    adr.town, adr.street, address_print(c.addr) as house,
   (a.last_name||' '||coalesce(substr(a.name,1,1),'')||'.'||coalesce(substr(a.patron_name,1,1),''))||'.'::varchar as abon,
    hp.reg_num as hreg_num,to_char(hp.reg_date, 'DD.MM.YYYY') as hreg_date, 
    p.reg_num ,to_char(p.reg_date, 'DD.MM.YYYY') as reg_date, to_char(p.pay_date, 'DD.MM.YYYY') as pay_date, 
    po.name as pay_origin,
    to_char(p.mmgg, 'DD.MM.YYYY') as mmgg,
    to_char(p.dt, 'DD.MM.YYYY') as dt_txt,
    p.value, p.value_tax,p.note, di.name as kind_doc,
    ('0'||substring(c.code FROM '[0-9]+'))::int as int_code,
    ('0'||substring(c.book FROM '[0-9]+'))::int as int_book,   
    u1.name as user_name 
    from acm_pay_tbl as p 
    join acm_headpay_tbl as hp on (hp.id = p.id_headpay)
    join clm_paccnt_tbl as c on (c.id =p.id_paccnt )
    join clm_abon_tbl as a on (a.id = c.id_abon) 
    join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class)
    left join aci_pay_origin_tbl as po on (po.id = hp.id_origin )
    left join dci_doc_tbl as di on (di.id = p.idk_doc)
    left join syi_user as u1 on (u1.id = p.id_person)
    $qWhere
    order by p.reg_date,int_book,c.book,int_code,c.code;";  
    
   // throw new Exception(json_encode($SQL));
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        $i=1;
        while ($row = pg_fetch_array($result)) {

            $abon = htmlspecialchars($row['abon']);
            $addr = htmlspecialchars($row['town'].' '.$row['street'].' '.$row['house']);
            $name_user = htmlspecialchars($row['user_name']);
            $note = htmlspecialchars($row['note']);
            
            $book=$row['book'].'/'.$row['code'];

            $summ_txt = number_format_ua ($row['value'],2);
            $summ_tax_txt = number_format_ua ($row['value_tax'],2);
            

            echo_html( "
            <tr >
            <td>{$i}</td>            
            <td class='c_t'>$book</td>
            <td>$abon</td>
            <td>$addr</td>
            
            <td class='c_t'>{$row['reg_num']}</td>
            <td >{$row['reg_date']}</td>
            <td >{$row['pay_date']}</td>
            <td >{$row['kind_doc']}</td>
            <td class='c_n'>{$summ_txt}</td>
            <td class='c_n'>{$summ_tax_txt}</td>
            
            <td >{$row['mmgg']}</td>
            <td class='c_t'>{$note}</td>
            <td class='c_t'>{$name_user}</td>
            <td class='c_t'>{$row['dt_txt']}</td>
            </tr>  ",1);
            
            $i++;
            
            $summ_all += round($row['value'],2);
            $summ_tax_all += round($row['value_tax'],2);
            
        }
        
    }
    $summ_txt = number_format_ua (round($summ_all,2),2);
    $summ_tax_txt = number_format_ua (round($summ_tax_all,2),2);
    
    eval("\$footer_text_eval = \"$footer_text\";");
    echo_html( $footer_text_eval);

}
//-----------------------------------------------------------------------------
if (($oper=='indic_change')||($oper=='indic_change_subs'))
{
    $p_mmgg = sql_field_val('dt_b', 'mmgg');
    
    list($year, $month, $day  ) = split('[/.-]', $p_mmgg);
    $month_str = ukr_month((int)$month,0);

    $p_id_tar = sql_field_val('id_gtar', 'int');        
    $period = "за $month_str $year р.";

    $p_id_sector = sql_field_val('id_sector', 'int');
    $p_book = sql_field_val('book', 'string');
    $p_id_person = sql_field_val('id_person', 'int');
    $p_id_operation = sql_field_val('id_operation', 'int');
    $p_id_region = sql_field_val('id_region', 'int');
    
    $p_id_town = sql_field_val('addr_town', 'int');
    $p_id_tar = sql_field_val('id_gtar', 'int');    
    $p_id_type_meter = sql_field_val('id_type_meter', 'int');    
    $p_id_type_meter_array = sql_field_val('id_type_meter_array', 'array');    
    //$p_value = sql_field_val('sum_value', 'numeric');    
    
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
      $where.= " and  i.id_operation_new = $p_id_operation  ";
   }
   
    if ($p_id_town!='null')
    {
        $where.= "and adr.id_town = $p_id_town ";
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
    
   if ($oper=='indic_change_subs')
   {
        $where.= " and exists ( select id_doc from acm_pay_tbl as p
          where p.id_pref = 10 and p.idk_doc in (110, 111, 193,194) 
           and p.mmgg = $p_mmgg and p.id_paccnt = c.id ) ";       
        $params_caption .= " Субсидіанти ";       
   }
    
    $demand_all    =0;
    $i=1;
    
    $np=0;
    $print_flag = 1;
    
    $SQL = "select indic_changes_fun($p_mmgg );";
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    
    
    
    eval("\$header_text = \"$header_text\";");
    echo_html( $header_text);
    
    $SQL = "select c.book,c.code,
 i.id_paccnt, i.id_meter, i.id_typemet, i.id_zone, i.num_eqp, 
       to_char(i.mmgg, 'DD.MM.YYYY') as mmgg_txt,
       i.id_operation, 
       to_char(i.dat_ind, 'DD.MM.YYYY') as dat_ind_txt,
       i.value::int, i.value_diff::int, i.value_prev::int, i.carry, 
       i.id_person_change, 
       to_char(i.dt_change, 'DD.MM.YYYY HH24:MI') as dt_change_txt,
       to_char(i.mmgg_change, 'DD.MM.YYYY') as mmgg_change_txt,
       i.id_change_reason, i.id_operation_new, 
       i.id_zone_new, 
       to_char(i.dat_ind_new, 'DD.MM.YYYY') as dat_ind_new_txt,
       i.value_new::int, i.value_diff_new::int, i.value_prev_new::int,
       it.name as indicoper ,it2.name as indicoper_new , z.nm as zone, z2.nm as zone_new,
       (coalesce(i.value_diff_new,0) - coalesce(i.value_diff,0))::int as value_delta,
(ab.last_name||' '||coalesce(ab.name,'')||' '||coalesce(ab.patron_name,''))::varchar as abon,
 (adr.street||' '||
        (coalesce('буд.'||(c.addr).house||'','')||
		coalesce('/'||(c.addr).slash||' ','')|| 
			coalesce(' корп.'||(c.addr).korp||'','')||
				coalesce(', кв. '||(c.addr).flat,'')||
					coalesce('/'||(c.addr).f_slash,''))::varchar
    )::varchar as addr,
    regexp_replace(regexp_replace(c.code, '-.*?$', '') , '[^0-9]', '','g')::int as int_code,
    regexp_replace(regexp_replace(c.book, '-.*?$', '') , '[^0-9]', '','g')::int as int_book,
  rs.name as sector, u1.name as user_name, cr.name as change_reason
from rep_indic_changes_tbl as i
join clm_paccnt_tbl c on (c.id=i.id_paccnt)
join clm_abon_tbl as ab on (ab.id = c.id_abon) 
join eqi_meter_tbl as m on (m.id = i.id_typemet)
join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class)
 left join syi_user as u1 on (u1.id = i.id_person_change)
 left join lgi_change_reason_tbl as cr on (cr.id = i.id_change_reason)
 left join eqk_zone_tbl as z on (z.id = i.id_zone)
 left join eqk_zone_tbl as z2 on (z2.id = i.id_zone_new)
 left join cli_indic_type_tbl as it on (it.id = i.id_operation)
 left join cli_indic_type_tbl as it2 on (it2.id = i.id_operation_new)
 left join prs_runner_paccnt as rp on (rp.id_paccnt = c.id)
 left join prs_runner_sectors as rs on (rp.id_sector = rs.id) 
 where  c.archive =0 $where
 order by  int_book,c.book,int_code,c.code, id_meter, mmgg, id_zone, dt_change; ";

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
            $addr = htmlspecialchars($row['addr']);
            $oper = htmlspecialchars($row['indicoper']);
            $oper_new = htmlspecialchars($row['indicoper_new']);
            $user_name = htmlspecialchars($row['user_name']);
            
            $book=$row['book'];
            $code=$row['code'];


            echo_html( "
            <tr >
            <td>{$i}</td>            
            <td class='c_t'>$book</td>
            <td>$code</td>
            <td>$abon</td>
            <td>$addr</td>
            <td class='c_t'>{$row['num_eqp']}</td>
            <td class='c_t'>{$row['mmgg_txt']}</td>
            <td class='c_t'>{$row['dt_change_txt']}</td>            
            <td class='c_t'>{$row['zone']}</td>
            <td class='c_i'>{$row['value_prev']}</td>            
            <td class='c_i'>{$row['value']}</td>
            <td class='c_t'>{$row['dat_ind_txt']}</td>
            <td class='c_i'>{$row['value_diff']}</td>            
            <td class='c_t'>{$oper}</td>

            <td class='c_t'>{$row['zone_new']}</td>
            <td class='c_i'>{$row['value_prev_new']}</td>            
            <td class='c_i'>{$row['value_new']}</td>
            <td class='c_t'>{$row['dat_ind_new_txt']}</td>
            <td class='c_i'>{$row['value_diff_new']}</td>            
            <td class='c_t'>{$oper_new}</td>
            
            <td class='c_i' style='font-weight: bold;' >{$row['value_delta']}</td>

            <td class='c_t'>{$user_name}</td>
            <td class='c_t'>{$row['change_reason']}</td>
            </tr>  ",$print_flag); 

            $i++;
            
            $demand_all += $row['value_delta'];
            
        }
        
    }

    eval("\$footer_text_eval = \"$footer_text\";");

    echo_html( $footer_text_eval,$print_flag);
   
    if ($print_flag==0) echo "</tbody> </table> ";
   
}
////----------------------------------------------------------------------------
if ($oper=='paymove_list')
{
    $p_dtb = sql_field_val('dt_b', 'date');
    $p_dte = sql_field_val('dt_e', 'date');
    $p_mmgg = sql_field_val('dt_b', 'mmgg');
    $p_id_region = sql_field_val('id_region', 'int');
    
    //$p_id_sector = sql_field_val('id_sector', 'int');

    //$p_book = sql_field_val('book', 'string');
    //$p_code = sql_field_val('code', 'string');
    
    //$p_dtb_str = trim($_POST['dt_b']);
    //$p_dte_str = trim($_POST['dt_e']);

    $period_str = trim($_POST['period_str']);
    
    $params_caption = '';
    $where="";
    
    $summ_all      =0;
    $summ_tax_all  =0;

    $i=1;

    if ($p_id_region!='null')
    {
        
        $where.= " and exists (select rs.id from 
            prs_runner_paccnt as rp 
            join prs_runner_sectors as rs on (rs.id = rp.id_sector)
            where ( rp.id_paccnt = c.id or rp.id_paccnt = ct.id) and rs.id_region =  $p_id_region ) ";
        
        $SQL_H="SELECT name from cli_region_tbl where id = $p_id_region ;";
    
        $result = pg_query($Link, $SQL_H) or die("SQL Error: " . pg_last_error($Link) . $SQL_H);
        $row = pg_fetch_array($result);
        
        $params_caption.= $row['name'];               
    }   

    
    $np=0;
    $print_flag = 1;
    
    eval("\$header_text = \"$header_text\";");
    echo_html( $header_text);
    
    $SQL = "  select  c.id,c.code,c.book, 
    adr.town, adr.street, address_print(c.addr) as house,
   (a.last_name||' '||coalesce(substr(a.name,1,1),'')||'.'||coalesce(substr(a.patron_name,1,1),''))||'.'::varchar as abon,
   ct.code as code_t,ct.book as book_t, 
    adrt.town as town_t, adrt.street as street_t, address_print(ct.addr) as house_t,
   (at.last_name||' '||coalesce(substr(at.name,1,1),'')||'.'||coalesce(substr(at.patron_name,1,1),''))||'.'::varchar as abon_t,
    hp.reg_num as hreg_num,to_char(hp.reg_date, 'DD.MM.YYYY') as hreg_date, 
    p.reg_num ,to_char(p.reg_date, 'DD.MM.YYYY') as reg_date, to_char(p.pay_date, 'DD.MM.YYYY') as pay_date, 
    po.name as pay_origin,u1.name as user_name,
    to_char(p.mmgg, 'DD.MM.YYYY') as mmgg,
    to_char(pt.dt, 'DD.MM.YYYY HH24:MI') as dt_txt,
    p.value, p.value_tax,p.note, di.name as kind_doc,
    pt.id_paccnt as id_paccnt_to, pt.note as note_to, pt.dt as dt_transfer, pt.id_person as id_person_transfer,
    ('0'||substring(c.code FROM '[0-9]+'))::int as int_code,
    ('0'||substring(c.book FROM '[0-9]+'))::int as int_book   
    from acm_pay_tbl as p 
    join acm_headpay_tbl as hp on (hp.id = p.id_headpay)
    join acm_pay_tbl as pf on (pf.id_corr_doc = p.id_doc and (pf.value <0 and p.value>0 or pf.value >0 and p.value<0))
    join acm_pay_tbl as pt on (pt.id_corr_doc = p.id_doc and (pt.value >0 and p.value>0 or pt.value <0 and p.value<0))
    join clm_paccnt_h as c on (c.id =p.id_paccnt )
    join (select id, max(dt_b) as dt_b from clm_paccnt_h  where  
    ((dt_b < ($p_mmgg::date+'1 month'::interval) and dt_e is null)
    or 
      tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
    ) 
    group by id order by id) as c2 
    on (c.id = c2.id and c.dt_b = c2.dt_b)    
    join clm_abon_tbl as a on (a.id = c.id_abon) 
    join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class)
    join clm_paccnt_h as ct on (ct.id =pt.id_paccnt )
    join (select id, max(dt_b) as dt_b from clm_paccnt_h  where  
    ((dt_b < ($p_mmgg::date+'1 month'::interval) and dt_e is null)
    or 
      tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
    ) 
    group by id order by id) as ct2 
    on (ct2.id = ct.id and ct2.dt_b = ct.dt_b)    
    join clm_abon_tbl as at on (at.id = ct.id_abon) 
    join adt_addr_town_street_tbl as adrt on (adrt.id = (ct.addr).id_class)
    left join aci_pay_origin_tbl as po on (po.id = hp.id_origin )
    left join dci_doc_tbl as di on (di.id = p.idk_doc)
    left join syi_user as u1 on (u1.id = pt.id_person)
    where p.mmgg  < $p_mmgg and p.id_pref = 10 -- and p.idk_doc not in ( 110,111, 193,194) 
    and pf.mmgg = $p_mmgg and pt.mmgg = $p_mmgg 
    $where
    order by pt.dt,int_book,c.book,int_code,c.code ;";


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
            $abon_t = htmlspecialchars($row['abon_t']);
            $addr = htmlspecialchars($row['town'].' '.$row['street'].' '.$row['house']);
            $addr_t = htmlspecialchars($row['town_t'].' '.$row['street_t'].' '.$row['house_t']);
            $book=$row['book'].'/'.$row['code'];
            $book_t=$row['book_t'].'/'.$row['code_t'];
            $user = htmlspecialchars($row['user_name']);

            $summ_txt = number_format_ua ($row['value'],2);
            $summ_tax_txt = number_format_ua ($row['value_tax'],2);
            
            echo_html( "
            <tr >
            <td>{$i}</td>     
            <td >{$row['mmgg']}</td>
            <td class='c_t'>{$row['hreg_num']}</td>
            <td >{$row['hreg_date']}</td>
            
            <td class='c_t'>{$row['reg_num']}</td>
            <td >{$row['reg_date']}</td>
            <td >{$row['pay_date']}</td>
            <td >{$row['kind_doc']}</td>
            <td class='c_n'>{$summ_txt}</td>
            <td class='c_n'>{$summ_tax_txt}</td>
            <td class='c_t'>{$row['pay_origin']}</td>
            
            <td class='c_t'>$book</td>
            <td>$abon</td>
            <td>$addr</td>

            <td>$book_t</td>
            <td>$abon_t</td>
            <td>$addr_t</td>
            
            <td class='c_t'>{$row['note_to']}</td>
            <td class='c_t'>{$user}</td>
            <td class='c_t'>{$row['dt_txt']}</td>
            </tr>  ",$print_flag);

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
if (($oper=='lgm_abon_edit')||($oper=='lgm_abon_cancel'))
{
    $p_dtb = sql_field_val('dt_b', 'date');
    $p_dte = sql_field_val('dt_e', 'date');
    $p_mmgg = sql_field_val('dt_b', 'mmgg');
    
    $p_id_sector = sql_field_val('id_sector', 'int');
    $p_id_region = sql_field_val('id_region', 'int');
    
    $p_id_person = sql_field_val('id_person', 'int');
    $p_id_town = sql_field_val('addr_town', 'int');
    

    $p_book = sql_field_val('book', 'string');
    $p_code = sql_field_val('code', 'string');
    
    //$p_dtb_str = trim($_POST['dt_b']);
    //$p_dte_str = trim($_POST['dt_e']);

    $period_str = trim($_POST['period_str']);
    
    $params_caption = '';
    $where="";
    
    $summ_all      =0;
    $summ_tax_all  =0;

    $i=1;
    
    if ((isset($_POST['sector']))&&($_POST['sector']!=''))
        $params_caption.= ' Дiльниця: '. trim($_POST['sector']);

    if ((isset($_POST['person']))&&($_POST['person']!=''))
        $params_caption.= ' Оператор: '. trim($_POST['person']);
    
    if ((isset($_POST['addr_town_name']))&&($_POST['addr_town_name']!=''))
        $params_caption.= 'Населений пункт: '. trim($_POST['addr_town_name']);
    
    
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

   if ($p_id_person!='null')
   {
      $where.= " and  u1.id_person = $p_id_person  ";
   }

    if ($p_id_town!='null')
    {
        
        if ($p_id_town==40854)
          $where.= " and ( adr.id_parent = $p_id_town or adr.id_parent = 40016 )";
        else
          $where.= " and ( adr.id_town = $p_id_town or adr.id_parent = $p_id_town )";
        
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
   
    
   if ($oper=='lgm_abon_cancel')
   {
      $where.= " and mode = 2 
      and (lg_old.id_paccnt is not null) and ( lg_new.id_paccnt is not null)
      and (coalesce(lg_old.dt_end,'2030-01-01'::date)>coalesce(lg_new.dt_end,'2030-01-01'::date))
      and lg_new.dt_end < $p_dte::date ";
      
      $params_caption .= " Скасування пільги ";
   }
   
   
   
    $np=0;
    $print_flag = 1;
    
    eval("\$header_text = \"$header_text\";");
    echo_html( $header_text);
    
    $SQL = " 

    select c.book,c.code,cl.mode, cl.id_paccnt, cl.id_usr, cl.date_change, cl.dt,  cl.id_reason,  cr.name as change_reason,u1.name as user_name,
    adr.town, (adr.street||' '||
        (coalesce('буд.'||(c.addr).house||'','')||
		coalesce('/'||(c.addr).slash||' ','')|| 
			coalesce(' корп.'||(c.addr).korp||'','')||
				coalesce(', кв. '||(c.addr).flat,'')||
					coalesce('/'||(c.addr).f_slash,''))::varchar
    )::varchar as addr,
    ('0'||substring(c.code FROM '[0-9]+'))::int as int_code,
    ('0'||substring(c.book FROM '[0-9]+'))::int as int_book ,  
    to_char(cl.dt, 'DD.MM.YYYY HH24:MI') as dt_txt,
    to_char(cl.date_change, 'DD.MM.YYYY') as date_change_txt,
    CASE WHEN cl.mode =1 THEN 'Нова' WHEN cl.mode =2 THEN 'Кориг.' WHEN cl.mode =3 THEN 'Видалення' END as mode_name,
    lg_old.id_grp_lgt, 
    lg_old.id_calc,
    lg_old.family_cnt,
    coalesce(lg_new.fio_lgt,lg_old.fio_lgt) as fio_lgt,
    to_char(lg_old.dt_start, 'DD.MM.YYYY') as dt_start_txt,
    to_char(lg_old.dt_end, 'DD.MM.YYYY') as dt_end_txt,
    g_old.name as lgt_name_old, g_old.ident as ident_old, lc_old.name as calc_name_old,
    lg_new.id_grp_lgt as id_grp_lgt_new, 
    lg_new.id_calc as id_calc_new,
    lg_new.family_cnt as family_cnt_new,
    to_char(lg_new.dt_start, 'DD.MM.YYYY') as dt_start_new_txt,
    to_char(lg_new.dt_end, 'DD.MM.YYYY') as dt_end_new_txt,
    g_new.name as lgt_name_new, g_new.ident as ident_new, lc_new.name as calc_name_new
    from lgm_changelog_tbl as cl
    join clm_paccnt_h as c on (c.id =  cl.id_paccnt)
    join (select id, max(dt_b) as dt_b from clm_paccnt_h  where 
            ((dt_b < ($p_mmgg::date+'1 month'::interval) and dt_e is null)
            or 
            tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
        ) group by id order by id) as c2 
       on (c.id = c2.id and c.dt_b = c2.dt_b)
    join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class)    
    
    left join lgm_abon_h as lg_old on (cl.id_paccnt = lg_old.id_paccnt and cl.id_lgt = lg_old.id and lg_old.dt_e = cl.date_change)
    left join lgm_abon_h as lg_new on (cl.id_paccnt = lg_new.id_paccnt and cl.id_lgt = lg_new.id and lg_new.dt_b = cl.date_change)

    left join lgi_group_tbl as g_old on (lg_old.id_grp_lgt = g_old.id)
    left join lgi_group_tbl as g_new on (lg_new.id_grp_lgt = g_new.id)

    left join lgi_calc_header_tbl as lc_old on (lg_old.id_calc = lc_old.id)
    left join lgi_calc_header_tbl as lc_new on (lg_new.id_calc = lc_new.id)
    left join lgi_change_reason_tbl as cr on (cr.id = cl.id_reason)
    left join syi_user as u1 on (u1.id = cl.id_usr)
    
    where date_trunc('month',cl.dt::date) = $p_mmgg and cl.processing = 1
    and (lg_old.id_paccnt is not null or lg_new.id_paccnt is not null) and 
    ((coalesce(lg_old.id_grp_lgt,0)     <>coalesce(lg_new.id_grp_lgt,0)) or 
     (coalesce(lg_old.id_calc,0) 	<>coalesce(lg_new.id_calc,0)) or 
     (coalesce(lg_old.family_cnt,0)     <>coalesce(lg_new.family_cnt,0)) or 
     (coalesce(lg_old.dt_start,'2030-01-01'::date)<>coalesce(lg_new.dt_start,'2030-01-01'::date)) or 
     (coalesce(lg_old.dt_end,'2030-01-01'::date)<>coalesce(lg_new.dt_end,'2030-01-01'::date) )
    )
    $where
    order by cl.dt,int_book,c.book,int_code,c.code ;";


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
            
            $abon = htmlspecialchars($row['fio_lgt']);
           
            if($res_code=='310')
                  $addr = $row['addr'];
            else
                  $addr = $row['town'].' '.$row['addr'];
            
            $addr = htmlspecialchars($addr);
            
            $book=$row['book'].'/'.$row['code'];
            $user = htmlspecialchars($row['user_name']);
            $lgt_name_new = htmlspecialchars($row['lgt_name_new']);
            $lgt_name_old = htmlspecialchars($row['lgt_name_old']);
            
            $calc_name_new = htmlspecialchars($row['calc_name_new']);
            $calc_name_old = htmlspecialchars($row['calc_name_old']);
            
            echo_html( "
            <tr >
            <td >{$i}</td>     
            <td class='c_t'>$book</td>
            <td>$addr</td>
            <td>$abon</td>
            <td class='c_t'>{$row['date_change_txt']}</td>
            <td class='c_t'>{$row['mode_name']}</td>
            
            <td class='c_t'>{$row['ident_old']}</td>
            <td class='c_t'>{$lgt_name_old}</td>
            <td class='c_t'>{$calc_name_old}</td>
            <td class='c_i'>{$row['family_cnt']}</td>
            <td class='c_t'>{$row['dt_start_txt']}</td>
            <td class='c_t'>{$row['dt_end_txt']}</td>

            <td class='c_t'>{$row['ident_new']}</td>
            <td class='c_t'>{$lgt_name_new}</td>
            <td class='c_t'>{$calc_name_new}</td>
            <td class='c_i'>{$row['family_cnt_new']}</td>
            <td class='c_t'>{$row['dt_start_new_txt']}</td>
            <td class='c_t'>{$row['dt_end_new_txt']}</td>
            
            <td class='c_t'>{$row['change_reason']}</td>
            <td class='c_t'>{$user}</td>
            <td class='c_t'>{$row['dt_txt']}</td>
            </tr>  ",$print_flag);

            $i++;
            
        }
        
    }
    

    eval("\$footer_text_eval = \"$footer_text\";");
    echo_html( $footer_text_eval,$print_flag);
   
    if ($print_flag==0) echo "</tbody> </table> ";

   
}
//----------------------------------------------------------------------------
if ($oper=='tarif_abon_edit')
{
    $p_dtb = sql_field_val('dt_b', 'date');
    $p_dte = sql_field_val('dt_e', 'date');
    $p_mmgg = sql_field_val('dt_b', 'mmgg');
    
    $p_id_sector = sql_field_val('id_sector', 'int');
    
    $p_id_person = sql_field_val('id_person', 'int');
    $p_id_town = sql_field_val('addr_town', 'int');
    $p_id_region = sql_field_val('id_region', 'int');
    $p_id_tar = sql_field_val('id_gtar', 'int');    

    $p_book = sql_field_val('book', 'string');
    $p_code = sql_field_val('code', 'string');
    
    $p_dtb_str = trim($_POST['dt_b']);
    $p_dte_str = trim($_POST['dt_e']);

    $period_str = " з $p_dtb_str до $p_dte_str";
    //$period_str = trim($_POST['period_str']);
    
    $params_caption = '';
    $where="";
    
    $summ_all      =0;
    $summ_tax_all  =0;

    $i=1;
    
    if ((isset($_POST['sector']))&&($_POST['sector']!=''))
        $params_caption.= ' Дiльниця: '. trim($_POST['sector']);

    if ((isset($_POST['person']))&&($_POST['person']!=''))
        $params_caption.= ' Оператор: '. trim($_POST['person']);
    
    if ((isset($_POST['addr_town_name']))&&($_POST['addr_town_name']!=''))
        $params_caption.= 'Населений пункт: '. trim($_POST['addr_town_name']);
    
    if ((isset($_POST['gtar']))&&($_POST['gtar']!=''))
        $params_caption .= ' Тарифна група : '. trim($_POST['gtar']);    
    
    if ($p_book!='null')
    {
        $where.= " and c.book = $p_book ";
        $params_caption .= " книга : $p_book ";
        
    }

    if ($p_code!='null')
    {
        $where.= " and c.code = $p_code ";
        $params_caption .= " рах. : $p_code ";
        
    }

   if ($p_id_person!='null')
   {
      $where.= " and  u1.id_person = $p_id_person  ";
   }

    if ($p_id_town!='null')
    {
        $where.= "and adr.id_town = $p_id_town ";
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
        $where.= " and (coalesce(c_old.id_gtar,0)= $p_id_tar or coalesce(c_new.id_gtar,0)= $p_id_tar ) ";
    }    
    
    $np=0;
    $print_flag = 1;
    
    eval("\$header_text = \"$header_text\";");
    echo_html( $header_text);
    
    $SQL = "  select c.book,c.code,cl.mode, c.note, cl.id_paccnt, cl.id_usr, cl.date_change, cl.dt,   cl.id_reason, cr.name as change_reason,
    u1.name as user_name,
 (adr.street||' '||
        (coalesce('буд.'||(c.addr).house||'','')||
		coalesce('/'||(c.addr).slash||' ','')|| 
			coalesce(' корп.'||(c.addr).korp||'','')||
				coalesce(', кв. '||(c.addr).flat,'')||
					coalesce('/'||(c.addr).f_slash,''))::varchar
    )::varchar as addr,
    (a.last_name||' '||coalesce(substr(a.name,1,1),'')||'.'||coalesce(substr(a.patron_name,1,1),''))||'.'::varchar as abon,
    ('0'||substring(c.code FROM '[0-9]+'))::int as int_code,
    ('0'||substring(c.book FROM '[0-9]+'))::int as int_book ,  
   
    to_char(cl.dt, 'DD.MM.YYYY HH24:MI') as dt_txt,
    to_char(cl.date_change, 'DD.MM.YYYY') as date_change_txt,

    c_old.id_gtar as id_gtar_old, 
    c_new.id_gtar as id_gtar_new,

    gt_old.sh_nm as tar_name_old,     
    gt_new.sh_nm as tar_name_new 
    
    from eqm_changelog_tbl as cl
    join clm_paccnt_h as c on (c.id =  cl.id_paccnt)
    join (select id, max(dt_b) as dt_b from clm_paccnt_h  where 
            ((dt_b < $p_dte and dt_e is null)
            or 
             tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_dtb::timestamp::abstime,$p_dte::timestamp::abstime))
        ) group by id order by id) as c2 
       on (c.id = c2.id and c.dt_b = c2.dt_b)
    join clm_abon_h as a on (a.id = c.id_abon) 
    join (select id, max(dt_b) as dt_b from clm_abon_h  where 
     ((dt_b < $p_dte and dt_e is null)
    or 
      tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_dtb::timestamp::abstime,$p_dte::timestamp::abstime))
    )
    group by id order by id) as a2 on (a.id = a2.id and a.dt_b = a2.dt_b)

    join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class)    
    
    left join clm_paccnt_h as c_old on (cl.id_paccnt = c_old.id and c_old.dt_e = cl.date_change)
    left join clm_paccnt_h as c_new on (cl.id_paccnt = c_new.id and c_new.dt_b = cl.date_change)

    left join aqi_grptar_tbl as gt_old on (gt_old.id =  c_old.id_gtar )
    left join aqi_grptar_tbl as gt_new on (gt_new.id =  c_new.id_gtar )

    left join lgi_change_reason_tbl as cr on (cr.id = cl.id_reason)
    left join syi_user as u1 on (u1.id = cl.id_usr)
    
    where  cl.mode = 32 
    and cl.dt >= $p_dtb and cl.dt <= $p_dte
    and cl.processing = 1
    and (c_old.id is not null or c_new.id is not null) and 
   (coalesce(c_old.id_gtar,0)  <>coalesce(c_new.id_gtar,0))
    $where
    order by cl.dt,int_book,c.book,int_code,c.code ;";

    //and date_trunc('month',cl.dt::date) = $p_mmgg 
    
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
            $addr = htmlspecialchars($row['addr']);
            $book=$row['book'].'/'.$row['code'];
            $user = htmlspecialchars($row['user_name']);
            $tar_name_old = htmlspecialchars($row['tar_name_old']);
            $tar_name_new = htmlspecialchars($row['tar_name_new']);
            $note = htmlspecialchars($row['note']);
            
            echo_html( "
            <tr >
            <td >{$i}</td>     
            <td class='c_t'>$book</td>
            <td>$abon</td>
            <td>$addr</td>
            <td class='c_t'>{$row['date_change_txt']}</td>
            <td class='c_t'>{$tar_name_old}</td>
            <td class='c_t'>{$tar_name_new}</td>
            <td class='c_t'>{$note}</td>
            
            <td class='c_t'>{$row['change_reason']}</td>
            <td class='c_t'>{$user}</td>
            <td class='c_t'>{$row['dt_txt']}</td>
            </tr>  ",$print_flag);

            $i++;
            
        }
        
    }
    

    eval("\$footer_text_eval = \"$footer_text\";");
    echo_html( $footer_text_eval,$print_flag);
   
    if ($print_flag==0) echo "</tbody> </table> ";

   
}
//------------------------------------------------------------------------------
if ($oper=='abon_list_new')
 {
        
    //$p_mmgg = sql_field_val('dt_b', 'mmgg');
    //$period_str = trim($_POST['period_str']);
    $p_mmgg1 = sql_field_val('dt_b', 'mmgg');
    $p_mmgg2 = sql_field_val('dt_e', 'mmgg');
    
    $mmgg1_str = ukr_date($_POST['dt_b'],0,1);
    $mmgg2_str = ukr_date($_POST['dt_e'],0,1);

    $period_str = "$mmgg1_str - $mmgg2_str";    

    
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
    
    
    $SQL = "select c.id,c.code,c.book, 
    adr.town,  (adr.street||' '||
        (coalesce('буд.'||(c.addr).house||'','')||
		coalesce('/'||(c.addr).slash||' ','')|| 
			coalesce(' корп.'||(c.addr).korp||'','')||
				coalesce(', кв. '||(c.addr).flat,'')||
					coalesce('/'||(c.addr).f_slash,''))::varchar
    )::varchar as addr,
    
   (a.last_name||' '||coalesce(substr(a.name,1,1),'')||'.'||coalesce(substr(a.patron_name,1,1),''))||'.'::varchar as abon,

    m.power,m.num_meter,m.coef_comp,m.carry, m.id_type_meter,
    to_char(m.dt_start, 'DD.MM.YYYY') as dt_start, to_char(m.dt_control, 'DD.MM.YYYY') as dt_control,
    im.name as type_meter,zzz.indic_str, c.note,
    ('0'||substring(c.code FROM '[0-9]+'))::int as int_code,
    ('0'||substring(c.book FROM '[0-9]+'))::int as int_book
from 
    clm_paccnt_tbl as c
    join clm_meterpoint_h as m on (m.id_paccnt = c.id) 
    join (select id, max(dt_b) as dt_b from clm_meterpoint_h  where 
     ((dt_b < ($p_mmgg2::date+'1 month'::interval) and dt_e is null)
          or 
          tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg2::timestamp::abstime,($p_mmgg2::date+'1 month - 1 day'::interval)::timestamp::abstime))
     )
    group by id order by id) as m2 
    on (m.id = m2.id and m.dt_b = m2.dt_b)

    join clm_abon_tbl as a on (a.id = c.id_abon) 
    --join (select id, max(dt_b) as dt_b from clm_abon_h  where  
    --((dt_b < ($p_mmgg2::date+'1 month'::interval) and dt_e is null)
    --        or 
    --        tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg2::timestamp::abstime,($p_mmgg2::date+'1 month - 1 day'::interval)::timestamp::abstime))
    --    )
    --   group by id order by id) as a2 on (a.id = a2.id and a.dt_b = a2.dt_b)
    join ( select id_paccnt, trim(sum(indic||','),',')::varchar as indic_str from 
     (
      select i.id_paccnt, i.id_zone, CASE WHEN z.id = 0 THEN (i.value::int)::varchar ELSE z.nm||':'||((i.value::int)::varchar) END::varchar as indic
      from acm_indication_tbl as i 
      join (select id_paccnt, min(dat_ind) as min_dat from acm_indication_tbl 
      group by id_paccnt
      ) as mi
      on (i.id_paccnt = mi.id_paccnt and i.dat_ind = mi.min_dat)
      join eqk_zone_tbl as z on (z.id = i.id_zone)
       order by i.id_zone
     ) as ss
    group by id_paccnt
    ) as zzz on (zzz.id_paccnt = c.id)
    
    join eqi_meter_tbl as im on (im.id = m.id_type_meter)
    join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class)
where c.archive=0 and date_trunc('month',c.dt_b) >= $p_mmgg1::date 
                  and date_trunc('month',c.dt_b) <= ($p_mmgg2::date+'1 month - 1 day'::interval)::date
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
                <td COLSPAN=11 class = 'tab_head'>{$town_str}</td>
                </tr>  ",$print_flag);        
                $i=1;
            }
            
            $abon = htmlspecialchars($row['abon']);
            if ($p_town_detal==1)
                $addr = $row['addr'];
            else
                $addr = $row['town'].' '.$row['addr'];
            
            $book=$row['book'];
            $code=$row['code'];
            $power_txt = number_format_ua ($row['power'],1);  
            $indic = htmlspecialchars($row['indic_str']);
            $note = htmlspecialchars($row['note']);
           
            echo_html("
            <tr >
            <td>{$i}</td>
            <td class='c_t'>{$book}</td>
            <td class='c_t'>{$code}</td>
            <td>{$abon}</td>
            <td>{$addr}</td>
            <td >{$row['dt_start']}</td>
            <td class='c_t'>{$row['type_meter']}</td>
            <td class='c_t'>{$row['num_meter']}</td>
            <td class='c_t'>{$indic}</td>            
            <td class='c_n'>{$power_txt}</td>
            <td class='c_t'>{$note}</td>            
            </tr>  ",$print_flag);        
            $i++;
        }
        
    }
    
    eval("\$footer_text_eval = \"$footer_text\";");
    echo_html($footer_text_eval);

    //return;
    
}
//---------------------------------------------------------------------
if ($oper=='indic_list_off')
{
    $p_mmgg = sql_field_val('dt_b', 'mmgg');
    
    list($year, $month, $day  ) = split('[/.-]', $p_mmgg);
    $month_str = ukr_month((int)$month,0);

    $p_id_tar = sql_field_val('id_gtar', 'int');        
    $period = "за $month_str $year р.";

    $p_id_sector = sql_field_val('id_sector', 'int');
    $p_book = sql_field_val('book', 'string');
    $p_id_person = sql_field_val('id_person', 'int');
    $p_id_operation = sql_field_val('id_operation', 'int');
    $p_id_region = sql_field_val('id_region', 'int');
    
    $p_id_town = sql_field_val('addr_town', 'int');
    $p_id_tar = sql_field_val('id_gtar', 'int');    
    $p_id_type_meter = sql_field_val('id_type_meter', 'int');    
    $p_id_type_meter_array = sql_field_val('id_type_meter_array', 'array');    
    //$p_value = sql_field_val('sum_value', 'numeric');    
    
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

    
    if ((isset($_POST['addr_town_name']))&&($_POST['addr_town_name']!=''))
        $params_caption.= 'Населений пункт: '. trim($_POST['addr_town_name']);
    
   
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
   
    if ($p_id_town!='null')
    {
        $where.= "and adr.id_town = $p_id_town ";
    }

    $i=1;
    
    $np=0;
    $print_flag = 1;
    $order ='int_book, book, int_code,code';
    
    
    eval("\$header_text = \"$header_text\";");
    echo_html( $header_text);
    
    $SQL = "select c.book,c.code,i.value::int,
    i.dat_ind,i.value_diff::int as demand,i.num_eqp,
 i.id_operation, it.name as indicoper ,i.id_zone, z.nm as zone,
 to_char( sw_dt, 'DD.MM.YYYY') as  sw_dt_txt,    
 to_char( i.dt, 'DD.MM.YYYY') as  dt_txt,        
 to_char( i.dat_ind, 'DD.MM.YYYY') as  dat_ind_txt,    
 (select value::int from acm_indication_tbl as si where si.id_paccnt = c.id and id_zone = i.id_zone
    and dat_ind >= sw.sw_dt order by dat_ind limit 1) as value_off,   
(ab.last_name||' '||coalesce(ab.name,'')||' '||coalesce(ab.patron_name,''))::varchar as abon,
 (adr.street||' '||
        (coalesce('буд.'||(c.addr).house||'','')||
		coalesce('/'||(c.addr).slash||' ','')|| 
			coalesce(' корп.'||(c.addr).korp||'','')||
				coalesce(', кв. '||(c.addr).flat,'')||
					coalesce('/'||(c.addr).f_slash,''))::varchar
    )::varchar as addr,
    regexp_replace(regexp_replace(c.code, '-.*?$', '') , '[^0-9]', '','g')::int as int_code,
    regexp_replace(regexp_replace(c.book, '-.*?$', '') , '[^0-9]', '','g')::int as int_book,
  rs.name as sector, u1.name as user_name, pr.represent_name  
from acm_indication_tbl as i
join clm_paccnt_tbl c on (c.id=i.id_paccnt)
join clm_abon_tbl as ab on (ab.id = c.id_abon) 
join eqi_meter_tbl as m on (m.id = i.id_typemet)
join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class)

join (
       select csw.id_paccnt, coalesce(csw.dt_action,csw.dt_create) as sw_dt , csw.action, a.name as sw_name
       from clm_switching_tbl as csw 
        join cli_switch_action_tbl as a on (a.id = csw.action )
        join (select id_paccnt, max(dt_action) as maxdt from clm_switching_tbl where action in (1,2) group by id_paccnt) as csdt 
       on (csw.id_paccnt = csdt.id_paccnt and csw.dt_action = csdt.maxdt) 
       left join clm_switching_tbl as cswc on (csw.id_paccnt = cswc.id_paccnt and cswc.dt_action >= csw.dt_action and ((cswc.action =3 and csw.action =1) or (cswc.action =4  and csw.action =2) ) ) 
       where csw.action =1 and cswc.id_paccnt is null  
       -- and coalesce(csw.dt_action,csw.dt_create) < $p_mmgg+'1 month'::interval
       order by csw.id_paccnt
 ) as sw on (sw.id_paccnt = c.id and i.dat_ind > sw.sw_dt)    
 left join syi_user as u1 on (u1.id = i.id_person)
 left join eqk_zone_tbl as z on (z.id = i.id_zone)
 left join cli_indic_type_tbl as it on (it.id = i.id_operation)
 left join prs_runner_paccnt as rp on (rp.id_paccnt = c.id)
 left join prs_runner_sectors as rs on (rp.id_sector = rs.id) 
 left join prs_persons as pr on (rs.id_runner = pr.id) 
 where i.mmgg = $p_mmgg::date  and i.value_diff <>0 
$where
 order by $order ;";

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
            $addr = htmlspecialchars($row['addr']);
            $sector = htmlspecialchars($row['sector']);
            $inspector = htmlspecialchars($row['represent_name']);
            

            $oper = htmlspecialchars($row['indicoper']);
            $user_name = htmlspecialchars($row['user_name']);
            
            $book=$row['book'];
            $code=$row['code'];


            echo_html( "
            <tr >
            <td>{$i}</td>            
            <td class='c_t'>$book</td>
            <td>$code</td>
            <td>$abon</td>
            <td>$addr</td>
            <td>$sector</td>
            <td>$inspector</td>
            <td class='c_t'>{$row['sw_dt_txt']}</td>
            <td class='c_i'>{$row['value_off']}</td>
            <td class='c_t'>{$row['num_eqp']}</td>
            <td class='c_t'>{$row['zone']}</td>
            <td class='c_t'>{$row['dat_ind_txt']}</td>
            <td class='c_i'>{$row['value']}</td>
            <td>$oper</td>
            <td class='c_i'>{$row['demand']}</td>
            <td class='c_t'>{$user_name}</td>
            <td class='c_t'>{$row['dt_txt']}</td>
            </tr>  ",$print_flag); 

            $i++;
            
            
        }
        
    }

    eval("\$footer_text_eval = \"$footer_text\";");

    echo_html( $footer_text_eval,$print_flag);
   
    if ($print_flag==0) echo "</tbody> </table> ";
   
}
//----------------------------------------------------------------------------
if ($oper=='switch_rep')
{
    $p_dtb = sql_field_val('dt_b', 'date');
    $p_dte = sql_field_val('dt_e', 'date');
    
    $p_dtb_str = trim($_POST['dt_b']);
    $p_dte_str = trim($_POST['dt_e']);

    $period = " з $p_dtb_str до $p_dte_str";

    $p_id_sector = sql_field_val('id_sector', 'int'); 
    $p_book = sql_field_val('book', 'string');
    $p_id_town = sql_field_val('addr_town', 'int');
    $p_id_region = sql_field_val('id_region', 'int');
    
    $where="where";
    
   
    $params_caption = '';
    
    if ((isset($_POST['sector']))&&($_POST['sector']!=''))
        $params_caption.= ' Дiльниця: '. trim($_POST['sector']);

    
    if ((isset($_POST['addr_town_name']))&&($_POST['addr_town_name']!=''))
        $params_caption.= 'Населений пункт: '. trim($_POST['addr_town_name']);
    
   
    if ($p_book!='null') 
    {
        if ($where!='where') $where.= " and ";
        $where.= " c.book = $p_book ";
        $params_caption .= " книга : $p_book ";
        
    }

   if ($p_id_sector!='null')
   {
      if ($where!='where') $where.= " and "; 
      $where.= " rp.id_sector = $p_id_sector  ";
   }
  
    if ($p_id_region!='null')
    {
        if ($where!='where') $where.= " and "; 
        $where.= " rs.id_region =  $p_id_region ";
        
        //if ($p_id_region==1) $params_caption=" Деснянський район";
        //if ($p_id_region==2) $params_caption=" Новозаводський район";
        $SQL_H="SELECT name from cli_region_tbl where id = $p_id_region ;";
    
        $result = pg_query($Link, $SQL_H) or die("SQL Error: " . pg_last_error($Link) . $SQL_H);
        $row = pg_fetch_array($result);
        
        $params_caption.= $row['name'];        

    }
   
   
    if ($p_id_town!='null')
    {
        if ($where!='where') $where.= " and ";
        $where.= " adr.id_town = $p_id_town ";
    }

    if ($where =="where") $where="" ;
    $i=1;
    
    $np=0;
    $print_flag = 1;
    $order ='date_off, int_book, book, int_code,code';
    
    $SQL = "select warning_off_fun();";
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    
    $SQL = "select rep_off_on_fun($p_dtb, $p_dte );";
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    
    
    eval("\$header_text = \"$header_text\";");
    echo_html( $header_text); 
    
    $SQL = "select c.book,c.code,
 to_char( r.date_off, 'DD.MM.YYYY') as  date_off_txt,    
 to_char( r.date_on, 'DD.MM.YYYY') as  date_on_txt,        
 to_char( r.date_warning, 'DD.MM.YYYY') as  date_warning_txt,            
 to_char( r.date_warning_print, 'DD.MM.YYYY') as  date_warning_print_txt,
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
    rs.name as sector, pr.represent_name, cn.represent_name as off_name, of.act_num, of.comment,
    r.sum_debet, r.sum_pay, r.indic_off, sp.name as switch_place , 
    ( select m.power  from
      clm_meterpoint_h as m 
      join (select id, max(dt_b) as dt_b from clm_meterpoint_h  where id_paccnt = r.id_paccnt 
       and dt_b <= r.date_off and coalesce(dt_e,r.date_off) >=r.date_off group by id order by id) as m2 
       on (m.id = m2.id and m.dt_b = m2.dt_b) order by m.dt_b desc limit 1
    ) as power_off
from rep_off_on_tbl as r
join clm_paccnt_tbl c on (c.id=r.id_paccnt)
join clm_abon_tbl as ab on (ab.id = c.id_abon) 
join prs_runner_paccnt as rp on (rp.id_paccnt = c.id)
join prs_runner_sectors as rs on (rs.id = rp.id_sector)
join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class)
join clm_switching_tbl as of on (of.id = r.id_off)   
left join cli_switch_place_tbl as sp on (sp.id = of.id_switch_place )    
left join prs_persons as cn on (cn.id = of.id_position)  
left join prs_persons as pr on (rs.id_runner = pr.id)     
$where
 order by $order ;";

    //echo $SQL;
    //throw new Exception(json_encode($SQL));
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        //$i=0;
        $sum_debet_all=0;
        $sum_pay_all=0;
        $sum_power_off=0;

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
            
            $sector = htmlspecialchars($row['sector']);
            $inspector = htmlspecialchars($row['represent_name']);
            $inspector_off = htmlspecialchars($row['off_name']);
            $note = htmlspecialchars($row['comment']);
            
            $sum_debet = number_format_ua ($row['sum_debet'],2);  
            $sum_pay = number_format_ua ($row['sum_pay'],2);  
            $power_off = number_format_ua ($row['power_off'],2);  
            
            $book=$row['book'];
            $code=$row['code'];
 
            echo_html( "
            <tr >
            <td>{$i}</td>            
            <td class='c_t'>$book</td>
            <td>$code</td>
            <td>$abon</td>
            <td>$addr</td>
            <td>$sector</td>
            <td>$inspector</td>
            <td class='c_t'>{$row['act_num']}</td>
            <td class='c_t'>{$row['date_off_txt']}</td>
            <td class='c_t'>{$row['date_on_txt']}</td>
            <td class='c_n'>$sum_debet</td>
            <td class='c_n'>$sum_pay</td>
            <td class='c_t'>{$row['indic_off']}</td>
            <td class='c_n'>{$power_off}</td>
            <td class='c_t'>{$row['switch_place']}</td>
            <td class='c_t'>{$inspector_off}</td>
            <td class='c_t'>{$note}</td>
            <td class='c_t'>{$row['date_warning_print_txt']}</td>
            <td class='c_t'>{$row['date_warning_txt']}</td>
            </tr>  ",$print_flag); 

            $i++;
            
            $sum_debet_all+=$row['sum_debet'];
            $sum_pay_all+=$row['sum_pay'];
            $sum_power_off+=$row['power_off'];
            
        }
        
    }

    $sum_debet = number_format_ua ($sum_debet_all,2);  
    $sum_pay = number_format_ua ($sum_pay_all,2);  
    
    eval("\$footer_text_eval = \"$footer_text\";");

    echo_html( $footer_text_eval,$print_flag);
   
    if ($print_flag==0) echo "</tbody> </table> ";
   
}
//---------------------------------------------------------------------
if (($oper=='agreem_rep')||($oper=='agreem_rep_all'))
{
    $p_dtb = sql_field_val('dt_b', 'date');
    $p_dte = sql_field_val('dt_e', 'date');
    
    $p_dt = sql_field_val('dt_rep', 'date');
    
    $p_dtb_str = trim($_POST['dt_b']);
    $p_dte_str = trim($_POST['dt_e']);
    
    $period = "";

    $p_id_sector = sql_field_val('id_sector', 'int');
    $p_book = sql_field_val('book', 'string');
    $p_id_town = sql_field_val('addr_town', 'int');
    $p_id_region = sql_field_val('id_region', 'int');   
    
    $where="where";
    
    $order ='int_book, book, int_code,code';
    if($oper=='agreem_rep')
    {
        $where.=" ag.date_agreem >= $p_dtb and ag.date_agreem <= $p_dte ";
        $period = " з $p_dtb_str до $p_dte_str";
        $order ='date_agreem, int_book, book, int_code,code';
    }
    if($oper=='agreem_rep_all')
    {
        $where.=" ag.date_agreem <= $p_dt and (ag.dt_e is null or ag.dt_e >= $p_dt or ag.id_iagreem = 2) ";
        $period = " з $p_dtb_str до $p_dte_str";
    }
    
    $params_caption = '';
    
    if ((isset($_POST['sector']))&&($_POST['sector']!=''))
        $params_caption.= ' Дiльниця: '. trim($_POST['sector']);

    
    if ((isset($_POST['addr_town_name']))&&($_POST['addr_town_name']!=''))
        $params_caption.= 'Населений пункт: '. trim($_POST['addr_town_name']);
    
   
    if ($p_book!='null') 
    {
        if ($where!='where') $where.= " and ";
        $where.= " c.book = $p_book ";
        $params_caption .= " книга : $p_book ";
        
    }

   if ($p_id_sector!='null')
   {
      if ($where!='where') $where.= " and "; 
      $where.= " rp.id_sector = $p_id_sector  ";
   }
  
    if ($p_id_town!='null')
    {
        if ($where!='where') $where.= " and ";
        $where.= " adr.id_town = $p_id_town ";
    }

    if ($p_id_region!='null')
    {
        
        $where.= " and rs.id_region =  $p_id_region ";
        
        $SQL_H="SELECT name from cli_region_tbl where id = $p_id_region ;";
    
        $result = pg_query($Link, $SQL_H) or die("SQL Error: " . pg_last_error($Link) . $SQL_H);
        $row = pg_fetch_array($result);
        
        $params_caption.= $row['name'];        
       
    }    
    
    if ($where =="where") $where="" ;

    $i=1;
    
    $np=0;
    $print_flag = 1;
    
    
    eval("\$header_text = \"$header_text\";");
    echo_html( $header_text);
    
    $SQL = "select c.book, c.code, ag.num_agreem, ag.date_agreem, ag.dt_e,  ag.power, m.num_meter,im.name as type_meter,
(a.last_name||' '||coalesce(a.name,'')||' '||coalesce(a.patron_name,''))::varchar as abon,
 adr.town, (adr.street||' '||
        (coalesce('буд.'||(c.addr).house||'','')||
		coalesce('/'||(c.addr).slash||' ','')|| 
			coalesce(' корп.'||(c.addr).korp||'','')||
				coalesce(', кв. '||(c.addr).flat,'')||
					coalesce('/'||(c.addr).f_slash,''))::varchar
    )::varchar as addr,
    ('0'||substring(c.code FROM '[0-9]+'))::int as int_code,
    ('0'||substring(c.book FROM '[0-9]+'))::int as int_book,

    to_char(ag.dt, 'DD.MM.YYYY HH24:MI') as dt_txt,
    to_char(ag.date_agreem, 'DD.MM.YYYY') as date_agreem_txt,
    CASE WHEN ag.id_iagreem = 2 THEN to_char(ag.dt_e, 'DD.MM.YYYY') END as dt_e_txt,   
rs.name as sector, u1.name as user_name, pr.represent_name, c.note,
CASE WHEN  coalesce(c.dt_dod,'2001-01-01'::date) = '2001-01-01' THEN '' ELSE 'Так' END as is_dod_agr,
CASE WHEN  coalesce(c.dt_dod,'2001-01-01'::date) = '2001-01-01' THEN null 
     ELSE to_char(c.dt_dod, 'DD.MM.YYYY') END as dod_agr_date,
(select trim(sum(CASE WHEN z.id = 0 THEN (i.value::int)::varchar ELSE z.nm||':'||((i.value::int)::varchar) END::varchar||','),',')::varchar
 from acm_indication_tbl as i 
 join (select max(dat_ind) as dat_ind from acm_indication_tbl where dat_ind <= ag.date_agreem  and  id_paccnt = ag.id_paccnt) as ii on  (i.dat_ind = ii.dat_ind)
 join eqk_zone_tbl as z on (z.id = i.id_zone)
 where i.id_paccnt = ag.id_paccnt and i.dat_ind = ii.dat_ind)::varchar as indic
 from clm_agreem_tbl as ag
    join clm_paccnt_tbl as c on (c.id = ag.id_paccnt)
    join clm_meterpoint_tbl as m on (m.id_paccnt = c.id) 
    join clm_abon_tbl as a on (a.id = c.id_abon) 
    join eqi_meter_tbl as im on (im.id = m.id_type_meter)
    join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class)
    join prs_runner_paccnt as rp on (rp.id_paccnt = c.id)
    join prs_runner_sectors as rs on (rp.id_sector = rs.id) 
    left join prs_persons as pr on (rs.id_runner = pr.id) 
    left join syi_user as u1 on (u1.id = ag.id_person and ag.id_person <>0)         
$where
 order by $order ;";

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
            
            $sector = htmlspecialchars($row['sector']);
            $inspector = htmlspecialchars($row['represent_name']);

            $note = htmlspecialchars($row['note']);
            
            $book=$row['book'];
            $code=$row['code'];
 
            echo_html( "
            <tr >
            <td>{$i}</td>            
            <td class='c_t'>$book</td>
            <td>$code</td>
            <td class='c_t'>{$row['date_agreem_txt']}</td>
            <td class='c_t'>{$row['dt_e_txt']}</td>
            <td>$abon</td>
            <td>$addr</td>
            <td>$sector</td>
            <td>$inspector</td>
            <td class='c_n'>{$row['power']}</td>
            <td class='c_t'>{$row['num_meter']}</td>
            <td class='c_t'>{$row['type_meter']}</td>
            <td class='c_t'>{$row['indic']}</td>
            
            <td class='c_t'>{$row['is_dod_agr']}</td>
            <td class='c_t'>{$row['dod_agr_date']}</td>
            <td class='c_t'>{$note}</td>
            <td class='c_t'>{$row['user_name']}</td>
            <td class='c_t'>{$row['dt_txt']}</td>            
            </tr>  ",$print_flag); 

            $i++;
        }
    }

    eval("\$footer_text_eval = \"$footer_text\";");

    echo_html( $footer_text_eval,$print_flag);
   
    if ($print_flag==0) echo "</tbody> </table> ";
   
}
//---------------------------------------------------------------------
if ($oper=='warning_calc')
{
    $p_mmgg = sql_field_val('dt_b', 'mmgg');
    $mmgg1_str = trim($_POST['period_str']);
    $p_dt = sql_field_val('dt_rep', 'date');
    $p_dt_txt = trim($_POST['dt_rep']);
    $p_value = sql_field_val('sum_value', 'numeric');
    $p_id_sector = sql_field_val('id_sector', 'int');
    $p_id_region = sql_field_val('id_region', 'int');
    $p_id_town = sql_field_val('addr_town', 'int');
    $p_book = sql_field_val('book', 'string');
    $p_id_paccnt = sql_field_val('id_paccnt', 'int');
    
    $SQL = "select ''''||to_char( value_ident::date , 'YYYY-MM-DD')||'''' as start_mmgg ,
        to_char( ($p_mmgg::date - '1 month'::interval)::date , 'DD.MM.YYYY') as dt_sum
        from syi_sysvars_tbl where ident = 'dt_start';";
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);    
    
    if ($result) {
        $row = pg_fetch_array($result); 
        $start_mmgg=$row['start_mmgg'];
        $dt_sum=$row['dt_sum'];
    }
        
    
    
    $params_caption='';
    $where ='';
    if ($p_id_sector!='null')
    {
        $where.= " and rp.id_sector = $p_id_sector ";
        $params_caption.= ' Дiльниця: '. trim($_POST['sector']);
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
    
    
    if ((isset($_POST['addr_town_name']))&&($_POST['addr_town_name']!=''))
        $params_caption.= 'Населений пункт: '. trim($_POST['addr_town_name']);
    
    if ($p_id_town!='null')
    {
        if ($where!='where') $where.= " and ";
        $where.= " id_town = $p_id_town ";
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
            if ($where!='where') $where.= " and ";
            $where.= " acc.book = $p_book ";
            $params_caption .= " книга : $p_book ";
        }
    }
    
    
    eval("\$header_text = \"$header_text\";");
    
    echo $header_text;
    
    if ($p_mmgg <= $start_mmgg)
    {    
    $SQL = " select * from (select acc.book, acc.code, 
 (adr.town||', '||adr.street||' '||
   (coalesce('буд.'||(acc.addr).house||'','')||
		coalesce('/'||(acc.addr).slash||' ','')||
			coalesce(' корп.'||(acc.addr).korp||'','')||
				coalesce(', кв. '||(acc.addr).flat,'')||
					coalesce('/'||(acc.addr).f_slash||' ',''))::varchar
)::varchar as addr,
(c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon,
 ('0'||substring(acc.code FROM '[0-9]+'))::int as int_code,
 ('0'||substring(acc.book FROM '[0-9]+'))::int as int_book,
 dt_all, p1.pay, b1.bill_corr, 
  (dt_all - coalesce(p1.pay,0) + coalesce(b1.bill_corr,0)) as debet_now,
  CASE WHEN sw2.dt_create is not null and ( sw.dt_action is null or sw.dt_action < sw2.dt_create ) THEN
  'створено попередження '||to_char(sw2.dt_create, 'DD.MM.YYYY') ELSE  sw.sw_action||' '||to_char(sw.dt_action, 'DD.MM.YYYY')   END as sw_action,
   adr.id_town
from 
   (
     select id_paccnt, sum(value) as dt_all
     from acm_bill_tbl where id_pref = 10 
      and mmgg = ($start_mmgg::date - '1 month'::interval)::date 
      and mmgg_bill <= ($p_mmgg::date - '2 month'::interval)::date
     group by id_paccnt order by id_paccnt
   ) as s
   join clm_paccnt_tbl as acc on (acc.id = s.id_paccnt)
   join clm_abon_tbl as c on (c.id = acc.id_abon) 
   join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class)
   join prs_runner_paccnt as rp on (rp.id_paccnt = acc.id)
   join prs_runner_sectors as rs on (rs.id = rp.id_sector)
   left join
   (
    select id_paccnt, sum(value) as pay from acm_pay_tbl where 
    mmgg >= $p_mmgg::date and id_pref = 10 and greatest(pay_date,reg_date) <= $p_dt
    group by id_paccnt order by id_paccnt
   ) as p1 on (p1.id_paccnt = s.id_paccnt )

   left join
   (
    select id_paccnt, sum(value) as bill_corr from acm_bill_tbl where 
    mmgg >= $p_mmgg::date- '1 month'::interval and 
   (mmgg_bill < $p_mmgg::date- '1 month'::interval  or  (idk_doc = 220 and reg_num <>'' and value <0 and coalesce(demand,0) = 0))
    and id_pref = 10 
    group by id_paccnt 
    having sum(value) < 0 
    order by id_paccnt
   ) as b1 on (b1.id_paccnt = s.id_paccnt )
   left join 
        (
            select csw.id_paccnt, sn.name as sw_action,csw.dt_action
            from clm_switching_tbl as csw 
            join (select id_paccnt, max(dt_action) as maxdt from clm_switching_tbl where action in (1,2) group by id_paccnt) as csdt 
            on (csw.id_paccnt = csdt.id_paccnt and csw.dt_action = csdt.maxdt) 
            join cli_switch_action_tbl as sn on (sn.id = csw.action)
            left join clm_switching_tbl as cswc on (csw.id_paccnt = cswc.id_paccnt and cswc.dt_action >= csw.dt_action and ((cswc.action =3 and csw.action =1) or (cswc.action =4  and csw.action =2) ) ) 
            where csw.action not in (3,4) and cswc.id_paccnt is null 
        ) as sw on (sw.id_paccnt = s.id_paccnt)    
    left join
        (
            select csw.id_paccnt, csw.dt_create
            from clm_switching_tbl as csw 
            where csw.dt_action is null and csw.action =2
        ) as sw2 on (sw2.id_paccnt = s.id_paccnt)    
where (dt_all - coalesce(p1.pay,0) + coalesce(b1.bill_corr,0)) >= $p_value 
   and dt_all >= $p_value 
 $where
) as ss     
order by int_book, int_code;";

    }
    else
    {
    $SQL = " select * from (select acc.book, acc.code, 
 (adr.town||', '||adr.street||' '||
   (coalesce('буд.'||(acc.addr).house||'','')||
		coalesce('/'||(acc.addr).slash||' ','')||
			coalesce(' корп.'||(acc.addr).korp||'','')||
				coalesce(', кв. '||(acc.addr).flat,'')||
					coalesce('/'||(acc.addr).f_slash||' ',''))::varchar
)::varchar as addr,
(c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon,
 ('0'||substring(acc.code FROM '[0-9]+'))::int as int_code,
 ('0'||substring(acc.book FROM '[0-9]+'))::int as int_book,
 dt_all, p1.pay, b1.bill_corr, 
  (dt_all - coalesce(p1.pay,0) + coalesce(b1.bill_corr,0)) as debet_now,
  CASE WHEN sw2.dt_create is not null and ( sw.dt_action is null or sw.dt_action < sw2.dt_create ) THEN
  'створено попередження '||to_char(sw2.dt_create, 'DD.MM.YYYY') ELSE  sw.sw_action||' '||to_char(sw.dt_action, 'DD.MM.YYYY')   END as sw_action,
  adr.id_town
from 
   (
    select id_paccnt, sum(b_dtval)-sum(b_ktval) as dt_all
    from seb_saldo where mmgg = ($p_mmgg::date - '1 month'::interval)::date and id_pref = 10 
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
   ) as s
   join clm_paccnt_tbl as acc on (acc.id = s.id_paccnt)
   join clm_abon_tbl as c on (c.id = acc.id_abon) 
   join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class)
   join prs_runner_paccnt as rp on (rp.id_paccnt = acc.id)
   join prs_runner_sectors as rs on (rs.id = rp.id_sector)
   left join
   (
    select id_paccnt, sum(value) as pay from acm_pay_tbl where 
    mmgg >= $p_mmgg::date- '1 month'::interval and id_pref = 10 and reg_date <= $p_dt
    and idk_doc <> 1000                 
    group by id_paccnt order by id_paccnt
   ) as p1 on (p1.id_paccnt = s.id_paccnt )

   left join
   (
    select id_paccnt, sum(value) as bill_corr from acm_bill_tbl where 
    mmgg >= $p_mmgg::date- '1 month'::interval and 
   (mmgg_bill < $p_mmgg::date- '1 month'::interval  or  (idk_doc = 220 and reg_num <>'' and value <0 and coalesce(demand,0) = 0))        
    and id_pref = 10 
    and idk_doc <> 1000                 
    group by id_paccnt 
    having sum(value) < 0 
    order by id_paccnt
   ) as b1 on (b1.id_paccnt = s.id_paccnt )
   left join 
        (
            select csw.id_paccnt, sn.name as sw_action,csw.dt_action
            from clm_switching_tbl as csw 
            join (select id_paccnt, max(dt_action) as maxdt from clm_switching_tbl where action in (1,2) group by id_paccnt) as csdt 
            on (csw.id_paccnt = csdt.id_paccnt and csw.dt_action = csdt.maxdt) 
            join cli_switch_action_tbl as sn on (sn.id = csw.action)
            left join clm_switching_tbl as cswc on (csw.id_paccnt = cswc.id_paccnt and cswc.dt_action >= csw.dt_action and ((cswc.action =3 and csw.action =1) or (cswc.action =4  and csw.action =2) ) ) 
            where csw.action not in (3,4) and cswc.id_paccnt is null 
        ) as sw on (sw.id_paccnt = s.id_paccnt)    
    left join
        (
            select csw.id_paccnt, csw.dt_create
            from clm_switching_tbl as csw 
            where csw.dt_action is null and csw.action =2
        ) as sw2 on (sw2.id_paccnt = s.id_paccnt)    
where (dt_all - coalesce(p1.pay,0) + coalesce(b1.bill_corr,0)) >= $p_value 
   and dt_all >= $p_value 
 $where
) as ss     
order by int_book, int_code;";
        
    }
   //echo $SQL; 
   // throw new Exception(json_encode($SQL));
   $sum_debet = 0;
   $sum_pay = 0;
   $sum_bill = 0;
   $sum_now = 0;
    
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        $i=1;
        while ($row = pg_fetch_array($result)) {

           
            $abon = htmlspecialchars($row['abon']);
            $addr = htmlspecialchars($row['addr']);
            $book=$row['book'];
            $code=$row['code'];
            //$sum_cnt_all++;
            //$sum_saldo+=$row['sum_warning'];
            
            $sum_debet_txt = number_format_ua ($row['dt_all'], 2);
            $sum_pay_txt = number_format_ua ($row['pay'], 2);
            $sum_bill_txt = number_format_ua ($row['bill_corr'], 2);
            $sum_now_txt = number_format_ua ($row['debet_now'], 2);
            
            echo_html( "
            <tr >
            <td class='c_i'>{$i}</td>                    
            <td class='c_t'>{$book}</td>
            <td class='c_t'>{$code}</td>
            <td>{$abon}</td>
            <td>{$addr}</td>
            <td class='c_n'>{$sum_debet_txt}</td>
            <td class='c_n'>{$sum_pay_txt}</td>
            <td class='c_n'>{$sum_bill_txt}</td>
            <td class='c_n'>{$sum_now_txt}</td>
            
            <td class='c_t'>{$row['sw_action']}</td>
            </tr>  ");        
            $i++;
            
            
            $sum_debet+= $row['dt_all'];
            $sum_pay+= $row['pay'];
            $sum_bill+= $row['bill_corr'];
            $sum_now+= $row['debet_now'];
            
        }
        
    }

    $sum_debet_txt = number_format_ua ($sum_debet, 2);
    $sum_pay_txt = number_format_ua ($sum_pay, 2);
    $sum_bill_txt = number_format_ua ($sum_bill, 2);
    $sum_now_txt = number_format_ua ($sum_now, 2);
    
    eval("\$footer_text_eval = \"$footer_text\";");
    echo $footer_text_eval;

    //return;
    
}
//----------------------------------------------------------------------------
if (($oper=='warning_pay')||($oper=='warning_pay_ww'))
{
    $p_mmgg = sql_field_val('dt_b', 'mmgg');
    $mmgg1_str = trim($_POST['period_str']);
    $p_dt = sql_field_val('dt_rep', 'date');
    $p_dt_txt = trim($_POST['dt_rep']);
    //$p_value = sql_field_val('sum_value', 'numeric');
    $p_id_sector = sql_field_val('id_sector', 'int');
    $p_id_region = sql_field_val('id_region', 'int');
    $p_id_town = sql_field_val('addr_town', 'int');
    $p_book = sql_field_val('book', 'string');
    
    
    $params_caption='';
    $where ='';
    if ($p_id_sector!='null')
    {
        $where.= " and rp.id_sector = $p_id_sector ";
        $params_caption.= ' Дiльниця: '. trim($_POST['sector']);
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
    
    if ((isset($_POST['addr_town_name']))&&($_POST['addr_town_name']!=''))
        $params_caption .= 'населений пункт: '. trim($_POST['addr_town_name']);
    
    if ($p_id_town!='null')
    {
        $where.= "and adr.id_town = $p_id_town ";
    }    
    
    if ($p_book!='null')
    {
        $where.= " and acc.book = $p_book ";
        $params_caption .= " книга : $p_book ";
    }    
    
    if($oper=='warning_pay_ww')
    {
        $where.= " and coalesce(dt_warning,(dt_action+'1 month'::interval)::date) <= $p_dt ";
        $params_caption.= 'Прострочені попередження ';
        
    }
    
    eval("\$header_text = \"$header_text\";");
    
    echo $header_text;
    
    $SQL = "select warning_off_fun();";
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    
    
    
    $SQL = " select *, coalesce(sum_warning,0)-coalesce(sum_pay,0)-coalesce(sum_recalc,0) as sum_delta from (
    
  select acc.book, acc.code, 
 (adr.town||', '||adr.street||' '||
   (coalesce('буд.'||(acc.addr).house||'','')||
		coalesce('/'||(acc.addr).slash||' ','')||
			coalesce(' корп.'||(acc.addr).korp||'','')||
				coalesce(', кв. '||(acc.addr).flat,'')||
					coalesce('/'||(acc.addr).f_slash||' ',''))::varchar
)::varchar as addr,
(c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon,
 ('0'||substring(acc.code FROM '[0-9]+'))::int as int_code,
 ('0'||substring(acc.book FROM '[0-9]+'))::int as int_book,
 sw.sum_warning, adr.id_town,
 to_char(sw.dt_action, 'DD.MM.YYYY') as dt_action,
 to_char(sw.dt_create, 'DD.MM.YYYY') as dt_create,
 to_char(coalesce(sw.dt_warning,(sw.dt_action+'1 month'::interval)::date), 'DD.MM.YYYY') as dt_warning,
 (select sum(p.value) from acm_pay_tbl as p 
        	where p.id_pref = 10 and (greatest(p.pay_date,p.reg_date) >= least(sw.dt_action, sw.dt_create) or p.dt > sw.dt ) and p.value<> 0 and  p.id_paccnt = sw.id_paccnt) as sum_pay,
 to_char((select max(p.pay_date) from acm_pay_tbl as p 
        	where p.id_pref = 10 and (greatest(p.pay_date,p.reg_date) >= least(sw.dt_action, sw.dt_create) or p.dt > sw.dt ) and p.value<> 0 and  p.id_paccnt = sw.id_paccnt), 'DD.MM.YYYY') as date_pay,
 (
   select greatest(-sum(value),0) as bill_corr from acm_bill_tbl as bb where 
   bb.mmgg >= sw.mmgg and 
   (bb.mmgg_bill < sw.mmgg - '1 month'::interval  or  (bb.idk_doc = 220 and bb.reg_num <>'' and bb.value <0 and coalesce(bb.demand,0) = 0))
   and bb.id_pref = 10 and bb.idk_doc=220
   and bb.dt > sw.dt
   and bb.id_paccnt = sw.id_paccnt
 ) as sum_recalc
   
         from 
         (   select csw.id_paccnt, csw.dt_action, csw.action, csw.sum_warning, csw.mmgg, csw.dt,
             csw.dt_create, csw.dt_warning
            from clm_switching_tbl as csw 
            join (select id_paccnt, max(dt_action) as maxdt from clm_switching_tbl where action in (1,2) group by id_paccnt) as csdt 
            on (csw.id_paccnt = csdt.id_paccnt and csw.dt_action = csdt.maxdt) 
            join cli_switch_action_tbl as sn on (sn.id = csw.action)
            left join clm_switching_tbl as cswc on (csw.id_paccnt = cswc.id_paccnt and cswc.dt_action >= csw.dt_action and ((cswc.action =3 and csw.action =1) or (cswc.action =4  and csw.action =2) ) ) 
            where csw.action not in (3,4) and cswc.id_paccnt is null 
            and csw.action = 2      
         ) as sw    
         join clm_paccnt_tbl as acc on (acc.id = sw.id_paccnt)
         join clm_abon_tbl as c on (c.id = acc.id_abon) 
         join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class)
         join prs_runner_paccnt as rp on (rp.id_paccnt = acc.id)
         join prs_runner_sectors as rs on (rs.id = rp.id_sector)
 where sw.action = 2 $where
) as ss     
order by int_book, int_code;";


   // throw new Exception(json_encode($SQL));
    $sum_delta=0;
    $sum_warning=0;
    $sum_pay=0;
    $sum_recalc = 0;
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        $i=1;
        while ($row = pg_fetch_array($result)) {

           
            $abon = htmlspecialchars($row['abon']);
            $addr = htmlspecialchars($row['addr']);
            $book=$row['book'];
            $code=$row['code'];
            
            $sum_warning_txt = number_format_ua ($row['sum_warning'], 2);
            $sum_pay_txt = number_format_ua ($row['sum_pay'], 2);
            $sum_recalc_txt = number_format_ua ($row['sum_recalc'], 2);
            $sum_delta_txt = number_format_ua ($row['sum_delta'], 2);
            
            $sum_delta+=$row['sum_delta'];
            $sum_warning+=$row['sum_warning'];
            $sum_pay+=$row['sum_pay'];
            $sum_recalc+=$row['sum_recalc'];
            
            echo_html( "
            <tr >
            <td class='c_i'>{$i}</td>                    
            <td class='c_t'>{$book}</td>
            <td class='c_t'>{$code}</td>
            <td>{$abon}</td>
            <td>{$addr}</td>
            <td class='c_t'>{$row['dt_create']}</td>
            <td class='c_t'>{$row['dt_action']}</td>
            <td class='c_n'>{$sum_warning_txt}</td>
            <td class='c_t'>{$row['dt_warning']}</td>
            <td class='c_n'>{$sum_pay_txt}</td>
            <td class='c_t'>{$row['date_pay']}</td>
            <td class='c_n'>{$sum_recalc_txt}</td>
            <td class='c_n'>{$sum_delta_txt}</td>
            </tr>  ");        
            $i++;
        }
        
    }

    //$sum_warning_txt = number_format_ua ($sum_saldo, 2);
    $sum_warning_txt = number_format_ua ($sum_warning, 2);
    $sum_pay_txt = number_format_ua ($sum_pay, 2);
    $sum_recalc_txt = number_format_ua ($sum_recalc, 2);
    $sum_delta_txt = number_format_ua ($sum_delta, 2);

    
    eval("\$footer_text_eval = \"$footer_text\";");
    echo $footer_text_eval;

    //return;
    
}
//----------------------------------------------------------------------------
if ($oper=='off_pay')
{
    $p_mmgg = sql_field_val('dt_b', 'mmgg');
    $mmgg1_str = trim($_POST['period_str']);
    $p_dt = sql_field_val('dt_rep', 'date');
    $p_dt_txt = trim($_POST['dt_rep']);
    //$p_value = sql_field_val('sum_value', 'numeric');
    $p_id_sector = sql_field_val('id_sector', 'int');
    $p_id_region = sql_field_val('id_region', 'int');
    $p_id_town = sql_field_val('addr_town', 'int');
    $p_book = sql_field_val('book', 'string');
    
    $params_caption='';
    $where ='';
    if ($p_id_sector!='null')
    {
        $where.= " and rp.id_sector = $p_id_sector ";
        $params_caption.= ' Дiльниця: '. trim($_POST['sector']);
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
    
    if ((isset($_POST['addr_town_name']))&&($_POST['addr_town_name']!=''))
        $params_caption .= 'населений пункт: '. trim($_POST['addr_town_name']);
    
    if ($p_id_town!='null')
    {
        $where.= "and adr.id_town = $p_id_town ";
    }    
    if ($p_book!='null')
    {
        $where.= " and acc.book = $p_book ";
        $params_caption .= " книга : $p_book ";
    }
   
    
   
    eval("\$header_text = \"$header_text\";");
    
    echo $header_text;
    
    $SQL = "select warning_off_fun();";
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    
    
    
    $SQL = " select *, 
    CASE WHEN coalesce(sum_warning,0) <>0 THEN 
      coalesce(sum_warning,0)-coalesce(sum_pay,0)-coalesce(sum_recalc,0) 
      END as sum_delta 
    from (
    
  select acc.book, acc.code, acc.note,
 (adr.town||', '||adr.street||' '||
   (coalesce('буд.'||(acc.addr).house||'','')||
		coalesce('/'||(acc.addr).slash||' ','')||
			coalesce(' корп.'||(acc.addr).korp||'','')||
				coalesce(', кв. '||(acc.addr).flat,'')||
					coalesce('/'||(acc.addr).f_slash||' ',''))::varchar
)::varchar as addr,
(c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon,
 ('0'||substring(acc.code FROM '[0-9]+'))::int as int_code,
 ('0'||substring(acc.book FROM '[0-9]+'))::int as int_book,
 sw.sum_warning, adr.id_town, coalesce(s.e_val,0) as e_val,
 to_char(sw.dt_action, 'DD.MM.YYYY') as dt_action,
 (select sum(p.value) from acm_pay_tbl as p 
        	where p.id_pref = 10 and p.reg_date >= sw.dt_action and p.value<> 0 and  p.id_paccnt = sw.id_paccnt) as sum_pay,
 to_char((select max(p.reg_date) from acm_pay_tbl as p 
        	where p.id_pref = 10 and p.reg_date >= sw.dt_action and p.value<> 0 and  p.id_paccnt = sw.id_paccnt), 'DD.MM.YYYY') as date_pay,
 (
   select -sum(value) as bill_corr from acm_bill_tbl as bb where 
   bb.mmgg >= sw.mmgg and 
   bb.mmgg_bill <= sw.mmgg
   and bb.id_pref = 10 and bb.idk_doc=220
   and bb.dt > sw.dt
   and bb.id_paccnt = sw.id_paccnt
 ) as sum_recalc
   
         from 
         (   select csw.id_paccnt, csw.dt_action, csw.action, csw.sum_warning, csw.mmgg, csw.dt,
             csw.dt_create, csw.dt_warning
            from clm_switching_tbl as csw 
            join (select id_paccnt, max(dt_action) as maxdt from clm_switching_tbl where action in (1,2) group by id_paccnt) as csdt 
            on (csw.id_paccnt = csdt.id_paccnt and csw.dt_action = csdt.maxdt) 
            join cli_switch_action_tbl as sn on (sn.id = csw.action)
            left join clm_switching_tbl as cswc on (csw.id_paccnt = cswc.id_paccnt and cswc.dt_action >= csw.dt_action and ((cswc.action =3 and csw.action =1) or (cswc.action =4  and csw.action =2) ) ) 
            where csw.action not in (3,4) and cswc.id_paccnt is null 
            and csw.action = 1      
         ) as sw    
         join clm_paccnt_tbl as acc on (acc.id = sw.id_paccnt)
         join clm_abon_tbl as c on (c.id = acc.id_abon) 
         join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class)
         join prs_runner_paccnt as rp on (rp.id_paccnt = acc.id)
         join prs_runner_sectors as rs on (rs.id = rp.id_sector)
         left join acm_saldo_tbl as s on (s.id_paccnt = acc.id and s.id_pref = 10 and s.mmgg = $p_mmgg )
 where sw.action = 1 and acc.archive =0 $where
) as ss     
order by int_book, int_code;";


   // throw new Exception(json_encode($SQL));
    $sum_delta=0;
    $sum_warning=0;
    $sum_pay=0;
    $sum_recalc = 0;
    $sum_debet = 0;
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        $i=1;
        while ($row = pg_fetch_array($result)) {

           
            $abon = htmlspecialchars($row['abon']);
            $addr = htmlspecialchars($row['addr']);
            $note = htmlspecialchars($row['note']);
            $book=$row['book'];
            $code=$row['code'];
            
            $sum_warning_txt = number_format_ua ($row['sum_warning'], 2);
            $sum_pay_txt = number_format_ua ($row['sum_pay'], 2);
            $sum_recalc_txt = number_format_ua ($row['sum_recalc'], 2);
            $sum_delta_txt = number_format_ua ($row['sum_delta'], 2);
            $debet_e_txt = number_format_ua ($row['e_val'], 2);
            
            $sum_delta+=$row['sum_delta'];
            $sum_warning+=$row['sum_warning'];
            $sum_pay+=$row['sum_pay'];
            $sum_recalc+=$row['sum_recalc'];
            $sum_debet+=$row['e_val'];
            
            echo_html( "
            <tr >
            <td class='c_i'>{$i}</td>                    
            <td class='c_t'>{$book}</td>
            <td class='c_t'>{$code}</td>
            <td>{$abon}</td>
            <td>{$addr}</td>
            <td class='c_t'>{$row['dt_action']}</td>
            <td class='c_n'>{$sum_warning_txt}</td>
            <td class='c_n'>{$sum_pay_txt}</td>
            <td class='c_t'>{$row['date_pay']}</td>
            <td class='c_n'>{$sum_recalc_txt}</td>
            <td class='c_n'>{$sum_delta_txt}</td>
            <td class='c_n'>{$debet_e_txt}</td>
            <td class='c_t'>{$note}</td>
            </tr>  ");        
            $i++;
        }
        
    }

    //$sum_warning_txt = number_format_ua ($sum_saldo, 2);
    $sum_warning_txt = number_format_ua ($sum_warning, 2);
    $sum_pay_txt = number_format_ua ($sum_pay, 2);
    $sum_recalc_txt = number_format_ua ($sum_recalc, 2);
    $sum_delta_txt = number_format_ua ($sum_delta, 2);
    $debet_e_txt = number_format_ua ($sum_debet, 2);
    
    eval("\$footer_text_eval = \"$footer_text\";");
    echo $footer_text_eval;

    //return;
    
}
//----------------------------------------------------------------------------

if ($oper=='9nkre')
{
    $p_mmgg1 = sql_field_val('dt_b', 'mmgg');
    $p_mmgg2 = sql_field_val('dt_e', 'mmgg');
    
    $mmgg1_str = ukr_date($_POST['dt_b'],0,1);
    $mmgg2_str = ukr_date($_POST['dt_e'],0,1);

    $p_id_region = sql_field_val('id_region', 'int');
    
    $period = "$mmgg1_str - $mmgg2_str";
    $params_caption = '';    
    $where ='';
    
    if ($p_id_region!='null')
    {
        $where= " and rs.id_region =  $p_id_region ";
        
        //if ($p_id_region==1) $params_caption=" Деснянський район";
        //if ($p_id_region==2) $params_caption=" Новозаводський район";
        $SQL_H="SELECT name from cli_region_tbl where id = $p_id_region ;";
    
        $result = pg_query($Link, $SQL_H) or die("SQL Error: " . pg_last_error($Link) . $SQL_H);
        $row = pg_fetch_array($result);
        
        $params_caption.= $row['name'];        
        
    }

    $sumdemand_all =0;
    $sumdemand_lgt =0;
    
    $i=1;
    $name = '';
    eval("\$header_text = \"$header_text\";");
    echo $header_text;
    

    $SQL = " select mmgg, to_char(mmgg, 'DD.MM.YYYY') as mmgg_str,
     sum(calc_kvt) as demand_all, sum(calc_kvt_lgt) as demand_lgt
from (
select p.*,
z.sum_all/100 as tar_rep,
(p.pay/(z.sum_all/100))::int as calc_kvt,
CASE WHEN coalesce(b.lgt,0) <> 0 THEN (p.pay/(z.sum_all/100))::int END as calc_kvt_lgt
from
(
select p.id_paccnt, p.mmgg, sum(p.value) as pay 
from acm_pay_tbl as p 
join prs_runner_paccnt as rp on (rp.id_paccnt = p.id_paccnt)
join prs_runner_sectors as rs on (rs.id = rp.id_sector)
where p.id_pref = 10
and p.mmgg >=$p_mmgg1 and p.mmgg <= $p_mmgg2
and p.idk_doc not in (110, 111, 193,194) 
and p.value <>0
$where       
group by p.id_paccnt, p.mmgg
) as p
join rep_zvit_tbl as z on (z.ident = 'bill_tar_avg2' and z.mmgg = p.mmgg and z.id_dep = $res_code )
left join (
 select b.id_paccnt, b.mmgg, sum(b.value_lgt) as lgt, sum(b.value) as sum_bill, sum(b.demand) as demand_bill   
 from acm_bill_tbl as b
 join prs_runner_paccnt as rp on (rp.id_paccnt = b.id_paccnt)
 join prs_runner_sectors as rs on (rs.id = rp.id_sector)
 where b.id_pref = 10 and b.idk_doc = 200
 and b.mmgg >=$p_mmgg1 and b.mmgg <= $p_mmgg2 and b.mmgg_bill = mmgg
 and coalesce(b.demand,0) <>0
 and coalesce(b.value_lgt,0)<>0
 $where   
 group by b.id_paccnt, b.mmgg
 order by b.id_paccnt, b.mmgg
) as b on (b.id_paccnt = p.id_paccnt and b.mmgg = p.mmgg )
) as ss
group by mmgg
order by mmgg; ";
 
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        $i=0;
        while ($row = pg_fetch_array($result)) {

            $mmgg_str = ukr_date($row['mmgg_str'],0,1);
            
            $demand_all =$row['demand_all'];
            $demand_lgt =$row['demand_lgt'];
            
            $sumdemand_all+=$demand_all;
            $sumdemand_lgt+=$demand_lgt;
            
            $demand_all_txt = number_format_ua ($demand_all,0);
            $demand_lgt_txt = number_format_ua ($demand_lgt,0);            
            
            echo "
            <tr >
            <td>$mmgg_str</td>
            <TD class='c_n'>{$demand_all_txt}</td>
            <TD class='c_n'>{$demand_lgt_txt}</td>
            </tr>  ";        
            $i++;
            
        }
        
    }
    
    $demand_all_txt = number_format_ua ($sumdemand_all,0);
    $demand_lgt_txt = number_format_ua ($sumdemand_lgt,0);            

    eval("\$footer_text_eval = \"$footer_text\";");
    echo $footer_text_eval;
   
}
//---------------------------------------------------------------------
if ($oper=='subs_dt_list')
{
    $p_mmgg = sql_field_val('dt_b', 'mmgg');
    $mmgg_str = ukr_date($_POST['dt_b'],0,1);

    $period = "за $mmgg_str ";
    
    $p_id_town = sql_field_val('addr_town', 'int');
    $p_id_region = sql_field_val('id_region', 'int');

    $where="";
    $params_caption = '';
    
    if ((isset($_POST['addr_town_name']))&&($_POST['addr_town_name']!=''))
        $params_caption.= 'Населений пункт: '. trim($_POST['addr_town_name']);
   
    if ($p_id_town!='null')
    {
        $where.= " and adr.id_town = $p_id_town ";
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
    $i=1;
    
    $np=0;
    $print_flag = 1;
    
    
    eval("\$header_text = \"$header_text\";");
    echo_html( $header_text);
    
    $SQL = "  select acc.book, acc.code, acc.n_subs,
 adr.town||' '||(adr.street||' '||
   (coalesce('буд.'||(acc.addr).house||'','')||
		coalesce('/'||(acc.addr).slash||' ','')||
			coalesce(' корп.'||(acc.addr).korp||'','')||
				coalesce(', кв. '||(acc.addr).flat,'')||
					coalesce('/'||(acc.addr).f_slash||' ',''))::varchar
)::varchar as addr,
(c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon,
 ('0'||substring(acc.code FROM '[0-9]+'))::int as int_code,
 ('0'||substring(acc.book FROM '[0-9]+'))::int as int_book,
 (s.bill_value - s.subs_value - s.pay_value - s.pay_avans + bill_corr + s.kt_b) as delta,
 (s.bill_value - s.subs_value + bill_corr  ) as bill_val,  
 (s.pay_value + s.pay_avans - s.kt_b) as pay_val,
 s.*
from 
(
select  s.id_paccnt, CASE WHEN s.b_val <0 THEN  s.b_val ELSE 0 END as kt_b, 
CASE WHEN s.b_val >0 THEN  s.b_val ELSE 0 END as dt_b, pp.value as pay2_value,
CASE WHEN s.b_val >0 and pp.value > s.b_val THEN  pp.value - s.b_val ELSE 0 END as pay_avans,
s.e_val, 
b.value as bill_value, ps.value as subs_value, coalesce(p.value,0) as pay_value , coalesce(bill_corr,0) as bill_corr
 from  acm_saldo_tbl as s 
join 
(
 select b.id_paccnt, sum(b.value) as value
 from acm_bill_tbl as b 
     where b.mmgg = $p_mmgg::date and b.id_pref = 10 and b.mmgg = b.mmgg_bill and b.idk_doc = 200
     group by b.id_paccnt
     order by id_paccnt
 ) as b on (b.id_paccnt = s.id_paccnt)
 join (
   	select p.id_paccnt, sum(p.value) as value
       	from acm_pay_tbl as p 
       	where p.mmgg = $p_mmgg::date and p.id_pref = 10 and p.idk_doc =110
       	group by p.id_paccnt
        having sum(p.value) >0
	order by id_paccnt
       ) as ps on (ps.id_paccnt = s.id_paccnt)
 left join (
   	select p.id_paccnt, sum(p.value) as value
       	from acm_pay_tbl as p 
       	where p.mmgg = $p_mmgg::date and p.id_pref = 10 and p.idk_doc not in (110, 111, 193,194) 
       	group by p.id_paccnt
        having sum(p.value) >0
	order by id_paccnt
       ) as pp on (pp.id_paccnt = s.id_paccnt)
 left join (
      	select p.id_paccnt, sum(p.value) as value
      	from acm_pay_tbl as p 
       	where p.mmgg >= $p_mmgg::date+'1 month'::interval and p.id_pref = 10 and p.idk_doc not in (110, 111, 193,194) 
       	group by p.id_paccnt
	order by id_paccnt
  ) as p on (p.id_paccnt = s.id_paccnt)
 left join
   (
    select id_paccnt, sum(value) as bill_corr 
    from acm_bill_tbl where 
    mmgg >= $p_mmgg::date+'1 month'::interval and 
    mmgg_bill  = $p_mmgg
    and id_pref = 10 
    group by id_paccnt 
    having sum(value) < 0 
    order by id_paccnt
   ) as b1 on (b1.id_paccnt = s.id_paccnt )
where s.mmgg = $p_mmgg and   s.e_val > 0 and s.id_pref = 10  
) as s 
 join clm_paccnt_tbl as acc on (acc.id = s.id_paccnt)
 join clm_abon_tbl as c on (c.id = acc.id_abon) 
 join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class)
where s.bill_value - s.subs_value - s.pay_value - s.pay_avans + s.bill_corr + s.kt_b >0
and s.e_val - s.pay_value + s.bill_corr > 0  
$where
order by int_book, int_code;";

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
            $addr = htmlspecialchars($row['addr']);
            
            $bill_val_txt = number_format_ua ($row['bill_val'],2);
            $pay_val_txt = number_format_ua ($row['pay_val'],2);
            $delta_txt = number_format_ua ($row['delta'],2);
            
            $bookcode=$row['book'].'/'.$row['code'];
 
            echo_html( "
            <tr >
            <td>{$i}</td>            
            <td>$abon</td>
            <td class='c_t'>$bookcode</td>
            <td class='c_t'>{$row['n_subs']}</td>
            <td>$addr</td>
            <TD class='c_n'>{$bill_val_txt}</td>
            <TD class='c_n'>{$pay_val_txt}</td>
            <TD class='c_n'>{$delta_txt}</td>
            </tr>  ",$print_flag); 

            $i++;
        }
    }

    eval("\$footer_text_eval = \"$footer_text\";");

    echo_html( $footer_text_eval,$print_flag);
   
    if ($print_flag==0) echo "</tbody> </table> ";
   
}
//---------------------------------------------------------------------
if ($oper=='subs_recalc_lgt_minus')
{
    $p_mmgg = sql_field_val('dt_b', 'mmgg');
    $mmgg_str = ukr_date($_POST['dt_b'],0,1);

    $period = "за $mmgg_str ";
    
    $p_id_town = sql_field_val('addr_town', 'int');
    $p_id_region = sql_field_val('id_region', 'int');
    
    $where="";
    $params_caption = '';
    
    if ((isset($_POST['addr_town_name']))&&($_POST['addr_town_name']!=''))
        $params_caption.= 'Населений пункт: '. trim($_POST['addr_town_name']);
   
    if ($p_id_town!='null')
    {
        $where.= " and adr.id_town = $p_id_town ";
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
    
    $SQL = "  select c.id, c.book,c.code, lg.fio_lgt,lg.family_cnt,lg.s_doc, lg.n_doc,lg.ident_cod_l,
    to_char(lg.dt_start, 'DD.MM.YYYY') as dt_start,
    to_char(lg.dt_end, 'DD.MM.YYYY') as dt_end,
    adr.town||' '||(adr.street||' '|| ( coalesce('буд.'||(c.addr).house||'','')||
		coalesce('/'||(c.addr).slash||' ','')||
			coalesce(' корп.'||(c.addr).korp||'','')||
				coalesce(', кв. '||(c.addr).flat,'')||
					coalesce('/'||(c.addr).f_slash||' ',''))::varchar
     )::varchar as addr,    
     g.name as lgt_name, g.ident,g.alt_code,
     lg.id_grp_lgt, c.id_gtar,
    regexp_replace(regexp_replace(c.code, '-.*?$', '') , '[^0-9]', '','g')::int as int_code,
    regexp_replace(regexp_replace(c.book, '-.*?$', '') , '[^0-9]', '','g')::int as int_book,
     ps.value as subs_value, pss.value as subs_recalc
     from 
    clm_paccnt_tbl as c
    join lgm_abon_h as lg on (lg.id_paccnt = c.id) 
    join (select id, max(dt_b) as dt_b from lgm_abon_h  where
            ((dt_b < ($p_mmgg::date+'1 month'::interval) 
             and ( dt_e is null and (dt_end is null or dt_end >= $p_mmgg )  )
             )
            or 
            tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
        )   group by id order by id) as lg2 
    on (lg.id = lg2.id and lg.dt_b = lg2.dt_b)
    join 
    (select b.id_paccnt, sum(b.value_lgt) as sum_lgt, sum(b.value) as sum_bill
     from acm_bill_tbl as b 
     where b.mmgg = $p_mmgg and b.mmgg_bill = $p_mmgg and b.id_pref = 10 and b.idk_doc = 200 and b.value>0
     group by b.id_paccnt 
    ) as bs on (bs.id_paccnt = c.id ) 
     join (
   	select p.id_paccnt, sum(p.value) as value
       	from acm_pay_tbl as p 
       	where p.mmgg = $p_mmgg::date and p.id_pref = 10 and p.idk_doc =110
        and p.value >0
       	group by p.id_paccnt
       ) as ps on (ps.id_paccnt = c.id)
     join (
   	select p.id_paccnt, sum(p.value) as value
       	from acm_pay_tbl as p 
       	where p.mmgg = ($p_mmgg::date+'1 month'::interval)::date and p.id_pref = 10 and p.idk_doc in (110 ,111, 193,194)
        and p.value <0
       	group by p.id_paccnt
       ) as pss on (pss.id_paccnt = c.id)
    join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class)    
    join aqi_grptar_tbl as tar on (tar.id = c.id_gtar)
    join lgi_group_tbl as g on (lg.id_grp_lgt = g.id)
    where c.archive =0 and (lg.dt_end is null or lg.dt_end >= $p_mmgg::date )
    and coalesce(bs.sum_lgt,0) = 0 
    $where
    order by int_book, int_code;";

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
            
            $book=$row['book'];
            $code=$row['code'];
            
            $abon = htmlspecialchars($row['fio_lgt']);
            $lgt = htmlspecialchars($row['lgt_name']);
            $addr = htmlspecialchars($row['addr']);
            
            $subs_value_txt = number_format_ua ($row['subs_value'],2);
            $subs_recalc_txt = number_format_ua ($row['subs_recalc'],2);
            
            echo_html( "
            <tr >
            <td>{$i}</td>            
            <td class='c_t'>$book</td>
            <td>$code</td>
            <td>$addr</td>
            <td>$abon</td>
            <td>$lgt</td>
            <TD class='c_n'>{$subs_value_txt}</td>
            <TD class='c_n'>{$subs_recalc_txt}</td>
            </tr>  ",$print_flag); 

            $i++;
        }
    }

    eval("\$footer_text_eval = \"$footer_text\";");

    echo_html( $footer_text_eval,$print_flag);
   
    if ($print_flag==0) echo "</tbody> </table> ";
   
}
//---------------------------------------------------------------------
if ($oper=='subs_recalc_lgt_plas')
{
    $p_mmgg = sql_field_val('dt_b', 'mmgg');
    $mmgg_str = ukr_date($_POST['dt_b'],0,1);

    $period = "за $mmgg_str ";
    
    $p_id_town = sql_field_val('addr_town', 'int');
    $p_id_region = sql_field_val('id_region', 'int');

    $where="";
    $params_caption = '';
    
    if ((isset($_POST['addr_town_name']))&&($_POST['addr_town_name']!=''))
        $params_caption.= 'Населений пункт: '. trim($_POST['addr_town_name']);
   
    if ($p_id_town!='null')
    {
        $where.= " and adr.id_town = $p_id_town ";
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
    
    $SQL = "  
   select c.id, c.book,c.code, lg.fio_lgt,lg.family_cnt,lg.s_doc, lg.n_doc,lg.ident_cod_l,
    to_char(lg.dt_reg, 'DD.MM.YYYY') as dt_reg,
     g.name as lgt_name, g.ident,g.alt_code,
 adr.town||' '||(adr.street||' '||
   (coalesce('буд.'||(c.addr).house||'','')||
		coalesce('/'||(c.addr).slash||' ','')||
			coalesce(' корп.'||(c.addr).korp||'','')||
				coalesce(', кв. '||(c.addr).flat,'')||
					coalesce('/'||(c.addr).f_slash||' ',''))::varchar
)::varchar as addr,
    lg.id_grp_lgt, c.id_gtar,
    regexp_replace(regexp_replace(c.code, '-.*?$', '') , '[^0-9]', '','g')::int as int_code,
    regexp_replace(regexp_replace(c.book, '-.*?$', '') , '[^0-9]', '','g')::int as int_book,
    pss.value as subs_recalc, bs.sum_lgt
     from 
    clm_paccnt_tbl as c
    join lgm_abon_h as lg on (lg.id_paccnt = c.id) 
    join (select id, max(dt_b) as dt_b from lgm_abon_h  where
            ((dt_b < ($p_mmgg::date+'1 month'::interval) 
             and ( dt_e is null and (dt_end is null or dt_end >= $p_mmgg )  )
             )
            or 
            tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
        )   group by id order by id) as lg2 
    on (lg.id = lg2.id and lg.dt_b = lg2.dt_b)
    join 
    (select b.id_paccnt, sum(b.value_lgt) as sum_lgt, sum(b.value) as sum_bill
     from acm_bill_tbl as b 
     where b.mmgg = $p_mmgg and b.mmgg_bill =$p_mmgg and b.id_pref = 10 and b.idk_doc = 200 and b.value>0
     group by b.id_paccnt 
    ) as bs on (bs.id_paccnt = c.id ) 
     join (
   	select p.id_paccnt, sum(p.value) as value
       	from acm_pay_tbl as p 
       	where p.mmgg = ($p_mmgg::date+'1 month'::interval)::date and p.id_pref = 10 and p.idk_doc in (111, 193,194)
        and p.value >0
       	group by p.id_paccnt
       ) as pss on (pss.id_paccnt = c.id)
    join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class)    
    join aqi_grptar_tbl as tar on (tar.id = c.id_gtar)
    join lgi_group_tbl as g on (lg.id_grp_lgt = g.id)
    where c.archive =0 and (lg.dt_end is null or lg.dt_end >= $p_mmgg::date )
    and coalesce(bs.sum_lgt,0) <> 0 
    $where
    order by int_book, int_code;";

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
            
            $book=$row['book'];
            $code=$row['code'];
            
            $abon = htmlspecialchars($row['fio_lgt']);
            $lgt = htmlspecialchars($row['lgt_name']);
            $addr = htmlspecialchars($row['addr']);
            
            $sum_lgt_txt = number_format_ua ($row['sum_lgt'],2);
            $subs_recalc_txt = number_format_ua ($row['subs_recalc'],2);
            
            echo_html( "
            <tr >
            <td>{$i}</td>            
            <td class='c_t'>$book</td>
            <td>$code</td>
            <td>$addr</td>
            <td>$abon</td>
            <td>$lgt</td>
            <TD class='c_n'>{$sum_lgt_txt}</td>
            <TD class='c_n'>{$subs_recalc_txt}</td>
            </tr>  ",$print_flag); 

            $i++;
        }
    }

    eval("\$footer_text_eval = \"$footer_text\";");

    echo_html( $footer_text_eval,$print_flag);
   
    if ($print_flag==0) echo "</tbody> </table> ";
   
}
//---------------------------------------------------------------------

if ($oper=='lgt_manual_list')
{

    //list($year, $month, $day  ) = split('[/.-]', $p_mmgg);
    //$month_str = ukr_month((int)$month,0);
    //$period = "файл  $p_filename ";
    $p_mmgg = sql_field_val('dt_b', 'mmgg');    
    $period = trim($_POST['period_str']);
    //разбор и обработка строки параметров jqgrid
    $qWhere='';
    if (isset($_POST['grid_params'])) {
          $grid_params_json = $_POST['grid_params'];
          $json_str = stripslashes($grid_params_json); 
          $grid_params = json_decode($json_str,true);
          
          
          $fildsArray =DbGetFieldsArray($Link,'clm_lgt_manual_tbl');
          $fildsArray['book'] = array('f_name' => 'book', 'f_type' => 'integer');
          $fildsArray['code'] = array('f_name' => 'code', 'f_type' => 'integer');
          $fildsArray['addr'] = array('f_name' => 'addr', 'f_type' => 'character varying');
          $fildsArray['town'] = array('f_name' => 'town', 'f_type' => 'character varying');
          $fildsArray['abon'] = array('f_name' => 'abon', 'f_type' => 'character varying');
          $fildsArray['user_name'] = array('f_name' => 'user_name', 'f_type' => 'character varying');
          
          $qWhere= DbBuildWhere($grid_params,$fildsArray);

    }
    if ($qWhere!='') $qWhere=$qWhere.' and ';
    else $qWhere=' where ';

    $qWhere=$qWhere." mmgg = $p_mmgg::date ";
    
    
    $params_caption = '';

    $sum_lgt = 0;

    $i=1;
    
    eval("\$header_text = \"$header_text\";");
    echo_html ($header_text);
    
    $SQL = "select * from ( select n.*, acc.book, acc.code,  
 (adr.street||' '||
   (coalesce('буд.'||(acc.addr).house||'','')||
		coalesce('/'||(acc.addr).slash||' ','')||
			coalesce(' корп.'||(acc.addr).korp||'','')|| 
				coalesce(', кв. '||(acc.addr).flat,'')||
					coalesce('/'||(acc.addr).f_slash||' ',''))::varchar
)::varchar as addr, adr.town,
to_char(n.mmgg_bill, 'DD.MM.YYYY') as mmgg_bill_txt,    
    to_char(n.dt, 'DD.MM.YYYY HH24:MI') as dt_txt,    
CASE WHEN id_action = 1 THEN '+' WHEN id_action = 2 THEN '-' END as action,
(c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon,
('0'||substring(acc.code FROM '[0-9]+'))::int as int_code,
('0'||substring(acc.book FROM '[0-9]+'))::int as int_book,
u1.name as user_name 
from clm_lgt_manual_tbl as n
join clm_paccnt_tbl as acc on (acc.id = n.id_paccnt)
join clm_abon_tbl as c on (c.id = acc.id_abon) 
join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class) 
left join syi_user as u1 on (u1.id = n.id_person)
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
            $town = htmlspecialchars($row['town']);
            $name_user = htmlspecialchars($row['user_name']);
            
            echo_html( "
            <tr >
            <td>{$i}</td>            
            <td class='c_t'>{$row['book']}</td>
            <td class='c_t'>{$row['code']}</td>
            <td class='c_t'>$abon</td>
            <td class='c_t'>$town</td>
            <td class='c_t'>$addr</td>
            
            <td class='c_t'>{$row['mmgg_bill_txt']}</td>
            <td class='c_t'>{$row['action']}</td>
            
            <td class='c_t'>$name_user</td>
            <td class='c_t'>{$row['dt_txt']}</td>
            </tr>  ");        
            $i++;
            
        }
        
    }
    
    eval("\$footer_text_eval = \"$footer_text\";");
    echo_html( $footer_text_eval);

}

//----------------------------------------------------------------------------

if (($oper=='debet_years_abon')||($oper=='debet_years_month_abon'))
{
    $p_mmgg = sql_field_val('dt_b', 'mmgg');
    
    list($year, $month, $day  ) = split('[/.-]', $p_mmgg);
    $month_str = ukr_month((int)$month,0);
    $p_debet_month = sql_field_val('debet_month', 'int');
    if ($p_debet_month=='null') $p_debet_month =0;

    
    $p_id_tar = sql_field_val('id_gtar', 'int');        
    $period = "за $month_str $year р.";

    $p_id_sector = sql_field_val('id_sector', 'int');
    $p_book = sql_field_val('book', 'string');
    $p_id_region = sql_field_val('id_region', 'int');

    $p_id_person = sql_field_val('id_person', 'int');

    $p_id_town = sql_field_val('addr_town', 'int');
    $p_year_rep = sql_field_val('year_rep', 'int');
    
    $where="";
    
    if ($p_id_tar!='null')
    {
        $where.= " and acc.id_gtar = $p_id_tar ";
    }
    
    $params_caption = '';
    if ((isset($_POST['gtar']))&&($_POST['gtar']!=''))
        $params_caption .= ' Тарифна група : '. trim($_POST['gtar']);
    
    
    if ((isset($_POST['sector']))&&($_POST['sector']!=''))
        $params_caption.= ' Дiльниця: '. trim($_POST['sector']);

    
    if ((isset($_POST['addr_town_name']))&&($_POST['addr_town_name']!=''))
        $params_caption.= 'Населений пункт: '. trim($_POST['addr_town_name']);
    

    if ($p_book!='null') 
    {
        $where.= " and acc.book = $p_book ";
        $params_caption .= " книга : $p_book ";
    }

   if ($p_id_sector!='null')
   {
       $where.= "and rs.id =  $p_id_sector  ";

   }

   if ($p_id_region!='null')
   {
        
        $where.= "and  rs.id_region =  $p_id_region  ";

        $SQL_H="SELECT name from cli_region_tbl where id = $p_id_region ;";
    
        $result = pg_query($Link, $SQL_H) or die("SQL Error: " . pg_last_error($Link) . $SQL_H);
        $row = pg_fetch_array($result);
        
        $params_caption.= $row['name'];        
   }   
  
    if ($p_id_town!='null')
    {
     //   $where.= " and adr.id_town = $p_id_town ";
        if ($p_id_town==40854)
          $where.= " and ( adr.id_parent = $p_id_town or adr.id_parent = 40016 )";
        else
          $where.= " and ( adr.id_town = $p_id_town or adr.id_parent = $p_id_town )";
    }

    $sum_e_val=0;
    $sum_d2009=0;
    $sum_d2010=0;
    $sum_d2011=0;
    $sum_d2012=0;
    $sum_d2013=0;
    $sum_d2014=0;
    $sum_d2015=0;
    $sum_d2016=0;
    $sum_d2017=0;
    
    $sum_deb01m=0;
    $sum_deb02m=0;
    $sum_deb03m=0;
    $sum_deb04m=0;
    $sum_deb05m=0;
    $sum_deb06m=0;
    $sum_deb07m=0;
    $sum_deb08m=0;
    $sum_deb09m=0;
    $sum_deb10m=0;
    $sum_deb11m=0;
    $sum_deb12m=0;
    
    $i=1;
    
    $np=0;
    $print_flag = 1;
    
    eval("\$header_text = \"$header_text\";");
    echo_html( $header_text);
    
    $town_hidden=CheckTownInAddrHidden($Link);
    
    
   $where_old_years = ''; 
   
   if ($p_year_rep!='null')
   {   
       if ($p_year_rep <= 2009) $p_year_rep = 2009;

       $where.= "and d{$p_year_rep} > 0 ";           
       
       $params_caption .= " що мають заборгованість за $p_year_rep рік ";
   }  

   if ($p_debet_month!=0)
   {   
       if ($p_year_rep!='null')
       {
           $where.= " and ( ";           
       }
       else
       {
       
         if ($year=="'2016")
           $where.= "and ( d2009 > 0 or d2010 > 0 or d2011 > 0 or d2012 > 0 or d2013 > 0 or d2014 > 0 or d2015 > 0 ";

         if ($year=="'2017") 
           $where.= "and ( d2009 > 0 or d2010 > 0 or d2011 > 0 or d2012 > 0 or d2013 > 0 or d2014 > 0 or d2015 > 0 or d2016 > 0 ";
       }
       for($c=1; $c<=(int)$month-$p_debet_month; $c++)
       {
           $month_c = sprintf('%1$02d', $c);
           if (($p_year_rep=='null')||($c>1)) $where.= " or ";
           $where.= " deb{$month_c}m > 0 ";  
       }
       
       $where.= " )  ";  
       $params_caption .= " що мають заборгованість більше  $p_debet_month місяців ";
       
       //echo $where;
   }  
   
   
   $table_name = 'seb_saldo_tmp';

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
            
          $SQL = "select crt_ttbl();";
          $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
            
          $SQL = "select rep_month_saldo_fun($p_mmgg,0);";
          $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
        }

    }
        
    
    $SQL = " select acc.book, acc.code , 
        (c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon,
        coalesce(c.home_phone,'')||coalesce(','||c.mob_phone,'') as phone,
        adr.town, adr.street||' '||address_print(acc.addr) as addr,
        d2009,d2010 ,d2011,d2012,d2013,d2014,d2015,d2016,d2017, sd.e_val, 
        deb01m,deb02m,deb03m, deb04m, deb05m,deb06m, deb07m, deb08m, deb09m, deb10m, deb11m, deb12m,
        (CASE WHEN sw.action = 1 then 'Відкл. ' WHEN sw.action = 2 then 'Попер. ' END || to_char(sw.dt_action, 'DD.MM.YYYY'))::varchar as action,
        zzz.indic_str||' на '||to_char(zzz.dat_ind, 'DD.MM.YYYY')::varchar as last_ind, zzz.num_eqp,
        to_char((select max(p.reg_date) from acm_pay_tbl as p 
        	where p.id_pref = 10  and p.value<> 0 and p.id_paccnt = s1.id_paccnt), 'DD.MM.YYYY') as date_pay,
        ('0'||substring(acc.code FROM '[0-9]+'))::int as int_code,
        ('0'||substring(acc.book FROM '[0-9]+'))::int as int_book,
        p.represent_name as runner
  from 
(
 select s.id_paccnt,
 sum(CASE WHEN hmmgg <= '2009-01-01' THEN e_dtval END) as d2009,
 sum(CASE WHEN hmmgg  = '2010-01-01' THEN e_dtval END) as d2010,
 sum(CASE WHEN hmmgg  = '2011-01-01' THEN e_dtval END) as d2011,
 sum(CASE WHEN hmmgg  = '2012-01-01' THEN e_dtval END) as d2012,
 sum(CASE WHEN hmmgg  = '2013-01-01' THEN e_dtval END) as d2013,
 sum(CASE WHEN hmmgg  = '2014-01-01' THEN e_dtval END) as d2014,
 sum(CASE WHEN hmmgg  = '2015-01-01' THEN e_dtval END) as d2015,
 sum(CASE WHEN hmmgg  = '2016-01-01' THEN e_dtval END) as d2016,
 sum(CASE WHEN hmmgg  = '2017-01-01' THEN e_dtval END) as d2017
 from $table_name as s  
  where s.mmgg = $p_mmgg::date and s.id_pref = 10 
  and s.e_dtval >0 
 group by s.id_paccnt  
) as s1
join acm_saldo_tbl as sd on (sd.id_paccnt = s1.id_paccnt and sd.id_pref = 10 and sd.mmgg = $p_mmgg::date )
join clm_paccnt_tbl as acc on (acc.id = s1.id_paccnt)
join clm_abon_tbl as c on (c.id = acc.id_abon) 
join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class)
join prs_runner_paccnt as rp on (rp.id_paccnt = acc.id)
join prs_runner_sectors as rs on (rs.id = rp.id_sector)
left join prs_persons as p on (p.id = rs.id_runner)    
left join rep_month_dt_tbl as m on (m.id_paccnt = s1.id_paccnt and m.mmgg = $p_mmgg::date )     
left join (
 select csw.id_paccnt, csw.dt_action, csw.action
	from clm_switching_tbl as csw 
	join (select id_paccnt, max(dt_action) as maxdt from clm_switching_tbl where action in (1,2) group by id_paccnt) as csdt 
	on (csw.id_paccnt = csdt.id_paccnt and csw.dt_action = csdt.maxdt) 
	left join clm_switching_tbl as cswc on (csw.id_paccnt = cswc.id_paccnt and cswc.dt_action >= csw.dt_action and ((cswc.action =3 and csw.action =1) or (cswc.action =4  and csw.action =2) ) ) 
	where csw.action in (1,2) and cswc.id_paccnt is null 
) as sw on (sw.id_paccnt = s1.id_paccnt)
left join ( select id_paccnt, dat_ind, max(num_eqp) as num_eqp,  trim(sum(indic||','),',')::varchar as indic_str from 
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
    ) as zzz on (zzz.id_paccnt = acc.id)    
where sd.e_val >0 
$where    
order by int_book, acc.book, int_code, acc.code  ;";

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
            $runner = htmlspecialchars($row['runner']);
            
            if ($town_hidden=='true')
                $addr = htmlspecialchars($row['addr']);
            else
                $addr = htmlspecialchars($row['town'].' '.$row['addr']);
                
            $book=$row['book'];
            $code=$row['code'];
            
            $e_val_txt = number_format_ua ($row['e_val'], 2);
            $d2009_txt = number_format_ua ($row['d2009'], 2);
            $d2010_txt = number_format_ua ($row['d2010'], 2);
            $d2011_txt = number_format_ua ($row['d2011'], 2);
            $d2012_txt = number_format_ua ($row['d2012'], 2);
            $d2013_txt = number_format_ua ($row['d2013'], 2);
            $d2014_txt = number_format_ua ($row['d2014'], 2);
            $d2015_txt = number_format_ua ($row['d2015'], 2);
            $d2016_txt = number_format_ua ($row['d2016'], 2);
            $d2017_txt = number_format_ua ($row['d2017'], 2);
            
            
            $deb01m_txt = number_format_ua ($row['deb01m'], 2);
            $deb02m_txt = number_format_ua ($row['deb02m'], 2);
            $deb03m_txt = number_format_ua ($row['deb03m'], 2);
            $deb04m_txt = number_format_ua ($row['deb04m'], 2);
            $deb05m_txt = number_format_ua ($row['deb05m'], 2);
            $deb06m_txt = number_format_ua ($row['deb06m'], 2);
            $deb07m_txt = number_format_ua ($row['deb07m'], 2);
            $deb08m_txt = number_format_ua ($row['deb08m'], 2);
            $deb09m_txt = number_format_ua ($row['deb09m'], 2);
            $deb10m_txt = number_format_ua ($row['deb10m'], 2);
            $deb11m_txt = number_format_ua ($row['deb11m'], 2);
            $deb12m_txt = number_format_ua ($row['deb12m'], 2);
            

            if ($p_sum_only!=1)
            {
           
              echo_html( "
              <tr >
              <td>{$i}</td>            
              <td class='c_t'>$addr</td>
              <td class='c_t'>$abon</td>
              <td class='c_t'>$book</td>
              <td>$code</td>
           
              <td class='c_n'>{$e_val_txt}</td>
              <td class='c_n'>{$d2009_txt}</td>
              <td class='c_n'>{$d2010_txt}</td>
              <td class='c_n'>{$d2011_txt}</td>
              <td class='c_n'>{$d2012_txt}</td>
              <td class='c_n'>{$d2013_txt}</td>
              <td class='c_n'>{$d2014_txt}</td> 
              <td class='c_n'>{$d2015_txt}</td>
              <td class='c_n'>{$d2016_txt}</td>
              <td class='c_n'>{$d2017_txt}</td>
              ",$print_flag); 
              
              if ($oper=='debet_years_month_abon')
              {
               echo_html( "
                <td class='c_n'>{$deb01m_txt}</td>
                <td class='c_n'>{$deb02m_txt}</td>
                <td class='c_n'>{$deb03m_txt}</td>
                <td class='c_n'>{$deb04m_txt}</td>
                <td class='c_n'>{$deb05m_txt}</td>
                <td class='c_n'>{$deb06m_txt}</td>
                <td class='c_n'>{$deb07m_txt}</td>
                <td class='c_n'>{$deb08m_txt}</td>
                <td class='c_n'>{$deb09m_txt}</td>
                <td class='c_n'>{$deb10m_txt}</td>
                <td class='c_n'>{$deb11m_txt}</td>
                <td class='c_n'>{$deb12m_txt}</td>
               ",$print_flag); 
              }
              
              echo_html( "
              <td class='c_t'>{$row['date_pay']}</td>
              <td class='c_t'>{$row['action']}</td>
              <td class='c_t'>{$runner}</td>
              <td class='c_t'>{$row['num_eqp']}</td>
              <td class='c_t'>{$row['last_ind']}</td>
              <td class='c_t'>{$row['phone']}</td>
              </tr>  ",$print_flag); 
            }
            $i++;
            
            $sum_e_val+=$row['e_val'];
            $sum_d2009+=$row['d2009'];
            $sum_d2010+=$row['d2010'];
            $sum_d2011+=$row['d2011'];
            $sum_d2012+=$row['d2012'];
            $sum_d2013+=$row['d2013'];
            $sum_d2014+=$row['d2014'];
            $sum_d2015+=$row['d2015'];
            $sum_d2016+=$row['d2016'];
            $sum_d2017+=$row['d2017'];
            
            $sum_deb01m+=$row['deb01m'];
            $sum_deb02m+=$row['deb02m'];
            $sum_deb03m+=$row['deb03m'];
            $sum_deb04m+=$row['deb04m'];
            $sum_deb05m+=$row['deb05m'];
            $sum_deb06m+=$row['deb06m'];
            $sum_deb07m+=$row['deb07m'];
            $sum_deb08m+=$row['deb08m'];
            $sum_deb09m+=$row['deb09m'];
            $sum_deb10m+=$row['deb10m'];
            $sum_deb11m+=$row['deb11m'];
            $sum_deb12m+=$row['deb12m'];
            
        }
    }

    $e_val_txt = number_format_ua ($sum_e_val, 2);
    $d2009_txt = number_format_ua ($sum_d2009, 2);
    $d2010_txt = number_format_ua ($sum_d2010, 2);
    $d2011_txt = number_format_ua ($sum_d2011, 2);
    $d2012_txt = number_format_ua ($sum_d2012, 2);
    $d2013_txt = number_format_ua ($sum_d2013, 2);
    $d2014_txt = number_format_ua ($sum_d2014, 2);
    $d2015_txt = number_format_ua ($sum_d2015, 2);
    $d2016_txt = number_format_ua ($sum_d2016, 2);
    $d2017_txt = number_format_ua ($sum_d2017, 2);
    
    $deb01m_txt = number_format_ua ($sum_deb01m, 2);
    $deb02m_txt = number_format_ua ($sum_deb02m, 2);
    $deb03m_txt = number_format_ua ($sum_deb03m, 2);
    $deb04m_txt = number_format_ua ($sum_deb04m, 2);
    $deb05m_txt = number_format_ua ($sum_deb05m, 2);
    $deb06m_txt = number_format_ua ($sum_deb06m, 2);
    $deb07m_txt = number_format_ua ($sum_deb07m, 2);
    $deb08m_txt = number_format_ua ($sum_deb08m, 2);
    $deb09m_txt = number_format_ua ($sum_deb09m, 2);
    $deb10m_txt = number_format_ua ($sum_deb10m, 2);
    $deb11m_txt = number_format_ua ($sum_deb11m, 2);
    $deb12m_txt = number_format_ua ($sum_deb12m, 2);
    
    eval("\$footer_text_eval = \"$footer_text\";");

    if ($p_sum_only==1) $print_flag=1;
    
    echo_html( $footer_text_eval,$print_flag);
   
    if ($print_flag==0) echo "</tbody> </table> ";
   
}
//------------------------------------------------------------------------------
//

if ($oper=='abon_indiconly')
{

    $p_mmgg1 = sql_field_val('dt_b', 'mmgg');
    $p_mmgg2 = sql_field_val('dt_e', 'mmgg');
    
    $mmgg1_str = ukr_date($_POST['dt_b'],0,1);
    $mmgg2_str = ukr_date($_POST['dt_e'],0,1);

    $period = "$mmgg1_str - $mmgg2_str";
    
    $p_book = sql_field_val('book', 'string');
    $p_id_town = sql_field_val('addr_town', 'int');
    $p_id_tar = sql_field_val('id_gtar', 'int');    
    $p_id_type_meter = sql_field_val('id_type_meter', 'int');    
    $p_id_operation = sql_field_val('id_operation', 'int');
    $p_id_region = sql_field_val('id_region', 'int');
    
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
    
    $operation='';
    if ((isset($_POST['id_operation']))&&($_POST['id_operation']!='null'))
    {
        $SQL = "select name from cli_indic_type_tbl where id = $p_id_operation ;";
       
        $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
        if ($result) 
        {
          $row = pg_fetch_array($result);
          $operation = $row['name'];
        }        

    }
    
    if ($params_caption !='') $params_caption .='<br/>';
        
    $where = ' ';

    if ($p_book!='null')
    {
        $where.= " and book = $p_book ";
        $params_caption .= " книга : $p_book ";
        
    }
    
    if ($p_id_town!='null')
    {
        $where.= "and adr.id_town = $p_id_town ";
    }

    if ($p_id_tar!='null')
    {
        $where.= "and c.id_gtar = $p_id_tar ";
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
    
    echo_html( $header_text);
   
    
    $SQL = "select c.id,c.code,c.book, 
    adr.town, adr.street,     
    address_print(c.addr) as house,
   (a.last_name||' '||coalesce(substr(a.name,1,1),'')||'.'||coalesce(substr(a.patron_name,1,1),''))||'.'::varchar as abon,
  m.num_meter,  im.name as type_meter,
    ('0'||substring(c.code FROM '[0-9]+'))::int as int_code,
    ('0'||substring(c.book FROM '[0-9]+'))::int as int_book, 
    to_char(zzz.dat_ind, 'DD.MM.YYYY') as dat_ind , zzz.indic_str, c.note, sw_name

from 
    clm_paccnt_tbl as c
    join clm_abon_tbl as a on (a.id = c.id_abon) 
    join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class)

    join  clm_meterpoint_tbl as m on (m.id_paccnt = c.id) 
    join eqi_meter_tbl as im on (im.id = m.id_type_meter)

    join ( select id_paccnt, dat_ind, trim(sum(indic||','),',')::varchar as indic_str from 
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
    ) as zzz on (zzz.id_paccnt = c.id)
    join 
    (
      select id_paccnt, count(*) as cnt
      from acm_indication_tbl as i 
      where mmgg >= $p_mmgg1 and mmgg <= $p_mmgg2
      and id_operation = $p_id_operation
      and not exists 
      (
        select i2.id from acm_indication_tbl as i2 
        where i2.mmgg >= $p_mmgg1 and i2.mmgg <= $p_mmgg2
        and i2.id_operation <> $p_id_operation
        and i2.id_paccnt = i.id_paccnt
      )
      group by id_paccnt
      having count(*) = date_part('month',age($p_mmgg2::timestamp, $p_mmgg1::timestamp))::int+1
    ) as ss on (ss.id_paccnt = c.id)

   left join (
       select csw.id_paccnt, csw.action, a.name as sw_name
       from clm_switching_tbl as csw 
        join cli_switch_action_tbl as a on (a.id = csw.action )
        join (select id_paccnt, max(dt_action) as maxdt from clm_switching_tbl where action in (1,2) group by id_paccnt) as csdt 
       on (csw.id_paccnt = csdt.id_paccnt and csw.dt_action = csdt.maxdt) 
       left join clm_switching_tbl as cswc on (csw.id_paccnt = cswc.id_paccnt and cswc.dt_action >= csw.dt_action and ((cswc.action =3 and csw.action =1) or (cswc.action =4  and csw.action =2) ) ) 
       where csw.action in (1,2) and cswc.id_paccnt is null 
       order by csw.id_paccnt
  ) as sw on (sw.id_paccnt = c.id)
where c.archive=0 $where 
order by int_book, int_code ;";

  
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
            
            
            $abon = htmlspecialchars($row['abon']);
            $addr = htmlspecialchars($row['town'].' '.$row['street'].' '.$row['house']);
            $book=$row['book'];
            $code=$row['code'];
           
            // <tr >
            echo_html( " <tr>
            <td>{$i}</td>
            <td class='c_t'>{$book}</td>
            <td class='c_t'>{$code}</td>
            <td>{$abon}</td>
            <td>{$addr}</td>
            <td class='c_t'>{$row['num_meter']}</td>
            <td>{$row['type_meter']}</td>
            <td class='c_t'>{$row['indic_str']}</td>
            <td class='c_t'>{$row['dat_ind']}</td>
            <td class='c_t'>{$row['sw_name']}</td>
            <td class='c_t'>{$row['note']}</td>            
            </tr> 
            ",$print_flag);
            
            $i++;
        }
        
    }
    
    eval("\$footer_text_eval = \"$footer_text\";");
    echo_html( $footer_text_eval);

    //return;
    
}
//----------------------------------------------------------------------------
if ($oper=='gek_demand_bill')
{
    $p_mmgg = sql_field_val('dt_b', 'date');
    
    $mmgg1_str = trim($_POST['period_str']);
    
    $params_caption=' згідно виписаним рахункам ';

    $where = '  ';
    
    eval("\$header_text_eval = \"$header_text\";");
    echo_html( $header_text_eval);
    

    $SQL = "select g.id, g.name, g.num_gek, count(distinct acc.id ) as cnt, sum(b.demand) as demand
from adi_build_tbl as g 
left join clm_paccnt_tbl as acc 
                        on ((acc.addr).id_class = (g.addr).id_class 
                        and (acc.addr).house = (g.addr).house 
                        and coalesce((acc.addr).slash,'') = coalesce((g.addr).slash,'') 
                        and coalesce((acc.addr).korp,'') = coalesce((g.addr).korp,'') 
                        and coalesce(acc.idk_house,1) not in (2,4) 
                        and acc.archive = 0 )
left join acm_bill_tbl as b on (b.id_paccnt = acc.id and b.mmgg = $p_mmgg::date and b.id_pref = 10 and coalesce(b.demand,0) <> 0)
--where 
--and coalesce((acc.addr).flat,'')<>''    
group by g.id, g.name, g.num_gek
order by g.num_gek, g.name;";

   // throw new Exception(json_encode($SQL));
    
    $current_gek='';
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        //throw new Exception("Try to save file $rows_count ....");    
        
        $cnt_headers = 0;
        $nm=1;
        
        $demand_all=0;
        $demand_gek_all=0;

        $np=0;
        
        while ($row = pg_fetch_array($result)) {

            
            if ($current_gek!=$row['num_gek'])
            {

                if($current_gek!='')    
                {
                    
                        $demand_txt=number_format_ua($demand_gek_all,0);
                        
                        echo_html( "
                        <tr class='table_footer'>
                            <td colspan='3'>Всього {$current_gek}</td>
                            <td>&nbsp;</td>
                            <td class='c_i'>$demand_txt</td>
                        </tr>  ");        
                            
                        $demand_gek_all=0;    
                }   

                $current_gek=$row['num_gek'];                
                $cnt_headers++;
            }
            
               
                $name = htmlspecialchars($row['name']);
                
                $demand_txt=number_format_ua($row['demand'],0);
                
echo <<<TEXT_BLOC2
                    <tr class='table_str'>
                     <td class='c_i'>{$nm}</td>                
                     <td class='c_t'>{$row['num_gek']}</td>
                     <td class='c_t'><a href='javascript:show_gek_detals("{$row['id']}")' class='zvit_link'>{$name}</a></td>
                     <td class='c_i'>{$row['cnt']}</td>
                     <td class='c_i'>{$demand_txt}</td>
                    </tr>          
TEXT_BLOC2;
                 $nm++;
                 
                 $demand_gek_all+= $row['demand'];
                 $demand_all+= $row['demand'];

        }
    }

    $demand_txt=number_format_ua($demand_gek_all,0);
    
    echo_html("
        <tr class='table_footer'>
        <td colspan='3'>Всього {$current_gek}</td>
        <td>&nbsp;</td>
        <td class='c_i'>$demand_txt</td>
    </tr>  ");

    $demand_txt=number_format_ua($demand_all,0);
    
    echo_html("
        <tr class='table_footer'>
        <td colspan='3'> ВСЬОГО </td>
        <td>&nbsp;</td>
        <td class='c_i'>$demand_txt</td>
    </tr>  ");

    eval("\$footer_text_eval = \"$footer_text\";");
    echo_html( $footer_text_eval);
    
}
//----------------------------------------------------------------------------
if ($oper=='gek_demand')
{
    $p_mmgg = sql_field_val('dt_b', 'date');
    
    $mmgg1_str = trim($_POST['period_str']);
    
    $params_caption=' згідно поточним показникам';

    $where = '  ';
    
    eval("\$header_text_eval = \"$header_text\";");
    echo_html( $header_text_eval);
    

    $SQL = "select g.id, g.name, g.num_gek, count(distinct acc.id ) as cnt, sum(b.value_cons) as demand
from adi_build_tbl as g 
left join clm_paccnt_tbl as acc 
                        on ((acc.addr).id_class = (g.addr).id_class 
                        and (acc.addr).house = (g.addr).house 
                        and coalesce((acc.addr).slash,'') = coalesce((g.addr).slash,'') 
                        and coalesce((acc.addr).korp,'') = coalesce((g.addr).korp,'') 
                        and coalesce(acc.idk_house,1) not in (2,4) 
                        and acc.archive = 0 )
left join acm_indication_tbl as b on (b.id_paccnt = acc.id and b.mmgg = $p_mmgg::date  )
--where 
--and coalesce((acc.addr).flat,'')<>''    
group by g.id, g.name, g.num_gek
order by g.num_gek, g.name;";

   // throw new Exception(json_encode($SQL));
    
    $current_gek='';
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        //throw new Exception("Try to save file $rows_count ....");    
        
        $cnt_headers = 0;
        $nm=1;
        
        $demand_all=0;
        $demand_gek_all=0;

        $np=0;
        
        while ($row = pg_fetch_array($result)) {

            
            if ($current_gek!=$row['num_gek'])
            {

                if($current_gek!='')    
                {
                    
                        $demand_txt=number_format_ua($demand_gek_all,0);
                        
                        echo_html( "
                        <tr class='table_footer'>
                            <td colspan='3'>Всього {$current_gek}</td>
                            <td>&nbsp;</td>
                            <td class='c_i'>$demand_txt</td>
                        </tr>  ");        
                            
                        $demand_gek_all=0;    
                }   

                $current_gek=$row['num_gek'];                
                $cnt_headers++;
            }
            
               
                $name = htmlspecialchars($row['name']);
                
                $demand_txt=number_format_ua($row['demand'],0);
                
echo <<<TEXT_BLOC2
                    <tr class='table_str'>
                     <td class='c_i'>{$nm}</td>                
                     <td class='c_t'>{$row['num_gek']}</td>
                     <td class='c_t'>{$name}</a></td>
                     <td class='c_i'>{$row['cnt']}</td>
                     <td class='c_i'>{$demand_txt}</td>
                    </tr>          
TEXT_BLOC2;
                 $nm++;
                 
                 $demand_gek_all+= $row['demand'];
                 $demand_all+= $row['demand'];

        }
    }

    $demand_txt=number_format_ua($demand_gek_all,0);
    
    echo_html("
        <tr class='table_footer'>
        <td colspan='3'>Всього {$current_gek}</td>
        <td>&nbsp;</td>
        <td class='c_i'>$demand_txt</td>
    </tr>  ");

    $demand_txt=number_format_ua($demand_all,0);
    
    echo_html("
        <tr class='table_footer'>
        <td colspan='3'> ВСЬОГО </td>
        <td>&nbsp;</td>
        <td class='c_i'>$demand_txt</td>
    </tr>  ");

    eval("\$footer_text_eval = \"$footer_text\";");
    echo_html( $footer_text_eval);
    
}
//----------------------------------------------------------------------------

if ($oper=='multif_demand')
{
    $p_mmgg = sql_field_val('dt_b', 'date');
    
    $mmgg1_str = trim($_POST['period_str']);
    
    $params_caption='';

    $where = '  ';
    
    eval("\$header_text_eval = \"$header_text\";");
    echo_html( $header_text_eval);
    

    $SQL = " select gid, floors, addr, count(distinct id ) as cnt, sum(demand) as demand
    from (
    select   (adr.town||' '||adr.street||' '||
                       (coalesce('буд.'||(acc.addr).house||'',''))::varchar   )::varchar as addr,
    g.id as gid, g.floors, acc.id , b.demand
from clm_paccnt_tbl as acc 
join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class)     
join adi_multifloor_tbl as g on ((acc.addr).id_class = g.id_street
                        and (acc.addr).house =g.build)
left join acm_bill_tbl as b on (b.id_paccnt = acc.id and  b.mmgg = $p_mmgg::date and b.id_pref = 10)
where coalesce(idk_house,1) not in (2,4)
) as sss    
    
group by gid, floors, addr
order by addr;";

   // throw new Exception(json_encode($SQL));
    
    $current_gek='';
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        //throw new Exception("Try to save file $rows_count ....");    
        
        $cnt_headers = 0;
        $nm=1;
        
        $demand_all=0;
        $cnt_all=0;
        //$demand_gek_all=0;

        $np=0;
        
        while ($row = pg_fetch_array($result)) {

/*            
            if ($current_gek!=$row['num_gek'])
            {

                if($current_gek!='')    
                {
                    
                        $demand_txt=number_format_ua($demand_gek_all,0);
                        
                        echo_html( "
                        <tr class='table_footer'>
                            <td colspan='3'>Всього {$current_gek}</td>
                            <td>&nbsp;</td>
                            <td class='c_i'>$demand_txt</td>
                        </tr>  ");        
                            
                        $demand_gek_all=0;    
                }   

                $current_gek=$row['num_gek'];                
                $cnt_headers++;
            }
  */          
               
                $addr = htmlspecialchars($row['addr']);
                
                $demand_txt=number_format_ua($row['demand'],0);
                
echo <<<TEXT_BLOC2
                    <tr class='table_str'>
                     <td class='c_i'>{$nm}</td>                
                     <td class='c_t'><a href='javascript:show_multif_detals("{$row['gid']}")' class='zvit_link'>{$addr}</a></td>
                     <td class='c_t'>{$row['floors']}</td>                     
                     <td class='c_i'>{$row['cnt']}</td>
                     <td class='c_i'>{$demand_txt}</td>
                    </tr>          
TEXT_BLOC2;
                 $nm++;
                 
                 //$demand_gek_all+= $row['demand'];
                 $demand_all+= $row['demand'];
                 $cnt_all+= $row['cnt'];

        }
    }
/*
    $demand_txt=number_format_ua($demand_gek_all,0);
    
    echo_html("
        <tr class='table_footer'>
        <td colspan='3'>Всього {$current_gek}</td>
        <td>&nbsp;</td>
        <td class='c_i'>$demand_txt</td>
    </tr>  ");
*/
    $demand_txt=number_format_ua($demand_all,0);
    $cnt_txt=number_format_ua($cnt_all,0);
    
    echo_html("
        <tr class='table_footer'>
        <td colspan='3'> ВСЬОГО </td>
        <td class='c_i'>$cnt_txt</td>
        <td class='c_i'>$demand_txt</td>
    </tr>  ");

    eval("\$footer_text_eval = \"$footer_text\";");
    echo_html( $footer_text_eval);
    
}

//------------------------------------------------------------------------------

if ($oper=='gek_detail')
{
    $p_mmgg = sql_field_val('dt_b', 'date');
    $p_id_house = sql_field_val('grid_params', 'int');
    
    $mmgg1_str = trim($_POST['period_str']);
    
    $params_caption='';

    $where = '  ';
    
    eval("\$header_text_eval = \"$header_text\";");
    echo_html( $header_text_eval);
    

    $SQL = "select  g.name, acc.code,acc.book, 
   (a.last_name||' '||coalesce(substr(a.name,1,1),'')||'.'||coalesce(substr(a.patron_name,1,1),''))||'.'::varchar as abon,
    b.demand,
    ('0'||substring(acc.code FROM '[0-9]+'))::int as int_code,
    ('0'||substring(acc.book FROM '[0-9]+'))::int as int_book ,
     address_print(acc.addr) as house
    
from clm_paccnt_tbl as acc 
join clm_abon_tbl as a on (a.id = acc.id_abon) 
join adi_build_tbl as g on ((acc.addr).id_class = (g.addr).id_class 
                        and (acc.addr).house = (g.addr).house 
                        and coalesce((acc.addr).korp,'') = coalesce((g.addr).korp,'') )
left join acm_bill_tbl as b on (b.id_paccnt = acc.id and b.mmgg = $p_mmgg::date and b.id_pref = 10 and coalesce(b.demand,0) <> 0 )
where  g.id = $p_id_house
and coalesce(acc.idk_house,1) not in (2,4) and acc.archive = 0
order by int_book, int_code";

   // throw new Exception(json_encode($SQL));
    
    $current_gek='';
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        //throw new Exception("Try to save file $rows_count ....");    
        
        $nm=1;
        
        $demand_all=0;

        $np=0;
        
        while ($row = pg_fetch_array($result)) {

                $name = htmlspecialchars($row['name']);
                $abon = htmlspecialchars($row['abon']);
                $book=$row['book'];
                $code=$row['code'];
                
                $demand_txt=number_format_ua($row['demand'],0);
                
                echo_html( "
                    <tr class='table_str'>
                     <td class='c_i'>{$nm}</td>   
                     <td class='c_t'>{$name}</td>
                     <td class='c_t'>{$book}</td>
                     <td class='c_t'>{$code}</td>
                     <td class='c_t'>{$abon}</td>                     
                     <td class='c_t'>{$row['house']}</td>
                     <td class='c_i'>{$demand_txt}</td>
                    </tr>          
                  ");

                 $nm++;
                 
                 $demand_all+= $row['demand'];

        }
    }

    $demand_txt=number_format_ua($demand_all,0);

    eval("\$footer_text_eval = \"$footer_text\";");
    echo_html( $footer_text_eval);
    
}
//------------------------------------------------------------------------------

if ($oper=='multif_detail')
{
    $p_mmgg = sql_field_val('dt_b', 'date');
    $p_id_house = sql_field_val('grid_params', 'int');
    
    $mmgg1_str = trim($_POST['period_str']);
    
    $params_caption='';

    $where = '  ';
    
    eval("\$header_text_eval = \"$header_text\";");
    echo_html( $header_text_eval);
    

    $SQL = "select  acc.code,acc.book, 
   (a.last_name||' '||coalesce(substr(a.name,1,1),'')||'.'||coalesce(substr(a.patron_name,1,1),''))||'.'::varchar as abon,
    b.demand,
    ('0'||substring(acc.code FROM '[0-9]+'))::int as int_code,
    ('0'||substring(acc.book FROM '[0-9]+'))::int as int_book ,
     (adr.town||' '||adr.street||' '||address_print(acc.addr))::varchar as house
    
from clm_paccnt_tbl as acc 
join clm_abon_tbl as a on (a.id = acc.id_abon) 
join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class)         
join adi_multifloor_tbl as g on ((acc.addr).id_class = g.id_street
                        and (acc.addr).house =g.build)
left join acm_bill_tbl as b on (b.id_paccnt = acc.id and  b.mmgg = $p_mmgg::date and b.id_pref = 10)

where g.id = $p_id_house
and coalesce(idk_house,1) not in (2,4)    
order by int_book, int_code";

   // throw new Exception(json_encode($SQL));
    
    $current_gek='';
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        //throw new Exception("Try to save file $rows_count ....");    
        
        $nm=1;
        
        $demand_all=0;

        $np=0;
        
        while ($row = pg_fetch_array($result)) {

                //$name = htmlspecialchars($row['name']);
                $abon = htmlspecialchars($row['abon']);
                $book=$row['book'];
                $code=$row['code'];
                
                $demand_txt=number_format_ua($row['demand'],0);
                
                echo_html( "
                    <tr class='table_str'>
                     <td class='c_i'>{$nm}</td>   
                     <td class='c_t'>{$book}</td>
                     <td class='c_t'>{$code}</td>
                     <td class='c_t'>{$abon}</td>                     
                     <td class='c_t'>{$row['house']}</td>
                     <td class='c_i'>{$demand_txt}</td>
                    </tr>          
                  ");

                 $nm++;
                 
                 $demand_all+= $row['demand'];

        }
    }

    $demand_txt=number_format_ua($demand_all,0);

    eval("\$footer_text_eval = \"$footer_text\";");
    echo_html( $footer_text_eval);
    
}
//------------------------------------------------------------------------------
if ($oper=='debetor_cnt_list')
{

    $p_mmgg = sql_field_val('dt_b', 'mmgg');
    $mmgg1_str = trim($_POST['period_str']);
    $p_dt = sql_field_val('dt_rep', 'date');
    $p_dt_txt = sql_field_val('dt_rep', 'text'); 
    
    $p_book = sql_field_val('book', 'string');
    $p_id_town = sql_field_val('addr_town', 'int');
    
    $p_id_sector = sql_field_val('id_sector', 'int');
    $p_id_region = sql_field_val('id_region', 'int');
    
    $SQL = "select ''''||to_char( value_ident::date , 'YYYY-MM-DD')||'''' as start_mmgg 
        from syi_sysvars_tbl where ident = 'dt_start';";
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);    
    
    if ($result) {
        $row = pg_fetch_array($result); 
        $start_mmgg=$row['start_mmgg'];
    }

    
    if ((isset($_POST['addr_town_name']))&&($_POST['addr_town_name']!=''))
        $params_caption = 'Населений пункт: '. trim($_POST['addr_town_name']);
    else 
        $params_caption ='';

    if ((isset($_POST['sector']))&&($_POST['sector']!=''))
        $params_caption.= ' Дільниця: '. trim($_POST['sector']);

    if ($params_caption !='') $params_caption .='<br/>';
        
    $where = ' ';

    if ($p_book!='null')
    {
        $where.= " and book = $p_book ";
        $params_caption .= " книга : $p_book ";        
    }
    
    if ($p_id_town!='null')
    {
        $where.= "and adr.id_town = $p_id_town ";
    }

    if ($p_id_sector!='null')
    {
        $where.= "and exists (select id from prs_runner_paccnt as rp 
            where rp.id_paccnt = acc.id and rp.id_sector = $p_id_sector ) ";
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
    
    echo_html( $header_text);
   
    $sum_debet = 0;
    $cnt_off = 0;
    $cnt_w = 0;
    $cnt_ww = 0;
    
    if ($p_mmgg <= $start_mmgg)
    {
    $SQL = "  select ss.id_paccnt,dt_all, 
  (dt_all - coalesce(p2.pay,0) -  coalesce(bill_corr,0)) as dt , p2.pay, 
  CASE WHEN sw.action = 1 THEN to_char(sw.dt_action, 'DD.MM.YYYY') END as date_off,
  CASE WHEN sw.action = 2 THEN to_char(sw.dt_action, 'DD.MM.YYYY') END as date_warning,
  CASE WHEN sw2.dt_warning is not null THEN to_char(sw2.dt_warning, 'DD.MM.YYYY') END as date_warning_ww,
  acc.book, acc.code, acc.note, p.represent_name as kontrol_name,  ap.name as add_param,
  sw.action, sw.dt_action, sw2.dt_warning, 
  coalesce(ab.home_phone,'')||coalesce(','||ab.mob_phone,'') as phone,
  ('0'||substring(acc.code FROM '[0-9]+'))::int as int_code,
  ('0'||substring(acc.book FROM '[0-9]+'))::int as int_book,
  (ab.last_name||' '||coalesce(ab.name,'')||' '||coalesce(ab.patron_name,''))::varchar as abon,
  (adr.town||', '||adr.street||' '||
        (coalesce('буд.'||(acc.addr).house||'','')||
		coalesce('/'||(acc.addr).slash||' ','')|| 
			coalesce(' корп.'||(acc.addr).korp||'','')||
				coalesce(', кв. '||(acc.addr).flat,'')||
					coalesce('/'||(acc.addr).f_slash,''))::varchar
    )::varchar as addr
      
   from 
   clm_paccnt_tbl as acc 
   join clm_abon_tbl as ab on (ab.id = acc.id_abon)         
   join acm_saldo_tbl as s on (s.id_paccnt = acc.id and s.mmgg = $p_mmgg and s.id_pref=10 and s.e_val >0 )                
   join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class)      
   join prs_runner_paccnt as rp on (rp.id_paccnt = acc.id)
   join prs_runner_sectors as rs on (rp.id_sector = rs.id)
   join
   (
     select id_paccnt, sum(value) as dt_all
     from acm_bill_tbl where id_pref = 10 
      and mmgg = ($start_mmgg::date - '1 month'::interval)::date 
      and mmgg_bill <= ($p_mmgg::date - '2 month'::interval)::date
     group by id_paccnt order by id_paccnt
   ) as ss
   on (ss.id_paccnt = acc.id )
   left join prs_persons as p on (p.id = rs.id_kontrol)        
   left join cli_addparam_tbl as ap on (ap.id = acc.id_cntrl)        
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
   		-- where (csw.action =1 or (csw.action =2 and csw.mmgg >= $p_mmgg ) )
                where (csw.action =1 or csw.action =2 )
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
        
   where (dt_all - coalesce(p2.pay,0) -  coalesce(bill_corr,0)) > 0 
   and dt_all >0  $where order by int_book, int_code
";
        
        
    }
    else
    {
    $SQL = "  select ss.id_paccnt,dt_all,  
  (dt_all - coalesce(p1.pay,0)- coalesce(p2.pay,0)-  coalesce(bill_corr,0)) as dt , p1.pay,p2.pay, sw.action, sw.dt_action, sw2.dt_warning,
  CASE WHEN sw.action = 1 THEN to_char(sw.dt_action, 'DD.MM.YYYY') END as date_off,
  CASE WHEN sw.action = 2 THEN to_char(sw.dt_action, 'DD.MM.YYYY') END as date_warning,
  CASE WHEN sw2.dt_warning is not null THEN to_char(sw2.dt_warning, 'DD.MM.YYYY') END as date_warning_ww,
  acc.book, acc.code, acc.note, p.represent_name as kontrol_name ,ap.name as add_param,
  ('0'||substring(acc.code FROM '[0-9]+'))::int as int_code,
  ('0'||substring(acc.book FROM '[0-9]+'))::int as int_book,
   coalesce(ab.home_phone,'')||coalesce(','||ab.mob_phone,'') as phone,        
  (ab.last_name||' '||coalesce(ab.name,'')||' '||coalesce(ab.patron_name,''))::varchar as abon,
  (adr.town||', '||adr.street||' '||
        (coalesce('буд.'||(acc.addr).house||'','')||
		coalesce('/'||(acc.addr).slash||' ','')|| 
			coalesce(' корп.'||(acc.addr).korp||'','')||
				coalesce(', кв. '||(acc.addr).flat,'')||
					coalesce('/'||(acc.addr).f_slash,''))::varchar
    )::varchar as addr
        
   from 
   clm_paccnt_tbl as acc 
   join clm_abon_tbl as ab on (ab.id = acc.id_abon)                 
   join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class)        
   -- join acm_saldo_tbl as s on (s.id_paccnt = acc.id and s.mmgg = $p_mmgg and s.id_pref=10 and s.e_val >0 )                
   join prs_runner_paccnt as rp on (rp.id_paccnt = acc.id)
   join prs_runner_sectors as rs on (rp.id_sector = rs.id)
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
   left join prs_persons as p on (p.id = rs.id_kontrol)        
   left join cli_addparam_tbl as ap on (ap.id = acc.id_cntrl)        
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
   		--where (csw.action =1 or (csw.action =2 and csw.mmgg >= $p_mmgg ) )
                where (csw.action =1 or csw.action =2 )
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
           and idk_doc <> 1000                 
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
   and dt_all >0  $where  order by int_book, int_code ";
    };

  
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
            
            
            $abon = htmlspecialchars($row['abon']);
            $addr = htmlspecialchars($row['addr']);
            $kontrol_name = htmlspecialchars($row['kontrol_name']);
            $add_param = htmlspecialchars($row['add_param']);
            $book=$row['book'].'/'.$row['code'];
            
            $debet_txt=number_format_ua($row['dt'],2);
            
            if($row['date_off']!='') $cnt_off++;
            if($row['date_warning']!='') $cnt_w++;
            if($row['date_warning_ww']!='') $cnt_ww++;
            
            $sum_debet+=$row['dt'];
            
            // <tr >
            echo_html( " <tr>
            <td>{$i}</td>
            <td class='c_t'>{$book}</td>
            <td>{$abon}</td>
            <td>{$addr}</td>
            <td class='c_n'>{$debet_txt}</td>
            <td class='c_t'>{$row['date_off']}</td>
            <td class='c_t'>{$row['date_warning']}</td>
            <td class='c_t'>{$row['date_warning_ww']}</td>
            <td class='c_t'>{$kontrol_name}</td>           
            <td class='c_t'>{$add_param}</td>            
            <td class='c_t'>{$row['note']}</td>            
            <td class='c_t'>{$row['phone']}</td>
            </tr> 
            ",$print_flag);
            
            $i++;
        }
        
    }
    
    $debet_txt=number_format_ua($sum_debet,2);
    
    eval("\$footer_text_eval = \"$footer_text\";");
    echo_html( $footer_text_eval);

    //return;
    
}
//----------------------------------------------------------------------------

if ($oper=='cabinet_indic_list')
{

    $p_mmgg = sql_field_val('dt_b', 'mmgg');    
    $period = trim($_POST['period_str']);
    //разбор и обработка строки параметров jqgrid
    $qWhere='';
    if (isset($_POST['grid_params'])) {
          $grid_params_json = $_POST['grid_params'];
          $json_str = stripslashes($grid_params_json); 
          $grid_params = json_decode($json_str,true);
          
          $fildsArray =DbGetFieldsArray($Link,'acd_cabindication_tbl');
          $fildsArray['book'] =   array('f_name'=>'book','f_type'=>'char');
          $fildsArray['code'] =   array('f_name'=>'code','f_type'=>'char');
          $fildsArray['addr']= array('f_name'=>'addr','f_type'=>'character varying');
          $fildsArray['abon'] =   array('f_name'=>'abon','f_type'=>'character varying');
          $fildsArray['sector'] =   array('f_name'=>'sector','f_type'=>'character varying');
          $fildsArray['name_zone'] =   array('f_name'=>'name_zone','f_type'=>'character varying');
          $fildsArray['represent_name'] =   array('f_name'=>'represent_name','f_type'=>'character varying');
          $fildsArray['demand'] =   array('f_name'=>'demand','f_type'=>'int');
          $qWhere= DbBuildWhere($grid_params,$fildsArray);

    }
    if ($qWhere!='') $qWhere=$qWhere.' and ';
    else $qWhere=' where ';

    $qWhere=$qWhere." date_trunc('month', dat_ind ) = date_trunc('month', {$p_mmgg}::date) ";
    
    $params_caption = '';

    $sum_lgt = 0;

    $i=1;
    
    eval("\$header_text = \"$header_text\";");
    echo_html ($header_text);
    
    $SQL = "
    select * from (select pd.value_prev::int, pd.value_ind::int, pd.calc_ind_pr::int,
    pd.num_eqp,pd.carry, pd.dat_ind,
    acc.book, acc.code, 
  adr.street||' '||address_print(acc.addr) as addr, 
  (c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon,
  ('0'||substring(acc.code FROM '[0-9]+'))::int as int_code,
  ('0'||substring(acc.book FROM '[0-9]+'))::int as int_book,
  rs.name as sector, pr.represent_name, z.nm as name_zone, pd.id_status, 
  CASE WHEN pd.id_status = 0 THEN 'Нові' 
       WHEN pd.id_status = 1 THEN 'Прийняті' 
       WHEN pd.id_status = 2 THEN 'Помилкові' END as status_name,
  to_char(pd.dat_ind , 'DD.MM.YYYY') as dat_ind_txt ,
  to_char(pd.now, 'DD.MM.YYYY HH24:MI') as dt_txt, 
  round(calc_demand_carry(pd.value_ind, pd.value_prev ,pd.carry),0)::int as demand    
  from acd_cabindication_tbl as pd 
  join eqk_zone_tbl as z on (z.id = pd.id_zone)
  join clm_paccnt_tbl as acc on (acc.id = pd.id_paccnt)
  join clm_abon_tbl as c on (c.id = acc.id_abon) 
  join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class)
  join prs_runner_paccnt as rp on (rp.id_paccnt = acc.id)
  join prs_runner_sectors as rs on (rp.id_sector = rs.id) 
  left join prs_persons as pr on (rs.id_operator = pr.id) 
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
            $sector = htmlspecialchars($row['sector']);
            $represent_name = htmlspecialchars($row['represent_name']);
            
            echo_html( "
            <tr >
            <td>{$i}</td>            
            <td class='c_t'>{$row['book']}</td>
            <td class='c_t'>{$row['code']}</td>
            <td class='c_t'>$addr</td>
            <td class='c_t'>$abon</td>
            <td class='c_t'>$sector</td>
            <td class='c_t'>$represent_name</td>            
            <td class='c_t'>{$row['num_eqp']}</td>
            <td class='c_i'>{$row['carry']}</td>
            <td class='c_t'>{$row['name_zone']}</td>
            <td class='c_i'>{$row['value_prev']}</td>
            <td class='c_i'>{$row['value_ind']}</td>
            <td class='c_t'>{$row['dat_ind_txt']}</td>
            <td class='c_i'>{$row['demand']}</td>
            <td class='c_t'>{$row['dt_txt']}</td>
            <td class='c_t'>{$row['status_name']}</td>
            </tr>  ");        
            $i++;
            
        }
        
    }
    
    eval("\$footer_text_eval = \"$footer_text\";");
    echo_html( $footer_text_eval);

}
//----------------------------------------------------------------------------

if ($oper=='cabinet_indic_bad')
{

    $p_mmgg = sql_field_val('dt_b', 'mmgg');    
    $period = trim($_POST['period_str']);
    //разбор и обработка строки параметров jqgrid
    $qWhere='';
    if (isset($_POST['grid_params'])) {
          $grid_params_json = $_POST['grid_params'];
          $json_str = stripslashes($grid_params_json); 
          $grid_params = json_decode($json_str,true);
          $fildsArray =DbGetFieldsArray($Link,'acd_cabindication_bad_tbl');
          $fildsArray['addr']= array('f_name'=>'addr','f_type'=>'character varying');
          $fildsArray['abon'] =   array('f_name'=>'abon','f_type'=>'character varying');
          $qWhere= DbBuildWhere($grid_params,$fildsArray);

    }
    if ($qWhere!='') $qWhere=$qWhere.' and ';
    else $qWhere=' where ';

    $qWhere=$qWhere." date_trunc('month', period ) = date_trunc('month', {$p_mmgg}::date) ";
    
    $params_caption = '';

    $sum_lgt = 0;

    $i=1;
    
    eval("\$header_text = \"$header_text\";");
    echo_html ($header_text);
    
    $SQL = "
    select * from (select pd.id_zone, pd.id_operation, pd.lic, pd.period, pd.dt_ind,
    pd.meter_num as num_eqp, pd.curr_ind::int,
    acc.book, acc.code, 
    adr.street||' '||address_print(acc.addr) as addr, 
      (c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon,
       rs.name as sector, pr.represent_name, 
    to_char(pd.dt_ind , 'DD.MM.YYYY') as dat_ind_txt ,
    to_char(pd.curr_dt, 'DD.MM.YYYY HH24:MI') as dt_txt
    from acd_cabindication_bad_tbl as pd 
    left join clm_paccnt_tbl as acc on (acc.id = pd.id_paccnt)
    left join clm_abon_tbl as c on (c.id = acc.id_abon) 
    left join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class)
    left join prs_runner_paccnt as rp on (rp.id_paccnt = acc.id)
    left join prs_runner_sectors as rs on (rp.id_sector = rs.id) 
    left join prs_persons as pr on (rs.id_operator = pr.id) 
   ) as ss
   $qWhere Order by dt_ind ;";    
    
   // throw new Exception(json_encode($SQL));
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        $i=1;
        while ($row = pg_fetch_array($result)) {

            $abon = htmlspecialchars($row['abon']);
            $addr = htmlspecialchars($row['addr']);
            $sector = htmlspecialchars($row['sector']);
            $represent_name = htmlspecialchars($row['represent_name']);
            
            echo_html( "
            <tr >
            <td>{$i}</td>            
            <td class='c_t'>{$row['lic']}</td>
            <td class='c_t'>$addr</td>
            <td class='c_t'>$abon</td>
            <td class='c_t'>$sector</td>
            <td class='c_t'>$represent_name</td>            
            <td class='c_t'>{$row['num_eqp']}</td>
            <td class='c_t'>{$row['id_zone']}</td>
            <td class='c_i'>{$row['curr_ind']}</td>
            <td class='c_t'>{$row['dat_ind_txt']}</td>
            <td class='c_t'>{$row['dt_txt']}</td>
            </tr>  ");        
            $i++;
            
        }
        
    }
    
    eval("\$footer_text_eval = \"$footer_text\";");
    echo_html( $footer_text_eval);

}
//------------------------------------------------------------------------------
if ($oper=='abon_lgt_children')
{

    $p_mmgg = sql_field_val('dt_b', 'mmgg');
    $mmgg1_str = trim($_POST['period_str']);

    $p_book = sql_field_val('book', 'string');
    $p_id_town = sql_field_val('addr_town', 'int');
    $p_id_sector = sql_field_val('id_sector', 'int');
    $p_id_region = sql_field_val('id_region', 'int');    

    if ((isset($_POST['addr_town_name']))&&($_POST['addr_town_name']!=''))
        $params_caption = 'Населений пункт: '. trim($_POST['addr_town_name']);
    else 
        $params_caption ='';


    if ((isset($_POST['sector']))&&($_POST['sector']!=''))
        $params_caption.= ' Дільниця: '. trim($_POST['sector']);
    
    $where = ' ';

    if ($p_book!='null')
    {
        $where.= " and book = $p_book ";
        $params_caption .= " книга : $p_book ";
    }
    
    if ($p_id_town!='null')
    {
        $where.= "and ( adr.id_town = $p_id_town or adr.id_parent = $p_id_town )";
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
    
    echo_html( $header_text);
   
    
    $SQL = " select c.id, c.book,c.code, tar.nm as tarif_name, 
  (a.last_name||' '||coalesce(a.name,'')||' '||coalesce(a.patron_name,''))::varchar as abon,
lg.family_cnt,lg.s_doc, lg.n_doc,lg.ident_cod_l,
    g.name as lgt_name, g.ident,g.alt_code,
    lg.id_grp_lgt, bls.id_grp_lgt,c.id_gtar, 
    adr.town, (adr.street||' '||
        (coalesce('буд.'||(c.addr).house||'','')||
		coalesce('/'||(c.addr).slash||' ','')|| 
			coalesce(' корп.'||(c.addr).korp||'','')||
				coalesce(', кв. '||(c.addr).flat,'')||
					coalesce('/'||(c.addr).f_slash,''))::varchar
    )::varchar as addr,
    regexp_replace(regexp_replace(c.code, '-.*?$', '') , '[^0-9]', '','g')::int as int_code,
    regexp_replace(regexp_replace(c.book, '-.*?$', '') , '[^0-9]', '','g')::int as int_book,
    pss.sum_subs ,
    bls.sum_lgt, bls.id_grp_lgt, g2.name as lgt_name2
     from 
     clm_paccnt_tbl as c
     join clm_abon_tbl as a on (a.id = c.id_abon) 
     join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class)    
     join aqi_grptar_tbl as tar on (tar.id = c.id_gtar)
     left join 
     (
        select lg.* from lgm_abon_h as lg 
        join (select id, max(dt_b) as dt_b from lgm_abon_h  where
            ((dt_b < ($p_mmgg::date+'1 month'::interval) 
             and ( dt_e is null and (dt_end is null or dt_end >= $p_mmgg )  )
             )
            or 
            tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg::timestamp::abstime,($p_mmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
        )   group by id order by id) as lg2 
        on (lg.id = lg2.id and lg.dt_b = lg2.dt_b and (lg.dt_end is null or lg.dt_end >= $p_mmgg ))
     ) as lg on (lg.id_paccnt = c.id) 
    left join 
     (
        select b.id_paccnt, ls.id_grp_lgt, sum(summ_lgt) as sum_lgt
          from acm_bill_tbl as b 
          join acm_lgt_summ_tbl as ls on (ls.id_doc = b.id_doc)
          where  b.id_pref = 10
          and b.mmgg = $p_mmgg

       group by b.id_paccnt, ls.id_grp_lgt
        having sum(summ_lgt) <>0
     ) as bls on (bls.id_paccnt = c.id ) 
    left  join (
      select id_paccnt, sum(subs_all) as sum_subs, sum(val_month) as val_month
      from acm_subs_tbl where mmgg = $p_mmgg
      and subs_all>0 and val_month >0
      group by id_paccnt
      order by id_paccnt
     ) as pss on (pss.id_paccnt = c.id)
   
    left join lgi_group_tbl as g on (lg.id_grp_lgt = g.id)
    left join lgi_group_tbl as g2 on (bls.id_grp_lgt = g2.id)
    where c.archive =0 
    and ( bls.id_grp_lgt in (16,103,102,104) or lg.id_grp_lgt in (16,103,102,104) or c.id_gtar in (5,6,8,9,13))
    $where
    order by int_book, int_code;";

  
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
            
            
            $abon = htmlspecialchars($row['abon']);
            
            if($res_code!='310')
              $addr = htmlspecialchars($row['town'].' '.$row['addr']);
            else
              $addr = htmlspecialchars($row['addr']);
            
            $book=$row['book'];
            $code=$row['code'];
            $tarif = htmlspecialchars($row['tarif_name']);
            $lgt = htmlspecialchars($row['lgt_name']);
            $lgt2 = htmlspecialchars($row['lgt_name2']);
            // <tr >
            echo_html( " <tr>
            <td>{$i}</td>
            <td class='c_t'>{$book}</td>
            <td class='c_t'>{$code}</td>
            <td>{$abon}</td>
            <td>{$addr}</td>
            <td class='c_t'>{$tarif}</td>
            <td class='c_t'>{$lgt}</td>
            <td class='c_i'>{$row['family_cnt']}</td>
            <td class='c_t'>{$lgt2}</td>
            <td class='c_n'>{$row['sum_lgt']}</td>
            <td class='c_n'>{$row['sum_subs']}</td>
            </tr> 
            ",$print_flag);
            
            $i++;
        }
        
    }
    
    
    $SQL2 = "select c.id, c.book,c.code, tar.nm as tarif_name, 
  (a.last_name||' '||coalesce(a.name,'')||' '||coalesce(a.patron_name,''))::varchar as abon,
      (adr.street||' '||
        (coalesce('буд.'||(c.addr).house||'','')||
		coalesce('/'||(c.addr).slash||' ','')|| 
			coalesce(' корп.'||(c.addr).korp||'','')||
				coalesce(', кв. '||(c.addr).flat,'')||
					coalesce('/'||(c.addr).f_slash,''))::varchar
    )::varchar as addr,
    to_char(ss.mmgg_bill, 'MM.YYYY') as mmgg_bill,
    regexp_replace(regexp_replace(c.code, '-.*?$', '') , '[^0-9]', '','g')::int as int_code,
    regexp_replace(regexp_replace(c.book, '-.*?$', '') , '[^0-9]', '','g')::int as int_book

from 
(
 select distinct t.id_grptar, b.id_paccnt, b.mmgg_bill
          from acm_bill_tbl as b 
          join acm_lgt_summ_tbl as ls on (ls.id_doc = b.id_doc)
          join aqm_tarif_tbl as t on (t.id = ls.id_tarif)
          where  b.id_pref = 10
          and b.mmgg = $p_mmgg
       and id_grp_lgt in (16,103,102,104)
       and t.id_grptar not in (5,6,8,9,13)
) as ss
     join clm_paccnt_tbl as c on (c.id = ss.id_paccnt)
     join clm_abon_tbl as a on (a.id = c.id_abon) 
     join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class)    
     join aqi_grptar_tbl as tar on (tar.id = ss.id_grptar)
    $where
    order by int_book, int_code;";

  
       
    $result = pg_query($Link, $SQL2) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        $i=1;
        $np=0;    
        $table_text='';
        
        while ($row = pg_fetch_array($result)) {
            $np++;
            
            
            $abon = htmlspecialchars($row['abon']);
            $addr = htmlspecialchars($row['addr']);
            $book=$row['book'];
            $code=$row['code'];
            $tarif = htmlspecialchars($row['tarif_name']);
            
           $table_text.=" <tr>
            <td>{$i}</td>
            <td class='c_t'>{$book}</td>
            <td class='c_t'>{$code}</td>
            <td>{$abon}</td>
            <td>{$addr}</td>
            <td class='c_t'>{$tarif}</td>
            <td class='c_t'>{$row['mmgg_bill']}</td>
            </tr> ";
            
            $i++;
        }
        
    }
      
    eval("\$footer_text_eval = \"$footer_text\";");
    echo_html( $footer_text_eval);

    //return;
}
//----------------------------------------------------------------------------

if ($oper=='debetor_cnt_3month')
{ 
    $p_mmgg = sql_field_val('dt_b', 'mmgg');
    $mmgg1_str = trim($_POST['period_str']);
    $p_dt = sql_field_val('dt_rep', 'date');
    $p_dt_txt = sql_field_val('dt_rep', 'text'); 
    $p_id_region = sql_field_val('id_region', 'int');
    
    $params_caption = '';

    $where = '';

  $SQL = "select ''''||to_char( value_ident::date- '1 month'::interval , 'YYYY-MM-DD')||'''' as start_mmgg ,
        CASE WHEN (value_ident::date- '1 month'::interval) >= ($p_mmgg::date - '2 month'::interval )::date THEN 1 ELSE 0 END as old3,
        CASE WHEN (value_ident::date- '1 month'::interval) >= ($p_mmgg::date - '5 month'::interval )::date THEN 1 ELSE 0 END as old6
        from syi_sysvars_tbl where ident = 'dt_start';";
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);    
    
    
    if ($result) {
        $row = pg_fetch_array($result); 
        $start_mmgg=$row['start_mmgg'];
        $fold3 = $row['old3'];
        $fold6 = $row['old6'];
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
    
    
    $SQL_base_new = "select 
      round(sum (CASE WHEN dt > %sum% THEN dt ELSE 0 END)::numeric/1000,2) as debet_sum_100, 
      sum (CASE WHEN dt > %sum% THEN 1 ELSE 0 END) as debet_cnt_100,
      round(sum (CASE WHEN dt > %sum% and coalesce(action,0) = 1 THEN dt ELSE 0 END)::numeric/1000,2) as debet_sum_off_100, 
      sum (CASE WHEN dt > %sum% and coalesce(action,0) = 1 THEN 1 ELSE 0 END) as debet_cnt_off_100 , 
      round(sum (CASE WHEN dt > %sum% and coalesce(action,0) = 2 THEN dt ELSE 0 END)::numeric/1000,2) as debet_sum_w_100, 
      sum (CASE WHEN dt > %sum% and coalesce(action,0) = 2 THEN 1 ELSE 0 END) as debet_cnt_w_100  ,
      round(sum (CASE WHEN dt > %sum% and dt_warning is not null THEN dt ELSE 0 END)::numeric/1000,2) as debet_sum_ww_100, 
      sum (CASE WHEN dt > %sum% and dt_warning is not null THEN 1 ELSE 0 END) as debet_cnt_ww_100     
    from
(
  select ss.id_paccnt,dt_all,  
  (dt_all - coalesce(p1.pay,0)- coalesce(p2.pay,0) -  coalesce(bill_corr,0)) as dt , p1.pay,p2.pay, bill_corr,  sw.action, sw.dt_action, sw2.dt_warning
   from 
   clm_paccnt_tbl as acc 
   join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class)        
--   join acm_saldo_tbl as s on (s.id_paccnt = acc.id and s.mmgg = $p_mmgg and s.id_pref=10 and s.e_val >0 )    
   join 
   (
    select id_paccnt, sum(b_dtval)-coalesce(sum(b_ktval),0) as dt_all
    from seb_saldo where mmgg = ($p_mmgg::date - '%nmonth% month'::interval)::date
    and id_pref = 10 
    group by id_paccnt 
    
     union        
    select sb.id_paccnt, sum(sb.value) as dt_all
    from acm_bill_tbl as sb 
    join clm_paccnt_tbl as c on (c.id = sb.id_paccnt)
    where sb.id_pref = 10 
    and sb.mmgg = '2017-09-01'::date and c.id_dep = 260
    and sb.mmgg_bill < ($p_mmgg::date - '%nmonth% month'::interval)::date
    and idk_doc = 1000   
    and not exists (select id_paccnt from seb_saldo as ss where ss.mmgg = ($p_mmgg::date - '%nmonth% month'::interval)::date and ss.id_pref = 10  and ss.id_paccnt = sb.id_paccnt  )
    group by sb.id_paccnt 
    
    order by id_paccnt
   ) as ss 
   on (ss.id_paccnt = acc.id )
   left join
   (
    select id_paccnt, sum(value) as pay from acm_pay_tbl where 

    ( mmgg <= ($p_mmgg::date - '1 month'::interval)::date and 
      mmgg >= ($p_mmgg::date - '%nmonth% month'::interval)::date )
    and idk_doc <> 1000         
    and id_pref = 10 
    group by id_paccnt order by id_paccnt
   ) as p1 on (p1.id_paccnt = ss.id_paccnt )
   left join
   (
    select id_paccnt, sum(value) as pay from acm_pay_tbl where 
        mmgg >= $p_mmgg::date and id_pref = 10  and greatest(pay_date,reg_date) <= $p_dt::date
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
   		where csw.action  in (1,2) 
                and cswc.id_paccnt is null 
   	) as sw on (sw.id_paccnt = ss.id_paccnt)
   left join 
   	(
   	select csw.id_paccnt, csw.dt_action, csw.action, csw.dt_warning
   		from clm_switching_tbl as csw 
   		join (select id_paccnt, max(dt_action) as maxdt from clm_switching_tbl where action in (1,2) group by id_paccnt) as csdt 
   		on (csw.id_paccnt = csdt.id_paccnt and csw.dt_action = csdt.maxdt) 
   		left join clm_switching_tbl as cswc on (csw.id_paccnt = cswc.id_paccnt and cswc.dt_action >= csw.dt_action and ((cswc.action =3 and csw.action =1) or (cswc.action =4  and csw.action =2) ) ) 
   		where csw.action =2 and csw.dt_warning <$p_dt::date
                and cswc.id_paccnt is null 
   	) as sw2 on (sw2.id_paccnt = ss.id_paccnt)       
   left join    	
       (
          select id_paccnt,  -sum(value) as bill_corr 
           from acm_bill_tbl as bb where 
           bb.mmgg >= ($p_mmgg::date - '%nmonth% month'::interval)  and 
          (bb.mmgg_bill < ($p_mmgg::date - '%nmonth% month'::interval)  or  ( bb.reg_num <>'' and bb.value <0 and coalesce(bb.demand,0) = 0)) and
           bb.id_pref = 10 and bb.idk_doc=220
           group by id_paccnt 
           having sum(value) <0
           order by id_paccnt
       ) as sum_recalc
       on (sum_recalc.id_paccnt = ss.id_paccnt)       
   where (dt_all - coalesce(p1.pay,0)- coalesce(p2.pay,0) - coalesce(bill_corr,0) ) > 0 
   and dt_all >0 $where 
   ) as s ;";
    
    
    $SQL_base_old = "select 
      round(sum (CASE WHEN dt > %sum% THEN dt ELSE 0 END)::numeric/1000,2) as debet_sum_100, 
      sum (CASE WHEN dt > %sum% THEN 1 ELSE 0 END) as debet_cnt_100,
      round(sum (CASE WHEN dt > %sum% and coalesce(action,0) = 1 THEN dt ELSE 0 END)::numeric/1000,2) as debet_sum_off_100, 
      sum (CASE WHEN dt > %sum% and coalesce(action,0) = 1 THEN 1 ELSE 0 END) as debet_cnt_off_100 , 
      round(sum (CASE WHEN dt > %sum% and coalesce(action,0) = 2 THEN dt ELSE 0 END)::numeric/1000,2) as debet_sum_w_100, 
      sum (CASE WHEN dt > %sum% and coalesce(action,0) = 2 THEN 1 ELSE 0 END) as debet_cnt_w_100  ,
      round(sum (CASE WHEN dt > %sum% and dt_warning is not null THEN dt ELSE 0 END)::numeric/1000,2) as debet_sum_ww_100, 
      sum (CASE WHEN dt > %sum% and dt_warning is not null THEN 1 ELSE 0 END) as debet_cnt_ww_100     
    from
(
  select ss.id_paccnt,dt_all,  
  (dt_all - coalesce(p1.pay,0)- coalesce(p2.pay,0) -  coalesce(bill_corr,0)) as dt , p1.pay,p2.pay, bill_corr,  sw.action, sw.dt_action, sw2.dt_warning
   from 
   clm_paccnt_tbl as acc 
   join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class)       
   join acm_saldo_tbl as s on (s.id_paccnt = acc.id and s.mmgg = $p_mmgg and s.id_pref=10 and s.e_val >0 )        
   join
   (
     select id_paccnt, sum(value) as dt_all
     from acm_bill_tbl where id_pref = 10 
      and mmgg = $start_mmgg::date 
      and mmgg_bill < ($p_mmgg::date - '%nmonth% month'::interval)::date
     group by id_paccnt order by id_paccnt
   ) as ss
   on (ss.id_paccnt = acc.id )
   left join
   (
    select id_paccnt, sum(value) as pay from acm_pay_tbl where 

    ( mmgg <= ($p_mmgg::date - '1 month'::interval)::date and 
      mmgg >= ($p_mmgg::date - '%nmonth% month'::interval)::date )

    and id_pref = 10 
    group by id_paccnt order by id_paccnt
   ) as p1 on (p1.id_paccnt = ss.id_paccnt )
   left join
   (
    select id_paccnt, sum(value) as pay from acm_pay_tbl where 
        mmgg >= $p_mmgg::date and id_pref = 10  and greatest(pay_date,reg_date) <= $p_dt::date
        group by id_paccnt order by id_paccnt
   ) as p2 on (p2.id_paccnt = ss.id_paccnt )
   left join 
   	(
   	select csw.id_paccnt, csw.dt_action, csw.action 
   		from clm_switching_tbl as csw 
   		join (select id_paccnt, max(dt_action) as maxdt from clm_switching_tbl where action in (1,2) group by id_paccnt) as csdt 
   		on (csw.id_paccnt = csdt.id_paccnt and csw.dt_action = csdt.maxdt) 
   		left join clm_switching_tbl as cswc on (csw.id_paccnt = cswc.id_paccnt and cswc.dt_action >= csw.dt_action and ((cswc.action =3 and csw.action =1) or (cswc.action =4  and csw.action =2) ) ) 
   		where csw.action  in (1,2) 
                and cswc.id_paccnt is null 
   	) as sw on (sw.id_paccnt = ss.id_paccnt)
   left join 
   	(
   	select csw.id_paccnt, csw.dt_action, csw.action, csw.dt_warning
   		from clm_switching_tbl as csw 
   		join (select id_paccnt, max(dt_action) as maxdt from clm_switching_tbl where action in (1,2) group by id_paccnt) as csdt 
   		on (csw.id_paccnt = csdt.id_paccnt and csw.dt_action = csdt.maxdt) 
   		left join clm_switching_tbl as cswc on (csw.id_paccnt = cswc.id_paccnt and cswc.dt_action >= csw.dt_action and ((cswc.action =3 and csw.action =1) or (cswc.action =4  and csw.action =2) ) ) 
   		where csw.action =2 and csw.dt_warning <$p_dt::date
                and cswc.id_paccnt is null 
   	) as sw2 on (sw2.id_paccnt = ss.id_paccnt)       
   left join    	
       (
          select id_paccnt,  -sum(value) as bill_corr 
           from acm_bill_tbl as bb where 
           bb.mmgg >= ($p_mmgg::date - '%nmonth% month'::interval)  and 
          (bb.mmgg_bill < ($p_mmgg::date - '%nmonth% month'::interval)  or  ( bb.reg_num <>'' and bb.value <0 and coalesce(bb.demand,0) = 0)) and
           bb.id_pref = 10 and bb.idk_doc=220
           group by id_paccnt 
           having sum(value) <0
           order by id_paccnt
       ) as sum_recalc
       on (sum_recalc.id_paccnt = ss.id_paccnt)       
   where (dt_all - coalesce(p1.pay,0)- coalesce(p2.pay,0) - coalesce(bill_corr,0) ) > 0 
   and dt_all >0 $where 
   ) as s ;";
    
    if ($fold3 ==1)
        $SQL3 = $SQL_base_old;
    else
        $SQL3 = $SQL_base_new;

    if ($fold6 ==1)
        $SQL6 = $SQL_base_old;
    else
        $SQL6 = $SQL_base_new;

    //------------------------------- 3 month 100 grn
    $SQL = str_replace('%nmonth%','2',$SQL3);
    $SQL = str_replace('%sum%','100',$SQL);
    
   // echo $SQL;
    
    //throw new Exception(json_encode($SQL));
/*
    $debet_sum_100=0;
    $debet_cnt_100=0;
    $debet_sum_off_100=0;
    $debet_cnt_off_100=0;
    $debet_sum_w_100=0;
    $debet_cnt_w_100=0;
    $debet_sum_ww_100=0;
    $debet_cnt_ww_100=0;
*/    
    $cnt_month=3;
    $min_summ = 100;
    $det_id=1;
    
    $str_text = file_get_contents("html_templates/".$template_name."_str.htm");
    eval("\$str_text = \"$str_text\";");
    echo $str_text;
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        $i=0;
        while ($row = pg_fetch_array($result)) {

           
            $debet_sum_100=$row['debet_sum_100'];
            $debet_cnt_100=$row['debet_cnt_100'];
            $debet_sum_off_100=$row['debet_sum_off_100'];
            $debet_cnt_off_100=$row['debet_cnt_off_100'];
            $debet_sum_w_100=$row['debet_sum_w_100'];
            $debet_cnt_w_100=$row['debet_cnt_w_100'];
            $debet_sum_ww_100=$row['debet_sum_ww_100'];
            $debet_cnt_ww_100=$row['debet_cnt_ww_100'];
            
        }
    }
    

    eval("\$footer_text_eval = \"$footer_text\";");
    echo $footer_text_eval;

    
//------------------------------- 3 month 500 grn
    
    $SQL = str_replace('%nmonth%','2',$SQL3);
    $SQL = str_replace('%sum%','500',$SQL);
    
//    echo $SQL;
    
    //throw new Exception(json_encode($SQL));
/*
    $debet_sum_100=0;
    $debet_cnt_100=0;
    $debet_sum_off_100=0;
    $debet_cnt_off_100=0;
    $debet_sum_w_100=0;
    $debet_cnt_w_100=0;
    $debet_sum_ww_100=0;
    $debet_cnt_ww_100=0;
*/    
    $cnt_month=3;
    $min_summ = 500;
    $det_id=2;
/*    
    $str_text = file_get_contents("html_templates/".$template_name."_str.htm");
    eval("\$str_text = \"$str_text\";");
    echo $str_text;
*/    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        $i=0;
        while ($row = pg_fetch_array($result)) {

           
            $debet_sum_100=$row['debet_sum_100'];
            $debet_cnt_100=$row['debet_cnt_100'];
            $debet_sum_off_100=$row['debet_sum_off_100'];
            $debet_cnt_off_100=$row['debet_cnt_off_100'];
            $debet_sum_w_100=$row['debet_sum_w_100'];
            $debet_cnt_w_100=$row['debet_cnt_w_100'];
            $debet_sum_ww_100=$row['debet_sum_ww_100'];
            $debet_cnt_ww_100=$row['debet_cnt_ww_100'];
            
        }
    }
    

    eval("\$footer_text_eval = \"$footer_text\";");
    echo $footer_text_eval;

//--------------------- 6 month 100 grn
    echo '</table>';
    echo '<br/>';
    echo " <table class ='count_table' width='100%' cellspacing='0' cellpadding='0'>";
    
    $SQL = str_replace('%nmonth%','5',$SQL6);
    $SQL = str_replace('%sum%','100',$SQL);
    
  //  echo $SQL;
    
    //throw new Exception(json_encode($SQL));
/*
    $debet_sum_100=0;
    $debet_cnt_100=0;
    $debet_sum_off_100=0;
    $debet_cnt_off_100=0;
    $debet_sum_w_100=0;
    $debet_cnt_w_100=0;
    $debet_sum_ww_100=0;
    $debet_cnt_ww_100=0;
*/    
    $cnt_month=6;
    $min_summ = 100;
    $det_id=3;
    
    $str_text = file_get_contents("html_templates/".$template_name."_str.htm");
    eval("\$str_text = \"$str_text\";");
    echo $str_text;
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        $i=0;
        while ($row = pg_fetch_array($result)) {

           
            $debet_sum_100=$row['debet_sum_100'];
            $debet_cnt_100=$row['debet_cnt_100'];
            $debet_sum_off_100=$row['debet_sum_off_100'];
            $debet_cnt_off_100=$row['debet_cnt_off_100'];
            $debet_sum_w_100=$row['debet_sum_w_100'];
            $debet_cnt_w_100=$row['debet_cnt_w_100'];
            $debet_sum_ww_100=$row['debet_sum_ww_100'];
            $debet_cnt_ww_100=$row['debet_cnt_ww_100'];
            
        }
    }
    

    eval("\$footer_text_eval = \"$footer_text\";");
    echo $footer_text_eval;

    echo '</table>';
        
}
//------------------------------------------------------------------------------


if ($oper=='debetor_cnt_3month_detail')
{
    $p_mmgg = sql_field_val('dt_b', 'mmgg');
    $mmgg1_str = trim($_POST['period_str']);
    $p_dt = sql_field_val('dt_rep', 'date');
    $p_dt_txt = sql_field_val('dt_rep', 'text'); 
    $p_id_region = sql_field_val('id_region', 'int');
    
    $p_str = sql_field_val('grid_params', 'int');
    
    //echo $p_str;
    
    $params_caption='';

    $where = '  ';
    
  $SQL = "select ''''||to_char( value_ident::date- '1 month'::interval , 'YYYY-MM-DD')||'''' as start_mmgg ,
        CASE WHEN (value_ident::date- '1 month'::interval) >= ($p_mmgg::date - '2 month'::interval )::date THEN 1 ELSE 0 END as old3,
        CASE WHEN (value_ident::date- '1 month'::interval) >= ($p_mmgg::date - '5 month'::interval )::date THEN 1 ELSE 0 END as old6
        from syi_sysvars_tbl where ident = 'dt_start';";
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);    
    
    if ($result) {
        $row = pg_fetch_array($result); 
        $start_mmgg=$row['start_mmgg'];
        $fold3 = $row['old3'];
        $fold6 = $row['old6'];
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
    
    
    
    $SQL_base_new = "
    select id_paccnt, book,code, abon, addr,town , note, kontrol_name, task_dt, 
    CASE WHEN dt > %sum% THEN dt ELSE 0 END as debet_sum_100, 
    CASE WHEN dt > %sum% and coalesce(action,0) = 1 THEN dt  END as debet_sum_off_100, 
    CASE WHEN dt > %sum% and coalesce(action,0) = 1 THEN to_char(dt_action, 'DD.MM.YYYY') END as debet_cnt_off_100 , 
    CASE WHEN dt > %sum% and coalesce(action,0) = 2 THEN dt  END as debet_sum_w_100, 
    CASE WHEN dt > %sum% and coalesce(action,0) = 2 THEN to_char(dt_action, 'DD.MM.YYYY')  END as debet_cnt_w_100  ,
    CASE WHEN dt > %sum% and dt_warning is not null THEN dt  END as debet_sum_ww_100, 
    CASE WHEN dt > %sum% and dt_warning is not null THEN to_char(dt_warning, 'DD.MM.YYYY') END as debet_cnt_ww_100  
    
from
(
  select ss.id_paccnt,dt_all,  
  (dt_all - coalesce(p1.pay,0)- coalesce(p2.pay,0) -  coalesce(bill_corr,0)) as dt , p1.pay,p2.pay, bill_corr,  sw.action, sw.dt_action, sw2.dt_warning,

   acc.book, acc.code, acc.note,    p.represent_name as kontrol_name,  
  ('0'||substring(acc.code FROM '[0-9]+'))::int as int_code,
  ('0'||substring(acc.book FROM '[0-9]+'))::int as int_book,
  st.task_dt , 
  (ab.last_name||' '||coalesce(ab.name,'')||' '||coalesce(ab.patron_name,''))::varchar as abon,
  adr.town, (adr.street||' '||
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
--   join acm_saldo_tbl as s on (s.id_paccnt = acc.id and s.mmgg = $p_mmgg and s.id_pref=10 and s.e_val >0 )        
   join prs_runner_paccnt as rp on (rp.id_paccnt = acc.id)
   join prs_runner_sectors as rs on (rp.id_sector = rs.id)
   join 
   (
    select id_paccnt, sum(b_dtval)-coalesce(sum(b_ktval),0) as dt_all
    from seb_saldo where mmgg = ($p_mmgg::date - '%nmonth% month'::interval)::date
    and id_pref = 10 
    group by id_paccnt 
    
     union        
    select sb.id_paccnt, sum(sb.value) as dt_all
    from acm_bill_tbl as sb 
    join clm_paccnt_tbl as c on (c.id = sb.id_paccnt)
    where sb.id_pref = 10 
    and sb.mmgg = '2017-09-01'::date and c.id_dep = 260
    and sb.mmgg_bill < ($p_mmgg::date - '%nmonth% month'::interval)::date
    and idk_doc = 1000   
    and not exists (select id_paccnt from seb_saldo as ss where ss.mmgg = ($p_mmgg::date - '%nmonth% month'::interval)::date and ss.id_pref = 10  and ss.id_paccnt = sb.id_paccnt  )
    group by sb.id_paccnt 
    
    order by id_paccnt
   ) as ss
   on (ss.id_paccnt = acc.id )
   left join prs_persons as p on (p.id = rs.id_kontrol)            
   left join
   (
    select id_paccnt, sum(value) as pay from acm_pay_tbl where 

    ( mmgg <= ($p_mmgg::date - '1 month'::interval)::date and 
      mmgg >= ($p_mmgg::date - '%nmonth% month'::interval)::date )
    and idk_doc <> 1000         
    and id_pref = 10 
    group by id_paccnt order by id_paccnt
   ) as p1 on (p1.id_paccnt = ss.id_paccnt )
   left join
   (
    select id_paccnt, sum(value) as pay from acm_pay_tbl where 
        mmgg >= $p_mmgg::date and id_pref = 10  and greatest(pay_date,reg_date) <= $p_dt::date
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
   		where csw.action  in (1,2) 
                and cswc.id_paccnt is null 
   	) as sw on (sw.id_paccnt = ss.id_paccnt)
   left join (
       select cst.id_paccnt, to_char(max(coalesce(cst.date_work,cst.date_print)), 'DD.MM.YYYY') as task_dt  
       from clm_tasks_tbl as cst 
       where cst.idk_work =1 and cst.task_state = 1 
       group by cst.id_paccnt 
   ) as st on (st.id_paccnt = ss.id_paccnt) 
   left join 
   	(
   	select csw.id_paccnt, csw.dt_action, csw.action, csw.dt_warning
   		from clm_switching_tbl as csw 
   		join (select id_paccnt, max(dt_action) as maxdt from clm_switching_tbl where action in (1,2) group by id_paccnt) as csdt 
   		on (csw.id_paccnt = csdt.id_paccnt and csw.dt_action = csdt.maxdt) 
   		left join clm_switching_tbl as cswc on (csw.id_paccnt = cswc.id_paccnt and cswc.dt_action >= csw.dt_action and ((cswc.action =3 and csw.action =1) or (cswc.action =4  and csw.action =2) ) ) 
   		where csw.action =2 and csw.dt_warning <$p_dt::date
                and cswc.id_paccnt is null 
   	) as sw2 on (sw2.id_paccnt = ss.id_paccnt)       
   left join    	
       (
          select id_paccnt,  -sum(value) as bill_corr 
           from acm_bill_tbl as bb where 
           bb.mmgg >= ($p_mmgg::date - '%nmonth% month'::interval)  and 
          (bb.mmgg_bill < ($p_mmgg::date - '%nmonth% month'::interval)  or  ( bb.reg_num <>'' and bb.value <0 and coalesce(bb.demand,0) = 0)) and
           bb.id_pref = 10 and bb.idk_doc=220
           group by id_paccnt 
           having sum(value) <0
           order by id_paccnt
       ) as sum_recalc
       on (sum_recalc.id_paccnt = ss.id_paccnt)       
   where (dt_all - coalesce(p1.pay,0)- coalesce(p2.pay,0) - coalesce(bill_corr,0) ) > %sum% 
   and dt_all >0 $where 
) as s 
order by int_book, int_code;";
    
    
    $SQL_base_old = "
    select id_paccnt, book,code, abon, addr,note,kontrol_name,town,task_dt,
    CASE WHEN dt > %sum% THEN dt ELSE 0 END as debet_sum_100, 
    CASE WHEN dt > %sum% and coalesce(action,0) = 1 THEN dt  END as debet_sum_off_100, 
    CASE WHEN dt > %sum% and coalesce(action,0) = 1 THEN to_char(dt_action, 'DD.MM.YYYY') END as debet_cnt_off_100 , 
    CASE WHEN dt > %sum% and coalesce(action,0) = 2 THEN dt  END as debet_sum_w_100, 
    CASE WHEN dt > %sum% and coalesce(action,0) = 2 THEN to_char(dt_action, 'DD.MM.YYYY')  END as debet_cnt_w_100  ,
    CASE WHEN dt > %sum% and dt_warning is not null THEN dt  END as debet_sum_ww_100, 
    CASE WHEN dt > %sum% and dt_warning is not null THEN to_char(dt_warning, 'DD.MM.YYYY') END as debet_cnt_ww_100  
    
from
(
  select ss.id_paccnt,dt_all,  
  (dt_all - coalesce(p1.pay,0)- coalesce(p2.pay,0) -  coalesce(bill_corr,0)) as dt , p1.pay,p2.pay, bill_corr,  sw.action, sw.dt_action, sw2.dt_warning,
   st.task_dt , 
   acc.book, acc.code, acc.note,   p.represent_name as kontrol_name,   
  ('0'||substring(acc.code FROM '[0-9]+'))::int as int_code,
  ('0'||substring(acc.book FROM '[0-9]+'))::int as int_book,
  (ab.last_name||' '||coalesce(ab.name,'')||' '||coalesce(ab.patron_name,''))::varchar as abon,
  adr.town, (adr.street||' '||
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
   join acm_saldo_tbl as s on (s.id_paccnt = acc.id and s.mmgg = $p_mmgg and s.id_pref=10 and s.e_val >0 )        
   join prs_runner_paccnt as rp on (rp.id_paccnt = acc.id)
   join prs_runner_sectors as rs on (rp.id_sector = rs.id)
   join 
   (
     select id_paccnt, sum(value) as dt_all
     from acm_bill_tbl where id_pref = 10 
      and mmgg = $start_mmgg::date 
      and mmgg_bill < ($p_mmgg::date - '%nmonth% month'::interval)::date
     group by id_paccnt order by id_paccnt
   ) as ss
   on (ss.id_paccnt = acc.id )
   left join prs_persons as p on (p.id = rs.id_kontrol)            
   left join
   (
    select id_paccnt, sum(value) as pay from acm_pay_tbl where 

    ( mmgg <= ($p_mmgg::date - '1 month'::interval)::date and 
      mmgg >= ($p_mmgg::date - '%nmonth% month'::interval)::date )

    and id_pref = 10 
    group by id_paccnt order by id_paccnt
   ) as p1 on (p1.id_paccnt = ss.id_paccnt )
   left join
   (
    select id_paccnt, sum(value) as pay from acm_pay_tbl where 
        mmgg >= $p_mmgg::date and id_pref = 10  and greatest(pay_date,reg_date) <= $p_dt::date
        group by id_paccnt order by id_paccnt
   ) as p2 on (p2.id_paccnt = ss.id_paccnt )
   left join 
   	(
   	select csw.id_paccnt, csw.dt_action, csw.action 
   		from clm_switching_tbl as csw 
   		join (select id_paccnt, max(dt_action) as maxdt from clm_switching_tbl where action in (1,2) group by id_paccnt) as csdt 
   		on (csw.id_paccnt = csdt.id_paccnt and csw.dt_action = csdt.maxdt) 
   		left join clm_switching_tbl as cswc on (csw.id_paccnt = cswc.id_paccnt and cswc.dt_action >= csw.dt_action and ((cswc.action =3 and csw.action =1) or (cswc.action =4  and csw.action =2) ) ) 
   		where csw.action  in (1,2) 
                and cswc.id_paccnt is null 
   	) as sw on (sw.id_paccnt = ss.id_paccnt)
   left join (
       select cst.id_paccnt, to_char(max(coalesce(cst.date_work,cst.date_print)), 'DD.MM.YYYY') as task_dt  
       from clm_tasks_tbl as cst 
       where cst.idk_work =1 and cst.task_state = 1 
       group by cst.id_paccnt 
   ) as st on (st.id_paccnt = ss.id_paccnt) 
   
   left join 
   	(
   	select csw.id_paccnt, csw.dt_action, csw.action, csw.dt_warning
   		from clm_switching_tbl as csw 
   		join (select id_paccnt, max(dt_action) as maxdt from clm_switching_tbl where action in (1,2) group by id_paccnt) as csdt 
   		on (csw.id_paccnt = csdt.id_paccnt and csw.dt_action = csdt.maxdt) 
   		left join clm_switching_tbl as cswc on (csw.id_paccnt = cswc.id_paccnt and cswc.dt_action >= csw.dt_action and ((cswc.action =3 and csw.action =1) or (cswc.action =4  and csw.action =2) ) ) 
   		where csw.action =2 and csw.dt_warning <$p_dt::date
                and cswc.id_paccnt is null 
   	) as sw2 on (sw2.id_paccnt = ss.id_paccnt)       
   left join    	
       (
          select id_paccnt,  -sum(value) as bill_corr 
           from acm_bill_tbl as bb where 
           bb.mmgg >= ($p_mmgg::date - '%nmonth% month'::interval)  and 
          (bb.mmgg_bill < ($p_mmgg::date - '%nmonth% month'::interval)  or  ( bb.reg_num <>'' and bb.value <0 and coalesce(bb.demand,0) = 0)) and
           bb.id_pref = 10 and bb.idk_doc=220
           group by id_paccnt 
           having sum(value) <0
           order by id_paccnt
       ) as sum_recalc
       on (sum_recalc.id_paccnt = ss.id_paccnt)       
   where (dt_all - coalesce(p1.pay,0)- coalesce(p2.pay,0) - coalesce(bill_corr,0) ) > %sum% 
   and dt_all >0 $where 
) as s 
order by int_book, int_code;";

    
  if ($fold3 ==1)
      $SQL3 = $SQL_base_old;
  else
      $SQL3 = $SQL_base_new;

  
  if ($fold6 ==1)
      $SQL6 = $SQL_base_old;
  else
      $SQL6 = $SQL_base_new;    
 
  //echo "<$p_str>";
  if($p_str==1)
  {
      $SQL = str_replace('%nmonth%','2',$SQL3);
      $SQL = str_replace('%sum%','100',$SQL);  
      $params_caption.=' Заборгованість >100 грн більше 3 місяців <br/>';
      $cnt_month = 3;
  }
  
  if($p_str==2)      
  {
      $SQL = str_replace('%nmonth%','2',$SQL3);
      $SQL = str_replace('%sum%','500',$SQL);  
      $params_caption.=' Заборгованість >500 грн більше 3 місяців <br/>';
      $cnt_month = 3;
  }
      
  if($p_str==3)    
  {
      $SQL = str_replace('%nmonth%','5',$SQL6);
      $SQL = str_replace('%sum%','100',$SQL);  
      $params_caption.=' Заборгованість >100 грн більше 6 місяців <br/>';
      $cnt_month = 6;
  }

  //echo $SQL;
  
  eval("\$header_text_eval = \"$header_text\";");
  echo_html( $header_text_eval);

   // throw new Exception(json_encode($SQL));
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        //throw new Exception("Try to save file $rows_count ....");    
        
        $nm=1;
        
        $debet_all=0;
        $debet_off_all=0;
        $debet_w_all=0;
        $debet_ww_all=0;

        $np=0;
        
        while ($row = pg_fetch_array($result)) {

                if($res_code=='310')
                    $addr = $row['addr'];
                else
                    $addr = $row['town'].' '.$row['addr'];
                
                $addr = htmlspecialchars($addr);
                
                $abon = htmlspecialchars($row['abon']);
                $note = htmlspecialchars($row['note']);
                $kontrol_name = htmlspecialchars($row['kontrol_name']);
                $book=$row['book'];
                $code=$row['code'];
                
                $debet_txt=number_format_ua($row['debet_sum_100'],2);
                $debet_off_txt=number_format_ua($row['debet_sum_off_100'],2);
                $debet_w_txt=number_format_ua($row['debet_sum_w_100'],2);
                $debet_ww_txt=number_format_ua($row['debet_sum_ww_100'],2);
                
                echo_html( "
                    <tr class='table_str'>
                     <td class='c_i'>{$nm}</td>   
                     <td class='c_t'>{$book}</td>
                     <td class='c_t'>{$code}</td>
                     <td class='c_t'>{$abon}</td>                     
                     <td class='c_t'>{$addr}</td>                                          
                     
                     <td class='c_n'>{$debet_txt}</td>
                     <td class='c_n'>{$debet_off_txt}</td>
                     <td class='c_t'>{$row['debet_cnt_off_100']}</td>
                     
                     <td class='c_n'>{$debet_w_txt}</td>
                     <td class='c_t'>{$row['debet_cnt_w_100']}</td>
                     
                     <td class='c_n'>{$debet_ww_txt}</td>
                     <td class='c_t'>{$row['debet_cnt_ww_100']}</td>
                     <td class='c_t'>{$kontrol_name}</td>
                     <td class='c_t'>{$row['task_dt']}</td>
                     <td class='c_t'>{$note}</td>
                    </tr>          
                  ");

                 $nm++;
                 
                 $debet_all+= $row['debet_sum_100'];
                 $debet_off_all+= $row['debet_sum_off_100'];
                 $debet_w_all+= $row['debet_sum_w_100'];
                 $debet_ww_all+= $row['debet_sum_ww_100'];

        }
    }

    $debet_txt=number_format_ua($debet_all,2);
    $debet_off_txt=number_format_ua($debet_off_all,2);
    $debet_w_txt=number_format_ua($debet_w_all,2);
    $debet_ww_txt=number_format_ua($debet_ww_all,2);

    eval("\$footer_text_eval = \"$footer_text\";");
    echo_html( $footer_text_eval);
    
}
//-----------------------------------------------------------------------------
if (($oper=='dt_inventar')||($oper=='dt_inventar_off')||($oper=='kt_inventar'))
{
    $p_mmgg = sql_field_val('dt_b', 'mmgg');
    
    list($year, $month, $day  ) = split('[/.-]', $p_mmgg);
    
    $year = trim($year,"'");
    if($month==12) 
    {$month =1;
     $year = $year+1;
    }
    else
    {
      $month =$month+1;  
    }
        
    $month_str = ukr_month((int)$month,1);

    $period_str = "на 01 $month_str $year р";

    $p_id_tar = sql_field_val('id_gtar', 'int');            
    $p_id_sector = sql_field_val('id_sector', 'int');
    $p_book = sql_field_val('book', 'string');
    $p_id_region = sql_field_val('id_region', 'int');
    
    $p_id_town = sql_field_val('addr_town', 'int');
    $p_id_tar = sql_field_val('id_gtar', 'int');    

   

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

   // if ((isset($_POST['person']))&&($_POST['person']!=''))
   //     $params_caption.= ' Оператор: '. trim($_POST['person']);
    
    if ((isset($_POST['addr_town_name']))&&($_POST['addr_town_name']!=''))
        $params_caption.= 'Населений пункт: '. trim($_POST['addr_town_name']);
    /*
    if ((isset($_POST['type_meter']))&&($_POST['type_meter']!=''))
        $params_caption .= ' Лічильник : '. trim($_POST['type_meter']);
    */
    
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
   
/*
   if ($p_id_person!='null')
   {
      $where.= " and  u1.id_person = $p_id_person  ";
   }
*/
   
    if ($p_id_town!='null')
    {
        //$where.= "and adr.id_town = $p_id_town ";
        if ($p_id_town==40854)
          $where.= " and ( adr.id_parent = $p_id_town or adr.id_parent = 40016 )";
        else
          $where.= " and ( adr.id_town = $p_id_town or adr.id_parent = $p_id_town )";

    }

    
    $sum_all    =0;
    $sum_0_3    =0;
    $sum_4_6    =0;
    $sum_7_9    =0;
    $sum_10_12    =0;
    $sum_1y    =0;
    $sum_2y    =0;
    $sum_3y    =0;
    
    $i=1;
    
    $np=0;
    $print_flag = 1;
    
    $SQL = "select rep_saldo_invent_fun($p_mmgg );";
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    
    
    if ($oper=='dt_inventar')
    {
        $table_name='rep_invent_dt_tbl';
        $kind_invent ='Дебiторська';
    }

    if ($oper=='dt_inventar_off')
    {
        $table_name='rep_invent_dt_tbl';
        $kind_invent ='Дебiторська відключених ';
        
        $where.= " and sw_dt is not null ";        
    }
    
    if($oper=='kt_inventar')
    {
        $table_name='rep_invent_kt_tbl';
        $kind_invent ='Кредиторська';
    }
    
    
    eval("\$header_text = \"$header_text\";");
    echo_html( $header_text);
    
    $SQL = "select c.book,c.code, i.id_paccnt, 
       i.summ_all, i.summ1_3, i.summ4_6, i.summ7_9, i.summ10_12, 
       i.summ0y, i.summ1y, i.summ2y, i.summ3y,
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
  rs.name as sector, sw_dt
from $table_name as i
join clm_paccnt_tbl c on (c.id=i.id_paccnt)
join clm_abon_tbl as ab on (ab.id = c.id_abon) 
join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class)
left join prs_runner_paccnt as rp on (rp.id_paccnt = c.id)
left join prs_runner_sectors as rs on (rp.id_sector = rs.id) 
left join (
       select csw.id_paccnt, to_char(coalesce(csw.dt_action,csw.dt_create), 'DD.MM.YYYY') as sw_dt 
       from clm_switching_tbl as csw 
        join cli_switch_action_tbl as a on (a.id = csw.action )
        join (select id_paccnt, max(dt_action) as maxdt from clm_switching_tbl where action in (1,2) group by id_paccnt) as csdt 
       on (csw.id_paccnt = csdt.id_paccnt and csw.dt_action = csdt.maxdt) 
       left join clm_switching_tbl as cswc on (csw.id_paccnt = cswc.id_paccnt and cswc.dt_action >= csw.dt_action and ((cswc.action =3 and csw.action =1) or (cswc.action =4  and csw.action =2) ) ) 
       where csw.action =1 and cswc.id_paccnt is null 
       order by csw.id_paccnt
  ) as sw on (sw.id_paccnt = c.id)    
where i.mmgg = $p_mmgg $where
order by  int_book,c.book,int_code,c.code ; ";

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
            
            $book=$row['book'].'/'.$row['code'];

            $sum_all    +=$row['summ_all'];
            $sum_0_3    +=$row['summ1_3'];
            $sum_4_6    +=$row['summ4_6'];
            $sum_7_9    +=$row['summ7_9'];
            $sum_10_12   +=$row['summ10_12'];
            $sum_1y    +=$row['summ1y'];
            $sum_2y    +=$row['summ2y'];
            $sum_3y    +=$row['summ3y'];
            
            
            $sum_all_txt=number_format_ua($row['summ_all'],2);
            $sum1_3_txt=number_format_ua($row['summ1_3'],2);
            $sum4_6_txt=number_format_ua($row['summ4_6'],2);
            $sum7_9_txt=number_format_ua($row['summ7_9'],2);
            $sum10_12_txt=number_format_ua($row['summ10_12'],2);
            
            $summ1y_txt=number_format_ua($row['summ1y'],2);
            $summ2y_txt=number_format_ua($row['summ2y'],2);
            $summ3y_txt=number_format_ua($row['summ3y'],2);

            echo_html( "
            <tr >
            <td>{$i}</td>            
            <td>$addr</td>
            <td>$abon</td>
            <td class='c_t'>$book</td>
            <td class='c_n'>{$sum_all_txt}</td>
            <td class='c_n'>{$sum1_3_txt}</td>
            <td class='c_n'>{$sum4_6_txt}</td>
            <td class='c_n'>{$sum7_9_txt}</td>
            <td class='c_n'>{$sum10_12_txt}</td>
            <td class='c_n'>{$summ1y_txt}</td>
            <td class='c_n'>{$summ2y_txt}</td>
            <td class='c_n'>{$summ3y_txt}</td> ",$print_flag); 

            if (($oper=='dt_inventar')||($oper=='dt_inventar_off'))
            {
              echo_html( " <td class='c_t'>{$row['sw_dt']}</td> ",$print_flag); 
            }
            
            echo_html( "</tr> ",$print_flag); 

            $i++;
            
            
        }
        
    }

    $sum_all_txt=number_format_ua($sum_all,2);
    $sum1_3_txt=number_format_ua($sum_0_3,2);
    $sum4_6_txt=number_format_ua($sum_4_6,2);
    $sum7_9_txt=number_format_ua($sum_7_9,2);
    $sum10_12_txt=number_format_ua($sum_10_12,2);
           
    $summ1y_txt=number_format_ua($sum_1y,2);
    $summ2y_txt=number_format_ua($sum_2y,2);
    $summ3y_txt=number_format_ua($sum_3y,2);
    
    eval("\$footer_text_eval = \"$footer_text\";");
    echo_html( $footer_text_eval,$print_flag);
   
    if ($print_flag==0) echo "</tbody> </table> ";
   
}

//----------------------------------------------------------------------------
if (($oper=='abon_meter_many')||($oper=='abon_meter_manypaccnt'))
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
    
    if ($oper=='abon_meter_many')    
    {
      $where.= " and exists (
      select m2.id_paccnt, count(*) as cnt from clm_meterpoint_tbl m2 where c.id = m2.id_paccnt
      group by id_paccnt
      having count(*) >1  ) ";
      
      $params_caption.= ' яким занесено більше одного лічильника.<BR/> ';
      
      if ($p_town_detal ==1)
      {
         $order ='town, int_book, book, int_code,code';
      }
      else
      {
         $order ='int_book, book, int_code,code';
      }
      
    }
    
    if ($oper=='abon_meter_manypaccnt')    
    {
      $where.= " and exists (
      select m2.num_meter, count( distinct m2.id_paccnt) as cnt from clm_meterpoint_tbl as m2 where m.num_meter = m2.num_meter
      group by m2.num_meter
      having count(distinct m2.id_paccnt) >1  ) ";
      
      $params_caption.= ' де лічильника з одним номером занесено декілька разів.<BR/> ';
      
      if ($p_town_detal ==1)
      {
         $order ='town, num_meter, int_book, book, int_code,code';
      }
      else
      {
         $order ='num_meter, int_book, book, int_code,code';
      }
      
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
   
   tar.sh_nm as tarif, ht.name as k_house, c.heat_area, c.n_subs, m.power,m.num_meter,m.coef_comp,m.carry, m.id_type_meter,
    im.name as type_meter,im.term_control, zzz.zones, 
    CASE WHEN im.phase = 1 THEN '1' WHEN im.phase = 2 THEN '3' END::varchar as phase,
    mp.name as meter_place,rs.name as sector, 
    CASE WHEN c.activ THEN 1 END as activ,
    CASE WHEN c.not_live THEN 1 END as not_live,
    ('0'||substring(c.code FROM '[0-9]+'))::int as int_code,
    ('0'||substring(c.book FROM '[0-9]+'))::int as int_book
from 
    clm_paccnt_tbl as c
    join clm_meterpoint_tbl as m on (m.id_paccnt = c.id) 
    join clm_abon_tbl as a on (a.id = c.id_abon) 
    
    join ( select z.id_meter, trim(sum(iz.nm||','),',')::varchar as zones
     from clm_meter_zone_tbl as z 
     join eqk_zone_tbl as iz on (iz.id = z.id_zone)
      group by z.id_meter order by z.id_meter
    ) as zzz on (zzz.id_meter = m.id)
    join prs_runner_paccnt as rp on (rp.id_paccnt = c.id)
    join prs_runner_sectors as rs on (rp.id_sector = rs.id)
    join eqi_meter_tbl as im on (im.id = m.id_type_meter)
    join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class)
    left join aqi_grptar_tbl as tar on (tar.id = c.id_gtar)
    left join cli_house_type_tbl as ht on (ht.id = c.idk_house)
    left join eqk_meter_places_tbl as mp on (mp.id = m.id_extra)
    where c.archive=0 
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
                <td COLSPAN=12 class = 'tab_head'>{$town_str}</td>
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
            </tr>  ",$print_flag);        
            $i++;
        }
        
    }
    
    eval("\$footer_text_eval = \"$footer_text\";");
    echo_html($footer_text_eval);

    //return;
    
}
//----------------------------------------------------------------------------
if ($oper=='warning_del')
{
    $p_mmgg = sql_field_val('dt_b', 'mmgg');
    $mmgg1_str = trim($_POST['period_str']);
    
    $p_id_sector = sql_field_val('id_sector', 'int');
    $p_id_region = sql_field_val('id_region', 'int');
    $p_book = sql_field_val('book', 'string');
    
    $print_flag=1;
    
    $params_caption = 'Видалені попередження. ';
    if ((isset($_POST['sector']))&&($_POST['sector']!=''))
        $params_caption.= ' Дільниця: '. trim($_POST['sector']);
    
    $where = ' ';
    
    if ($p_id_sector!='null') {
        $where.= " and r.id_sector = $p_id_sector ";
    }
     
    if ($p_id_region!='null')
    {
        $where.= " and rs.id_region =  $p_id_region ";
        
        $SQL_H="SELECT name from cli_region_tbl where id = $p_id_region ;";
    
        $result = pg_query($Link, $SQL_H) or die("SQL Error: " . pg_last_error($Link) . $SQL_H);
        $row = pg_fetch_array($result);
        
        $params_caption.= $row['name'];        
    }    
    
    if ($p_book!='null')
    {
        $where.= " and acc.book = $p_book ";
        $params_caption .= " книга : $p_book ";
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
where s.action=2 and s.dt_action is null $where
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
            
            
            $id_del=$row['id'];
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
            
            $SQL_del = "delete from clm_switching_tbl where id = $id_del ;";
            $resultd = pg_query($Link, $SQL_del) or die("SQL Error: " . pg_last_error($Link) . $SQL_del);
            
        }
        
    }

    $sum_warning_txt = number_format_ua ($sum_saldo, 2);
    
    eval("\$footer_text_eval = \"$footer_text\";");
    echo_html( $footer_text_eval,$print_flag);
    
    
    echo '</tbody> </table> ';
    //return;
    
}
//-----------------------------------------------
if ($oper=='zon3_abon')
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

        
    $SQL = " select ssss.*,
    acc.book, acc.code, 
    adr.town, adr.street, address_print(acc.addr) as house,
    (c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon,
    ('0'||substring(acc.code FROM '[0-9]+'))::int as int_code,
    ('0'||substring(acc.book FROM '[0-9]+'))::int as int_book,
    tt.name 
    from
    (
    select ident,id_paccnt,
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
    from aqm_tarif_tbl as t left join 
    (
      select id_tarif, b.id_paccnt,
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
     group by id_tarif, b.id_paccnt order by id_tarif, b.id_paccnt
    ) as ss 
    on (ss.id_tarif = t.id)
    ) as sss
    group by ident, id_paccnt
    ) as ssss
    join clm_paccnt_tbl as acc on (acc.id = ssss.id_paccnt)
    join clm_abon_tbl as c on (c.id = acc.id_abon) 
    join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class)
    join rep_tarif_names_tbl as tt on (tt.ident = ssss.ident )
    where tt.str_code is not null and tt.grp_code = 2 
    order by str_code, tt.ident, int_book, int_code ;";
        
/*        
    $SQL = " select ssss.*, tt.name , tt.grp_code, tt.str_code ,
    acc.book, acc.code, 
    adr.town, adr.street, address_print(acc.addr) as house,
    (c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon,
    ('0'||substring(acc.code FROM '[0-9]+'))::int as int_code,
    ('0'||substring(acc.book FROM '[0-9]+'))::int as int_book
     from
    (
    select ident,id_paccnt,
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
      select s.id_tarif, b.id_paccnt,
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
     --group by s.id_tarif order by id_tarif
     group by s.id_tarif, b.id_paccnt order by s.id_tarif, b.id_paccnt   
    ) as ss 
    on (ss.id_tarif = t.id)
    ) as sss
    group by ident, id_paccnt
    ) as ssss
    join rep_tarif_names_tbl as tt on (tt.ident = ssss.ident )
    join clm_paccnt_tbl as acc on (acc.id = ssss.id_paccnt)
    join clm_abon_tbl as c on (c.id = acc.id_abon) 
    join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class)
    where grp_code = 1 
    and str_code is not null                
    union all
     select * from ( 
        select ssss.*, tt.name , tt.grp_code, tt.str_code,
        acc.book, acc.code, 
        adr.town, adr.street, address_print(acc.addr) as house,
        (c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon,
        ('0'||substring(acc.code FROM '[0-9]+'))::int as int_code,
        ('0'||substring(acc.book FROM '[0-9]+'))::int as int_book
        from
        (
        select ident,id_paccnt,
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
          select s.id_tarif, b.id_paccnt,
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
         --group by s.id_tarif order by s.id_tarif
         group by s.id_tarif, b.id_paccnt order by s.id_tarif, b.id_paccnt
        ) as ss 
        on (ss.id_tarif = t.id)
        ) as sss
        group by ident, id_paccnt
        ) as ssss
        join rep_tarif_names_tbl as tt on (tt.ident = ssss.ident )        
        join clm_paccnt_tbl as acc on (acc.id = ssss.id_paccnt)
        join clm_abon_tbl as c on (c.id = acc.id_abon) 
        join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class)
        where grp_code = 2 and str_code is not null        
   ) as ssssss
   order by grp_code, str_code, ident, int_book, int_code;  ";        
*/        
    }
    else
    {        
    
    $SQL = " select ssss.*,
    acc.book, acc.code, 
    adr.town, adr.street, address_print(acc.addr) as house,
    (c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon,
    ('0'||substring(acc.code FROM '[0-9]+'))::int as int_code,
    ('0'||substring(acc.book FROM '[0-9]+'))::int as int_book,
    tt.name 
    from
    (
    select ident,id_paccnt,
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
      select id_tarif, b.id_paccnt,
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
     group by id_tarif, b.id_paccnt order by id_tarif, b.id_paccnt
    ) as ss 
    on (ss.id_tarif = t.id)
    ) as sss
    group by ident, id_paccnt
    ) as ssss
    join clm_paccnt_tbl as acc on (acc.id = ssss.id_paccnt)
    join clm_abon_tbl as c on (c.id = acc.id_abon) 
    join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class)
    join rep_tarif_names_tbl as tt on (tt.ident = ssss.ident )
    where tt.str_code is not null and tt.grp_code = 1 
    order by str_code, tt.ident, int_book, int_code ;";
    };
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
            $abon = htmlspecialchars($row['abon']);
            
            if($res_code!='310')
              $addr = htmlspecialchars($row['town'].' '.$row['street'].' '.$row['house']);
            else
              $addr = htmlspecialchars($row['street'].' '.$row['house']);
            
            
            $book=$row['book'].'/'.$row['code'];
            
            
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
            <td class='c_t'>{$book}</td>
            <td class='c_t'>{$abon}</td>
            <td class='c_t'>{$addr}</td>
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
       <td>&nbsp;</td>
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
     
    
    eval("\$footer_text_eval = \"$footer_text\";");
    echo $footer_text_eval;

    //return;
    
}

//------------------------------------------------------------------------------
if ($oper=='zon2_abon')
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
        
    $SQL = " select ssss.*,
    acc.book, acc.code, 
    adr.town, adr.street, address_print(acc.addr) as house,
    (c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon,
    ('0'||substring(acc.code FROM '[0-9]+'))::int as int_code,
    ('0'||substring(acc.book FROM '[0-9]+'))::int as int_book,
    tt.name 
    from
    (
    select ident,id_paccnt,
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
    from aqm_tarif_tbl as t left join 
    (
      select id_tarif, b.id_paccnt,
		   sum(CASE WHEN id_zone in (9)  THEN s.demand END ) as demand1,
                   sum(CASE WHEN id_zone in (9)  THEN s.summ END) as summ1,
		   sum(CASE WHEN id_zone in (10)  THEN s.demand END ) as demand2,
                   sum(CASE WHEN id_zone in (10)  THEN s.summ END) as summ2
    from acm_summ_tbl as s
    join acm_bill_tbl as b on (b.id_doc=s.id_doc)    
    where b.mmgg = $p_mmgg and id_zone in (9,10) and b.id_pref = 10
    $where
     group by id_tarif, b.id_paccnt order by id_tarif, b.id_paccnt
    ) as ss 
    on (ss.id_tarif = t.id)
    ) as sss
    group by ident, id_paccnt
    ) as ssss
    join clm_paccnt_tbl as acc on (acc.id = ssss.id_paccnt)
    join clm_abon_tbl as c on (c.id = acc.id_abon) 
    join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class)
    join rep_tarif_names_tbl as tt on (tt.ident = ssss.ident )
    where tt.str_code is not null and tt.grp_code = 2 
    order by str_code, tt.ident, int_book, int_code ;";
        
        
/*
    $SQL = " select ssss.*, tt.name , tt.grp_code, tt.str_code ,
    acc.book, acc.code, 
    adr.town, adr.street, address_print(acc.addr) as house,
    (c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon,
    ('0'||substring(acc.code FROM '[0-9]+'))::int as int_code,
    ('0'||substring(acc.book FROM '[0-9]+'))::int as int_book
    from
    (
    select ident,id_paccnt,
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
      select s.id_tarif, b.id_paccnt,
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
     --group by s.id_tarif order by id_tarif
     group by s.id_tarif, b.id_paccnt order by s.id_tarif, b.id_paccnt   
    ) as ss 
    on (ss.id_tarif = t.id)
    ) as sss
    group by ident, id_paccnt
    ) as ssss
    join rep_tarif_names_tbl as tt on (tt.ident = ssss.ident )
    join clm_paccnt_tbl as acc on (acc.id = ssss.id_paccnt)
    join clm_abon_tbl as c on (c.id = acc.id_abon) 
    join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class)
    where grp_code = 1 
    and str_code is not null                
    union all
     select * from ( 
        select ssss.*, tt.name , tt.grp_code, tt.str_code,
        acc.book, acc.code, 
        adr.town, adr.street, address_print(acc.addr) as house,
        (c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon,
        ('0'||substring(acc.code FROM '[0-9]+'))::int as int_code,
        ('0'||substring(acc.book FROM '[0-9]+'))::int as int_book
        
        from
        (
        select ident,id_paccnt,
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
          select s.id_tarif, b.id_paccnt,
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
         --group by s.id_tarif order by s.id_tarif
         group by s.id_tarif, b.id_paccnt order by s.id_tarif, b.id_paccnt
        ) as ss 
        on (ss.id_tarif = t.id)
        ) as sss
        group by ident, id_paccnt
        ) as ssss
        join rep_tarif_names_tbl as tt on (tt.ident = ssss.ident )        
        join clm_paccnt_tbl as acc on (acc.id = ssss.id_paccnt)
        join clm_abon_tbl as c on (c.id = acc.id_abon) 
        join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class)
        where grp_code = 2 and str_code is not null        
   ) as ssssss
   order by grp_code, str_code, ident, int_book, int_code;  ";        
*/        
    }
    else
    {        
    
    $SQL = " select ssss.*,
    acc.book, acc.code, 
    adr.town, adr.street, address_print(acc.addr) as house,
    (c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon,
    ('0'||substring(acc.code FROM '[0-9]+'))::int as int_code,
    ('0'||substring(acc.book FROM '[0-9]+'))::int as int_book,
    tt.name 
    from
    (
    select ident,id_paccnt,
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
    from aqm_tarif_tbl as t left join 
    (
      select id_tarif, b.id_paccnt,
		   sum(CASE WHEN id_zone in (9)  THEN s.demand END ) as demand1,
                   sum(CASE WHEN id_zone in (9)  THEN s.summ END) as summ1,
		   sum(CASE WHEN id_zone in (10)  THEN s.demand END ) as demand2,
                   sum(CASE WHEN id_zone in (10)  THEN s.summ END) as summ2
    from acm_summ_tbl as s
    join acm_bill_tbl as b on (b.id_doc=s.id_doc)    
    where b.mmgg = $p_mmgg and id_zone in (9,10) and b.id_pref = 10
    $where
     group by id_tarif, b.id_paccnt order by id_tarif, b.id_paccnt
    ) as ss 
    on (ss.id_tarif = t.id)
    ) as sss
    group by ident, id_paccnt
    ) as ssss
    join clm_paccnt_tbl as acc on (acc.id = ssss.id_paccnt)
    join clm_abon_tbl as c on (c.id = acc.id_abon) 
    join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class)
    join rep_tarif_names_tbl as tt on (tt.ident = ssss.ident )
    where tt.str_code is not null and tt.grp_code = 1 
    order by str_code, tt.ident, int_book, int_code ;";
    };
    

    //echo $SQL;
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

            $abon = htmlspecialchars($row['abon']);
            if($res_code!='310')
              $addr = htmlspecialchars($row['town'].' '.$row['street'].' '.$row['house']);
            else
              $addr = htmlspecialchars($row['street'].' '.$row['house']);
            
            $book=$row['book'].'/'.$row['code'];
            
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
            <td class='c_t'>{$book}</td>
            <td class='c_t'>{$abon}</td>
            <td class='c_t'>{$addr}</td>
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
       <td>&nbsp;</td>
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
    
    
    eval("\$footer_text_eval = \"$footer_text\";");
    echo $footer_text_eval;

    //return;
    
}
//------------------------------------------------------------------------------

if ($oper=='avg_demand') 
{
    $p_dtb = sql_field_val('dt_b', 'mmgg');
    $p_dte = sql_field_val('dt_e', 'mmgg');

    $p_dtb_str = trim($_POST['dt_b']);
    $p_dte_str = trim($_POST['dt_e']);

    $period_str = " з $p_dtb_str до $p_dte_str";
    
    
    $p_book = sql_field_val('book', 'string');
    $p_id_town = sql_field_val('addr_town', 'int');
    $p_id_tar = sql_field_val('id_gtar', 'int');    
    $p_id_type_meter = sql_field_val('id_type_meter', 'int');    
    $p_id_type_meter_array = sql_field_val('id_type_meter_array', 'array');    
    $p_id_region = sql_field_val('id_region', 'int');
    $p_id_dod = sql_field_val('id_cntrl', 'int');
    $p_name_dod = sql_field_val('cntrl', 'string');
    
    $p_id_sector = sql_field_val('id_sector', 'int');
    

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
    
    if ($p_id_sector!='null')
    {
       $where.= "and exists (select id from prs_runner_paccnt as rp 
          where rp.id_paccnt = c.id and rp.id_sector = $p_id_sector ) ";
        
    }
        
    eval("\$header_text = \"$header_text\";");
    
    echo_html( $header_text);
    
   
    
    $SQL = "select c.id,c.code,c.book, c.note, ap.name as add_param, 
    adr.town, adr.street,     
    address_print(c.addr) as house,
   (a.last_name||' '||coalesce(substr(a.name,1,1),'')||'.'||coalesce(substr(a.patron_name,1,1),''))||'.'::varchar as abon,
   m.power,m.num_meter,m.coef_comp,    m.type_meter, meter_place, 
    ('0'||substring(c.code FROM '[0-9]+'))::int as int_code,
    ('0'||substring(c.book FROM '[0-9]+'))::int as int_book,
    avg.avg_dem
from 
    clm_paccnt_h as c
    join (select id, max(dt_b) as dt_b from clm_paccnt_h  where 
     ((dt_b < ($p_dte::date+'1 month'::interval) and dt_e is null)
     or 
     tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_dtb::timestamp::abstime,($p_dte::date+'1 month - 1 day'::interval)::timestamp::abstime))
     ) and archive=0 
    group by id order by id) as c2 
    on (c.id = c2.id and c.dt_b = c2.dt_b)
    join clm_abon_tbl as a on (a.id = c.id_abon) 
    join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class)
    left join cli_addparam_tbl as ap on (ap.id = c.id_cntrl)
    
    join ( select m.*, im.name as type_meter, im.phase , mp.name as meter_place from
      clm_meterpoint_tbl as m 
      join eqi_meter_tbl as im on (im.id = m.id_type_meter)
      left join eqk_meter_places_tbl as mp on (mp.id = m.id_extra)
      order by m.id_paccnt
    ) as m on (m.id_paccnt = c.id) 
    
    join (
        select b.id_paccnt,  count( distinct date_trunc('month',b.mmgg_bill)) as cnt_month, sum(bs.demand) as dem ,
        round(sum(bs.demand)/count( distinct date_trunc('month',b.mmgg_bill))) as avg_dem
        from acm_bill_tbl as b 
        join acm_summ_tbl as bs on (bs.id_doc = b.id_doc)
        where b.id_pref=10 and b.mmgg_bill >= $p_dtb and b.mmgg_bill <= $p_dte
        and b.idk_doc in ( 200,220) 
        group by  b.id_paccnt
        having count( distinct date_trunc('month',b.mmgg_bill)) >=1
    ) as avg on (avg.id_paccnt = c.id )

where c.archive=0 $where 
order by int_book, book, int_code, code ;";

  
    $cur_town='';
    $summ_dem = 0;
    
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
            
            /*
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
            */
            $abon = htmlspecialchars($row['abon']);
            $note = htmlspecialchars($row['note']);
            //$add_param = htmlspecialchars($row['add_param']);
            $town = htmlspecialchars($row['town']);
            $addr = htmlspecialchars($row['street'].' '.$row['house']);
            $book=$row['book'].'/'.$row['code'];
            
            $avg_demand_txt = number_format_ua ($row['avg_dem'],0);  
           
            // <tr >
            echo_html( " <tr>
            <td>{$i}</td>
            <td>{$town}</td>
            <td>{$addr}</td>
            <td class='c_t'>{$book}</td>
            <td>{$abon}</td>
            <td>{$row['type_meter']}</td>
            <td>{$row['meter_place']}</td>
            <td class='c_i'>{$avg_demand_txt}</td>
            </tr>
            ",$print_flag);
            
            $summ_dem+=$row['avg_dem'];

            $i++;
        }
        
    }
    
    $summ_dem_txt = number_format_ua ($summ_dem,0);      
    
    eval("\$footer_text_eval = \"$footer_text\";");
    echo_html( $footer_text_eval);

    //return;
    
}
//----------------------------------------------------------------------------
if ($oper=='task_print')
{
    
    $p_mmgg = sql_field_val('dt_b', 'mmgg');
    $mmgg1_str = sql_field_val('period_str','str');

    $p_id_town = sql_field_val('addr_town', 'int');
    $p_id_sector = sql_field_val('id_sector', 'int');
    $p_book = sql_field_val('book', 'string');
    $p_id_warning = sql_field_val('id_warning', 'int');
    $p_dt = sql_field_val('dt_rep', 'date');
    $p_id_region = sql_field_val('id_region', 'int');
    $p_id_person = sql_field_val('id_person', 'int');
    $p_idk_work  = sql_field_val('idk_work', 'int');
    //$p_value = sql_field_val('sum_value', 'numeric');
    
    
    $where = 'where';

    if ($p_idk_work!='null')
    {
        if ($where!='where') $where.= " and ";
        $where.= " s.idk_work = $p_idk_work ";
    }
    
    if ($p_id_town!='null')
    {
        if ($where!='where') $where.= " and ";
        $where.= " adr.id_town = $p_id_town ";
    }
    
    if ($p_book!='null')
    {
        if ($where!='where') $where.= " and ";
        $where.= "  acc.book = $p_book ";
    }

    if ($p_id_sector!='null') {
        if ($where!='where') $where.= " and ";
        $where.= " r.id_sector = $p_id_sector ";
    }

    if ($p_id_person!='null')
    {
         if ($where!='where') $where.= " and ";        
         $where.= " u1.id_person = $p_id_person ";
    }
    
    
    $where1 = '';
    $where2 = '';
    if ($p_id_warning!='null')
    {
        if ($where!='where') $where.= " and ";
        $where.= " s.id = $p_id_warning ";
        
        $SQL="SELECT * from clm_tasks_tbl where id = $p_id_warning ;";
    
        $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
        $row = pg_fetch_array($result);
        
        $p_dt= "'".$row['date_print']."'";
        $id_paccnt = $row['id_paccnt'];
        $where1 = " where id_paccnt = $id_paccnt ";
        $where2 = " where i.id_paccnt = $id_paccnt ";
        
    }

    if ($p_id_region!='null')
    {
        if ($where!='where') $where.= " and ";
        $where.= " rs.id_region =  $p_id_region ";

    }
    
    if ($p_dt!='null')
    {
        if ($where!='where') $where.= " and ";
        $where.= " s.date_print = $p_dt  ";
    }
    else
    {
        if ($p_mmgg!='null')
        {
            if ($where!='where') $where.= " and ";
            $where.= " s.mmgg = $p_mmgg ";
        }
        
    }
    
    if ($where=='where') $where= " ";
/*
    if ($p_value!='null')
    {
        $where.= " and s.sum_warning >= $p_value ";
    }
*/    
/*    
    $SQL = "select fun_mmgg() as mmgg_current ;";
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    $row = pg_fetch_array($result);
    $mmgg_current = "'{$row['mmgg_current']}'";
*/    
  
    
    $SQL = "select * from ( select s.*, acc.book, acc.code, 
    adr.town, adr.street, address_print(acc.addr) as house, rs.name as sector,
   (c.last_name||' '||coalesce(c.name,'')||'.'||coalesce(c.patron_name,''))||'.'::varchar as abon,
    to_char(m.dt_b, 'DD.MM.YYYY') as dt_b_str,
    to_char(m.dt_control, 'DD.MM.YYYY') as dt_control_str,
    to_char(zzz.dat_ind, 'DD.MM.YYYY') as dat_ind_str,
     m.num_meter, m.type_meter, zzz.indic_str, pp.plombs,
    sw.sw_dt, tr.name as reason, ta.name as abn_state, ti.name as task_kind,
    (coalesce(c.home_phone||'; ','')|| coalesce(c.mob_phone,'') ) as phone,
   ('0'||substring(acc.code FROM '[0-9]+'))::int as int_code,
   ('0'||substring(acc.book FROM '[0-9]+'))::int as int_book
from clm_tasks_tbl as s
join clm_paccnt_tbl as acc on (acc.id = s.id_paccnt)
join clm_abon_tbl as c on (c.id = acc.id_abon) 
join prs_runner_paccnt as r on (r.id_paccnt = acc.id)            
join prs_runner_sectors as rs on (rs.id = r.id_sector)    
join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class)
left join syi_user as u1 on (u1.id = s.id_person)        
left join cli_tasks_tbl as ti on (ti.id = s.idk_work)    
left join cli_tasks_reason_tbl as tr on (tr.id = s.idk_reason)    
left join cli_tasks_abn_state_tbl as ta on (ta.id = s.idk_abn_state)    
left join ( select m.id_paccnt, m.num_meter, m.dt_control, im.name as type_meter,
     ( select min(mm.dt_b) from clm_meterpoint_h as mm where mm.id = m.id and mm.num_meter = m.num_meter) as dt_b
      from  clm_meterpoint_h as m 
      join (select id, max(dt_b) as dt_b from clm_meterpoint_h  where dt_b <= $p_dt and coalesce(dt_e,$p_dt) >=$p_dt group by id order by id) as m2 
       on (m.id = m2.id and m.dt_b = m2.dt_b)
      join eqi_meter_tbl as im on (im.id = m.id_type_meter)
      $where1
      order by m.id_paccnt
    ) as m on (m.id_paccnt = acc.id) 
left join ( select id_paccnt, dat_ind, trim(sum(indic||','),',')::varchar as indic_str from 
     (
      select i.id_paccnt, i.id_zone, i.dat_ind, CASE WHEN z.id = 0 THEN (i.value::int)::varchar ELSE z.nm||':'||((i.value::int)::varchar) END::varchar as indic
      from acm_indication_tbl as i 
      join (select id_paccnt, max(dat_ind) as max_dat from acm_indication_tbl 
      where dat_ind<=$p_dt 
      group by id_paccnt
      ) as mi
      on (i.id_paccnt = mi.id_paccnt and i.dat_ind = mi.max_dat)
      join eqk_zone_tbl as z on (z.id = i.id_zone)
      $where2
       order by i.id_zone
     ) as ss
    group by id_paccnt, dat_ind
    ) as zzz on (zzz.id_paccnt = acc.id)    
left join 
    (
     select id_paccnt, trim(sum(plomb_num||','),',')::varchar as plombs 
     from clm_plomb_tbl where coalesce(id_plomb_owner,0) <> 3 and dt_off is null
     group by id_paccnt    
    ) as pp  on (pp.id_paccnt = acc.id)     
  left join (
       select csw.id_paccnt, to_char(coalesce(csw.dt_action,csw.dt_create), 'DD.MM.YYYY') as sw_dt , csw.action
       from clm_switching_tbl as csw 
       join (select id_paccnt, max(dt_action) as maxdt from clm_switching_tbl where action in (1,2) group by id_paccnt) as csdt 
       on (csw.id_paccnt = csdt.id_paccnt and csw.dt_action = csdt.maxdt) 
       left join clm_switching_tbl as cswc on (csw.id_paccnt = cswc.id_paccnt and cswc.dt_action >= csw.dt_action and ((cswc.action =3 and csw.action =1) or (cswc.action =4  and csw.action =2) ) ) 
       where csw.action =2 and cswc.id_paccnt is null 
       order by csw.id_paccnt
  ) as sw on (sw.id_paccnt = acc.id)    
$where
) as ss     
order by int_book, int_code;";
    
   // echo $SQL;
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        $i=0;
        while ($row = pg_fetch_array($result)) {
        
           $doc_num=$row['task_num'];
           $bookcode=$row['book'].'/'.$row['code'].'&nbsp;&nbsp;&nbsp;&nbsp;'.$row['sector'] ; 

           if($res_code=='310')
                $addr =$row['street'].' '.$row['house'];
           else
                $addr = $row['town'].' '.$row['street'].' '.$row['house'];

           $abn=htmlspecialchars($row['abon']);
           $abn_phone=htmlspecialchars($row['phone']);
           $note=htmlspecialchars($row['note']);
    
           $meter_num=$row['num_meter'];
           $meter_type=htmlspecialchars($row['type_meter']);

           $last_indic=$row['indic_str'];
           $last_indic_date=$row['dat_ind_str'];
    
           $plomb_list=htmlspecialchars($row['plombs']);
           $meter_check_date=$row['dt_control_str'];
           $meter_change_date=$row['dt_b_str'];

           if ($row['idk_reason']==1)
            $reason_txt=$row['reason'].' '.number_format_ua ($row['sum_warning'],2).' грн.' ;
           else
            $reason_txt=$row['reason'];

           if ($row['idk_abn_state']==1)
            $abon_state=$row['abn_state'].' '.$row['sw_dt'] ;
           else
            $abon_state=$row['abn_state'];
           
           $task_kind=$row['task_kind'];
           
           $executor=$name_executor;
           

           if (($i==1)||($i==2))
           {
              echo '<HR style="margin-bottom: 5px; margin-top: 5px" SIZE="1" WIDTH="100%" NOSHADE /> ';               
           }
           if ($i==3) $i=0; 
               
           eval("\$header_text_eval = \"$header_text\";");
           echo $header_text_eval;
           
           
           eval("\$footer_text_eval = \"$footer_text\";");
           echo $footer_text_eval;

           $i++;
            
        }   
    }
    
}
//----------------------------------------------------------------------------
if ($oper=='task_list')
{
    $p_mmgg = sql_field_val('dt_b', 'mmgg');    
    $p_mode = sql_field_val('list_mode', 'int');    
    
    $period_str = trim($_POST['period_str']);
    //разбор и обработка строки параметров jqgrid
    $qWhere='';
    if (isset($_POST['grid_params'])) {
          $grid_params_json = $_POST['grid_params'];
          $json_str = stripslashes($grid_params_json); 
          $grid_params = json_decode($json_str,true);
          
          $fildsArray =DbGetFieldsArray($Link,'clm_tasks_tbl');
          $fildsArray['book'] = array('f_name' => 'book', 'f_type' => 'char');
          $fildsArray['code'] = array('f_name' => 'code', 'f_type' => 'char');
          $fildsArray['addr'] = array('f_name' => 'addr', 'f_type' => 'character varying');
          $fildsArray['town'] = array('f_name' => 'town', 'f_type' => 'character varying');
          $fildsArray['abon'] = array('f_name' => 'abon', 'f_type' => 'character varying');
          $fildsArray['sector'] = array('f_name' => 'sector', 'f_type' => 'character varying');
          $fildsArray['user_name'] = array('f_name'=>'user_name','f_type'=>'character varying');
          
          $qWhere= DbBuildWhere($grid_params,$fildsArray);

    }

    $params_caption = '';
    if ($p_mode == 0) {
        if ($qWhere != '')
            $qWhere = $qWhere . ' and ';
        else
            $qWhere = ' where ';

        $qWhere = $qWhere . " work_period = $p_mmgg::date ";

        $order = 'date_print, int_book, book ,int_code ,code ';
    }

    $i=1;
    
    eval("\$header_text = \"$header_text\";");
    echo_html ($header_text);
    
    $SQL = "select * from (
 select n.*, acc.book, acc.code,  
 adr.street, adr.town,
   (coalesce('буд.'||(acc.addr).house||'','')||
		coalesce('/'||(acc.addr).slash||' ','')||
			coalesce(' корп.'||(acc.addr).korp||'','')|| 
				coalesce(', кв. '||(acc.addr).flat,'')||
					coalesce('/'||(acc.addr).f_slash||' ',''))::varchar as addr, 
(c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon,
('0'||substring(acc.code FROM '[0-9]+'))::int as int_code,
('0'||substring(acc.book FROM '[0-9]+'))::int as int_book,
to_char(n.date_print, 'DD.MM.YYYY') as date_print_str,
to_char(n.date_work, 'DD.MM.YYYY') as date_work_str,    
to_char(n.work_period, 'DD.MM.YYYY') as mmgg_str,        
to_char(n.dt_input, 'DD.MM.YYYY HH24:MI') as dt_txt,
rp.id_sector, coalesce(rs.name,'')::varchar as sector, u1.name as user_name ,
tr.name as reason, ta.name as abn_state, ti.name as task_kind, ts.name as task_state
from clm_tasks_tbl  as n 
join clm_paccnt_tbl as acc on (acc.id = n.id_paccnt)
join clm_abon_tbl as c on (c.id = acc.id_abon) 
join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class) 

left join cli_tasks_reason_tbl as tr on (tr.id = n.idk_reason)    
left join cli_tasks_abn_state_tbl as ta on (ta.id = n.idk_abn_state)    
left join cli_tasks_tbl as ti on (ti.id = n.idk_work)
left join cli_tasks_state_tbl as ts on (ts.id = n.task_state)
    
left join prs_runner_paccnt as rp on (rp.id_paccnt = acc.id)
left join prs_runner_sectors as rs on (rs.id = rp.id_sector)
left join syi_user as u1 on (u1.id = n.id_person)    
    ) as ss
  $qWhere Order by $order ";    
    
    
   // throw new Exception(json_encode($SQL));
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        $i=1;
        while ($row = pg_fetch_array($result)) {

            $code = htmlspecialchars($row['book'].'/'.$row['code']);
            $abon = htmlspecialchars($row['abon']);

            if($res_code=='310')
                $addr =$row['street'].' '.$row['addr'];
            else
                $addr = $row['town'].' '.$row['street'].' '.$row['addr'];

            $sector = htmlspecialchars($row['sector']);
            
            $name_user = htmlspecialchars($row['user_name']);
            $comment = htmlspecialchars($row['note']);
            
            $sum_txt=number_format_ua($row['sum_warning'],2);
            
            echo_html( "
            <tr >
            <td>{$i}</td>            
            <td class='c_t'>{$code}</td>
            <td class='c_t'>$abon</td>
            <td class='c_t'>$addr</td>
            <td class='c_t'>$sector</td>
            <td class='c_t'>{$row['task_kind']}</td>
            <td class='c_t'>{$row['task_num']}</td>
            <td class='c_t'>{$row['date_print_str']}</td>
            <td class='c_t'>{$row['date_work_str']}</td>
            <td class='c_t'>{$row['reason']}</td>
            <td class='c_n'>$sum_txt</td>
            <td class='c_t'>{$row['abn_state']}</td>
            <td class='c_t'>{$row['task_state']}</td>
            <td class='c_t'>{$row['mmgg_str']}</td>            
            <td class='c_t'>$comment</td>
            <td class='c_t'>$name_user</td>
            <td class='c_t'>{$row['dt_txt']}</td>
            </tr>  ");        
            $i++;
            
        }
        
    }
    
    eval("\$footer_text_eval = \"$footer_text\";");
    echo_html( $footer_text_eval);

}
//-------------------------------------------------
if ($oper=='task_print_list')
{
    $p_mmgg = sql_field_val('dt_b', 'mmgg');
    $mmgg1_str = trim($_POST['period_str']);
    
    $p_id_town = sql_field_val('addr_town', 'int');
    $p_id_sector = sql_field_val('id_sector', 'int');
    $p_book = sql_field_val('book', 'string');
    $p_dt = sql_field_val('dt_rep', 'date');    
    $p_dt_txt = $_POST['dt_rep'];
    $p_id_region = sql_field_val('id_region', 'int');
    $p_dt2_txt = $p_dt_txt;
    $p_id_person = sql_field_val('id_person', 'int');
    $p_idk_work  = sql_field_val('idk_work', 'int');    
    $print_flag=1;
    
    $params_region = '';
    $params_caption = '';
    $params_task = '';
    
    if ((isset($_POST['addr_town_name']))&&($_POST['addr_town_name']!=''))
        $params_region = trim($_POST['addr_town_name']);
    
    if ((isset($_POST['sector']))&&($_POST['sector']!=''))
        $params_caption.= ' Дільниця: '. trim($_POST['sector']);
    
    $where = 'where';

    if ($p_idk_work!='null')
    {
        if ($where!='where') $where.= " and ";
        $where.= " n.idk_work = $p_idk_work ";
        
        if ($p_idk_work==1)  $params_task = 'відключенню';
        if ($p_idk_work==2)  $params_task = 'підключенню';
        if ($p_idk_work==3)  $params_task = 'контр.огляду';
        if ($p_idk_work==4)  $params_task = 'тех.перевірці';
        if ($p_idk_work==5)  $params_task = 'заміні лічильника';
    }
    
    if ($p_id_town!='null')
    {
        if ($where!='where') $where.= " and ";
        $where.= " adr.id_town = $p_id_town ";
    }
    
    if ($p_book!='null')
    {
        if ($where!='where') $where.= " and ";
        $where.= "  acc.book = $p_book ";
    }

    if ($p_id_sector!='null') {
        if ($where!='where') $where.= " and ";
        $where.= " rp.id_sector = $p_id_sector ";
    }
    
    if ($p_id_region!='null')
    {
        if ($where!='where') $where.= " and ";
        $where.= " rs.id_region =  $p_id_region ";
        
        $SQL_H="SELECT name from cli_region_tbl where id = $p_id_region ;";
    
        $result = pg_query($Link, $SQL_H) or die("SQL Error: " . pg_last_error($Link) . $SQL_H);
        $row = pg_fetch_array($result);
        
        $params_region.= $row['name'].' р-н ';               
        

    }
    
    if ($p_dt!='null')
    {
        if ($where!='where') $where.= " and ";
        $where.= " n.date_print = $p_dt  ";
    }
    else
    {
        if ($p_mmgg!='null')
        {
            if ($where!='where') $where.= " and ";
            $where.= " n.work_period = $p_mmgg ";
        }
        
    }
    
    if ($p_id_person!='null')
    {
         if ($where!='where') $where.= " and ";        
         $where.= " u1.id_person = $p_id_person ";
    }
    
    
    if ($where=='where') $where= " ";
    
    
    eval("\$header_text_eval = \"$header_text\";");
    echo $header_text_eval;
    
    $SQL = "   select n.*, acc.book, acc.code,  
    adr.street, adr.town,
   (coalesce('буд.'||(acc.addr).house||'','')||
		coalesce('/'||(acc.addr).slash||' ','')||
			coalesce(' корп.'||(acc.addr).korp||'','')|| 
				coalesce(', кв. '||(acc.addr).flat,'')||
					coalesce('/'||(acc.addr).f_slash||' ',''))::varchar as addr, 
    (c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon,
    CASE WHEN idk_reason = 1 THEN trim(to_char(n.sum_warning,'99999999.99')) ELSE tr.name END as reason,
    CASE WHEN idk_abn_state = 1 THEN sw_dt ELSE ta.name END as abn_state,

    ('0'||substring(acc.code FROM '[0-9]+'))::int as int_code,
    ('0'||substring(acc.book FROM '[0-9]+'))::int as int_book,

    to_char(n.date_print, 'DD.MM.YYYY') as date_print_str,
    to_char(n.date_work, 'DD.MM.YYYY') as date_work_str,    
    to_char(n.work_period, 'DD.MM.YYYY') as mmgg_str,        
    to_char(n.dt_input, 'DD.MM.YYYY HH24:MI') as dt_txt,

    rp.id_sector, coalesce(rs.name,'')::varchar as sector, u1.name as user_name ,
    ti.name as task_kind, ts.name as task_state
    from clm_tasks_tbl  as n 
    join clm_paccnt_tbl as acc on (acc.id = n.id_paccnt)
    join clm_abon_tbl as c on (c.id = acc.id_abon) 
    join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class) 
    left join syi_user as u1 on (u1.id = n.id_person)        
    left join cli_tasks_reason_tbl as tr on (tr.id = n.idk_reason)    
    left join cli_tasks_abn_state_tbl as ta on (ta.id = n.idk_abn_state)    
    left join cli_tasks_tbl as ti on (ti.id = n.idk_work)
    left join cli_tasks_state_tbl as ts on (ts.id = n.task_state)
    
    left join prs_runner_paccnt as rp on (rp.id_paccnt = acc.id)
    left join prs_runner_sectors as rs on (rs.id = rp.id_sector)
    left join (
       select csw.id_paccnt, to_char(coalesce(csw.dt_action,csw.dt_create), 'DD.MM.YYYY') as sw_dt 
       from clm_switching_tbl as csw 
       join (select id_paccnt, max(dt_action) as maxdt from clm_switching_tbl where action in (1,2) group by id_paccnt) as csdt 
       on (csw.id_paccnt = csdt.id_paccnt and csw.dt_action = csdt.maxdt) 
       left join clm_switching_tbl as cswc on (csw.id_paccnt = cswc.id_paccnt and cswc.dt_action >= csw.dt_action and ((cswc.action =3 and csw.action =1) or (cswc.action =4  and csw.action =2) ) ) 
       where csw.action =2 and cswc.id_paccnt is null 
       order by csw.id_paccnt
    ) as sw on (sw.id_paccnt = acc.id)    
    $where
    order by task_num, int_book, book,  int_code, code;";

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
             
            $abon = htmlspecialchars($row['abon']);

            if($res_code=='310')
                $addr =$row['street'].' '.$row['addr'];
            else
                $addr = $row['town'].' '.$row['street'].' '.$row['addr'];
            
            $note = htmlspecialchars($row['note']);
            
            $abon = str_replace('і','i',$abon);
            $abon = str_replace('І','I',$abon);

            $addr = str_replace('і','i',$addr);
            $addr = str_replace('І','I',$addr);
            
            //$addr = htmlspecialchars($row['street'].' '.$row['house']);
            $book=$row['book'].'/'.$row['code'];
            //$sum_cnt_all++;
            //$sum_saldo+=$row['sum_warning'];
            $task_num = $row['task_num'];
            $reason = $row['reason'];
            $state = $row['abn_state'];
            //$sum_warning_txt = number_format_ua ($row['sum_warning'], 2);
            
            echo_html( "
            <tr height=24px;>
            <td class='c_t'>$task_num</td>
            <td class='c_t'>{$book}</td>                    
            <td>{$addr}</td>                    
            <td>{$abon}</td>
            <td class='c_t'>{$state}</td>                    
            <td>&nbsp;</td>
            <td class='c_t'>{$reason}</td>                    
            <td class='c_t'>{$note}</td>
            </tr>  ",1);        
            $i++;
            
            if($np == 33 )
            {
                //$sum_warning_txt = number_format_ua ($sum_saldo, 2);
    
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
      //$sum_warning_txt = number_format_ua ($sum_saldo, 2);
      for($c=$np; $c<=33; $c++)        
      {
            echo_html( "
            <tr height=24px;  >
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            </tr>  ",1);        
            $i++;
          
      }
    
      eval("\$footer_text_eval = \"$footer_text\";");
      echo_html( $footer_text_eval,$print_flag);

    }
    //return;
    
}
//----------------------------------------------------------------------------
if ($oper=='lgt_month_summary')
{
    $p_mmgg1 = sql_field_val('dt_b', 'mmgg');
    $p_mmgg2 = sql_field_val('dt_e', 'mmgg');

    $p_id_region = sql_field_val('id_region', 'int');
    $p_id_town = sql_field_val('addr_town', 'int');
    
    $params_caption = '';
    $where='';
    
    
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
   
    eval("\$header_text_eval = \"$header_text\";");
    echo $header_text_eval;
    
    $SQL = " select distinct date_trunc('month', c_date)::date as hmmgg from calendar
    where date_trunc('month', c_date)::date >=$p_mmgg1::date and 
     date_trunc('month', c_date)::date <=$p_mmgg2::date
     order by date_trunc('month', c_date)::date ;";
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        $i=0;
        while ($row = pg_fetch_array($result)) {

            $hmmgg  = "'".$row['hmmgg']."'";
            list($year, $month, $day  ) = split('[/.-]', $hmmgg);
    
            $year = str_replace("'","",$year);
            $month_str = ukr_month((int)$month,0);
            $period = "$month_str $year р.";
            
             echo " <tr height='17'>
                    <th>{$period}</th>
                    <th>&nbsp;</th>    
             </tr>";
            
            $sum_lgt_all = 0;
            $lgt_html = '';

            $SQL2 = " select * from (
            select grp2.ident, grp2.name,grp2.lvl,
            sum(all_cnt) as all_cnt, 
            sum(summ_lgt) as summ_lgt
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
            sum(summ_lgt) as summ_lgt
            from 
            ( select c.id, adr.is_town,lg.demand_lgt, summ_lgt, demand_lgt,  k.id as k_id from     
                clm_paccnt_h as c
                join (select id, max(dt_b) as dt_b from clm_paccnt_h  where 
                ((dt_b < ($hmmgg::date+'1 month'::interval) and dt_e is null)
             or 
            tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($hmmgg::timestamp::abstime,($hmmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))
            ) 
            group by id order by id) as c2 
            on (c.id = c2.id and c.dt_b = c2.dt_b)
            join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class)
            join 
                (
                select id_paccnt, id_grp_lgt , sum(demand_lgt) as demand_lgt , sum(summ_lgt) as summ_lgt
                from 
                (
                select b.id_paccnt, ls.id_grp_lgt , ls.demand_lgt, ls.summ_lgt
                from acm_bill_tbl as b 
                join acm_lgt_summ_tbl as ls on (b.id_doc= ls.id_doc) 
                where b.id_pref = 10 
                and (coalesce(ls.demand_lgt,0) <>0 or coalesce(ls.demand_add_lgt,0) <>0 )
                and b.mmgg = $hmmgg 
                 union all
                select lg.id_paccnt, lg.id_grp_lgt,lg.demand_val, lg.sum_val
                from acm_dop_lgt_tbl as lg
                join lgi_group_tbl as g on(g.id = lg.id_grp_lgt)
                where mmgg = $hmmgg  
                ) as ss
            group by id_paccnt, id_grp_lgt        
            ) as lg on (lg.id_paccnt = c.id)    
            join lgi_group_tbl as gr on (gr.id = lg.id_grp_lgt)
            left join lgi_kategor_tbl k on (k.id = gr.id_kategor)
             where gr.id_budjet = 1  $where
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
        group by  grp2.ident, grp2.name,grp2.lvl 
        order by grp2.ident
        ) as ss
        where lvl =1;";
            
            $result2 = pg_query($Link, $SQL2) or die("SQL Error: " . pg_last_error($Link) . $SQL2);
            if ($result2) {
        
                $rows_count2 = pg_num_rows($result2);
                while ($row2 = pg_fetch_array($result2)) {

                    $name = $row2['name'];
                    $summ_lgt_txt = number_format_ua ($row2['summ_lgt'],2);
                    
                    $lgt_html.="
                        <tr >
                        <TD class='c_t'>{$name}</td>
                        <TD class='c_n'>{$summ_lgt_txt}</td>
                        </tr>  ";        

                    $sum_lgt_all+=$row2['summ_lgt'];
                    
                }
            }
                
            //местные  льготы
            $SQL2 = " select sum(summ_lgt) as summ_lgt  from 
        (
        select c.id, adr.is_town,lg.demand_lgt, summ_lgt, demand_lgt from     
        clm_paccnt_h as c
            join (select id, max(dt_b) as dt_b from clm_paccnt_h  where 
            ((dt_b < ($hmmgg::date+'1 month'::interval) and dt_e is null)
                or 
                tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($hmmgg::timestamp::abstime,($hmmgg::date+'1 month - 1 day'::interval)::timestamp::abstime))            )
            group by id order by id) as c2 
            on (c.id = c2.id and c.dt_b = c2.dt_b)
        join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class)
        join 
            (
            select id_paccnt, id_grp_lgt , sum(demand_lgt) as demand_lgt , sum(summ_lgt) as summ_lgt
            from 
            (
            select b.id_paccnt, ls.id_grp_lgt , ls.demand_lgt, ls.summ_lgt
            from acm_bill_tbl as b 
            join acm_lgt_summ_tbl as ls on (b.id_doc= ls.id_doc) 
            where b.id_pref = 10 and coalesce(ls.demand_lgt,0) <>0 
            and b.mmgg = $hmmgg
            union all
            select lg.id_paccnt, lg.id_grp_lgt,lg.demand_val, lg.sum_val
            from acm_dop_lgt_tbl as lg
            where mmgg = $hmmgg  
            ) as ss
            group by id_paccnt, id_grp_lgt        
            ) as lg on (lg.id_paccnt = c.id)    
        join lgi_group_tbl as gr on (gr.id = lg.id_grp_lgt)
        where gr.id_budjet = 2  $where
        ) as ss";
            
            $result2 = pg_query($Link, $SQL2) or die("SQL Error: " . pg_last_error($Link) . $SQL2);
            if ($result2) {
        
                $rows_count2 = pg_num_rows($result2);
                while ($row2 = pg_fetch_array($result2)) {

                    $name = "Місцеві пільги";
                    $summ_lgt_txt = number_format_ua ($row2['summ_lgt'],2);
                    
                    $lgt_html.="
                        <tr >
                        <TD class='c_t'>{$name}</td>
                        <TD class='c_n'>{$summ_lgt_txt}</td>
                        </tr>  ";        

                    $sum_lgt_all+=$row2['summ_lgt'];
                    
                }
            }

            $summ_lgt_txt = number_format_ua ($sum_lgt_all,2);
            echo " <tr class='tab_head'>
                        <TD class='c_t'>ПІЛЬГИ</td>
                        <TD class='c_n'>{$summ_lgt_txt}</td>
                   </tr>  ";        
            echo $lgt_html;
            

            //субсидии
            $SQL2 = " select sum(value) as sum_subs
                from acm_pay_tbl as p
                join clm_paccnt_tbl as c on (c.id = p.id_paccnt)
                join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class)
                where p.id_pref = 10 and p.idk_doc in (110, 111, 193,194) 
                and p.mmgg = $hmmgg $where ;";
            
            $result2 = pg_query($Link, $SQL2) or die("SQL Error: " . pg_last_error($Link) . $SQL2);
            if ($result2) {
        
                $rows_count2 = pg_num_rows($result2);
                while ($row2 = pg_fetch_array($result2)) {

                    $name = "СУБСИДІЇ";
                    $summ_lgt_txt = number_format_ua ($row2['sum_subs'],2);
                    
                    echo "
                        <tr class='tab_head'>
                        <TD class='c_t'>{$name}</td>
                        <TD class='c_n'>{$summ_lgt_txt}</td>
                        </tr>  ";        
                    
                }
            }
        }
    }
    
    
    eval("\$footer_text_eval = \"$footer_text\";");
    echo $footer_text_eval;
   
}

////----------------------------------------------------------------------------
if ($oper=='work_count')
{
    
    $p_mmgg1 = sql_field_val('dt_b', 'mmgg');
    $p_mmgg2 = sql_field_val('dt_e', 'mmgg');
    
    $mmgg1_str = ukr_date($_POST['dt_b'],0,1);
    $mmgg2_str = ukr_date($_POST['dt_e'],0,1);

    $mmgg1_str = "$mmgg1_str - $mmgg2_str";    
    
    
    $p_id_town = sql_field_val('addr_town', 'int');
    //$p_id_sector = sql_field_val('id_sector', 'int');
    $p_book = sql_field_val('book', 'string');
    $p_id_sector = sql_field_val('id_sector', 'int');
    $p_id_region = sql_field_val('id_region', 'int');
    
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
   
    eval("\$header_text_eval = \"$header_text\";");
    echo $header_text_eval;
    
    $SQL = " select w.idk_work, i.name as name_work, count(*) as cnt
    from clm_works_tbl as w
    join cli_works_tbl as i on (i.id = w.idk_work )
    join clm_paccnt_tbl as acc on (acc.id = w.id_paccnt)
    left join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class)
    where w.work_period >= $p_mmgg1 and w.work_period <= $p_mmgg2 $where
    group by w.idk_work, i.name
    order by w.idk_work;";

   // throw new Exception(json_encode($SQL));
    
    $i=0;
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        while ($row = pg_fetch_array($result)) {

            echo_html( "
            <tr height=18px;>
            <td class='c_t'> {$row['name_work']}</td>
            <td class='c_i'>{$row['cnt']}</td>                    
            </tr>  ",1);        
            $i++;
        }
    }

    eval("\$footer_text_eval = \"$footer_text\";");
    echo $footer_text_eval;

}
//-----------------------------------------------------------------------------
if ($oper=='work_stat_count')
{
    $p_mmgg1 = sql_field_val('dt_b', 'mmgg');
    $p_mmgg2 = sql_field_val('dt_e', 'mmgg');
    
    $mmgg1_str = ukr_date($_POST['dt_b'],0,1);
    $mmgg2_str = ukr_date($_POST['dt_e'],0,1);

    $mmgg1_str = "$mmgg1_str - $mmgg2_str";    
    
    $p_id_town = sql_field_val('addr_town', 'int');
    //$p_id_sector = sql_field_val('id_sector', 'int');
    $p_book = sql_field_val('book', 'string');
    $p_id_sector = sql_field_val('id_sector', 'int');
    $p_id_region = sql_field_val('id_region', 'int');
    
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
   /*
    $SQL = "select fun_mmgg() as mmgg_current ;";
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    $row = pg_fetch_array($result);
    $mmgg_current = "'{$row['mmgg_current']}'";   
   */
   
    eval("\$header_text_eval = \"$header_text\";");
    echo $header_text_eval;
    
    $cnt_all = 0;
    $cnt_nw = 0;
    $cnt_nl = 0;
    
    $SQL = "select adr.town, count(*) as cnt, 
    sum(wcnt) as wcnt, count(*) - sum(wcnt) as nw_cnt,
    count(distinct CASE WHEN coalesce(acc.not_live,false)=true or coalesce(ncnt,0)=1 THEN acc.id END) as nl_cnt
    from 
    clm_paccnt_tbl as acc
    join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class)
    left join 
    (
     select w.id_paccnt, count(distinct w.id_paccnt) as wcnt 
     from clm_works_tbl as w 
     where w.work_period >= $p_mmgg1 and w.work_period <= $p_mmgg2
     group by w.id_paccnt
    ) as ww on (ww.id_paccnt= acc.id)
    left join (
      	select n.id_paccnt, count(distinct n.id_paccnt) as ncnt
        from clm_notlive_tbl as n 
       where n.dt_b<= $mmgg_current::date+'1 month -1 day'::interval and ((n.dt_e is null) or (n.dt_e>= $mmgg_current::date))
       group by n.id_paccnt
       order by n.id_paccnt
   ) as n on (n.id_paccnt = acc.id)
    where archive=0 $where
    group by adr.town
    order by adr.town;";

   // throw new Exception(json_encode($SQL));
    
    $i=0;
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        while ($row = pg_fetch_array($result)) {

            $cnt_all += $row['cnt'];
            $cnt_nw  += $row['nw_cnt'];
            $cnt_nl  += $row['nl_cnt'];
            
            
            echo_html( "
            <tr height=18px;>
            <td class='c_t'> {$row['town']}</td>
            <td class='c_i'>{$row['cnt']}</td>            
            <td class='c_i'>{$row['nw_cnt']}</td>
            <td class='c_i'>{$row['nl_cnt']}</td>
            </tr>  ",1);        
            $i++;
        }
    }

    eval("\$footer_text_eval = \"$footer_text\";");
    echo $footer_text_eval;

}

//-----------------------------------------------------------------------------
if ($oper=='debet_years_month_old_abon')
{
    $p_mmgg = sql_field_val('dt_b', 'mmgg');
    
    list($year, $month, $day  ) = split('[/.-]', $p_mmgg);
    $month_str = ukr_month((int)$month,0);
    $p_debet_month = sql_field_val('debet_month', 'int');
    if ($p_debet_month=='null') $p_debet_month =0;

    
    $p_id_tar = sql_field_val('id_gtar', 'int');        
    $period = "за $month_str $year р.";

    $p_id_sector = sql_field_val('id_sector', 'int');
    $p_book = sql_field_val('book', 'string');
    $p_id_region = sql_field_val('id_region', 'int');

    $p_id_person = sql_field_val('id_person', 'int');

    $p_id_town = sql_field_val('addr_town', 'int');
    $p_year_rep = sql_field_val('year_rep', 'int');
    
    $where="";
    
    if ($p_id_tar!='null')
    {
        $where.= " and acc.id_gtar = $p_id_tar ";
    }
    
    $params_caption = '';
    if ((isset($_POST['gtar']))&&($_POST['gtar']!=''))
        $params_caption .= ' Тарифна група : '. trim($_POST['gtar']);
    
    
    if ((isset($_POST['sector']))&&($_POST['sector']!=''))
        $params_caption.= ' Дiльниця: '. trim($_POST['sector']);

    
    if ((isset($_POST['addr_town_name']))&&($_POST['addr_town_name']!=''))
        $params_caption.= 'Населений пункт: '. trim($_POST['addr_town_name']);
    

    if ($p_book!='null') 
    {
        $where.= " and acc.book = $p_book ";
        $params_caption .= " книга : $p_book ";
    }

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
  
    if ($p_id_town!='null')
    {
     //   $where.= " and adr.id_town = $p_id_town ";
        if ($p_id_town==40854)
          $where.= " and ( adr.id_parent = $p_id_town or adr.id_parent = 40016 )";
        else
          $where.= " and ( adr.id_town = $p_id_town or adr.id_parent = $p_id_town )";
    }

    $sum_deb=0;
    $sum_deb01m=0;
    $sum_deb02m=0;
    $sum_deb03m=0;
    $sum_deb04m=0;
    $sum_deb05m=0;
    $sum_deb06m=0;
    $sum_deb07m=0;
    $sum_deb08m=0;
    $sum_deb09m=0;
    $sum_deb10m=0;
    $sum_deb11m=0;
    $sum_deb12m=0;
    
    $i=1;
    
    $np=0;
    $print_flag = 1;
    
   
    $town_hidden=CheckTownInAddrHidden($Link);
   
    
   $where_old_years = ''; 
   
   if ($p_year_rep=='null')   $p_year_rep = '2016';

   $p_hmmgg = "'$p_year_rep-01-01'";
    $params_caption .= " що мають заборгованість за $p_year_rep рік ";


    eval("\$header_text = \"$header_text\";");
    echo_html( $header_text);

   $SQL = "select crt_ttbl();";
   $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    
   
    $SQL = "select rep_month_saldo_year_fun($p_mmgg::date,$p_hmmgg::date,1);";
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
        
    
    $SQL = " select acc.book, acc.code , acc.note, 
        (c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon,
        adr.town, adr.street||' '||address_print(acc.addr) as addr,
        dt_all, deb01m,deb02m,deb03m, deb04m, deb05m,deb06m, deb07m, deb08m, deb09m, deb10m, deb11m, deb12m,
        (CASE WHEN sw.action = 1 then 'Відкл. ' WHEN sw.action = 2 then 'Попер. ' END || to_char(sw.dt_action, 'DD.MM.YYYY'))::varchar as action,
        ap.name as add_param,
        to_char((select max(p.reg_date) from acm_pay_tbl as p 
        	where p.id_pref = 10  and p.value<> 0 and p.id_paccnt = s1.id_paccnt), 'DD.MM.YYYY') as date_pay,
        ('0'||substring(acc.code FROM '[0-9]+'))::int as int_code,
        ('0'||substring(acc.book FROM '[0-9]+'))::int as int_book
  from 
(
 select s.*
 from rep_month_year_dt_tbl as s  
  where s.mmgg = $p_mmgg::date and s.hmmgg = $p_hmmgg::date
) as s1
join acm_saldo_tbl as sd on (sd.id_paccnt = s1.id_paccnt and sd.id_pref = 10 and sd.mmgg = $p_mmgg::date )
join clm_paccnt_tbl as acc on (acc.id = s1.id_paccnt)
join clm_abon_tbl as c on (c.id = acc.id_abon) 
join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class)
left join cli_addparam_tbl as ap on (ap.id = acc.id_cntrl)    
left join (
 select csw.id_paccnt, csw.dt_action, csw.action
	from clm_switching_tbl as csw 
	join (select id_paccnt, max(dt_action) as maxdt from clm_switching_tbl where action in (1,2) group by id_paccnt) as csdt 
	on (csw.id_paccnt = csdt.id_paccnt and csw.dt_action = csdt.maxdt) 
	left join clm_switching_tbl as cswc on (csw.id_paccnt = cswc.id_paccnt and cswc.dt_action >= csw.dt_action and ((cswc.action =3 and csw.action =1) or (cswc.action =4  and csw.action =2) ) ) 
	where csw.action in (1,2) and cswc.id_paccnt is null 
) as sw on (sw.id_paccnt = s1.id_paccnt)
where sd.e_val >0 
$where    
order by int_book, acc.book, int_code, acc.code  ;";

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
            $note = htmlspecialchars($row['note']);
            $add_param = htmlspecialchars($row['add_param']);
            
            if ($town_hidden=='true')
                $addr = htmlspecialchars($row['addr']);
            else
                $addr = htmlspecialchars($row['town'].' '.$row['addr']);
                
            $book=$row['book'];
            $code=$row['code'];
            
            //$e_val_txt = number_format_ua ($row['e_val'], 2);
            $deb_txt = number_format_ua ($row['dt_all'], 2);
            $deb01m_txt = number_format_ua ($row['deb01m'], 2);
            $deb02m_txt = number_format_ua ($row['deb02m'], 2);
            $deb03m_txt = number_format_ua ($row['deb03m'], 2);
            $deb04m_txt = number_format_ua ($row['deb04m'], 2);
            $deb05m_txt = number_format_ua ($row['deb05m'], 2);
            $deb06m_txt = number_format_ua ($row['deb06m'], 2);
            $deb07m_txt = number_format_ua ($row['deb07m'], 2);
            $deb08m_txt = number_format_ua ($row['deb08m'], 2);
            $deb09m_txt = number_format_ua ($row['deb09m'], 2);
            $deb10m_txt = number_format_ua ($row['deb10m'], 2);
            $deb11m_txt = number_format_ua ($row['deb11m'], 2);
            $deb12m_txt = number_format_ua ($row['deb12m'], 2);
            

            if ($p_sum_only!=1)
            {
           
              echo_html( "
              <tr >
              <td>{$i}</td>            
              <td class='c_t'>$addr</td>
              <td class='c_t'>$abon</td>
              <td class='c_t'>$book</td>
              <td>$code</td>
              <td class='c_n'>{$deb_txt}</td>              
              <td class='c_n'>{$deb01m_txt}</td>
              <td class='c_n'>{$deb02m_txt}</td>
              <td class='c_n'>{$deb03m_txt}</td>
              <td class='c_n'>{$deb04m_txt}</td>
              <td class='c_n'>{$deb05m_txt}</td>
              <td class='c_n'>{$deb06m_txt}</td>
              <td class='c_n'>{$deb07m_txt}</td>
              <td class='c_n'>{$deb08m_txt}</td>
              <td class='c_n'>{$deb09m_txt}</td>
              <td class='c_n'>{$deb10m_txt}</td>
              <td class='c_n'>{$deb11m_txt}</td>
              <td class='c_n'>{$deb12m_txt}</td>
              <td class='c_t'>{$row['date_pay']}</td>
              <td class='c_t'>{$row['action']}</td>
              <td class='c_t'>{$add_param}</td>
              <td class='c_t'>{$row['note']}</td>
              </tr>  ",$print_flag); 
            }
            $i++;
            
            $sum_deb+=$row['dt_all'];
            
            $sum_deb01m+=$row['deb01m'];
            $sum_deb02m+=$row['deb02m'];
            $sum_deb03m+=$row['deb03m'];
            $sum_deb04m+=$row['deb04m'];
            $sum_deb05m+=$row['deb05m'];
            $sum_deb06m+=$row['deb06m'];
            $sum_deb07m+=$row['deb07m'];
            $sum_deb08m+=$row['deb08m'];
            $sum_deb09m+=$row['deb09m'];
            $sum_deb10m+=$row['deb10m'];
            $sum_deb11m+=$row['deb11m'];
            $sum_deb12m+=$row['deb12m'];
            
        }
    }

    $deb_txt = number_format_ua ($sum_deb, 2);
    
    $deb01m_txt = number_format_ua ($sum_deb01m, 2);
    $deb02m_txt = number_format_ua ($sum_deb02m, 2);
    $deb03m_txt = number_format_ua ($sum_deb03m, 2);
    $deb04m_txt = number_format_ua ($sum_deb04m, 2);
    $deb05m_txt = number_format_ua ($sum_deb05m, 2);
    $deb06m_txt = number_format_ua ($sum_deb06m, 2);
    $deb07m_txt = number_format_ua ($sum_deb07m, 2);
    $deb08m_txt = number_format_ua ($sum_deb08m, 2);
    $deb09m_txt = number_format_ua ($sum_deb09m, 2);
    $deb10m_txt = number_format_ua ($sum_deb10m, 2);
    $deb11m_txt = number_format_ua ($sum_deb11m, 2);
    $deb12m_txt = number_format_ua ($sum_deb12m, 2);
    
    eval("\$footer_text_eval = \"$footer_text\";");

    if ($p_sum_only==1) $print_flag=1;
    
    echo_html( $footer_text_eval,$print_flag);
   
    if ($print_flag==0) echo "</tbody> </table> ";
   
}
//------------------------------------------------------------------------------
if ($oper=='check_ind_plan_zero3') 
{
    $p_mmgg = sql_field_val('dt_b', 'mmgg');
    $period = trim($_POST['period_str']);
    
    $p_book = sql_field_val('book', 'string');
    $p_id_town = sql_field_val('addr_town', 'int');
    $p_id_tar = sql_field_val('id_gtar', 'int');    

    $p_id_region = sql_field_val('id_region', 'int');
    $p_id_sector = sql_field_val('id_sector', 'int');
    

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
        $where.= " and ( adr.id_town = $p_id_town or adr.id_parent = $p_id_town )";
    }

    if ($p_id_tar!='null')
    {
        $where.= "and acc.id_gtar = $p_id_tar ";
    }

    
    
    if ($p_id_region!='null')
    {
        
        $where.= "and exists (select rs.id from 
            prs_runner_paccnt as rp 
            join prs_runner_sectors as rs on (rs.id = rp.id_sector)
            where rp.id_paccnt = acc.id and rs.id_region =  $p_id_region ) "; 
        
        //$where.= " and rs.id_region =  $p_id_region ";

        $SQL_H="SELECT name from cli_region_tbl where id = $p_id_region ;";
    
        $result = pg_query($Link, $SQL_H) or die("SQL Error: " . pg_last_error($Link) . $SQL_H);
        $row = pg_fetch_array($result);
        
        $params_caption.= $row['name'];        
       
    }
    
    if ($p_id_sector!='null')
    {
       $where.= "and exists (select id from prs_runner_paccnt as rp 
          where rp.id_paccnt = acc.id and rp.id_sector = $p_id_sector ) ";
        
    }

    
    eval("\$header_text = \"$header_text\";");
    echo_html( $header_text);
    
   
    
    $SQL = "select acc.book, acc.code, 
  (ab.last_name||' '||coalesce(ab.name,'')||' '||coalesce(ab.patron_name,''))::varchar as abon,
  adr.town, (adr.street||' '||
        (coalesce('буд.'||(acc.addr).house||'','')||
		coalesce('/'||(acc.addr).slash||' ','')|| 
			coalesce(' корп.'||(acc.addr).korp||'','')||
				coalesce(', кв. '||(acc.addr).flat,'')||
					coalesce('/'||(acc.addr).f_slash,''))::varchar
    )::varchar as addr,

        (CASE WHEN sw.action = 1 then 'Вiдкл. ' WHEN sw.action = 2 then 'Попер. ' END || to_char(sw.dt_action, 'DD.MM.YYYY'))::varchar as action,
            acc.note , ap.name as add_param , demand_ind
           from  clm_paccnt_tbl as acc 
           join clm_abon_tbl as ab on (ab.id = acc.id_abon)         
           join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class)
           join (select id_paccnt,sum(value_diff) as demand_ind from acm_indication_tbl 
            where mmgg = $p_mmgg and id_operation =14 and value_diff >0
            group by id_paccnt) as i on (i.id_paccnt = acc.id)
left join (
 select csw.id_paccnt, csw.dt_action, csw.action
	from clm_switching_tbl as csw 
	join (select id_paccnt, max(dt_action) as maxdt from clm_switching_tbl where action in (1,2) group by id_paccnt) as csdt 
	on (csw.id_paccnt = csdt.id_paccnt and csw.dt_action = csdt.maxdt) 
	left join clm_switching_tbl as cswc on (csw.id_paccnt = cswc.id_paccnt and cswc.dt_action >= csw.dt_action and ((cswc.action =3 and csw.action =1) or (cswc.action =4  and csw.action =2) ) ) 
	where csw.action in (1,2) and cswc.id_paccnt is null 
) as sw on (sw.id_paccnt = acc.id)
left join cli_addparam_tbl as ap on (ap.id = acc.id_cntrl)         
 where acc.archive = 0
 and not exists (select id_doc from acm_bill_tbl where id_pref = 10 
          and mmgg_bill >= ($p_mmgg::date - '3 month'::interval)::date and mmgg_bill <= ($p_mmgg::date -'1 month'::interval)::date
          and acm_bill_tbl.id_paccnt = acc.id and demand >0)
         $where
 order by  ('0'||substring(acc.book FROM '[0-9]+'))::int ,
           ('0'||substring(acc.code FROM '[0-9]+'))::int;";

    
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
 
            $abon = htmlspecialchars($row['abon']);
            $note = htmlspecialchars($row['note']);
            
            if($res_code=='310')
                 $addr = $row['addr'];
            else
                 $addr = $row['town'].' '.$row['addr'];
            
            $book=$row['book'];
            $code=$row['code'];
            
            $demand_txt = number_format_ua ($row['demand_ind'], 0);
           
            // <tr >
            echo_html( " <tr>
            <td>{$i}</td>
            <td class='c_t'>{$book}</td>
            <td class='c_t'>{$code}</td>
            <td class='c_t'>{$abon}</td>
            <td class='c_t'>{$addr}</td>
            <td class='c_i'>{$demand_txt}</td>
            <td class='c_t'>{$row['action']}</td>
            <td class='c_t'>{$note}</td>
            <td class='c_t'>{$row['add_param']}</td>
            </tr>
            ",$print_flag);
            

            $i++;
        }
        
    }
    
    eval("\$footer_text_eval = \"$footer_text\";");
    echo_html( $footer_text_eval);
    
}
//------------------------------------------------------------------------------
if ($oper=='check_ind_less') 
{
    $p_mmgg = sql_field_val('dt_b', 'mmgg');
    $period = trim($_POST['period_str']);
    
    $p_book = sql_field_val('book', 'string');
    $p_id_town = sql_field_val('addr_town', 'int');
    $p_id_tar = sql_field_val('id_gtar', 'int');    

    $p_id_region = sql_field_val('id_region', 'int');
    $p_id_sector = sql_field_val('id_sector', 'int');
    $p_id_operation = sql_field_val('id_operation', 'int');    

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
        $where.= " and ( adr.id_town = $p_id_town or adr.id_parent = $p_id_town )";
    }

    if ($p_id_tar!='null')
    {
        $where.= "and acc.id_gtar = $p_id_tar ";
    }

    
    
    if ($p_id_region!='null')
    {
        
        $where.= "and rs.id_region =  $p_id_region  "; 
        
        $SQL_H="SELECT name from cli_region_tbl where id = $p_id_region ;";
    
        $result = pg_query($Link, $SQL_H) or die("SQL Error: " . pg_last_error($Link) . $SQL_H);
        $row = pg_fetch_array($result);
        
        $params_caption.= $row['name'];        
       
    }
    
    if ($p_id_sector!='null')
    {
       $where.= "and exists (select id from prs_runner_paccnt as rp 
          where rp.id_paccnt = acc.id and rp.id_sector = $p_id_sector ) ";
        
    }
    
    $where_oper='';
    if ((isset($_POST['id_operation']))&&($_POST['id_operation']!='null'))
    {
        
        $SQL = "select name from cli_indic_type_tbl where id = $p_id_operation ;";
       
        $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
        if ($result) 
        {
          $row = pg_fetch_array($result);
          $operation = $row['name'];
        }        
        $params_caption .= ' тип попередніх показників : '. $operation;
        
        $where_oper= " and ii.id_operation = $p_id_operation  ";        
    }    
    
    eval("\$header_text = \"$header_text\";");
    echo_html( $header_text);
    
   
    
    $SQL = "select acc.book, acc.code, 
  (ab.last_name||' '||coalesce(ab.name,'')||' '||coalesce(ab.patron_name,''))::varchar as abon,
  adr.town, (adr.street||' '||
        (coalesce('буд.'||(acc.addr).house||'','')||
		coalesce('/'||(acc.addr).slash||' ','')|| 
			coalesce(' корп.'||(acc.addr).korp||'','')||
				coalesce(', кв. '||(acc.addr).flat,'')||
					coalesce('/'||(acc.addr).f_slash,''))::varchar
    )::varchar as addr,

        (CASE WHEN sw.action = 1 then 'Вiдкл. ' WHEN sw.action = 2 then 'Попер. ' END || to_char(sw.dt_action, 'DD.MM.YYYY'))::varchar as action,
            acc.note , ap.name as add_param , i.*,  z.nm as zone, it.name as indic_type, rs.name as sector_name
           from  clm_paccnt_tbl as acc 
           join clm_abon_tbl as ab on (ab.id = acc.id_abon)         
           join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class)
           join (select i.id_paccnt, i.num_eqp, i.id_zone , i.value, 
           to_char(i.dat_ind, 'DD.MM.YYYY') as dat_ind , i.id_operation, i.value_diff, 
              ii.value value_prev, ii.dat_ind as dat_prev 
              from acm_indication_tbl as i 
              join acm_indication_tbl as ii on (ii.id = i.id_prev $where_oper )
              where i.mmgg = $p_mmgg and i.value < ii.value and ( i.value_diff = 0 or i.value_diff >1000)
            ) as i on (i.id_paccnt = acc.id)
           join eqk_zone_tbl as z on (z.id = i.id_zone ) 
           join cli_indic_type_tbl as it on (it.id = i.id_operation)
           join prs_runner_paccnt as rp on (rp.id_paccnt = acc.id)
           join prs_runner_sectors as rs on (rs.id = rp.id_sector)
left join (
 select csw.id_paccnt, csw.dt_action, csw.action
	from clm_switching_tbl as csw 
	join (select id_paccnt, max(dt_action) as maxdt from clm_switching_tbl where action in (1,2) group by id_paccnt) as csdt 
	on (csw.id_paccnt = csdt.id_paccnt and csw.dt_action = csdt.maxdt) 
	left join clm_switching_tbl as cswc on (csw.id_paccnt = cswc.id_paccnt and cswc.dt_action >= csw.dt_action and ((cswc.action =3 and csw.action =1) or (cswc.action =4  and csw.action =2) ) ) 
	where csw.action in (1,2) and cswc.id_paccnt is null 
) as sw on (sw.id_paccnt = acc.id)
left join cli_addparam_tbl as ap on (ap.id = acc.id_cntrl)         
 where acc.archive = 0  $where
 order by  ('0'||substring(acc.book FROM '[0-9]+'))::int ,
           ('0'||substring(acc.code FROM '[0-9]+'))::int;";

    
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
 
            $abon = htmlspecialchars($row['abon']);
            $note = htmlspecialchars($row['note']);
            $sector = htmlspecialchars($row['sector_name']);
                        
            if($res_code=='310')
                 $addr = $row['addr'];
            else
                 $addr = $row['town'].' '.$row['addr'];
            
            $book=$row['book']; 
            $code=$row['code'];
            
            $value_txt = number_format_ua ($row['value'], 0);
            $value_prev_txt = number_format_ua ($row['value_prev'], 0);
            
            // <tr >
            echo_html( " <tr>
            <td>{$i}</td>
            <td class='c_t'>{$book}</td>
            <td class='c_t'>{$code}</td>
            <td class='c_t'>{$abon}</td>
            <td class='c_t'>{$addr}</td>
            <td class='c_t'>{$sector}</td>
            <td class='c_i'>{$value_txt}</td>
            <td class='c_i'>{$value_prev_txt}</td>
            <td class='c_t'>{$row['dat_ind']}</td>
            <td class='c_t'>{$row['num_eqp']}</td>
            <td class='c_t'>{$row['zone']}</td>
            <td class='c_t'>{$row['indic_type']}</td>
            <td class='c_t'>{$row['action']}</td>
            <td class='c_t'>{$note}</td>
            <td class='c_t'>{$row['add_param']}</td>
            </tr>
            ",$print_flag);

            $i++;
        }
        
    }
    
    eval("\$footer_text_eval = \"$footer_text\";");
    echo_html( $footer_text_eval);
    
}

//------------------------------------------------------------------------------
if ($oper=='demand_check') 
{
    $p_mmgg = sql_field_val('dt_b', 'mmgg');
    $period_str = trim($_POST['period_str']);

    $p_id_region = sql_field_val('id_region', 'int');
   
    $params_caption ='';    
    $where = ' ';
    
    if ((isset($_POST['note']))&&($_POST['note']!=''))
    {
        $where.= $_POST['note'];
        $params_caption .= $_POST['note'];
    }
    
    if ($p_id_region!='null')
    {
        
        $where.= "and exists (select rs.id from 
            prs_runner_paccnt as rp 
            join prs_runner_sectors as rs on (rs.id = rp.id_sector)
            where rp.id_paccnt = acc.id and rs.id_region =  $p_id_region ) "; 
        
        //$where.= " and rs.id_region =  $p_id_region ";

        $SQL_H="SELECT name from cli_region_tbl where id = $p_id_region ;";
    
        $result = pg_query($Link, $SQL_H) or die("SQL Error: " . pg_last_error($Link) . $SQL_H);
        $row = pg_fetch_array($result);
        
        $params_caption.= $row['name'];        
       
    }
    
    eval("\$header_text = \"$header_text\";");
    echo_html( $header_text);
    
   
    $SQL = "select acc.id, acc.book, acc.code, 
  (ab.last_name||' '||coalesce(ab.name,'')||' '||coalesce(ab.patron_name,''))::varchar as abon,
  adr.town, (adr.street||' '||
        (coalesce('буд.'||(acc.addr).house||'','')||
		coalesce('/'||(acc.addr).slash||' ','')|| 
			coalesce(' корп.'||(acc.addr).korp||'','')||
				coalesce(', кв. '||(acc.addr).flat,'')|| 
					coalesce('/'||(acc.addr).f_slash,''))::varchar
    )::varchar as addr,sum_dem, bill_demand,coalesce(bill_corr,0) as bill_corr,
    coalesce(bill_demand,0) + coalesce(bill_corr,0) as bill_all,
    coalesce(sum_dem,0) - (coalesce(bill_demand,0) + coalesce(bill_corr,0))   as bill_delta,
  ('0'||substring(acc.book FROM '[0-9]+'))::int as int_book,
  ('0'||substring(acc.code FROM '[0-9]+'))::int as int_code
 from 
 clm_paccnt_tbl as acc 
 join clm_abon_tbl as ab on (ab.id = acc.id_abon) 
 join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class)
 left join ( select i.id_paccnt, coalesce(sum(i.value_cons),0) as sum_dem
   from acm_indication_tbl as i 
   where i.mmgg = $p_mmgg and i.id_prev is not null
   group by i.id_paccnt
  ) as ii on (ii.id_paccnt = acc.id)
 left join (select id_paccnt, sum(demand) as bill_demand from 
    acm_bill_tbl as b 
    where b.mmgg = $p_mmgg and b.mmgg_bill = $p_mmgg 
    and b.value_calc<>0 
    and b.id_pref = 10 and b.idk_doc = 200
   group by id_paccnt
) as b on (b.id_paccnt = acc.id)  
left join    	
 (
          select id_paccnt,  sum(demand) as bill_corr 
           from acm_bill_tbl as bb where 
           bb.mmgg > $p_mmgg  and 
           bb.mmgg_bill =$p_mmgg  and 
           bb.id_pref = 10 and bb.idk_doc=220 and bb.id_corr_doc is not null
           group by id_paccnt 
           order by id_paccnt
 ) as sum_recalc
  on (sum_recalc.id_paccnt = acc.id)         
where coalesce(sum_dem,0) <>coalesce(bill_demand,0) $where
  order by int_book, int_code;";

    
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
 
            $abon = htmlspecialchars($row['abon']);
            
            if($res_code=='310')
                 $addr = $row['addr'];
            else
                 $addr = $row['town'].' '.$row['addr'];
            
            $book=$row['book']; 
            $code=$row['code'];
            
            $sum_dem_txt = number_format_ua ($row['sum_dem'], 0);
            $bill_demand_txt = number_format_ua ($row['bill_demand'], 0);
            $bill_corr_txt = number_format_ua ($row['bill_corr'], 0);
            $bill_all_txt = number_format_ua ($row['bill_all'], 0);
            $bill_delta_txt = number_format_ua ($row['bill_delta'], 0);
            
            echo_html( " <tr>
            <td>{$i}</td>
            <td class='c_t'>{$book}</td>
            <td class='c_t'>{$code}</td>
            <td class='c_t'>{$abon}</td>
            <td class='c_t'>{$addr}</td>
            <td class='c_i'>{$sum_dem_txt}</td>
            <td class='c_i'>{$bill_demand_txt}</td>
            <td class='c_i'>{$bill_corr_txt}</td>
            <td class='c_i'>{$bill_all_txt}</td>
            <td class='c_i'>{$bill_delta_txt}</td>
            </tr>
            ",$print_flag);

            $i++;
        }
    }
    
    eval("\$footer_text_eval = \"$footer_text\";");
    echo_html( $footer_text_eval);
    
}
//------------------------------------------------------------------------------
if ($oper=='saldo_check') 
{
    $p_mmgg = sql_field_val('dt_b', 'mmgg');
    $period_str = trim($_POST['period_str']);

    $p_id_region = sql_field_val('id_region', 'int');
   
    $params_caption ='';    
    $where = ' ';
    
    if ((isset($_POST['note']))&&($_POST['note']!=''))
    {
        $where.= $_POST['note'];
        $params_caption .= $_POST['note'];
    }
    
    if ($p_id_region!='null')
    {
        
        $where.= "and exists (select rs.id from 
            prs_runner_paccnt as rp 
            join prs_runner_sectors as rs on (rs.id = rp.id_sector)
            where rp.id_paccnt = acc.id and rs.id_region =  $p_id_region ) "; 
        
        //$where.= " and rs.id_region =  $p_id_region ";

        $SQL_H="SELECT name from cli_region_tbl where id = $p_id_region ;";
    
        $result = pg_query($Link, $SQL_H) or die("SQL Error: " . pg_last_error($Link) . $SQL_H);
        $row = pg_fetch_array($result);
        
        $params_caption.= $row['name'];        
       
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
        //else
        //{
        //  $SQL = "select rep_month_saldo_fun($p_mmgg,0);";
        //  $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
        //}
    }   
    
    
    $SQL = "select acc.id, acc.book, acc.code, 
  (ab.last_name||' '||coalesce(ab.name,'')||' '||coalesce(ab.patron_name,''))::varchar as abon,
  adr.town,(adr.street||' '||
        (coalesce('буд.'||(acc.addr).house||'','')||
		coalesce('/'||(acc.addr).slash||' ','')|| 
			coalesce(' корп.'||(acc.addr).korp||'','')||
				coalesce(', кв. '||(acc.addr).flat,'')||
					coalesce('/'||(acc.addr).f_slash,''))::varchar
    )::varchar as addr,s_saldo, saldo, s_saldo-saldo as delta,
  ('0'||substring(acc.book FROM '[0-9]+'))::int as int_book,
  ('0'||substring(acc.code FROM '[0-9]+'))::int as int_code    
 from 
 clm_paccnt_tbl as acc 
 join clm_abon_tbl as ab on (ab.id = acc.id_abon) 
 join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class)
 join
(
select id_paccnt, coalesce(sum(e_dtval),0) - coalesce(sum(e_ktval),0) as s_saldo
    from $table_name where mmgg = $p_mmgg and id_pref = 10 
    group by id_paccnt 
order by id_paccnt
) as ss on (ss.id_paccnt = acc.id)
join 
(select id_paccnt, sum(e_val) as saldo  from acm_saldo_tbl as s 
     where s.mmgg = $p_mmgg and s.id_pref=10 group by id_paccnt order by id_paccnt) as s
on (s.id_paccnt = ss.id_paccnt)
where s_saldo<>saldo $where ";

    
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
 
            $abon = htmlspecialchars($row['abon']);
            
            if($res_code=='310')
                 $addr = $row['addr'];
            else
                 $addr = $row['town'].' '.$row['addr'];
            
            $book=$row['book']; 
            $code=$row['code'];
            
            $s_saldo_txt = number_format_ua ($row['s_saldo'], 2);
            $saldo_txt = number_format_ua ($row['saldo'], 2);
            
            echo_html( " <tr>
            <td>{$i}</td>
            <td class='c_t'>{$book}</td>
            <td class='c_t'>{$code}</td>
            <td class='c_t'>{$abon}</td>
            <td class='c_t'>{$addr}</td>
            <td class='c_i'>{$s_saldo_txt}</td>
            <td class='c_i'>{$saldo_txt}</td>
            </tr>
            ",$print_flag);

            $i++;
        }
    }
    
    eval("\$footer_text_eval = \"$footer_text\";");
    echo_html( $footer_text_eval);
    
}
//--------------------------------------------------------
if ($oper=='dtkt_check') 
{
    $p_mmgg = sql_field_val('dt_b', 'mmgg');
    $period_str = trim($_POST['period_str']);

    $p_id_region = sql_field_val('id_region', 'int');
   
    $params_caption ='';    
    $where = ' ';
    
    if ((isset($_POST['note']))&&($_POST['note']!=''))
    {
        $where.= $_POST['note'];
        $params_caption .= $_POST['note'];
    }
    
    if ($p_id_region!='null')
    {
        
        $where.= "where exists (select rs.id from 
            prs_runner_paccnt as rp 
            join prs_runner_sectors as rs on (rs.id = rp.id_sector)
            where rp.id_paccnt = acc.id and rs.id_region =  $p_id_region ) "; 
        
        //$where.= " and rs.id_region =  $p_id_region ";

        $SQL_H="SELECT name from cli_region_tbl where id = $p_id_region ;";
    
        $result = pg_query($Link, $SQL_H) or die("SQL Error: " . pg_last_error($Link) . $SQL_H);
        $row = pg_fetch_array($result);
        
        $params_caption.= $row['name'];        
       
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
        //else
        //{
        //  $SQL = "select rep_month_saldo_fun($p_mmgg,0);";
        //  $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
        //}
    }   
    
    
    $SQL = "select acc.id, acc.book, acc.code, 
  (ab.last_name||' '||coalesce(ab.name,'')||' '||coalesce(ab.patron_name,''))::varchar as abon,
  adr.town,(adr.street||' '||
        (coalesce('буд.'||(acc.addr).house||'','')||
		coalesce('/'||(acc.addr).slash||' ','')|| 
			coalesce(' корп.'||(acc.addr).korp||'','')||
				coalesce(', кв. '||(acc.addr).flat,'')||
					coalesce('/'||(acc.addr).f_slash,''))::varchar
    )::varchar as addr,d, k,
  ('0'||substring(acc.book FROM '[0-9]+'))::int as int_book,
  ('0'||substring(acc.code FROM '[0-9]+'))::int as int_code
 from 
 clm_paccnt_tbl as acc 
 join clm_abon_tbl as ab on (ab.id = acc.id_abon) 
 join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class)
 join (
    select id_paccnt, sum(e_dtval) as d,sum(e_ktval) as k
    from $table_name where mmgg = $p_mmgg and id_pref = 10 
    group by id_paccnt 
having sum(e_dtval) <> 0 and sum(e_ktval) <>0
order by id_paccnt
) as s on (s.id_paccnt = acc.id)
 $where    
order by int_book, int_code; ";

    
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
 
            $abon = htmlspecialchars($row['abon']);
            
            if($res_code=='310')
                 $addr = $row['addr'];
            else
                 $addr = $row['town'].' '.$row['addr'];
            
            $book=$row['book']; 
            $code=$row['code'];
            
            $sum_dt_txt = number_format_ua ($row['d'], 2);
            $sum_dt_txt = number_format_ua ($row['k'], 2);
            
            echo_html( " <tr>
            <td>{$i}</td>
            <td class='c_t'>{$book}</td>
            <td class='c_t'>{$code}</td>
            <td class='c_t'>{$abon}</td>
            <td class='c_t'>{$addr}</td>
            <td class='c_i'>{$sum_dt_txt}</td>
            <td class='c_i'>{$sum_dt_txt}</td>
            </tr>
            ",$print_flag);

            $i++;
        }
    }
    
    eval("\$footer_text_eval = \"$footer_text\";");
    echo_html( $footer_text_eval);
    
}

//----------------------------------------------------------------------------
if ($oper=='lgtsubs_check') 
{
    $p_mmgg = sql_field_val('dt_b', 'mmgg');
    $period_str = trim($_POST['period_str']);

    $p_id_region = sql_field_val('id_region', 'int');
   
    $params_caption ='';    
    $where = ' ';
    
    if ((isset($_POST['note']))&&($_POST['note']!=''))
    {
        $where.= $_POST['note'];
        $params_caption .= $_POST['note'];
    }
    
    if ($p_id_region!='null')
    {
        
        $where.= "where exists (select rs.id from 
            prs_runner_paccnt as rp 
            join prs_runner_sectors as rs on (rs.id = rp.id_sector)
            where rp.id_paccnt = acc.id and rs.id_region =  $p_id_region ) "; 
        
        //$where.= " and rs.id_region =  $p_id_region ";

        $SQL_H="SELECT name from cli_region_tbl where id = $p_id_region ;";
    
        $result = pg_query($Link, $SQL_H) or die("SQL Error: " . pg_last_error($Link) . $SQL_H);
        $row = pg_fetch_array($result);
        
        $params_caption.= $row['name'];        
       
    }
    
    eval("\$header_text = \"$header_text\";");
    echo_html( $header_text);
    
   
    $SQL = "select acc.id, acc.book, acc.code, 
  (ab.last_name||' '||coalesce(ab.name,'')||' '||coalesce(ab.patron_name,''))::varchar as abon,
  adr.town,(adr.street||' '||
        (coalesce('буд.'||(acc.addr).house||'','')||
		coalesce('/'||(acc.addr).slash||' ','')|| 
			coalesce(' корп.'||(acc.addr).korp||'','')||
				coalesce(', кв. '||(acc.addr).flat,'')||
					coalesce('/'||(acc.addr).f_slash,''))::varchar
    )::varchar as addr,sum_lgt, value_subs,
  ('0'||substring(acc.book FROM '[0-9]+'))::int as int_book,
  ('0'||substring(acc.code FROM '[0-9]+'))::int as int_code
 from 
 clm_paccnt_tbl as acc 
 join clm_abon_tbl as ab on (ab.id = acc.id_abon) 
 join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class)
 join (select id_paccnt, sum(value_lgt) as sum_lgt from 
  acm_bill_tbl as b 
    where b.mmgg = $p_mmgg and b.id_pref = 10 and b.value_lgt <>0
   group by id_paccnt
 ) as b on (b.id_paccnt = acc.id)  
 join (
     	select p.id_paccnt, sum(p.value) as value_subs
       	from acm_pay_tbl as p 
       	where p.mmgg = $p_mmgg and p.id_pref = 10 and p.idk_doc = 110
       	group by p.id_paccnt
  ) as p on (p.id_paccnt = acc.id)
  $where  
  order by int_book, int_code;";

    
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
 
            $abon = htmlspecialchars($row['abon']);
            
            if($res_code=='310')
                 $addr = $row['addr'];
            else
                 $addr = $row['town'].' '.$row['addr'];
            
            $book=$row['book']; 
            $code=$row['code'];
            
            $sum_lgt_txt = number_format_ua ($row['sum_lgt'], 2);
            $value_subs_txt = number_format_ua ($row['value_subs'], 2);
            
            echo_html( " <tr>
            <td>{$i}</td>
            <td class='c_t'>{$book}</td>
            <td class='c_t'>{$code}</td>
            <td class='c_t'>{$abon}</td>
            <td class='c_t'>{$addr}</td>
            <td class='c_i'>{$sum_lgt_txt}</td>
            <td class='c_i'>{$value_subs_txt}</td>
            </tr>
            ",$print_flag);

            $i++;
        }
    }
    
    eval("\$footer_text_eval = \"$footer_text\";");
    echo_html( $footer_text_eval);
    
}
//------------------------------------------------------------------------------

if ($oper=='zvitf2_check') 
{
    $p_mmgg = sql_field_val('dt_b', 'mmgg');
    $period_str = trim($_POST['period_str']);

    $p_id_region = sql_field_val('id_region', 'int');
   
    $params_caption ='';    
    $where = ' ';
    
    if ((isset($_POST['note']))&&($_POST['note']!=''))
    {
        $where.= $_POST['note'];
        $params_caption .= $_POST['note'];
    }
    
    if ($p_id_region!='null')
    {
        
        $where.= "and exists (select rs.id from 
            prs_runner_paccnt as rp 
            join prs_runner_sectors as rs on (rs.id = rp.id_sector)
            where rp.id_paccnt = acc.id and rs.id_region =  $p_id_region ) "; 
        
        //$where.= " and rs.id_region =  $p_id_region ";

        $SQL_H="SELECT name from cli_region_tbl where id = $p_id_region ;";
    
        $result = pg_query($Link, $SQL_H) or die("SQL Error: " . pg_last_error($Link) . $SQL_H);
        $row = pg_fetch_array($result);
        
        $params_caption.= $row['name'];        
       
    }
    
    eval("\$header_text = \"$header_text\";");
    echo_html( $header_text);
    
    
    $SQL = "delete from clm_paccnt_tmp;";
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    
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
 join ( 
   select min(t.id) as id_tar, t.id_grptar
	from aqm_tarif_tbl as t 
        where ((dt_e is null ) or (dt_e > $p_mmgg )) and (dt_b <= $p_mmgg)
        and (t.per_min is null or (t.per_min <= $p_mmgg and t.per_max >= $p_mmgg))
        group by id_grptar
 ) as tt on (tt.id_grptar = a.id_gtar) ;";
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);    

      $SQL = "delete from rep_zvit_lgt_bill_tbl;";
      $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    
      $SQL = " insert into rep_zvit_lgt_bill_tbl (id_paccnt,id_grp_lgt,id_grptar,id_tarif,is_town,summ_lgt)
        select ls.id_paccnt, ls.id_grp_lgt,  a.id_gtar, 
        CASE WHEN coalesce(t.id_grptar,0)<>coalesce(a.id_gtar,0) THEN tt.id_tar ELSE ls.id_tarif END , --coalesce(ls.id_tarif,tt.id_tar),
        adr.is_town, sum(ls.summ_lgt) as sum_lgt
        from 
        (
            select b.id_paccnt, ls.id_grp_lgt, ls.summ_lgt, ls.id_tarif
            from acm_bill_tbl as b 
            join acm_lgt_summ_tbl as ls on (b.id_doc= ls.id_doc) 
            where b.idk_doc in (200,220,209,291) and b.id_pref = 10
            and b.mmgg = $p_mmgg    
        ) as ls
        join clm_paccnt_tmp as a on (a.id = ls.id_paccnt)
        join adt_addr_tbl as adr on (adr.id = (a.addr).id_class) 
        join ( 
        select min(t.id) as id_tar, t.id_grptar
            from aqm_tarif_tbl as t 
            where ((dt_e is null ) or (dt_e > $p_mmgg )) and (dt_b <= $p_mmgg)
            and (t.per_min is null or (t.per_min <= $p_mmgg and t.per_max >= $p_mmgg))
            group by id_grptar
        ) as tt on (tt.id_grptar = a.id_gtar)
        left join aqm_tarif_tbl as t on (t.id = ls.id_tarif)
        group by ls.id_paccnt,ls.id_grp_lgt,a.id_gtar, CASE WHEN coalesce(t.id_grptar,0)<>coalesce(a.id_gtar,0) THEN tt.id_tar ELSE ls.id_tarif END ,adr.is_town;";
      
        $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);      
    
   
    $SQL = "select acc.id, acc.book, acc.code, 
  (ab.last_name||' '||coalesce(ab.name,'')||' '||coalesce(ab.patron_name,''))::varchar as abon,
  adr.town,(adr.street||' '||
        (coalesce('буд.'||(acc.addr).house||'','')||
		coalesce('/'||(acc.addr).slash||' ','')|| 
			coalesce(' корп.'||(acc.addr).korp||'','')||
				coalesce(', кв. '||(acc.addr).flat,'')||
					coalesce('/'||(acc.addr).f_slash,''))::varchar
 )::varchar as addr, ss.*, r.summ_lgt
  from 
(
select sss.id_paccnt, sss.id_grp_lgt, sum(sss.summ_lgt_all) as summ_lgt_all from 
(
     select s.id_paccnt, s.id_grp_lgt,  
                   sum( demand_lgt ) as demand_lgt_all,
                   sum( summ_lgt ) as summ_lgt_all,
    
                   sum(CASE WHEN coalesce(id_zone,0) = 0 and coalesce(h.ident,'') <> 'light' THEN demand_lgt END ) as demand_lgt0,
                   sum(CASE WHEN coalesce(id_zone,0) = 0 and coalesce(h.ident,'') <> 'light' THEN summ_lgt END) as summ_lgt0,

		   sum(CASE WHEN coalesce(id_zone,0) in (6,9) and coalesce(h.ident,'') <> 'light'  THEN demand_lgt END ) as demand_lgt1,
                   sum(CASE WHEN coalesce(id_zone,0) in (6,9) and coalesce(h.ident,'') <> 'light'  THEN summ_lgt END) as summ_lgt1,

		   sum(CASE WHEN coalesce(id_zone,0) in (7,10) and coalesce(h.ident,'') <> 'light'  THEN demand_lgt END ) as demand_lgt2,
                   sum(CASE WHEN coalesce(id_zone,0) in (7,10) and coalesce(h.ident,'') <> 'light'  THEN summ_lgt END) as summ_lgt2,

		   sum(CASE WHEN coalesce(id_zone,0) = 8  and coalesce(h.ident,'') <> 'light' THEN demand_lgt END ) as demand_lgt3,
                   sum(CASE WHEN coalesce(id_zone,0) = 8 and coalesce(h.ident,'')<> 'light' THEN summ_lgt END) as summ_lgt3,

		   sum(CASE WHEN coalesce(h.ident,'')= 'light' THEN demand_lgt END ) as demand_lgtl,
                   sum(CASE WHEN coalesce(h.ident,'')= 'light' THEN summ_lgt END) as summ_lgtl
                   
    from acm_lgt_summ_tbl as s 
    join acm_bill_tbl as b on (b.id_doc = s.id_doc)
    join lgi_group_tbl as g on (s.id_grp_lgt = g.id)
    left join lgi_calc_header_tbl as h on (h.id = g.id_calc)
    where b.mmgg = $p_mmgg and b.id_pref = 10 
    and (coalesce(s.demand_lgt,0) <>0 or coalesce(s.demand_add_lgt,0) <>0 )
     group by s.id_paccnt, s.id_grp_lgt 
  union all 
   select lg.id_paccnt, lg.id_grp_lgt, 
                   sum(lg.demand_val) as demand_lgt_all,
                   sum(lg.sum_val) as summ_lgt_all,
                   sum((CASE WHEN coalesce(zz.id_zone,0) = 0 THEN lg.demand_val END )) as demand_lgt0,
                   sum((CASE WHEN coalesce(zz.id_zone,0) = 0 THEN lg.sum_val END)) as summ_lgt0,

		   sum((CASE WHEN coalesce(zz.id_zone,0) in (6,9) THEN lg.demand_val END )) as demand_lgt1,
                   sum((CASE WHEN coalesce(zz.id_zone,0) in (6,9) THEN lg.sum_val END)) as summ_lgt1,

		   sum((CASE WHEN coalesce(zz.id_zone,0) in (7,10) THEN lg.demand_val END )) as demand_lgt2,
                   sum((CASE WHEN coalesce(zz.id_zone,0) in (7,10) THEN lg.sum_val END)) as summ_lgt2,

		   sum((CASE WHEN coalesce(zz.id_zone,0) = 8 THEN lg.demand_val END )) as demand_lgt3,
                   sum((CASE WHEN coalesce(zz.id_zone,0) = 8 THEN lg.sum_val END)) as summ_lgt3,0,0
                   
    from acm_dop_lgt_tbl as lg
    join lgi_group_tbl as g on(g.id = lg.id_grp_lgt)
    join (select m.id_paccnt, min(id_zone) as id_zone from clm_meter_zone_tbl as mz 
      join clm_meterpoint_tbl as m on (m.id = mz.id_meter) group by m.id_paccnt   ) as zz on (zz.id_paccnt = lg.id_paccnt)
        where mmgg = $p_mmgg
        group by lg.id_paccnt, lg.id_grp_lgt
) as sss
group by id_paccnt, id_grp_lgt
) as ss 
full  join ( select id_paccnt, id_grp_lgt , sum(summ_lgt) as summ_lgt 
    from rep_zvit_lgt_bill_tbl group by id_paccnt, id_grp_lgt) as r 
  on (ss.id_paccnt = r.id_paccnt and ss.id_grp_lgt = r.id_grp_lgt)

 join clm_paccnt_tbl as acc on (coalesce(ss.id_paccnt,r.id_paccnt) = acc.id)
 join clm_abon_tbl as ab on (ab.id = acc.id_abon) 
 join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class)
        where coalesce(r.summ_lgt,0) <>coalesce(ss.summ_lgt_all,0) $where ;";

    
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
 
            $abon = htmlspecialchars($row['abon']);
            
            if($res_code=='310')
                 $addr = $row['addr'];
            else
                 $addr = $row['town'].' '.$row['addr'];
            
            $book=$row['book'];  
            $code=$row['code'];
            
            $summ_lgt_txt = number_format_ua ($row['summ_lgt'], 2);
            $summ_lgt_all_txt = number_format_ua ($row['summ_lgt_all'], 2);
            
            echo_html( " <tr>
            <td>{$i}</td>
            <td class='c_t'>{$book}</td>
            <td class='c_t'>{$code}</td>
            <td class='c_t'>{$abon}</td>
            <td class='c_t'>{$addr}</td>
            <td class='c_i'>{$summ_lgt_all_txt}</td>
            <td class='c_i'>{$summ_lgt_txt}</td>
            </tr>
            ",$print_flag);

            $i++;
        }
    }
    
    eval("\$footer_text_eval = \"$footer_text\";");
    echo_html( $footer_text_eval);
    
}
//------------------------------------------------------------------------------
if ($oper=='abon_subs_heat')
{
    $p_mmgg1 = sql_field_val('dt_b', 'mmgg');
    $p_mmgg2 = sql_field_val('dt_e', 'mmgg');
    
    $mmgg1_str = ukr_date($_POST['dt_b'],0,1);
    $mmgg2_str = ukr_date($_POST['dt_e'],0,1);

    $mmgg1_str = "$mmgg1_str - $mmgg2_str";    
    
    $p_value = sql_field_val('sum_value', 'numeric');
    $p_id_town = sql_field_val('addr_town', 'int');
    $p_id_sector = sql_field_val('id_sector', 'int');
    $p_book = sql_field_val('book', 'string');
    $p_id_region = sql_field_val('id_region', 'int'); 
    $p_id_tar = sql_field_val('id_gtar', 'int');    
    $p_id_paccnt = sql_field_val('id_paccnt', 'int');

    if (isset($_POST['town_detal']))
        $p_town_detal = trim($_POST['town_detal']);
    else 
        $p_town_detal =0;

    $params_caption="";
    $where = '  ';

    if ($p_id_town!='null')
    {
        $where.= " and ( adr.id_town = $p_id_town or adr.id_parent = $p_id_town )";
    }

    if ($p_id_paccnt!='null')
    {
        $where.= " and c.id = $p_id_paccnt ";
    }
    else
    {
      if ($p_book!='null')
      {
        $where.= " and c.book = $p_book ";
        $params_caption .= " книга : $p_book ";
      }
        
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
    
    if ($p_id_tar!='null')
    {
        $where.= " and c.id_gtar = $p_id_tar ";

        if ((isset($_POST['gtar']))&&($_POST['gtar']!=''))
            $params_caption .= ' Тарифна група : '. trim($_POST['gtar']);
        
    }    
    
    if ($p_value !='null')
    {
        $where.= " and exists (select id from acm_subs_tbl as sb where sb.id_paccnt = c.id and sb.norma_subskwt >= $p_value ) ";
        $params_caption="що мають норму по субсидії більше $p_value";
    }
    else
    {
        $where.= " and c.id_gtar in (4,6,14) ";
        $params_caption="що мають тариф електроопалення";
    }
    
    eval("\$header_text = \"$header_text\";");
    echo_html( $header_text);
    
    $np=0;
    $print_flag = 1;
    $print_h_flag =1;
  
    $SQL = "select c.id, c.book, c.code, (c.book||'/'||c.code) as bookcode, 
        to_char(p.mmgg, 'DD.MM.YYYY') as mmgg_txt,p.mmgg, 
	adr.town, adr.street, address_print(c.addr) as house,
	(a.last_name||' '||coalesce(substr(a.name,1,1),'')||'.'||coalesce(substr(a.patron_name,1,1),''))||'.'::varchar as abon,
	c.id_gtar, t.sh_nm as tarname,
      	p.value,  p.idk_doc, di.name as doc, s.subs_month, s.subs_all,  s.val_month, 
        to_char(s.dt_b, 'DD.MM.YYYY') as dt_b_txt, s.dt_b,
        to_char(s.dt_e, 'DD.MM.YYYY') as dt_e_txt, s.dt_e,
        s.norma_subskwt, coalesce(s.ob_pay,0)+coalesce(s.ob_zon,0) as ob_pay,
        ('0'||substring(c.code FROM '[0-9]+'))::int as int_code,
        ('0'||substring(c.book FROM '[0-9]+'))::int as int_book   

       	from acm_pay_tbl as p 
	join dci_doc_tbl as di on (di.id = p.idk_doc)    
	join clm_paccnt_h as c on (c.id = p.id_paccnt)
	join (select id, max(dt_b) as dt_b from clm_paccnt_h  where 
	((dt_b < ($p_mmgg2::date+'1 month'::interval) and dt_e is null)
		or 
		tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg1::timestamp::abstime,($p_mmgg2::date+'1 month - 1 day'::interval)::timestamp::abstime))
		)    
	group by id order by id) as c2 
	on (c.id = c2.id and c.dt_b = c2.dt_b)
	join clm_abon_tbl as a on (a.id = c.id_abon) 
	join aqi_grptar_tbl as t on (t.id = c.id_gtar)
	join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class)
       	left join acm_subs_tbl as s on (s.id = p.id_subs)
       	where p.mmgg >= $p_mmgg1::date and p.mmgg <= $p_mmgg2::date
       	 and p.id_pref = 10 
        and p.idk_doc in (110,111,193)
        and c.archive =0 
    $where
    order by int_book, int_code, p.mmgg,  s.dt_b ;";

    $current_abon='';
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        //throw new Exception("Try to save file $rows_count ....");    
        
        $cnt_headers = 0;
        $nm=1;

        $sum_subs_summ=0;
        $sum_abon_subs_summ=0;
        
            
        while ($row = pg_fetch_array($result)) {
            
            if ($current_abon!=$row['bookcode'])
            {

                if ($current_abon!='')
                {
                    
                        $sum_abon_subs_summ_str = number_format_ua($sum_abon_subs_summ,2);
                    
                        echo_html( "
                        <tr class='table_footer'>
                        <td colspan='4'>Всього по {$current_abon}</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td class='c_n'>{$sum_abon_subs_summ_str}</td>
                        <td colspan='7'>&nbsp;</td>
                        </tr>  ",1);
                    
                        $sum_abon_subs_summ=0;
                        $nm=1;   
                }
               $current_abon=$row['bookcode'];
             }  
                
             $addr = htmlspecialchars($row['town'].' '.$row['street'].' '.$row['house']);
                
             $book =$row['book'];
             $code =$row['code'];
                
             $abon = htmlspecialchars($row['abon']);
             $tar= htmlspecialchars($row['tarname']);
             $sum_subs_summ_str = number_format_ua($row['value'],2);
             
             $subs_month_str = number_format_ua($row['subs_month'],2);
             $subs_all_str = number_format_ua($row['subs_all'],2);
             $ob_pay_str = number_format_ua($row['ob_pay'],2);
                
             echo_html( "
                    <tr class='table_str'>
                     <td class='c_t'>{$book}</td>
                     <td class='c_t'>{$code}</td>                     
                     <td>{$addr}</td>
                     <td>{$abon}</td>            
                     <td class='c_t'>{$tar}</td>
                     <td class='c_t'>{$row['mmgg_txt']}</td>
                     <td class='c_t'>{$row['doc']}</td>
                     <td class='c_n'>{$sum_subs_summ_str}</td>                     
                     <td class='c_n'>{$subs_all_str}</td>                     
                     <td class='c_n'>{$subs_month_str}</td>                     
                     <td class='c_i'>{$row['val_month']}</td>

                     <td class='c_t'>{$row['dt_b_txt']}</td>
                     <td class='c_t'>{$row['dt_e_txt']}</td>
                     
                     <td class='c_n'>{$row['norma_subskwt']}</td>
                     <td class='c_n'>{$row['ob_pay']}</td>
                    </tr>  ",1);        
            
                     $nm++;

            $sum_subs_summ+=$row['value'];
            $sum_abon_subs_summ+=$row['value'];
            
        }
    }
      
      $sum_abon_subs_summ_str = number_format_ua($sum_abon_subs_summ,2);
      
      echo_html( "
        <tr class='table_footer'>
         <td colspan='4'>Всього по {$current_abon}</td>
         <td>&nbsp;</td>
         <td>&nbsp;</td>
         <td>&nbsp;</td>
         <td class='c_n'>{$sum_abon_subs_summ_str}</td>
         <td colspan='7'>&nbsp;</td>
        </tr>  ",1);

      $sum_subs_summ_str = number_format_ua($sum_subs_summ,2);
          
    eval("\$footer_text_eval = \"$footer_text\";");
    echo_html( $footer_text_eval);
    
}

//-----------------------------------------------------------------------------
if ($oper=='subs_recalc_abon')
{
    $p_mmgg1 = sql_field_val('dt_b', 'mmgg');
    $p_mmgg2 = sql_field_val('dt_e', 'mmgg');
    
    
    $mmgg1_str = ukr_date($_POST['dt_b'],0,1);
    $mmgg2_str = ukr_date($_POST['dt_e'],0,1);

    $mmgg1_str = "$mmgg1_str - $mmgg2_str";    

    
    $p_id_town = sql_field_val('addr_town', 'int');
    $p_id_sector = sql_field_val('id_sector', 'int');
    $p_book = sql_field_val('book', 'string');
    $p_id_region = sql_field_val('id_region', 'int'); 
    $p_id_tar = sql_field_val('id_gtar', 'int');    
    $p_id_paccnt = sql_field_val('id_paccnt', 'int');

    if (isset($_POST['town_detal']))
        $p_town_detal = trim($_POST['town_detal']);
    else 
        $p_town_detal =0;

    $params_caption="";
    $where = '  ';

    if ($p_id_town!='null')
    {
        $where.= " and ( adr.id_town = $p_id_town or adr.id_parent = $p_id_town )";
    }

    if ($p_id_paccnt!='null')
    {
        $where.= " and c.id = $p_id_paccnt ";
    }
    else
    {
      if ($p_book!='null')
      {
        $where.= " and c.book = $p_book ";
        $params_caption .= " книга : $p_book ";
      }
        
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
    
    if ($p_id_tar!='null')
    {
        $where.= " and c.id_gtar = $p_id_tar ";

        if ((isset($_POST['gtar']))&&($_POST['gtar']!=''))
            $params_caption .= ' Тарифна група : '. trim($_POST['gtar']);
        
    }    
    
    
    $SQL = "select rep_subs_recalc_fun($mmgg_current, $p_mmgg1,$p_mmgg2 );";
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    
   
    $np=0;
    $print_flag = 1;
    $print_h_flag =1;
  
    $SQL = "select s.id_paccnt, c.book, c.code, (c.book||'/'||c.code) as bookcode, 
	adr.town, adr.street, address_print(c.addr) as house,
	(ab.last_name||' '||coalesce(substr(ab.name,1,1),'')||'.'||coalesce(substr(ab.patron_name,1,1),''))||'.'::varchar as abon,
	s.*, t.sh_nm as tarname, 
        ('0'||substring(c.code FROM '[0-9]+'))::int as int_code,
        ('0'||substring(c.book FROM '[0-9]+'))::int as int_book   
        from rep_subs_recalc_tbl as s
        join clm_paccnt_tbl c on (c.id=s.id_paccnt)
        join clm_abon_tbl as ab on (ab.id = c.id_abon) 
        join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class)
        join aqi_grptar_tbl as t on (t.id =  s.id_gtar )
        where s.subs_return >0 and s.mmgg = $mmgg_current
        $where
	order by int_book, int_code;";

    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        //throw new Exception("Try to save file $rows_count ....");    
        
        $cnt_headers = 0;
        $nm=1;

        $sum_subs_summ=0;
        $sum_abon_subs_summ=0;
        
            
        while ($row = pg_fetch_array($result)) {
            
             $addr = htmlspecialchars($row['town'].' '.$row['street'].' '.$row['house']);
             $id_paccnt = $row['id_paccnt'];
             $book =$row['book'];
             $code =$row['code'];
             $bookcode = "{$book}/{$code}";
                
             $price150 = $row['bonus_sum'];
             $subs_ret_summ = $row['subs_return'];
             
             $abn = htmlspecialchars($row['abon']);
             
             eval("\$header_text_eval = \"$header_text\";");
             echo_html( $header_text_eval);
             
                $SQL_abon = "select mmgg_calc, 
                    date_part('month',mmgg_calc) as mmgg_month, 
                    date_part('year', mmgg_calc) as mmgg_year,
                    CASE WHEN enabled = 1 then bill_sum ELSE 0 END as bill_sum,
                    subs_current, ob_pay
                    from rep_subs_recalc_month_tbl
                    where id_paccnt = $id_paccnt
                    and mmgg = $mmgg_current
                    order by mmgg_calc;";
                //echo $SQL_abon;
                $result_abon = pg_query($Link, $SQL_abon) or die("SQL Error: " . pg_last_error($Link) . $SQL_abon);
                if ($result_abon) {
                    
                   $sum_subs_summ = 0;
                   $sum_bill_summ = 0;
                   $ob_pay_summ = 0;
                   
                   while ($row_abon = pg_fetch_array($result_abon)) { 
                       
                       $mmgg_str = ukr_month($row_abon['mmgg_month'],0).' '.$row_abon['mmgg_year'];
                       
                       $value_bill = number_format_ua ($row_abon['bill_sum'],2);
                       $value_subs = number_format_ua ($row_abon['subs_current'],2);
                       $ob_pay = number_format_ua ($row_abon['ob_pay'],2);
                       
                       
                       echo_html( "
                            <tr class='table_str'>
                             <td class='c_t'>{$mmgg_str}</td>                     
                             <td class='c_n'>{$value_subs}</td>
                             <td class='c_n'>{$ob_pay}</td>
                             <td class='c_n'>{$value_bill}</td>
                             <td class='c_t'>&nbsp;</td>
                             <td class='c_t'>&nbsp;</td>
                            </tr>  ",1);        
            
                            $sum_subs_summ+=$row_abon['subs_current'];
                            $sum_bill_summ+=$row_abon['bill_sum'];
                            $ob_pay_summ+=  $row_abon['ob_pay'];

                   }
                    
                }
               
                $subs_summ_str = number_format_ua ($sum_subs_summ,2);
                $obpay_summ_str = number_format_ua ($ob_pay_summ,2);
                $bill_summ_str = number_format_ua ($sum_bill_summ,2);
                $price150_str = number_format_ua ($price150,2);
                
                if ($subs_ret_summ==0) 
                {
                    $subs_ret_summ_str = "0.00";
                }
                else
                {
                  $subs_ret_summ_str = number_format_ua ($subs_ret_summ,2);
                }
                
                $subs_ret_summ_all_str = $subs_ret_summ_str;
                $executor = $name_executor;
                
                eval("\$footer_text_eval = \"$footer_text\";");
                echo_html( $footer_text_eval);

        }
    }
      
         
    
}
//----------------------------------------------------
if ($oper=='check_ind_calc_greater') 
{
    $p_mmgg = sql_field_val('dt_b', 'mmgg');
    $period = trim($_POST['period_str']);
    
    $p_book = sql_field_val('book', 'string');
    $p_id_town = sql_field_val('addr_town', 'int');
    $p_id_tar = sql_field_val('id_gtar', 'int');    

    $p_id_region = sql_field_val('id_region', 'int');
    $p_id_sector = sql_field_val('id_sector', 'int');
    

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
        $where.= " and ( adr.id_town = $p_id_town or adr.id_parent = $p_id_town )";
    }

    if ($p_id_tar!='null')
    {
        $where.= "and acc.id_gtar = $p_id_tar ";
    }

    
    
    if ($p_id_region!='null')
    {
        
        $where.= "and exists (select rs.id from 
            prs_runner_paccnt as rp 
            join prs_runner_sectors as rs on (rs.id = rp.id_sector)
            where rp.id_paccnt = acc.id and rs.id_region =  $p_id_region ) "; 
        
        //$where.= " and rs.id_region =  $p_id_region ";

        $SQL_H="SELECT name from cli_region_tbl where id = $p_id_region ;";
    
        $result = pg_query($Link, $SQL_H) or die("SQL Error: " . pg_last_error($Link) . $SQL_H);
        $row = pg_fetch_array($result);
        
        $params_caption.= $row['name'];        
       
    }
    
    if ($p_id_sector!='null')
    {
       $where.= "and exists (select id from prs_runner_paccnt as rp 
          where rp.id_paccnt = acc.id and rp.id_sector = $p_id_sector ) ";
        
    }

    
    eval("\$header_text = \"$header_text\";");
    echo_html( $header_text);
    
   
    
    $SQL = "select acc.book, acc.code, 
  (ab.last_name||' '||coalesce(ab.name,'')||' '||coalesce(ab.patron_name,''))::varchar as abon,
  adr.town, (adr.street||' '||
        (coalesce('буд.'||(acc.addr).house||'','')||
		coalesce('/'||(acc.addr).slash||' ','')|| 
			coalesce(' корп.'||(acc.addr).korp||'','')||
				coalesce(', кв. '||(acc.addr).flat,'')||
					coalesce('/'||(acc.addr).f_slash,''))::varchar
    )::varchar as addr,

        (CASE WHEN sw.action = 1 then 'Вiдкл. ' WHEN sw.action = 2 then 'Попер. ' END || to_char(sw.dt_action, 'DD.MM.YYYY'))::varchar as action,
            acc.note , ap.name as add_param , i.*,  z.nm as zone, it.name as indic_type,
            i2.value_diff as demand_abon, i2.dat_ind as dat_ind_abon
           from  clm_paccnt_tbl as acc 
           join clm_abon_tbl as ab on (ab.id = acc.id_abon)         
           join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class)

           join (select i.id_paccnt, i.num_eqp, i.id_zone , i.value, 
           to_char(i.dat_ind, 'DD.MM.YYYY') as dat_ind , i.id_operation, i.value_diff
              from acm_indication_tbl as i 
              where i.mmgg = $p_mmgg and i.id_operation = 23
            ) as i on (i.id_paccnt = acc.id)
           join eqk_zone_tbl as z on (z.id = i.id_zone ) 
           join cli_indic_type_tbl as it on (it.id = i.id_operation)
           left join (select i.id_paccnt, i.num_eqp, i.id_zone , sum(i.value_diff) as value_diff, 
              to_char(max(i.dat_ind), 'DD.MM.YYYY') as dat_ind
              from acm_indication_tbl as i 
              where i.mmgg = $p_mmgg and i.id_operation <> 23
              group by i.id_paccnt, i.num_eqp, i.id_zone
            ) as i2 on (i2.id_paccnt = acc.id and i.num_eqp = i2.num_eqp and i.id_zone = i2.id_zone)    
left join (
 select csw.id_paccnt, csw.dt_action, csw.action
	from clm_switching_tbl as csw 
	join (select id_paccnt, max(dt_action) as maxdt from clm_switching_tbl where action in (1,2) group by id_paccnt) as csdt 
	on (csw.id_paccnt = csdt.id_paccnt and csw.dt_action = csdt.maxdt) 
	left join clm_switching_tbl as cswc on (csw.id_paccnt = cswc.id_paccnt and cswc.dt_action >= csw.dt_action and ((cswc.action =3 and csw.action =1) or (cswc.action =4  and csw.action =2) ) ) 
	where csw.action in (1,2) and cswc.id_paccnt is null 
) as sw on (sw.id_paccnt = acc.id)
left join cli_addparam_tbl as ap on (ap.id = acc.id_cntrl)         
 where acc.archive = 0 
 and i.value_diff> coalesce(i2.value_diff,0)   $where
 order by  ('0'||substring(acc.book FROM '[0-9]+'))::int ,
           ('0'||substring(acc.code FROM '[0-9]+'))::int;";

    
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
 
            $abon = htmlspecialchars($row['abon']);
            $note = htmlspecialchars($row['note']);
            
            if($res_code=='310')
                 $addr = $row['addr'];
            else
                 $addr = $row['town'].' '.$row['addr'];
            
            $book=$row['book']; 
            $code=$row['code'];
            
            $value_txt = number_format_ua ($row['value_diff'], 0);
            $value_abon_txt = number_format_ua ($row['demand_abon'], 0);
            
            // <tr >
            echo_html( " <tr>
            <td>{$i}</td>
            <td class='c_t'>{$book}</td>
            <td class='c_t'>{$code}</td>
            <td class='c_t'>{$abon}</td>
            <td class='c_t'>{$addr}</td>
            
            <td class='c_i'>{$value_abon_txt}</td>            
            <td class='c_t'>{$row['dat_ind_abon']}</td>
            
            <td class='c_i'>{$value_txt}</td>
            
            <td class='c_t'>{$row['num_eqp']}</td>
            <td class='c_t'>{$row['zone']}</td>
            <td class='c_t'>{$row['action']}</td>
            <td class='c_t'>{$note}</td>
            <td class='c_t'>{$row['add_param']}</td>
            </tr>
            ",$print_flag);

            $i++;
        }
        
    }
    
    eval("\$footer_text_eval = \"$footer_text\";");
    echo_html( $footer_text_eval);
    
}
//------------------------------------------------------------------------------

if ($oper=='autocancellog') 
{
    $p_mmgg = sql_field_val('dt_b', 'mmgg');
    $period = trim($_POST['period_str']);
    
    $p_id_region = sql_field_val('id_region', 'int');

    $params_caption ='';    
        
    $where = ' ';
    
    if ((isset($_POST['note']))&&($_POST['note']!=''))
    {
        $where.= $_POST['note'];
        $params_caption .= $_POST['note'];
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
    
    
    eval("\$header_text = \"$header_text\";");
    echo_html( $header_text);
    
   
    
    $SQL = " select t.id,acc.book, acc.code,  
    (adr.adr||' '||
    (coalesce('буд.'||(acc.addr).house||'','')||
		coalesce('/'||(acc.addr).slash||' ','')||
			coalesce(' корп.'||(acc.addr).korp||'','')||
				coalesce(', кв. '||(acc.addr).flat,'')||
					coalesce('/'||(acc.addr).f_slash,''))::varchar
    )::varchar as addr,
    (c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon,
    lg.name as grp_lgt,lg.ident, lg.alt_code,
    t.family_cnt, t.family_cnt_new, 
    to_char(t.dt_oper, 'DD.MM.YYYY') as dt_oper_txt,
    t.note,
    to_char(t.dt_birth, 'DD.MM.YYYY') as dt_birth_txt,
    t.dt_child_end, 
    to_char(l.dt_start, 'DD.MM.YYYY') as dt_start_txt,    
    to_char(l.dt_end,  'DD.MM.YYYY') as dt_end_txt,        
    tar_old.sh_nm as tar_old, tar_new.sh_nm as tar_new, 
   ('0'||substring(acc.code FROM '[0-9]+'))::int as int_code,
   ('0'||substring(acc.book FROM '[0-9]+'))::int as int_book   
    from lgm_autocancellog_tbl as t
    join clm_paccnt_tbl as acc on (acc.id = t.id_paccnt)
    join clm_abon_tbl as c on (c.id = acc.id_abon) 
    join adt_addr_tbl as adr on (adr.id = (acc.addr).id_class)
    left join lgm_abon_tbl as l on (l.id = t.id_lgt)
    left join lgi_group_tbl as lg on (lg.id = t.id_grp_lgt) 
    left join aqi_grptar_tbl as tar_old on (tar_old.id = t.id_old_tar)
    left join aqi_grptar_tbl as tar_new on (tar_new.id = t.id_new_tar)
    where t.mmgg = $p_mmgg 
    $where
    order by int_book, int_code, dt_oper , note";

    
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
 
            $abon = htmlspecialchars($row['abon']);
            $note = htmlspecialchars($row['note']);
            $addr = htmlspecialchars($row['addr']);
            $lgt = htmlspecialchars($row['grp_lgt']);
            
            $book=$row['book']; 
            $code=$row['code'];
            
            
            // <tr >
            echo_html( " <tr>
            <td>{$i}</td>
            <td class='c_t'>{$book}</td>
            <td class='c_t'>{$code}</td>
            <td class='c_t'>{$abon}</td>
            <td class='c_t'>{$addr}</td>
            <td class='c_t'>{$lgt}</td>
            <td class='c_t'>{$row['dt_start_txt']}</td>
            <td class='c_t'>{$row['dt_end_txt']}</td>

            <td class='c_t'>{$row['dt_oper_txt']}</td>
            <td class='c_t'>{$note}</td>
            
            <td class='c_i'>{$row['family_cnt']}</td>
            <td class='c_i'>{$row['family_cnt_new']}</td>
            
            <td class='c_t'>{$row['dt_birth_txt']}</td>
            <td class='c_t'>{$row['tar_old']}</td>
            <td class='c_t'>{$row['tar_new']}</td>

            </tr>
            ",$print_flag);

            $i++;
        }
        
    }
    
    eval("\$footer_text_eval = \"$footer_text\";");
    echo_html( $footer_text_eval);
    
}
//-----------------------------------------------------------------------------
if (($oper=='abon_subs_heat_recalc')||($oper=='abon_subs_heat_recalc_only'))
{
    $p_mmgg1 = sql_field_val('dt_b', 'mmgg');
    $p_mmgg2 = sql_field_val('dt_e', 'mmgg');
    
    $mmgg1_str = ukr_date($_POST['dt_b'],0,1);
    $mmgg2_str = ukr_date($_POST['dt_e'],0,1);

    $mmgg1_str = "$mmgg1_str - $mmgg2_str";    

    
    $p_id_town = sql_field_val('addr_town', 'int');
    $p_id_sector = sql_field_val('id_sector', 'int');
    $p_book = sql_field_val('book', 'string');
    $p_id_region = sql_field_val('id_region', 'int'); 
    $p_id_tar = sql_field_val('id_gtar', 'int');    
    $p_id_paccnt = sql_field_val('id_paccnt', 'int');

    if (isset($_POST['town_detal']))
        $p_town_detal = trim($_POST['town_detal']);
    else 
        $p_town_detal =0;

    $params_caption="";
    $where = '  ';

    if ($p_id_town!='null')
    {
        $where.= " and ( adr.id_town = $p_id_town or adr.id_parent = $p_id_town )";
    }

    if ($p_id_paccnt!='null')
    {
        $where.= " and c.id = $p_id_paccnt ";
    }
    else
    {
      if ($p_book!='null')
      {
        $where.= " and c.book = $p_book ";
        $params_caption .= " книга : $p_book ";
      }
        
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
    
    if ($p_id_tar!='null')
    {
        $where.= " and c.id_gtar = $p_id_tar ";

        if ((isset($_POST['gtar']))&&($_POST['gtar']!=''))
            $params_caption .= ' Тарифна група : '. trim($_POST['gtar']);
        
    }    

    if ($oper=='abon_subs_heat_recalc_only')
    {
       $where.=" and subs_return >0 " ;
    }        
    
    $np=0;
    $print_flag = 1;
    
    $SQL = "select rep_subs_recalc_fun($mmgg_current, $p_mmgg1,$p_mmgg2 );";
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    
        
    eval("\$header_text = \"$header_text\";");
    echo_html( $header_text);
    
    $SQL = "select c.book,c.code,
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
    s.*,  
    to_char(s.opal_start_date, 'DD.MM.YYYY') as opal_start_date_txt,
    to_char(s.opal_end_date, 'DD.MM.YYYY') as opal_end_date_txt,
    to_char(s.mmgg1, 'DD.MM.YYYY') as mmgg_start_txt,
    to_char(s.mmgg2, 'DD.MM.YYYY') as mmgg_end_txt,
    
    gt.nm as tar_name
  from rep_subs_recalc_tbl as s
  join clm_paccnt_tbl c on (c.id=s.id_paccnt)
  join clm_abon_tbl as ab on (ab.id = c.id_abon) 
  join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class)
  left join aqi_grptar_tbl as gt on (gt.id =  s.id_gtar )
  where s.mmgg = $mmgg_current
  $where  
  order by  int_book,c.book,int_code,c.code; ";

    //echo $SQL;
    //throw new Exception(json_encode($SQL));
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        $i=0;
        $sum_subs_return=0;
        
        $sum_bonus=0;
        $bill_sum=0;
        $subs_sum_sum=0;        
        
        while ($row = pg_fetch_array($result)) {

            $np++;
            
            if ((($np> ($page_num-1)* $page_size )&& ($np<= $page_num* $page_size )) || ($show_pager ==0))
                $print_flag = 1;
            else
                $print_flag = 0;
            
            $abon = htmlspecialchars($row['abon']);
            if($res_code=='310')
                 $addr = htmlspecialchars($row['addr']);
            else
                 $addr = htmlspecialchars($row['town'].' '.$row['addr']);
            
            
            $book=$row['book'];
            $code=$row['code'];

            $bill_sum_txt = number_format_ua ($row['bill_sum'],2);
            $bill_sum_msm_txt = number_format_ua ($row['bill_msm_sum'],2);
            
            $bill_sum_all_txt = number_format_ua ($row['bill_sum']+$row['bill_msm_sum'],2);
            
            $subs_all_txt = number_format_ua ($row['subs_all'],2);
            $subs_current_txt = number_format_ua ($row['subs_current'],2);
            $subs_old_txt = number_format_ua ($row['subs_old'],2);
            $subs_recalc_txt = number_format_ua ($row['subs_recalc'],2);
            $subs_recalc_old_txt = number_format_ua ($row['subs_recalc_old'],2);
            $subs_manual_txt = number_format_ua ($row['subs_manual'],2);
            $subs_manual_old_txt = number_format_ua ($row['subs_manual_old'],2);
            $subs_msm_txt = number_format_ua ($row['subs_msm_sum'],2);
            $norm_kvt_txt = number_format_ua ($row['kvt_norm'],2);
            
            $bonus_sum_txt = number_format_ua ($row['bonus_sum'],2);
            $subs_return_txt = number_format_ua ($row['subs_return'],2);

            $subs_sum=$row['subs_current']+$row['subs_recalc']+$row['subs_manual']+$row['subs_msm_sum'];
            $subs_sum_txt = number_format_ua ($subs_sum,2);
            
            echo_html( "
            <tr >
            <td>{$i}</td>            
            <td class='c_t'>$book</td>
            <td>$code</td>
            <td>$abon</td>
            <td>$addr</td>
            <td class='c_t'>{$row['tar_name']}</td>
            <td class='c_n'>{$bill_sum_all_txt}</td>
            <td class='c_n'>{$bill_sum_txt}</td>
            <td class='c_n'>{$bill_sum_msm_txt}</td>
            
            <td class='c_n'>{$subs_all_txt}</td>
            <td class='c_n'>{$subs_sum_txt}</td>
            <td class='c_n'>{$subs_current_txt}</td>
            <td class='c_n'>{$subs_recalc_txt}</td>
            <td class='c_n'>{$subs_manual_txt}</td>
            <td class='c_n'>{$subs_msm_txt}</td>
            
            <td class='c_n'>{$subs_old_txt}</td>
            <td class='c_n'>{$subs_recalc_old_txt}</td>
            <td class='c_n'>{$subs_manual_old_txt}</td>
            
            <td class='c_i'>{$row['subs_month_cnt']}</td> 
            <td class='c_i'>{$row['subs_bad']}</td> 
            
            <td class='c_n'>{$bonus_sum_txt}</td>
            <td class='c_n' style='font-weight: bold;'>{$subs_return_txt}</td>
            <td class='c_n'>{$norm_kvt_txt}</td>
            <td class='c_i'>{$row['flag_recalc']}</td> 
            <td class='c_t'>{$row['opal_start_date_txt']}</td>
            <td class='c_t'>{$row['opal_end_date_txt']}</td>

            <td class='c_t'>{$row['mmgg_start_txt']}</td>
            <td class='c_t'>{$row['mmgg_end_txt']}</td>
            
            </tr>  ",$print_flag); 

            $i++;
            
            $sum_subs_return += $row['subs_return'];
            
            $sum_bonus+=$row['bonus_sum'];
            $bill_sum+=$row['bill_sum']+$row['bill_msm_sum'];
            $subs_sum_sum+=$subs_sum;
            
            
        }
        
    }

    $sum_subs_return_str = number_format_ua ($sum_subs_return,2);

    $sum_bonus_str = number_format_ua ($sum_bonus,2);
    $bill_sum_str = number_format_ua ($bill_sum,2);
    $subs_sum_str = number_format_ua ($subs_sum_sum,2);    
    
    eval("\$footer_text_eval = \"$footer_text\";");
    echo_html( $footer_text_eval,$print_flag);
   
    if ($print_flag==0) echo "</tbody> </table> ";
   
}
//------------------------------------------------------------------------------
if ($oper=='abon_subs_heat_list')
{
    $p_mmgg1 = sql_field_val('dt_b', 'mmgg');
    $p_mmgg2 = sql_field_val('dt_e', 'mmgg');
    
    $mmgg1_str = ukr_date($_POST['dt_b'],0,1);
    $mmgg2_str = ukr_date($_POST['dt_e'],0,1);

    $mmgg1_str = "$mmgg1_str - $mmgg2_str";    

    
    $p_id_town = sql_field_val('addr_town', 'int');
    $p_id_sector = sql_field_val('id_sector', 'int');
    $p_book = sql_field_val('book', 'string');
    $p_id_region = sql_field_val('id_region', 'int'); 
    $p_id_tar = sql_field_val('id_gtar', 'int');    
    $p_id_paccnt = sql_field_val('id_paccnt', 'int');

    if (isset($_POST['town_detal']))
        $p_town_detal = trim($_POST['town_detal']);
    else 
        $p_town_detal =0;

    $params_caption="";
    $where = '  ';

    if ($p_id_town!='null')
    {
        $where.= " and ( adr.id_town = $p_id_town or adr.id_parent = $p_id_town )";
    }

    if ($p_id_paccnt!='null')
    {
        $where.= " and c.id = $p_id_paccnt ";
    }
    else
    {
      if ($p_book!='null')
      {
        $where.= " and c.book = $p_book ";
        $params_caption .= " книга : $p_book ";
      }
        
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
    
    if ($p_id_tar!='null')
    {
        $where.= " and c.id_gtar = $p_id_tar ";

        if ((isset($_POST['gtar']))&&($_POST['gtar']!=''))
            $params_caption .= ' Тарифна група : '. trim($_POST['gtar']);
        
    }    
    
    $np=0;
    $print_flag = 1;
    
    $SQL = "select rep_subs_recalc_fun($mmgg_current, $p_mmgg1,$p_mmgg2 );";
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    
        
    eval("\$header_text = \"$header_text\";");
    echo_html( $header_text);
    
    $SQL = "select c.book,c.code,
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
    s.*,  
    to_char(s.opal_start_date, 'DD.MM.YYYY') as opal_start_date_txt,
    to_char(s.opal_end_date, 'DD.MM.YYYY') as opal_end_date_txt,
    gt.nm as tar_name
  from rep_subs_recalc_tbl as s
  join clm_paccnt_tbl c on (c.id=s.id_paccnt)
  join clm_abon_tbl as ab on (ab.id = c.id_abon) 
  join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class)
  left join aqi_grptar_tbl as gt on (gt.id =  s.id_gtar )
  where s.subs_return >0 and s.mmgg = $mmgg_current
  $where  
  order by  int_book,c.book,int_code,c.code; ";

    //echo $SQL;
    //throw new Exception(json_encode($SQL));
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        $i=1;
        $sum_subs_return=0;
        $sum_bonus=0;
        $bill_sum=0;
        $subs_sum=0;
        
        while ($row = pg_fetch_array($result)) {

            $np++;
            
            if ((($np> ($page_num-1)* $page_size )&& ($np<= $page_num* $page_size )) || ($show_pager ==0))
                $print_flag = 1;
            else
                $print_flag = 0;
            
            $abon = htmlspecialchars($row['abon']);
            if($res_code=='310')
                 $addr = htmlspecialchars($row['addr']);
            else
                 $addr = htmlspecialchars($row['town'].' '.$row['addr']);
            
            
            $book=$row['book']."/".$code=$row['code'];
            
            $subs_return_txt = number_format_ua ($row['subs_return'],2);

            echo_html( "
            <tr >
            <td>{$i}</td>            
            <td>$abon</td>            
            <td class='c_t'>$book</td>
            <td>$addr</td>
            <td class='c_n'>{$subs_return_txt}</td>
            <td class='c_t'>&nbsp;</td>
            </tr>  ",$print_flag); 

            $i++;
            
            $sum_subs_return += $row['subs_return'];
            $sum_bonus+=$row['bonus_sum'];
            $bill_sum+=$row['bill_sum']+$row['bill_msm_sum'];
            $subs_sum+=$row['subs_current']+$row['subs_recalc']+$row['subs_manual']+$row['subs_msm_sum'];
            
        }
        
    }

    $sum_subs_return_str = number_format_ua ($sum_subs_return,2);
    
    $sum_bonus_str = number_format_ua ($sum_bonus,2);
    $bill_sum_str = number_format_ua ($bill_sum,2);
    $subs_sum_str = number_format_ua ($subs_sum,2);
    $executor=$name_executor;
    eval("\$footer_text_eval = \"$footer_text\";");
    echo_html( $footer_text_eval,$print_flag);
   
    if ($print_flag==0) echo "</tbody> </table> ";
   
}
//------------------------------------------------------------------------------
if ($oper=='abon_subs_heat150_list')
{
    $p_mmgg1 = sql_field_val('dt_b', 'mmgg');
    $p_mmgg2 = sql_field_val('dt_e', 'mmgg');
    
    $mmgg1_str = ukr_date($_POST['dt_b'],0,1);
    $mmgg2_str = ukr_date($_POST['dt_e'],0,1);

    $mmgg1_str = "$mmgg1_str - $mmgg2_str";    

    
    $p_id_town = sql_field_val('addr_town', 'int');
    $p_id_sector = sql_field_val('id_sector', 'int');
    $p_book = sql_field_val('book', 'string');
    $p_id_region = sql_field_val('id_region', 'int'); 
    $p_id_tar = sql_field_val('id_gtar', 'int');    
    $p_id_paccnt = sql_field_val('id_paccnt', 'int');

    if (isset($_POST['town_detal']))
        $p_town_detal = trim($_POST['town_detal']);
    else 
        $p_town_detal =0;

    $params_caption="";
    $where = '  ';

    if ($p_id_town!='null')
    {
        $where.= " and ( adr.id_town = $p_id_town or adr.id_parent = $p_id_town )";
    }

    if ($p_id_paccnt!='null')
    {
        $where.= " and c.id = $p_id_paccnt ";
    }
    else
    {
      if ($p_book!='null')
      {
        $where.= " and c.book = $p_book ";
        $params_caption .= " книга : $p_book ";
      }
        
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
    
    if ($p_id_tar!='null')
    {
        $where.= " and c.id_gtar = $p_id_tar ";

        if ((isset($_POST['gtar']))&&($_POST['gtar']!=''))
            $params_caption .= ' Тарифна група : '. trim($_POST['gtar']);
        
    }    
    
    $np=0;
    $print_flag = 1;
    
    //$SQL = "select rep_subs_recalc_fun($mmgg_current, $p_mmgg1,$p_mmgg2 );";
    //$result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);

    $SQL = "update rep_subs_recalc_tbl set bonus_sum = 135 ;";
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    
        
    eval("\$header_text = \"$header_text\";");
    echo_html( $header_text);
    
    $SQL = "select c.book,c.code,
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
    s.*,  
    least(s.bonus_sum,s.subs_return) as sum_150,
    to_char(s.opal_start_date, 'DD.MM.YYYY') as opal_start_date_txt,
    to_char(s.opal_end_date, 'DD.MM.YYYY') as opal_end_date_txt,
    gt.nm as tar_name
  from rep_subs_recalc_tbl as s
  join clm_paccnt_tbl c on (c.id=s.id_paccnt)
  join clm_abon_tbl as ab on (ab.id = c.id_abon) 
  join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class)
  left join aqi_grptar_tbl as gt on (gt.id =  s.id_gtar )
  where s.subs_return >0 and s.mmgg = (select max(mmgg) from rep_subs_recalc_tbl  )
  $where  
  order by  int_book,c.book,int_code,c.code; ";

    //echo $SQL;
    //throw new Exception(json_encode($SQL));
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        $i=1;
        $sum_subs_return=0;
        $sum_bonus=0;
        $bill_sum=0;
        $subs_sum=0;
        
        while ($row = pg_fetch_array($result)) {

            $np++;
            
            if ((($np> ($page_num-1)* $page_size )&& ($np<= $page_num* $page_size )) || ($show_pager ==0))
                $print_flag = 1;
            else
                $print_flag = 0;
            
            $abon = htmlspecialchars($row['abon']);
            if($res_code=='310')
                 $addr = htmlspecialchars($row['addr']);
            else
                 $addr = htmlspecialchars($row['town'].' '.$row['addr']);
            
            
            $book=$row['book']."/".$code=$row['code'];
            
            $sum_bonus_txt = number_format_ua ($row['sum_150'],2);

            echo_html( "
            <tr >
            <td>{$i}</td>            
            <td>$abon</td>            
            <td class='c_t'>$book</td>
            <td>$addr</td>
            <td class='c_n'>{$sum_bonus_txt}</td>
            </tr>  ",$print_flag); 

            $i++;
            
            $sum_bonus+=$row['sum_150'];
            
        }
        
    }

    $sum_bonus_str = number_format_ua ($sum_bonus,2);
    
    $executor=$name_executor;
    eval("\$footer_text_eval = \"$footer_text\";");
    echo_html( $footer_text_eval,$print_flag);
   
    if ($print_flag==0) echo "</tbody> </table> ";
   
}

//------------------------------------------------------------------------------

if ($oper=='ind_pack_summary') 
{
    $p_mmgg = sql_field_val('dt_b', 'mmgg');
    $period = trim($_POST['period_str']);
    
    $p_id_region = sql_field_val('id_region', 'int');

    $params_caption ='';    
        
    $where = ' ';
    
    if ((isset($_POST['note']))&&($_POST['note']!=''))
    {
        $where.= $_POST['note'];
        $params_caption .= $_POST['note'];
    }
    
    
    
    if ($p_id_region!='null')
    {
        
        $where= "where s.id_region =  $p_id_region "; 
        
        $SQL_H="SELECT name from cli_region_tbl where id = $p_id_region ;";
    
        $result = pg_query($Link, $SQL_H) or die("SQL Error: " . pg_last_error($Link) . $SQL_H);
        $row = pg_fetch_array($result);
        
        $params_caption.= $row['name'];        
    }
    
    
    eval("\$header_text = \"$header_text\";");
    echo_html( $header_text);
    
    $demand_all=0;
    $indic_cnt_all=0;
    $abon_cnt_all=0;
    
    $SQL = " select s.id, s.name, s.code, cnt_sector, p.represent_name,  ind.*
    from 
    prs_runner_sectors as s 
    left join (select rp.id_sector, count(*) as cnt_sector from prs_runner_paccnt as rp
        join clm_paccnt_tbl c on (c.id=rp.id_paccnt) where coalesce(c.archive,0) = 0
        group by rp.id_sector 
    ) as sc on (sc.id_sector = s.id)
    left join prs_persons as p on (p.id = s.id_runner)
    left join 
    (
        select h.id_sector, h.num_pack,  
        count(distinct p.id_paccnt) as cnt_pack ,
        count(distinct CASE WHEN p.indic is not null THEN p.id_paccnt END) as cnt_fill,
        count(distinct CASE WHEN p.id_operation =14  THEN p.id_paccnt END) as cnt_plan,
        count(distinct CASE WHEN p.id_operation =16 THEN p.id_paccnt END) as cnt_nedop,
	count(distinct CASE WHEN c.activ =false THEN p.id_paccnt END) as cnt_off,
        count(distinct CASE WHEN p.indic is not null THEN p.id_paccnt END)*100/count(p.id_paccnt) as proc_fill,
        count(distinct CASE WHEN coalesce(p.id_operation,0) in (1,24) THEN p.id_paccnt END) as cnt_list,
        sum(i.value_diff::int ) as demand
        from ind_pack_data as p 
	join ind_pack_header as h on (h.id_pack = p.id_pack)
	join clm_paccnt_tbl c on (c.id=p.id_paccnt)
        left join acm_indication_tbl as i on (i.id_ind = p.id)
	where h.work_period = $p_mmgg 
	group by h.id_sector, h.num_pack
    ) as ind on (ind.id_sector = s.id )
    $where
    order by code";

    
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
 
            $name = htmlspecialchars($row['name']);
            $insp = htmlspecialchars($row['represent_name']);
            
            echo_html( " <tr>
            <td>{$i}</td>
            <td class='c_t'>{$row['code']}</td>
            <td class='c_t'>{$name}</td>
            <td class='c_t'>{$insp}</td>
            <td class='c_i'>{$row['cnt_sector']}</td>
            <td class='c_t'>{$row['num_pack']}</td>
            <td class='c_i'>{$row['cnt_pack']}</td>
            <td class='c_i'>{$row['cnt_fill']}</td>
            <td class='c_i'>{$row['cnt_list']}</td>
            <td class='c_i'>{$row['proc_fill']}</td>
            <td class='c_i'>{$row['cnt_off']}</td>
            <td class='c_i'>{$row['cnt_nedop']}</td>
            <td class='c_i'>{$row['cnt_plan']}</td>
            <td class='c_i'>{$row['demand']}</td>
            </tr>
            ",$print_flag);

            $i++;
            $demand_all+=$row['demand'];
            $abon_cnt_all+=$row['cnt_pack'];
            $indic_cnt_all+=$row['cnt_fill'];
        }
        
    }
    
    eval("\$footer_text_eval = \"$footer_text\";");
    echo_html( $footer_text_eval);
    
}
//-----------------------------------------------------------------------------
if ($oper=='subs_dt2month_list')
{
    $p_mmgg = sql_field_val('dt_b', 'mmgg');
    $mmgg_str = ukr_date($_POST['dt_b'],0,1);

    $period = "за $mmgg_str ";
    
    $p_id_town = sql_field_val('addr_town', 'int');
    $p_id_region = sql_field_val('id_region', 'int');

    $where="";
    $params_caption = '';
    
    if ((isset($_POST['addr_town_name']))&&($_POST['addr_town_name']!=''))
        $params_caption.= 'Населений пункт: '. trim($_POST['addr_town_name']);
   
    if ($p_id_town!='null')
    {
        //$where.= " and adr.id_town = $p_id_town ";
        $where.= " and ( adr.id_town = $p_id_town or adr.id_parent = $p_id_town )";
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
    $i=1;
    
    $np=0;
    $print_flag = 1;
    //=========================================
    rep_abon_dt($Link, $p_mmgg,  2, 0);    
    //==============================================
    eval("\$header_text = \"$header_text\";");
    echo_html( $header_text);
    
    $SQL = " select acc.book, acc.code, acc.n_subs,
 adr.town||' '||(adr.street||' '||
   (coalesce('буд.'||(acc.addr).house||'','')||
		coalesce('/'||(acc.addr).slash||' ','')||
			coalesce(' корп.'||(acc.addr).korp||'','')||
				coalesce(', кв. '||(acc.addr).flat,'')||
					coalesce('/'||(acc.addr).f_slash||' ',''))::varchar
)::varchar as addr,
(c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon,
r.summ as debet_2m ,
 ('0'||substring(acc.code FROM '[0-9]+'))::int as int_code,
 ('0'||substring(acc.book FROM '[0-9]+'))::int as int_book,
 CASE WHEN coalesce(subs_return,0) <>0 THEN 1 END as flag_return    
from rep_abon_dt_tbl as r
join (
   	select p.id_paccnt, sum(p.value) as subs_value,
        sum(CASE WHEN p.idk_doc = 194 then p.value END ) as subs_return
       	from acm_pay_tbl as p 
       	where (((p.mmgg = $p_mmgg::date or p.mmgg = ($p_mmgg::date - '1 month'::interval)::date  ) and p.idk_doc =110 ) or ( p.idk_doc = 194 and date_trunc('year',p.mmgg) = date_trunc('year',$p_mmgg::date) ))
        and p.id_pref = 10 
       	group by p.id_paccnt
        having ((sum(p.value) >0) or (sum(CASE WHEN p.idk_doc = 194 then p.value END )<>0))
	order by id_paccnt
       ) as ps on (ps.id_paccnt = r.id_paccnt)
 join clm_paccnt_tbl as acc on (acc.id = r.id_paccnt)
 join clm_abon_tbl as c on (c.id = acc.id_abon) 
 join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class)
 where acc.archive=0    
$where    
 order by int_book, int_code;";

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
            $addr = htmlspecialchars($row['addr']);
            
            $debet_2m_txt = number_format_ua ($row['debet_2m'],2);
            
            $bookcode=$row['book'].'/'.$row['code'];
 
            echo_html( "
            <tr >
            <td>{$i}</td>            
            <td>$abon</td>
            <td class='c_t'>$bookcode</td>
            <td class='c_t'>{$row['n_subs']}</td>
            <td class='c_t'>{$row['flag_return']}</td>
            <td>$addr</td>
            <TD class='c_n'>{$debet_2m_txt}</td>
            </tr>  ",$print_flag); 

            $i++;
        }
    }

    eval("\$footer_text_eval = \"$footer_text\";");

    echo_html( $footer_text_eval,$print_flag);
   
    if ($print_flag==0) echo "</tbody> </table> ";
   
}
//---------------------------------------------------------------------

if ($oper=='biginfo2016')
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
    
    $p_id_paccnt = sql_field_val('id_paccnt', 'int');
    $abon_name = trim($_POST['paccnt_name']);
    
    if ((isset($_POST['addr_town_name']))&&($_POST['addr_town_name']!=''))
        $params_caption = 'Населений пункт: '. trim($_POST['addr_town_name']);
    else 
        $params_caption ='';

    
    if ((isset($_POST['sector']))&&($_POST['sector']!=''))
        $params_caption.= ' Дільниця: '. trim($_POST['sector']);
    
    
    if ($params_caption !='') $params_caption .='<br/>';
        
    $where = ' ';
    $left = '';
    
    
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

    if ($p_id_paccnt!='null')
    {
        $where.= "and c.id = $p_id_paccnt ";
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
        
        //$where.= " and rs.id_region =  $p_id_region ";

        $SQL_H="SELECT name from cli_region_tbl where id = $p_id_region ;";
    
        $result = pg_query($Link, $SQL_H) or die("SQL Error: " . pg_last_error($Link) . $SQL_H);
        $row = pg_fetch_array($result);
        
        $params_caption.= $row['name'];        
       
    }
    
    //$order ='town, int_book, book, int_code,code';
    $order = "adr.town,adr.street,  int_house, (c.addr).house,(c.addr).korp , int_flat, int_code, c.code ";
    
    
    $SQL = "select c.id,c.code,c.book, c.note, 
    adr.town, adr.street,     
    address_print(c.addr) as house, b.value, b.demand, 
   (a.last_name||' '||coalesce(substr(a.name,1,1),'')||'.'||coalesce(substr(a.patron_name,1,1),''))||'.'::varchar as abon,
    ('0'||substring(c.code FROM '[0-9]+'))::int as int_code,
    ('0'||substring(c.book FROM '[0-9]+'))::int as int_book,
    ('0'||coalesce(regexp_replace(regexp_replace((c.addr).house, '-.*?$', '') , '[^0-9]', '','g'),''))::int as int_house,
    ('0'||coalesce(regexp_replace(regexp_replace((c.addr).flat, '-.*?$', '') , '[^0-9]', '','g'),''))::int as int_flat
from 
    clm_paccnt_h as c
    join (select id, max(dt_b) as dt_b from clm_paccnt_h  where dt_b <= $p_dt and coalesce(dt_e,$p_dt) >=$p_dt group by id order by id) as c2 
    on (c.id = c2.id and c.dt_b = c2.dt_b)
    join clm_abon_tbl as a on (a.id = c.id_abon) 
    join adt_addr_town_street_tbl as adr on (adr.id = (c.addr).id_class)
    left join cli_addparam_tbl as ap on (ap.id = c.id_cntrl)
    left join (
      	select b.id_paccnt, CASE WHEN sum(demand)<>0 THEN round(sum(b.value_calc)/sum(demand),2)::numeric ELSE 0 END as value,  
                            round(sum(demand)/count(distinct b.mmgg_bill),0)::int as demand 
        from acm_bill_tbl as b 
               where date_part('year',b.mmgg_bill) = 2016 
               and b.id_pref = 10
               and b.idk_doc in (200,220)
             group by b.id_paccnt
        ) as b
   on (b.id_paccnt = c.id)    
   where c.archive=0 $where 
   order by $order ;";

  
    $cur_town='';
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        $i=1;
        $np=0;        
        while ($row = pg_fetch_array($result)) {

            $abon = htmlspecialchars($row['abon']);
            $note = htmlspecialchars($row['note']);
            if($res_code=='310')
              $addr = htmlspecialchars('м.Чернігів, '.$row['street'].' '.$row['house']);
            else
              $addr = htmlspecialchars($row['town'].', '.$row['street'].' '.$row['house']);  
                
            $book=$row['book'].'/'.$row['code'];
            $value_txt = number_format_ua ($row['value'],2);  
            $value_txt=str_replace('.', ',', $value_txt);
            
            $demand_txt = number_format_ua ($row['demand'],0);  
            if ($demand_txt=='') $demand_txt ='0';
            if ($value_txt=='') $value_txt ='0,00';

            eval("\$header_text_eval = \"$header_text\";");
            echo_html( $header_text_eval);
           
            if ($np==0)
            {
              $np=1;
              echo_html(" <div class='center_page'> &nbsp; </div>  ") ;
            }
            else
            {
              $np=0;
              echo_html(" <div style='clear:both;' > </div> ");
            }
            
            $i++;
        }
        
    }

    //return;
    
}
//-----------------------------------------------------------------------------
if ($oper=='zvit_column_move')
{
    $p_mmgg = sql_field_val('dt_b', 'mmgg');
    $mmgg_str = ukr_date($_POST['dt_b'],0,1);

    $period = "за $mmgg_str ";
    
    $p_id_region = sql_field_val('id_region', 'int');

    $where="";
    $params_caption = '';
    
    
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
       
        if ($res_code==240)
        {
            $res_code=242;
        }
    }    
    $i=1;
    
    $np=0;
    $print_flag = 1;
    //==============================================
    eval("\$header_text = \"$header_text\";");
    echo_html( $header_text);
    
    $SQL = " select acc.book, acc.code, 
 adr.town||' '||(adr.street||' '||
   (coalesce('буд.'||(acc.addr).house||'','')||
		coalesce('/'||(acc.addr).slash||' ','')||
			coalesce(' корп.'||(acc.addr).korp||'','')||
				coalesce(', кв. '||(acc.addr).flat,'')||
					coalesce('/'||(acc.addr).f_slash||' ',''))::varchar
)::varchar as addr,
(c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon,
 r.sum_saldo, t1.sh_nm as tar_old, t2.sh_nm as tar_new,  r.zcolumn_old, r.zcolumn_new, 
 r.str_num_old, r.str_num_new,   
 ('0'||substring(acc.code FROM '[0-9]+'))::int as int_code,
 ('0'||substring(acc.book FROM '[0-9]+'))::int as int_book
from rep_zvit_column_move_tbl as r
 join clm_paccnt_tbl as acc on (acc.id = r.id_paccnt)
 join clm_abon_tbl as c on (c.id = acc.id_abon) 
 join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class)
 join aqi_grptar_tbl as t1 on (t1.id = r.id_gtar_old)
 join aqi_grptar_tbl as t2 on (t2.id = r.id_gtar_new)
 where r.mmgg = $p_mmgg and r.id_dep = $res_code  
$where    
 order by int_book, int_code;";

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
            $addr = htmlspecialchars($row['addr']);
            
            $sum_saldo_txt = number_format_ua ($row['sum_saldo'],2);
            
            $bookcode=$row['book'].'/'.$row['code'];
 
            echo_html( "
            <tr >
            <td>{$i}</td>            
            <td class='c_t'>$bookcode</td>
            <td>$abon</td>
            <td>$addr</td>
            <td class='c_t'>{$row['tar_old']}</td>
            <td class='c_t'>{$row['zcolumn_old']}</td>
            <td class='c_t'>{$row['str_num_old']}</td>
            <td class='c_t'>{$row['tar_new']}</td>
            <td class='c_t'>{$row['zcolumn_new']}</td>
            <td class='c_t'>{$row['str_num_new']}</td>
            <TD class='c_n'>{$sum_saldo_txt}</td>
            </tr>  ",$print_flag); 

            $i++;
        }
    }

    eval("\$footer_text_eval = \"$footer_text\";");

    echo_html( $footer_text_eval,$print_flag);
   
    if ($print_flag==0) echo "</tbody> </table> ";
   
}
//---------------------------------------------------------------------

if ($oper=='abon_meter_control')
 {
    $p_mmgg1 = sql_field_val('dt_b', 'mmgg');
    $p_mmgg2 = sql_field_val('dt_e', 'mmgg');
    
    $mmgg1_str = ukr_date($_POST['dt_b'],0,1);
    $mmgg2_str = ukr_date($_POST['dt_e'],0,1);

    $mmgg1_str = "$mmgg1_str - $mmgg2_str";   
    
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
    
    to_char(coalesce(ww2.dt_work), 'DD.MM.YYYY') as dt_work,
    last_work_name, 
    
    im.name as type_meter, zzz.zones, 
    to_char(zz.dat_ind, 'DD.MM.YYYY') as last_dat_ind , zz.indic_str,
    CASE WHEN im.phase = 1 THEN '1' WHEN im.phase = 2 THEN '3' END::varchar as phase,
    mp.name as meter_place,rs.name as sector, 

    CASE WHEN c.activ THEN 1 END as activ,
    CASE WHEN c.not_live or n.ncnt is not null THEN 1 END as not_live,
    
    ('0'||substring(c.code FROM '[0-9]+'))::int as int_code,
    ('0'||substring(c.book FROM '[0-9]+'))::int as int_book

from 
    clm_paccnt_tbl as c
    join clm_meterpoint_h as m on (m.id_paccnt = c.id) 
    join (select id, max(dt_b) as dt_b from clm_meterpoint_h  where 
     ((dt_b < ($p_mmgg2::date+'1 month'::interval) and dt_e is null)
          or 
          tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($p_mmgg2::timestamp::abstime,($p_mmgg2::date+'1 month - 1 day'::interval)::timestamp::abstime))
     )
    group by id order by id) as m2 
    on (m.id = m2.id and m.dt_b = m2.dt_b)
    join clm_abon_tbl as a on (a.id = c.id_abon) 
   
    join ( select z.id_meter, trim(sum(iz.nm||','),',')::varchar as zones
     from clm_meter_zone_tbl as z 
     join eqk_zone_tbl as iz on (iz.id = z.id_zone)
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
    left join 
    (
     select w.id_paccnt, count(distinct w.id_paccnt) as wcnt 
     from clm_works_tbl as w 
     where w.work_period >= $p_mmgg1 and w.work_period <= $p_mmgg2
     group by w.id_paccnt
    ) as ww on (ww.id_paccnt= c.id)
    left join 
    (
     select w.id_paccnt, w.idk_work, w.dt_work, wi.name as last_work_name
     from clm_works_tbl as w 
     join cli_works_tbl as wi on (wi.id = w.idk_work)
     join (select id_paccnt, max(dt_work) as max_dt from clm_works_tbl as w1 group by  id_paccnt) as ww1
     on (ww1.id_paccnt = w.id_paccnt and ww1.max_dt = w.dt_work)
    ) as ww2 on (ww2.id_paccnt= c.id)
    
    left join (
      	select n.id_paccnt, count(distinct n.id_paccnt) as ncnt
        from clm_notlive_tbl as n 
       where n.dt_b<= $mmgg_current::date+'1 month -1 day'::interval and ((n.dt_e is null) or (n.dt_e>= $mmgg_current::date))
       group by n.id_paccnt
       order by n.id_paccnt
   ) as n on (n.id_paccnt = c.id)
    where c.archive=0 and ww.wcnt is null
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

            <td class='c_t'>{$row['last_work_name']}</td>
            <td class='c_t'>{$row['dt_work']}</td>
            
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

//-----------------------------------------------------------------------------
if ($oper=='abon_lgt_period')
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
    
    if ($p_family_cnt!='null')
    {
        $params_caption.= " <br/> від $p_family_cnt та більше членів родини" ;
        if ($where!='where') $where.= " and ";
        $where.= "  family_cnt >= $p_family_cnt ";
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
        $order ="int_ident,  dt_start, book, int_code,code";
    else
        $order ="town, int_ident, dt_start,  book, int_code, code";
        
    if ((isset($_POST['gtar']))&&($_POST['gtar']!=''))
        $params_caption .= ' Тарифна група : '. trim($_POST['gtar']);
    
    if ($where=='where') $where= '';
    
   // if ($where == ' WHERE ') $where ='';
     
    
    $SQL = " select * from (
    select c.id, c.code, c.book, lg.id as id_lgm, lg.fio_lgt,lg.family_cnt,lg.s_doc, lg.n_doc,lg.ident_cod_l,
    to_char(lg.dt_reg, 'DD.MM.YYYY') as dt_reg,
     g.name as lgt_name, g.ident,g.alt_code,adr.town, adr.street, address_print(c.addr) as house,adr.id_town,adr.id_parent,
    --tt.name as name_tar, n.percent, round(tt.value*100,2) as tar_value, round(tt.value*(100-n.percent),2) as tar_lgt_value,
    --bs.demand,bs.demand_lgt,bs.summ_lgt, 
    lg.id_grp_lgt,  c.id_gtar,
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
--    left join aqm_tarif_tmp as tt on (tt.id_grptar = c.id_gtar and coalesce(tt.lim_min ,0)=0)
--    left join 
--    (select s.id_paccnt,s.id_grp_lgt, sum(s.demand) as demand, sum(s.demand_lgt) as demand_lgt, sum(s.summ_lgt) as summ_lgt
--     from acm_lgt_summ_tbl as s 
--     join acm_bill_tbl as b on (b.id_doc = s.id_doc)
--     where b.mmgg = $p_mmgg and b.id_pref = 10
--     group by s.id_paccnt, s.id_grp_lgt order by s.id_paccnt
--    ) as bs
--     on (bs.id_paccnt = c.id and bs.id_grp_lgt = lg.id_grp_lgt) 
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
        $sum_all_cnt=0;
        
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
                    </tr>  ",$print_flag*$print_h2_flag);        
                     
                    $sum_lgt_cnt=0;
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

                     </tr>  ",$print_flag*$print_h2_flag);        
                    
                    //throw new Exception("Try to save file $rows_count ....");    
                    $r = $i++;                    

                    echo $footer_text;    
                    
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
                    </tr>  ",$print_flag*$print_h_flag);    
                     
                    $sum_lgt_cnt=0; 
                    
                }

                $current_lgt=$row['lgt_name'];
                $current_lgt_ident=$row['ident'];
                $current_lgt_alt=$row['alt_code'];
                
                $current_lgt_calc=$row['calc_name'];
                $current_lgt_state=$row['lgt_state'];
                
                $r = $i++;

                 echo_html( "
                    <tr class='tab_head'>
                     <td colspan='10'>{$current_lgt} &nbsp;({$current_lgt_ident}/{$current_lgt_alt})&nbsp;&nbsp; {$current_lgt_calc} &nbsp;&nbsp;{$current_lgt_state} </td>
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
                     <td class='c_t'>{$book}</td>
                     <td class='c_t'>{$doc}</td>
                     
                     <td class='c_t'>{$row['dt_doc_txt']}</td>
                     <td class='c_t'>{$row['dt_doc_end_txt']}</td>
                     <td class='c_t'>{$row['dt_start_txt']}</td>
                     <td class='c_t'>{$row['dt_end_txt']}</td>
                     <td class='c_i'>{$row['family_cnt']}</td>
                    </tr>  ",$print_flag*$print_h_flag);
            
                     $nm++;

            $sum_lgt_cnt++;

            $sum_all_cnt++;
            
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
                    </tr> " ,$print_flag*$print_h_flag);

    echo $footer_text;    
    
}


//------------------------------------------------------------------------------
if ($oper=='pay_decade')
{
    $p_mmgg = sql_field_val('dt_b', 'mmgg');
    $mmgg1_str = trim($_POST['period_str']);
    
    $p_id_sector = sql_field_val('id_sector', 'int');
    $p_id_region = sql_field_val('id_region', 'int');
    
    $p_id_town = sql_field_val('addr_town', 'int');


    $params_caption="" ;
    $where = ' ';

/*
    if ($p_id_town!='null')
    {
        //$where.= "and adr.id_town = $p_id_town ";
        $where.= " and ( adr.id_town = $p_id_town or adr.id_parent = $p_id_town )";
    }
*/

    if ((isset($_POST['sector']))&&($_POST['sector']!=''))
        $params_caption.= ' Дiльниця: '. trim($_POST['sector']);

    if ($p_id_sector!='null')
    {
      $where.= " and rp.id_sector = $p_id_sector ";
    }
    
    if ($p_id_region!='null')
    {
        
        $where.= " and rs.id_region =  $p_id_region  ";

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
    
    eval("\$header_text_eval = \"$header_text\";");
    echo $header_text_eval;
    
    
    $SQL = "select zone,sector,
    sum( pay_value ) as pay,
    sum(CASE WHEN date_part('day',reg_date) = 1 THEN pay_value END) as pay1,
    sum(CASE WHEN date_part('day',reg_date) = 2 THEN pay_value END) as pay2,
    sum(CASE WHEN date_part('day',reg_date) = 3 THEN pay_value END) as pay3,
    sum(CASE WHEN date_part('day',reg_date) = 4 THEN pay_value END) as pay4,
    sum(CASE WHEN date_part('day',reg_date) = 5 THEN pay_value END) as pay5,
    sum(CASE WHEN date_part('day',reg_date) = 6 THEN pay_value END) as pay6,
    sum(CASE WHEN date_part('day',reg_date) = 7 THEN pay_value END) as pay7,
    sum(CASE WHEN date_part('day',reg_date) = 8 THEN pay_value END) as pay8,
    sum(CASE WHEN date_part('day',reg_date) = 9 THEN pay_value END) as pay9,
    sum(CASE WHEN date_part('day',reg_date) = 10 THEN pay_value END) as pay10,
    sum(CASE WHEN date_part('day',reg_date) = 11 THEN pay_value END) as pay11,
    sum(CASE WHEN date_part('day',reg_date) = 12 THEN pay_value END) as pay12,
    sum(CASE WHEN date_part('day',reg_date) = 13 THEN pay_value END) as pay13,
    sum(CASE WHEN date_part('day',reg_date) = 14 THEN pay_value END) as pay14,
    sum(CASE WHEN date_part('day',reg_date) = 15 THEN pay_value END) as pay15,
    sum(CASE WHEN date_part('day',reg_date) = 16 THEN pay_value END) as pay16,
    sum(CASE WHEN date_part('day',reg_date) = 17 THEN pay_value END) as pay17,
    sum(CASE WHEN date_part('day',reg_date) = 18 THEN pay_value END) as pay18,
    sum(CASE WHEN date_part('day',reg_date) = 19 THEN pay_value END) as pay19,
    sum(CASE WHEN date_part('day',reg_date) = 20 THEN pay_value END) as pay20,
    sum(CASE WHEN date_part('day',reg_date) = 21 THEN pay_value END) as pay21,
    sum(CASE WHEN date_part('day',reg_date) = 22 THEN pay_value END) as pay22,
    sum(CASE WHEN date_part('day',reg_date) = 23 THEN pay_value END) as pay23,
    sum(CASE WHEN date_part('day',reg_date) = 24 THEN pay_value END) as pay24,
    sum(CASE WHEN date_part('day',reg_date) = 25 THEN pay_value END) as pay25,
    sum(CASE WHEN date_part('day',reg_date) = 26 THEN pay_value END) as pay26,
    sum(CASE WHEN date_part('day',reg_date) = 27 THEN pay_value END) as pay27,
    sum(CASE WHEN date_part('day',reg_date) = 28 THEN pay_value END) as pay28,
    sum(CASE WHEN date_part('day',reg_date) = 29 THEN pay_value END) as pay29,
    sum(CASE WHEN date_part('day',reg_date) = 30 THEN pay_value END) as pay30,
    sum(CASE WHEN date_part('day',reg_date) = 31 THEN pay_value END) as pay31
    from(
    select p.reg_date,rs.zone, rs.name as sector,sum(p.value) as pay_value
        from acm_pay_tbl as p 
        join acm_headpay_tbl as hp on (hp.id = p.id_headpay)
        join prs_runner_paccnt as rp on (rp.id_paccnt = p.id_paccnt)
        join prs_runner_sectors as rs on (rs.id = rp.id_sector)
        where p.id_pref = 10 and p.idk_doc not in ( 110,111, 193,194) 
        and p.value<>0
        and p.mmgg = $p_mmgg::date
    $where
    group by p.reg_date,rs.name,rs.zone
    ) as ss
    where coalesce(pay_value,0) <>0
    group by zone,sector
    order by zone,sector;   ";

   // throw new Exception(json_encode($SQL));
    
    $current_zone='';
    $np=0;
    //$print_flag = 1;    
    //$print_h_flag=1;
    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        $sum_zone=0;
        $sum_zone1=0;
        $sum_zone2=0;
        $sum_zone3=0;
        $sum_zone4=0;
        $sum_zone5=0;
        $sum_zone6=0;
        $sum_zone7=0;
        $sum_zone8=0;
        $sum_zone9=0;
        $sum_zone10=0;
        $sum_zone11=0;        
        $sum_zone12=0;
        $sum_zone13=0;
        $sum_zone14=0;
        $sum_zone15=0;        
        $sum_zone16=0;
        $sum_zone17=0;
        $sum_zone18=0;
        $sum_zone19=0;
        $sum_zone20=0;        
        $sum_zone21=0;        
        $sum_zone22=0;        
        $sum_zone23=0;        
        $sum_zone24=0;        
        $sum_zone25=0;        
        $sum_zone26=0;        
        $sum_zone27=0;        
        $sum_zone28=0;        
        $sum_zone29=0;                
        $sum_zone30=0;        
        $sum_zone31=0;                

        $sum_all=0;
        $sum_all1=0;
        $sum_all2=0;
        $sum_all3=0;
        $sum_all4=0;
        $sum_all5=0;
        $sum_all6=0;
        $sum_all7=0;
        $sum_all8=0;
        $sum_all9=0;
        $sum_all10=0;
        $sum_all11=0;        
        $sum_all12=0;
        $sum_all13=0;
        $sum_all14=0;
        $sum_all15=0;        
        $sum_all16=0;
        $sum_all17=0;
        $sum_all18=0;
        $sum_all19=0;
        $sum_all20=0;        
        $sum_all21=0;        
        $sum_all22=0;        
        $sum_all23=0;        
        $sum_all24=0;        
        $sum_all25=0;        
        $sum_all26=0;        
        $sum_all27=0;        
        $sum_all28=0;        
        $sum_all29=0;                
        $sum_all30=0;        
        $sum_all31=0;   
        

        
        while ($row = pg_fetch_array($result)) {

            if ($current_zone!=$row['zone'])
            {
              if ($current_zone!='')  
              {    
                $summ_txt =  number_format_ua ($sum_zone,2);  
                $summ1_txt =  number_format_ua ($sum_zone1,2);
                $summ2_txt =  number_format_ua ($sum_zone2,2);
                $summ3_txt =  number_format_ua ($sum_zone3,2);
                $summ4_txt =  number_format_ua ($sum_zone4,2);
                $summ5_txt =  number_format_ua ($sum_zone5,2);
                $summ6_txt =  number_format_ua ($sum_zone6,2);
                $summ7_txt =  number_format_ua ($sum_zone7,2);
                $summ8_txt =  number_format_ua ($sum_zone8,2);
                $summ9_txt =  number_format_ua ($sum_zone9,2);
                $summ10_txt=  number_format_ua ($sum_zone10,2);

                $summ11_txt =  number_format_ua ($sum_zone11,2);
                $summ12_txt =  number_format_ua ($sum_zone12,2);
                $summ13_txt =  number_format_ua ($sum_zone13,2);
                $summ14_txt =  number_format_ua ($sum_zone14,2);
                $summ15_txt =  number_format_ua ($sum_zone15,2);
                $summ16_txt =  number_format_ua ($sum_zone16,2);
                $summ17_txt =  number_format_ua ($sum_zone17,2);
                $summ18_txt =  number_format_ua ($sum_zone18,2);
                $summ19_txt =  number_format_ua ($sum_zone19,2);
                $summ20_txt=  number_format_ua ($sum_zone20,2);
                
                $summ21_txt =  number_format_ua ($sum_zone21,2);
                $summ22_txt =  number_format_ua ($sum_zone22,2);
                $summ23_txt =  number_format_ua ($sum_zone23,2);
                $summ24_txt =  number_format_ua ($sum_zone24,2);
                $summ25_txt =  number_format_ua ($sum_zone25,2);
                $summ26_txt =  number_format_ua ($sum_zone26,2);
                $summ27_txt =  number_format_ua ($sum_zone27,2);
                $summ28_txt =  number_format_ua ($sum_zone28,2);
                $summ29_txt =  number_format_ua ($sum_zone29,2);
                $summ30_txt=  number_format_ua ($sum_zone30,2);
                $summ31_txt=  number_format_ua ($sum_zone31,2);                
                
                echo_html( "
                    <TR class='tab_head'>
                    <TD colspan='2'>ВСЬОГО $current_zone </td>
                    <td class='c_n'>{$summ_txt}</td>
                    <td class='c_n'>{$summ1_txt}</td>
                    <td class='c_n'>{$summ2_txt}</td>
                    <td class='c_n'>{$summ3_txt}</td>
                    <td class='c_n'>{$summ4_txt}</td>
                    <td class='c_n'>{$summ5_txt}</td>
                    <td class='c_n'>{$summ6_txt}</td>
                    <td class='c_n'>{$summ7_txt}</td>
                    <td class='c_n'>{$summ8_txt}</td>
                    <td class='c_n'>{$summ9_txt}</td>
                    <td class='c_n'>{$summ10_txt}</td>

                    <td class='c_n'>{$summ11_txt}</td>
                    <td class='c_n'>{$summ12_txt}</td>
                    <td class='c_n'>{$summ13_txt}</td>
                    <td class='c_n'>{$summ14_txt}</td>
                    <td class='c_n'>{$summ15_txt}</td>
                    <td class='c_n'>{$summ16_txt}</td>
                    <td class='c_n'>{$summ17_txt}</td>
                    <td class='c_n'>{$summ18_txt}</td>
                    <td class='c_n'>{$summ19_txt}</td>
                    <td class='c_n'>{$summ20_txt}</td>

                    <td class='c_n'>{$summ21_txt}</td>
                    <td class='c_n'>{$summ22_txt}</td>
                    <td class='c_n'>{$summ23_txt}</td>
                    <td class='c_n'>{$summ24_txt}</td>
                    <td class='c_n'>{$summ25_txt}</td>
                    <td class='c_n'>{$summ26_txt}</td>
                    <td class='c_n'>{$summ27_txt}</td>
                    <td class='c_n'>{$summ28_txt}</td>
                    <td class='c_n'>{$summ29_txt}</td>
                    <td class='c_n'>{$summ30_txt}</td>
                    <td class='c_n'>{$summ31_txt}</td>
                 </tr> ",1);        
                 }    
                 $current_zone=$row['zone'];
                 $zone_str=''; 
                 $sum_zone = 0;
                 $sum_zone1 = 0;
                 $sum_zone2 = 0;
                 $sum_zone3 = 0;
                 $sum_zone4 = 0;
                 $sum_zone5 = 0;
                 $sum_zone6 = 0;
                 $sum_zone7 = 0;
                 $sum_zone8 = 0;
                 $sum_zone9 = 0;
                 $sum_zone10 = 0;
                 $sum_zone11 = 0;
                 $sum_zone12 = 0;
                 $sum_zone13 = 0;
                 $sum_zone14 = 0;
                 $sum_zone15 = 0;
                 $sum_zone16 = 0;
                 $sum_zone17 = 0;
                 $sum_zone18 = 0;
                 $sum_zone19 = 0;
                 $sum_zone20 = 0;
                 $sum_zone21 = 0;
                 $sum_zone22 = 0;
                 $sum_zone23 = 0;
                 $sum_zone24 = 0;
                 $sum_zone25 = 0;
                 $sum_zone26 = 0;
                 $sum_zone27 = 0;
                 $sum_zone28 = 0;
                 $sum_zone29 = 0;
                 $sum_zone30 = 0;
                 $sum_zone31 = 0;

               // $cnt_headers++;
            }
            
               
            $sector=$row['sector'];
                
            $summ_txt =  number_format_ua ($row['pay'],2);
            $summ1_txt =  number_format_ua ($row['pay1'],2);
            $summ2_txt =  number_format_ua ($row['pay2'],2);
            $summ3_txt =  number_format_ua ($row['pay3'],2);
            $summ4_txt =  number_format_ua ($row['pay4'],2);
            $summ5_txt =  number_format_ua ($row['pay5'],2);
            $summ6_txt =  number_format_ua ($row['pay6'],2);
            $summ7_txt =  number_format_ua ($row['pay7'],2);
            $summ8_txt =  number_format_ua ($row['pay8'],2);
            $summ9_txt =  number_format_ua ($row['pay9'],2);
            $summ10_txt=  number_format_ua ($row['pay10'],2);

            $summ11_txt =  number_format_ua ($row['pay11'],2);
            $summ12_txt =  number_format_ua ($row['pay12'],2);
            $summ13_txt =  number_format_ua ($row['pay13'],2);
            $summ14_txt =  number_format_ua ($row['pay14'],2);
            $summ15_txt =  number_format_ua ($row['pay15'],2);
            $summ16_txt =  number_format_ua ($row['pay16'],2);
            $summ17_txt =  number_format_ua ($row['pay17'],2);
            $summ18_txt =  number_format_ua ($row['pay18'],2);
            $summ19_txt =  number_format_ua ($row['pay19'],2);
            $summ20_txt =  number_format_ua ($row['pay20'],2);
               
            $summ21_txt =  number_format_ua ($row['pay21'],2);
            $summ22_txt =  number_format_ua ($row['pay22'],2);
            $summ23_txt =  number_format_ua ($row['pay23'],2);
            $summ24_txt =  number_format_ua ($row['pay24'],2);
            $summ25_txt =  number_format_ua ($row['pay25'],2);
            $summ26_txt =  number_format_ua ($row['pay26'],2);
            $summ27_txt =  number_format_ua ($row['pay27'],2);
            $summ28_txt =  number_format_ua ($row['pay28'],2);
            $summ29_txt =  number_format_ua ($row['pay29'],2);
            $summ30_txt =  number_format_ua ($row['pay30'],2);
            $summ31_txt =  number_format_ua ($row['pay31'],2);                


            echo_html( "
                   <tr>
                    <td>{$current_zone}</td>
                    <td >{$sector}</td>
                    <td class='c_n'>{$summ_txt}</td>
                    <td class='c_n'>{$summ1_txt}</td>
                    <td class='c_n'>{$summ2_txt}</td>
                    <td class='c_n'>{$summ3_txt}</td>
                    <td class='c_n'>{$summ4_txt}</td>
                    <td class='c_n'>{$summ5_txt}</td>
                    <td class='c_n'>{$summ6_txt}</td>
                    <td class='c_n'>{$summ7_txt}</td>
                    <td class='c_n'>{$summ8_txt}</td>
                    <td class='c_n'>{$summ9_txt}</td>
                    <td class='c_n'>{$summ10_txt}</td>

                    <td class='c_n'>{$summ11_txt}</td>
                    <td class='c_n'>{$summ12_txt}</td>
                    <td class='c_n'>{$summ13_txt}</td>
                    <td class='c_n'>{$summ14_txt}</td>
                    <td class='c_n'>{$summ15_txt}</td>
                    <td class='c_n'>{$summ16_txt}</td>
                    <td class='c_n'>{$summ17_txt}</td>
                    <td class='c_n'>{$summ18_txt}</td>
                    <td class='c_n'>{$summ19_txt}</td>
                    <td class='c_n'>{$summ20_txt}</td>

                    <td class='c_n'>{$summ21_txt}</td>
                    <td class='c_n'>{$summ22_txt}</td>
                    <td class='c_n'>{$summ23_txt}</td>
                    <td class='c_n'>{$summ24_txt}</td>
                    <td class='c_n'>{$summ25_txt}</td>
                    <td class='c_n'>{$summ26_txt}</td>
                    <td class='c_n'>{$summ27_txt}</td>
                    <td class='c_n'>{$summ28_txt}</td>
                    <td class='c_n'>{$summ29_txt}</td>
                    <td class='c_n'>{$summ30_txt}</td>
                    <td class='c_n'>{$summ31_txt}</td>
                     
                   </tr>  ",1);        
                            
            $sum_zone  += $row['pay'];    
            $sum_zone1 += $row['pay1'];
            $sum_zone2 += $row['pay2'];
            $sum_zone3 += $row['pay3'];
            $sum_zone4 += $row['pay4'];
            $sum_zone5 += $row['pay5'];
            $sum_zone6 += $row['pay6'];
            $sum_zone7 += $row['pay7'];
            $sum_zone8 += $row['pay8'];
            $sum_zone9 += $row['pay9'];
            $sum_zone10 += $row['pay10'];
            $sum_zone11 += $row['pay11'];
            $sum_zone12 += $row['pay12'];
            $sum_zone13 += $row['pay13'];
            $sum_zone14 += $row['pay14'];
            $sum_zone15 += $row['pay15'];
            $sum_zone16 += $row['pay16'];
            $sum_zone17 += $row['pay17'];
            $sum_zone18 += $row['pay18'];
            $sum_zone19 += $row['pay19'];
            $sum_zone20 += $row['pay20'];
            $sum_zone21 += $row['pay21'];
            $sum_zone22 += $row['pay22'];
            $sum_zone23 += $row['pay23'];
            $sum_zone24 += $row['pay24'];
            $sum_zone25 += $row['pay25'];
            $sum_zone26 += $row['pay26'];
            $sum_zone27 += $row['pay27'];
            $sum_zone28 += $row['pay28'];
            $sum_zone29 += $row['pay29'];
            $sum_zone30 += $row['pay30'];
            $sum_zone31 += $row['pay31'];
                
            $sum_all += $row['pay'];
            $sum_all1 += $row['pay1'];
            $sum_all2 += $row['pay2'];
            $sum_all3 += $row['pay3'];
            $sum_all4 += $row['pay4'];
            $sum_all5 += $row['pay5'];
            $sum_all6 += $row['pay6'];
            $sum_all7 += $row['pay7'];
            $sum_all8 += $row['pay8'];
            $sum_all9 += $row['pay9'];
            $sum_all10 += $row['pay10'];
            $sum_all11 += $row['pay11'];
            $sum_all12 += $row['pay12'];
            $sum_all13 += $row['pay13'];
            $sum_all14 += $row['pay14'];
            $sum_all15 += $row['pay15'];
            $sum_all16 += $row['pay16'];
            $sum_all17 += $row['pay17'];
            $sum_all18 += $row['pay18'];
            $sum_all19 += $row['pay19'];
            $sum_all20 += $row['pay20'];
            $sum_all21 += $row['pay21'];
            $sum_all22 += $row['pay22'];
            $sum_all23 += $row['pay23'];
            $sum_all24 += $row['pay24'];
            $sum_all25 += $row['pay25'];
            $sum_all26 += $row['pay26'];
            $sum_all27 += $row['pay27'];
            $sum_all28 += $row['pay28'];
            $sum_all29 += $row['pay29'];
            $sum_all30 += $row['pay30'];
            $sum_all31 += $row['pay31'];


        }
    }


    if ($current_zone != '') {
                $summ_txt =  number_format_ua ($sum_zone,2);
                $summ1_txt =  number_format_ua ($sum_zone1,2);
                $summ2_txt =  number_format_ua ($sum_zone2,2);
                $summ3_txt =  number_format_ua ($sum_zone3,2);
                $summ4_txt =  number_format_ua ($sum_zone4,2);
                $summ5_txt =  number_format_ua ($sum_zone5,2);
                $summ6_txt =  number_format_ua ($sum_zone6,2);
                $summ7_txt =  number_format_ua ($sum_zone7,2);
                $summ8_txt =  number_format_ua ($sum_zone8,2);
                $summ9_txt =  number_format_ua ($sum_zone9,2);
                $summ10_txt=  number_format_ua ($sum_zone10,2);

                $summ11_txt =  number_format_ua ($sum_zone11,2);
                $summ12_txt =  number_format_ua ($sum_zone12,2);
                $summ13_txt =  number_format_ua ($sum_zone13,2);
                $summ14_txt =  number_format_ua ($sum_zone14,2);
                $summ15_txt =  number_format_ua ($sum_zone15,2);
                $summ16_txt =  number_format_ua ($sum_zone16,2);
                $summ17_txt =  number_format_ua ($sum_zone17,2);
                $summ18_txt =  number_format_ua ($sum_zone18,2);
                $summ19_txt =  number_format_ua ($sum_zone19,2);
                $summ20_txt=  number_format_ua ($sum_zone20,2);
                
                $summ21_txt =  number_format_ua ($sum_zone21,2);
                $summ22_txt =  number_format_ua ($sum_zone22,2);
                $summ23_txt =  number_format_ua ($sum_zone23,2);
                $summ24_txt =  number_format_ua ($sum_zone24,2);
                $summ25_txt =  number_format_ua ($sum_zone25,2);
                $summ26_txt =  number_format_ua ($sum_zone26,2);
                $summ27_txt =  number_format_ua ($sum_zone27,2);
                $summ28_txt =  number_format_ua ($sum_zone28,2);
                $summ29_txt =  number_format_ua ($sum_zone29,2);
                $summ30_txt=  number_format_ua ($sum_zone30,2);
                $summ31_txt=  number_format_ua ($sum_zone31,2);                
                
                echo_html( "
                    <TR class='tab_head'>
                    <TD colspan='2'>ВСЬОГО $current_zone </td>
                    <td class='c_n'>{$summ_txt}</td>   
                    <td class='c_n'>{$summ1_txt}</td>
                    <td class='c_n'>{$summ2_txt}</td>
                    <td class='c_n'>{$summ3_txt}</td>
                    <td class='c_n'>{$summ4_txt}</td>
                    <td class='c_n'>{$summ5_txt}</td>
                    <td class='c_n'>{$summ6_txt}</td>
                    <td class='c_n'>{$summ7_txt}</td>
                    <td class='c_n'>{$summ8_txt}</td>
                    <td class='c_n'>{$summ9_txt}</td>
                    <td class='c_n'>{$summ10_txt}</td>

                    <td class='c_n'>{$summ11_txt}</td>
                    <td class='c_n'>{$summ12_txt}</td>
                    <td class='c_n'>{$summ13_txt}</td>
                    <td class='c_n'>{$summ14_txt}</td>
                    <td class='c_n'>{$summ15_txt}</td>
                    <td class='c_n'>{$summ16_txt}</td>
                    <td class='c_n'>{$summ17_txt}</td>
                    <td class='c_n'>{$summ18_txt}</td>
                    <td class='c_n'>{$summ19_txt}</td>
                    <td class='c_n'>{$summ20_txt}</td>

                    <td class='c_n'>{$summ21_txt}</td>
                    <td class='c_n'>{$summ22_txt}</td>
                    <td class='c_n'>{$summ23_txt}</td>
                    <td class='c_n'>{$summ24_txt}</td>
                    <td class='c_n'>{$summ25_txt}</td>
                    <td class='c_n'>{$summ26_txt}</td>
                    <td class='c_n'>{$summ27_txt}</td>
                    <td class='c_n'>{$summ28_txt}</td>
                    <td class='c_n'>{$summ29_txt}</td>
                    <td class='c_n'>{$summ30_txt}</td>
                    <td class='c_n'>{$summ31_txt}</td>
                 </tr> ",1);        
            }    
            
           $summ_txt =  number_format_ua ($sum_all,2);
           $summ1_txt =  number_format_ua ($sum_all1,2);
           $summ2_txt =  number_format_ua ($sum_all2,2);
           $summ3_txt =  number_format_ua ($sum_all3,2);
           $summ4_txt =  number_format_ua ($sum_all4,2);
           $summ5_txt =  number_format_ua ($sum_all5,2);
           $summ6_txt =  number_format_ua ($sum_all6,2);
           $summ7_txt =  number_format_ua ($sum_all7,2);
           $summ8_txt =  number_format_ua ($sum_all8,2);
           $summ9_txt =  number_format_ua ($sum_all9,2);
           $summ10_txt=  number_format_ua ($sum_all10,2);

           $summ11_txt =  number_format_ua ($sum_all11,2);
           $summ12_txt =  number_format_ua ($sum_all12,2);
           $summ13_txt =  number_format_ua ($sum_all13,2);
           $summ14_txt =  number_format_ua ($sum_all14,2);
           $summ15_txt =  number_format_ua ($sum_all15,2);
           $summ16_txt =  number_format_ua ($sum_all16,2);
           $summ17_txt =  number_format_ua ($sum_all17,2);
           $summ18_txt =  number_format_ua ($sum_all18,2);
           $summ19_txt =  number_format_ua ($sum_all19,2);
           $summ20_txt=  number_format_ua ($sum_all20,2);
              
           $summ21_txt =  number_format_ua ($sum_all21,2);
           $summ22_txt =  number_format_ua ($sum_all22,2);
           $summ23_txt =  number_format_ua ($sum_all23,2);
           $summ24_txt =  number_format_ua ($sum_all24,2);
           $summ25_txt =  number_format_ua ($sum_all25,2);
           $summ26_txt =  number_format_ua ($sum_all26,2);
           $summ27_txt =  number_format_ua ($sum_all27,2);
           $summ28_txt =  number_format_ua ($sum_all28,2);
           $summ29_txt =  number_format_ua ($sum_all29,2);
           $summ30_txt=  number_format_ua ($sum_all30,2);
           $summ31_txt=  number_format_ua ($sum_all31,2);          
            
    eval("\$footer_text_eval = \"$footer_text\";");
    echo_html( $footer_text_eval,1);
    
}
//------------------------------------------------------------------------------

if ($oper=='ind_dates_summary') 
{
    $p_mmgg = sql_field_val('dt_b', 'mmgg');
    $period = trim($_POST['period_str']);
    
    $p_id_region = sql_field_val('id_region', 'int');

    $params_caption ='';    
        
    $where = ' ';
    
    if ((isset($_POST['note']))&&($_POST['note']!=''))
    {
        $where.= $_POST['note'];
        $params_caption .= $_POST['note'];
    }
    
    
    
    if ($p_id_region!='null')
    {
        
        $where= "and exists (select rs.id from 
            prs_runner_paccnt as rp 
            join prs_runner_sectors as rs on (rs.id = rp.id_sector)
            where rp.id_paccnt = i.id_paccnt and rs.id_region =  $p_id_region ) ";        
        
        $SQL_H="SELECT name from cli_region_tbl where id = $p_id_region ;";
    
        $result = pg_query($Link, $SQL_H) or die("SQL Error: " . pg_last_error($Link) . $SQL_H);
        $row = pg_fetch_array($result);
        
        $params_caption.= $row['name'];        
    }
    
    
    eval("\$header_text = \"$header_text\";");
    echo_html( $header_text);
    
    $demand_all=0;
    $indic_cnt_all=0;
    $abon_cnt_all=0;
    
    $SQL = " select i.dat_ind,  to_char(i.dat_ind, 'DD.MM.YYYY') as dat_ind_txt,    
    count(distinct i.id_paccnt ) as cnt,    sum(i.value_cons)::int as demand
        from acm_indication_tbl as i
        where i.mmgg_corr = $p_mmgg
        and i.mmgg = $p_mmgg
        and coalesce(i.id_operation,0) <>23
        $where
        group by i.dat_ind
        order by i.dat_ind;";

    
    $result = pg_query($Link, $SQL) or die("SQL Error: " . pg_last_error($Link) . $SQL);
    if ($result) {
        
        $rows_count = pg_num_rows($result);
        
        $i=1;
        $np=0;        
        while ($row = pg_fetch_array($result)) {
            $np++;

            echo_html( " <tr>
            <td>{$row['dat_ind_txt']}</td>
            <td class='c_i'>{$row['cnt']}</td>
            <td class='c_i'>{$row['demand']}</td>
            </tr>
            ",1);

            $i++;
            $demand_all+=$row['demand'];
            $abon_cnt_all+=$row['cnt'];
        }
        
    }
    
    eval("\$footer_text_eval = \"$footer_text\";");
    echo_html( $footer_text_eval);
    
}
//-----------------------------------------------------------------------------


?>