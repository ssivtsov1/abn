select a.*,b.addr,c.nm,c.ident,adr.is_town
from acm_bill_tbl a
inner join clm_paccnt_tbl b on
a.id_paccnt=b.id
join aqi_grptar_tbl c on
b.id_gtar=c.id
join adt_addr_tbl as adr on 
(adr.id = (b.addr).id_class)
--join acm_summ_tbl as bs on
--(a.id_doc= bs.id_doc) 
where a.mmgg_bill='2018-03-01' and a.id_pref = 10 
and adr.is_town = 1 and (c.ident~'tgr7_3' or (c.ident~'tgr7_51') or (c.ident~'tgr7_53') or c.ident~'tgr7_1' or c.ident~'tgr7_6') and a.value_calc<>0
order by a.value_calc


