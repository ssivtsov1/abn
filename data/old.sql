-- Function: public.job_counter_detail(date, character varying)

-- DROP FUNCTION public.job_counter_detail(date, character varying);

CREATE OR REPLACE FUNCTION public.job_counter_detail(
    IN date date,
    IN person character varying)
  RETURNS TABLE(sector character varying, runner character varying, place1 numeric, place2 numeric, place3 numeric, place4 numeric, place5 numeric, place6 numeric, cost1 numeric, cost2 numeric, cost3 numeric, cost4 numeric, cost5 numeric, cost6 numeric, cost_all numeric) AS
$BODY$

--drop table IF EXISTS tab1;
delete from tab2;

--create table tab1 (runner character varying, place1 bigint, place2 bigint, place3 bigint, place4 bigint, place5 bigint,
--   cost1 numeric, cost2 numeric, cost3 numeric, cost4 numeric, cost5 numeric, cost_all numeric);
   
insert into tab2(sector,runner,place1 , place2 , place3 , place4 , place5 , place6,
   cost1 , cost2 , cost3 , cost4 , cost5 , cost6, cost_all)
select sector,' ' as runner,count(CASE WHEN id_extra is null THEN 1 END) as place1,
count(CASE WHEN id_extra = 2 THEN 1 END) as place2,
count(CASE WHEN id_extra = 3 THEN 1 END) as place3,
count(CASE WHEN id_extra = 4 THEN 1 END) as place4,
count(CASE WHEN id_extra = 5 THEN 1 END) as place5,
count(CASE WHEN id_extra = 6 THEN 1 END) as place6,
cast(count(CASE WHEN id_extra is null THEN 1 END)/norm::numeric as numeric(7,2)) as cost2,
cast(count(CASE WHEN id_extra = 2 THEN 1 END)/norm::numeric as numeric(7,2)) as cost2,
cast(count(CASE WHEN id_extra = 3 THEN 1 END)/norm::numeric as numeric(7,2)) as cost3,
cast(count(CASE WHEN id_extra = 4 THEN 1 END)/norm::numeric as numeric(7,2)) as cost4,
cast(count(CASE WHEN id_extra = 5 THEN 1 END)/norm::numeric as numeric(7,2)) as cost5,
cast(count(CASE WHEN id_extra = 6 THEN 1 END)/norm::numeric as numeric(7,2)) as cost6,

(cast(count(CASE WHEN id_extra is null THEN 1 END)/norm::numeric as numeric(7,2)) +
cast(count(CASE WHEN id_extra = 2 THEN 1 END)/norm::numeric as numeric(7,2)) +
cast(count(CASE WHEN id_extra = 3 THEN 1 END)/norm::numeric as numeric(7,2)) +
cast(count(CASE WHEN id_extra = 4 THEN 1 END)/norm::numeric as numeric(7,2)) +
cast(count(CASE WHEN id_extra = 5 THEN 1 END)/norm::numeric as numeric(7,2)) +
cast(count(CASE WHEN id_extra = 6 THEN 1 END)/norm::numeric as numeric(7,2))) as cost_all
  from (select acc.code, min(pd.indic) as indic, sww.name as sector,qqq.norm,pzz.represent_name as runner, mp.id_extra 
from 
ind_pack_data as pd 
join clm_paccnt_tbl as acc on (acc.id = pd.id_paccnt)
join clm_abon_tbl as c on (c.id = acc.id_abon) 
join eqi_meter_tbl as im on (im.id = pd.id_type_meter)
join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class)
join adt_addr_tbl as adr_full on (adr_full.id = (acc.addr).id_class)
left join acm_indication_tbl as i on (i.id = pd.id_p_indic)

left join (
  select la.id_paccnt, count(*) as lgt_cnt, max(lg.alt_code) as lgt_code 
     from lgm_abon_tbl as la
     join lgi_group_tbl as lg on (lg.id = la.id_grp_lgt)
     where ((la.dt_start < ($1::date+'1 month'::interval) and la.dt_end is null)
            or 
            tintervalov(tinterval(la.dt_start::timestamp::abstime,la.dt_end::timestamp::abstime),tinterval($1::timestamp::abstime,($1::date+'1 month - 1 day'::interval)::timestamp::abstime)))
     group by la.id_paccnt order by la.id_paccnt
 )  as lg on (lg.id_paccnt = acc.id)
left join (
  select b.id_paccnt, s.id_zone,sum(s.demand) as p_demand
  from acm_bill_tbl as b 
  join acm_summ_tbl as s on (b.id_doc=s.id_doc)
  where  b.id_pref = 10
  and b.mmgg_bill = ($1::date -'1 month'::interval)::date
  group by b.id_paccnt, s.id_zone
) as bb on (bb.id_paccnt = acc.id and pd.id_zone = bb.id_zone)
left join (
 select distinct id_paccnt 
 from acm_subs_tbl as s 
 join (select max(mmgg) as mmgg from acm_subs_tbl where mmgg <= $1::date ) as mm on (mm.mmgg = s.mmgg)
 where tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($1::timestamp::abstime,($1::date+'1 month - 1 day'::interval)::timestamp::abstime))
 and s.dt_b is not null
 ) as sb on (sb.id_paccnt = acc.id)
left join clm_meterpoint_tbl as mp on (mp.id_paccnt = pd.id_paccnt)
left join eqk_meter_places_tbl as plc on (mp.id_extra=plc.id)
left join clm_notlive_tbl as n on (n.id_paccnt = pd.id_paccnt 
  and n.dt_b<=pd.work_period and ((n.dt_e is null) or (n.dt_e>=pd.work_period+'1 month - 1 day'::interval) ))
left join ( 
      select i.id_meter, i.id_zone, i.dat_ind as last_dat_ind, i.value as last_value, i.id_operation as last_operation
      from acm_indication_tbl as i 
      join (select id_meter, id_zone, max(dat_ind) as max_dat from acm_indication_tbl 
        group by id_meter, id_zone
        ) as mi
      on (i.id_meter = mi.id_meter and i.id_zone = mi.id_zone and i.dat_ind = mi.max_dat)
) as li on (li.id_meter = pd.id_meter and li.id_zone = pd.id_zone )
left join cli_indic_type_tbl as mit on (mit.id = li.last_operation)
left join clm_plandemand_tbl as plan on (plan.id_paccnt = acc.id and plan.id_zone = pd.id_zone and plan.mmgg = $1::date)
inner join ind_pack_header as zz on zz.id_pack=pd.id_pack and zz.work_period = date_trunc('month', $1::date)
inner join prs_runner_sectors as sww on (sww.id = zz.id_sector)
left join prs_persons as pzz on (pzz.id = zz.id_runner)
left join spr_norms_controller as qqq on (mp.id_extra=qqq.id)
where case when $2<>'' then pzz.represent_name=$2 else 1=1 end
group by 
acc.code, sww.name,qqq.norm,pzz.represent_name, mp.id_extra
) as ss
 
  --and EXTRACT(Month FROM last_dat_ind) = 12 and  EXTRACT(Year FROM last_dat_ind) = 2017
  --and  work_period = date_trunc('month', '2017-12-01'::date)
   group by 1,norm;


select sector,runner,
sum(place1) as place1,
sum(place2) as place2,
sum(place3) as place3,
sum(place4) as place4,
sum(place5) as place5,
sum(place6) as place6,
0::numeric as cost1,
sum(cost2) as cost2,
sum(cost3) as cost3,
sum(cost4) as cost4,
sum(cost5) as cost5,
sum(cost6) as cost6,
sum(cost_all) as cost_all
from tab2
where sector<>''
group by sector,runner
having sum(cost_all)>0
order by cast(regexp_replace(sector, ' .+', '') as int);


$BODY$
  LANGUAGE sql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION public.job_counter_detail(date, character varying)
  OWNER TO local;




-- Function: public.job_controler(date, date, date)

-- DROP FUNCTION public.job_controler(date, date, date);

CREATE OR REPLACE FUNCTION public.job_controler(
    IN date date,
    IN date1 date,
    IN date2 date)
  RETURNS TABLE(sector character varying, runner character varying, place1 numeric, place2 numeric, place3 numeric, place4 numeric, place5 numeric, place6 numeric, cost1 numeric, cost2 numeric, cost3 numeric, cost4 numeric, cost5 numeric, cost6 numeric, cost_all numeric) AS
$BODY$

--drop table IF EXISTS tab1;
delete from tab1;

--create table tab1 (runner character varying, place1 bigint, place2 bigint, place3 bigint, place4 bigint, place5 bigint,
--   cost1 numeric, cost2 numeric, cost3 numeric, cost4 numeric, cost5 numeric, cost_all numeric);
   
insert into tab1(runner,place1 , place2 , place3 , place4 , place5 , place6,
   cost1 , cost2 , cost3 , cost4 , cost5 , cost6, cost_all)
select runner,0 as place1,
count(CASE WHEN id_extra = 2 THEN 1 END) as place2,
count(CASE WHEN id_extra = 3 THEN 1 END) as place3,
count(CASE WHEN id_extra = 4 THEN 1 END) as place4,
count(CASE WHEN id_extra = 5 THEN 1 END) as place5,
count(CASE WHEN id_extra = 6 THEN 1 END) as place6,
0.00 as cost1,
count(CASE WHEN id_extra = 2 THEN 1 END)*priz_norm(cast(count(CASE WHEN id_extra = 2 THEN 1 END)/norm::numeric as numeric(7,3))*100,2) as cost2,
count(CASE WHEN id_extra = 3 THEN 1 END)*priz_norm(cast(count(CASE WHEN id_extra = 3 THEN 1 END)/norm::numeric as numeric(7,3))*100,3) as cost3,
count(CASE WHEN id_extra = 4 THEN 1 END)*priz_norm(cast(count(CASE WHEN id_extra = 4 THEN 1 END)/norm::numeric as numeric(7,3))*100,4) as cost4,
count(CASE WHEN id_extra = 5 THEN 1 END)*priz_norm(cast(count(CASE WHEN id_extra = 5 THEN 1 END)/norm::numeric as numeric(7,3))*100,5) as cost5,
count(CASE WHEN id_extra = 6 THEN 1 END)*priz_norm(cast(count(CASE WHEN id_extra = 6 THEN 1 END)/norm::numeric as numeric(7,3))*100,6) as cost6,

(0.00 +
count(CASE WHEN id_extra = 2 THEN 1 END)*priz_norm(cast(count(CASE WHEN id_extra = 2 THEN 1 END)/norm::numeric as numeric(7,3))*100,2) +
count(CASE WHEN id_extra = 3 THEN 1 END)*priz_norm(cast(count(CASE WHEN id_extra = 3 THEN 1 END)/norm::numeric as numeric(7,3))*100,3) +
count(CASE WHEN id_extra = 4 THEN 1 END)*priz_norm(cast(count(CASE WHEN id_extra = 4 THEN 1 END)/norm::numeric as numeric(7,3))*100,4) +
count(CASE WHEN id_extra = 5 THEN 1 END)*priz_norm(cast(count(CASE WHEN id_extra = 5 THEN 1 END)/norm::numeric as numeric(7,3))*100,5)+
count(CASE WHEN id_extra = 6 THEN 1 END)*priz_norm(cast(count(CASE WHEN id_extra = 6 THEN 1 END)/norm::numeric as numeric(7,3))*100,6)) as cost_all
  from (select acc.code, min(pd.indic) as indic, sww.name as sector,qqq.norm,pzz.represent_name as runner, mp.id_extra 
from 
ind_pack_data as pd 
join clm_paccnt_tbl as acc on (acc.id = pd.id_paccnt)
join clm_abon_tbl as c on (c.id = acc.id_abon) 
join eqi_meter_tbl as im on (im.id = pd.id_type_meter)
join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class)
join adt_addr_tbl as adr_full on (adr_full.id = (acc.addr).id_class)
left join acm_indication_tbl as i on (i.id = pd.id_p_indic)

left join (
  select la.id_paccnt, count(*) as lgt_cnt, max(lg.alt_code) as lgt_code 
     from lgm_abon_tbl as la
     join lgi_group_tbl as lg on (lg.id = la.id_grp_lgt)
     where ((la.dt_start < ($1::date+'1 month'::interval) and la.dt_end is null)
            or 
            tintervalov(tinterval(la.dt_start::timestamp::abstime,la.dt_end::timestamp::abstime),tinterval($1::timestamp::abstime,($1::date+'1 month - 1 day'::interval)::timestamp::abstime)))
     group by la.id_paccnt order by la.id_paccnt
 )  as lg on (lg.id_paccnt = acc.id)
left join (
  select b.id_paccnt, s.id_zone,sum(s.demand) as p_demand
  from acm_bill_tbl as b 
  join acm_summ_tbl as s on (b.id_doc=s.id_doc)
  where  b.id_pref = 10
  and b.mmgg_bill = ($1::date -'1 month'::interval)::date
  group by b.id_paccnt, s.id_zone
) as bb on (bb.id_paccnt = acc.id and pd.id_zone = bb.id_zone)
left join (
 select distinct id_paccnt 
 from acm_subs_tbl as s 
 join (select max(mmgg) as mmgg from acm_subs_tbl where mmgg <= $1::date ) as mm on (mm.mmgg = s.mmgg)
 where tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($1::timestamp::abstime,($1::date+'1 month - 1 day'::interval)::timestamp::abstime))
 and s.dt_b is not null
 ) as sb on (sb.id_paccnt = acc.id)
left join clm_meterpoint_tbl as mp on (mp.id_paccnt = pd.id_paccnt)
left join eqk_meter_places_tbl as plc on (mp.id_extra=plc.id)
left join clm_notlive_tbl as n on (n.id_paccnt = pd.id_paccnt 
  and n.dt_b<=pd.work_period and ((n.dt_e is null) or (n.dt_e>=pd.work_period+'1 month - 1 day'::interval) ))
left join ( 
      select i.id_meter, i.id_zone, i.dat_ind as last_dat_ind, i.value as last_value, i.id_operation as last_operation
      from acm_indication_tbl as i 
      join (select id_meter, id_zone, max(dat_ind) as max_dat from acm_indication_tbl 
        group by id_meter, id_zone
        ) as mi
      on (i.id_meter = mi.id_meter and i.id_zone = mi.id_zone and i.dat_ind = mi.max_dat)
) as li on (li.id_meter = pd.id_meter and li.id_zone = pd.id_zone )
left join cli_indic_type_tbl as mit on (mit.id = li.last_operation)
left join clm_plandemand_tbl as plan on (plan.id_paccnt = acc.id and plan.id_zone = pd.id_zone and plan.mmgg = $1::date)
inner join ind_pack_header as zz on zz.id_pack=pd.id_pack and (zz.work_period = date_trunc('month', $2::date) or zz.work_period = date_trunc('month', $3::date))
left join prs_runner_sectors as sww on (sww.id = zz.id_sector)
left join prs_persons as pzz on (pzz.id = zz.id_runner)
left join spr_norms_controller as qqq on (mp.id_extra=qqq.id)
where 
pd.dt_indic>=$2 and pd.dt_indic<=$3
group by 
acc.code, sww.name,qqq.norm,pzz.represent_name, mp.id_extra
) as ss
  where runner<>'' and indic<>0
  --and EXTRACT(Month FROM last_dat_ind) = 12 and  EXTRACT(Year FROM last_dat_ind) = 2017
  --and  work_period = date_trunc('month', '2017-12-01'::date)
   group by 1,norm;

select ' '::character varying as sector,runner,
0::numeric as place1,
sum(place2) as place2,
sum(place3) as place3,
sum(place4) as place4,
sum(place5) as place5,
sum(place6) as place6,
0::numeric as cost1,
sum(cost2) as cost2,
sum(cost3) as cost3,
sum(cost4) as cost4,
sum(cost5) as cost5,
sum(cost6) as cost6,
sum(cost_all) as cost_all
from tab1
group by runner
having sum(cost_all)>0
--order by cast(regexp_replace(sector, ' .+', '') as int);

$BODY$
  LANGUAGE sql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION public.job_controler(date, date, date)
  OWNER TO local;




-- Function: public.job_controler_detail(date, date, date, character varying)

-- DROP FUNCTION public.job_controler_detail(date, date, date, character varying);

CREATE OR REPLACE FUNCTION public.job_controler_detail(
    IN date date,
    IN date1 date,
    IN date2 date,
    IN person character varying)
  RETURNS TABLE(sector character varying, runner character varying, place1 numeric, place2 numeric, place3 numeric, place4 numeric, place5 numeric, place6 numeric, cost1 numeric, cost2 numeric, cost3 numeric, cost4 numeric, cost5 numeric, cost6 numeric, cost_all numeric) AS
$BODY$

--drop table IF EXISTS tab1;
delete from tab2;

--create table tab1 (runner character varying, place1 bigint, place2 bigint, place3 bigint, place4 bigint, place5 bigint,
--   cost1 numeric, cost2 numeric, cost3 numeric, cost4 numeric, cost5 numeric, cost_all numeric);
   
insert into tab2(sector,runner,place1 , place2 , place3 , place4 , place5 , place6,
   cost1 , cost2 , cost3 , cost4 , cost5 , cost6, cost_all)
select sector,runner,0 as place1,
count(CASE WHEN id_extra = 2 THEN 1 END) as place2,
count(CASE WHEN id_extra = 3 THEN 1 END) as place3,
count(CASE WHEN id_extra = 4 THEN 1 END) as place4,
count(CASE WHEN id_extra = 5 THEN 1 END) as place5,
count(CASE WHEN id_extra = 6 THEN 1 END) as place6,
0.00 as cost1,
count(CASE WHEN id_extra = 2 THEN 1 END)*priz_norm(cast(count(CASE WHEN id_extra = 2 THEN 1 END)/norm::numeric as numeric(7,3))*100,2) as cost2,
count(CASE WHEN id_extra = 3 THEN 1 END)*priz_norm(cast(count(CASE WHEN id_extra = 3 THEN 1 END)/norm::numeric as numeric(7,3))*100,3) as cost3,
count(CASE WHEN id_extra = 4 THEN 1 END)*priz_norm(cast(count(CASE WHEN id_extra = 4 THEN 1 END)/norm::numeric as numeric(7,3))*100,4) as cost4,
count(CASE WHEN id_extra = 5 THEN 1 END)*priz_norm(cast(count(CASE WHEN id_extra = 5 THEN 1 END)/norm::numeric as numeric(7,3))*100,5) as cost5,
count(CASE WHEN id_extra = 6 THEN 1 END)*priz_norm(cast(count(CASE WHEN id_extra = 6 THEN 1 END)/norm::numeric as numeric(7,3))*100,6) as cost6,

(0.00 +
count(CASE WHEN id_extra = 2 THEN 1 END)*priz_norm(cast(count(CASE WHEN id_extra = 2 THEN 1 END)/norm::numeric as numeric(7,3))*100,2) +
count(CASE WHEN id_extra = 3 THEN 1 END)*priz_norm(cast(count(CASE WHEN id_extra = 3 THEN 1 END)/norm::numeric as numeric(7,3))*100,3) +
count(CASE WHEN id_extra = 4 THEN 1 END)*priz_norm(cast(count(CASE WHEN id_extra = 4 THEN 1 END)/norm::numeric as numeric(7,3))*100,4) +
count(CASE WHEN id_extra = 5 THEN 1 END)*priz_norm(cast(count(CASE WHEN id_extra = 5 THEN 1 END)/norm::numeric as numeric(7,3))*100,5)+
count(CASE WHEN id_extra = 6 THEN 1 END)*priz_norm(cast(count(CASE WHEN id_extra = 6 THEN 1 END)/norm::numeric as numeric(7,3))*100,6)) as cost_all
  from (select acc.code, min(pd.indic) as indic, sww.name as sector,qqq.norm,pzz.represent_name as runner, mp.id_extra 
  
from 
ind_pack_data as pd 
join clm_paccnt_tbl as acc on (acc.id = pd.id_paccnt)
join clm_abon_tbl as c on (c.id = acc.id_abon) 
join eqi_meter_tbl as im on (im.id = pd.id_type_meter)
join adt_addr_town_street_tbl as adr on (adr.id = (acc.addr).id_class)
join adt_addr_tbl as adr_full on (adr_full.id = (acc.addr).id_class)
left join acm_indication_tbl as i on (i.id = pd.id_p_indic)

left join (
  select la.id_paccnt, count(*) as lgt_cnt, max(lg.alt_code) as lgt_code 
     from lgm_abon_tbl as la
     join lgi_group_tbl as lg on (lg.id = la.id_grp_lgt)
     where ((la.dt_start < ($1::date+'1 month'::interval) and la.dt_end is null)
            or 
            tintervalov(tinterval(la.dt_start::timestamp::abstime,la.dt_end::timestamp::abstime),tinterval($1::timestamp::abstime,($1::date+'1 month - 1 day'::interval)::timestamp::abstime)))
     group by la.id_paccnt order by la.id_paccnt
 )  as lg on (lg.id_paccnt = acc.id)
left join (
  select b.id_paccnt, s.id_zone,sum(s.demand) as p_demand
  from acm_bill_tbl as b 
  join acm_summ_tbl as s on (b.id_doc=s.id_doc)
  where  b.id_pref = 10
  and b.mmgg_bill = ($1::date -'1 month'::interval)::date
  group by b.id_paccnt, s.id_zone
) as bb on (bb.id_paccnt = acc.id and pd.id_zone = bb.id_zone)
left join (
 select distinct id_paccnt 
 from acm_subs_tbl as s 
 join (select max(mmgg) as mmgg from acm_subs_tbl where mmgg <= $1::date ) as mm on (mm.mmgg = s.mmgg)
 where tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval($1::timestamp::abstime,($1::date+'1 month - 1 day'::interval)::timestamp::abstime))
 and s.dt_b is not null
 ) as sb on (sb.id_paccnt = acc.id)
left join clm_meterpoint_tbl as mp on (mp.id_paccnt = pd.id_paccnt)
left join eqk_meter_places_tbl as plc on (mp.id_extra=plc.id)
left join clm_notlive_tbl as n on (n.id_paccnt = pd.id_paccnt 
  and n.dt_b<=pd.work_period and ((n.dt_e is null) or (n.dt_e>=pd.work_period+'1 month - 1 day'::interval) ))
left join ( 
      select i.id_meter, i.id_zone, i.dat_ind as last_dat_ind, i.value as last_value, i.id_operation as last_operation
      from acm_indication_tbl as i 
      join (select id_meter, id_zone, max(dat_ind) as max_dat from acm_indication_tbl 
        group by id_meter, id_zone
        ) as mi
      on (i.id_meter = mi.id_meter and i.id_zone = mi.id_zone and i.dat_ind = mi.max_dat)
) as li on (li.id_meter = pd.id_meter and li.id_zone = pd.id_zone )
left join cli_indic_type_tbl as mit on (mit.id = li.last_operation)
left join clm_plandemand_tbl as plan on (plan.id_paccnt = acc.id and plan.id_zone = pd.id_zone and plan.mmgg = $1::date)
inner join ind_pack_header as zz on zz.id_pack=pd.id_pack and (zz.work_period = date_trunc('month', $2::date) or zz.work_period = date_trunc('month', $3::date))
inner join prs_runner_sectors as sww on (sww.id = zz.id_sector)
left join prs_persons as pzz on (pzz.id = zz.id_runner)
left join spr_norms_controller as qqq on (mp.id_extra=qqq.id)
where 
pd.dt_indic>=$2 and pd.dt_indic<=$3
and case when $4<>'' then pzz.represent_name=$4 else 1=1 end
group by 
acc.code, sww.name,qqq.norm,pzz.represent_name, mp.id_extra
) as ss
  where runner<>'' and indic<>0
  
  --and EXTRACT(Month FROM last_dat_ind) = 12 and  EXTRACT(Year FROM last_dat_ind) = 2017
  --and  work_period = date_trunc('month', '2017-12-01'::date)
   group by 1,2,norm;

select sector,runner,
0::numeric as place1,
sum(place2) as place2,
sum(place3) as place3,
sum(place4) as place4,
sum(place5) as place5,
sum(place6) as place6,
0::numeric as cost1,
sum(cost2) as cost2,
sum(cost3) as cost3,
sum(cost4) as cost4,
sum(cost5) as cost5,
sum(cost6) as cost6,
sum(cost_all) as cost_all
from tab2
where sector<>''
group by sector,runner
having sum(cost_all)>0
order by cast(regexp_replace(sector, ' .+', '') as int);

$BODY$
  LANGUAGE sql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION public.job_controler_detail(date, date, date, character varying)
  OWNER TO local;

