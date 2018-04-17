select * from (
 select b.*, 
 acc.book, acc.code, CASE WHEN acc.rem_worker THEN 1 END as rem_worker,
 (adr.street||' '||
   (coalesce('буд.'||(acc.addr).house||'','')||
		coalesce('/'||(acc.addr).slash||' ','')||
			coalesce(' корп.'||(acc.addr).korp||'','')||
				coalesce(', кв. '||(acc.addr).flat,'')||
					coalesce('/'||(acc.addr).f_slash||' ',''))::varchar
)::varchar as addr,
 adr.town, adr.street, 
 (coalesce((acc.addr).house,'')||coalesce('/'||(acc.addr).slash,''))::varchar as house,
 (acc.addr).korp, 
 (coalesce((acc.addr).flat,'')|| coalesce('/'||(acc.addr).f_slash,''))::varchar as flat,
(c.last_name||' '||coalesce(c.name,'')||' '||coalesce(c.patron_name,''))::varchar as abon,
('0'||substring(acc.code FROM '[0-9]+'))::int as int_code,
('0'||substring(acc.book FROM '[0-9]+'))::int as int_book,
rp.id_sector, coalesce(rs.name,'')::varchar as sector, u1.name as user_name 
from acm_bill_tbl as b
join clm_paccnt_tbl as acc on (acc.id = b.id_paccnt)
join clm_abon_tbl as c on (c.id = acc.id_abon) 
join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class)
left join prs_runner_paccnt as rp on (rp.id_paccnt = acc.id)
left join prs_runner_sectors as rs on (rs.id = rp.id_sector)
left join syi_user as u1 on (u1.id = b.id_person)
) as ss
   where  mmgg = date_trunc('month', '2018-01-01'::date) and id_pref = 10  Order by int_book ASC, book ASC ,int_code asc ,code asc LIMIT 50 OFFSET 0 