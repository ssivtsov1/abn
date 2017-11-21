<?php

if ($oper=='tarif_abon_sum')
{
    $p_mmgg = sql_field_val('dt_b', 'date');

    list($year, $month, $day  ) = split('[/.-]', $p_mmgg);
    $month_str = ukr_month((int)$month,0);
    $period = "за $month_str $year р.";
    $period_str= str_replace("'",'',$period);
    
    $p_id_tar = sql_field_val('id_gtar', 'int');        
    
    $params_caption = '';
    $where = '';
    
    if ($p_id_tar!='null')
    {
        $where.= " and t.id_grptar = $p_id_tar ";
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

    $SQL = "insert into rep_zvit_lgt_tbl (id_paccnt,id_grptar,id_tarif,is_town,summ_lgt)
  select b.id_paccnt,t.id_grptar,ls.id_tarif,adr.is_town, sum(ls.summ_lgt) as sum_lgt
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
 where b.idk_doc = 200 and b.id_pref = 10
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
	where b.idk_doc = 200 and b.id_pref = 10
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
?>
