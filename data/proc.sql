-- Function: public.rep_zvit_fun(date, integer, integer, integer)

-- DROP FUNCTION public.rep_zvit_fun(date, integer, integer, integer);

CREATE OR REPLACE FUNCTION public.rep_zvit_fun(
    date,
    integer,
    integer,
    integer)
  RETURNS integer AS
$BODY$
declare 
  pmmgg alias for $1; 
  pid_paccnt alias for $2; 
  pid_town alias for $3; 
  prebuild alias for $4; 

  v int;
  r record;
  rr record;
  vmmgg date;
  vdepartment int;
  vid_paccnt int;
  vcur_mmgg date;

  tabl varchar;
  del  varchar;
  nul varchar;
  SQL  varchar;
  kodres int;
  vdel_ng int;

  vname1 varchar;
  vname2 varchar;
  vstr_num varchar;
  vstr_num_old varchar;
BEGIN

 vmmgg = date_trunc('month',pmmgg);

 select into kodres value_ident::int as value from syi_sysvars_tbl where ident='id_res';

 vdepartment:= kodres;

 vid_paccnt:= coalesce(pid_paccnt,0);

 vcur_mmgg:= fun_mmgg();


<<label_calc>>
BEGIN

 raise notice '0';
-- if (pmmgg='2016-02-01') then
--  return rep_zvit_022016_fun(pmmgg, pid_paccnt, pid_town);
-- end if;

 if pmmgg < vcur_mmgg then

  --delete from seb_saldo_tmp;--
  --insert into seb_saldo_tmp select * from seb_saldo where mmgg = pmmgg and id_pref = 10 ;--

  raise notice 'EXIT';

  EXIT label_calc;

 else

  raise notice 'calc';
  delete from rep_zvit_tmp;


  if prebuild = 1 then
    perform  seb_saldo(vmmgg,0,null);
  end if;
 end if;


 -- delete from rep_zvit_tmp where mmgg=vmmgg ;
 --vdepartment = getsysvar('kod_res');
 ------------------------------------------------------------------------------------------------------------------------------------

 delete from rep_zvit_lgt_tbl;
 delete from rep_zvit_lgt_oldt_tbl;

 delete from rep_zvit_lgt_bill_tbl;
 delete from rep_zvit_lgt_billoldt_tbl;
-- delete from rep_zvit_lgt_dod_tbl;
 delete from rep_zvit_lgt_chnoe_tbl;

 delete from rep_zvit_subs_tbl;
 delete from rep_zvit_subs_ret_tbl;
 delete from clm_paccnt_tmp;


 if pid_town< 0 then 

   insert into clm_paccnt_tmp
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
    ((dt_b < (vmmgg::date+'1 month'::interval) and dt_e is null)
    or 
    tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval(vmmgg::timestamp::abstime,(vmmgg::date+'1 month - 1 day'::interval)::timestamp::abstime)))
   group by id order by id) as a2 on (a.id = a2.id and a2.dt = a.dt_b)
   join adt_addr_town_street_tbl as adr on (adr.id = (a.addr).id_class)    
   join prs_runner_paccnt as rp on (rp.id_paccnt = a.id)
   join prs_runner_sectors as rs on (rs.id = rp.id_sector)
   join ( 
   select min(t.id) as id_tar, t.id_grptar
	from aqm_tarif_tbl as t 
        where ((dt_e is null ) or (dt_e > vmmgg )) and (dt_b <= vmmgg)
        and (t.per_min is null or (t.per_min <= vmmgg and t.per_max >= vmmgg))
        group by id_grptar
   ) as tt on (tt.id_grptar = a.id_gtar)
   where  rs.id_region = - pid_town;

   select into v coalesce(code_res,0)   from  cli_region_tbl  where id = - pid_town; 

   if (v<>0) then

	kodres := v;
	vdepartment:=v;

        if kodres = 240 then
          -- Прилуки - нужно и общий отчет, и город/район отдельно
  	  kodres := 242;
	  vdepartment:=242;
        end if;        

        if kodres = 200 then
          -- Мена - нужно и общий отчет, и Мена/Сосница отдельно
  	  kodres := 202;
	  vdepartment:=202;
        end if;        
        
   end if; 

 else 

   insert into clm_paccnt_tmp
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
    ((dt_b < (vmmgg::date+'1 month'::interval) and dt_e is null)
    or 
    tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval(vmmgg::timestamp::abstime,(vmmgg::date+'1 month - 1 day'::interval)::timestamp::abstime)))
   group by id order by id) as a2 on (a.id = a2.id and a2.dt = a.dt_b)
   join adt_addr_town_street_tbl as adr on (adr.id = (a.addr).id_class)    
   join ( 
   select min(t.id) as id_tar, t.id_grptar
	from aqm_tarif_tbl as t 
        where ((dt_e is null ) or (dt_e > vmmgg )) and (dt_b <= vmmgg)
        and (t.per_min is null or (t.per_min <= vmmgg and t.per_max >= vmmgg))
        group by id_grptar
   ) as tt on (tt.id_grptar = a.id_gtar)
   where (pid_paccnt is null or a.id = pid_paccnt) and (pid_town is null or adr.id_town = pid_town);    ----!!!

 end if; 



 raise notice '1-1';

 insert into rep_zvit_lgt_tbl (id_paccnt,id_grp_lgt,id_grptar,id_tarif,is_town,summ_lgt)
 select ls.id_paccnt, ls.id_grp_lgt, 
 a.id_gtar, --coalesce(t.id_grptar,a.id_gtar), 
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
  and b.mmgg = vmmgg    -- все доп.льготы будут в счете      
  and g.id_budjet <> 3
--  and ls.dat_b >='2017-03-01'  
  and (tv.id is null or tv.dt_begin >='2017-03-01')
 ) as ls
  join clm_paccnt_tmp as a on (a.id = ls.id_paccnt)
  join adt_addr_tbl as adr on (adr.id = (a.addr).id_class) 
  join ( 
   select min(t.id) as id_tar, t.id_grptar
	from aqm_tarif_tbl as t 
        where ((dt_e is null ) or (dt_e > vmmgg )) and (dt_b <= vmmgg)
        and (t.per_min is null or (t.per_min <= vmmgg and t.per_max >= vmmgg))
        group by id_grptar
  ) as tt on (tt.id_grptar = a.id_gtar)
  left join aqm_tarif_tbl as t on (t.id = ls.id_tarif)
  where coalesce(t.id_grptar,0) not in (5,6,8,9,12,13) 
  and   coalesce(a.id_gtar,0) not in (5,6,8,9,12,13) 
 group by ls.id_paccnt,ls.id_grp_lgt,a.id_gtar, CASE WHEN coalesce(t.id_grptar,0)<>coalesce(a.id_gtar,0) THEN tt.id_tar ELSE ls.id_tarif END ,adr.is_town;


 raise notice '1-1m';
 -- отдельно выбираем данные по тарифам многодетных, чтобы точно учесть строчки многодетных
 -- по исстинным тарифам из счетов
 insert into rep_zvit_lgt_tbl (id_paccnt,id_grp_lgt,id_grptar,id_tarif,is_town,summ_lgt)
 select ls.id_paccnt, ls.id_grp_lgt, 
 a.id_gtar, --coalesce(t.id_grptar,a.id_gtar), 
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
  and b.mmgg = vmmgg    -- все доп.льготы будут в счете      
  and g.id_budjet <> 3 
  and (tv.id is null or tv.dt_begin >='2017-03-01')
--  and ls.dat_b >='2017-03-01'   
) as ls
  join clm_paccnt_tmp as a on (a.id = ls.id_paccnt)
  join adt_addr_tbl as adr on (adr.id = (a.addr).id_class) 
  join ( 
   select min(t.id) as id_tar, t.id_grptar
	from aqm_tarif_tbl as t 
        where ((dt_e is null ) or (dt_e > vmmgg )) and (dt_b <= vmmgg)
        and (t.per_min is null or (t.per_min <= vmmgg and t.per_max >= vmmgg))
        group by id_grptar
  ) as tt on (tt.id_grptar = a.id_gtar)
  left join aqm_tarif_tbl as t on (t.id = ls.id_tarif)
  where (coalesce(t.id_grptar,0) in (5,6,8,9,12,13) 
     or  coalesce(a.id_gtar,0)   in (5,6,8,9,12,13) )
 group by ls.id_paccnt,ls.id_grp_lgt,
 a.id_gtar, --coalesce(t.id_grptar,a.id_gtar), 
 coalesce(ls.id_tarif,tt.id_tar),adr.is_town;


 raise notice '1-2';

 insert into rep_zvit_lgt_oldt_tbl (id_paccnt,id_grp_lgt,id_grptar,id_tarif,is_town,summ_lgt)
 select ls.id_paccnt, ls.id_grp_lgt, 
 a.id_gtar, --coalesce(t.id_grptar,a.id_gtar), 
 CASE WHEN coalesce(t.id_grptar,0)<>coalesce(a.id_gtar,0) THEN tt.id_tar ELSE ls.id_tarif END , --coalesce(ls.id_tarif,tt.id_tar),
 adr.is_town, sum(ls.summ_lgt) as sum_lgt
 from 
 (
  select b.id_paccnt, ls.id_grp_lgt, ls.summ_lgt, ls.id_tarif
  from acm_bill_tbl as b 
  join acm_lgt_summ_tbl as ls on (b.id_doc= ls.id_doc) 
  join lgi_group_tbl as g on(g.id = ls.id_grp_lgt)
  join aqd_tarif_tbl as tv on (tv.id = ls.id_summtarif)
  where b.idk_doc in (200,220,209,291) and b.id_pref = 10
  and b.mmgg = vmmgg    -- все доп.льготы будут в счете      
  and g.id_budjet <> 3
--  and ls.dat_b <'2017-03-01'  
  and ( tv.dt_begin < '2017-03-01' )
  ) as ls
  join clm_paccnt_tmp as a on (a.id = ls.id_paccnt)
  join adt_addr_tbl as adr on (adr.id = (a.addr).id_class) 
  join ( 
   select min(t.id) as id_tar, t.id_grptar
	from aqm_tarif_tbl as t 
        where ((dt_e is null ) or (dt_e > '2017-02-01' )) and (dt_b <= '2017-02-01')
        and (t.per_min is null or (t.per_min <= '2017-02-01' and t.per_max >= '2017-02-01'))
        group by id_grptar
  ) as tt on (tt.id_grptar = a.id_gtar)
  left join aqm_tarif_tbl as t on (t.id = ls.id_tarif)
  where coalesce(t.id_grptar,0) not in (5,6,8,9,12,13) 
  and   coalesce(a.id_gtar,0) not in (5,6,8,9,12,13) 
 group by ls.id_paccnt,ls.id_grp_lgt,a.id_gtar, CASE WHEN coalesce(t.id_grptar,0)<>coalesce(a.id_gtar,0) THEN tt.id_tar ELSE ls.id_tarif END ,adr.is_town;


 raise notice '1-2m';
 -- отдельно выбираем данные по тарифам многодетных, чтобы точно учесть строчки многодетных
 -- по исстинным тарифам из счетов
 insert into rep_zvit_lgt_oldt_tbl (id_paccnt,id_grp_lgt,id_grptar,id_tarif,is_town,summ_lgt)
 select ls.id_paccnt, ls.id_grp_lgt, 
-- coalesce(t.id_grptar,a.id_gtar), 
 a.id_gtar, --coalesce(t.id_grptar,a.id_gtar), 
 coalesce(ls.id_tarif,tt.id_tar),
 adr.is_town, sum(ls.summ_lgt) as sum_lgt
 from 
 (
  select b.id_paccnt, ls.id_grp_lgt, ls.summ_lgt, ls.id_tarif
  from acm_bill_tbl as b 
  join acm_lgt_summ_tbl as ls on (b.id_doc= ls.id_doc) 
  join lgi_group_tbl as g on(g.id = ls.id_grp_lgt)
  join aqd_tarif_tbl as tv on (tv.id = ls.id_summtarif)
  where b.idk_doc in (200,220,209,291) and b.id_pref = 10
  and b.mmgg = vmmgg    -- все доп.льготы будут в счете      
  and g.id_budjet <> 3 
  and ( tv.dt_begin < '2017-03-01' )
--  and ls.dat_b <'2017-03-01'   
) as ls
  join clm_paccnt_tmp as a on (a.id = ls.id_paccnt)
  join adt_addr_tbl as adr on (adr.id = (a.addr).id_class) 
  join ( 
   select min(t.id) as id_tar, t.id_grptar
	from aqm_tarif_tbl as t 
        where ((dt_e is null ) or (dt_e > '2017-02-01' )) and (dt_b <= '2017-02-01')
        and (t.per_min is null or (t.per_min <= '2017-02-01' and t.per_max >= '2017-02-01'))
        group by id_grptar
  ) as tt on (tt.id_grptar = a.id_gtar)
  left join aqm_tarif_tbl as t on (t.id = ls.id_tarif)
  where (coalesce(t.id_grptar,0) in (5,6,8,9,12,13) 
     or  coalesce(a.id_gtar,0)   in (5,6,8,9,12,13) )
 group by ls.id_paccnt,ls.id_grp_lgt,
 a.id_gtar, --coalesce(t.id_grptar,a.id_gtar), 
 coalesce(ls.id_tarif,tt.id_tar),adr.is_town;


 raise notice '1-3';
-------------------------------------------

 insert into rep_zvit_lgt_bill_tbl (id_paccnt,id_grp_lgt,id_grptar,id_tarif,is_town,summ_lgt)
 select ls.id_paccnt, ls.id_grp_lgt, 
 a.id_gtar, --coalesce(t.id_grptar,a.id_gtar), 
 CASE WHEN coalesce(t.id_grptar,0)<>coalesce(a.id_gtar,0) THEN tt.id_tar ELSE ls.id_tarif END , --coalesce(ls.id_tarif,tt.id_tar),
 adr.is_town, sum(ls.summ_lgt) as sum_lgt
 from 
 (
  select b.id_paccnt, ls.id_grp_lgt, ls.summ_lgt, ls.id_tarif
  from acm_bill_tbl as b 
  join acm_lgt_summ_tbl as ls on (b.id_doc= ls.id_doc) 
  left join aqd_tarif_tbl as tv on (tv.id = ls.id_summtarif)
--  join lgi_group_tbl as g on(g.id = ls.id_grp_lgt)
  where b.idk_doc in (200,220,209,291) and b.id_pref = 10
  and b.mmgg = vmmgg    
--  and ls.dat_b >='2017-03-01'  
  and (tv.id is null or tv.dt_begin >='2017-03-01')
--  and g.id_budjet <> 3        
) as ls
  join clm_paccnt_tmp as a on (a.id = ls.id_paccnt)
  join adt_addr_tbl as adr on (adr.id = (a.addr).id_class) 
  join ( 
   select min(t.id) as id_tar, t.id_grptar
	from aqm_tarif_tbl as t 
        where ((dt_e is null ) or (dt_e > vmmgg )) and (dt_b <= vmmgg)
        and (t.per_min is null or (t.per_min <= vmmgg and t.per_max >= vmmgg))
        group by id_grptar
  ) as tt on (tt.id_grptar = a.id_gtar)
  left join aqm_tarif_tbl as t on (t.id = ls.id_tarif)
  where coalesce(t.id_grptar,0) not in (5,6,8,9,12,13) 
  and   coalesce(a.id_gtar,0) not in (5,6,8,9,12,13) 
-- group by ls.id_paccnt,ls.id_grp_lgt,coalesce(t.id_grptar,a.id_gtar),coalesce(ls.id_tarif,tt.id_tar),adr.is_town;
 group by ls.id_paccnt,ls.id_grp_lgt,a.id_gtar, CASE WHEN coalesce(t.id_grptar,0)<>coalesce(a.id_gtar,0) THEN tt.id_tar ELSE ls.id_tarif END ,adr.is_town;

 raise notice '1-3-m';

 insert into rep_zvit_lgt_bill_tbl (id_paccnt,id_grp_lgt,id_grptar,id_tarif,is_town,summ_lgt)
 select ls.id_paccnt, ls.id_grp_lgt, 
 coalesce(t.id_grptar,a.id_gtar), 
 coalesce(ls.id_tarif,tt.id_tar),
 adr.is_town, sum(ls.summ_lgt) as sum_lgt
 from 
 (
  select b.id_paccnt, ls.id_grp_lgt, ls.summ_lgt, ls.id_tarif
  from acm_bill_tbl as b 
  join acm_lgt_summ_tbl as ls on (b.id_doc= ls.id_doc) 
  left join aqd_tarif_tbl as tv on (tv.id = ls.id_summtarif)
--  join lgi_group_tbl as g on(g.id = ls.id_grp_lgt)
  where b.idk_doc in (200,220,209,291) and b.id_pref = 10
  and b.mmgg = vmmgg    
--  and ls.dat_b >='2017-03-01'  
  and (tv.id is null or tv.dt_begin >='2017-03-01')
--  and g.id_budjet <> 3        
) as ls
  join clm_paccnt_tmp as a on (a.id = ls.id_paccnt)
  join adt_addr_tbl as adr on (adr.id = (a.addr).id_class) 
  join ( 
   select min(t.id) as id_tar, t.id_grptar
	from aqm_tarif_tbl as t 
        where ((dt_e is null ) or (dt_e > vmmgg )) and (dt_b <= vmmgg)
        and (t.per_min is null or (t.per_min <= vmmgg and t.per_max >= vmmgg))
        group by id_grptar
  ) as tt on (tt.id_grptar = a.id_gtar)
  left join aqm_tarif_tbl as t on (t.id = ls.id_tarif)
  where (coalesce(t.id_grptar,0) in (5,6,8,9,12,13) 
     or  coalesce(a.id_gtar,0)   in (5,6,8,9,12,13) )
 group by ls.id_paccnt,ls.id_grp_lgt,coalesce(t.id_grptar,a.id_gtar),coalesce(ls.id_tarif,tt.id_tar),adr.is_town;


 raise notice '1-4';

 insert into rep_zvit_lgt_billoldt_tbl (id_paccnt,id_grp_lgt,id_grptar,id_tarif,is_town,summ_lgt)
 select ls.id_paccnt, ls.id_grp_lgt, 
 a.id_gtar, --coalesce(t.id_grptar,a.id_gtar), 
 CASE WHEN coalesce(t.id_grptar,0)<>coalesce(a.id_gtar,0) THEN tt.id_tar ELSE ls.id_tarif END , --coalesce(ls.id_tarif,tt.id_tar),
 adr.is_town, sum(ls.summ_lgt) as sum_lgt
 from 
 (
  select b.id_paccnt, ls.id_grp_lgt, ls.summ_lgt, ls.id_tarif
  from acm_bill_tbl as b 
  join acm_lgt_summ_tbl as ls on (b.id_doc= ls.id_doc) 
  join aqd_tarif_tbl as tv on (tv.id = ls.id_summtarif)
--  join lgi_group_tbl as g on(g.id = ls.id_grp_lgt)
  where b.idk_doc in (200,220,209,291) and b.id_pref = 10
  and b.mmgg = vmmgg    
--  and ls.dat_b <'2017-03-01'  
  and ( tv.dt_begin < '2017-03-01' )
--  and g.id_budjet <> 3        
) as ls
  join clm_paccnt_tmp as a on (a.id = ls.id_paccnt)
  join adt_addr_tbl as adr on (adr.id = (a.addr).id_class) 
  join ( 
   select min(t.id) as id_tar, t.id_grptar
	from aqm_tarif_tbl as t 
        where ((dt_e is null ) or (dt_e > '2017-02-01' )) and (dt_b <= '2017-02-01')
        and (t.per_min is null or (t.per_min <= '2017-02-01' and t.per_max >= '2017-02-01'))
        group by id_grptar
  ) as tt on (tt.id_grptar = a.id_gtar)
  left join aqm_tarif_tbl as t on (t.id = ls.id_tarif)
  where coalesce(t.id_grptar,0) not in (5,6,8,9,12,13) 
  and   coalesce(a.id_gtar,0) not in (5,6,8,9,12,13) 
-- group by ls.id_paccnt,ls.id_grp_lgt,coalesce(t.id_grptar,a.id_gtar),coalesce(ls.id_tarif,tt.id_tar),adr.is_town;
 group by ls.id_paccnt,ls.id_grp_lgt,a.id_gtar, CASE WHEN coalesce(t.id_grptar,0)<>coalesce(a.id_gtar,0) THEN tt.id_tar ELSE ls.id_tarif END ,adr.is_town;

 raise notice '1-4-m';

 insert into rep_zvit_lgt_billoldt_tbl (id_paccnt,id_grp_lgt,id_grptar,id_tarif,is_town,summ_lgt)
 select ls.id_paccnt, ls.id_grp_lgt, 
 coalesce(t.id_grptar,a.id_gtar), 
 coalesce(ls.id_tarif,tt.id_tar),
 adr.is_town, sum(ls.summ_lgt) as sum_lgt
 from 
 (
  select b.id_paccnt, ls.id_grp_lgt, ls.summ_lgt, ls.id_tarif
  from acm_bill_tbl as b 
  join acm_lgt_summ_tbl as ls on (b.id_doc= ls.id_doc) 
  join aqd_tarif_tbl as tv on (tv.id = ls.id_summtarif)
--  join lgi_group_tbl as g on(g.id = ls.id_grp_lgt)
  where b.idk_doc in (200,220,209,291) and b.id_pref = 10
  and b.mmgg = vmmgg    
--  and ls.dat_b <'2017-03-01'  
  and ( tv.dt_begin < '2017-03-01' )
--  and g.id_budjet <> 3        
) as ls
  join clm_paccnt_tmp as a on (a.id = ls.id_paccnt)
  join adt_addr_tbl as adr on (adr.id = (a.addr).id_class) 
  join ( 
   select min(t.id) as id_tar, t.id_grptar
	from aqm_tarif_tbl as t 
        where ((dt_e is null ) or (dt_e > '2017-02-01' )) and (dt_b <= '2017-02-01')
        and (t.per_min is null or (t.per_min <= '2017-02-01' and t.per_max >= '2017-02-01'))
        group by id_grptar
  ) as tt on (tt.id_grptar = a.id_gtar)
  left join aqm_tarif_tbl as t on (t.id = ls.id_tarif)
  where (coalesce(t.id_grptar,0) in (5,6,8,9,12,13) 
     or  coalesce(a.id_gtar,0)   in (5,6,8,9,12,13) )
 group by ls.id_paccnt,ls.id_grp_lgt,coalesce(t.id_grptar,a.id_gtar),coalesce(ls.id_tarif,tt.id_tar),adr.is_town;

 raise notice '1-5';

 insert into rep_zvit_lgt_chnoe_tbl (id_paccnt,id_grp_lgt,id_grptar,id_tarif,is_town,summ_lgt)
 select ls.id_paccnt, ls.id_grp_lgt, 
 a.id_gtar, 
 CASE WHEN coalesce(t.id_grptar,0)<>coalesce(a.id_gtar,0) THEN tt.id_tar ELSE ls.id_tarif END , --coalesce(ls.id_tarif,tt.id_tar),
 adr.is_town, sum(ls.summ_lgt) as sum_lgt
 from 
 (
  select b.id_paccnt, ls.id_grp_lgt, ls.summ_lgt, ls.id_tarif
  from acm_bill_tbl as b 
  join acm_lgt_summ_tbl as ls on (b.id_doc= ls.id_doc) 
  join lgi_group_tbl as g on(g.id = ls.id_grp_lgt)
  where b.idk_doc in (200,220,209,291) and b.id_pref = 10
  and b.mmgg = vmmgg    
  and g.id_budjet = 3        
) as ls
  join clm_paccnt_tmp as a on (a.id = ls.id_paccnt)
  join adt_addr_tbl as adr on (adr.id = (a.addr).id_class) 
  join ( 
   select min(t.id) as id_tar, t.id_grptar
	from aqm_tarif_tbl as t 
        where ((dt_e is null ) or (dt_e > vmmgg )) and (dt_b <= vmmgg)
        and (t.per_min is null or (t.per_min <= vmmgg and t.per_max >= vmmgg))
        group by id_grptar
  ) as tt on (tt.id_grptar = a.id_gtar)

  left join aqm_tarif_tbl as t on (t.id = ls.id_tarif)
  group by ls.id_paccnt,ls.id_grp_lgt,a.id_gtar, CASE WHEN coalesce(t.id_grptar,0)<>coalesce(a.id_gtar,0) THEN tt.id_tar ELSE ls.id_tarif END ,adr.is_town;


 /*
 insert into rep_zvit_lgt_dod_tbl (id_paccnt,id_grp_lgt,id_grptar,id_tarif,is_town,summ_lgt)
 select ls.id_paccnt, ls.id_grp_lgt, a.id_gtar, tt.id_tar,adr.is_town, sum(ls.summ_lgt) as sum_lgt
 from 
 (
  select lg.id_paccnt, lg.id_grp_lgt, lg.sum_val as summ_lgt
  from acm_dop_lgt_tbl as lg
  join lgi_group_tbl as g on(g.id = lg.id_grp_lgt)
  where mmgg = vmmgg and g.id_budjet =1  
) as ls

  join clm_paccnt_tmp as a on (a.id = ls.id_paccnt)
  join adt_addr_tbl as adr on (adr.id = (a.addr).id_class) 

  join ( 
   select min(t.id) as id_tar, t.id_grptar
	from aqm_tarif_tbl as t 
        where ((dt_e is null ) or (dt_e > vmmgg )) and (dt_b <= vmmgg)
        group by id_grptar
  ) as tt on (tt.id_grptar = a.id_gtar)

--  left join aqm_tarif_tbl as t on (t.id = ls.id_tarif)

 where a.archive =0
 group by ls.id_paccnt,ls.id_grp_lgt,a.id_gtar,tt.id_tar,adr.is_town;
       */

 raise notice '2';


 insert into rep_zvit_subs_tbl (id_paccnt,id_grptar,id_tarif,is_town,summ_subs,summ_tax,summ_resubs,summ_retax)
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
       and p.mmgg = vmmgg 
       group by p.id_paccnt
 ) as pp on (pp.id_paccnt = a.id)
 join ( 
   select min(t.id) as id_tar, t.id_grptar
	from aqm_tarif_tbl as t 
        where ((dt_e is null ) or (dt_e > vmmgg )) and (dt_b <= vmmgg)
        and (t.per_min is null or (t.per_min <= vmmgg and t.per_max >= vmmgg))
        group by id_grptar
 ) as tt on (tt.id_grptar = a.id_gtar)
-- where a.archive =0
--  and (pid_paccnt is null or a.id = pid_paccnt)
;


 raise notice '2-1';


 insert into rep_zvit_subs_ret_tbl (id_paccnt,id_grptar,id_tarif,is_town,summ_subs,summ_tax)
select a.id, a.id_gtar,tt.id_tar, adr.is_town, coalesce(subs_pay,0),coalesce(subs_tax,0) 
 from 
 clm_paccnt_tmp as a
 join adt_addr_tbl as adr on (adr.id = (a.addr).id_class)
 join (
      select p.id_paccnt, 
      sum( value ) as subs_pay, 
      sum( value_tax ) as subs_tax 
       from acm_pay_tbl as p
       where p.id_pref = 10 and p.idk_doc = 194 
       and p.mmgg = vmmgg 
       group by p.id_paccnt
 ) as pp on (pp.id_paccnt = a.id)
 join ( 
   select min(t.id) as id_tar, t.id_grptar
	from aqm_tarif_tbl as t 
        where ((dt_e is null ) or (dt_e > vmmgg )) and (dt_b <= vmmgg)
        and (t.per_min is null or (t.per_min <= vmmgg and t.per_max >= vmmgg))
        group by id_grptar
 ) as tt on (tt.id_grptar = a.id_gtar);



 raise notice '3';
 update rep_zvit_subs_tbl set summ_subs = coalesce(summ_subs,0) ,summ_tax = coalesce(summ_tax,0),
                               summ_resubs = coalesce(summ_resubs,0),summ_retax = coalesce(summ_retax,0);

 ------------------------------------------------------------------------------------------------------------------------------------
 --Кiлькicть абонентiв 
 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment,'acc_count_f','  кiлькicть абонентiв(без дачникiв)','1_1','',
 count( distinct CASE WHEN adr.is_town = 1 and t.ident~'tgr7_3' THEN a.id END),
 --count( distinct CASE WHEN  ((t.ident~'tgr7_51') or (t.ident~'tgr7_53')) THEN a.id END),
 count( distinct CASE WHEN ((t.ident~'tgr7_51') or (t.ident~'tgr7_53')) THEN a.id END),
-- count( distinct CASE WHEN adr.is_town = 1 and (t.ident!~'tgr7_3') and (t.ident!~'tgr7_5') THEN a.id END),
 count( distinct CASE WHEN t.ident~'tgr7_1' or (adr.is_town = 1 and t.ident~'tgr7_6') THEN a.id END),
 count( distinct CASE WHEN adr.is_town = 0 and t.ident~'tgr7_3' THEN a.id END),
 --count( distinct CASE WHEN  t.ident~'tgr7_52' THEN a.id END),
count( distinct CASE WHEN t.ident~'tgr7_52' THEN a.id END), 
 -- count( distinct CASE WHEN adr.is_town = 0 and (t.ident!~'tgr7_3') and (t.ident!~'tgr7_5') THEN a.id END),
 count( distinct CASE WHEN t.ident~'tgr7_2' or (adr.is_town = 0 and t.ident~'tgr7_6')  THEN a.id END),
 1,1,vmmgg
 from 
 clm_paccnt_tmp as a
 join aqi_grptar_tbl as t on (t.id = a.id_gtar)
 join adt_addr_tbl as adr on (adr.id = (a.addr).id_class)
 where a.archive =0 and coalesce(a.idk_house,1) <>3 -- не дача 
-- and a.dt_b <= vmmgg
-- and (a.dt_e is null or a.dt_e >=vmmgg)
 ;


 raise notice '4';

 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment,'acc_count_d','  кiлькicть дачникiв','1_2','',
 count( distinct CASE WHEN adr.is_town = 1 and t.ident~'tgr7_3' THEN a.id END),
 count( distinct CASE WHEN ((t.ident~'tgr7_51') or (t.ident~'tgr7_53')) THEN a.id END),
-- count( distinct CASE WHEN adr.is_town = 1 and (t.ident!~'tgr7_3') and (t.ident!~'tgr7_5') THEN a.id END),
 count( distinct CASE WHEN t.ident~'tgr7_1'  or (adr.is_town = 1 and t.ident~'tgr7_6') THEN a.id END),
 count( distinct CASE WHEN adr.is_town = 0 and t.ident~'tgr7_3' THEN a.id END),
 count( distinct CASE WHEN t.ident~'tgr7_52' THEN a.id END),
-- count( distinct CASE WHEN adr.is_town = 0 and (t.ident!~'tgr7_3') and (t.ident!~'tgr7_5') THEN a.id END),
 count( distinct CASE WHEN t.ident~'tgr7_2'  or (adr.is_town = 0 and t.ident~'tgr7_6') THEN a.id END),
 1,1,vmmgg
 from 
 clm_paccnt_tmp as a
 join aqi_grptar_tbl as t on (t.id = a.id_gtar)
 join adt_addr_tbl as adr on (adr.id = (a.addr).id_class)
 where a.archive =0 and coalesce(a.idk_house,1) =3 -- дача 
-- and a.dt_b <= vmmgg
-- and (a.dt_e is null or a.dt_e >=vmmgg)
;
 

 raise notice '5';

 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment,'acc_count','Кiлькicть абонентiв, в т.ч.','1_0','', sum(town_stove),sum(town_heat), sum(town_other), 
                           sum(village_stove), sum(village_heat), sum(village_other), 2, 1, vmmgg
 from rep_zvit_tmp where mmgg = vmmgg and ident in ('acc_count_d','acc_count_f');
 


 /*
 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment, 'acc_count'||text(p2.num),'В т.ч. з споживанням '||p2.caption,'1_3'||text(p2.num),'',
 town_stove,town_heat, town_other, village_stove, village_heat, village_other,1,1,vmmgg
from 
rep_zvit_pattern_tbl as p2 left join 
(
 select ss2.id_grp,
 count( distinct CASE WHEN adr.is_town = 1 and t.ident~'tgr7_3' THEN a.id END) as town_stove,
 count( distinct CASE WHEN ((t.ident~'tgr7_51') or (t.ident~'tgr7_53')) THEN a.id END) as town_heat,
-- count( distinct CASE WHEN adr.is_town = 1 and (t.ident!~'tgr7_3') and (t.ident!~'tgr7_5') THEN a.id END) as town_other,
 count( distinct CASE WHEN t.ident~'tgr7_1'  or (adr.is_town = 1 and t.ident~'tgr7_6') THEN a.id END) as town_other,
 count( distinct CASE WHEN adr.is_town = 0 and t.ident~'tgr7_3' THEN a.id END) as village_stove,
 count( distinct CASE WHEN t.ident~'tgr7_52' THEN a.id END) as village_heat,
-- count( distinct CASE WHEN adr.is_town = 0 and (t.ident!~'tgr7_3') and (t.ident!~'tgr7_5') THEN a.id END) as village_other
 count( distinct CASE WHEN t.ident~'tgr7_2'  or (adr.is_town = 0 and t.ident~'tgr7_6') THEN a.id END) as village_other
 from 
(
select p.id as id_grp, s1.*, t2.lim_max ,t2.ident from
(
   select b.id_paccnt, t.id_grptar, t.per_min, max(t.lim_min) as lim_min 
        from acm_bill_tbl as b 
	join acm_summ_tbl as bs on (b.id_doc= bs.id_doc) 
	join aqm_tarif_tbl as t on (t.id = bs.id_tarif)
	where bs.demand <>0 and b.id_pref = 10
        and b.mmgg = vmmgg 
         and (pid_paccnt is null or b.id_paccnt = pid_paccnt)
group by  b.id_paccnt, t.id_grptar,t.per_min
) as s1 join aqm_tarif_tbl as t2 
on (s1.id_grptar = t2.id_grptar and coalesce(s1.per_min,now()::date)= coalesce(t2.per_min,now()::date) and coalesce(t2.lim_min,0) = coalesce(s1.lim_min,0))
join 
rep_zvit_pattern_tbl as p on (p.lim_min = coalesce(s1.lim_min,0) and p.lim_max = coalesce(t2.lim_max,0))
) as ss2
 join clm_paccnt_tmp as a on (a.id = ss2.id_paccnt)
 join aqi_grptar_tbl as t on (t.id = ss2.id_grptar)
 join adt_addr_tbl as adr on (adr.id = (a.addr).id_class)
where a.archive =0 
--and a.dt_b <= vmmgg and (a.dt_e is null or a.dt_e >=vmmgg)
group by ss2.id_grp
) as ss3 on (p2.id = ss3.id_grp)
order by p2.num;
 */

 raise notice '6';

 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment,'acc_count_arc','Кiлькicть абонентiв(в архiвi)','1_99','',
 count( distinct CASE WHEN adr.is_town = 1 and t.ident~'tgr7_3' THEN a.id END),
 count( distinct CASE WHEN ((t.ident~'tgr7_51') or (t.ident~'tgr7_53')) THEN a.id END),
-- count( distinct CASE WHEN adr.is_town = 1 and (t.ident!~'tgr7_3') and (t.ident!~'tgr7_5') THEN a.id END),
 count( distinct CASE WHEN t.ident~'tgr7_1'  or (adr.is_town = 1 and t.ident~'tgr7_6')  THEN a.id END),
 count( distinct CASE WHEN adr.is_town = 0 and t.ident~'tgr7_3' THEN a.id END),
 count( distinct CASE WHEN t.ident~'tgr7_52' THEN a.id END),
-- count( distinct CASE WHEN adr.is_town = 0 and (t.ident!~'tgr7_3') and (t.ident!~'tgr7_5') THEN a.id END),
 count( distinct CASE WHEN t.ident~'tgr7_2'  or (adr.is_town = 0 and t.ident~'tgr7_6') THEN a.id END),
 1,1,vmmgg
 from 
 clm_paccnt_tmp as a
 join aqi_grptar_tbl as t on (t.id = a.id_gtar)
 join adt_addr_tbl as adr on (adr.id = (a.addr).id_class)
 where a.archive =1 
-- and a.dt_b <= vmmgg
-- and (a.dt_e is null or a.dt_e >=vmmgg)
;

 raise notice '7';
---------------------------------------------------------------------------------------------
------ выписано счетов -----------
 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment,'bill_all_cnt',' Виписано рахункiв','5_01','шт',
 count( distinct CASE WHEN adr.is_town = 1 and t.ident~'tgr7_3' THEN bb.id_paccnt END),
 count( distinct CASE WHEN ((t.ident~'tgr7_51') or (t.ident~'tgr7_53')) THEN bb.id_paccnt END),
-- count( distinct CASE WHEN adr.is_town = 1 and (t.ident!~'tgr7_3') and (t.ident!~'tgr7_5') THEN bb.id_doc END),
-- count( distinct CASE WHEN t.ident~'tgr7_1' THEN bb.id_doc END),
 count( distinct CASE WHEN t.ident~'tgr7_1' or (adr.is_town = 1 and t.ident~'tgr7_6') THEN bb.id_paccnt END),
 count( distinct CASE WHEN adr.is_town = 0 and t.ident~'tgr7_3' THEN bb.id_paccnt END),
 count( distinct CASE WHEN  t.ident~'tgr7_52' THEN bb.id_paccnt END),
-- count( distinct CASE WHEN adr.is_town = 0 and (t.ident!~'tgr7_3') and (t.ident!~'tgr7_5') THEN bb.id_doc END)
 count( distinct CASE WHEN t.ident~'tgr7_2' or (adr.is_town = 0 and t.ident~'tgr7_6') THEN bb.id_paccnt END)
 ,2,1,vmmgg
 from 
 clm_paccnt_tmp as a
-- join (select id, max(dt_b) as dt from clm_paccnt_h  where dt_b <= vmmgg and coalesce(dt_e,vmmgg) >= vmmgg group by id order by id) as a2 on (a.id = a2.id and a2.dt = a.dt_b)
 --join adt_addr_tbl as adr on (adr.id = (a.addr).id_class)
 join adt_addr_town_street_tbl as adr on (adr.id = (a.addr).id_class)
 join ( 
   select b.id_doc , b.id_paccnt, t.id_grptar 
        from acm_bill_tbl as b 
	join acm_summ_tbl as bs on (b.id_doc= bs.id_doc) 
	join aqm_tarif_tbl as t on (t.id = bs.id_tarif)
	where b.idk_doc in (200,220,209,291)  and bs.demand <>0 
        and b.mmgg = vmmgg and b.id_pref = 10
 ) as bb on (bb.id_paccnt = a.id)
-- join aqi_grptar_tbl as t on (t.id = bb.id_grptar);
 join aqi_grptar_tbl as t on (t.id = a.id_gtar);
 --where a.archive =0 ;


-- кВтг
 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment,'bill_all_dem',' ','5_02','кВтг',
 sum(CASE WHEN adr.is_town = 1 and t.ident~'tgr7_3' THEN bb.demand END),
 sum(CASE WHEN ((t.ident~'tgr7_51') or (t.ident~'tgr7_53')) THEN bb.demand END),
-- sum(CASE WHEN adr.is_town = 1 and (t.ident!~'tgr7_3') and (t.ident!~'tgr7_5') THEN bb.demand END),
-- sum(CASE WHEN t.ident~'tgr7_1' THEN bb.demand END),
 sum(CASE WHEN t.ident~'tgr7_1' or (adr.is_town = 1 and t.ident~'tgr7_6') THEN bb.demand END),
 sum(CASE WHEN adr.is_town = 0 and t.ident~'tgr7_3' THEN bb.demand END),
 sum(CASE WHEN t.ident~'tgr7_52' THEN bb.demand END),
-- sum(CASE WHEN adr.is_town = 0 and (t.ident!~'tgr7_3') and (t.ident!~'tgr7_5') THEN bb.demand END)
-- sum(CASE WHEN t.ident~'tgr7_2' THEN bb.demand END)
 sum(CASE WHEN t.ident~'tgr7_2' or (adr.is_town = 0 and t.ident~'tgr7_6') THEN bb.demand END)
,2,1,vmmgg
 from 
 clm_paccnt_tmp as a
-- join (select id, max(dt_b) as dt from clm_paccnt_h  where 
--    ((dt_b < (vmmgg::date+'1 month'::interval) and dt_e is null)
--    or 
--    tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval(vmmgg::timestamp::abstime,(vmmgg::date+'1 month - 1 day'::interval)::timestamp::abstime)))
-- group by id order by id) as a2 on (a.id = a2.id and a2.dt = a.dt_b)

-- join (select id, max(dt_b) as dt from clm_paccnt_h  where dt_b <= vmmgg and coalesce(dt_e,vmmgg) >= vmmgg group by id order by id) as a2 on (a.id = a2.id and a2.dt = a.dt_b)
 join adt_addr_tbl as adr on (adr.id = (a.addr).id_class)
 join ( 
   select b.id_doc , b.id_paccnt, t.id_grptar , bs.demand
        from acm_bill_tbl as b 
	join acm_summ_tbl as bs on (b.id_doc= bs.id_doc) 
	join aqm_tarif_tbl as t on (t.id = bs.id_tarif)
	where b.idk_doc in (200,220,209,291) and bs.demand <>0 
        and b.mmgg = vmmgg and b.id_pref = 10
 ) as bb on (bb.id_paccnt = a.id)
-- join aqi_grptar_tbl as t on (t.id = bb.id_grptar);
 join aqi_grptar_tbl as t on (t.id = a.id_gtar);
 --where a.archive =0 ;
 


----------------------------сумма счета--------------------------------------------------------------
 -- сумма всего (по заголовкам счетов)


 raise notice 'bill_sum';
 raise notice '8';

 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment, 'bill_sum','              Всього','5_03','грн.',
 sum(CASE WHEN adr.is_town = 1 and t.ident~'tgr7_3' THEN value END),
 sum(CASE WHEN  ((t.ident~'tgr7_51') or (t.ident~'tgr7_53')) THEN value  END),
 sum(CASE WHEN t.ident~'tgr7_1' or (adr.is_town = 1 and t.ident~'tgr7_6')  THEN value  END),
 sum(CASE WHEN adr.is_town = 0 and t.ident~'tgr7_3' THEN value  END),
 sum(CASE WHEN  t.ident~'tgr7_52' THEN value  END),
 sum(CASE WHEN t.ident~'tgr7_2' or (adr.is_town = 0 and t.ident~'tgr7_6') THEN value  END)
,2,1,vmmgg
 from 

 clm_paccnt_tmp as a
 join adt_addr_tbl as adr on (adr.id = (a.addr).id_class)
 join ( 
--   select b.id_paccnt, coalesce(bs.id_grptar,ls.id_grptar) as id_grptar , sum(b.value) as value 
   select b.id_paccnt, sum(b.value) as value 
        from acm_bill_tbl as b 
	left join 
        (select bs.id_doc,max(t.id_grptar) as id_grptar
         from acm_summ_tbl as bs 
	 join aqm_tarif_tbl as t on (t.id = bs.id_tarif)
         where bs.mmgg = vmmgg      
         group by bs.id_doc
         order by bs.id_doc 
        ) as bs on (b.id_doc= bs.id_doc) 
--        left join (select id_paccnt, max(id_grptar) as id_grptar
--          from rep_zvit_lgt_bill_tbl 
--  	  group by id_paccnt
--        ) as ls on (ls.id_paccnt = b.id_paccnt)
	where b.idk_doc in (200,220,209,291) and b.id_pref = 10
        and b.mmgg = vmmgg      
--     group by b.id_paccnt, coalesce(bs.id_grptar,ls.id_grptar)
     group by b.id_paccnt
 ) as bb on (bb.id_paccnt = a.id)
-- join aqi_grptar_tbl as t on (t.id = bb.id_grptar);
 join aqi_grptar_tbl as t on (t.id = a.id_gtar);
 --where a.archive =0 ;



---------------------------------------------------------------------------------------------------------------------
 -- сумма счета = товарная сумма - льготы - субсидии
 -- сумма счета в целом есть в заголовке счета 
 -- так как в нужно бить по тарифным ступенькам, сумма в заголовке счета не устраивает
 -- 
/*
 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, gr_lvl, part_code, mmgg)
 values (vdepartment,'bill_sum_c', 'З 1 березня 2017 року','5_30','',1,1,vmmgg);
*/
 raise notice '11-01';

 -- сумма по шкале   -s-o-s-

 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
select vdepartment, 'bill_sum'||text(p2.ident)||text(p2.num),'В т.ч. випис. рах. '||p2.caption,'5_31'||text(p2.num),'грн.',
town_stove,town_heat, town_other, village_stove, village_heat, village_other
,1,1,vmmgg
from 
rep_zvit_pattern_tbl as p2 left join 
(
 select coalesce(ss2.id_grp, lz.id_grp) as id_grp,
 sum( CASE WHEN adr.is_town = 1 and t2.ident~'tgr7_3' THEN coalesce(sum_val,0)- coalesce(lz.sum_lgt,0)/*- coalesce(sz.sum_subs,0)*/ END) as town_stove,
 sum( CASE WHEN  ((t2.ident~'tgr7_51') or (t2.ident~'tgr7_53')) THEN coalesce(sum_val,0)- coalesce(lz.sum_lgt,0)/*- coalesce(sz.sum_subs,0)*/ END) as town_heat,
 sum( CASE WHEN (t2.ident~'tgr7_1') or (adr.is_town = 1 and t2.ident~'tgr7_6') THEN coalesce(sum_val,0)- coalesce(lz.sum_lgt,0)/*- coalesce(sz.sum_subs,0)*/ END) as town_other,
 sum( CASE WHEN adr.is_town = 0 and t2.ident~'tgr7_3' THEN coalesce(sum_val,0)- coalesce(lz.sum_lgt,0) /*-coalesce(sz.sum_subs,0) */END) as village_stove,
 sum( CASE WHEN  t2.ident~'tgr7_52' THEN coalesce(sum_val,0)- coalesce(lz.sum_lgt,0)/*- coalesce(sz.sum_subs,0)*/ END) as village_heat,
 sum( CASE WHEN (t2.ident~'tgr7_2') or (adr.is_town = 0 and t2.ident~'tgr7_6')  THEN coalesce(sum_val,0)- coalesce(lz.sum_lgt,0)/*- coalesce(sz.sum_subs,0)*/ END) as village_other
 from 
 clm_paccnt_tmp as a 
 join adt_addr_tbl as adr on (adr.id = (a.addr).id_class)
 left join (
--   select b.id_paccnt, t.id_grptar, p.id as id_grp, bs.sum_tovar- coalesce(ls.sum_lgt,0) as sum_val
   select b.id_paccnt, t.id_grptar, p.id as id_grp, sum(bs.sum_tovar) as sum_val
        from acm_bill_tbl as b 
        join clm_paccnt_tmp as a2 on (a2.id = b.id_paccnt)
	join (select id_doc, id_tarif, sum(summ) as sum_tovar from acm_summ_tbl where mmgg = vmmgg and dat_b >='2017-03-01'
          group by id_doc, id_tarif order by id_doc, id_tarif) as bs on (b.id_doc= bs.id_doc) 
	join aqm_tarif_tbl as t2 on (t2.id = bs.id_tarif)
        join aqm_tarif_tbl as t on (t.id = CASE WHEN coalesce(t2.id_grptar,0)<>coalesce(a2.id_gtar,0) and (coalesce(a2.id_gtar,0) not in (5,6,8,9,12,13))
                                                THEN a2.id_tarif_min ELSE bs.id_tarif END)
        join rep_zvit_pattern_tbl as p on (p.ident = '0' and p.lim_min = coalesce(t.lim_min,0) and p.lim_max = coalesce(t.lim_max,0))
--	left join (select id_doc, id_tarif, sum(summ_lgt) as sum_lgt from acm_lgt_summ_tbl where mmgg = vmmgg group by id_doc, id_tarif order by id_doc, id_tarif) as ls on (b.id_doc= ls.id_doc and bs.id_tarif = ls.id_tarif) 
	where b.idk_doc in (200,220,209,291) 
        and t2.ident !~'tgr7_6'
        and t2.ident not in ('tgr7_15','tgr7_25','tgr7_35','tgr7_53','tgr7_63')   
        and b.mmgg = vmmgg  and b.id_pref = 10
--         and (pid_paccnt is null or b.id_paccnt = pid_paccnt)
        group by b.id_paccnt,t.id_grptar, p.id       
        order by b.id_paccnt
) as ss2 on (a.id = ss2.id_paccnt)
 left join 
  (
  select z.id_paccnt, z.id_grptar,p.id as id_grp, sum(z.summ_lgt) as sum_lgt
  from rep_zvit_lgt_bill_tbl as z 
  join aqi_grptar_tbl as t on (t.id = z.id_grptar) 
  join aqm_tarif_tbl as tm on (tm.id = z.id_tarif)
  join rep_zvit_pattern_tbl as p on (p.ident = '0' and p.lim_min = coalesce(tm.lim_min,0) and p.lim_max = coalesce(tm.lim_max,0))
  where tm.ident !~'tgr7_6'
        and tm.ident not in ('tgr7_15','tgr7_25','tgr7_35','tgr7_53','tgr7_63')   
  group by z.id_paccnt, z.id_grptar,p.id order by z.id_paccnt, z.id_grptar,p.id
--) as lz on (lz.id_paccnt = a.id and lz.id_grptar = ss2.id_grptar and lz.id_grp = ss2.id_grp)
) as lz on (lz.id_paccnt = a.id and lz.id_grptar = coalesce(ss2.id_grptar,lz.id_grptar) and lz.id_grp = coalesce(ss2.id_grp,lz.id_grp)  )

 left join aqi_grptar_tbl as t on (t.id = coalesce(ss2.id_grptar,lz.id_grptar,0))
 left join aqi_grptar_tbl as t2 on (t2.id = a.id_gtar)

    /*
 left join 
  (
  select z.id_paccnt, z.id_grptar,p.id as id_grp, sum(z.summ_subs+z.summ_resubs) as sum_subs
  from rep_zvit_subs_tbl as z 
  join aqi_grptar_tbl as t on (t.id = z.id_grptar) 
  join aqm_tarif_tbl as tm on (tm.id = z.id_tarif)
  join rep_zvit_pattern_tbl as p on (p.lim_min = coalesce(tm.lim_min,0) and p.lim_max = coalesce(tm.lim_max,0))
  where tm.ident !~'tgr7_6'
        and tm.ident not in ('tgr7_15','tgr7_25','tgr7_35','tgr7_53','tgr7_63')   
  group by z.id_paccnt, z.id_grptar,p.id order by z.id_paccnt, z.id_grptar,p.id
) as sz on (sz.id_paccnt = a.id and sz.id_grptar = ss2.id_grptar and sz.id_grp = ss2.id_grp)
*/
--where a.archive =0 
where t.ident not in ('tgr7_15','tgr7_25','tgr7_35','tgr7_53','tgr7_63')   
 and not exists (select lh.id_paccnt from rep_zvit_lgt_chnoe_tbl as lh where lh.id_paccnt = a.id)
group by coalesce(ss2.id_grp, lz.id_grp)
) as ss3 on (p2.id = ss3.id_grp)
where p2.ident = '0'
order by p2.num;


 raise notice '11-02';
-- многодетным 
 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment, 'bill_sum_0m','В т.ч. випис. рах. багатодiтним','5_32','грн.',
 sum(CASE WHEN adr.is_town = 1 and t.ident~'tgr7_3' THEN coalesce(sum_val,0)- coalesce(lz.sum_lgt,0)/*- coalesce(sz.sum_subs,0)*/ END),
 sum(CASE WHEN  ((t.ident~'tgr7_51') or (t.ident~'tgr7_53')) THEN coalesce(sum_val,0)- coalesce(lz.sum_lgt,0)/*- coalesce(sz.sum_subs,0) */END),
 sum(CASE WHEN (t.ident~'tgr7_1') or (adr.is_town = 1 and t.ident~'tgr7_6')  THEN coalesce(sum_val,0)- coalesce(lz.sum_lgt,0)/*- coalesce(sz.sum_subs,0)*/ END),
 sum(CASE WHEN adr.is_town = 0 and t.ident~'tgr7_3' THEN coalesce(sum_val,0)- coalesce(lz.sum_lgt,0)/*- coalesce(sz.sum_subs,0)*/ END),
 sum(CASE WHEN  t.ident~'tgr7_52' THEN coalesce(sum_val,0)- coalesce(lz.sum_lgt,0)/*- coalesce(sz.sum_subs,0)*/ END),
 sum(CASE WHEN (t.ident~'tgr7_2') or (adr.is_town = 0 and t.ident~'tgr7_6')  THEN coalesce(sum_val,0)- coalesce(lz.sum_lgt,0)/*- coalesce(sz.sum_subs,0)*/ END)
,1,1,vmmgg
 from 
 clm_paccnt_tmp as a
 join adt_addr_tbl as adr on (adr.id = (a.addr).id_class)
 left join ( 
--   select b.id_doc , b.id_paccnt, t.id_grptar , bs.sum_tovar- coalesce(ls.sum_lgt,0) as sum_val
    select  b.id_paccnt, t.id_grptar , sum(bs.sum_tovar) as sum_val
--  select  b.id_paccnt, sum(bs.sum_tovar) as sum_val
        from acm_bill_tbl as b 
	join (select id_doc, id_tarif, sum(summ) as sum_tovar from acm_summ_tbl where mmgg = vmmgg and dat_b >='2017-03-01' 
          group by id_doc, id_tarif order by id_doc, id_tarif) as bs on (b.id_doc= bs.id_doc) 
	join aqm_tarif_tbl as t on (t.id = bs.id_tarif)
--        left join (select id_doc, id_tarif, sum(summ_lgt) as sum_lgt from acm_lgt_summ_tbl where mmgg = vmmgg group by id_doc, id_tarif order by id_doc, id_tarif) as ls on (b.id_doc= ls.id_doc and bs.id_tarif = ls.id_tarif) 
	where b.idk_doc in (200,220,209,291) 
        and b.mmgg = vmmgg and b.id_pref = 10        
        and t.ident in ('tgr7_15','tgr7_25','tgr7_35','tgr7_53','tgr7_63')   
        group by b.id_paccnt,t.id_grptar       
--        group by b.id_paccnt
        order by b.id_paccnt
 ) as bb on (bb.id_paccnt = a.id)

 left join 
  (
  select z.id_paccnt, z.id_grptar, sum(z.summ_lgt) as sum_lgt
--  select z.id_paccnt, sum(z.summ_lgt) as sum_lgt
  from rep_zvit_lgt_bill_tbl as z 
  join aqm_tarif_tbl as tm on (tm.id = z.id_tarif)
  where  tm.ident  in ('tgr7_15','tgr7_25','tgr7_35','tgr7_53','tgr7_63')   
  group by z.id_paccnt, z.id_grptar order by z.id_paccnt, z.id_grptar
--  group by z.id_paccnt order by z.id_paccnt
) as lz on (lz.id_paccnt = bb.id_paccnt and lz.id_grptar = bb.id_grptar )
--) as lz on (lz.id_paccnt = a.id )

 left join aqi_grptar_tbl as t on (t.id = coalesce(bb.id_grptar,lz.id_grptar)) 
-- left join aqi_grptar_tbl as t on (t.id = a.id_gtar) 
 where t.ident in ('tgr7_15','tgr7_25','tgr7_35','tgr7_53','tgr7_63');   
/*
 left join 
  (
  select z.id_paccnt, z.id_grptar, sum(z.summ_subs+z.summ_resubs) as sum_subs
  from rep_zvit_subs_tbl as z 
  join aqm_tarif_tbl as tm on (tm.id = z.id_tarif)
  where  tm.ident  in ('tgr7_15','tgr7_25','tgr7_35','tgr7_53','tgr7_63')   
  group by z.id_paccnt, z.id_grptar order by z.id_paccnt, z.id_grptar
) as sz on (sz.id_paccnt = bb.id_paccnt and sz.id_grptar = bb.id_grptar )
*/
--where a.archive =0; 

 raise notice '11-03';
-- не газифицированные    -s-o-s-

 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
select vdepartment,'bill_sum_ng'||text(p2.ident)||text(p2.num),'В т.ч. не газиф '||p2.caption,'5_33'||text(p2.num),'грн.',
0,0,town_heat, 0, 0, village_heat, 1,1,vmmgg

from
rep_zvit_pattern_tbl as p2 left join 
(
 select coalesce(ss2.id_grp, lz.id_grp) as id_grp,
 sum( CASE WHEN adr.is_town = 1 THEN coalesce(sum_val,0)- coalesce(lz.sum_lgt,0) /*-coalesce(sz.sum_subs,0)*/ END) as town_heat,
 sum( CASE WHEN adr.is_town = 0 THEN coalesce(sum_val,0)- coalesce(lz.sum_lgt,0) /*-coalesce(sz.sum_subs,0)*/ END) as village_heat
 from 
 clm_paccnt_tmp as a 
 join adt_addr_tbl as adr on (adr.id = (a.addr).id_class)
 left join (
--   select b.id_paccnt, t.id_grptar, p.id as id_grp, bs.sum_tovar- coalesce(ls.sum_lgt,0) as sum_val
   select b.id_paccnt, t.id_grptar, p.id as id_grp, sum(bs.sum_tovar) as sum_val
        from acm_bill_tbl as b 
        join clm_paccnt_tmp as a2 on (a2.id = b.id_paccnt)
	join (select id_doc, id_tarif, sum(summ) as sum_tovar from acm_summ_tbl where mmgg = vmmgg and dat_b >='2017-03-01'
           group by id_doc, id_tarif order by id_doc, id_tarif) as bs on (b.id_doc= bs.id_doc) 
	join aqm_tarif_tbl as t2 on (t2.id = bs.id_tarif)
        join aqm_tarif_tbl as t on (t.id = CASE WHEN coalesce(t2.id_grptar,0)<>coalesce(a2.id_gtar,0) THEN a2.id_tarif_min ELSE bs.id_tarif END)
        join rep_zvit_pattern_tbl as p on (p.ident = '0' and p.lim_min = coalesce(t.lim_min,0) and p.lim_max = coalesce(t.lim_max,0))
--	left join (select id_doc, id_tarif, sum(summ_lgt) as sum_lgt from acm_lgt_summ_tbl where mmgg = vmmgg group by id_doc, id_tarif order by id_doc, id_tarif) as ls on (b.id_doc= ls.id_doc and bs.id_tarif = ls.id_tarif) 
	where b.idk_doc in (200,220,209,291) 
        and t2.ident ~'tgr7_6' and t2.ident <> 'tgr7_63'
        and b.mmgg = vmmgg  and b.id_pref = 10
--         and (pid_paccnt is null or b.id_paccnt = pid_paccnt)
        group by b.id_paccnt,t.id_grptar, p.id       
        order by b.id_paccnt
) as ss2 on (a.id = ss2.id_paccnt)
 left join 
  (
  select z.id_paccnt, z.id_grptar,p.id as id_grp, sum(z.summ_lgt) as sum_lgt
  from rep_zvit_lgt_bill_tbl as z 
  join aqi_grptar_tbl as t on (t.id = z.id_grptar) 
  join aqm_tarif_tbl as tm on (tm.id = z.id_tarif)
  join rep_zvit_pattern_tbl as p on (p.ident = '0' and p.lim_min = coalesce(tm.lim_min,0) and p.lim_max = coalesce(tm.lim_max,0))
  where tm.ident ~'tgr7_6' and tm.ident <> 'tgr7_63'
  group by z.id_paccnt, z.id_grptar,p.id order by z.id_paccnt, z.id_grptar,p.id
--) as lz on (lz.id_paccnt = a.id and lz.id_grptar = ss2.id_grptar and lz.id_grp = ss2.id_grp)
) as lz on (lz.id_paccnt = a.id and lz.id_grptar = coalesce(ss2.id_grptar,lz.id_grptar) and lz.id_grp = coalesce(ss2.id_grp,lz.id_grp)  )

 left join aqi_grptar_tbl as t on (t.id = coalesce(ss2.id_grptar,lz.id_grptar,0))
--where a.archive =0 
group by coalesce(ss2.id_grp, lz.id_grp)
) as ss3 on (p2.id = ss3.id_grp)
where p2.ident = '0'
order by p2.num;

------------------------------------------------------------------
/*
 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, gr_lvl, part_code, mmgg)
 values (vdepartment,'bill_sum_c1', 'до 1 березня 2017 року','5_40','',1,1,vmmgg);
*/
 raise notice '11-11';

 -- сумма по шкале   -s-o-s-

 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
select vdepartment, 'bill_sum'||text(p2.ident)||text(p2.num),'В т.ч. випис. рах. '||p2.caption,'5_41'||text(p2.num),'грн.',
town_stove,town_heat, town_other, village_stove, village_heat, village_other
,1,1,vmmgg
from 
rep_zvit_pattern_tbl as p2 left join 
(
 select coalesce(ss2.id_grp, lz.id_grp) as id_grp,
 sum( CASE WHEN adr.is_town = 1 and t.ident~'tgr7_3' THEN coalesce(sum_val,0)- coalesce(lz.sum_lgt,0)/*- coalesce(sz.sum_subs,0)*/ END) as town_stove,
 sum( CASE WHEN  ((t.ident~'tgr7_51') or (t.ident~'tgr7_53')) THEN coalesce(sum_val,0)- coalesce(lz.sum_lgt,0)/*- coalesce(sz.sum_subs,0)*/ END) as town_heat,
 sum( CASE WHEN (t.ident~'tgr7_1') or (adr.is_town = 1 and t.ident~'tgr7_6') THEN coalesce(sum_val,0)- coalesce(lz.sum_lgt,0)/*- coalesce(sz.sum_subs,0)*/ END) as town_other,
 sum( CASE WHEN adr.is_town = 0 and t.ident~'tgr7_3' THEN coalesce(sum_val,0)- coalesce(lz.sum_lgt,0) /*-coalesce(sz.sum_subs,0) */END) as village_stove,
 sum( CASE WHEN  t.ident~'tgr7_52' THEN coalesce(sum_val,0)- coalesce(lz.sum_lgt,0)/*- coalesce(sz.sum_subs,0)*/ END) as village_heat,
 sum( CASE WHEN (t.ident~'tgr7_2') or (adr.is_town = 0 and t.ident~'tgr7_6')  THEN coalesce(sum_val,0)- coalesce(lz.sum_lgt,0)/*- coalesce(sz.sum_subs,0)*/ END) as village_other
 from 
 clm_paccnt_tmp as a 
 join adt_addr_tbl as adr on (adr.id = (a.addr).id_class)
 left join (
--   select b.id_paccnt, t.id_grptar, p.id as id_grp, bs.sum_tovar- coalesce(ls.sum_lgt,0) as sum_val
   select b.id_paccnt, t.id_grptar, p.id as id_grp, sum(bs.sum_tovar) as sum_val
        from acm_bill_tbl as b 
        join clm_paccnt_tmp as a2 on (a2.id = b.id_paccnt)
	join ( 
	   select min(t.id) as id_tar_old, t.id_grptar
		from aqm_tarif_tbl as t 
	        where ((dt_e is null ) or (dt_e > '2017-02-01' )) and (dt_b <= '2017-02-01')
	        and (t.per_min is null or (t.per_min <= '2017-02-01' and t.per_max >= '2017-02-01'))
	        group by id_grptar
	 ) as told on (told.id_grptar = a2.id_gtar)

	join (select id_doc, id_tarif, sum(summ) as sum_tovar from acm_summ_tbl where mmgg = vmmgg and dat_b <'2017-03-01'
          group by id_doc, id_tarif order by id_doc, id_tarif) as bs on (b.id_doc= bs.id_doc) 
	join aqm_tarif_tbl as t2 on (t2.id = bs.id_tarif)
        join aqm_tarif_tbl as t on (t.id = CASE WHEN coalesce(t2.id_grptar,0)<>coalesce(a2.id_gtar,0) and (coalesce(a2.id_gtar,0) not in (5,6,8,9,12,13))
                                                THEN told.id_tar_old --a2.id_tarif_min 
                                                ELSE bs.id_tarif END)
        join rep_zvit_pattern_tbl as p on (p.ident = '1' and p.lim_min = coalesce(t.lim_min,0) and p.lim_max = coalesce(t.lim_max,0))
--	left join (select id_doc, id_tarif, sum(summ_lgt) as sum_lgt from acm_lgt_summ_tbl where mmgg = vmmgg group by id_doc, id_tarif order by id_doc, id_tarif) as ls on (b.id_doc= ls.id_doc and bs.id_tarif = ls.id_tarif) 
	where b.idk_doc in (200,220,209,291) 
        and t.ident !~'tgr7_6'
        and t2.ident not in ('tgr7_15','tgr7_25','tgr7_35','tgr7_53','tgr7_63')   
        and b.mmgg = vmmgg  and b.id_pref = 10
--         and (pid_paccnt is null or b.id_paccnt = pid_paccnt)
        group by b.id_paccnt,t.id_grptar, p.id       
        order by b.id_paccnt
) as ss2 on (a.id = ss2.id_paccnt)
 left join 
  (
  select z.id_paccnt, z.id_grptar,p.id as id_grp, sum(z.summ_lgt) as sum_lgt
  from rep_zvit_lgt_billoldt_tbl as z 
  join aqi_grptar_tbl as t on (t.id = z.id_grptar) 
  join aqm_tarif_tbl as tm on (tm.id = z.id_tarif)
  join rep_zvit_pattern_tbl as p on (p.ident = '1' and p.lim_min = coalesce(tm.lim_min,0) and p.lim_max = coalesce(tm.lim_max,0))
  where tm.ident !~'tgr7_6'
        and tm.ident not in ('tgr7_15','tgr7_25','tgr7_35','tgr7_53','tgr7_63')   
  group by z.id_paccnt, z.id_grptar,p.id order by z.id_paccnt, z.id_grptar,p.id
--) as lz on (lz.id_paccnt = a.id and lz.id_grptar = ss2.id_grptar and lz.id_grp = ss2.id_grp)
) as lz on (lz.id_paccnt = a.id and lz.id_grptar = coalesce(ss2.id_grptar,lz.id_grptar) and lz.id_grp = coalesce(ss2.id_grp,lz.id_grp)  )

 left join aqi_grptar_tbl as t on (t.id = coalesce(ss2.id_grptar,lz.id_grptar,0))
 where t.ident not in ('tgr7_15','tgr7_25','tgr7_35','tgr7_53','tgr7_63')   
 and not exists (select lh.id_paccnt from rep_zvit_lgt_chnoe_tbl as lh where lh.id_paccnt = a.id)
group by coalesce(ss2.id_grp, lz.id_grp)
) as ss3 on (p2.id = ss3.id_grp)
where p2.ident = '1'
order by p2.num;


 raise notice '11-12';
-- многодетным 
 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment, 'bill_sum_1m','В т.ч. випис. рах. багатодiтним','5_42','грн.',
 sum(CASE WHEN adr.is_town = 1 and t.ident~'tgr7_3' THEN coalesce(sum_val,0)- coalesce(lz.sum_lgt,0)/*- coalesce(sz.sum_subs,0)*/ END),
 sum(CASE WHEN  ((t.ident~'tgr7_51') or (t.ident~'tgr7_53')) THEN coalesce(sum_val,0)- coalesce(lz.sum_lgt,0)/*- coalesce(sz.sum_subs,0) */END),
 sum(CASE WHEN (t.ident~'tgr7_1') or (adr.is_town = 1 and t.ident~'tgr7_6')  THEN coalesce(sum_val,0)- coalesce(lz.sum_lgt,0)/*- coalesce(sz.sum_subs,0)*/ END),
 sum(CASE WHEN adr.is_town = 0 and t.ident~'tgr7_3' THEN coalesce(sum_val,0)- coalesce(lz.sum_lgt,0)/*- coalesce(sz.sum_subs,0)*/ END),
 sum(CASE WHEN  t.ident~'tgr7_52' THEN coalesce(sum_val,0)- coalesce(lz.sum_lgt,0)/*- coalesce(sz.sum_subs,0)*/ END),
 sum(CASE WHEN (t.ident~'tgr7_2') or (adr.is_town = 0 and t.ident~'tgr7_6')  THEN coalesce(sum_val,0)- coalesce(lz.sum_lgt,0)/*- coalesce(sz.sum_subs,0)*/ END)
,1,1,vmmgg
 from 
 clm_paccnt_tmp as a
 join adt_addr_tbl as adr on (adr.id = (a.addr).id_class)
 left join ( 
--   select b.id_doc , b.id_paccnt, t.id_grptar , bs.sum_tovar- coalesce(ls.sum_lgt,0) as sum_val
    select  b.id_paccnt, t.id_grptar , sum(bs.sum_tovar) as sum_val
--  select  b.id_paccnt, sum(bs.sum_tovar) as sum_val
        from acm_bill_tbl as b 
	join (select id_doc, id_tarif, sum(summ) as sum_tovar from acm_summ_tbl where mmgg = vmmgg and dat_b <'2017-03-01' 
          group by id_doc, id_tarif order by id_doc, id_tarif) as bs on (b.id_doc= bs.id_doc) 
	join aqm_tarif_tbl as t on (t.id = bs.id_tarif)
--        left join (select id_doc, id_tarif, sum(summ_lgt) as sum_lgt from acm_lgt_summ_tbl where mmgg = vmmgg group by id_doc, id_tarif order by id_doc, id_tarif) as ls on (b.id_doc= ls.id_doc and bs.id_tarif = ls.id_tarif) 
	where b.idk_doc in (200,220,209,291) 
        and b.mmgg = vmmgg and b.id_pref = 10        
        and t.ident in ('tgr7_15','tgr7_25','tgr7_35','tgr7_53','tgr7_63')   
        group by b.id_paccnt,t.id_grptar       
--        group by b.id_paccnt
        order by b.id_paccnt
 ) as bb on (bb.id_paccnt = a.id)

 left join 
  (
  select z.id_paccnt, z.id_grptar, sum(z.summ_lgt) as sum_lgt
--  select z.id_paccnt, sum(z.summ_lgt) as sum_lgt
  from rep_zvit_lgt_billoldt_tbl as z 
  join aqm_tarif_tbl as tm on (tm.id = z.id_tarif)
  where  tm.ident  in ('tgr7_15','tgr7_25','tgr7_35','tgr7_53','tgr7_63')   
  group by z.id_paccnt, z.id_grptar order by z.id_paccnt, z.id_grptar
--  group by z.id_paccnt order by z.id_paccnt
) as lz on (lz.id_paccnt = bb.id_paccnt and lz.id_grptar = bb.id_grptar )
--) as lz on (lz.id_paccnt = a.id )

 left join aqi_grptar_tbl as t on (t.id = coalesce(bb.id_grptar,lz.id_grptar)) 
-- left join aqi_grptar_tbl as t on (t.id = a.id_gtar) 
 where t.ident in ('tgr7_15','tgr7_25','tgr7_35','tgr7_53','tgr7_63');   
/*
 left join 
  (
  select z.id_paccnt, z.id_grptar, sum(z.summ_subs+z.summ_resubs) as sum_subs
  from rep_zvit_subs_tbl as z 
  join aqm_tarif_tbl as tm on (tm.id = z.id_tarif)
  where  tm.ident  in ('tgr7_15','tgr7_25','tgr7_35','tgr7_53','tgr7_63')   
  group by z.id_paccnt, z.id_grptar order by z.id_paccnt, z.id_grptar
) as sz on (sz.id_paccnt = bb.id_paccnt and sz.id_grptar = bb.id_grptar )
*/
--where a.archive =0; 

 raise notice '11-2-1';
-- не газифицированные    -s-o-s-

 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
select vdepartment,'bill_sum_ng'||text(p2.ident)||text(p2.num),'В т.ч. не газиф '||p2.caption,'5_43'||text(p2.num),'грн.',
0,0,town_heat, 0, 0, village_heat, 1,1,vmmgg

from
rep_zvit_pattern_tbl as p2 left join 
(
 select coalesce(ss2.id_grp, lz.id_grp) as id_grp,
 sum( CASE WHEN adr.is_town = 1 THEN coalesce(sum_val,0)- coalesce(lz.sum_lgt,0) /*-coalesce(sz.sum_subs,0)*/ END) as town_heat,
 sum( CASE WHEN adr.is_town = 0 THEN coalesce(sum_val,0)- coalesce(lz.sum_lgt,0) /*-coalesce(sz.sum_subs,0)*/ END) as village_heat
 from 
 clm_paccnt_tmp as a 
 join adt_addr_tbl as adr on (adr.id = (a.addr).id_class)
 left join (
--   select b.id_paccnt, t.id_grptar, p.id as id_grp, bs.sum_tovar- coalesce(ls.sum_lgt,0) as sum_val
   select b.id_paccnt, t.id_grptar, p.id as id_grp, sum(bs.sum_tovar) as sum_val
        from acm_bill_tbl as b 
        join clm_paccnt_tmp as a2 on (a2.id = b.id_paccnt)
	join ( 
	   select min(t.id) as id_tar_old, t.id_grptar
		from aqm_tarif_tbl as t 
	        where ((dt_e is null ) or (dt_e > '2017-02-01' )) and (dt_b <= '2017-02-01')
	        and (t.per_min is null or (t.per_min <= '2017-02-01' and t.per_max >= '2017-02-01'))
	        group by id_grptar
	 ) as told on (told.id_grptar = a2.id_gtar)

	join (select id_doc, id_tarif, sum(summ) as sum_tovar from acm_summ_tbl where mmgg = vmmgg and dat_b <'2017-03-01'
           group by id_doc, id_tarif order by id_doc, id_tarif) as bs on (b.id_doc= bs.id_doc) 
	join aqm_tarif_tbl as t2 on (t2.id = bs.id_tarif)
        join aqm_tarif_tbl as t on (t.id = CASE WHEN coalesce(t2.id_grptar,0)<>coalesce(a2.id_gtar,0) and (coalesce(a2.id_gtar,0) not in (5,6,8,9,12,13))
					   THEN told.id_tar_old --a2.id_tarif_min 
                                           ELSE bs.id_tarif END)
        join rep_zvit_pattern_tbl as p on (p.ident = '1' and p.lim_min = coalesce(t.lim_min,0) and p.lim_max = coalesce(t.lim_max,0))
--	left join (select id_doc, id_tarif, sum(summ_lgt) as sum_lgt from acm_lgt_summ_tbl where mmgg = vmmgg group by id_doc, id_tarif order by id_doc, id_tarif) as ls on (b.id_doc= ls.id_doc and bs.id_tarif = ls.id_tarif) 
	where b.idk_doc in (200,220,209,291) 
        and t.ident ~'tgr7_6' and t.ident <> 'tgr7_63'
        and b.mmgg = vmmgg  and b.id_pref = 10
--         and (pid_paccnt is null or b.id_paccnt = pid_paccnt)
        group by b.id_paccnt,t.id_grptar, p.id       
        order by b.id_paccnt
) as ss2 on (a.id = ss2.id_paccnt)
 left join 
  (
  select z.id_paccnt, z.id_grptar,p.id as id_grp, sum(z.summ_lgt) as sum_lgt
  from rep_zvit_lgt_billoldt_tbl as z 
  join aqi_grptar_tbl as t on (t.id = z.id_grptar) 
  join aqm_tarif_tbl as tm on (tm.id = z.id_tarif)
  join rep_zvit_pattern_tbl as p on (p.ident = '1' and p.lim_min = coalesce(tm.lim_min,0) and p.lim_max = coalesce(tm.lim_max,0))
  where tm.ident ~'tgr7_6' and tm.ident <> 'tgr7_63'
  group by z.id_paccnt, z.id_grptar,p.id order by z.id_paccnt, z.id_grptar,p.id
--) as lz on (lz.id_paccnt = a.id and lz.id_grptar = ss2.id_grptar and lz.id_grp = ss2.id_grp)
) as lz on (lz.id_paccnt = a.id and lz.id_grptar = coalesce(ss2.id_grptar,lz.id_grptar) and lz.id_grp = coalesce(ss2.id_grp,lz.id_grp)  )

 left join aqi_grptar_tbl as t on (t.id = coalesce(ss2.id_grptar,lz.id_grptar,0))
--where a.archive =0 
group by coalesce(ss2.id_grp, lz.id_grp)
) as ss3 on (p2.id = ss3.id_grp)
where p2.ident = '1'
order by p2.num;




 raise notice '11-2';
-- подстанции 
 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment, 'bill_sum_ps1','В т.ч. перс.пiдст. до 3000 кВтг','5_61','грн.',0,
 sum( CASE WHEN adr.is_town = 1 THEN coalesce(sum_val,0)- coalesce(lz.sum_lgt,0) - coalesce(lz2.sum_lgt,0) END) as town_heat,0,0,
 sum( CASE WHEN adr.is_town = 0 THEN coalesce(sum_val,0)- coalesce(lz.sum_lgt,0) - coalesce(lz2.sum_lgt,0) END) as village_heat,0
,1,1,vmmgg
 from 
 clm_paccnt_tmp as a
 join adt_addr_tbl as adr on (adr.id = (a.addr).id_class)
 left join ( 
   select  b.id_paccnt, sum(bs.sum_tovar) as sum_val
        from acm_bill_tbl as b 
	join (select id_doc, id_tarif, sum(summ) as sum_tovar from acm_summ_tbl where mmgg = vmmgg group by id_doc, id_tarif order by id_doc, id_tarif) as bs on (b.id_doc= bs.id_doc) 
	join aqm_tarif_tbl as t on (t.id = bs.id_tarif)
	where b.idk_doc in (200,220,209,291) 
        and b.mmgg = vmmgg and b.id_pref = 10        
--        and coalesce(t.lim_min,0)=0
        and coalesce(t.lim_min,0)<3000
        group by b.id_paccnt
        order by b.id_paccnt
 ) as bb on (bb.id_paccnt = a.id)
 left join 
  (
  select z.id_paccnt, sum(z.summ_lgt) as sum_lgt
  from rep_zvit_lgt_bill_tbl as z 
  join aqm_tarif_tbl as tm on (tm.id = z.id_tarif)
  where coalesce(tm.lim_min,0)=0
  group by z.id_paccnt order by z.id_paccnt
) as lz on (lz.id_paccnt = a.id )
 left join 
  (
  select z.id_paccnt, sum(z.summ_lgt) as sum_lgt
  from rep_zvit_lgt_billoldt_tbl as z 
  join aqm_tarif_tbl as tm on (tm.id = z.id_tarif)
  where coalesce(tm.lim_min,0)=0
  group by z.id_paccnt order by z.id_paccnt
) as lz2 on (lz2.id_paccnt = a.id )
 where exists (select lh.id_paccnt from rep_zvit_lgt_chnoe_tbl as lh where lh.id_paccnt = a.id);


 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment, 'bill_sum_ps2','В т.ч. перс.пiдст. понад  3000 кВтг','5_62','грн.',0,
 sum( CASE WHEN adr.is_town = 1 THEN coalesce(sum_val,0)- coalesce(lz.sum_lgt,0) - coalesce(lz2.sum_lgt,0) END) as town_heat,0,0,
 sum( CASE WHEN adr.is_town = 0 THEN coalesce(sum_val,0)- coalesce(lz.sum_lgt,0) - coalesce(lz2.sum_lgt,0) END) as village_heat,0
,1,1,vmmgg
 from 
 clm_paccnt_tmp as a
 join adt_addr_tbl as adr on (adr.id = (a.addr).id_class)
 left join ( 
   select  b.id_paccnt, sum(bs.sum_tovar) as sum_val
        from acm_bill_tbl as b 
	join (select id_doc, id_tarif, sum(summ) as sum_tovar from acm_summ_tbl where mmgg = vmmgg group by id_doc, id_tarif order by id_doc, id_tarif) as bs on (b.id_doc= bs.id_doc) 
	join aqm_tarif_tbl as t on (t.id = bs.id_tarif)
	where b.idk_doc in (200,220,209,291) 
        and b.mmgg = vmmgg and b.id_pref = 10        
        and coalesce(t.lim_min,0)>=3000
        group by b.id_paccnt
        order by b.id_paccnt
 ) as bb on (bb.id_paccnt = a.id)
 left join 
  (
  select z.id_paccnt, sum(z.summ_lgt) as sum_lgt
  from rep_zvit_lgt_bill_tbl as z 
  join aqm_tarif_tbl as tm on (tm.id = z.id_tarif)
  where coalesce(tm.lim_min,0)>=3000
  group by z.id_paccnt order by z.id_paccnt
) as lz on (lz.id_paccnt = a.id )
 left join 
  (
  select z.id_paccnt, sum(z.summ_lgt) as sum_lgt
  from rep_zvit_lgt_billoldt_tbl as z 
  join aqm_tarif_tbl as tm on (tm.id = z.id_tarif)
  where coalesce(tm.lim_min,0)>=3000
  group by z.id_paccnt order by z.id_paccnt
) as lz2 on (lz2.id_paccnt = a.id )

 where exists (select lh.id_paccnt from rep_zvit_lgt_chnoe_tbl as lh where lh.id_paccnt = a.id);



-------------------------------------------------------------
 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment, 'bill_corsum',' -- рахунки - корегування ','5_9','грн.',
 sum(CASE WHEN adr.is_town = 1 and t.ident~'tgr7_3' THEN value /*- coalesce(sz.sum_subs,0) */END),
 sum(CASE WHEN  ((t.ident~'tgr7_51') or (t.ident~'tgr7_53')) THEN value /*- coalesce(sz.sum_subs,0)*/ END),
 sum(CASE WHEN t.ident~'tgr7_1' or (adr.is_town = 1 and t.ident~'tgr7_6')  THEN value /*- coalesce(sz.sum_subs,0)*/ END),
 sum(CASE WHEN adr.is_town = 0 and t.ident~'tgr7_3' THEN value /*- coalesce(sz.sum_subs,0)*/ END),
 sum(CASE WHEN  t.ident~'tgr7_52' THEN value /*- coalesce(sz.sum_subs,0) */ END),
 sum(CASE WHEN t.ident~'tgr7_2' or (adr.is_town = 0 and t.ident~'tgr7_6') THEN value /*- coalesce(sz.sum_subs,0)*/ END)
,1,1,vmmgg
 from 

 clm_paccnt_tmp as a
 join aqi_grptar_tbl as t on (t.id = a.id_gtar)
 join adt_addr_tbl as adr on (adr.id = (a.addr).id_class)
 join (select b.id_paccnt, sum(b.value) as value 
        from acm_bill_tbl as b 
        left join acm_summ_tbl as bs on (b.id_doc= bs.id_doc) 
        where b.mmgg = vmmgg::date and b.id_pref = 10  and b.idk_doc = 220 and bs.id_doc is null  --
       group by b.id_paccnt order by b.id_paccnt ) as p   on (p.id_paccnt = a.id);


------------------------------------------------------------------------------------------------------
/*

 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment,'bill_sum','              Всього','5_03','грн.', sum(town_stove),sum(town_heat), sum(town_other), 
                           sum(village_stove), sum(village_heat), sum(village_other), 2, 1, vmmgg
 from rep_zvit_tmp where mmgg = vmmgg and ident ~ 'bill_sum';
*/
-------------------------------------------------------------------------------------------
------------товарная сумма (полная сумма за электроенергию без вычета льгот и субсидий  )-------------
 --сумма всего
 raise notice 'tovar_sum';
 raise notice '12';


 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment, 'tovar_sum','Сума товарна Всього','4_0','грн.',
 sum(CASE WHEN adr.is_town = 1 and t.ident~'tgr7_3' THEN sum_val END),
 sum(CASE WHEN  ((t.ident~'tgr7_51') or (t.ident~'tgr7_53')) THEN sum_val END),
-- sum(CASE WHEN (t.ident~'tgr7_1')  THEN sum_val END),
 sum(CASE WHEN t.ident~'tgr7_1' or (adr.is_town = 1 and t.ident~'tgr7_6')  THEN sum_val END),
 sum(CASE WHEN adr.is_town = 0 and t.ident~'tgr7_3' THEN sum_val END),
 sum(CASE WHEN  t.ident~'tgr7_52' THEN sum_val END),
-- sum(CASE WHEN (t.ident~'tgr7_2')  THEN sum_val END)
 sum(CASE WHEN t.ident~'tgr7_2' or (adr.is_town = 0 and t.ident~'tgr7_6')  THEN sum_val END)
,2,1,vmmgg
 from 
 clm_paccnt_tmp as a
 join adt_addr_tbl as adr on (adr.id = (a.addr).id_class)
 join ( 
   select b.id_doc , b.id_paccnt, t.id_grptar , bs.summ as sum_val
        from acm_bill_tbl as b 
	join acm_summ_tbl as bs on (b.id_doc= bs.id_doc) 
	join aqm_tarif_tbl as t on (t.id = bs.id_tarif)
	where b.idk_doc in (200,220,209,291) and b.id_pref = 10
        and b.mmgg = vmmgg         
 ) as bb on (bb.id_paccnt = a.id)
-- join aqi_grptar_tbl as t on (t.id = bb.id_grptar);
 join aqi_grptar_tbl as t on (t.id = a.id_gtar);
 --where a.archive =0;

/*
 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, gr_lvl, part_code, mmgg)
 values (vdepartment,'tovar_sum_c', 'З 1 березня 2017 року','4_30','',1,1,vmmgg);
*/

 --сумма по шкале  -s-o-s-
 raise notice '12-1';

 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
select vdepartment, 'tovar_sum'||text(p2.ident)||text(p2.num),'В т.ч. товар.сума '||p2.caption,'4_31'||text(p2.num),'грн.',
town_stove,town_heat, town_other, village_stove, village_heat, village_other,1,1,vmmgg
from 
rep_zvit_pattern_tbl as p2 left join 
(
 select p.id as id_grp, -- ss2.id_grp,
 sum( CASE WHEN adr.is_town = 1 and t.ident~'tgr7_3' THEN sum_val END) as town_stove,
 sum( CASE WHEN  ((t.ident~'tgr7_51') or (t.ident~'tgr7_53')) THEN sum_val END) as town_heat,
 sum( CASE WHEN (t.ident~'tgr7_1') or (adr.is_town = 1 and t.ident~'tgr7_6') THEN sum_val END) as town_other,
 sum( CASE WHEN adr.is_town = 0 and t.ident~'tgr7_3' THEN sum_val END) as village_stove,
 sum( CASE WHEN  t.ident~'tgr7_52' THEN sum_val END) as village_heat,
 sum( CASE WHEN (t.ident~'tgr7_2') or (adr.is_town = 0 and t.ident~'tgr7_6') THEN sum_val END) as village_other
 from 
(
--   select b.id_paccnt, t.id_grptar, t.lim_min, t.lim_max, bs.summ as sum_val, p.id as id_grp
   select b.id_paccnt, bs.id_tarif, t.id_grptar, t.lim_min, t.lim_max, bs.summ as sum_val
        from acm_bill_tbl as b 
	join acm_summ_tbl as bs on (b.id_doc= bs.id_doc) 
	join aqm_tarif_tbl as t on (t.id = bs.id_tarif)
--        join rep_zvit_pattern_tbl as p on (p.lim_min = coalesce(t.lim_min,0) and p.lim_max = coalesce(t.lim_max,0))
	where b.idk_doc in (200,220,209,291) and b.id_pref = 10
        and t.ident !~'tgr7_6'
        and t.ident not in ('tgr7_15','tgr7_25','tgr7_35','tgr7_53','tgr7_63')   
        and b.mmgg = vmmgg         
        and bs.dat_b >='2017-03-01'
) as ss2
 join clm_paccnt_tmp as a on (a.id = ss2.id_paccnt)
 join aqi_grptar_tbl as t on (t.id = a.id_gtar)
 join adt_addr_tbl as adr on (adr.id = (a.addr).id_class)

 join aqm_tarif_tbl as t2 on (t2.id =  CASE WHEN coalesce(ss2.id_grptar,0)<>coalesce(a.id_gtar,0) and (coalesce(a.id_gtar,0) not in (5,6,8,9,12,13))
                                            THEN a.id_tarif_min ELSE ss2.id_tarif END)
 join rep_zvit_pattern_tbl as p on (p.ident = '0' and p.lim_min = coalesce(t2.lim_min,0) and p.lim_max = coalesce(t2.lim_max,0))

where -- a.archive =0   and 
(pid_paccnt is null or a.id = pid_paccnt)
--and  t.ident not in ('tgr7_15','tgr7_25','tgr7_35','tgr7_53','tgr7_63')   
 and not exists (select lh.id_paccnt from rep_zvit_lgt_chnoe_tbl as lh where lh.id_paccnt = a.id)
group by p.id -- ss2.id_grp
) as ss3 on (p2.id = ss3.id_grp)
where p2.ident = '0'
order by p2.num;



 raise notice '12-2';
-- многодетным 
 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment, 'tovar_sum_0m','В т.ч. товар.сума багатодiтним','4_32','грн.',
 sum(CASE WHEN adr.is_town = 1 and t.ident~'tgr7_3' THEN sum_val END),
 sum(CASE WHEN  ((t.ident~'tgr7_51') or (t.ident~'tgr7_53')) THEN sum_val END),
 sum(CASE WHEN (t.ident~'tgr7_1') or (adr.is_town = 1 and t.ident~'tgr7_6')  THEN sum_val END),
 sum(CASE WHEN adr.is_town = 0 and t.ident~'tgr7_3' THEN sum_val END),
 sum(CASE WHEN  t.ident~'tgr7_52' THEN sum_val END),
 sum(CASE WHEN (t.ident~'tgr7_2') or (adr.is_town = 0 and t.ident~'tgr7_6')  THEN sum_val END)
,1,1,vmmgg
 from 
 clm_paccnt_tmp as a
 join adt_addr_tbl as adr on (adr.id = (a.addr).id_class)
 join ( 
   select b.id_doc , b.id_paccnt, t.id_grptar , bs.summ as sum_val
        from acm_bill_tbl as b 
	join acm_summ_tbl as bs on (b.id_doc= bs.id_doc) 
	join aqm_tarif_tbl as t on (t.id = bs.id_tarif)
	where b.idk_doc in (200,220,209,291) and b.id_pref = 10
        and b.mmgg = vmmgg         
        and bs.dat_b >='2017-03-01'
--        and t.ident in ('tgr7_15','tgr7_25','tgr7_35','tgr7_53','tgr7_63')   
 ) as bb on (bb.id_paccnt = a.id)
 join aqi_grptar_tbl as t on (t.id = bb.id_grptar)
-- join aqi_grptar_tbl as t on (t.id = a.id_gtar)
 where t.ident in ('tgr7_15','tgr7_25','tgr7_35','tgr7_53','tgr7_63') ;  


 raise notice '12-3';
-- не газифицированные  -s-o-s-
 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)

select vdepartment, 'tovar_sum_ng'||text(p2.ident)||text(p2.num),'В т.ч. не газиф '||p2.caption,'4_33'||text(p2.num),'грн.',
0,0,town_heat, 0, 0, village_heat, 1,1,vmmgg
from 
rep_zvit_pattern_tbl as p2 left join 
(
 select p.id as id_grp, -- ss2.id_grp,
 sum( CASE WHEN adr.is_town = 1 THEN sum_val END) as town_heat,
 sum( CASE WHEN adr.is_town = 0 THEN sum_val END) as village_heat
 from 
(
--   select b.id_paccnt, t.id_grptar, t.lim_min, t.lim_max, bs.summ as sum_val, p.id as id_grp
   select b.id_paccnt, bs.id_tarif, t.id_grptar, t.lim_min, t.lim_max, bs.summ as sum_val
        from acm_bill_tbl as b 
	join acm_summ_tbl as bs on (b.id_doc= bs.id_doc) 
	join aqm_tarif_tbl as t on (t.id = bs.id_tarif)
--        join rep_zvit_pattern_tbl as p on (p.lim_min = coalesce(t.lim_min,0) and p.lim_max = coalesce(t.lim_max,0))
	where b.idk_doc in (200,220,209,291) and b.id_pref = 10
        and t.ident ~'tgr7_6' and t.ident <> 'tgr7_63'
        and b.mmgg = vmmgg 
        and bs.dat_b >='2017-03-01'        
) as ss2
 join clm_paccnt_tmp as a on (a.id = ss2.id_paccnt)
 join aqi_grptar_tbl as t on (t.id = a.id_gtar)
 join adt_addr_tbl as adr on (adr.id = (a.addr).id_class)

 join aqm_tarif_tbl as t2 on (t2.id =  CASE WHEN coalesce(ss2.id_grptar,0)<>coalesce(a.id_gtar,0) THEN a.id_tarif_min ELSE ss2.id_tarif END)
 join rep_zvit_pattern_tbl as p on (p.ident = '0' and p.lim_min = coalesce(t2.lim_min,0) and p.lim_max = coalesce(t2.lim_max,0))

where -- a.archive =0   and 
(pid_paccnt is null or a.id = pid_paccnt)
group by p.id -- ss2.id_grp
) as ss3 on (p2.id = ss3.id_grp)
where p2.ident = '0'
order by p2.num;


/*
 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, gr_lvl, part_code, mmgg)
 values (vdepartment,'tovar_sum_c1', 'до 1 березня 2017 року','4_40','',1,1,vmmgg);
*/

 --сумма по шкале  -s-o-s-
 raise notice '13-1';

 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
select vdepartment, 'tovar_sum'||text(p2.ident)||text(p2.num),'В т.ч. товар.сума '||p2.caption,'4_41'||text(p2.num),'грн.',
town_stove,town_heat, town_other, village_stove, village_heat, village_other,1,1,vmmgg
from 
rep_zvit_pattern_tbl as p2 left join 
(
 select p.id as id_grp, -- ss2.id_grp,
 sum( CASE WHEN adr.is_town = 1 and t.ident~'tgr7_3' THEN sum_val END) as town_stove,
 sum( CASE WHEN  ((t.ident~'tgr7_51') or (t.ident~'tgr7_53')) THEN sum_val END) as town_heat,
 sum( CASE WHEN (t.ident~'tgr7_1') or (adr.is_town = 1 and t.ident~'tgr7_6') THEN sum_val END) as town_other,
 sum( CASE WHEN adr.is_town = 0 and t.ident~'tgr7_3' THEN sum_val END) as village_stove,
 sum( CASE WHEN  t.ident~'tgr7_52' THEN sum_val END) as village_heat,
 sum( CASE WHEN (t.ident~'tgr7_2') or (adr.is_town = 0 and t.ident~'tgr7_6') THEN sum_val END) as village_other
 from 
(
--   select b.id_paccnt, t.id_grptar, t.lim_min, t.lim_max, bs.summ as sum_val, p.id as id_grp
   select b.id_paccnt, bs.id_tarif, t.id_grptar, t.lim_min, t.lim_max, bs.summ as sum_val
        from acm_bill_tbl as b 
	join acm_summ_tbl as bs on (b.id_doc= bs.id_doc) 
	join aqm_tarif_tbl as t on (t.id = bs.id_tarif)
--        join rep_zvit_pattern_tbl as p on (p.lim_min = coalesce(t.lim_min,0) and p.lim_max = coalesce(t.lim_max,0))
	where b.idk_doc in (200,220,209,291) and b.id_pref = 10
        and t.ident !~'tgr7_6'
        and t.ident not in ('tgr7_15','tgr7_25','tgr7_35','tgr7_53','tgr7_63')   
        and b.mmgg = vmmgg         
        and bs.dat_b <'2017-03-01'
) as ss2
 join clm_paccnt_tmp as a on (a.id = ss2.id_paccnt)
	join ( 
	   select min(t.id) as id_tar_old, t.id_grptar
		from aqm_tarif_tbl as t 
	        where ((dt_e is null ) or (dt_e > '2017-02-01' )) and (dt_b <= '2017-02-01')
	        and (t.per_min is null or (t.per_min <= '2017-02-01' and t.per_max >= '2017-02-01'))
	        group by id_grptar
	 ) as told on (told.id_grptar = a.id_gtar)

 join aqi_grptar_tbl as t on (t.id = a.id_gtar)
 join adt_addr_tbl as adr on (adr.id = (a.addr).id_class)

 join aqm_tarif_tbl as t2 on (t2.id =  CASE WHEN coalesce(ss2.id_grptar,0)<>coalesce(a.id_gtar,0) and (coalesce(a.id_gtar,0) not in (5,6,8,9,12,13))
                                            THEN told.id_tar_old -- a.id_tarif_min 
				  	    ELSE ss2.id_tarif END)
 join rep_zvit_pattern_tbl as p on (p.ident = '1' and p.lim_min = coalesce(t2.lim_min,0) and p.lim_max = coalesce(t2.lim_max,0))

where -- a.archive =0   and 
(pid_paccnt is null or a.id = pid_paccnt)
--and  t.ident not in ('tgr7_15','tgr7_25','tgr7_35','tgr7_53','tgr7_63')   
 and not exists (select lh.id_paccnt from rep_zvit_lgt_chnoe_tbl as lh where lh.id_paccnt = a.id)
group by p.id -- ss2.id_grp
) as ss3 on (p2.id = ss3.id_grp)
where p2.ident = '1'
order by p2.num;



 raise notice '13-2';
-- многодетным 
 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment, 'tovar_sum_1m','В т.ч. товар.сума багатодiтним','4_42','грн.',
 sum(CASE WHEN adr.is_town = 1 and t.ident~'tgr7_3' THEN sum_val END),
 sum(CASE WHEN  ((t.ident~'tgr7_51') or (t.ident~'tgr7_53')) THEN sum_val END),
 sum(CASE WHEN (t.ident~'tgr7_1') or (adr.is_town = 1 and t.ident~'tgr7_6')  THEN sum_val END),
 sum(CASE WHEN adr.is_town = 0 and t.ident~'tgr7_3' THEN sum_val END),
 sum(CASE WHEN  t.ident~'tgr7_52' THEN sum_val END),
 sum(CASE WHEN (t.ident~'tgr7_2') or (adr.is_town = 0 and t.ident~'tgr7_6')  THEN sum_val END)
,1,1,vmmgg
 from 
 clm_paccnt_tmp as a
 join adt_addr_tbl as adr on (adr.id = (a.addr).id_class)
 join ( 
   select b.id_doc , b.id_paccnt, t.id_grptar , bs.summ as sum_val
        from acm_bill_tbl as b 
	join acm_summ_tbl as bs on (b.id_doc= bs.id_doc) 
	join aqm_tarif_tbl as t on (t.id = bs.id_tarif)
	where b.idk_doc in (200,220,209,291) and b.id_pref = 10
        and b.mmgg = vmmgg         
        and bs.dat_b <'2017-03-01'
--        and t.ident in ('tgr7_15','tgr7_25','tgr7_35','tgr7_53','tgr7_63')   
 ) as bb on (bb.id_paccnt = a.id)
 join aqi_grptar_tbl as t on (t.id = bb.id_grptar)
-- join aqi_grptar_tbl as t on (t.id = a.id_gtar)
 where t.ident in ('tgr7_15','tgr7_25','tgr7_35','tgr7_53','tgr7_63') ;  


 raise notice '13-3';
-- не газифицированные  -s-o-s-
 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)

select vdepartment, 'tovar_sum_ng'||text(p2.ident)||text(p2.num),'В т.ч. не газиф '||p2.caption,'4_43'||text(p2.num),'грн.',
0,0,town_heat, 0, 0, village_heat, 1,1,vmmgg
from 
rep_zvit_pattern_tbl as p2 left join 
(
 select p.id as id_grp, -- ss2.id_grp,
 sum( CASE WHEN adr.is_town = 1 THEN sum_val END) as town_heat,
 sum( CASE WHEN adr.is_town = 0 THEN sum_val END) as village_heat
 from 
(
--   select b.id_paccnt, t.id_grptar, t.lim_min, t.lim_max, bs.summ as sum_val, p.id as id_grp
   select b.id_paccnt, bs.id_tarif, t.id_grptar, t.lim_min, t.lim_max, bs.summ as sum_val
        from acm_bill_tbl as b 
	join acm_summ_tbl as bs on (b.id_doc= bs.id_doc) 
	join aqm_tarif_tbl as t on (t.id = bs.id_tarif)
--        join rep_zvit_pattern_tbl as p on (p.lim_min = coalesce(t.lim_min,0) and p.lim_max = coalesce(t.lim_max,0))
	where b.idk_doc in (200,220,209,291) and b.id_pref = 10
        and t.ident ~'tgr7_6' and t.ident <> 'tgr7_63'
        and b.mmgg = vmmgg 
        and bs.dat_b <'2017-03-01'        
) as ss2
 join clm_paccnt_tmp as a on (a.id = ss2.id_paccnt)
	join ( 
	   select min(t.id) as id_tar_old, t.id_grptar
		from aqm_tarif_tbl as t 
	        where ((dt_e is null ) or (dt_e > '2017-02-01' )) and (dt_b <= '2017-02-01')
	        and (t.per_min is null or (t.per_min <= '2017-02-01' and t.per_max >= '2017-02-01'))
	        group by id_grptar
	 ) as told on (told.id_grptar = a.id_gtar)

 join aqi_grptar_tbl as t on (t.id = a.id_gtar)
 join adt_addr_tbl as adr on (adr.id = (a.addr).id_class)

 join aqm_tarif_tbl as t2 on (t2.id =  CASE WHEN coalesce(ss2.id_grptar,0)<>coalesce(a.id_gtar,0) 
					THEN told.id_tar_old -- a.id_tarif_min 
					ELSE ss2.id_tarif END)
 join rep_zvit_pattern_tbl as p on (p.ident = '1' and p.lim_min = coalesce(t2.lim_min,0) and p.lim_max = coalesce(t2.lim_max,0))

where -- a.archive =0   and 
(pid_paccnt is null or a.id = pid_paccnt)
group by p.id -- ss2.id_grp
) as ss3 on (p2.id = ss3.id_grp)
where p2.ident = '1'
order by p2.num;


 

 raise notice '14-1';
-- подстанции 
 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment, 'tovar_sum_ps1','В т.ч. перс.пiдст. до 3000 кВтг','4_61','грн.',0,
 sum( CASE WHEN adr.is_town = 1 THEN sum_val END) as town_heat,0,0,
 sum( CASE WHEN adr.is_town = 0 THEN sum_val END) as village_heat ,0
,1,1,vmmgg
 from 
 clm_paccnt_tmp as a
 join adt_addr_tbl as adr on (adr.id = (a.addr).id_class)
 join ( 
   select b.id_doc , b.id_paccnt, t.id_grptar , bs.summ as sum_val
        from acm_bill_tbl as b 
	join acm_summ_tbl as bs on (b.id_doc= bs.id_doc) 
	join aqm_tarif_tbl as t on (t.id = bs.id_tarif)
	where b.idk_doc in (200,220,209,291) and b.id_pref = 10
--	and coalesce(t.lim_min,0)=0
	and coalesce(t.lim_min,0)<3000
        and b.mmgg = vmmgg         
 ) as bb on (bb.id_paccnt = a.id)
 where exists (select lh.id_paccnt from rep_zvit_lgt_chnoe_tbl as lh where lh.id_paccnt = a.id);

 raise notice '14-2';

 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment, 'tovar_sum_ps2','В т.ч. перс.пiдст. понад 3000 кВтг','4_62','грн.',0,
 sum( CASE WHEN adr.is_town = 1 THEN sum_val END) as town_heat,0,0,
 sum( CASE WHEN adr.is_town = 0 THEN sum_val END) as village_heat ,0
,1,1,vmmgg
 from 
 clm_paccnt_tmp as a
 join adt_addr_tbl as adr on (adr.id = (a.addr).id_class)
 join ( 
   select b.id_doc , b.id_paccnt, t.id_grptar , bs.summ as sum_val
        from acm_bill_tbl as b 
	join acm_summ_tbl as bs on (b.id_doc= bs.id_doc) 
	join aqm_tarif_tbl as t on (t.id = bs.id_tarif)
	where b.idk_doc in (200,220,209,291) and b.id_pref = 10
	and coalesce(t.lim_min,0)>=3000
        and b.mmgg = vmmgg         
 ) as bb on (bb.id_paccnt = a.id)
 where exists (select lh.id_paccnt from rep_zvit_lgt_chnoe_tbl as lh where lh.id_paccnt = a.id);
-----------------------------------------------------------------------
/*
 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment,'tovar_sum','Сума товарна Всього','4_0','грн.', sum(town_stove),sum(town_heat), sum(town_other), 
                           sum(village_stove), sum(village_heat), sum(village_other), 2, 1, vmmgg
 from rep_zvit_tmp where mmgg = vmmgg and ident ~ 'tovar_sum';
*/

-----------------------------------------------------------------------
--------------- перенос начального сальдо -----------------------------
-- перенос включен всегда!
--  if (pid_paccnt is null ) and ( (pid_town is null and kodres not in (320,330))  or (pid_town< 0 and kodres in (320,330) ) )  then
 -- if (pid_paccnt is null ) and (pid_town is null) then

    delete from rep_zvit_abon_tbl where mmgg=vmmgg and id_dep = vdepartment;

    delete from rep_zvit_column_move_tbl where mmgg=vmmgg and id_dep = vdepartment;


    update rep_zvit_tmp set town_stove =coalesce(town_stove,0), town_heat = coalesce(town_heat,0),town_other = coalesce(town_other,0),
     village_stove =coalesce( village_stove,0),  village_heat = coalesce( village_heat,0), village_other = coalesce( village_other,0)
	where mmgg=vmmgg ;

    raise notice '14-3';

    -- for next month saldo check
    insert into rep_zvit_abon_tbl (id_dep, id_paccnt, id_gtar, id_tarif, zcolumn, mmgg)
    select vdepartment, a.id, a.id_gtar, a.id_tarif_min, 
    CASE WHEN adr.is_town = 1 and t.ident~'tgr7_3' THEN 1 
         WHEN ((t.ident~'tgr7_51') or (t.ident~'tgr7_53')) THEN 2
         WHEN t.ident~'tgr7_1' or (adr.is_town = 1 and t.ident~'tgr7_6') THEN 3
         WHEN adr.is_town = 0 and t.ident~'tgr7_3' THEN 4
         WHEN t.ident~'tgr7_52' THEN 5 
         WHEN t.ident~'tgr7_2' or (adr.is_town = 0 and t.ident~'tgr7_6')  THEN 6 END ,vmmgg
     from 
     clm_paccnt_tmp as a
     join aqi_grptar_tbl as t on (t.id = a.id_gtar)
     join adt_addr_tbl as adr on (adr.id = (a.addr).id_class)  ;

    raise notice '14-4';

  for rr in 
   select z1.*, z2.id_gtar as old_gtar, z2.id_tarif as old_tarif, z2.zcolumn  as old_column,
     t1.ident as ident1,t2.ident as ident2, s.saldo
   from rep_zvit_abon_tbl as z1
   join rep_zvit_abon_tbl as z2 on (z1.id_paccnt = z2.id_paccnt )
   join aqi_grptar_tbl as t1 on (t1.id = z1.id_gtar)
   join aqi_grptar_tbl as t2 on (t2.id = z2.id_gtar)
   join (
     select id_paccnt, sum(e_dtval-e_ktval) as saldo  from seb_saldo where mmgg = (vmmgg::date-'1 month'::interval)::date
     group by id_paccnt order by id_paccnt
   ) as s on (s.id_paccnt = z1.id_paccnt) 
   where z1.mmgg = vmmgg and z2.mmgg = (vmmgg::date-'1 month'::interval)::date
   and z1.id_dep = vdepartment 
   and z2.id_dep = vdepartment
   and z1.zcolumn <>z2.zcolumn and coalesce(s.saldo,0)<>0
  loop

    vname1 = CASE WHEN rr.zcolumn = 1 THEN 'town_stove'
		  WHEN rr.zcolumn = 2 THEN 'town_heat'
		  WHEN rr.zcolumn = 3 THEN 'town_other'
		  WHEN rr.zcolumn = 4 THEN 'village_stove'
		  WHEN rr.zcolumn = 5 THEN 'village_heat'
		  WHEN rr.zcolumn = 6 THEN 'village_other' END;


    vname2 = CASE WHEN rr.old_column = 1 THEN 'town_stove'
		  WHEN rr.old_column = 2 THEN 'town_heat'
		  WHEN rr.old_column = 3 THEN 'town_other'
		  WHEN rr.old_column = 4 THEN 'village_stove'
		  WHEN rr.old_column = 5 THEN 'village_heat'
		  WHEN rr.old_column = 6 THEN 'village_other' END;


    raise notice 'id_paccnt % % -> %', rr.id_paccnt, vname2, vname1 ;

    vstr_num_old:=null;
    vstr_num:=null;

    if (rr.ident1 in ('tgr7_15','tgr7_25','tgr7_35','tgr7_53','tgr7_63')) then

      SQL='update rep_zvit_tmp set '||vname1||'='||vname1||'+ '||(rr.saldo)::varchar||' where mmgg = '''||to_char(vmmgg, 'YYYY-MM-DD') || ''' and ident = ''tovar_sum_0m''; ';

      raise notice 'SQL1 - %', SQL;
      Execute SQL;
      

      SQL='update rep_zvit_tmp set '||vname1||'='||vname1||'+ '||(rr.saldo)::varchar||' where mmgg = '''||to_char(vmmgg, 'YYYY-MM-DD') || ''' and ident = ''bill_sum_0m''; ';

      raise notice 'SQL2 - %', SQL;
      Execute SQL;

    else

      select into vstr_num (p.ident||p.num)::varchar
      from aqm_tarif_tbl as t2 
      join rep_zvit_pattern_tbl as p on (p.ident = '0' and p.lim_min = coalesce(t2.lim_min,0) and p.lim_max = coalesce(t2.lim_max,0))
      where t2.id = rr.id_tarif;


      SQL='update rep_zvit_tmp set '||vname1||'='||vname1||'+ '||(rr.saldo)::varchar||' where mmgg = '''||to_char(vmmgg, 'YYYY-MM-DD') || ''' and ident = '||'''tovar_sum'||vstr_num||''';';

      raise notice 'SQL3 - %', SQL;
      Execute SQL;
      

      SQL='update rep_zvit_tmp set '||vname1||'='||vname1||'+ '||(rr.saldo)::varchar||' where mmgg = '''||to_char(vmmgg, 'YYYY-MM-DD') || ''' and ident = '||'''bill_sum'||vstr_num||''';';

      raise notice 'SQL4 - %', SQL;
      Execute SQL;
    

    end if; 


    SQL='update rep_zvit_tmp set '||vname1||'='||vname1||'+ '||(rr.saldo)::varchar||' where mmgg = '''||to_char(vmmgg, 'YYYY-MM-DD') || ''' and ident = '||'''tovar_sum''';

    raise notice 'SQL5 - %', SQL;
    Execute SQL;
      

    SQL='update rep_zvit_tmp set '||vname1||'='||vname1||'+ '||(rr.saldo)::varchar||' where mmgg = '''||to_char(vmmgg, 'YYYY-MM-DD') || ''' and ident = '||'''bill_sum''';

    raise notice 'SQL6 - %', SQL;
    Execute SQL;




    if (rr.ident2 in ('tgr7_15','tgr7_25','tgr7_35','tgr7_53','tgr7_63')) then

      SQL='update rep_zvit_tmp set '||vname2||'='||vname2||'- '||(rr.saldo)::varchar||' where mmgg = '''||to_char(vmmgg, 'YYYY-MM-DD') || ''' and ident = ''tovar_sum_0m''; ';

      raise notice 'SQL7 - %', SQL;
      Execute SQL;
      

      SQL='update rep_zvit_tmp set '||vname2||'='||vname2||'- '||(rr.saldo)::varchar||' where mmgg = '''||to_char(vmmgg, 'YYYY-MM-DD') || ''' and ident = ''bill_sum_0m''; ';

      raise notice 'SQL8 - %', SQL;
      Execute SQL;

    else

      if (vmmgg='2017-03-01') then 

        select into vstr_num_old (p.ident||p.num)::varchar
        from aqm_tarif_tbl as t2 
        join rep_zvit_pattern_tbl as p on (p.ident = '1' and p.lim_min = coalesce(t2.lim_min,0) and p.lim_max = coalesce(t2.lim_max,0))
        where t2.id = rr.old_tarif;

      else

        select into vstr_num_old (p.ident||p.num)::varchar
        from aqm_tarif_tbl as t2 
        join rep_zvit_pattern_tbl as p on (p.ident = '0' and p.lim_min = coalesce(t2.lim_min,0) and p.lim_max = coalesce(t2.lim_max,0))
        where t2.id = rr.old_tarif;

      end if;
  

      SQL='update rep_zvit_tmp set '||vname2||'='||vname2||'- '||(rr.saldo)::varchar||' where mmgg = '''||to_char(vmmgg, 'YYYY-MM-DD') || ''' and ident = '||'''tovar_sum'||vstr_num_old||''';';

      raise notice 'SQL9 - %', SQL;
      Execute SQL;
      

      SQL='update rep_zvit_tmp set '||vname2||'='||vname2||'- '||(rr.saldo)::varchar||' where mmgg = '''||to_char(vmmgg, 'YYYY-MM-DD') || ''' and ident = '||'''bill_sum'||vstr_num_old||''';';

      raise notice 'SQL10 - %', SQL;
      Execute SQL;


    end if; 


    SQL='update rep_zvit_tmp set '||vname2||'='||vname2||'- '||(rr.saldo)::varchar||' where mmgg = '''||to_char(vmmgg, 'YYYY-MM-DD') || ''' and ident = '||'''tovar_sum''';

    raise notice 'SQL11 - %', SQL;
    Execute SQL;
      

    SQL='update rep_zvit_tmp set '||vname2||'='||vname2||'- '||(rr.saldo)::varchar||' where mmgg = '''||to_char(vmmgg, 'YYYY-MM-DD') || ''' and ident = '||'''bill_sum''';

    raise notice 'SQL12 - %', SQL;
    Execute SQL;

    
    insert into rep_zvit_column_move_tbl(id_dep, id_paccnt, id_gtar_old,  zcolumn_old,  str_num_old, id_gtar_new, zcolumn_new, str_num_new , sum_saldo, mmgg )
    values(vdepartment, rr.id_paccnt, rr.old_gtar, rr.old_column, vstr_num_old, rr.id_gtar, rr.zcolumn, vstr_num, rr.saldo, vmmgg);


  end loop;


-- end if;

--------------------- полезный отпуск, кВтг ---------------------------
/*
 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, gr_lvl, part_code, mmgg)
 values (vdepartment,'bill_dem_c', 'З 1 березня 2017 року','3_02','',1,1,vmmgg);
*/

 --сумма по шкале  -s-o-s-
 raise notice 'bill_dem';
 raise notice '15';

 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
select vdepartment, 'bill_dem'||text(p2.ident)||text(p2.num),'В т.ч. вiдпуск '||p2.caption,'3_12'||text(p2.num),'кВтг',
town_stove,town_heat, town_other, village_stove, village_heat, village_other,1,1,vmmgg
from 
rep_zvit_pattern_tbl as p2 left join 
(
 select p.id as id_grp, -- ss2.id_grp,
 sum( CASE WHEN adr.is_town = 1 and t.ident~'tgr7_3' THEN demand END) as town_stove,
 sum( CASE WHEN  ((t.ident~'tgr7_51') or (t.ident~'tgr7_53')) THEN demand END) as town_heat,
 sum( CASE WHEN t.ident~'tgr7_1' or (adr.is_town = 1 and t.ident~'tgr7_6')  THEN demand END) as town_other,
 sum( CASE WHEN adr.is_town = 0 and t.ident~'tgr7_3' THEN demand END) as village_stove,
 sum( CASE WHEN  t.ident~'tgr7_52' THEN demand END) as village_heat,
 sum( CASE WHEN t.ident~'tgr7_2' or (adr.is_town = 0 and t.ident~'tgr7_6')  THEN demand END) as village_other

 from 
(
--   select b.id_paccnt, t.id_grptar, t.lim_min, t.lim_max, bs.demand, p.id as id_grp
   select b.id_paccnt, bs.id_tarif, t.id_grptar, t.lim_min, t.lim_max, bs.demand
        from acm_bill_tbl as b 
	join acm_summ_tbl as bs on (b.id_doc= bs.id_doc) 
	join aqm_tarif_tbl as t on (t.id = bs.id_tarif)
--        join rep_zvit_pattern_tbl as p on (p.lim_min = coalesce(t.lim_min,0) and p.lim_max = coalesce(t.lim_max,0))
	where b.idk_doc in (200,220,209,291) and bs.demand <>0 and b.id_pref = 10
        and t.ident !~'tgr7_6'
        and t.ident not in ('tgr7_15','tgr7_25','tgr7_35','tgr7_53','tgr7_63')   
        and b.mmgg = vmmgg    
        and bs.dat_b >='2017-03-01'
) as ss2
 join clm_paccnt_tmp as a on (a.id = ss2.id_paccnt)

 join aqi_grptar_tbl as t on (t.id = a.id_gtar)
 join adt_addr_tbl as adr on (adr.id = (a.addr).id_class)

 join aqm_tarif_tbl as t2 on (t2.id =  CASE WHEN coalesce(ss2.id_grptar,0)<>coalesce(a.id_gtar,0) and (coalesce(a.id_gtar,0) not in (5,6,8,9,12,13))
                                            THEN a.id_tarif_min ELSE ss2.id_tarif END)
 join rep_zvit_pattern_tbl as p on (p.ident = '0' and p.lim_min = coalesce(t2.lim_min,0) and p.lim_max = coalesce(t2.lim_max,0))

where -- a.archive =0   and 
(pid_paccnt is null or a.id = pid_paccnt)
--and t.ident not in ('tgr7_15','tgr7_25','tgr7_35','tgr7_53','tgr7_63')      
 and not exists (select lh.id_paccnt from rep_zvit_lgt_chnoe_tbl as lh where lh.id_paccnt = a.id)
group by p.id -- ss2.id_grp
) as ss3 on (p2.id = ss3.id_grp)
where p2.ident = '0'
order by p2.num;


-- многодетным 
 raise notice '15-1';
 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment, 'bill_dem_0m','В т.ч. вiдпуск багатодiтним','3_14','кВтг',
 sum(CASE WHEN adr.is_town = 1 and t.ident~'tgr7_3' THEN demand END),
 sum(CASE WHEN  ((t.ident~'tgr7_51') or (t.ident~'tgr7_53')) THEN demand END),
 sum(CASE WHEN (t.ident~'tgr7_1') or (adr.is_town = 1 and t.ident~'tgr7_6')  THEN demand END),
 sum(CASE WHEN adr.is_town = 0 and t.ident~'tgr7_3' THEN demand END),
 sum(CASE WHEN  t.ident~'tgr7_52' THEN demand END),
 sum(CASE WHEN (t.ident~'tgr7_2') or (adr.is_town = 0 and t.ident~'tgr7_6')  THEN demand END)
,1,1,vmmgg
 from 
 clm_paccnt_tmp as a
 join adt_addr_tbl as adr on (adr.id = (a.addr).id_class)
 join ( 
   select b.id_doc , b.id_paccnt, t.id_grptar , bs.demand
        from acm_bill_tbl as b 
	join acm_summ_tbl as bs on (b.id_doc= bs.id_doc) 
	join aqm_tarif_tbl as t on (t.id = bs.id_tarif)
	where b.idk_doc in (200,220,209,291) 
        and bs.demand <>0 
        and b.mmgg = vmmgg and b.id_pref = 10
        and bs.dat_b >='2017-03-01'
--        and t.ident in ('tgr7_15','tgr7_25','tgr7_35','tgr7_53','tgr7_63')   
 ) as bb on (bb.id_paccnt = a.id)
 join aqi_grptar_tbl as t on (t.id = bb.id_grptar)
-- join aqi_grptar_tbl as t on (t.id = a.id_gtar)
 where t.ident in ('tgr7_15','tgr7_25','tgr7_35','tgr7_53','tgr7_63') ;


-- не газифицированные -s-o-s-
 raise notice '15-2';
 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)

select vdepartment, 'bill_dem_ng'||text(p2.ident)||text(p2.num),'В т.ч. не газиф '||p2.caption,'3_15'||text(p2.num),'кВтг',
0,0,town_heat, 0,0, village_heat, 1,1,vmmgg
from 
rep_zvit_pattern_tbl as p2 left join 
(
 select p.id as id_grp, -- ss2.id_grp,
 sum( CASE WHEN adr.is_town = 1 THEN demand END) as town_heat,
 sum( CASE WHEN adr.is_town = 0 THEN demand END) as village_heat
 from 
(
--   select b.id_paccnt, t.id_grptar, t.lim_min, t.lim_max, bs.demand, p.id as id_grp
   select b.id_paccnt, bs.id_tarif, t.id_grptar, t.lim_min, t.lim_max, bs.demand
        from acm_bill_tbl as b 
	join acm_summ_tbl as bs on (b.id_doc= bs.id_doc) 
	join aqm_tarif_tbl as t on (t.id = bs.id_tarif)
--        join rep_zvit_pattern_tbl as p on (p.lim_min = coalesce(t.lim_min,0) and p.lim_max = coalesce(t.lim_max,0))
	where b.idk_doc in (200,220,209,291) and bs.demand <>0 and b.id_pref = 10
        and t.ident ~'tgr7_6' and t.ident <> 'tgr7_63'
        and b.mmgg = vmmgg   
        and bs.dat_b >='2017-03-01'      
) as ss2
 join clm_paccnt_tmp as a on (a.id = ss2.id_paccnt)
 join aqi_grptar_tbl as t on (t.id = a.id_gtar)
 join adt_addr_tbl as adr on (adr.id = (a.addr).id_class)

 join aqm_tarif_tbl as t2 on (t2.id =  CASE WHEN coalesce(ss2.id_grptar,0)<>coalesce(a.id_gtar,0) THEN a.id_tarif_min ELSE ss2.id_tarif END)
 join rep_zvit_pattern_tbl as p on (p.ident = '0' and p.lim_min = coalesce(t2.lim_min,0) and p.lim_max = coalesce(t2.lim_max,0))

where -- a.archive =0   and 
(pid_paccnt is null or a.id = pid_paccnt)
group by p.id -- ss2.id_grp
) as ss3 on (p2.id = ss3.id_grp)
where p2.ident = '0'
order by p2.num;


-- before 01.03.2017
/*
 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, gr_lvl, part_code, mmgg)
 values (vdepartment,'bill_dem_c1', 'до 1 березня 2017 року','3_20','',1,1,vmmgg);
*/
 raise notice '16';

 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
select vdepartment, 'bill_dem'||text(p2.ident)||text(p2.num),'В т.ч. вiдпуск '||p2.caption,'3_22'||text(p2.num),'кВтг',
town_stove,town_heat, town_other, village_stove, village_heat, village_other,1,1,vmmgg
from 
rep_zvit_pattern_tbl as p2 left join 
(
 select p.id as id_grp, -- ss2.id_grp,
 sum( CASE WHEN adr.is_town = 1 and t.ident~'tgr7_3' THEN demand END) as town_stove,
 sum( CASE WHEN  ((t.ident~'tgr7_51') or (t.ident~'tgr7_53')) THEN demand END) as town_heat,
 sum( CASE WHEN t.ident~'tgr7_1' or (adr.is_town = 1 and t.ident~'tgr7_6')  THEN demand END) as town_other,
 sum( CASE WHEN adr.is_town = 0 and t.ident~'tgr7_3' THEN demand END) as village_stove,
 sum( CASE WHEN  t.ident~'tgr7_52' THEN demand END) as village_heat,
 sum( CASE WHEN t.ident~'tgr7_2' or (adr.is_town = 0 and t.ident~'tgr7_6')  THEN demand END) as village_other

 from 
(
--   select b.id_paccnt, t.id_grptar, t.lim_min, t.lim_max, bs.demand, p.id as id_grp
   select b.id_paccnt, bs.id_tarif, t.id_grptar, t.lim_min, t.lim_max, bs.demand
        from acm_bill_tbl as b 
	join acm_summ_tbl as bs on (b.id_doc= bs.id_doc) 
	join aqm_tarif_tbl as t on (t.id = bs.id_tarif)
--        join rep_zvit_pattern_tbl as p on (p.lim_min = coalesce(t.lim_min,0) and p.lim_max = coalesce(t.lim_max,0))
	where b.idk_doc in (200,220,209,291) and bs.demand <>0 and b.id_pref = 10
        and t.ident !~'tgr7_6'
        and t.ident not in ('tgr7_15','tgr7_25','tgr7_35','tgr7_53','tgr7_63')   
        and b.mmgg = vmmgg    
        and bs.dat_b <'2017-03-01'
) as ss2
 join clm_paccnt_tmp as a on (a.id = ss2.id_paccnt)
	join ( 
	   select min(t.id) as id_tar_old, t.id_grptar
		from aqm_tarif_tbl as t 
	        where ((dt_e is null ) or (dt_e > '2017-02-01' )) and (dt_b <= '2017-02-01')
	        and (t.per_min is null or (t.per_min <= '2017-02-01' and t.per_max >= '2017-02-01'))
	        group by id_grptar
	 ) as told on (told.id_grptar = a.id_gtar)

 join aqi_grptar_tbl as t on (t.id = a.id_gtar)
 join adt_addr_tbl as adr on (adr.id = (a.addr).id_class)

 join aqm_tarif_tbl as t2 on (t2.id =  CASE WHEN coalesce(ss2.id_grptar,0)<>coalesce(a.id_gtar,0) and (coalesce(a.id_gtar,0) not in (5,6,8,9,12,13))
                                            THEN told.id_tar_old --a.id_tarif_min 
					    ELSE ss2.id_tarif END)
 join rep_zvit_pattern_tbl as p on (p.ident = '1' and p.lim_min = coalesce(t2.lim_min,0) and p.lim_max = coalesce(t2.lim_max,0))

where -- a.archive =0   and 
(pid_paccnt is null or a.id = pid_paccnt)
--and t.ident not in ('tgr7_15','tgr7_25','tgr7_35','tgr7_53','tgr7_63')   
 and not exists (select lh.id_paccnt from rep_zvit_lgt_chnoe_tbl as lh where lh.id_paccnt = a.id)
group by p.id -- ss2.id_grp
) as ss3 on (p2.id = ss3.id_grp)
where p2.ident = '1'
order by p2.num;


-- многодетным 
 raise notice '16-1';
 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment, 'bill_dem_1m','В т.ч. вiдпуск багатодiтним','3_24','кВтг',
 sum(CASE WHEN adr.is_town = 1 and t.ident~'tgr7_3' THEN demand END),
 sum(CASE WHEN  ((t.ident~'tgr7_51') or (t.ident~'tgr7_53')) THEN demand END),
 sum(CASE WHEN (t.ident~'tgr7_1') or (adr.is_town = 1 and t.ident~'tgr7_6')  THEN demand END),
 sum(CASE WHEN adr.is_town = 0 and t.ident~'tgr7_3' THEN demand END),
 sum(CASE WHEN  t.ident~'tgr7_52' THEN demand END),
 sum(CASE WHEN (t.ident~'tgr7_2') or (adr.is_town = 0 and t.ident~'tgr7_6')  THEN demand END)
,1,1,vmmgg
 from 
 clm_paccnt_tmp as a
 join adt_addr_tbl as adr on (adr.id = (a.addr).id_class)
 join ( 
   select b.id_doc , b.id_paccnt, t.id_grptar , bs.demand
        from acm_bill_tbl as b 
	join acm_summ_tbl as bs on (b.id_doc= bs.id_doc) 
	join aqm_tarif_tbl as t on (t.id = bs.id_tarif)
	where b.idk_doc in (200,220,209,291) 
        and bs.demand <>0 
        and b.mmgg = vmmgg and b.id_pref = 10
        and bs.dat_b <'2017-03-01'
--        and t.ident in ('tgr7_15','tgr7_25','tgr7_35','tgr7_53','tgr7_63')   
 ) as bb on (bb.id_paccnt = a.id)
 join aqi_grptar_tbl as t on (t.id = bb.id_grptar)
-- join aqi_grptar_tbl as t on (t.id = a.id_gtar)
 where t.ident in ('tgr7_15','tgr7_25','tgr7_35','tgr7_53','tgr7_63') ;


-- не газифицированные -s-o-s-
 raise notice '16-2';
 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)

select vdepartment, 'bill_dem_ng'||text(p2.ident)||text(p2.num),'В т.ч. не газиф '||p2.caption,'3_25'||text(p2.num),'кВтг',
0,0,town_heat, 0,0, village_heat, 1,1,vmmgg
from 
rep_zvit_pattern_tbl as p2 left join 
(
 select p.id as id_grp, -- ss2.id_grp,
 sum( CASE WHEN adr.is_town = 1 THEN demand END) as town_heat,
 sum( CASE WHEN adr.is_town = 0 THEN demand END) as village_heat
 from 
(
--   select b.id_paccnt, t.id_grptar, t.lim_min, t.lim_max, bs.demand, p.id as id_grp
   select b.id_paccnt, bs.id_tarif, t.id_grptar, t.lim_min, t.lim_max, bs.demand
        from acm_bill_tbl as b 
	join acm_summ_tbl as bs on (b.id_doc= bs.id_doc) 
	join aqm_tarif_tbl as t on (t.id = bs.id_tarif)
--        join rep_zvit_pattern_tbl as p on (p.lim_min = coalesce(t.lim_min,0) and p.lim_max = coalesce(t.lim_max,0))
	where b.idk_doc in (200,220,209,291) and bs.demand <>0 and b.id_pref = 10
        and t.ident ~'tgr7_6' and t.ident <> 'tgr7_63'
        and b.mmgg = vmmgg   
        and bs.dat_b <'2017-03-01'      
) as ss2
 join clm_paccnt_tmp as a on (a.id = ss2.id_paccnt)
	join ( 
	   select min(t.id) as id_tar_old, t.id_grptar
		from aqm_tarif_tbl as t 
	        where ((dt_e is null ) or (dt_e > '2017-02-01' )) and (dt_b <= '2017-02-01')
	        and (t.per_min is null or (t.per_min <= '2017-02-01' and t.per_max >= '2017-02-01'))
	        group by id_grptar
	 ) as told on (told.id_grptar = a.id_gtar)

 join aqi_grptar_tbl as t on (t.id = a.id_gtar)
 join adt_addr_tbl as adr on (adr.id = (a.addr).id_class)

 join aqm_tarif_tbl as t2 on (t2.id =  CASE WHEN coalesce(ss2.id_grptar,0)<>coalesce(a.id_gtar,0) 
                                            THEN told.id_tar_old --a.id_tarif_min 
                                            ELSE ss2.id_tarif END)
 join rep_zvit_pattern_tbl as p on (p.ident = '1' and p.lim_min = coalesce(t2.lim_min,0) and p.lim_max = coalesce(t2.lim_max,0))

where -- a.archive =0   and 
(pid_paccnt is null or a.id = pid_paccnt)
group by p.id -- ss2.id_grp
) as ss3 on (p2.id = ss3.id_grp)
where p2.ident = '1'
order by p2.num;



 raise notice '17-1';
 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment, 'bill_dem_ps1','В т.ч. перс.пiдст. до 3000 кВтг','3_61','кВтг', 0,
 sum( CASE WHEN adr.is_town = 1 THEN demand END) as town_heat, 0,0,
 sum( CASE WHEN adr.is_town = 0 THEN demand END) as village_heat,0
 ,1,1,vmmgg
 from 
 clm_paccnt_tmp as a
 join adt_addr_tbl as adr on (adr.id = (a.addr).id_class)
 join ( 
   select b.id_doc , b.id_paccnt, t.id_grptar , bs.demand
        from acm_bill_tbl as b 
	join acm_summ_tbl as bs on (b.id_doc= bs.id_doc) 
	join aqm_tarif_tbl as t on (t.id = bs.id_tarif)
	where b.idk_doc in (200,220,209,291) and bs.demand <>0 
        and b.mmgg = vmmgg and b.id_pref = 10 
        --and coalesce(t.lim_min,0)=0
        and coalesce(t.lim_min,0)<3000
 ) as bb on (bb.id_paccnt = a.id)
 where exists (select lh.id_paccnt from rep_zvit_lgt_chnoe_tbl as lh where lh.id_paccnt = a.id);


 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment, 'bill_dem_ps2','В т.ч. перс.пiдст. понад 3000 кВтг','3_62','кВтг', 0,
 sum( CASE WHEN adr.is_town = 1 THEN demand END) as town_heat, 0,0,
 sum( CASE WHEN adr.is_town = 0 THEN demand END) as village_heat,0
 ,1,1,vmmgg
 from 
 clm_paccnt_tmp as a
 join adt_addr_tbl as adr on (adr.id = (a.addr).id_class)
 join ( 
   select b.id_doc , b.id_paccnt, t.id_grptar , bs.demand
        from acm_bill_tbl as b 
	join acm_summ_tbl as bs on (b.id_doc= bs.id_doc) 
	join aqm_tarif_tbl as t on (t.id = bs.id_tarif)
	where b.idk_doc in (200,220,209,291) and bs.demand <>0 
        and b.mmgg = vmmgg and b.id_pref = 10 and coalesce(t.lim_min,0)>=3000
 ) as bb on (bb.id_paccnt = a.id)
 where exists (select lh.id_paccnt from rep_zvit_lgt_chnoe_tbl as lh where lh.id_paccnt = a.id);


 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment,'bill_dem','Корисний вiдпуск','3_0','кВтг.', sum(town_stove),sum(town_heat), sum(town_other), 
                           sum(village_stove), sum(village_heat), sum(village_other), 2, 1, vmmgg
 from rep_zvit_tmp where mmgg = vmmgg and ident ~ 'bill_dem';


 raise notice '18';
---------------------------------------------------------------------------------------------------------
-- сальдо на начало

 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, gr_lvl, part_code, mmgg)
 values (vdepartment,'saldo_b', 'Заборгованiсть абонентiв на початок перiоду','2_0','',0,1,vmmgg);


-- select into v count(*) from rep_zvit_abon_tbl where id_dep = vdepartment and mmgg = (vmmgg::date-'1 month'::interval)::date;
 select into v count(*) from rep_zvit_tbl where id_dep = vdepartment and mmgg = (vmmgg::date-'1 month'::interval)::date;


 if (pid_paccnt is not null ) or (pid_town is not null and kodres not in (320,330,241,242,202,260,200) ) or (pid_town is null and kodres in (320,330) ) or (v = 0) then


   select into v count(*) from rep_zvit_abon_tbl where mmgg = (vmmgg::date-'1 month'::interval)::date;

   -- первый месяц работы Сосницы в составе Мены
   if kodres in ( 200,202,260) and vcur_mmgg = '2017-09-01' and pid_town =-2 then
    v := 0;
   end if;
   --
   
   if (v = 0) then


     raise notice 'Saldo start calc tarif';

     insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
     select vdepartment,'saldo_b_dt','Дебиторська заборг.     Всього','2_1','грн.',
     sum(CASE WHEN adr.is_town = 1 and t.ident~'tgr7_3' THEN b_val END),
     sum(CASE WHEN  ((t.ident~'tgr7_51') or (t.ident~'tgr7_53')) THEN b_val END),
     sum(CASE WHEN (t.ident~'tgr7_1') or (adr.is_town = 1 and t.ident~'tgr7_6') THEN b_val END),
     sum(CASE WHEN adr.is_town = 0 and t.ident~'tgr7_3' THEN b_val END),
     sum(CASE WHEN  t.ident~'tgr7_52' THEN b_val END),
     sum(CASE WHEN (t.ident~'tgr7_2') or (adr.is_town = 0 and t.ident~'tgr7_6') THEN b_val END)
    ,2,1,vmmgg
     from 
     clm_paccnt_tmp as a
     join aqi_grptar_tbl as t on (t.id = a.id_gtar)
     join adt_addr_tbl as adr on (adr.id = (a.addr).id_class)
     join (
       select id_paccnt, sum(b_dtval) as b_val  from seb_saldo_tmp where mmgg = vmmgg group by id_paccnt order by id_paccnt
     ) as s on (s.id_paccnt = a.id) ;

    -- по годам
     insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                             village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)

     select vdepartment,'saldo_b_dt_'||date_part('year',hmmgg)::varchar,'- - дебет '||date_part('year',hmmgg)::varchar||' року','2_10'||date_part('year',hmmgg)::varchar,'грн.',
     sum(CASE WHEN adr.is_town = 1 and t.ident~'tgr7_3' THEN b_val END),
     sum(CASE WHEN  ((t.ident~'tgr7_51') or (t.ident~'tgr7_53')) THEN b_val END),
     sum(CASE WHEN (t.ident~'tgr7_1') or (adr.is_town = 1 and t.ident~'tgr7_6') THEN b_val END),
     sum(CASE WHEN adr.is_town = 0 and t.ident~'tgr7_3' THEN b_val END),
     sum(CASE WHEN  t.ident~'tgr7_52' THEN b_val END),
     sum(CASE WHEN (t.ident~'tgr7_2') or (adr.is_town = 0 and t.ident~'tgr7_6') THEN b_val END)
    ,1,1,vmmgg
     from 
     clm_paccnt_tmp as a
     join aqi_grptar_tbl as t on (t.id = a.id_gtar)
     join adt_addr_tbl as adr on (adr.id = (a.addr).id_class)
     join (
       select id_paccnt, hmmgg, sum(b_dtval) as b_val  from seb_saldo_tmp where mmgg = vmmgg and b_dtval<>0 group by id_paccnt,hmmgg order by id_paccnt
     ) as s on (s.id_paccnt = a.id) 
    group by hmmgg order by hmmgg;


    insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
    select vdepartment,'saldo_b_kt','Кредиторська заборг.    Всього','2_2','грн.',
    sum(CASE WHEN adr.is_town = 1 and t.ident~'tgr7_3' THEN -b_val END),
    sum(CASE WHEN  ((t.ident~'tgr7_51') or (t.ident~'tgr7_53')) THEN -b_val END),
    sum(CASE WHEN (t.ident~'tgr7_1') or (adr.is_town = 1 and t.ident~'tgr7_6')  THEN -b_val END),
    sum(CASE WHEN adr.is_town = 0 and t.ident~'tgr7_3' THEN -b_val END),
    sum(CASE WHEN  t.ident~'tgr7_52' THEN -b_val END),
    sum(CASE WHEN (t.ident~'tgr7_2') or (adr.is_town = 0 and t.ident~'tgr7_6')  THEN -b_val END)
    ,2,1,vmmgg
     from 
     clm_paccnt_tmp as a
     join aqi_grptar_tbl as t on (t.id = a.id_gtar)
     join adt_addr_tbl as adr on (adr.id = (a.addr).id_class)
     join (
       select id_paccnt, -sum(b_ktval) as b_val  from seb_saldo_tmp where mmgg = vmmgg group by id_paccnt order by id_paccnt
     ) as s on (s.id_paccnt = a.id) ;
  else

     raise notice 'Saldo start calc rep_zvit_abon_tbl';

     insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
     select vdepartment,'saldo_b_dt','Дебиторська заборг.     Всього','2_1','грн.',

     sum(CASE WHEN coalesce(za.zcolumn,6) =1 THEN b_val END),
     sum(CASE WHEN coalesce(za.zcolumn,6) =2 THEN b_val END),
     sum(CASE WHEN coalesce(za.zcolumn,6) =3 THEN b_val END),
     sum(CASE WHEN coalesce(za.zcolumn,6) =4 THEN b_val END),
     sum(CASE WHEN coalesce(za.zcolumn,6) =5 THEN b_val END),
     sum(CASE WHEN coalesce(za.zcolumn,6) =6 THEN b_val END)
    ,2,1,vmmgg
     from 
     clm_paccnt_tmp as a
--     join aqi_grptar_tbl as t on (t.id = a.id_gtar)
--     join adt_addr_tbl as adr on (adr.id = (a.addr).id_class)
     join (
       select id_paccnt, sum(b_dtval) as b_val  from seb_saldo_tmp where mmgg = vmmgg group by id_paccnt order by id_paccnt
     ) as s on (s.id_paccnt = a.id) 
     left join rep_zvit_abon_tbl as za on (za.id_paccnt = a.id and za.mmgg = (vmmgg::date-'1 month'::interval)::date and za.id_dep = vdepartment);


  -- по годам
     insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                             village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)

     select vdepartment,'saldo_b_dt_'||date_part('year',hmmgg)::varchar,'- - дебет '||date_part('year',hmmgg)::varchar||' року','2_10'||date_part('year',hmmgg)::varchar,'грн.',
     sum(CASE WHEN coalesce(za.zcolumn,6) =1 THEN b_val END),
     sum(CASE WHEN coalesce(za.zcolumn,6) =2 THEN b_val END),
     sum(CASE WHEN coalesce(za.zcolumn,6) =3 THEN b_val END),
     sum(CASE WHEN coalesce(za.zcolumn,6) =4 THEN b_val END),
     sum(CASE WHEN coalesce(za.zcolumn,6) =5 THEN b_val END),
     sum(CASE WHEN coalesce(za.zcolumn,6) =6 THEN b_val END)
    ,1,1,vmmgg
     from 
     clm_paccnt_tmp as a
--     join aqi_grptar_tbl as t on (t.id = a.id_gtar)
--     join adt_addr_tbl as adr on (adr.id = (a.addr).id_class)
     join (
       select id_paccnt, hmmgg, sum(b_dtval) as b_val  from seb_saldo_tmp where mmgg = vmmgg and b_dtval<>0 group by id_paccnt,hmmgg order by id_paccnt
     ) as s on (s.id_paccnt = a.id) 
     left join rep_zvit_abon_tbl as za on (za.id_paccnt = a.id and za.mmgg = (vmmgg::date-'1 month'::interval)::date and za.id_dep = vdepartment)
    group by hmmgg order by hmmgg;


    insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
    select vdepartment,'saldo_b_kt','Кредиторська заборг.    Всього','2_2','грн.',
     sum(CASE WHEN coalesce(za.zcolumn,6) =1 THEN -b_val END),
     sum(CASE WHEN coalesce(za.zcolumn,6) =2 THEN -b_val END),
     sum(CASE WHEN coalesce(za.zcolumn,6) =3 THEN -b_val END),
     sum(CASE WHEN coalesce(za.zcolumn,6) =4 THEN -b_val END),
     sum(CASE WHEN coalesce(za.zcolumn,6) =5 THEN -b_val END),
     sum(CASE WHEN coalesce(za.zcolumn,6) =6 THEN -b_val END)
    ,2,1,vmmgg
     from 
     clm_paccnt_tmp as a
--     join aqi_grptar_tbl as t on (t.id = a.id_gtar)
--     join adt_addr_tbl as adr on (adr.id = (a.addr).id_class)
     join (
       select id_paccnt, -sum(b_ktval) as b_val  from seb_saldo_tmp where mmgg = vmmgg group by id_paccnt order by id_paccnt
     ) as s on (s.id_paccnt = a.id) 
     left join rep_zvit_abon_tbl as za on (za.id_paccnt = a.id and za.mmgg = (vmmgg::date-'1 month'::interval)::date and za.id_dep = vdepartment);
  end if;

 else

   raise notice 'Saldo start copy..';

   insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
   select vdepartment,'saldo_b_dt','Дебиторська заборг.     Всього','2_1','грн.',
   z.town_stove,z.town_heat, z.town_other,  z.village_stove, z.village_heat, z.village_other
   ,2,1,vmmgg
   from rep_zvit_tbl as z where z.id_dep = vdepartment and z.mmgg = (vmmgg-'1 month'::interval)::date and z.ident ='saldo_e_dt';


  -- по годам
   insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                             village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)

   select vdepartment, replace(ident,'saldo_e_','saldo_b_'),caption, replace(num,'b_10','2_10'),'грн.',
   z.town_stove,z.town_heat, z.town_other,  z.village_stove, z.village_heat, z.village_other
   ,1,1,vmmgg
   from rep_zvit_tbl as z where z.id_dep = vdepartment and z.mmgg = (vmmgg-'1 month'::interval)::date and z.ident ~'saldo_e_dt_20';


  insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
  select vdepartment,'saldo_b_kt','Кредиторська заборг.    Всього','2_2','грн.',
   z.town_stove,z.town_heat, z.town_other,  z.village_stove, z.village_heat, z.village_other
   ,2,1,vmmgg
   from rep_zvit_tbl as z where z.id_dep = vdepartment and  z.mmgg = (vmmgg-'1 month'::interval)::date and z.ident ='saldo_e_kt';

  ---
  if kodres in ( 200,202, 260) and vcur_mmgg = '2017-09-01' then

     -- Отдельно выбор сальдо по Соснице, присоединенной к Мене
     insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
     select vdepartment,'saldo_b_dt_s','Дебиторська заборг Сосниця.     Всього','2_101','грн.',
     sum(CASE WHEN adr.is_town = 1 and t.ident~'tgr7_3' THEN b_val END),
     sum(CASE WHEN  ((t.ident~'tgr7_51') or (t.ident~'tgr7_53')) THEN b_val END),
     sum(CASE WHEN (t.ident~'tgr7_1') or (adr.is_town = 1 and t.ident~'tgr7_6') THEN b_val END),
     sum(CASE WHEN adr.is_town = 0 and t.ident~'tgr7_3' THEN b_val END),
     sum(CASE WHEN  t.ident~'tgr7_52' THEN b_val END),
     sum(CASE WHEN (t.ident~'tgr7_2') or (adr.is_town = 0 and t.ident~'tgr7_6') THEN b_val END)
    ,2,1,vmmgg
     from 
     clm_paccnt_tmp as a
     join aqi_grptar_tbl as t on (t.id = a.id_gtar)
     join adt_addr_tbl as adr on (adr.id = (a.addr).id_class)
     join (
       select id_paccnt, sum(b_dtval) as b_val  from seb_saldo_tmp where mmgg = vmmgg group by id_paccnt order by id_paccnt
     ) as s on (s.id_paccnt = a.id)
     where a.id_dep=260 ;

    -- по годам
     insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                             village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)

     select vdepartment,'saldo_b_dt_s'||date_part('year',hmmgg)::varchar,'- - дебет '||date_part('year',hmmgg)::varchar||' року','2_101'||date_part('year',hmmgg)::varchar,'грн.',
     sum(CASE WHEN adr.is_town = 1 and t.ident~'tgr7_3' THEN b_val END),
     sum(CASE WHEN  ((t.ident~'tgr7_51') or (t.ident~'tgr7_53')) THEN b_val END),
     sum(CASE WHEN (t.ident~'tgr7_1') or (adr.is_town = 1 and t.ident~'tgr7_6') THEN b_val END),
     sum(CASE WHEN adr.is_town = 0 and t.ident~'tgr7_3' THEN b_val END),
     sum(CASE WHEN  t.ident~'tgr7_52' THEN b_val END),
     sum(CASE WHEN (t.ident~'tgr7_2') or (adr.is_town = 0 and t.ident~'tgr7_6') THEN b_val END)
    ,1,1,vmmgg
     from 
     clm_paccnt_tmp as a
     join aqi_grptar_tbl as t on (t.id = a.id_gtar)
     join adt_addr_tbl as adr on (adr.id = (a.addr).id_class)
     join (
       select id_paccnt, hmmgg, sum(b_dtval) as b_val  from seb_saldo_tmp where mmgg = vmmgg and b_dtval<>0 group by id_paccnt,hmmgg order by id_paccnt
     ) as s on (s.id_paccnt = a.id) 
     where a.id_dep=260 
    group by hmmgg order by hmmgg;


    insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
    select vdepartment,'saldo_b_kt_s','Кредиторська заборг. Сосниця   Всього','2_201','грн.',
    sum(CASE WHEN adr.is_town = 1 and t.ident~'tgr7_3' THEN -b_val END),
    sum(CASE WHEN  ((t.ident~'tgr7_51') or (t.ident~'tgr7_53')) THEN -b_val END),
    sum(CASE WHEN (t.ident~'tgr7_1') or (adr.is_town = 1 and t.ident~'tgr7_6')  THEN -b_val END),
    sum(CASE WHEN adr.is_town = 0 and t.ident~'tgr7_3' THEN -b_val END),
    sum(CASE WHEN  t.ident~'tgr7_52' THEN -b_val END),
    sum(CASE WHEN (t.ident~'tgr7_2') or (adr.is_town = 0 and t.ident~'tgr7_6')  THEN -b_val END)
    ,2,1,vmmgg
     from 
     clm_paccnt_tmp as a
     join aqi_grptar_tbl as t on (t.id = a.id_gtar)
     join adt_addr_tbl as adr on (adr.id = (a.addr).id_class)
     join (
       select id_paccnt, -sum(b_ktval) as b_val  from seb_saldo_tmp where mmgg = vmmgg group by id_paccnt order by id_paccnt
     ) as s on (s.id_paccnt = a.id) 
     where a.id_dep=260 ;

     
    update rep_zvit_tmp
               set town_stove = coalesce(rep_zvit_tmp.town_stove,0) + coalesce(ss.town_stove,0),
	       town_heat  = coalesce(rep_zvit_tmp.town_heat,0)  + coalesce(ss.town_heat,0),
	       town_other = coalesce(rep_zvit_tmp.town_other,0) + coalesce(ss.town_other,0),
               village_stove = coalesce(rep_zvit_tmp.village_stove,0) + coalesce(ss.village_stove,0),
	       village_heat  = coalesce(rep_zvit_tmp.village_heat,0)  + coalesce(ss.village_heat,0),
	       village_other = coalesce(rep_zvit_tmp.village_other,0) + coalesce(ss.village_other,0)
    from (select * from rep_zvit_tmp where mmgg=vmmgg and ident = 'saldo_b_dt_s') as ss
    where rep_zvit_tmp.mmgg=vmmgg and rep_zvit_tmp.ident = 'saldo_b_dt';

    update rep_zvit_tmp
               set town_stove = coalesce(rep_zvit_tmp.town_stove,0) + coalesce(ss.town_stove,0),
	       town_heat  = coalesce(rep_zvit_tmp.town_heat,0)  + coalesce(ss.town_heat,0),
	       town_other = coalesce(rep_zvit_tmp.town_other,0) + coalesce(ss.town_other,0),
               village_stove = coalesce(rep_zvit_tmp.village_stove,0) + coalesce(ss.village_stove,0),
	       village_heat  = coalesce(rep_zvit_tmp.village_heat,0)  + coalesce(ss.village_heat,0),
	       village_other = coalesce(rep_zvit_tmp.village_other,0) + coalesce(ss.village_other,0)
    from (select * from rep_zvit_tmp where mmgg=vmmgg and ident = 'saldo_b_kt_s') as ss
    where rep_zvit_tmp.mmgg=vmmgg and rep_zvit_tmp.ident = 'saldo_b_kt';

    update rep_zvit_tmp
               set town_stove = coalesce(rep_zvit_tmp.town_stove,0) + coalesce(ss.town_stove,0),
	       town_heat  = coalesce(rep_zvit_tmp.town_heat,0)  + coalesce(ss.town_heat,0),
	       town_other = coalesce(rep_zvit_tmp.town_other,0) + coalesce(ss.town_other,0),
               village_stove = coalesce(rep_zvit_tmp.village_stove,0) + coalesce(ss.village_stove,0),
	       village_heat  = coalesce(rep_zvit_tmp.village_heat,0)  + coalesce(ss.village_heat,0),
	       village_other = coalesce(rep_zvit_tmp.village_other,0) + coalesce(ss.village_other,0)
    from (select * from rep_zvit_tmp where mmgg=vmmgg and ident = 'saldo_b_dt_s2017') as ss
    where rep_zvit_tmp.mmgg=vmmgg and rep_zvit_tmp.ident = 'saldo_b_dt_2017';

    update rep_zvit_tmp set ident = 'saldo_b_dt_2016' , num = '2_102016'
    where ident = 'saldo_b_dt_s2016' and mmgg=vmmgg;

    delete from rep_zvit_tmp where ident = 'saldo_b_dt_s2017' and mmgg=vmmgg;
    delete from rep_zvit_tmp where ident = 'saldo_b_dt_s' and mmgg=vmmgg;
    delete from rep_zvit_tmp where ident = 'saldo_b_kt_s' and mmgg=vmmgg;

  end if;

 end if;

 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment,'saldo_b_dt_e','              втч електроенергiя ','2_11','грн.', 
                           sum(town_stove)-round(sum(town_stove)/6,2),
			   sum(town_heat)-round(sum(town_heat)/6,2),
			   sum(town_other)-round(sum(town_other)/6,2),
                           sum(village_stove)-round(sum(village_stove)/6,2),
			   sum(village_heat)-round(sum(village_heat)/6,2),
			   sum(village_other)-round(sum(village_other)/6,2),  1, 1, vmmgg
 from rep_zvit_tmp where mmgg = vmmgg and ident ='saldo_b_dt';


 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment,'saldo_b_dt_tax','              втч ПДВ','2_12','грн.', 
                           round(sum(town_stove)/6,2),
			   round(sum(town_heat)/6,2),
			   round(sum(town_other)/6,2),
                           round(sum(village_stove)/6,2),
			   round(sum(village_heat)/6,2),
			   round(sum(village_other)/6,2),  1, 1, vmmgg
 from rep_zvit_tmp where mmgg = vmmgg and ident ='saldo_b_dt';


 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment,'saldo_b_kt_e','              втч електроенергiя ','2_21','грн.', 
                           sum(town_stove)-round(sum(town_stove)/6,2),
			   sum(town_heat)-round(sum(town_heat)/6,2),
			   sum(town_other)-round(sum(town_other)/6,2),
                           sum(village_stove)-round(sum(village_stove)/6,2),
			   sum(village_heat)-round(sum(village_heat)/6,2),
			   sum(village_other)-round(sum(village_other)/6,2),  1, 1, vmmgg
 from rep_zvit_tmp where mmgg = vmmgg and ident ='saldo_b_kt';


 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment,'saldo_b_kt_tax','              втч ПДВ','2_22','грн.', 
                           round(sum(town_stove)/6,2),
			   round(sum(town_heat)/6,2),
			   round(sum(town_other)/6,2),
                           round(sum(village_stove)/6,2),
			   round(sum(village_heat)/6,2),
			   round(sum(village_other)/6,2),  1, 1, vmmgg
 from rep_zvit_tmp where mmgg = vmmgg and ident ='saldo_b_kt';



-- сальдо на конец
 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, gr_lvl, part_code, mmgg)
 values (vdepartment,'saldo_e', 'Заборгованiсть абонентiв на кiнець  перiоду','b_0','',0,1,vmmgg);


 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment,'saldo_e_dt','Дебиторська заборг.     Всього','b_1','грн.',
 sum(CASE WHEN adr.is_town = 1 and t.ident~'tgr7_3' THEN e_val END),
 sum(CASE WHEN  ((t.ident~'tgr7_51') or (t.ident~'tgr7_53')) THEN e_val END),
 sum(CASE WHEN (t.ident~'tgr7_1') or (adr.is_town = 1 and t.ident~'tgr7_6')  THEN e_val END),
 sum(CASE WHEN adr.is_town = 0 and t.ident~'tgr7_3' THEN e_val END),
 sum(CASE WHEN  t.ident~'tgr7_52' THEN e_val END),
 sum(CASE WHEN (t.ident~'tgr7_2') or (adr.is_town = 0 and t.ident~'tgr7_6')  THEN e_val END)
,2,1,vmmgg
 from 
 clm_paccnt_tmp as a
-- join (select id, max(dt_b) as dt from clm_paccnt_h  where dt_b <= vmmgg and coalesce(dt_e,vmmgg) >= vmmgg group by id order by id) as a2 on (a.id = a2.id and a2.dt = a.dt_b)
-- join (select id, max(dt_b) as dt from clm_paccnt_h  where 
--    ((dt_b < (vmmgg::date+'1 month'::interval) and dt_e is null)
--    or 
--    tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval(vmmgg::timestamp::abstime,(vmmgg::date+'1 month - 1 day'::interval)::timestamp::abstime)))
-- group by id order by id) as a2 on (a.id = a2.id and a2.dt = a.dt_b)

 join aqi_grptar_tbl as t on (t.id = a.id_gtar)
 join adt_addr_tbl as adr on (adr.id = (a.addr).id_class)
-- join acm_saldo_tbl as s on (s.id_paccnt = a.id) 
 join (
   select id_paccnt, sum(e_dtval) as e_val  from seb_saldo_tmp where mmgg = vmmgg group by id_paccnt order by id_paccnt
 ) as s on (s.id_paccnt = a.id) 
;
-- where s.id_pref = 10 and s.mmgg = vmmgg;
-- and s.e_val > 0 ;


-- по годам
 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)

 select vdepartment,'saldo_e_dt_'||date_part('year',hmmgg)::varchar,'- - дебет '||date_part('year',hmmgg)::varchar||' року','b_10'||date_part('year',hmmgg)::varchar,'грн.',
 sum(CASE WHEN adr.is_town = 1 and t.ident~'tgr7_3' THEN e_val END),
 sum(CASE WHEN  ((t.ident~'tgr7_51') or (t.ident~'tgr7_53')) THEN e_val END),
 sum(CASE WHEN (t.ident~'tgr7_1') or (adr.is_town = 1 and t.ident~'tgr7_6') THEN e_val END),
 sum(CASE WHEN adr.is_town = 0 and t.ident~'tgr7_3' THEN e_val END),
 sum(CASE WHEN  t.ident~'tgr7_52' THEN e_val END),
 sum(CASE WHEN (t.ident~'tgr7_2') or (adr.is_town = 0 and t.ident~'tgr7_6') THEN e_val END)
,1,1,vmmgg
 from 
 clm_paccnt_tmp as a
 join aqi_grptar_tbl as t on (t.id = a.id_gtar)
 join adt_addr_tbl as adr on (adr.id = (a.addr).id_class)
 join (
   select id_paccnt, hmmgg, sum(e_dtval) as e_val  from seb_saldo_tmp where mmgg = vmmgg and e_dtval<>0 group by id_paccnt,hmmgg order by id_paccnt
 ) as s on (s.id_paccnt = a.id) 
group by hmmgg order by hmmgg;


 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment,'saldo_e_dt_e','              втч електроенергiя ','b_11','грн.', 
                           sum(town_stove)-round(sum(town_stove)/6,2),
			   sum(town_heat)-round(sum(town_heat)/6,2),
			   sum(town_other)-round(sum(town_other)/6,2),
                           sum(village_stove)-round(sum(village_stove)/6,2),
			   sum(village_heat)-round(sum(village_heat)/6,2),
			   sum(village_other)-round(sum(village_other)/6,2),  1, 1, vmmgg
 from rep_zvit_tmp where mmgg = vmmgg and ident ='saldo_e_dt';


 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment,'saldo_e_dt_tax','              втч ПДВ','b_12','грн.', 
                           round(sum(town_stove)/6,2),
			   round(sum(town_heat)/6,2),
			   round(sum(town_other)/6,2),
                           round(sum(village_stove)/6,2),
			   round(sum(village_heat)/6,2),
			   round(sum(village_other)/6,2),  1, 1, vmmgg
 from rep_zvit_tmp where mmgg = vmmgg and ident ='saldo_e_dt';



 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment,'saldo_e_kt','Кредиторська заборг.    Всього','b_2','грн.',
 sum(CASE WHEN adr.is_town = 1 and t.ident~'tgr7_3' THEN -e_val END),
 sum(CASE WHEN  ((t.ident~'tgr7_51') or (t.ident~'tgr7_53')) THEN -e_val END),
 sum(CASE WHEN (t.ident~'tgr7_1') or (adr.is_town = 1 and t.ident~'tgr7_6')  THEN -e_val END),
 sum(CASE WHEN adr.is_town = 0 and t.ident~'tgr7_3' THEN -e_val END),
 sum(CASE WHEN  t.ident~'tgr7_52' THEN -e_val END),
 sum(CASE WHEN (t.ident~'tgr7_2') or (adr.is_town = 0 and t.ident~'tgr7_6')  THEN -e_val END)
,2,1,vmmgg
 from 
 clm_paccnt_tmp as a
-- join (select id, max(dt_b) as dt from clm_paccnt_h  where dt_b <= vmmgg and coalesce(dt_e,vmmgg) >= vmmgg group by id order by id) as a2 on (a.id = a2.id and a2.dt = a.dt_b)
-- join (select id, max(dt_b) as dt from clm_paccnt_h  where 
--    ((dt_b < (vmmgg::date+'1 month'::interval) and dt_e is null)
--    or 
--    tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval(vmmgg::timestamp::abstime,(vmmgg::date+'1 month - 1 day'::interval)::timestamp::abstime)))
-- group by id order by id) as a2 on (a.id = a2.id and a2.dt = a.dt_b)

 join aqi_grptar_tbl as t on (t.id = a.id_gtar)
 join adt_addr_tbl as adr on (adr.id = (a.addr).id_class)
-- join acm_saldo_tbl as s on (s.id_paccnt = a.id) 
 join (
   select id_paccnt, -sum(e_ktval) as e_val  from seb_saldo_tmp where mmgg = vmmgg group by id_paccnt order by id_paccnt
 ) as s on (s.id_paccnt = a.id) 
;
-- where s.id_pref = 10 and s.mmgg = vmmgg;
-- and s.e_val < 0 ;


 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment,'saldo_e_kt_e','              втч електроенергiя ','b_21','грн.', 
                           sum(town_stove)-round(sum(town_stove)/6,2),
			   sum(town_heat)-round(sum(town_heat)/6,2),
			   sum(town_other)-round(sum(town_other)/6,2),
                           sum(village_stove)-round(sum(village_stove)/6,2),
			   sum(village_heat)-round(sum(village_heat)/6,2),
			   sum(village_other)-round(sum(village_other)/6,2),  1, 1, vmmgg
 from rep_zvit_tmp where mmgg = vmmgg and ident ='saldo_e_kt';


 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment,'saldo_e_kt_tax','              втч ПДВ','b_22','грн.', 
                           round(sum(town_stove)/6,2),
			   round(sum(town_heat)/6,2),
			   round(sum(town_other)/6,2),
                           round(sum(village_stove)/6,2),
			   round(sum(village_heat)/6,2),
			   round(sum(village_other)/6,2),  1, 1, vmmgg
 from rep_zvit_tmp where mmgg = vmmgg and ident ='saldo_e_kt';


--------------------------------------------------------------
--- льготы ---
 --сумма по шкале

 raise notice 'lgt_sum';
 raise notice '19';
/*
 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, gr_lvl, part_code, mmgg)
 values (vdepartment,'lgt_sum_c', 'З 1 березня 2017 року','8_30','',1,1,vmmgg);
*/

 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
select vdepartment, 'lgt_sum'||text(p2.ident)||text(p2.num),'В т.ч. сума пiльг '||p2.caption,'8_31'||text(p2.num),'грн.',
town_stove,town_heat, town_other, village_stove, village_heat, village_other,1,1,vmmgg
from 
rep_zvit_pattern_tbl as p2 left join 
(
 select p.id as id_grp,
 sum( CASE WHEN z.is_town = 1 and t.ident~'tgr7_3' THEN summ_lgt END) as town_stove,
 sum( CASE WHEN  ((t.ident~'tgr7_51') or (t.ident~'tgr7_53')) THEN summ_lgt END) as town_heat,
 sum( CASE WHEN (t.ident~'tgr7_1')  THEN summ_lgt END) as town_other,
 sum( CASE WHEN z.is_town = 0 and t.ident~'tgr7_3' THEN summ_lgt END) as village_stove,
 sum( CASE WHEN  t.ident~'tgr7_52' THEN summ_lgt END) as village_heat,
 sum( CASE WHEN (t.ident~'tgr7_2')  THEN summ_lgt END) as village_other
 from 

 rep_zvit_lgt_tbl as z 
  join aqi_grptar_tbl as t on (t.id = z.id_grptar) 
  join aqm_tarif_tbl as tm on (tm.id = z.id_tarif)
  join rep_zvit_pattern_tbl as p on (p.ident = '0' and p.lim_min = coalesce(tm.lim_min,0) and p.lim_max = coalesce(tm.lim_max,0))
  where tm.ident !~'tgr7_6'
        and tm.ident not in ('tgr7_15','tgr7_25','tgr7_35','tgr7_53','tgr7_63')   
  group by p.id

) as ss3 on (p2.id = ss3.id_grp)
where p2.ident = '0'
order by p2.num;

-- многодетным 
 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment, 'lgt_sum_0m','В т.ч. сума пiльг багатодiтним','8_32','грн.',
 sum(CASE WHEN z.is_town = 1 and t.ident~'tgr7_3' THEN summ_lgt END),
 sum(CASE WHEN  ((t.ident~'tgr7_51') or (t.ident~'tgr7_53')) THEN summ_lgt END),
 sum(CASE WHEN (t.ident~'tgr7_1')  or (z.is_town = 1 and t.ident~'tgr7_6') THEN summ_lgt END),
 sum(CASE WHEN z.is_town = 0 and t.ident~'tgr7_3' THEN summ_lgt END),
 sum(CASE WHEN  t.ident~'tgr7_52' THEN summ_lgt END),
 sum(CASE WHEN (t.ident~'tgr7_2') or (z.is_town = 0 and t.ident~'tgr7_6') THEN summ_lgt END)
,1,1,vmmgg
 from 
 rep_zvit_lgt_tbl as z 
  join aqi_grptar_tbl as t on (t.id = z.id_grptar) 
  join aqm_tarif_tbl as tm on (tm.id = z.id_tarif)
where  tm.ident in ('tgr7_15','tgr7_25','tgr7_35','tgr7_53','tgr7_63') ;


-- не газифицированные
 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
select vdepartment, 'lgt_sum_ng'||text(p2.ident)||text(p2.num),'В т.ч. не газиф '||p2.caption,'8_33'||text(p2.num),'грн.',

0,0,town_heat, 0,0, village_heat ,1,1,vmmgg
from 
rep_zvit_pattern_tbl as p2 left join 
(
 select p.id as id_grp,
 sum( CASE WHEN z.is_town = 1 THEN summ_lgt END) as town_heat,
 sum( CASE WHEN z.is_town = 0 THEN summ_lgt END) as village_heat
 from 

 rep_zvit_lgt_tbl as z 
  join aqi_grptar_tbl as t on (t.id = z.id_grptar) 
  join aqm_tarif_tbl as tm on (tm.id = z.id_tarif)
  join rep_zvit_pattern_tbl as p on (p.ident = '0' and p.lim_min = coalesce(tm.lim_min,0) and p.lim_max = coalesce(tm.lim_max,0))
  where tm.ident ~'tgr7_6' and tm.ident <>'tgr7_63'
  group by p.id
) as ss3 on (p2.id = ss3.id_grp)
where p2.ident = '0'
order by p2.num;


 raise notice '19-1';
/*
 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, gr_lvl, part_code, mmgg)
 values (vdepartment,'lgt_sum_c1', 'до 1 березня 2017 року','8_40','',1,1,vmmgg);
*/

 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
select vdepartment, 'lgt_sum'||text(p2.ident)||text(p2.num),'В т.ч. сума пiльг '||p2.caption,'8_41'||text(p2.num),'грн.',
town_stove,town_heat, town_other, village_stove, village_heat, village_other,1,1,vmmgg
from 
rep_zvit_pattern_tbl as p2 left join 
(
 select p.id as id_grp,
 sum( CASE WHEN z.is_town = 1 and t.ident~'tgr7_3' THEN summ_lgt END) as town_stove,
 sum( CASE WHEN  ((t.ident~'tgr7_51') or (t.ident~'tgr7_53')) THEN summ_lgt END) as town_heat,
 sum( CASE WHEN (t.ident~'tgr7_1')  THEN summ_lgt END) as town_other,
 sum( CASE WHEN z.is_town = 0 and t.ident~'tgr7_3' THEN summ_lgt END) as village_stove,
 sum( CASE WHEN  t.ident~'tgr7_52' THEN summ_lgt END) as village_heat,
 sum( CASE WHEN (t.ident~'tgr7_2')  THEN summ_lgt END) as village_other
 from 

 rep_zvit_lgt_oldt_tbl as z 
  join aqi_grptar_tbl as t on (t.id = z.id_grptar) 
  join aqm_tarif_tbl as tm on (tm.id = z.id_tarif)
  join rep_zvit_pattern_tbl as p on (p.ident = '1' and p.lim_min = coalesce(tm.lim_min,0) and p.lim_max = coalesce(tm.lim_max,0))
  where tm.ident !~'tgr7_6'
        and tm.ident not in ('tgr7_15','tgr7_25','tgr7_35','tgr7_53','tgr7_63')   
  group by p.id

) as ss3 on (p2.id = ss3.id_grp)
where p2.ident = '1'
order by p2.num;

-- многодетным 
 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment, 'lgt_sum_1m','В т.ч. сума пiльг багатодiтним','8_42','грн.',
 sum(CASE WHEN z.is_town = 1 and t.ident~'tgr7_3' THEN summ_lgt END),
 sum(CASE WHEN  ((t.ident~'tgr7_51') or (t.ident~'tgr7_53')) THEN summ_lgt END),
 sum(CASE WHEN (t.ident~'tgr7_1')  or (z.is_town = 1 and t.ident~'tgr7_6') THEN summ_lgt END),
 sum(CASE WHEN z.is_town = 0 and t.ident~'tgr7_3' THEN summ_lgt END),
 sum(CASE WHEN  t.ident~'tgr7_52' THEN summ_lgt END),
 sum(CASE WHEN (t.ident~'tgr7_2') or (z.is_town = 0 and t.ident~'tgr7_6') THEN summ_lgt END)
,1,1,vmmgg
 from 
 rep_zvit_lgt_oldt_tbl as z 
  join aqi_grptar_tbl as t on (t.id = z.id_grptar) 
  join aqm_tarif_tbl as tm on (tm.id = z.id_tarif)
where  tm.ident in ('tgr7_15','tgr7_25','tgr7_35','tgr7_53','tgr7_63') ;


-- не газифицированные
 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
select vdepartment, 'lgt_sum_ng'||text(p2.ident)||text(p2.num),'В т.ч. не газиф '||p2.caption,'8_43'||text(p2.num),'грн.',

0,0,town_heat, 0,0, village_heat ,1,1,vmmgg
from 
rep_zvit_pattern_tbl as p2 left join 
(
 select p.id as id_grp,
 sum( CASE WHEN z.is_town = 1 THEN summ_lgt END) as town_heat,
 sum( CASE WHEN z.is_town = 0 THEN summ_lgt END) as village_heat
 from 

 rep_zvit_lgt_oldt_tbl as z 
  join aqi_grptar_tbl as t on (t.id = z.id_grptar) 
  join aqm_tarif_tbl as tm on (tm.id = z.id_tarif)
  join rep_zvit_pattern_tbl as p on (p.ident = '1' and p.lim_min = coalesce(tm.lim_min,0) and p.lim_max = coalesce(tm.lim_max,0))
  where tm.ident ~'tgr7_6' and tm.ident <>'tgr7_63'
  group by p.id
) as ss3 on (p2.id = ss3.id_grp)
where p2.ident = '1'
order by p2.num;



 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment,'lgt_sum','Сума наданих пiльг       Всього','8_0','грн.', sum(town_stove),sum(town_heat), sum(town_other), 
                           sum(village_stove), sum(village_heat), sum(village_other), 2, 1, vmmgg
 from rep_zvit_tmp where mmgg = vmmgg and ident ~ 'lgt_sum';

 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment,'lgt_sum_e','              втч електроенергiя ','8_1','грн.', 
                           sum(town_stove)-round(sum(town_stove)/6,2),
			   sum(town_heat)-round(sum(town_heat)/6,2),
			   sum(town_other)-round(sum(town_other)/6,2),
                           sum(village_stove)-round(sum(village_stove)/6,2),
			   sum(village_heat)-round(sum(village_heat)/6,2),
			   sum(village_other)-round(sum(village_other)/6,2),  1, 1, vmmgg
 from rep_zvit_tmp where mmgg = vmmgg and ident ='lgt_sum';

 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment,'lgt_sum_tax','              втч ПДВ','8_2','грн.', 
                           round(sum(town_stove)/6,2),
			   round(sum(town_heat)/6,2),
			   round(sum(town_other)/6,2),
                           round(sum(village_stove)/6,2),
			   round(sum(village_heat)/6,2),
			   round(sum(village_other)/6,2),  1, 1, vmmgg
 from rep_zvit_tmp where mmgg = vmmgg and ident ='lgt_sum';




-- льгота персоналу подстанций 
 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment, 'lgt_sum_ps1','В т.ч. сума пiдст. до 3000 кВтг','8_7','грн.',
 sum(CASE WHEN z.is_town = 1 and t.ident~'tgr7_3' THEN summ_lgt END),
 sum(CASE WHEN  ((t.ident~'tgr7_51') or (t.ident~'tgr7_53')) THEN summ_lgt END),
 sum(CASE WHEN (t.ident~'tgr7_1')  THEN summ_lgt END),
 sum(CASE WHEN z.is_town = 0 and t.ident~'tgr7_3' THEN summ_lgt END),
 sum(CASE WHEN  t.ident~'tgr7_52' THEN summ_lgt END),
 sum(CASE WHEN (t.ident~'tgr7_2')  THEN summ_lgt END)
,1,1,vmmgg
 from 
 rep_zvit_lgt_chnoe_tbl as z 
  join aqi_grptar_tbl as t on (t.id = z.id_grptar) 
  join aqm_tarif_tbl as tm on (tm.id = z.id_tarif)
where  coalesce(tm.lim_min,0)=0;


 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment, 'lgt_sum_ps2','В т.ч. сума пiдст. бiльше 3000 кВтг','8_8','грн.',
 sum(CASE WHEN z.is_town = 1 and t.ident~'tgr7_3' THEN summ_lgt END),
 sum(CASE WHEN  ((t.ident~'tgr7_51') or (t.ident~'tgr7_53')) THEN summ_lgt END),
 sum(CASE WHEN (t.ident~'tgr7_1')  THEN summ_lgt END),
 sum(CASE WHEN z.is_town = 0 and t.ident~'tgr7_3' THEN summ_lgt END),
 sum(CASE WHEN  t.ident~'tgr7_52' THEN summ_lgt END),
 sum(CASE WHEN (t.ident~'tgr7_2')  THEN summ_lgt END)
,1,1,vmmgg
 from 
 rep_zvit_lgt_chnoe_tbl as z 
  join aqi_grptar_tbl as t on (t.id = z.id_grptar) 
  join aqm_tarif_tbl as tm on (tm.id = z.id_tarif)
where  coalesce(tm.lim_min,0)>=3000;


 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment,'lgt_sum_ps','Пiльга для персоналу подстанцiй','8_6','грн.', sum(town_stove),sum(town_heat), sum(town_other), 
                           sum(village_stove), sum(village_heat), sum(village_other), 2, 1, vmmgg
 from rep_zvit_tmp where mmgg = vmmgg and ident ~ 'lgt_sum_ps';

--------------------------------------------------------------

 -- доп льгота ---
 --сумма по шкале
/*
 raise notice '19-1';

 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
select vdepartment, 'dop_lgt_sum'||text(p2.num),'В т.ч. сума дод. пiльг '||p2.caption,'9_3'||text(p2.num),'грн.',
town_stove,town_heat, town_other, village_stove, village_heat, village_other,1,1,vmmgg
from 
rep_zvit_pattern_tbl as p2 left join 
(
 select p.id as id_grp,
 sum( CASE WHEN z.is_town = 1 and t.ident~'tgr7_3' THEN summ_lgt END) as town_stove,
 sum( CASE WHEN  ((t.ident~'tgr7_51') or (t.ident~'tgr7_53')) THEN summ_lgt END) as town_heat,
 sum( CASE WHEN (t.ident~'tgr7_1')  THEN summ_lgt END) as town_other,
 sum( CASE WHEN z.is_town = 0 and t.ident~'tgr7_3' THEN summ_lgt END) as village_stove,
 sum( CASE WHEN  t.ident~'tgr7_52' THEN summ_lgt END) as village_heat,
 sum( CASE WHEN (t.ident~'tgr7_2')  THEN summ_lgt END) as village_other
 from 

 rep_zvit_lgt_dod_tbl as z 
  join aqi_grptar_tbl as t on (t.id = z.id_grptar) 
  join aqm_tarif_tbl as tm on (tm.id = z.id_tarif)
  join rep_zvit_pattern_tbl as p on (p.lim_min = coalesce(tm.lim_min,0) and p.lim_max = coalesce(tm.lim_max,0))
  where tm.ident !~'tgr7_6'
        and tm.ident not in ('tgr7_15','tgr7_25','tgr7_35','tgr7_53','tgr7_63')   
  group by p.id

) as ss3 on (p2.id = ss3.id_grp)
order by p2.num;

-- многодетным 
 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment, 'dop_lgt_sum_m','В т.ч. сума дод.пiльг багатодiтним','9_4','грн.',
 sum(CASE WHEN z.is_town = 1 and t.ident~'tgr7_3' THEN summ_lgt END),
 sum(CASE WHEN  ((t.ident~'tgr7_51') or (t.ident~'tgr7_53')) THEN summ_lgt END),
 sum(CASE WHEN (t.ident~'tgr7_1')  THEN summ_lgt END),
 sum(CASE WHEN z.is_town = 0 and t.ident~'tgr7_3' THEN summ_lgt END),
 sum(CASE WHEN  t.ident~'tgr7_52' THEN summ_lgt END),
 sum(CASE WHEN (t.ident~'tgr7_2')  THEN summ_lgt END)
,1,1,vmmgg
 from 
 rep_zvit_lgt_dod_tbl as z 
  join aqi_grptar_tbl as t on (t.id = z.id_grptar) 
  join aqm_tarif_tbl as tm on (tm.id = z.id_tarif)
where  tm.ident in ('tgr7_15','tgr7_25','tgr7_35','tgr7_53','tgr7_63') ;


-- не газифицированные
 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
select vdepartment,'dop_lgt_sum_ng'||substr(p2.ident,7),'В т.ч. '||p2.short_name,'9_5'||substr(p2.ident,7),'грн.',
0,town_heat, 0, 0, village_heat, 0 ,1,1,vmmgg
from 
(select id, short_name, ident from aqm_tarif_tbl where ident~'tgr7_6' 
 and dt_b <= (vmmgg::date+'1 month'::interval)::date and (dt_e is null or dt_e >= vmmgg::date)
 order by ident ) as p2 left join 
(
 select tm.id as id_tar,
 sum( CASE WHEN z.is_town = 1 THEN summ_lgt END) as town_heat,
 sum( CASE WHEN z.is_town = 0 THEN summ_lgt END) as village_heat
 from 

 rep_zvit_lgt_dod_tbl as z 
  join aqm_tarif_tbl as tm on (tm.id = z.id_tarif)
  where tm.ident ~'tgr7_6'


group by tm.id
) as ss3 on (p2.id = ss3.id_tar)
order by p2.ident;


 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment,'dop_lgt_sum','-- Сума додаткових пiльг       Всього','9_0','грн.', sum(town_stove),sum(town_heat), sum(town_other), 
                           sum(village_stove), sum(village_heat), sum(village_other), 2, 1, vmmgg
 from rep_zvit_tmp where mmgg = vmmgg and ident ~ 'dop_lgt_sum';

 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment,'dop_lgt_sum_e','              втч електроенергiя ','9_1','грн.', 
                           sum(town_stove)-round(sum(town_stove)/6,2),
			   sum(town_heat)-round(sum(town_heat)/6,2),
			   sum(town_other)-round(sum(town_other)/6,2),
                           sum(village_stove)-round(sum(village_stove)/6,2),
			   sum(village_heat)-round(sum(village_heat)/6,2),
			   sum(village_other)-round(sum(village_other)/6,2),  1, 1, vmmgg
 from rep_zvit_tmp where mmgg = vmmgg and ident ='dop_lgt_sum';

 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment,'dop_lgt_sum_tax','              втч ПДВ','9_2','грн.', 
                           round(sum(town_stove)/6,2),
			   round(sum(town_heat)/6,2),
			   round(sum(town_other)/6,2),
                           round(sum(village_stove)/6,2),
			   round(sum(village_heat)/6,2),
			   round(sum(village_other)/6,2),  1, 1, vmmgg
 from rep_zvit_tmp where mmgg = vmmgg and ident ='dop_lgt_sum';
---------------------------------------------------------------------------------------------------
*/
 raise notice '20';
-- Субсидия не делится по тарифным группам и идет одной суммой 
-- как оплата, но для отчета ее надо искусственно рассовать по строкам

-- сумма субсидий всего
 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment,'subs_all','Сума наданих субсидiй    Всього','a_01','грн.',
 sum(CASE WHEN z.is_town = 1 and t.ident~'tgr7_3' THEN z.summ_subs+z.summ_resubs END),
 sum(CASE WHEN  ((t.ident~'tgr7_51') or (t.ident~'tgr7_53')) THEN z.summ_subs+z.summ_resubs END),
 sum(CASE WHEN (t.ident~'tgr7_1') or (z.is_town = 1 and t.ident~'tgr7_6') THEN z.summ_subs+z.summ_resubs END),
 sum(CASE WHEN z.is_town = 0 and t.ident~'tgr7_3' THEN z.summ_subs+z.summ_resubs END),
 sum(CASE WHEN  t.ident~'tgr7_52' THEN z.summ_subs+z.summ_resubs END),
 sum(CASE WHEN (t.ident~'tgr7_2') or (z.is_town = 0 and t.ident~'tgr7_6') THEN z.summ_subs+z.summ_resubs END)
,2,1,vmmgg
 from 
 rep_zvit_subs_tbl as z 
  join aqi_grptar_tbl as t on (t.id = z.id_grptar) ;



 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment,'subs_e','              втч електроенергiя ','a_02','грн.', 
                           sum(town_stove)-round(sum(town_stove)/6,2),
			   sum(town_heat)-round(sum(town_heat)/6,2),
			   sum(town_other)-round(sum(town_other)/6,2),
                           sum(village_stove)-round(sum(village_stove)/6,2),
			   sum(village_heat)-round(sum(village_heat)/6,2),
			   sum(village_other)-round(sum(village_other)/6,2),  1, 1, vmmgg
 from rep_zvit_tmp where mmgg = vmmgg and ident ='subs_all';

 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment,'subs_tax','              втч ПДВ','a_03','грн.', 
                           round(sum(town_stove)/6,2),
			   round(sum(town_heat)/6,2),
			   round(sum(town_other)/6,2),
                           round(sum(village_stove)/6,2),
			   round(sum(village_heat)/6,2),
			   round(sum(village_other)/6,2),  1, 1, vmmgg
 from rep_zvit_tmp where mmgg = vmmgg and ident ='subs_all';


 -- строки по уровням
 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
select vdepartment, 'subs_sum'||text(p2.ident)||text(p2.num),'В т.ч. сума субс.'||p2.caption,'a_1'||text(p2.num),'грн.',
town_stove,town_heat, town_other, village_stove, village_heat, village_other,1,1,vmmgg
from 
rep_zvit_pattern_tbl as p2 left join 
(
 select p.id as id_grp,
 sum( CASE WHEN z.is_town = 1 and t.ident~'tgr7_3' THEN z.summ_subs+z.summ_resubs END) as town_stove,
 sum( CASE WHEN  ((t.ident~'tgr7_51') or (t.ident~'tgr7_53')) THEN z.summ_subs+z.summ_resubs END) as town_heat,
 sum( CASE WHEN (t.ident~'tgr7_1')  THEN z.summ_subs+z.summ_resubs END) as town_other,
 sum( CASE WHEN z.is_town = 0 and t.ident~'tgr7_3' THEN z.summ_subs+z.summ_resubs END) as village_stove,
 sum( CASE WHEN  t.ident~'tgr7_52' THEN z.summ_subs+z.summ_resubs END) as village_heat,
 sum( CASE WHEN (t.ident~'tgr7_2')  THEN z.summ_subs+z.summ_resubs END) as village_other
 from 

 rep_zvit_subs_tbl as z 
  join aqi_grptar_tbl as t on (t.id = z.id_grptar) 
  join aqm_tarif_tbl as tm on (tm.id = z.id_tarif)
  join rep_zvit_pattern_tbl as p on (p.ident = '0' and p.lim_min = coalesce(tm.lim_min,0) and p.lim_max = coalesce(tm.lim_max,0))
  where tm.ident !~'tgr7_6'
        and tm.ident not in ('tgr7_15','tgr7_25','tgr7_35','tgr7_53','tgr7_63')   
   and not exists (select lh.id_paccnt from rep_zvit_lgt_chnoe_tbl as lh where lh.id_paccnt = z.id_paccnt)
  group by p.id
) as ss3 on (p2.id = ss3.id_grp)
where p2.ident = '0'
order by p2.num;

-- многодетным 

 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment,'subs_m','В т.ч. сума субс.багатодiтним','a_2','грн.',
 sum(CASE WHEN z.is_town = 1 and t.ident~'tgr7_3' THEN z.summ_subs+z.summ_resubs END),
 sum(CASE WHEN  ((t.ident~'tgr7_51') or (t.ident~'tgr7_53')) THEN z.summ_subs+z.summ_resubs END),
 sum(CASE WHEN (t.ident~'tgr7_1') or (z.is_town = 1 and t.ident~'tgr7_6')  THEN z.summ_subs+z.summ_resubs END),
 sum(CASE WHEN z.is_town = 0 and t.ident~'tgr7_3' THEN z.summ_subs+z.summ_resubs END),
 sum(CASE WHEN  t.ident~'tgr7_52' THEN z.summ_subs+z.summ_resubs END),
 sum(CASE WHEN (t.ident~'tgr7_2') or (z.is_town = 0 and t.ident~'tgr7_6')  THEN z.summ_subs+z.summ_resubs END)
,1,1,vmmgg
 from 
 rep_zvit_subs_tbl as z 
  join aqi_grptar_tbl as t on (t.id = z.id_grptar) 
  join aqm_tarif_tbl as tm on (tm.id = z.id_tarif)
where  tm.ident in ('tgr7_15','tgr7_25','tgr7_35','tgr7_53','tgr7_63') ;


-- не газифицированные
 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
select vdepartment, 'subs_sum_ng'||text(p2.ident)||text(p2.num),'В т.ч. не газиф '||p2.caption,'a_3'||text(p2.num),'грн.',

0,0,town_heat, 0, 0, village_heat ,1,1,vmmgg
from 
rep_zvit_pattern_tbl as p2 left join 
(
 select p.id as id_grp,
 sum( CASE WHEN z.is_town = 1 THEN z.summ_subs+z.summ_resubs END) as town_heat,
 sum( CASE WHEN z.is_town = 0 THEN z.summ_subs+z.summ_resubs END) as village_heat
 from 

 rep_zvit_subs_tbl as z 
  join aqi_grptar_tbl as t on (t.id = z.id_grptar) 
  join aqm_tarif_tbl as tm on (tm.id = z.id_tarif)
  join rep_zvit_pattern_tbl as p on (p.ident = '0' and p.lim_min = coalesce(tm.lim_min,0) and p.lim_max = coalesce(tm.lim_max,0))
  where tm.ident ~'tgr7_6' and tm.ident <>'tgr7_63'
  group by p.id
) as ss3 on (p2.id = ss3.id_grp)
where p2.ident = '0'
order by p2.num;



 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment,'subs_sum_ps1','В т.ч. перс.пiдст. до 3000 кВтг','a_4','грн.',0,
 sum( CASE WHEN z.is_town = 1 THEN z.summ_subs+z.summ_resubs END) as town_heat,0,0,
 sum( CASE WHEN z.is_town = 0 THEN z.summ_subs+z.summ_resubs END) as village_heat,0
,1,1,vmmgg
 from 
 rep_zvit_subs_tbl as z 
  join aqi_grptar_tbl as t on (t.id = z.id_grptar) 
  join aqm_tarif_tbl as tm on (tm.id = z.id_tarif)
 where exists (select lh.id_paccnt from rep_zvit_lgt_chnoe_tbl as lh where lh.id_paccnt = z.id_paccnt);


--только текущая субсидия 
 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment,'subs_current','  в т.ч. субсидiї','a_5','грн.',
 sum(CASE WHEN z.is_town = 1 and t.ident~'tgr7_3' THEN z.summ_subs END),
 sum(CASE WHEN  ((t.ident~'tgr7_51') or (t.ident~'tgr7_53')) THEN z.summ_subs END),
 sum(CASE WHEN (t.ident~'tgr7_1') or (z.is_town = 1 and t.ident~'tgr7_6')  THEN z.summ_subs END),
 sum(CASE WHEN z.is_town = 0 and t.ident~'tgr7_3' THEN z.summ_subs END),
 sum(CASE WHEN  t.ident~'tgr7_52' THEN z.summ_subs END),
 sum(CASE WHEN (t.ident~'tgr7_2') or (z.is_town = 0 and t.ident~'tgr7_6') THEN z.summ_subs END)
,1,1,vmmgg
 from 
 rep_zvit_subs_tbl as z 
  join aqi_grptar_tbl as t on (t.id = z.id_grptar) ;

--только перерасчет 
 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment,'subs_recalc','  в т.ч. доплати','a_6','грн.',

 sum(CASE WHEN z.is_town = 1 and t.ident~'tgr7_3' THEN z.summ_resubs END),
 sum(CASE WHEN  ((t.ident~'tgr7_51') or (t.ident~'tgr7_53')) THEN z.summ_resubs END),
 sum(CASE WHEN (t.ident~'tgr7_1') or (z.is_town = 1 and t.ident~'tgr7_6') THEN z.summ_resubs END),
 sum(CASE WHEN z.is_town = 0 and t.ident~'tgr7_3' THEN z.summ_resubs END),
 sum(CASE WHEN  t.ident~'tgr7_52' THEN z.summ_resubs END),
 sum(CASE WHEN (t.ident~'tgr7_2') or (z.is_town = 0 and t.ident~'tgr7_6') THEN z.summ_resubs END)
,1,1,vmmgg
 from 
 rep_zvit_subs_tbl as z 
  join aqi_grptar_tbl as t on (t.id = z.id_grptar) ;

-------------------------------------------------------------------
-- возврат субсидии 2016 год 


 raise notice '20-2';
-- Субсидия не делится по тарифным группам и идет одной суммой 
-- как оплата, но для отчета ее надо искусственно рассовать по строкам

-- сумма субсидий всего
 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment,'rsubs_all','Невикористана сума субсидiї','ar_01','грн.',
 sum(CASE WHEN z.is_town = 1 and t.ident~'tgr7_3' THEN z.summ_subs END),
 sum(CASE WHEN  ((t.ident~'tgr7_51') or (t.ident~'tgr7_53')) THEN z.summ_subs END),
 sum(CASE WHEN (t.ident~'tgr7_1') or (z.is_town = 1 and t.ident~'tgr7_6') THEN z.summ_subs END),
 sum(CASE WHEN z.is_town = 0 and t.ident~'tgr7_3' THEN z.summ_subs END),
 sum(CASE WHEN  t.ident~'tgr7_52' THEN z.summ_subs END),
 sum(CASE WHEN (t.ident~'tgr7_2') or (z.is_town = 0 and t.ident~'tgr7_6') THEN z.summ_subs END)
,2,1,vmmgg
 from 
 rep_zvit_subs_ret_tbl as z 
  join aqi_grptar_tbl as t on (t.id = z.id_grptar) ;


/*
 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment,'subs_e','              втч електроенергiя ','a_02','грн.', 
                           sum(town_stove)-round(sum(town_stove)/6,2),
			   sum(town_heat)-round(sum(town_heat)/6,2),
			   sum(town_other)-round(sum(town_other)/6,2),
                           sum(village_stove)-round(sum(village_stove)/6,2),
			   sum(village_heat)-round(sum(village_heat)/6,2),
			   sum(village_other)-round(sum(village_other)/6,2),  1, 1, vmmgg
 from rep_zvit_tmp where mmgg = vmmgg and ident ='subs_all';

 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment,'subs_tax','              втч ПДВ','a_03','грн.', 
                           round(sum(town_stove)/6,2),
			   round(sum(town_heat)/6,2),
			   round(sum(town_other)/6,2),
                           round(sum(village_stove)/6,2),
			   round(sum(village_heat)/6,2),
			   round(sum(village_other)/6,2),  1, 1, vmmgg
 from rep_zvit_tmp where mmgg = vmmgg and ident ='subs_all';
*/

 -- строки по уровням
 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
select vdepartment, 'rsubs_sum'||text(p2.num),'В т.ч. сума '||p2.caption,'ar_1'||text(p2.num),'грн.',
town_stove,town_heat, town_other, village_stove, village_heat, village_other,1,1,vmmgg
from 
rep_zvit_pattern_tbl as p2 left join 
(
 select p.id as id_grp,
 sum( CASE WHEN z.is_town = 1 and t.ident~'tgr7_3' THEN z.summ_subs END) as town_stove,
 sum( CASE WHEN  ((t.ident~'tgr7_51') or (t.ident~'tgr7_53')) THEN z.summ_subs END) as town_heat,
 sum( CASE WHEN (t.ident~'tgr7_1')  THEN z.summ_subs END) as town_other,
 sum( CASE WHEN z.is_town = 0 and t.ident~'tgr7_3' THEN z.summ_subs END) as village_stove,
 sum( CASE WHEN  t.ident~'tgr7_52' THEN z.summ_subs END) as village_heat,
 sum( CASE WHEN (t.ident~'tgr7_2')  THEN z.summ_subs END) as village_other
 from 

 rep_zvit_subs_ret_tbl as z 
  join aqi_grptar_tbl as t on (t.id = z.id_grptar) 
  join aqm_tarif_tbl as tm on (tm.id = z.id_tarif)
  join rep_zvit_pattern_tbl as p on (p.lim_min = coalesce(tm.lim_min,0) and p.lim_max = coalesce(tm.lim_max,0))
  where tm.ident !~'tgr7_6'
        and tm.ident not in ('tgr7_15','tgr7_25','tgr7_35','tgr7_53','tgr7_63')   
  group by p.id
) as ss3 on (p2.id = ss3.id_grp)
order by p2.num;

-- многодетным 

 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment,'rsubs_m','В т.ч. сума багатодiтним','ar_2','грн.',
 sum(CASE WHEN z.is_town = 1 and t.ident~'tgr7_3' THEN z.summ_subs END),
 sum(CASE WHEN  ((t.ident~'tgr7_51') or (t.ident~'tgr7_53')) THEN z.summ_subs END),
 sum(CASE WHEN (t.ident~'tgr7_1') or (z.is_town = 0 and t.ident~'tgr7_6')  THEN z.summ_subs END),
 sum(CASE WHEN z.is_town = 0 and t.ident~'tgr7_3' THEN z.summ_subs END),
 sum(CASE WHEN  t.ident~'tgr7_52' THEN z.summ_subs END),
 sum(CASE WHEN (t.ident~'tgr7_2') or (z.is_town = 0 and t.ident~'tgr7_6') THEN z.summ_subs END)
,1,1,vmmgg
 from 
 rep_zvit_subs_ret_tbl as z 
  join aqi_grptar_tbl as t on (t.id = z.id_grptar) 
  join aqm_tarif_tbl as tm on (tm.id = z.id_tarif)
where  tm.ident in ('tgr7_15','tgr7_25','tgr7_35','tgr7_53','tgr7_63') ;


-- не газифицированные
 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
select vdepartment, 'rsubs_sum_ng'||text(p2.num),'В т.ч. не газиф '||p2.caption,'ar_3'||text(p2.num),'грн.',

0,0,town_heat, 0, 0, village_heat ,1,1,vmmgg
from 
rep_zvit_pattern_tbl as p2 left join 
(
 select p.id as id_grp,
 sum( CASE WHEN z.is_town = 1 THEN z.summ_subs END) as town_heat,
 sum( CASE WHEN z.is_town = 0 THEN z.summ_subs END) as village_heat
 from 

  rep_zvit_subs_ret_tbl as z 
  join aqi_grptar_tbl as t on (t.id = z.id_grptar) 
  join aqm_tarif_tbl as tm on (tm.id = z.id_tarif)
  join rep_zvit_pattern_tbl as p on (p.lim_min = coalesce(tm.lim_min,0) and p.lim_max = coalesce(tm.lim_max,0))
  where tm.ident ~'tgr7_6' and tm.ident <>'tgr7_63'
  group by p.id
) as ss3 on (p2.id = ss3.id_grp)
order by p2.num;


----------------- оплата -----------------------------

 raise notice '61';

 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment,'pay_count','Оплочено рахункiв','7_1','шт.',
 count( distinct CASE WHEN adr.is_town = 1 and t.ident~'tgr7_3' THEN a.id END),
 count( distinct CASE WHEN  ((t.ident~'tgr7_51') or (t.ident~'tgr7_53')) THEN a.id END),
 count( distinct CASE WHEN (t.ident~'tgr7_1')   or (adr.is_town = 1 and t.ident~'tgr7_6')  THEN a.id END),
 count( distinct CASE WHEN adr.is_town = 0 and t.ident~'tgr7_3' THEN a.id END),
 count( distinct CASE WHEN  t.ident~'tgr7_52' THEN a.id END),
 count( distinct CASE WHEN (t.ident~'tgr7_2')   or (adr.is_town = 0 and t.ident~'tgr7_6') THEN a.id END),
 2,1,vmmgg
 from 
 clm_paccnt_tmp as a
-- join (select id, max(dt_b) as dt from clm_paccnt_h  where archive =0 and 
--    ((dt_b < (vmmgg::date+'1 month'::interval) and dt_e is null)
--    or 
--    tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval(vmmgg::timestamp::abstime,(vmmgg::date+'1 month - 1 day'::interval)::timestamp::abstime)))
-- group by id order by id) as a2 on (a.id = a2.id and a2.dt = a.dt_b)
 join aqi_grptar_tbl as t on (t.id = a.id_gtar)
 join adt_addr_tbl as adr on (adr.id = (a.addr).id_class)
 --join (select distinct id_paccnt from acm_pay_tbl where mmgg = vmmgg::date and id_pref = 10 and idk_doc not in ( 110, 193, 120, 1000) )  as p 
 join (select distinct id_paccnt from acm_pay_tbl where mmgg = vmmgg::date and id_pref = 10 and idk_doc = 100  )  as p 
  on (p.id_paccnt = a.id);
 --where archive =0;

 raise notice '62';


 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment,'pay_sum','              Всього','7_20','грн.',
 sum( CASE WHEN adr.is_town = 1 and t.ident~'tgr7_3' THEN p.sum_val END),
 sum( CASE WHEN  ((t.ident~'tgr7_51') or (t.ident~'tgr7_53')) THEN p.sum_val END),
 sum( CASE WHEN (t.ident~'tgr7_1')  or (adr.is_town = 1 and t.ident~'tgr7_6')  THEN p.sum_val END),
 sum( CASE WHEN adr.is_town = 0 and t.ident~'tgr7_3' THEN p.sum_val END),
 sum( CASE WHEN  t.ident~'tgr7_52' THEN p.sum_val END),
 sum( CASE WHEN (t.ident~'tgr7_2')  or (adr.is_town = 0 and t.ident~'tgr7_6')  THEN p.sum_val END),
 2,1,vmmgg
 from 
 clm_paccnt_tmp as a
-- join (select id, max(dt_b) as dt from clm_paccnt_h  where archive =0 and 
--    ((dt_b < (vmmgg::date+'1 month'::interval) and dt_e is null)
--    or 
--    tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval(vmmgg::timestamp::abstime,(vmmgg::date+'1 month - 1 day'::interval)::timestamp::abstime)))
-- group by id order by id) as a2 on (a.id = a2.id and a2.dt = a.dt_b)
 join aqi_grptar_tbl as t on (t.id = a.id_gtar)
 join adt_addr_tbl as adr on (adr.id = (a.addr).id_class)
 join (select id_paccnt, sum(value) as sum_val from acm_pay_tbl where mmgg = vmmgg::date and id_pref = 10
   --and idk_doc not in ( 110, 193, 120, 1000) 
   --and idk_doc =100 
   and idk_doc not in ( 110, 111, 193,194, 1000)
       group by id_paccnt order by id_paccnt ) as p   on (p.id_paccnt = a.id);
 --where a.archive =0;


 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment,'pay_sum_e','              втч електроенергiя ','7_21','грн.', 
                           sum(town_stove)-round(sum(town_stove)/6,2),
			   sum(town_heat)-round(sum(town_heat)/6,2),
			   sum(town_other)-round(sum(town_other)/6,2),
                           sum(village_stove)-round(sum(village_stove)/6,2),
			   sum(village_heat)-round(sum(village_heat)/6,2),
			   sum(village_other)-round(sum(village_other)/6,2),  1, 1, vmmgg
 from rep_zvit_tmp where mmgg = vmmgg and ident ='pay_sum';


 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment,'pay_sum_tax','              втч ПДВ','7_22','грн.', 
                           round(sum(town_stove)/6,2),
			   round(sum(town_heat)/6,2),
			   round(sum(town_other)/6,2),
                           round(sum(village_stove)/6,2),
			   round(sum(village_heat)/6,2),
			   round(sum(village_other)/6,2),  1, 1, vmmgg
 from rep_zvit_tmp where mmgg = vmmgg and ident ='pay_sum';


 raise notice '65';

 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment,'pay_count_cart',' В т.ч. оплата з карт рахунку','7_3','шт.',
 count( distinct CASE WHEN adr.is_town = 1 and t.ident~'tgr7_3' THEN a.id END),
 count( distinct CASE WHEN  ((t.ident~'tgr7_51') or (t.ident~'tgr7_53')) THEN a.id END),
 count( distinct CASE WHEN (t.ident~'tgr7_1')  or (adr.is_town = 1 and t.ident~'tgr7_6') THEN a.id END),
 count( distinct CASE WHEN adr.is_town = 0 and t.ident~'tgr7_3' THEN a.id END),
 count( distinct CASE WHEN  t.ident~'tgr7_52' THEN a.id END),
 count( distinct CASE WHEN (t.ident~'tgr7_2')  or (adr.is_town = 0 and t.ident~'tgr7_6') THEN a.id END),
 1,1,vmmgg
 from 
 clm_paccnt_tmp as a
-- join (select id, max(dt_b) as dt from clm_paccnt_h  where archive =0 and 
--    ((dt_b < (vmmgg::date+'1 month'::interval) and dt_e is null)
--    or 
--    tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval(vmmgg::timestamp::abstime,(vmmgg::date+'1 month - 1 day'::interval)::timestamp::abstime)))
-- group by id order by id) as a2 on (a.id = a2.id and a2.dt = a.dt_b)
 join aqi_grptar_tbl as t on (t.id = a.id_gtar)
 join adt_addr_tbl as adr on (adr.id = (a.addr).id_class)
 join (select distinct p.id_paccnt from acm_pay_tbl as p
       join acm_headpay_tbl as h on (h.id = p.id_headpay)
       join aci_pay_origin_tbl as po on (po.id = h.id_origin)
       where p.mmgg = vmmgg::date and p.id_pref = 10 
       --and p.idk_doc not in ( 110, 193, 120, 1000) 
       --and p.idk_doc=100
       and idk_doc not in ( 110, 111, 193,194)
       and po.id = 1001 )  as p 
  on (p.id_paccnt = a.id);
 --where archive =0 ;

 raise notice '67';

 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment,'pay_sum_cart','В т.ч. оплата з карт рахунку','7_4','грн.',
 sum( CASE WHEN adr.is_town = 1 and t.ident~'tgr7_3' THEN p.sum_val END),
 sum( CASE WHEN  ((t.ident~'tgr7_51') or (t.ident~'tgr7_53')) THEN p.sum_val END),
 sum( CASE WHEN (t.ident~'tgr7_1')  or (adr.is_town = 1 and t.ident~'tgr7_6')  THEN p.sum_val END),
 sum( CASE WHEN adr.is_town = 0 and t.ident~'tgr7_3' THEN p.sum_val END),
 sum( CASE WHEN  t.ident~'tgr7_52' THEN p.sum_val END),
 sum( CASE WHEN (t.ident~'tgr7_2')  or (adr.is_town = 0 and t.ident~'tgr7_6')  THEN p.sum_val END),
 1,1,vmmgg
 from 
 clm_paccnt_tmp as a
-- join (select id, max(dt_b) as dt from clm_paccnt_h  where archive =0 and 
--    ((dt_b < (vmmgg::date+'1 month'::interval) and dt_e is null)
--    or 
--    tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval(vmmgg::timestamp::abstime,(vmmgg::date+'1 month - 1 day'::interval)::timestamp::abstime)))
-- group by id order by id) as a2 on (a.id = a2.id and a2.dt = a.dt_b)
 join aqi_grptar_tbl as t on (t.id = a.id_gtar)
 join adt_addr_tbl as adr on (adr.id = (a.addr).id_class)
 join (select p.id_paccnt, sum(p.value) as sum_val from acm_pay_tbl as p 
       join acm_headpay_tbl as h on (h.id = p.id_headpay)
       join aci_pay_origin_tbl as po on (po.id = h.id_origin)
where p.mmgg = vmmgg::date and id_pref = 10 
--and idk_doc not in ( 110, 193, 120, 1000) 
--and idk_doc =100
  and idk_doc not in ( 110, 111, 193,194)
and po.id = 1001 
       group by id_paccnt order by id_paccnt ) as p   on (p.id_paccnt = a.id);
--where a.archive =0 ;


 raise notice '69';

 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, sum_all, town_all,town_stove,town_heat, town_other, 
                           village_all,village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment,'pay_avg1','Середня оплата на 1 абонента','7_6','грн.',
 CASE WHEN count( distinct a.id ) >0 THEN sum( p.sum_val)/count( distinct a.id ) ELSE 0 END,
 CASE WHEN count( distinct CASE WHEN ((t.ident~'tgr7_1') or (((t.ident~'tgr7_51') or (t.ident~'tgr7_53'))) or (adr.is_town = 1 and (t.ident~'tgr7_3' or t.ident~'tgr7_6'))) THEN a.id END) >0 THEN sum( CASE WHEN ((t.ident~'tgr7_1') or (((t.ident~'tgr7_51') or (t.ident~'tgr7_53'))) or (adr.is_town = 1 and (t.ident~'tgr7_3' or t.ident~'tgr7_6'))) THEN p.sum_val END)/count( distinct CASE WHEN ((t.ident~'tgr7_1') or (((t.ident~'tgr7_51') or (t.ident~'tgr7_53'))) or (adr.is_town = 1 and (t.ident~'tgr7_3' or t.ident~'tgr7_6'))) THEN a.id END) ELSE 0 END,
 CASE WHEN count( distinct CASE WHEN adr.is_town = 1 and t.ident~'tgr7_3' THEN a.id END) >0 THEN sum( CASE WHEN adr.is_town = 1 and t.ident~'tgr7_3' THEN p.sum_val END)/count( distinct CASE WHEN adr.is_town = 1 and t.ident~'tgr7_3' THEN a.id END) ELSE 0 END,
 CASE WHEN count( distinct CASE WHEN  ((t.ident~'tgr7_51') or (t.ident~'tgr7_53')) THEN a.id END) >0 THEN sum( CASE WHEN  ((t.ident~'tgr7_51') or (t.ident~'tgr7_53')) THEN p.sum_val END)/count( distinct CASE WHEN  ((t.ident~'tgr7_51') or (t.ident~'tgr7_53')) THEN a.id END) ELSE 0 END,
 CASE WHEN count( distinct CASE WHEN (t.ident~'tgr7_1' or (adr.is_town = 1 and t.ident~'tgr7_6'))  THEN a.id END) >0 THEN sum( CASE WHEN (t.ident~'tgr7_1' or (adr.is_town = 1 and t.ident~'tgr7_6'))  THEN p.sum_val END)/count( distinct CASE WHEN (t.ident~'tgr7_1' or (adr.is_town = 1 and t.ident~'tgr7_6'))  THEN a.id END)  ELSE 0 END,
 CASE WHEN count( distinct CASE WHEN ((t.ident~'tgr7_2') or (t.ident~'tgr7_52') or (adr.is_town = 0 and (t.ident~'tgr7_3' or t.ident~'tgr7_6'))) THEN a.id END)>0 THEN sum( CASE WHEN ((t.ident~'tgr7_2') or (t.ident~'tgr7_52') or (adr.is_town = 0 and (t.ident~'tgr7_3' or t.ident~'tgr7_6'))) THEN p.sum_val END)/count( distinct CASE WHEN ((t.ident~'tgr7_2') or (t.ident~'tgr7_52') or (adr.is_town = 0 and (t.ident~'tgr7_3' or t.ident~'tgr7_6'))) THEN a.id END)  ELSE 0 END,
 CASE WHEN count( distinct CASE WHEN adr.is_town = 0 and t.ident~'tgr7_3' THEN a.id END) >0 THEN sum( CASE WHEN adr.is_town = 0 and t.ident~'tgr7_3' THEN p.sum_val END)/count( distinct CASE WHEN adr.is_town = 0 and t.ident~'tgr7_3' THEN a.id END) ELSE 0 END,
 CASE WHEN count( distinct CASE WHEN  t.ident~'tgr7_52' THEN a.id END)>0 THEN sum( CASE WHEN  t.ident~'tgr7_52' THEN p.sum_val END)/count( distinct CASE WHEN  t.ident~'tgr7_52' THEN a.id END) ELSE 0 END,
 CASE WHEN count( distinct CASE WHEN (t.ident~'tgr7_2' or (adr.is_town = 0 and t.ident~'tgr7_6'))  THEN a.id END) >0 THEN sum( CASE WHEN (t.ident~'tgr7_2' or (adr.is_town = 0 and t.ident~'tgr7_6'))  THEN p.sum_val END)/count( distinct CASE WHEN (t.ident~'tgr7_2' or (adr.is_town = 0 and t.ident~'tgr7_6'))  THEN a.id END) ELSE 0 END,
 1,1,vmmgg
 from 
 clm_paccnt_tmp as a
-- join (select id, max(dt_b) as dt from clm_paccnt_h  where archive =0 and 
--    ((dt_b < (vmmgg::date+'1 month'::interval) and dt_e is null)
--    or 
--    tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval(vmmgg::timestamp::abstime,(vmmgg::date+'1 month - 1 day'::interval)::timestamp::abstime)))
-- group by id order by id) as a2 on (a.id = a2.id and a2.dt = a.dt_b)
 join aqi_grptar_tbl as t on (t.id = a.id_gtar)
 join adt_addr_tbl as adr on (adr.id = (a.addr).id_class)
 left join (select id_paccnt, sum(value) as sum_val from acm_pay_tbl where mmgg = vmmgg::date and id_pref = 10 
 --and idk_doc not in ( 110, 193, 120, 1000) 
-- and idk_doc =100
  and idk_doc not in ( 110, 111, 193,194,1000)
       group by id_paccnt order by id_paccnt ) as p   on (p.id_paccnt = a.id);
 --where archive =0;
-- where (pid_paccnt is null or a.id = pid_paccnt);


 raise notice '70';

 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, sum_all, town_all,town_stove,town_heat, town_other, 
                           village_all,village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment,'pay_avg2','Середня оплата на 1 платника     ','7_7','грн.',

 CASE WHEN count( distinct a.id ) >0 THEN sum( p.sum_val)/count( distinct a.id ) ELSE 0 END,
 CASE WHEN count( distinct CASE WHEN ((t.ident~'tgr7_1') or (((t.ident~'tgr7_51') or (t.ident~'tgr7_53'))) or (adr.is_town = 1 and (t.ident~'tgr7_3' or t.ident~'tgr7_6'))) THEN a.id END) >0 THEN sum( CASE WHEN ((t.ident~'tgr7_1') or (((t.ident~'tgr7_51') or (t.ident~'tgr7_53'))) or (adr.is_town = 1 and (t.ident~'tgr7_3' or t.ident~'tgr7_6'))) THEN p.sum_val END)/count( distinct CASE WHEN ((t.ident~'tgr7_1') or (((t.ident~'tgr7_51') or (t.ident~'tgr7_53'))) or (adr.is_town = 1 and (t.ident~'tgr7_3' or t.ident~'tgr7_6'))) THEN a.id END) ELSE 0 END,
 CASE WHEN count( distinct CASE WHEN adr.is_town = 1 and t.ident~'tgr7_3' THEN a.id END) >0 THEN sum( CASE WHEN adr.is_town = 1 and t.ident~'tgr7_3' THEN p.sum_val END)/count( distinct CASE WHEN adr.is_town = 1 and t.ident~'tgr7_3' THEN a.id END)ELSE 0 END,
 CASE WHEN count( distinct CASE WHEN  ((t.ident~'tgr7_51') or (t.ident~'tgr7_53')) THEN a.id END) >0 THEN sum( CASE WHEN  ((t.ident~'tgr7_51') or (t.ident~'tgr7_53')) THEN p.sum_val END)/count( distinct CASE WHEN  ((t.ident~'tgr7_51') or (t.ident~'tgr7_53')) THEN a.id END) ELSE 0 END,
 CASE WHEN count( distinct CASE WHEN (t.ident~'tgr7_1' or (adr.is_town = 1 and t.ident~'tgr7_6'))  THEN a.id END) >0 THEN sum( CASE WHEN (t.ident~'tgr7_1' or (adr.is_town = 1 and t.ident~'tgr7_6'))  THEN p.sum_val END)/count( distinct CASE WHEN (t.ident~'tgr7_1' or (adr.is_town = 1 and t.ident~'tgr7_6'))  THEN a.id END) ELSE 0 END,
 CASE WHEN count( distinct CASE WHEN ((t.ident~'tgr7_2') or (t.ident~'tgr7_52') or (adr.is_town = 0 and (t.ident~'tgr7_3' or t.ident~'tgr7_6'))) THEN a.id END) >0 THEN sum( CASE WHEN ((t.ident~'tgr7_2') or (t.ident~'tgr7_52') or (adr.is_town = 0 and (t.ident~'tgr7_3' or t.ident~'tgr7_6'))) THEN p.sum_val END)/count( distinct CASE WHEN ((t.ident~'tgr7_2') or (t.ident~'tgr7_52') or (adr.is_town = 0 and (t.ident~'tgr7_3'  or t.ident~'tgr7_6'))) THEN a.id END) ELSE 0 END,
 CASE WHEN count( distinct CASE WHEN adr.is_town = 0 and t.ident~'tgr7_3' THEN a.id END) >0 THEN sum( CASE WHEN adr.is_town = 0 and t.ident~'tgr7_3' THEN p.sum_val END)/count( distinct CASE WHEN adr.is_town = 0 and t.ident~'tgr7_3' THEN a.id END) ELSE 0 END,
 CASE WHEN count( distinct CASE WHEN  t.ident~'tgr7_52' THEN a.id END) >0 THEN sum( CASE WHEN  t.ident~'tgr7_52' THEN p.sum_val END)/count( distinct CASE WHEN  t.ident~'tgr7_52' THEN a.id END) ELSE 0 END,
 CASE WHEN count( distinct CASE WHEN (t.ident~'tgr7_2' or (adr.is_town = 0 and t.ident~'tgr7_6'))  THEN a.id END) >0 THEN sum( CASE WHEN (t.ident~'tgr7_2' or (adr.is_town = 0 and t.ident~'tgr7_6'))  THEN p.sum_val END)/count( distinct CASE WHEN (t.ident~'tgr7_2' or (adr.is_town = 0 and t.ident~'tgr7_6'))  THEN a.id END) ELSE 0 END,
 1,1,vmmgg
 from 
 clm_paccnt_tmp as a
-- join (select id, max(dt_b) as dt from clm_paccnt_h  where archive =0 and 
--    ((dt_b < (vmmgg::date+'1 month'::interval) and dt_e is null)
--    or 
--    tintervalov(tinterval(dt_b::timestamp::abstime,dt_e::timestamp::abstime),tinterval(vmmgg::timestamp::abstime,(vmmgg::date+'1 month - 1 day'::interval)::timestamp::abstime)))
-- group by id order by id) as a2 on (a.id = a2.id and a2.dt = a.dt_b)
 join aqi_grptar_tbl as t on (t.id = a.id_gtar)
 join adt_addr_tbl as adr on (adr.id = (a.addr).id_class)
 join (select id_paccnt, sum(value) as sum_val from acm_pay_tbl where mmgg = vmmgg::date and id_pref = 10
 -- and idk_doc not in ( 110, 193, 120, 1000) 
  and idk_doc not in ( 110, 111, 193,194, 1000)
       group by id_paccnt order by id_paccnt ) as p   on (p.id_paccnt = a.id);
 --where archive =0;
-- where (pid_paccnt is null or a.id = pid_paccnt);

 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment,'pay_corsum',' -- Iншi платежi(корегування) ','7_9','грн.',
 sum( CASE WHEN adr.is_town = 1 and t.ident~'tgr7_3' THEN p.sum_val END),
 sum( CASE WHEN  ((t.ident~'tgr7_51') or (t.ident~'tgr7_53')) THEN p.sum_val END),
 sum( CASE WHEN (t.ident~'tgr7_1')  or (adr.is_town = 1 and t.ident~'tgr7_6')  THEN p.sum_val END),
 sum( CASE WHEN adr.is_town = 0 and t.ident~'tgr7_3' THEN p.sum_val END),
 sum( CASE WHEN  t.ident~'tgr7_52' THEN p.sum_val END),
 sum( CASE WHEN (t.ident~'tgr7_2')  or (adr.is_town = 0 and t.ident~'tgr7_6')  THEN p.sum_val END),
 1,1,vmmgg
 from 
 clm_paccnt_tmp as a
 join aqi_grptar_tbl as t on (t.id = a.id_gtar)
 join adt_addr_tbl as adr on (adr.id = (a.addr).id_class)
 join (select id_paccnt, sum(value) as sum_val from acm_pay_tbl where mmgg = vmmgg::date and id_pref = 10
   and idk_doc not in ( 110, 111, 193,194, 100, 1000) 
       group by id_paccnt order by id_paccnt ) as p   on (p.id_paccnt = a.id);

--------------------------------------------------------------
-- вычтем субсидию всем массивом 

update rep_zvit_tmp
               set town_stove = coalesce(rep_zvit_tmp.town_stove,0) - coalesce(ss.town_stove,0),
	       town_heat  = coalesce(rep_zvit_tmp.town_heat,0)  - coalesce(ss.town_heat,0),
	       town_other = coalesce(rep_zvit_tmp.town_other,0) - coalesce(ss.town_other,0),
               village_stove = coalesce(rep_zvit_tmp.village_stove,0) - coalesce(ss.village_stove,0),
	       village_heat  = coalesce(rep_zvit_tmp.village_heat,0)  - coalesce(ss.village_heat,0),
	       village_other = coalesce(rep_zvit_tmp.village_other,0) - coalesce(ss.village_other,0)
 from (select * from rep_zvit_tmp where mmgg=vmmgg and ident = 'subs_all') as ss
where rep_zvit_tmp.mmgg=vmmgg and rep_zvit_tmp.ident = 'bill_sum';


update rep_zvit_tmp
               set town_stove = coalesce(rep_zvit_tmp.town_stove,0) - coalesce(ss.town_stove,0),
	       town_heat  = coalesce(rep_zvit_tmp.town_heat,0)  - coalesce(ss.town_heat,0),
	       town_other = coalesce(rep_zvit_tmp.town_other,0) - coalesce(ss.town_other,0),
               village_stove = coalesce(rep_zvit_tmp.village_stove,0) - coalesce(ss.village_stove,0),
	       village_heat  = coalesce(rep_zvit_tmp.village_heat,0)  - coalesce(ss.village_heat,0),
	       village_other = coalesce(rep_zvit_tmp.village_other,0) - coalesce(ss.village_other,0)
 from (select * from rep_zvit_tmp where mmgg=vmmgg and ident = 'subs_sum01') as ss
where rep_zvit_tmp.mmgg=vmmgg and rep_zvit_tmp.ident = 'bill_sum01';

update rep_zvit_tmp
               set town_stove = coalesce(rep_zvit_tmp.town_stove,0) - coalesce(ss.town_stove,0),
	       town_heat  = coalesce(rep_zvit_tmp.town_heat,0)  - coalesce(ss.town_heat,0),
	       town_other = coalesce(rep_zvit_tmp.town_other,0) - coalesce(ss.town_other,0),
               village_stove = coalesce(rep_zvit_tmp.village_stove,0) - coalesce(ss.village_stove,0),
	       village_heat  = coalesce(rep_zvit_tmp.village_heat,0)  - coalesce(ss.village_heat,0),
	       village_other = coalesce(rep_zvit_tmp.village_other,0) - coalesce(ss.village_other,0)
 from (select * from rep_zvit_tmp where mmgg=vmmgg and ident = 'subs_sum02') as ss
where rep_zvit_tmp.mmgg=vmmgg and rep_zvit_tmp.ident = 'bill_sum02';

update rep_zvit_tmp
               set town_stove = coalesce(rep_zvit_tmp.town_stove,0) - coalesce(ss.town_stove,0),
	       town_heat  = coalesce(rep_zvit_tmp.town_heat,0)  - coalesce(ss.town_heat,0),
	       town_other = coalesce(rep_zvit_tmp.town_other,0) - coalesce(ss.town_other,0),
               village_stove = coalesce(rep_zvit_tmp.village_stove,0) - coalesce(ss.village_stove,0),
	       village_heat  = coalesce(rep_zvit_tmp.village_heat,0)  - coalesce(ss.village_heat,0),
	       village_other = coalesce(rep_zvit_tmp.village_other,0) - coalesce(ss.village_other,0)
 from (select * from rep_zvit_tmp where mmgg=vmmgg and ident = 'subs_sum03') as ss
where rep_zvit_tmp.mmgg=vmmgg and rep_zvit_tmp.ident = 'bill_sum03';

update rep_zvit_tmp
               set town_stove = coalesce(rep_zvit_tmp.town_stove,0) - coalesce(ss.town_stove,0),
	       town_heat  = coalesce(rep_zvit_tmp.town_heat,0)  - coalesce(ss.town_heat,0),
	       town_other = coalesce(rep_zvit_tmp.town_other,0) - coalesce(ss.town_other,0),
               village_stove = coalesce(rep_zvit_tmp.village_stove,0) - coalesce(ss.village_stove,0),
	       village_heat  = coalesce(rep_zvit_tmp.village_heat,0)  - coalesce(ss.village_heat,0),
	       village_other = coalesce(rep_zvit_tmp.village_other,0) - coalesce(ss.village_other,0)
 from (select * from rep_zvit_tmp where mmgg=vmmgg and ident = 'subs_sum04') as ss
where rep_zvit_tmp.mmgg=vmmgg and rep_zvit_tmp.ident = 'bill_sum04';

update rep_zvit_tmp
               set town_stove = coalesce(rep_zvit_tmp.town_stove,0) - coalesce(ss.town_stove,0),
	       town_heat  = coalesce(rep_zvit_tmp.town_heat,0)  - coalesce(ss.town_heat,0),
	       town_other = coalesce(rep_zvit_tmp.town_other,0) - coalesce(ss.town_other,0),
               village_stove = coalesce(rep_zvit_tmp.village_stove,0) - coalesce(ss.village_stove,0),
	       village_heat  = coalesce(rep_zvit_tmp.village_heat,0)  - coalesce(ss.village_heat,0),
	       village_other = coalesce(rep_zvit_tmp.village_other,0) - coalesce(ss.village_other,0)
 from (select * from rep_zvit_tmp where mmgg=vmmgg and ident = 'subs_m') as ss
where rep_zvit_tmp.mmgg=vmmgg and rep_zvit_tmp.ident = 'bill_sum_0m';




update rep_zvit_tmp
               set town_stove = coalesce(rep_zvit_tmp.town_stove,0) - coalesce(ss.town_stove,0),
	       town_heat  = coalesce(rep_zvit_tmp.town_heat,0)  - coalesce(ss.town_heat,0),
	       town_other = coalesce(rep_zvit_tmp.town_other,0) - coalesce(ss.town_other,0),
               village_stove = coalesce(rep_zvit_tmp.village_stove,0) - coalesce(ss.village_stove,0),
	       village_heat  = coalesce(rep_zvit_tmp.village_heat,0)  - coalesce(ss.village_heat,0),
	       village_other = coalesce(rep_zvit_tmp.village_other,0) - coalesce(ss.village_other,0)
 from (select * from rep_zvit_tmp where mmgg=vmmgg and ident = 'subs_sum_ng01') as ss
where rep_zvit_tmp.mmgg=vmmgg and rep_zvit_tmp.ident = 'bill_sum_ng01';

update rep_zvit_tmp
               set town_stove = coalesce(rep_zvit_tmp.town_stove,0) - coalesce(ss.town_stove,0),
	       town_heat  = coalesce(rep_zvit_tmp.town_heat,0)  - coalesce(ss.town_heat,0),
	       town_other = coalesce(rep_zvit_tmp.town_other,0) - coalesce(ss.town_other,0),
               village_stove = coalesce(rep_zvit_tmp.village_stove,0) - coalesce(ss.village_stove,0),
	       village_heat  = coalesce(rep_zvit_tmp.village_heat,0)  - coalesce(ss.village_heat,0),
	       village_other = coalesce(rep_zvit_tmp.village_other,0) - coalesce(ss.village_other,0)
 from (select * from rep_zvit_tmp where mmgg=vmmgg and ident = 'subs_sum_ng02') as ss
where rep_zvit_tmp.mmgg=vmmgg and rep_zvit_tmp.ident = 'bill_sum_ng02';

update rep_zvit_tmp
               set town_stove = coalesce(rep_zvit_tmp.town_stove,0) - coalesce(ss.town_stove,0),
	       town_heat  = coalesce(rep_zvit_tmp.town_heat,0)  - coalesce(ss.town_heat,0),
	       town_other = coalesce(rep_zvit_tmp.town_other,0) - coalesce(ss.town_other,0),
               village_stove = coalesce(rep_zvit_tmp.village_stove,0) - coalesce(ss.village_stove,0),
	       village_heat  = coalesce(rep_zvit_tmp.village_heat,0)  - coalesce(ss.village_heat,0),
	       village_other = coalesce(rep_zvit_tmp.village_other,0) - coalesce(ss.village_other,0)
 from (select * from rep_zvit_tmp where mmgg=vmmgg and ident = 'subs_sum_ng03') as ss
where rep_zvit_tmp.mmgg=vmmgg and rep_zvit_tmp.ident = 'bill_sum_ng03';


update rep_zvit_tmp
               set town_stove = coalesce(rep_zvit_tmp.town_stove,0) - coalesce(ss.town_stove,0),
	       town_heat  = coalesce(rep_zvit_tmp.town_heat,0)  - coalesce(ss.town_heat,0),
	       town_other = coalesce(rep_zvit_tmp.town_other,0) - coalesce(ss.town_other,0),
               village_stove = coalesce(rep_zvit_tmp.village_stove,0) - coalesce(ss.village_stove,0),
	       village_heat  = coalesce(rep_zvit_tmp.village_heat,0)  - coalesce(ss.village_heat,0),
	       village_other = coalesce(rep_zvit_tmp.village_other,0) - coalesce(ss.village_other,0)
 from (select * from rep_zvit_tmp where mmgg=vmmgg and ident = 'subs_sum_ng04') as ss
where rep_zvit_tmp.mmgg=vmmgg and rep_zvit_tmp.ident = 'bill_sum_ng04';

update rep_zvit_tmp
               set town_stove = coalesce(rep_zvit_tmp.town_stove,0) - coalesce(ss.town_stove,0),
	       town_heat  = coalesce(rep_zvit_tmp.town_heat,0)  - coalesce(ss.town_heat,0),
	       town_other = coalesce(rep_zvit_tmp.town_other,0) - coalesce(ss.town_other,0),
               village_stove = coalesce(rep_zvit_tmp.village_stove,0) - coalesce(ss.village_stove,0),
	       village_heat  = coalesce(rep_zvit_tmp.village_heat,0)  - coalesce(ss.village_heat,0),
	       village_other = coalesce(rep_zvit_tmp.village_other,0) - coalesce(ss.village_other,0)
 from (select * from rep_zvit_tmp where mmgg=vmmgg and ident = 'subs_sum_ps1') as ss
where rep_zvit_tmp.mmgg=vmmgg and rep_zvit_tmp.ident = 'bill_sum_ps1';

---------------------------------------------------------------
-- вычтем возврат субсидии всем массивом 
  /*
update rep_zvit_tmp
               set town_stove = coalesce(rep_zvit_tmp.town_stove,0) - coalesce(ss.town_stove,0),
	       town_heat  = coalesce(rep_zvit_tmp.town_heat,0)  - coalesce(ss.town_heat,0),
	       town_other = coalesce(rep_zvit_tmp.town_other,0) - coalesce(ss.town_other,0),
               village_stove = coalesce(rep_zvit_tmp.village_stove,0) - coalesce(ss.village_stove,0),
	       village_heat  = coalesce(rep_zvit_tmp.village_heat,0)  - coalesce(ss.village_heat,0),
	       village_other = coalesce(rep_zvit_tmp.village_other,0) - coalesce(ss.village_other,0)
 from (select * from rep_zvit_tmp where mmgg=vmmgg and ident = 'rsubs_all') as ss
where rep_zvit_tmp.mmgg=vmmgg and rep_zvit_tmp.ident = 'bill_sum';


update rep_zvit_tmp
               set town_stove = coalesce(rep_zvit_tmp.town_stove,0) - coalesce(ss.town_stove,0),
	       town_heat  = coalesce(rep_zvit_tmp.town_heat,0)  - coalesce(ss.town_heat,0),
	       town_other = coalesce(rep_zvit_tmp.town_other,0) - coalesce(ss.town_other,0),
               village_stove = coalesce(rep_zvit_tmp.village_stove,0) - coalesce(ss.village_stove,0),
	       village_heat  = coalesce(rep_zvit_tmp.village_heat,0)  - coalesce(ss.village_heat,0),
	       village_other = coalesce(rep_zvit_tmp.village_other,0) - coalesce(ss.village_other,0)
 from (select * from rep_zvit_tmp where mmgg=vmmgg and ident = 'rsubs_sum1') as ss
where rep_zvit_tmp.mmgg=vmmgg and rep_zvit_tmp.ident = 'bill_sum1';

update rep_zvit_tmp
               set town_stove = coalesce(rep_zvit_tmp.town_stove,0) - coalesce(ss.town_stove,0),
	       town_heat  = coalesce(rep_zvit_tmp.town_heat,0)  - coalesce(ss.town_heat,0),
	       town_other = coalesce(rep_zvit_tmp.town_other,0) - coalesce(ss.town_other,0),
               village_stove = coalesce(rep_zvit_tmp.village_stove,0) - coalesce(ss.village_stove,0),
	       village_heat  = coalesce(rep_zvit_tmp.village_heat,0)  - coalesce(ss.village_heat,0),
	       village_other = coalesce(rep_zvit_tmp.village_other,0) - coalesce(ss.village_other,0)
 from (select * from rep_zvit_tmp where mmgg=vmmgg and ident = 'rsubs_sum2') as ss
where rep_zvit_tmp.mmgg=vmmgg and rep_zvit_tmp.ident = 'bill_sum2';

update rep_zvit_tmp
               set town_stove = coalesce(rep_zvit_tmp.town_stove,0) - coalesce(ss.town_stove,0),
	       town_heat  = coalesce(rep_zvit_tmp.town_heat,0)  - coalesce(ss.town_heat,0),
	       town_other = coalesce(rep_zvit_tmp.town_other,0) - coalesce(ss.town_other,0),
               village_stove = coalesce(rep_zvit_tmp.village_stove,0) - coalesce(ss.village_stove,0),
	       village_heat  = coalesce(rep_zvit_tmp.village_heat,0)  - coalesce(ss.village_heat,0),
	       village_other = coalesce(rep_zvit_tmp.village_other,0) - coalesce(ss.village_other,0)
 from (select * from rep_zvit_tmp where mmgg=vmmgg and ident = 'rsubs_sum3') as ss
where rep_zvit_tmp.mmgg=vmmgg and rep_zvit_tmp.ident = 'bill_sum3';

update rep_zvit_tmp
               set town_stove = coalesce(rep_zvit_tmp.town_stove,0) - coalesce(ss.town_stove,0),
	       town_heat  = coalesce(rep_zvit_tmp.town_heat,0)  - coalesce(ss.town_heat,0),
	       town_other = coalesce(rep_zvit_tmp.town_other,0) - coalesce(ss.town_other,0),
               village_stove = coalesce(rep_zvit_tmp.village_stove,0) - coalesce(ss.village_stove,0),
	       village_heat  = coalesce(rep_zvit_tmp.village_heat,0)  - coalesce(ss.village_heat,0),
	       village_other = coalesce(rep_zvit_tmp.village_other,0) - coalesce(ss.village_other,0)
 from (select * from rep_zvit_tmp where mmgg=vmmgg and ident = 'rsubs_sum4') as ss
where rep_zvit_tmp.mmgg=vmmgg and rep_zvit_tmp.ident = 'bill_sum4';

update rep_zvit_tmp
               set town_stove = coalesce(rep_zvit_tmp.town_stove,0) - coalesce(ss.town_stove,0),
	       town_heat  = coalesce(rep_zvit_tmp.town_heat,0)  - coalesce(ss.town_heat,0),
	       town_other = coalesce(rep_zvit_tmp.town_other,0) - coalesce(ss.town_other,0),
               village_stove = coalesce(rep_zvit_tmp.village_stove,0) - coalesce(ss.village_stove,0),
	       village_heat  = coalesce(rep_zvit_tmp.village_heat,0)  - coalesce(ss.village_heat,0),
	       village_other = coalesce(rep_zvit_tmp.village_other,0) - coalesce(ss.village_other,0)
 from (select * from rep_zvit_tmp where mmgg=vmmgg and ident = 'rsubs_sum5') as ss
where rep_zvit_tmp.mmgg=vmmgg and rep_zvit_tmp.ident = 'bill_sum5';

update rep_zvit_tmp
               set town_stove = coalesce(rep_zvit_tmp.town_stove,0) - coalesce(ss.town_stove,0),
	       town_heat  = coalesce(rep_zvit_tmp.town_heat,0)  - coalesce(ss.town_heat,0),
	       town_other = coalesce(rep_zvit_tmp.town_other,0) - coalesce(ss.town_other,0),
               village_stove = coalesce(rep_zvit_tmp.village_stove,0) - coalesce(ss.village_stove,0),
	       village_heat  = coalesce(rep_zvit_tmp.village_heat,0)  - coalesce(ss.village_heat,0),
	       village_other = coalesce(rep_zvit_tmp.village_other,0) - coalesce(ss.village_other,0)
 from (select * from rep_zvit_tmp where mmgg=vmmgg and ident = 'rsubs_sum6') as ss
where rep_zvit_tmp.mmgg=vmmgg and rep_zvit_tmp.ident = 'bill_sum6';


update rep_zvit_tmp
               set town_stove = coalesce(rep_zvit_tmp.town_stove,0) - coalesce(ss.town_stove,0),
	       town_heat  = coalesce(rep_zvit_tmp.town_heat,0)  - coalesce(ss.town_heat,0),
	       town_other = coalesce(rep_zvit_tmp.town_other,0) - coalesce(ss.town_other,0),
               village_stove = coalesce(rep_zvit_tmp.village_stove,0) - coalesce(ss.village_stove,0),
	       village_heat  = coalesce(rep_zvit_tmp.village_heat,0)  - coalesce(ss.village_heat,0),
	       village_other = coalesce(rep_zvit_tmp.village_other,0) - coalesce(ss.village_other,0)
 from (select * from rep_zvit_tmp where mmgg=vmmgg and ident = 'rsubs_sum7') as ss
where rep_zvit_tmp.mmgg=vmmgg and rep_zvit_tmp.ident = 'bill_sum7';

update rep_zvit_tmp
               set town_stove = coalesce(rep_zvit_tmp.town_stove,0) - coalesce(ss.town_stove,0),
	       town_heat  = coalesce(rep_zvit_tmp.town_heat,0)  - coalesce(ss.town_heat,0),
	       town_other = coalesce(rep_zvit_tmp.town_other,0) - coalesce(ss.town_other,0),
               village_stove = coalesce(rep_zvit_tmp.village_stove,0) - coalesce(ss.village_stove,0),
	       village_heat  = coalesce(rep_zvit_tmp.village_heat,0)  - coalesce(ss.village_heat,0),
	       village_other = coalesce(rep_zvit_tmp.village_other,0) - coalesce(ss.village_other,0)
 from (select * from rep_zvit_tmp where mmgg=vmmgg and ident = 'rsubs_m') as ss
where rep_zvit_tmp.mmgg=vmmgg and rep_zvit_tmp.ident = 'bill_sum_m';




update rep_zvit_tmp
               set town_stove = coalesce(rep_zvit_tmp.town_stove,0) - coalesce(ss.town_stove,0),
	       town_heat  = coalesce(rep_zvit_tmp.town_heat,0)  - coalesce(ss.town_heat,0),
	       town_other = coalesce(rep_zvit_tmp.town_other,0) - coalesce(ss.town_other,0),
               village_stove = coalesce(rep_zvit_tmp.village_stove,0) - coalesce(ss.village_stove,0),
	       village_heat  = coalesce(rep_zvit_tmp.village_heat,0)  - coalesce(ss.village_heat,0),
	       village_other = coalesce(rep_zvit_tmp.village_other,0) - coalesce(ss.village_other,0)
 from (select * from rep_zvit_tmp where mmgg=vmmgg and ident = 'rsubs_sum_ng1') as ss
where rep_zvit_tmp.mmgg=vmmgg and rep_zvit_tmp.ident = 'bill_sum_ng1';

update rep_zvit_tmp
               set town_stove = coalesce(rep_zvit_tmp.town_stove,0) - coalesce(ss.town_stove,0),
	       town_heat  = coalesce(rep_zvit_tmp.town_heat,0)  - coalesce(ss.town_heat,0),
	       town_other = coalesce(rep_zvit_tmp.town_other,0) - coalesce(ss.town_other,0),
               village_stove = coalesce(rep_zvit_tmp.village_stove,0) - coalesce(ss.village_stove,0),
	       village_heat  = coalesce(rep_zvit_tmp.village_heat,0)  - coalesce(ss.village_heat,0),
	       village_other = coalesce(rep_zvit_tmp.village_other,0) - coalesce(ss.village_other,0)
 from (select * from rep_zvit_tmp where mmgg=vmmgg and ident = 'rsubs_sum_ng2') as ss
where rep_zvit_tmp.mmgg=vmmgg and rep_zvit_tmp.ident = 'bill_sum_ng2';

update rep_zvit_tmp
               set town_stove = coalesce(rep_zvit_tmp.town_stove,0) - coalesce(ss.town_stove,0),
	       town_heat  = coalesce(rep_zvit_tmp.town_heat,0)  - coalesce(ss.town_heat,0),
	       town_other = coalesce(rep_zvit_tmp.town_other,0) - coalesce(ss.town_other,0),
               village_stove = coalesce(rep_zvit_tmp.village_stove,0) - coalesce(ss.village_stove,0),
	       village_heat  = coalesce(rep_zvit_tmp.village_heat,0)  - coalesce(ss.village_heat,0),
	       village_other = coalesce(rep_zvit_tmp.village_other,0) - coalesce(ss.village_other,0)
 from (select * from rep_zvit_tmp where mmgg=vmmgg and ident = 'rsubs_sum_ng3') as ss
where rep_zvit_tmp.mmgg=vmmgg and rep_zvit_tmp.ident = 'bill_sum_ng3';


update rep_zvit_tmp
               set town_stove = coalesce(rep_zvit_tmp.town_stove,0) - coalesce(ss.town_stove,0),
	       town_heat  = coalesce(rep_zvit_tmp.town_heat,0)  - coalesce(ss.town_heat,0),
	       town_other = coalesce(rep_zvit_tmp.town_other,0) - coalesce(ss.town_other,0),
               village_stove = coalesce(rep_zvit_tmp.village_stove,0) - coalesce(ss.village_stove,0),
	       village_heat  = coalesce(rep_zvit_tmp.village_heat,0)  - coalesce(ss.village_heat,0),
	       village_other = coalesce(rep_zvit_tmp.village_other,0) - coalesce(ss.village_other,0)
 from (select * from rep_zvit_tmp where mmgg=vmmgg and ident = 'rsubs_sum_ng4') as ss
where rep_zvit_tmp.mmgg=vmmgg and rep_zvit_tmp.ident = 'bill_sum_ng4';

update rep_zvit_tmp
               set town_stove = coalesce(rep_zvit_tmp.town_stove,0) - coalesce(ss.town_stove,0),
	       town_heat  = coalesce(rep_zvit_tmp.town_heat,0)  - coalesce(ss.town_heat,0),
	       town_other = coalesce(rep_zvit_tmp.town_other,0) - coalesce(ss.town_other,0),
               village_stove = coalesce(rep_zvit_tmp.village_stove,0) - coalesce(ss.village_stove,0),
	       village_heat  = coalesce(rep_zvit_tmp.village_heat,0)  - coalesce(ss.village_heat,0),
	       village_other = coalesce(rep_zvit_tmp.village_other,0) - coalesce(ss.village_other,0)
 from (select * from rep_zvit_tmp where mmgg=vmmgg and ident = 'rsubs_sum_ng5') as ss
where rep_zvit_tmp.mmgg=vmmgg and rep_zvit_tmp.ident = 'bill_sum_ng5';


update rep_zvit_tmp
               set town_stove = coalesce(rep_zvit_tmp.town_stove,0) - coalesce(ss.town_stove,0),
	       town_heat  = coalesce(rep_zvit_tmp.town_heat,0)  - coalesce(ss.town_heat,0),
	       town_other = coalesce(rep_zvit_tmp.town_other,0) - coalesce(ss.town_other,0),
               village_stove = coalesce(rep_zvit_tmp.village_stove,0) - coalesce(ss.village_stove,0),
	       village_heat  = coalesce(rep_zvit_tmp.village_heat,0)  - coalesce(ss.village_heat,0),
	       village_other = coalesce(rep_zvit_tmp.village_other,0) - coalesce(ss.village_other,0)
 from (select * from rep_zvit_tmp where mmgg=vmmgg and ident = 'rsubs_sum_ng6') as ss
where rep_zvit_tmp.mmgg=vmmgg and rep_zvit_tmp.ident = 'bill_sum_ng6';


update rep_zvit_tmp
               set town_stove = coalesce(rep_zvit_tmp.town_stove,0) - coalesce(ss.town_stove,0),
	       town_heat  = coalesce(rep_zvit_tmp.town_heat,0)  - coalesce(ss.town_heat,0),
	       town_other = coalesce(rep_zvit_tmp.town_other,0) - coalesce(ss.town_other,0),
               village_stove = coalesce(rep_zvit_tmp.village_stove,0) - coalesce(ss.village_stove,0),
	       village_heat  = coalesce(rep_zvit_tmp.village_heat,0)  - coalesce(ss.village_heat,0),
	       village_other = coalesce(rep_zvit_tmp.village_other,0) - coalesce(ss.village_other,0)
 from (select * from rep_zvit_tmp where mmgg=vmmgg and ident = 'rsubs_sum_ng7') as ss
where rep_zvit_tmp.mmgg=vmmgg and rep_zvit_tmp.ident = 'bill_sum_ng7';

*/
---------------------------------------------------------------



 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment,'tovar_sum_e','              втч електроенергiя ','4_1','грн.', 
                           sum(town_stove)-round(sum(town_stove)/6,2),
			   sum(town_heat)-round(sum(town_heat)/6,2),
			   sum(town_other)-round(sum(town_other)/6,2),
                           sum(village_stove)-round(sum(village_stove)/6,2),
			   sum(village_heat)-round(sum(village_heat)/6,2),
			   sum(village_other)-round(sum(village_other)/6,2),  1, 1, vmmgg
 from rep_zvit_tmp where mmgg = vmmgg and ident ='tovar_sum';

 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment,'tovar_sum_tax','              втч ПДВ','4_2','грн.', 
                           round(sum(town_stove)/6,2),
			   round(sum(town_heat)/6,2),
			   round(sum(town_other)/6,2),
                           round(sum(village_stove)/6,2),
			   round(sum(village_heat)/6,2),
			   round(sum(village_other)/6,2),  1, 1, vmmgg
 from rep_zvit_tmp where mmgg = vmmgg and ident ='tovar_sum';



 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment,'bill_sum_e','              втч електроенергiя ','5_1','грн.', 
                           sum(town_stove)-round(sum(town_stove)/6,2),
			   sum(town_heat)-round(sum(town_heat)/6,2),
			   sum(town_other)-round(sum(town_other)/6,2),
                           sum(village_stove)-round(sum(village_stove)/6,2),
			   sum(village_heat)-round(sum(village_heat)/6,2),
			   sum(village_other)-round(sum(village_other)/6,2),  1, 1, vmmgg
 from rep_zvit_tmp where mmgg = vmmgg and ident ='bill_sum';

 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment,'bill_sum_tax','              втч ПДВ','5_2','грн.', 
                           round(sum(town_stove)/6,2),
			   round(sum(town_heat)/6,2),
			   round(sum(town_other)/6,2),
                           round(sum(village_stove)/6,2),
			   round(sum(village_heat)/6,2),
			   round(sum(village_other)/6,2),  1, 1, vmmgg
 from rep_zvit_tmp where mmgg = vmmgg and ident ='bill_sum';
----------------------------------------------------------------------------------------
-- кВтг по АПК
 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, town_stove,town_heat, town_other, 
                           village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment,'bill_all_dem_v','В т.ч. споживання ел.ен. АПК ','5_91','кВтг',
 sum(CASE WHEN adr.is_town = 1 and t.ident~'tgr7_3' THEN bb.demand END),
 sum(CASE WHEN ((t.ident~'tgr7_51') or (t.ident~'tgr7_53')) THEN bb.demand END),
 sum(CASE WHEN t.ident~'tgr7_1' or (adr.is_town = 1 and t.ident~'tgr7_6') THEN bb.demand END),
 sum(CASE WHEN adr.is_town = 0 and t.ident~'tgr7_3' THEN bb.demand END),
 sum(CASE WHEN t.ident~'tgr7_52' THEN bb.demand END),
 sum(CASE WHEN t.ident~'tgr7_2' or (adr.is_town = 0 and t.ident~'tgr7_6') THEN bb.demand END)
,1,1,vmmgg
 from 
 clm_paccnt_tmp as a
 join adt_addr_tbl as adr on (adr.id = (a.addr).id_class)
 join ( 
   select b.id_doc , b.id_paccnt, t.id_grptar , bs.demand
        from acm_bill_tbl as b 
	join acm_summ_tbl as bs on (b.id_doc= bs.id_doc) 
	join aqm_tarif_tbl as t on (t.id = bs.id_tarif)
	where b.idk_doc in (200,220,209,291) and bs.demand <>0 
        and b.mmgg = vmmgg and b.id_pref = 10
 ) as bb on (bb.id_paccnt = a.id)
-- join aqi_grptar_tbl as t on (t.id = bb.id_grptar and t.id = 11);
 join aqi_grptar_tbl as t on (t.id = a.id_gtar and t.id = 11);

----------------------------------------------------------------------------------------
/*
  if kodres =250 and vmmgg = '2017-01-01' then

   update rep_zvit_tmp set village_heat = village_heat-22.91 where mmgg=vmmgg and ident='tovar_sum2';
   update rep_zvit_tmp set village_heat = village_heat+22.91 where mmgg=vmmgg and ident='tovar_sum6';


   update rep_zvit_tmp set village_heat = village_heat-22.91 where mmgg=vmmgg and ident='bill_sum2';
   update rep_zvit_tmp set village_heat = village_heat+22.91 where mmgg=vmmgg and ident='bill_sum6';
 
  end if;
*/
-------------------------------------------------

    --bill_dem
    update rep_zvit_tmp
               set town_stove = coalesce(rep_zvit_tmp.town_stove,0) + coalesce(ss.town_stove,0),
	       town_heat  = coalesce(rep_zvit_tmp.town_heat,0)  + coalesce(ss.town_heat,0),
	       town_other = coalesce(rep_zvit_tmp.town_other,0) + coalesce(ss.town_other,0),
               village_stove = coalesce(rep_zvit_tmp.village_stove,0) + coalesce(ss.village_stove,0),
	       village_heat  = coalesce(rep_zvit_tmp.village_heat,0)  + coalesce(ss.village_heat,0),
	       village_other = coalesce(rep_zvit_tmp.village_other,0) + coalesce(ss.village_other,0)

    from (select sum(town_stove) as town_stove,sum(town_heat) as town_heat, sum(town_other) as town_other, sum(village_stove) as village_stove, sum(village_heat) as village_heat, sum(village_other) as village_other  from 
    rep_zvit_tmp where mmgg=vmmgg and ident in ('bill_dem11','bill_dem12') ) as ss
    where rep_zvit_tmp.mmgg=vmmgg and rep_zvit_tmp.ident = 'bill_dem01';

    update rep_zvit_tmp
               set town_stove = coalesce(rep_zvit_tmp.town_stove,0) + coalesce(ss.town_stove,0),
	       town_heat  = coalesce(rep_zvit_tmp.town_heat,0)  + coalesce(ss.town_heat,0),
	       town_other = coalesce(rep_zvit_tmp.town_other,0) + coalesce(ss.town_other,0),
               village_stove = coalesce(rep_zvit_tmp.village_stove,0) + coalesce(ss.village_stove,0),
	       village_heat  = coalesce(rep_zvit_tmp.village_heat,0)  + coalesce(ss.village_heat,0),
	       village_other = coalesce(rep_zvit_tmp.village_other,0) + coalesce(ss.village_other,0)

    from (select sum(town_stove) as town_stove,sum(town_heat) as town_heat, sum(town_other) as town_other, sum(village_stove) as village_stove, sum(village_heat) as village_heat, sum(village_other) as village_other  from  
    rep_zvit_tmp where mmgg=vmmgg and ident in ('bill_dem13','bill_dem14','bill_dem15') ) as ss
    where rep_zvit_tmp.mmgg=vmmgg and rep_zvit_tmp.ident = 'bill_dem02';


    update rep_zvit_tmp
               set town_stove = coalesce(rep_zvit_tmp.town_stove,0) + coalesce(ss.town_stove,0),
	       town_heat  = coalesce(rep_zvit_tmp.town_heat,0)  + coalesce(ss.town_heat,0),
	       town_other = coalesce(rep_zvit_tmp.town_other,0) + coalesce(ss.town_other,0),
               village_stove = coalesce(rep_zvit_tmp.village_stove,0) + coalesce(ss.village_stove,0),
	       village_heat  = coalesce(rep_zvit_tmp.village_heat,0)  + coalesce(ss.village_heat,0),
	       village_other = coalesce(rep_zvit_tmp.village_other,0) + coalesce(ss.village_other,0)

    from (select sum(town_stove) as town_stove,sum(town_heat) as town_heat, sum(town_other) as town_other, sum(village_stove) as village_stove, sum(village_heat) as village_heat, sum(village_other) as village_other  from  
    rep_zvit_tmp where mmgg=vmmgg and ident in ('bill_dem16') ) as ss
    where rep_zvit_tmp.mmgg=vmmgg and rep_zvit_tmp.ident = 'bill_dem03';


    update rep_zvit_tmp
               set town_stove = coalesce(rep_zvit_tmp.town_stove,0) + coalesce(ss.town_stove,0),
	       town_heat  = coalesce(rep_zvit_tmp.town_heat,0)  + coalesce(ss.town_heat,0),
	       town_other = coalesce(rep_zvit_tmp.town_other,0) + coalesce(ss.town_other,0),
               village_stove = coalesce(rep_zvit_tmp.village_stove,0) + coalesce(ss.village_stove,0),
	       village_heat  = coalesce(rep_zvit_tmp.village_heat,0)  + coalesce(ss.village_heat,0),
	       village_other = coalesce(rep_zvit_tmp.village_other,0) + coalesce(ss.village_other,0)

    from (select sum(town_stove) as town_stove,sum(town_heat) as town_heat, sum(town_other) as town_other, sum(village_stove) as village_stove, sum(village_heat) as village_heat, sum(village_other) as village_other  from  
    rep_zvit_tmp where mmgg=vmmgg and ident in ('bill_dem17') ) as ss
    where rep_zvit_tmp.mmgg=vmmgg and rep_zvit_tmp.ident = 'bill_dem04';


    update rep_zvit_tmp
               set town_stove = coalesce(rep_zvit_tmp.town_stove,0) + coalesce(ss.town_stove,0),
	       town_heat  = coalesce(rep_zvit_tmp.town_heat,0)  + coalesce(ss.town_heat,0),
	       town_other = coalesce(rep_zvit_tmp.town_other,0) + coalesce(ss.town_other,0),
               village_stove = coalesce(rep_zvit_tmp.village_stove,0) + coalesce(ss.village_stove,0),
	       village_heat  = coalesce(rep_zvit_tmp.village_heat,0)  + coalesce(ss.village_heat,0),
	       village_other = coalesce(rep_zvit_tmp.village_other,0) + coalesce(ss.village_other,0)

    from (select sum(town_stove) as town_stove,sum(town_heat) as town_heat, sum(town_other) as town_other, sum(village_stove) as village_stove, sum(village_heat) as village_heat, sum(village_other) as village_other  from  
    rep_zvit_tmp where mmgg=vmmgg and ident in ('bill_dem_1m') ) as ss
    where rep_zvit_tmp.mmgg=vmmgg and rep_zvit_tmp.ident = 'bill_dem_0m';



    update rep_zvit_tmp
               set town_stove = coalesce(rep_zvit_tmp.town_stove,0) + coalesce(ss.town_stove,0),
	       town_heat  = coalesce(rep_zvit_tmp.town_heat,0)  + coalesce(ss.town_heat,0),
	       town_other = coalesce(rep_zvit_tmp.town_other,0) + coalesce(ss.town_other,0),
               village_stove = coalesce(rep_zvit_tmp.village_stove,0) + coalesce(ss.village_stove,0),
	       village_heat  = coalesce(rep_zvit_tmp.village_heat,0)  + coalesce(ss.village_heat,0),
	       village_other = coalesce(rep_zvit_tmp.village_other,0) + coalesce(ss.village_other,0)

    from (select sum(town_stove) as town_stove,sum(town_heat) as town_heat, sum(town_other) as town_other, sum(village_stove) as village_stove, sum(village_heat) as village_heat, sum(village_other) as village_other  from  
    rep_zvit_tmp where mmgg=vmmgg and ident in ('bill_dem_ng11','bill_dem_ng12') ) as ss
    where rep_zvit_tmp.mmgg=vmmgg and rep_zvit_tmp.ident = 'bill_dem_ng01';

    update rep_zvit_tmp
               set town_stove = coalesce(rep_zvit_tmp.town_stove,0) + coalesce(ss.town_stove,0),
	       town_heat  = coalesce(rep_zvit_tmp.town_heat,0)  + coalesce(ss.town_heat,0),
	       town_other = coalesce(rep_zvit_tmp.town_other,0) + coalesce(ss.town_other,0),
               village_stove = coalesce(rep_zvit_tmp.village_stove,0) + coalesce(ss.village_stove,0),
	       village_heat  = coalesce(rep_zvit_tmp.village_heat,0)  + coalesce(ss.village_heat,0),
	       village_other = coalesce(rep_zvit_tmp.village_other,0) + coalesce(ss.village_other,0)

    from (select sum(town_stove) as town_stove,sum(town_heat) as town_heat, sum(town_other) as town_other, sum(village_stove) as village_stove, sum(village_heat) as village_heat, sum(village_other) as village_other  from  
    rep_zvit_tmp where mmgg=vmmgg and ident in ('bill_dem_ng13','bill_dem_ng14','bill_dem_ng15') ) as ss
    where rep_zvit_tmp.mmgg=vmmgg and rep_zvit_tmp.ident = 'bill_dem_ng02';


    update rep_zvit_tmp
               set town_stove = coalesce(rep_zvit_tmp.town_stove,0) + coalesce(ss.town_stove,0),
	       town_heat  = coalesce(rep_zvit_tmp.town_heat,0)  + coalesce(ss.town_heat,0),
	       town_other = coalesce(rep_zvit_tmp.town_other,0) + coalesce(ss.town_other,0),
               village_stove = coalesce(rep_zvit_tmp.village_stove,0) + coalesce(ss.village_stove,0),
	       village_heat  = coalesce(rep_zvit_tmp.village_heat,0)  + coalesce(ss.village_heat,0),
	       village_other = coalesce(rep_zvit_tmp.village_other,0) + coalesce(ss.village_other,0)

    from (select sum(town_stove) as town_stove,sum(town_heat) as town_heat, sum(town_other) as town_other, sum(village_stove) as village_stove, sum(village_heat) as village_heat, sum(village_other) as village_other  from  
    rep_zvit_tmp where mmgg=vmmgg and ident in ('bill_dem_ng16') ) as ss
    where rep_zvit_tmp.mmgg=vmmgg and rep_zvit_tmp.ident = 'bill_dem_ng03';


    update rep_zvit_tmp
               set town_stove = coalesce(rep_zvit_tmp.town_stove,0) + coalesce(ss.town_stove,0),
	       town_heat  = coalesce(rep_zvit_tmp.town_heat,0)  + coalesce(ss.town_heat,0),
	       town_other = coalesce(rep_zvit_tmp.town_other,0) + coalesce(ss.town_other,0),
               village_stove = coalesce(rep_zvit_tmp.village_stove,0) + coalesce(ss.village_stove,0),
	       village_heat  = coalesce(rep_zvit_tmp.village_heat,0)  + coalesce(ss.village_heat,0),
	       village_other = coalesce(rep_zvit_tmp.village_other,0) + coalesce(ss.village_other,0)

    from (select sum(town_stove) as town_stove,sum(town_heat) as town_heat, sum(town_other) as town_other, sum(village_stove) as village_stove, sum(village_heat) as village_heat, sum(village_other) as village_other  from  
    rep_zvit_tmp where mmgg=vmmgg and ident in ('bill_dem_ng17') ) as ss
    where rep_zvit_tmp.mmgg=vmmgg and rep_zvit_tmp.ident = 'bill_dem_ng04';


    --tovar_sum
    update rep_zvit_tmp
               set town_stove = coalesce(rep_zvit_tmp.town_stove,0) + coalesce(ss.town_stove,0),
	       town_heat  = coalesce(rep_zvit_tmp.town_heat,0)  + coalesce(ss.town_heat,0),
	       town_other = coalesce(rep_zvit_tmp.town_other,0) + coalesce(ss.town_other,0),
               village_stove = coalesce(rep_zvit_tmp.village_stove,0) + coalesce(ss.village_stove,0),
	       village_heat  = coalesce(rep_zvit_tmp.village_heat,0)  + coalesce(ss.village_heat,0),
	       village_other = coalesce(rep_zvit_tmp.village_other,0) + coalesce(ss.village_other,0)

    from (select sum(town_stove) as town_stove,sum(town_heat) as town_heat, sum(town_other) as town_other, sum(village_stove) as village_stove, sum(village_heat) as village_heat, sum(village_other) as village_other  from  
    rep_zvit_tmp where mmgg=vmmgg and ident in ('tovar_sum11','tovar_sum12') ) as ss
    where rep_zvit_tmp.mmgg=vmmgg and rep_zvit_tmp.ident = 'tovar_sum01';

    update rep_zvit_tmp
               set town_stove = coalesce(rep_zvit_tmp.town_stove,0) + coalesce(ss.town_stove,0),
	       town_heat  = coalesce(rep_zvit_tmp.town_heat,0)  + coalesce(ss.town_heat,0),
	       town_other = coalesce(rep_zvit_tmp.town_other,0) + coalesce(ss.town_other,0),
               village_stove = coalesce(rep_zvit_tmp.village_stove,0) + coalesce(ss.village_stove,0),
	       village_heat  = coalesce(rep_zvit_tmp.village_heat,0)  + coalesce(ss.village_heat,0),
	       village_other = coalesce(rep_zvit_tmp.village_other,0) + coalesce(ss.village_other,0)

    from (select sum(town_stove) as town_stove,sum(town_heat) as town_heat, sum(town_other) as town_other, sum(village_stove) as village_stove, sum(village_heat) as village_heat, sum(village_other) as village_other  from  
    rep_zvit_tmp where mmgg=vmmgg and ident in ('tovar_sum13','tovar_sum14','tovar_sum15') ) as ss
    where rep_zvit_tmp.mmgg=vmmgg and rep_zvit_tmp.ident = 'tovar_sum02';


    update rep_zvit_tmp
               set town_stove = coalesce(rep_zvit_tmp.town_stove,0) + coalesce(ss.town_stove,0),
	       town_heat  = coalesce(rep_zvit_tmp.town_heat,0)  + coalesce(ss.town_heat,0),
	       town_other = coalesce(rep_zvit_tmp.town_other,0) + coalesce(ss.town_other,0),
               village_stove = coalesce(rep_zvit_tmp.village_stove,0) + coalesce(ss.village_stove,0),
	       village_heat  = coalesce(rep_zvit_tmp.village_heat,0)  + coalesce(ss.village_heat,0),
	       village_other = coalesce(rep_zvit_tmp.village_other,0) + coalesce(ss.village_other,0)

    from (select sum(town_stove) as town_stove,sum(town_heat) as town_heat, sum(town_other) as town_other, sum(village_stove) as village_stove, sum(village_heat) as village_heat, sum(village_other) as village_other  from  
    rep_zvit_tmp where mmgg=vmmgg and ident in ('tovar_sum16') ) as ss
    where rep_zvit_tmp.mmgg=vmmgg and rep_zvit_tmp.ident = 'tovar_sum03';


    update rep_zvit_tmp
               set town_stove = coalesce(rep_zvit_tmp.town_stove,0) + coalesce(ss.town_stove,0),
	       town_heat  = coalesce(rep_zvit_tmp.town_heat,0)  + coalesce(ss.town_heat,0),
	       town_other = coalesce(rep_zvit_tmp.town_other,0) + coalesce(ss.town_other,0),
               village_stove = coalesce(rep_zvit_tmp.village_stove,0) + coalesce(ss.village_stove,0),
	       village_heat  = coalesce(rep_zvit_tmp.village_heat,0)  + coalesce(ss.village_heat,0),
	       village_other = coalesce(rep_zvit_tmp.village_other,0) + coalesce(ss.village_other,0)

    from (select sum(town_stove) as town_stove,sum(town_heat) as town_heat, sum(town_other) as town_other, sum(village_stove) as village_stove, sum(village_heat) as village_heat, sum(village_other) as village_other  from  
    rep_zvit_tmp where mmgg=vmmgg and ident in ('tovar_sum17') ) as ss
    where rep_zvit_tmp.mmgg=vmmgg and rep_zvit_tmp.ident = 'tovar_sum04';


    update rep_zvit_tmp
               set town_stove = coalesce(rep_zvit_tmp.town_stove,0) + coalesce(ss.town_stove,0),
	       town_heat  = coalesce(rep_zvit_tmp.town_heat,0)  + coalesce(ss.town_heat,0),
	       town_other = coalesce(rep_zvit_tmp.town_other,0) + coalesce(ss.town_other,0),
               village_stove = coalesce(rep_zvit_tmp.village_stove,0) + coalesce(ss.village_stove,0),
	       village_heat  = coalesce(rep_zvit_tmp.village_heat,0)  + coalesce(ss.village_heat,0),
	       village_other = coalesce(rep_zvit_tmp.village_other,0) + coalesce(ss.village_other,0)

    from (select sum(town_stove) as town_stove,sum(town_heat) as town_heat, sum(town_other) as town_other, sum(village_stove) as village_stove, sum(village_heat) as village_heat, sum(village_other) as village_other  from  
    rep_zvit_tmp where mmgg=vmmgg and ident in ('tovar_sum_1m') ) as ss
    where rep_zvit_tmp.mmgg=vmmgg and rep_zvit_tmp.ident = 'tovar_sum_0m';



    update rep_zvit_tmp
               set town_stove = coalesce(rep_zvit_tmp.town_stove,0) + coalesce(ss.town_stove,0),
	       town_heat  = coalesce(rep_zvit_tmp.town_heat,0)  + coalesce(ss.town_heat,0),
	       town_other = coalesce(rep_zvit_tmp.town_other,0) + coalesce(ss.town_other,0),
               village_stove = coalesce(rep_zvit_tmp.village_stove,0) + coalesce(ss.village_stove,0),
	       village_heat  = coalesce(rep_zvit_tmp.village_heat,0)  + coalesce(ss.village_heat,0),
	       village_other = coalesce(rep_zvit_tmp.village_other,0) + coalesce(ss.village_other,0)

    from (select sum(town_stove) as town_stove,sum(town_heat) as town_heat, sum(town_other) as town_other, sum(village_stove) as village_stove, sum(village_heat) as village_heat, sum(village_other) as village_other  from  
    rep_zvit_tmp where mmgg=vmmgg and ident in ('tovar_sum_ng11','tovar_sum_ng12') ) as ss
    where rep_zvit_tmp.mmgg=vmmgg and rep_zvit_tmp.ident = 'tovar_sum_ng01';

    update rep_zvit_tmp
               set town_stove = coalesce(rep_zvit_tmp.town_stove,0) + coalesce(ss.town_stove,0),
	       town_heat  = coalesce(rep_zvit_tmp.town_heat,0)  + coalesce(ss.town_heat,0),
	       town_other = coalesce(rep_zvit_tmp.town_other,0) + coalesce(ss.town_other,0),
               village_stove = coalesce(rep_zvit_tmp.village_stove,0) + coalesce(ss.village_stove,0),
	       village_heat  = coalesce(rep_zvit_tmp.village_heat,0)  + coalesce(ss.village_heat,0),
	       village_other = coalesce(rep_zvit_tmp.village_other,0) + coalesce(ss.village_other,0)

    from (select sum(town_stove) as town_stove,sum(town_heat) as town_heat, sum(town_other) as town_other, sum(village_stove) as village_stove, sum(village_heat) as village_heat, sum(village_other) as village_other  from  
    rep_zvit_tmp where mmgg=vmmgg and ident in ('tovar_sum_ng13','tovar_sum_ng14','tovar_sum_ng15') ) as ss
    where rep_zvit_tmp.mmgg=vmmgg and rep_zvit_tmp.ident = 'tovar_sum_ng02';


    update rep_zvit_tmp
               set town_stove = coalesce(rep_zvit_tmp.town_stove,0) + coalesce(ss.town_stove,0),
	       town_heat  = coalesce(rep_zvit_tmp.town_heat,0)  + coalesce(ss.town_heat,0),
	       town_other = coalesce(rep_zvit_tmp.town_other,0) + coalesce(ss.town_other,0),
               village_stove = coalesce(rep_zvit_tmp.village_stove,0) + coalesce(ss.village_stove,0),
	       village_heat  = coalesce(rep_zvit_tmp.village_heat,0)  + coalesce(ss.village_heat,0),
	       village_other = coalesce(rep_zvit_tmp.village_other,0) + coalesce(ss.village_other,0)

    from (select sum(town_stove) as town_stove,sum(town_heat) as town_heat, sum(town_other) as town_other, sum(village_stove) as village_stove, sum(village_heat) as village_heat, sum(village_other) as village_other  from  
    rep_zvit_tmp where mmgg=vmmgg and ident in ('tovar_sum_ng16') ) as ss
    where rep_zvit_tmp.mmgg=vmmgg and rep_zvit_tmp.ident = 'tovar_sum_ng03';


    update rep_zvit_tmp
               set town_stove = coalesce(rep_zvit_tmp.town_stove,0) + coalesce(ss.town_stove,0),
	       town_heat  = coalesce(rep_zvit_tmp.town_heat,0)  + coalesce(ss.town_heat,0),
	       town_other = coalesce(rep_zvit_tmp.town_other,0) + coalesce(ss.town_other,0),
               village_stove = coalesce(rep_zvit_tmp.village_stove,0) + coalesce(ss.village_stove,0),
	       village_heat  = coalesce(rep_zvit_tmp.village_heat,0)  + coalesce(ss.village_heat,0),
	       village_other = coalesce(rep_zvit_tmp.village_other,0) + coalesce(ss.village_other,0)

    from (select sum(town_stove) as town_stove,sum(town_heat) as town_heat, sum(town_other) as town_other, sum(village_stove) as village_stove, sum(village_heat) as village_heat, sum(village_other) as village_other  from  
    rep_zvit_tmp where mmgg=vmmgg and ident in ('tovar_sum_ng17') ) as ss
    where rep_zvit_tmp.mmgg=vmmgg and rep_zvit_tmp.ident = 'tovar_sum_ng04';

    --bill_sum
    update rep_zvit_tmp
               set town_stove = coalesce(rep_zvit_tmp.town_stove,0) + coalesce(ss.town_stove,0),
	       town_heat  = coalesce(rep_zvit_tmp.town_heat,0)  + coalesce(ss.town_heat,0),
	       town_other = coalesce(rep_zvit_tmp.town_other,0) + coalesce(ss.town_other,0),
               village_stove = coalesce(rep_zvit_tmp.village_stove,0) + coalesce(ss.village_stove,0),
	       village_heat  = coalesce(rep_zvit_tmp.village_heat,0)  + coalesce(ss.village_heat,0),
	       village_other = coalesce(rep_zvit_tmp.village_other,0) + coalesce(ss.village_other,0)

    from (select sum(town_stove) as town_stove,sum(town_heat) as town_heat, sum(town_other) as town_other, sum(village_stove) as village_stove, sum(village_heat) as village_heat, sum(village_other) as village_other  from  
    rep_zvit_tmp where mmgg=vmmgg and ident in ('bill_sum11','bill_sum12') ) as ss
    where rep_zvit_tmp.mmgg=vmmgg and rep_zvit_tmp.ident = 'bill_sum01';

    update rep_zvit_tmp
               set town_stove = coalesce(rep_zvit_tmp.town_stove,0) + coalesce(ss.town_stove,0),
	       town_heat  = coalesce(rep_zvit_tmp.town_heat,0)  + coalesce(ss.town_heat,0),
	       town_other = coalesce(rep_zvit_tmp.town_other,0) + coalesce(ss.town_other,0),
               village_stove = coalesce(rep_zvit_tmp.village_stove,0) + coalesce(ss.village_stove,0),
	       village_heat  = coalesce(rep_zvit_tmp.village_heat,0)  + coalesce(ss.village_heat,0),
	       village_other = coalesce(rep_zvit_tmp.village_other,0) + coalesce(ss.village_other,0)

    from (select sum(town_stove) as town_stove,sum(town_heat) as town_heat, sum(town_other) as town_other, sum(village_stove) as village_stove, sum(village_heat) as village_heat, sum(village_other) as village_other  from  
    rep_zvit_tmp where mmgg=vmmgg and ident in ('bill_sum13','bill_sum14','bill_sum15') ) as ss
    where rep_zvit_tmp.mmgg=vmmgg and rep_zvit_tmp.ident = 'bill_sum02';


    update rep_zvit_tmp
               set town_stove = coalesce(rep_zvit_tmp.town_stove,0) + coalesce(ss.town_stove,0),
	       town_heat  = coalesce(rep_zvit_tmp.town_heat,0)  + coalesce(ss.town_heat,0),
	       town_other = coalesce(rep_zvit_tmp.town_other,0) + coalesce(ss.town_other,0),
               village_stove = coalesce(rep_zvit_tmp.village_stove,0) + coalesce(ss.village_stove,0),
	       village_heat  = coalesce(rep_zvit_tmp.village_heat,0)  + coalesce(ss.village_heat,0),
	       village_other = coalesce(rep_zvit_tmp.village_other,0) + coalesce(ss.village_other,0)

    from (select sum(town_stove) as town_stove,sum(town_heat) as town_heat, sum(town_other) as town_other, sum(village_stove) as village_stove, sum(village_heat) as village_heat, sum(village_other) as village_other  from  
    rep_zvit_tmp where mmgg=vmmgg and ident in ('bill_sum16') ) as ss
    where rep_zvit_tmp.mmgg=vmmgg and rep_zvit_tmp.ident = 'bill_sum03';


    update rep_zvit_tmp
               set town_stove = coalesce(rep_zvit_tmp.town_stove,0) + coalesce(ss.town_stove,0),
	       town_heat  = coalesce(rep_zvit_tmp.town_heat,0)  + coalesce(ss.town_heat,0),
	       town_other = coalesce(rep_zvit_tmp.town_other,0) + coalesce(ss.town_other,0),
               village_stove = coalesce(rep_zvit_tmp.village_stove,0) + coalesce(ss.village_stove,0),
	       village_heat  = coalesce(rep_zvit_tmp.village_heat,0)  + coalesce(ss.village_heat,0),
	       village_other = coalesce(rep_zvit_tmp.village_other,0) + coalesce(ss.village_other,0)

    from (select sum(town_stove) as town_stove,sum(town_heat) as town_heat, sum(town_other) as town_other, sum(village_stove) as village_stove, sum(village_heat) as village_heat, sum(village_other) as village_other  from  
    rep_zvit_tmp where mmgg=vmmgg and ident in ('bill_sum17') ) as ss
    where rep_zvit_tmp.mmgg=vmmgg and rep_zvit_tmp.ident = 'bill_sum04';


    update rep_zvit_tmp
               set town_stove = coalesce(rep_zvit_tmp.town_stove,0) + coalesce(ss.town_stove,0),
	       town_heat  = coalesce(rep_zvit_tmp.town_heat,0)  + coalesce(ss.town_heat,0),
	       town_other = coalesce(rep_zvit_tmp.town_other,0) + coalesce(ss.town_other,0),
               village_stove = coalesce(rep_zvit_tmp.village_stove,0) + coalesce(ss.village_stove,0),
	       village_heat  = coalesce(rep_zvit_tmp.village_heat,0)  + coalesce(ss.village_heat,0),
	       village_other = coalesce(rep_zvit_tmp.village_other,0) + coalesce(ss.village_other,0)

    from (select sum(town_stove) as town_stove,sum(town_heat) as town_heat, sum(town_other) as town_other, sum(village_stove) as village_stove, sum(village_heat) as village_heat, sum(village_other) as village_other  from  
    rep_zvit_tmp where mmgg=vmmgg and ident in ('bill_sum_1m') ) as ss
    where rep_zvit_tmp.mmgg=vmmgg and rep_zvit_tmp.ident = 'bill_sum_0m';



    update rep_zvit_tmp
               set town_stove = coalesce(rep_zvit_tmp.town_stove,0) + coalesce(ss.town_stove,0),
	       town_heat  = coalesce(rep_zvit_tmp.town_heat,0)  + coalesce(ss.town_heat,0),
	       town_other = coalesce(rep_zvit_tmp.town_other,0) + coalesce(ss.town_other,0),
               village_stove = coalesce(rep_zvit_tmp.village_stove,0) + coalesce(ss.village_stove,0),
	       village_heat  = coalesce(rep_zvit_tmp.village_heat,0)  + coalesce(ss.village_heat,0),
	       village_other = coalesce(rep_zvit_tmp.village_other,0) + coalesce(ss.village_other,0)

    from (select sum(town_stove) as town_stove,sum(town_heat) as town_heat, sum(town_other) as town_other, sum(village_stove) as village_stove, sum(village_heat) as village_heat, sum(village_other) as village_other  from  
    rep_zvit_tmp where mmgg=vmmgg and ident in ('bill_sum_ng11','bill_sum_ng12') ) as ss
    where rep_zvit_tmp.mmgg=vmmgg and rep_zvit_tmp.ident = 'bill_sum_ng01';

    update rep_zvit_tmp
               set town_stove = coalesce(rep_zvit_tmp.town_stove,0) + coalesce(ss.town_stove,0),
	       town_heat  = coalesce(rep_zvit_tmp.town_heat,0)  + coalesce(ss.town_heat,0),
	       town_other = coalesce(rep_zvit_tmp.town_other,0) + coalesce(ss.town_other,0),
               village_stove = coalesce(rep_zvit_tmp.village_stove,0) + coalesce(ss.village_stove,0),
	       village_heat  = coalesce(rep_zvit_tmp.village_heat,0)  + coalesce(ss.village_heat,0),
	       village_other = coalesce(rep_zvit_tmp.village_other,0) + coalesce(ss.village_other,0)

    from (select sum(town_stove) as town_stove,sum(town_heat) as town_heat, sum(town_other) as town_other, sum(village_stove) as village_stove, sum(village_heat) as village_heat, sum(village_other) as village_other  from  
    rep_zvit_tmp where mmgg=vmmgg and ident in ('bill_sum_ng13','bill_sum_ng14','bill_sum_ng15') ) as ss
    where rep_zvit_tmp.mmgg=vmmgg and rep_zvit_tmp.ident = 'bill_sum_ng02';


    update rep_zvit_tmp
               set town_stove = coalesce(rep_zvit_tmp.town_stove,0) + coalesce(ss.town_stove,0),
	       town_heat  = coalesce(rep_zvit_tmp.town_heat,0)  + coalesce(ss.town_heat,0),
	       town_other = coalesce(rep_zvit_tmp.town_other,0) + coalesce(ss.town_other,0),
               village_stove = coalesce(rep_zvit_tmp.village_stove,0) + coalesce(ss.village_stove,0),
	       village_heat  = coalesce(rep_zvit_tmp.village_heat,0)  + coalesce(ss.village_heat,0),
	       village_other = coalesce(rep_zvit_tmp.village_other,0) + coalesce(ss.village_other,0)

    from (select sum(town_stove) as town_stove,sum(town_heat) as town_heat, sum(town_other) as town_other, sum(village_stove) as village_stove, sum(village_heat) as village_heat, sum(village_other) as village_other  from  
    rep_zvit_tmp where mmgg=vmmgg and ident in ('bill_sum_ng16') ) as ss
    where rep_zvit_tmp.mmgg=vmmgg and rep_zvit_tmp.ident = 'bill_sum_ng03';


    update rep_zvit_tmp
               set town_stove = coalesce(rep_zvit_tmp.town_stove,0) + coalesce(ss.town_stove,0),
	       town_heat  = coalesce(rep_zvit_tmp.town_heat,0)  + coalesce(ss.town_heat,0),
	       town_other = coalesce(rep_zvit_tmp.town_other,0) + coalesce(ss.town_other,0),
               village_stove = coalesce(rep_zvit_tmp.village_stove,0) + coalesce(ss.village_stove,0),
	       village_heat  = coalesce(rep_zvit_tmp.village_heat,0)  + coalesce(ss.village_heat,0),
	       village_other = coalesce(rep_zvit_tmp.village_other,0) + coalesce(ss.village_other,0)

    from (select sum(town_stove) as town_stove,sum(town_heat) as town_heat, sum(town_other) as town_other, sum(village_stove) as village_stove, sum(village_heat) as village_heat, sum(village_other) as village_other  from  
    rep_zvit_tmp where mmgg=vmmgg and ident in ('bill_sum_ng17') ) as ss
    where rep_zvit_tmp.mmgg=vmmgg and rep_zvit_tmp.ident = 'bill_sum_ng04';


    --lgt_sum
    update rep_zvit_tmp
               set town_stove = coalesce(rep_zvit_tmp.town_stove,0) + coalesce(ss.town_stove,0),
	       town_heat  = coalesce(rep_zvit_tmp.town_heat,0)  + coalesce(ss.town_heat,0),
	       town_other = coalesce(rep_zvit_tmp.town_other,0) + coalesce(ss.town_other,0),
               village_stove = coalesce(rep_zvit_tmp.village_stove,0) + coalesce(ss.village_stove,0),
	       village_heat  = coalesce(rep_zvit_tmp.village_heat,0)  + coalesce(ss.village_heat,0),
	       village_other = coalesce(rep_zvit_tmp.village_other,0) + coalesce(ss.village_other,0)

    from (select sum(town_stove) as town_stove,sum(town_heat) as town_heat, sum(town_other) as town_other, sum(village_stove) as village_stove, sum(village_heat) as village_heat, sum(village_other) as village_other  from  
    rep_zvit_tmp where mmgg=vmmgg and ident in ('lgt_sum11','lgt_sum12') ) as ss
    where rep_zvit_tmp.mmgg=vmmgg and rep_zvit_tmp.ident = 'lgt_sum01';

    update rep_zvit_tmp
               set town_stove = coalesce(rep_zvit_tmp.town_stove,0) + coalesce(ss.town_stove,0),
	       town_heat  = coalesce(rep_zvit_tmp.town_heat,0)  + coalesce(ss.town_heat,0),
	       town_other = coalesce(rep_zvit_tmp.town_other,0) + coalesce(ss.town_other,0),
               village_stove = coalesce(rep_zvit_tmp.village_stove,0) + coalesce(ss.village_stove,0),
	       village_heat  = coalesce(rep_zvit_tmp.village_heat,0)  + coalesce(ss.village_heat,0),
	       village_other = coalesce(rep_zvit_tmp.village_other,0) + coalesce(ss.village_other,0)

    from (select sum(town_stove) as town_stove,sum(town_heat) as town_heat, sum(town_other) as town_other, sum(village_stove) as village_stove, sum(village_heat) as village_heat, sum(village_other) as village_other  from  
    rep_zvit_tmp where mmgg=vmmgg and ident in ('lgt_sum13','lgt_sum14','lgt_sum15') ) as ss
    where rep_zvit_tmp.mmgg=vmmgg and rep_zvit_tmp.ident = 'lgt_sum02';


    update rep_zvit_tmp
               set town_stove = coalesce(rep_zvit_tmp.town_stove,0) + coalesce(ss.town_stove,0),
	       town_heat  = coalesce(rep_zvit_tmp.town_heat,0)  + coalesce(ss.town_heat,0),
	       town_other = coalesce(rep_zvit_tmp.town_other,0) + coalesce(ss.town_other,0),
               village_stove = coalesce(rep_zvit_tmp.village_stove,0) + coalesce(ss.village_stove,0),
	       village_heat  = coalesce(rep_zvit_tmp.village_heat,0)  + coalesce(ss.village_heat,0),
	       village_other = coalesce(rep_zvit_tmp.village_other,0) + coalesce(ss.village_other,0)

    from (select sum(town_stove) as town_stove,sum(town_heat) as town_heat, sum(town_other) as town_other, sum(village_stove) as village_stove, sum(village_heat) as village_heat, sum(village_other) as village_other  from  
    rep_zvit_tmp where mmgg=vmmgg and ident in ('lgt_sum16') ) as ss
    where rep_zvit_tmp.mmgg=vmmgg and rep_zvit_tmp.ident = 'lgt_sum03';


    update rep_zvit_tmp
               set town_stove = coalesce(rep_zvit_tmp.town_stove,0) + coalesce(ss.town_stove,0),
	       town_heat  = coalesce(rep_zvit_tmp.town_heat,0)  + coalesce(ss.town_heat,0),
	       town_other = coalesce(rep_zvit_tmp.town_other,0) + coalesce(ss.town_other,0),
               village_stove = coalesce(rep_zvit_tmp.village_stove,0) + coalesce(ss.village_stove,0),
	       village_heat  = coalesce(rep_zvit_tmp.village_heat,0)  + coalesce(ss.village_heat,0),
	       village_other = coalesce(rep_zvit_tmp.village_other,0) + coalesce(ss.village_other,0)

    from (select sum(town_stove) as town_stove,sum(town_heat) as town_heat, sum(town_other) as town_other, sum(village_stove) as village_stove, sum(village_heat) as village_heat, sum(village_other) as village_other  from  
    rep_zvit_tmp where mmgg=vmmgg and ident in ('lgt_sum17') ) as ss
    where rep_zvit_tmp.mmgg=vmmgg and rep_zvit_tmp.ident = 'lgt_sum04';


    update rep_zvit_tmp
               set town_stove = coalesce(rep_zvit_tmp.town_stove,0) + coalesce(ss.town_stove,0),
	       town_heat  = coalesce(rep_zvit_tmp.town_heat,0)  + coalesce(ss.town_heat,0),
	       town_other = coalesce(rep_zvit_tmp.town_other,0) + coalesce(ss.town_other,0),
               village_stove = coalesce(rep_zvit_tmp.village_stove,0) + coalesce(ss.village_stove,0),
	       village_heat  = coalesce(rep_zvit_tmp.village_heat,0)  + coalesce(ss.village_heat,0),
	       village_other = coalesce(rep_zvit_tmp.village_other,0) + coalesce(ss.village_other,0)

    from (select sum(town_stove) as town_stove,sum(town_heat) as town_heat, sum(town_other) as town_other, sum(village_stove) as village_stove, sum(village_heat) as village_heat, sum(village_other) as village_other  from  
    rep_zvit_tmp where mmgg=vmmgg and ident in ('lgt_sum_1m') ) as ss
    where rep_zvit_tmp.mmgg=vmmgg and rep_zvit_tmp.ident = 'lgt_sum_0m';



    update rep_zvit_tmp
               set town_stove = coalesce(rep_zvit_tmp.town_stove,0) + coalesce(ss.town_stove,0),
	       town_heat  = coalesce(rep_zvit_tmp.town_heat,0)  + coalesce(ss.town_heat,0),
	       town_other = coalesce(rep_zvit_tmp.town_other,0) + coalesce(ss.town_other,0),
               village_stove = coalesce(rep_zvit_tmp.village_stove,0) + coalesce(ss.village_stove,0),
	       village_heat  = coalesce(rep_zvit_tmp.village_heat,0)  + coalesce(ss.village_heat,0),
	       village_other = coalesce(rep_zvit_tmp.village_other,0) + coalesce(ss.village_other,0)

    from (select sum(town_stove) as town_stove,sum(town_heat) as town_heat, sum(town_other) as town_other, sum(village_stove) as village_stove, sum(village_heat) as village_heat, sum(village_other) as village_other  from  
    rep_zvit_tmp where mmgg=vmmgg and ident in ('lgt_sum_ng11','lgt_sum_ng12') ) as ss
    where rep_zvit_tmp.mmgg=vmmgg and rep_zvit_tmp.ident = 'lgt_sum_ng01';

    update rep_zvit_tmp
               set town_stove = coalesce(rep_zvit_tmp.town_stove,0) + coalesce(ss.town_stove,0),
	       town_heat  = coalesce(rep_zvit_tmp.town_heat,0)  + coalesce(ss.town_heat,0),
	       town_other = coalesce(rep_zvit_tmp.town_other,0) + coalesce(ss.town_other,0),
               village_stove = coalesce(rep_zvit_tmp.village_stove,0) + coalesce(ss.village_stove,0),
	       village_heat  = coalesce(rep_zvit_tmp.village_heat,0)  + coalesce(ss.village_heat,0),
	       village_other = coalesce(rep_zvit_tmp.village_other,0) + coalesce(ss.village_other,0)

    from (select sum(town_stove) as town_stove,sum(town_heat) as town_heat, sum(town_other) as town_other, sum(village_stove) as village_stove, sum(village_heat) as village_heat, sum(village_other) as village_other  from  
    rep_zvit_tmp where mmgg=vmmgg and ident in ('lgt_sum_ng13','lgt_sum_ng14','lgt_sum_ng15') ) as ss
    where rep_zvit_tmp.mmgg=vmmgg and rep_zvit_tmp.ident = 'lgt_sum_ng02';


    update rep_zvit_tmp
               set town_stove = coalesce(rep_zvit_tmp.town_stove,0) + coalesce(ss.town_stove,0),
	       town_heat  = coalesce(rep_zvit_tmp.town_heat,0)  + coalesce(ss.town_heat,0),
	       town_other = coalesce(rep_zvit_tmp.town_other,0) + coalesce(ss.town_other,0),
               village_stove = coalesce(rep_zvit_tmp.village_stove,0) + coalesce(ss.village_stove,0),
	       village_heat  = coalesce(rep_zvit_tmp.village_heat,0)  + coalesce(ss.village_heat,0),
	       village_other = coalesce(rep_zvit_tmp.village_other,0) + coalesce(ss.village_other,0)

    from (select sum(town_stove) as town_stove,sum(town_heat) as town_heat, sum(town_other) as town_other, sum(village_stove) as village_stove, sum(village_heat) as village_heat, sum(village_other) as village_other  from  
    rep_zvit_tmp where mmgg=vmmgg and ident in ('lgt_sum_ng16') ) as ss
    where rep_zvit_tmp.mmgg=vmmgg and rep_zvit_tmp.ident = 'lgt_sum_ng03';


    update rep_zvit_tmp
               set town_stove = coalesce(rep_zvit_tmp.town_stove,0) + coalesce(ss.town_stove,0),
	       town_heat  = coalesce(rep_zvit_tmp.town_heat,0)  + coalesce(ss.town_heat,0),
	       town_other = coalesce(rep_zvit_tmp.town_other,0) + coalesce(ss.town_other,0),
               village_stove = coalesce(rep_zvit_tmp.village_stove,0) + coalesce(ss.village_stove,0),
	       village_heat  = coalesce(rep_zvit_tmp.village_heat,0)  + coalesce(ss.village_heat,0),
	       village_other = coalesce(rep_zvit_tmp.village_other,0) + coalesce(ss.village_other,0)

    from (select sum(town_stove) as town_stove,sum(town_heat) as town_heat, sum(town_other) as town_other, sum(village_stove) as village_stove, sum(village_heat) as village_heat, sum(village_other) as village_other  from  
    rep_zvit_tmp where mmgg=vmmgg and ident in ('lgt_sum_ng17') ) as ss
    where rep_zvit_tmp.mmgg=vmmgg and rep_zvit_tmp.ident = 'lgt_sum_ng04';

    ----

    delete from rep_zvit_tmp where  
    (ident ~'11' or ident ~'12' or ident ~'13' or ident ~'14' or ident ~'15' or ident ~'16' or ident ~'17' or ident ~'1m') and (ident !~'saldo')
    and mmgg=vmmgg;



-------------------------------------------------

update rep_zvit_tmp set town_all =coalesce(town_stove,0)+coalesce(town_heat,0)+coalesce(town_other,0)
where mmgg=vmmgg and town_all is null;

update rep_zvit_tmp set village_all =coalesce(village_stove,0)+coalesce(village_heat,0)+coalesce(village_other,0)
where mmgg=vmmgg and village_all is null;

update rep_zvit_tmp set sum_all = village_all +town_all
where mmgg=vmmgg and sum_all is null;

-- В МЕМ нет негазифицированных, уберем чтобы не занимали место
vdel_ng:=0;
select into vdel_ng to_number(value_ident,'9') from syi_sysvars_tbl where ident='rep_ng';

if vdel_ng=0 then 

 delete from rep_zvit_tmp where mmgg=vmmgg and ident ~ '_ng';

end if;


 ------------------avg calc data --------------------------------------------------------

 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, sum_all, town_all,town_stove,town_heat, town_other, 
                           village_all,village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment,'bill_tar_avg1','- - Повний середньовiдпуск. тариф','3_01','коп/кВтг',
  CASE WHEN coalesce(z2.sum_all,0)<>0 THEN round(z1.sum_all::numeric*100/z2.sum_all,2)::numeric END,
  CASE WHEN coalesce(z2.town_all,0)<>0 THEN round(z1.town_all::numeric*100/z2.town_all,2)::numeric  END,
  CASE WHEN coalesce(z2.town_stove,0)<>0 THEN round(z1.town_stove::numeric*100/z2.town_stove,2)::numeric END,
  CASE WHEN coalesce(z2.town_heat,0)<>0 THEN round(z1.town_heat::numeric*100/ z2.town_heat,2)::numeric END,
  CASE WHEN coalesce(z2.town_other,0)<>0 THEN round(z1.town_other::numeric*100/z2. town_other,2)::numeric END,
  CASE WHEN coalesce(z2.village_all,0)<>0 THEN round(z1.village_all::numeric*100/z2.village_all,2)::numeric END,
  CASE WHEN coalesce(z2.village_stove,0)<>0 THEN round(z1.village_stove::numeric*100/ z2.village_stove,2)::numeric END,
  CASE WHEN coalesce(z2.village_heat,0)<>0 THEN round(z1.village_heat::numeric*100/ z2.village_heat,2)::numeric END,
  CASE WHEN coalesce(z2.village_other,0)<>0 THEN round(z1.village_other::numeric*100/z2.village_other,2)::numeric END,
  1, 1, vmmgg
 from rep_zvit_tmp as z1 , rep_zvit_tmp as z2 
 where z1.mmgg = vmmgg and z1.ident ='tovar_sum' and z2.mmgg = vmmgg and z2.ident ='bill_dem';



 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, sum_all, town_all,town_stove,town_heat, town_other, 
                           village_all,village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment,'bill_tar_avg2','- - Середньовiдпускний тариф','5_92','коп/кВтг',
  CASE WHEN coalesce(z2.sum_all,0)<>0 THEN round(z1.sum_all::numeric*100/z2.sum_all,2)::numeric END,
  CASE WHEN coalesce(z2.town_all,0)<>0 THEN round(z1.town_all::numeric*100/z2.town_all,2)::numeric  END,
  CASE WHEN coalesce(z2.town_stove,0)<>0 THEN round(z1.town_stove::numeric*100/z2.town_stove,2)::numeric END,
  CASE WHEN coalesce(z2.town_heat,0)<>0 THEN round(z1.town_heat::numeric*100/ z2.town_heat,2)::numeric END,
  CASE WHEN coalesce(z2.town_other,0)<>0 THEN round(z1.town_other::numeric*100/z2. town_other,2)::numeric END,
  CASE WHEN coalesce(z2.village_all,0)<>0 THEN round(z1.village_all::numeric*100/z2.village_all,2)::numeric END,
  CASE WHEN coalesce(z2.village_stove,0)<>0 THEN round(z1.village_stove::numeric*100/ z2.village_stove,2)::numeric END,
  CASE WHEN coalesce(z2.village_heat,0)<>0 THEN round(z1.village_heat::numeric*100/ z2.village_heat,2)::numeric END,
  CASE WHEN coalesce(z2.village_other,0)<>0 THEN round(z1.village_other::numeric*100/z2.village_other,2)::numeric END,
  1, 1, vmmgg
 from rep_zvit_tmp as z1 , rep_zvit_tmp as z2 
 where z1.mmgg = vmmgg and z1.ident ='bill_sum' and z2.mmgg = vmmgg and z2.ident ='bill_all_dem';



 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, sum_all, town_all,town_stove,town_heat, town_other, 
                           village_all,village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment,'avg_bill_dem','- - Середнє спожив. на 1 абонента','5_93','кВтг',
  CASE WHEN coalesce(z2.sum_all,0)<>0 THEN round(z1.sum_all::numeric/z2.sum_all,0)::int END,
  CASE WHEN coalesce(z2.town_all,0)<>0 THEN round(z1.town_all::numeric/z2.town_all,0)::int  END,
  CASE WHEN coalesce(z2.town_stove,0)<>0 THEN round(z1.town_stove::numeric/z2.town_stove,0)::int END,
  CASE WHEN coalesce(z2.town_heat,0)<>0 THEN round(z1.town_heat::numeric/ z2.town_heat,0)::int END,
  CASE WHEN coalesce(z2.town_other,0)<>0 THEN round(z1.town_other::numeric/z2. town_other,0)::int END,
  CASE WHEN coalesce(z2.village_all,0)<>0 THEN round(z1.village_all::numeric/z2.village_all,0)::int END,
  CASE WHEN coalesce(z2.village_stove,0)<>0 THEN round(z1.village_stove::numeric/ z2.village_stove,0)::int END,
  CASE WHEN coalesce(z2.village_heat,0)<>0 THEN round(z1.village_heat::numeric/ z2.village_heat,0)::int END,
  CASE WHEN coalesce(z2.village_other,0)<>0 THEN round(z1.village_other::numeric/z2.village_other,0)::int END,
  1, 1, vmmgg
 from rep_zvit_tmp as z1 , rep_zvit_tmp as z2 
-- where z1.mmgg = vmmgg and z1.ident ='bill_all_dem' and z2.mmgg = vmmgg and z2.ident ='bill_all_cnt';
 where z1.mmgg = vmmgg and z1.ident ='bill_all_dem' and z2.mmgg = vmmgg and z2.ident ='acc_count';


 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, sum_all, town_all,town_stove,town_heat, town_other, 
                           village_all,village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment,'avg_bill_sum','- - Середнє спожив. на 1 абонента','5_94','грн.',
  CASE WHEN coalesce(z2.sum_all,0)<>0 THEN round(z1.sum_all::numeric/z2.sum_all,2)::numeric END,
  CASE WHEN coalesce(z2.town_all,0)<>0 THEN round(z1.town_all::numeric/z2.town_all,2)::numeric  END,
  CASE WHEN coalesce(z2.town_stove,0)<>0 THEN round(z1.town_stove::numeric/z2.town_stove,2)::numeric END,
  CASE WHEN coalesce(z2.town_heat,0)<>0 THEN round(z1.town_heat::numeric/ z2.town_heat,2)::numeric END,
  CASE WHEN coalesce(z2.town_other,0)<>0 THEN round(z1.town_other::numeric/z2. town_other,2)::numeric END,
  CASE WHEN coalesce(z2.village_all,0)<>0 THEN round(z1.village_all::numeric/z2.village_all,2)::numeric END,
  CASE WHEN coalesce(z2.village_stove,0)<>0 THEN round(z1.village_stove::numeric/ z2.village_stove,2)::numeric END,
  CASE WHEN coalesce(z2.village_heat,0)<>0 THEN round(z1.village_heat::numeric/ z2.village_heat,2)::numeric END,
  CASE WHEN coalesce(z2.village_other,0)<>0 THEN round(z1.village_other::numeric/z2.village_other,2)::numeric END,
  1, 1, vmmgg
 from rep_zvit_tmp as z1 , rep_zvit_tmp as z2 
-- where z1.mmgg = vmmgg and z1.ident ='bill_sum' and z2.mmgg = vmmgg and z2.ident ='bill_all_cnt';
 where z1.mmgg = vmmgg and z1.ident ='bill_sum' and z2.mmgg = vmmgg and z2.ident ='acc_count';



 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, sum_all, town_all, town_stove,town_heat, town_other, 
                           village_all,village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment,'pay_proc','- Процент оплати','7_5','%(шт)',
  CASE WHEN coalesce(z2.sum_all,0)<>0 THEN round(z1.sum_all::numeric*100/z2.sum_all,2)::numeric END,
  CASE WHEN coalesce(z2.town_all,0)<>0 THEN round(z1.town_all::numeric*100/z2.town_all,2)::numeric  END,
  CASE WHEN coalesce(z2.town_stove,0)<>0 THEN round(z1.town_stove::numeric*100/z2.town_stove,2)::numeric END,
  CASE WHEN coalesce(z2.town_heat,0)<>0 THEN round(z1.town_heat::numeric*100/ z2.town_heat,2)::numeric END,
  CASE WHEN coalesce(z2.town_other,0)<>0 THEN round(z1.town_other::numeric*100/z2. town_other,2)::numeric END,
  CASE WHEN coalesce(z2.village_all,0)<>0 THEN round(z1.village_all::numeric*100/z2.village_all,2)::numeric END,
  CASE WHEN coalesce(z2.village_stove,0)<>0 THEN round(z1.village_stove::numeric*100/ z2.village_stove,2)::numeric END,
  CASE WHEN coalesce(z2.village_heat,0)<>0 THEN round(z1.village_heat::numeric*100/ z2.village_heat,2)::numeric END,
  CASE WHEN coalesce(z2.village_other,0)<>0 THEN round(z1.village_other::numeric*100/z2.village_other,2)::numeric END,
  1, 1, vmmgg
 from rep_zvit_tmp as z1 , rep_zvit_tmp as z2 
 where z1.mmgg = vmmgg and z1.ident ='pay_count' and z2.mmgg = vmmgg and z2.ident ='bill_all_cnt';


 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, sum_all, town_all, town_stove,town_heat, town_other, 
                           village_all,village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment,'pay_proc_sum','- Процент оплати','7_51','%(грн.)',
  CASE WHEN coalesce(z2.sum_all,0)<>0 THEN round(z1.sum_all::numeric*100/z2.sum_all,2)::numeric END,
  CASE WHEN coalesce(z2.town_all,0)<>0 THEN round(z1.town_all::numeric*100/z2.town_all,2)::numeric  END,
  CASE WHEN coalesce(z2.town_stove,0)<>0 THEN round(z1.town_stove::numeric*100/z2.town_stove,2)::numeric END,
  CASE WHEN coalesce(z2.town_heat,0)<>0 THEN round(z1.town_heat::numeric*100/ z2.town_heat,2)::numeric END,
  CASE WHEN coalesce(z2.town_other,0)<>0 THEN round(z1.town_other::numeric*100/z2. town_other,2)::numeric END,
  CASE WHEN coalesce(z2.village_all,0)<>0 THEN round(z1.village_all::numeric*100/z2.village_all,2)::numeric END,
  CASE WHEN coalesce(z2.village_stove,0)<>0 THEN round(z1.village_stove::numeric*100/ z2.village_stove,2)::numeric END,
  CASE WHEN coalesce(z2.village_heat,0)<>0 THEN round(z1.village_heat::numeric*100/ z2.village_heat,2)::numeric END,
  CASE WHEN coalesce(z2.village_other,0)<>0 THEN round(z1.village_other::numeric*100/z2.village_other,2)::numeric END,
  1, 1, vmmgg
 from rep_zvit_tmp as z1 , rep_zvit_tmp as z2 
 where z1.mmgg = vmmgg and z1.ident ='pay_sum' and z2.mmgg = vmmgg and z2.ident ='bill_sum';




 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, sum_all, 
                           town_all, town_stove,town_heat, town_other, 
                           village_all,village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment,'checksum_bill','- - Контроль рах.','ff_1','грн.',
  coalesce(z1.sum_all,0)-coalesce(z2.sum_all,0)-coalesce(z3.sum_all,0)-coalesce(z4.sum_all,0) -coalesce(z5.sum_all,0),
  coalesce(z1.town_all,0)-coalesce(z2.town_all,0)-coalesce(z3.town_all,0)-coalesce(z4.town_all,0) -coalesce(z5.town_all,0),
  coalesce(z1.town_stove,0)-coalesce(z2.town_stove,0)-coalesce(z3.town_stove,0)-coalesce(z4.town_stove,0) -coalesce(z5.town_stove,0),
  coalesce(z1.town_heat,0)-coalesce(z2.town_heat,0)-coalesce(z3.town_heat,0)-coalesce(z4.town_heat,0) -coalesce(z5.town_heat,0),
  coalesce(z1.town_other,0)-coalesce(z2.town_other,0)-coalesce(z3.town_other,0)-coalesce(z4.town_other,0) -coalesce(z5.town_other,0),
  coalesce(z1.village_all,0)-coalesce(z2.village_all,0)-coalesce(z3.village_all,0)-coalesce(z4.village_all,0) -coalesce(z5.village_all,0),
  coalesce(z1.village_stove,0)-coalesce(z2.village_stove,0)-coalesce(z3.village_stove,0)-coalesce(z4.village_stove,0) -coalesce(z5.village_stove,0),
  coalesce(z1.village_heat,0)-coalesce(z2.village_heat,0)-coalesce(z3.village_heat,0)-coalesce(z4.village_heat,0) -coalesce(z5.village_heat,0),
  coalesce(z1.village_other,0)-coalesce(z2.village_other,0)-coalesce(z3.village_other,0)-coalesce(z4.village_other,0) -coalesce(z5.village_other,0),
  1, 1, vmmgg
 from rep_zvit_tmp as z1 , rep_zvit_tmp as z2 , rep_zvit_tmp as z3 , rep_zvit_tmp as z4 , rep_zvit_tmp as z5 
 where z1.mmgg = vmmgg and z1.ident ='tovar_sum' 
   and z2.mmgg = vmmgg and z2.ident ='bill_sum'
   and z3.mmgg = vmmgg and z3.ident ='lgt_sum'
   and z4.mmgg = vmmgg and z4.ident ='subs_all'
   and z5.mmgg = vmmgg and z5.ident ='lgt_sum_ps';


 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, sum_all, 
                           town_all, town_stove,town_heat, town_other, 
                           village_all,village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment,'checksum_billsum','- - Контроль суми виписано рах.','ff_11','грн.',
  coalesce(z1.sum_all,0)-coalesce(ss.sum_all,0),
  coalesce(z1.town_all,0)-coalesce(ss.town_all,0),
  coalesce(z1.town_stove,0)-coalesce(ss.town_stove,0),
  coalesce(z1.town_heat,0)-coalesce(ss.town_heat,0),
  coalesce(z1.town_other,0)-coalesce(ss.town_other,0),
  coalesce(z1.village_all,0)-coalesce(ss.village_all,0),
  coalesce(z1.village_stove,0)-coalesce(ss.village_stove,0),
  coalesce(z1.village_heat,0)-coalesce(ss.village_heat,0),
  coalesce(z1.village_other,0)-coalesce(ss.village_other,0),
  1, 1, vmmgg
 from rep_zvit_tmp as z1 
    ,(select sum(sum_all) as sum_all , sum(town_all) as town_all,  
     sum(town_stove) as town_stove,sum(town_heat) as town_heat, sum(town_other) as town_other, 
     sum(village_all) as village_all, sum(village_stove) as village_stove, sum(village_heat) as village_heat, sum(village_other) as village_other  
     from  rep_zvit_tmp where mmgg=vmmgg and ident ~ '^bill_sum' and ident not in ('bill_sum','bill_sum_e','bill_sum_tax') ) as ss
 where z1.mmgg = vmmgg and z1.ident ='bill_sum'; 



 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, sum_all, 
                           town_all, town_stove,town_heat, town_other, 
                           village_all,village_stove, village_heat, village_other, gr_lvl, part_code, mmgg)
 select vdepartment,'checksum_m','- - Контроль багатодiтних.','ff_12','грн.',
  coalesce(z1.sum_all,0)-coalesce(z2.sum_all,0)-coalesce(z3.sum_all,0)-coalesce(z4.sum_all,0) ,
  coalesce(z1.town_all,0)-coalesce(z2.town_all,0)-coalesce(z3.town_all,0)-coalesce(z4.town_all,0) ,
  coalesce(z1.town_stove,0)-coalesce(z2.town_stove,0)-coalesce(z3.town_stove,0)-coalesce(z4.town_stove,0) ,
  coalesce(z1.town_heat,0)-coalesce(z2.town_heat,0)-coalesce(z3.town_heat,0)-coalesce(z4.town_heat,0) ,
  coalesce(z1.town_other,0)-coalesce(z2.town_other,0)-coalesce(z3.town_other,0)-coalesce(z4.town_other,0) ,
  coalesce(z1.village_all,0)-coalesce(z2.village_all,0)-coalesce(z3.village_all,0)-coalesce(z4.village_all,0) ,
  coalesce(z1.village_stove,0)-coalesce(z2.village_stove,0)-coalesce(z3.village_stove,0)-coalesce(z4.village_stove,0) ,
  coalesce(z1.village_heat,0)-coalesce(z2.village_heat,0)-coalesce(z3.village_heat,0)-coalesce(z4.village_heat,0) ,
  coalesce(z1.village_other,0)-coalesce(z2.village_other,0)-coalesce(z3.village_other,0)-coalesce(z4.village_other,0) ,
  1, 1, vmmgg
 from rep_zvit_tmp as z1 , rep_zvit_tmp as z2 , rep_zvit_tmp as z3 , rep_zvit_tmp as z4 
 where z1.mmgg = vmmgg and z1.ident ='tovar_sum_0m' 
   and z2.mmgg = vmmgg and z2.ident ='bill_sum_0m'
   and z3.mmgg = vmmgg and z3.ident ='lgt_sum_0m'
   and z4.mmgg = vmmgg and z4.ident ='subs_m';



 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, sum_all,
  town_all, town_stove,town_heat, town_other, village_all,village_stove, village_heat, village_other,
  gr_lvl, part_code, mmgg)
 select vdepartment,'checksum_saldo','- - Контроль сальдо','ff_2','грн.',
  coalesce(z1.sum_all,0)-coalesce(z2.sum_all,0)+coalesce(z3.sum_all,0)-coalesce(z4.sum_all,0)/*-coalesce(z5.sum_all,0)*/+coalesce(z6.sum_all,0)-coalesce(z7.sum_all,0),
  coalesce(z1.town_all,0)-coalesce(z2.town_all,0)+coalesce(z3.town_all,0)-coalesce(z4.town_all,0)/*-coalesce(z5.town_all,0)*/+coalesce(z6.town_all,0)-coalesce(z7.town_all,0),
  coalesce(z1.town_stove,0)-coalesce(z2.town_stove,0)+coalesce(z3.town_stove,0)-coalesce(z4.town_stove,0)/*-coalesce(z5.town_stove,0)*/+coalesce(z6.town_stove,0)-coalesce(z7.town_stove,0),
  coalesce(z1.town_heat,0)-coalesce(z2.town_heat,0)+coalesce(z3.town_heat,0)-coalesce(z4.town_heat,0)/*-coalesce(z5.town_heat,0)*/+coalesce(z6.town_heat,0)-coalesce(z7.town_heat,0),
  coalesce(z1.town_other,0)-coalesce(z2.town_other,0)+coalesce(z3.town_other,0)-coalesce(z4.town_other,0)/*-coalesce(z5.town_other,0)*/+coalesce(z6.town_other,0)-coalesce(z7.town_other,0),
  coalesce(z1.village_all,0)-coalesce(z2.village_all,0)+coalesce(z3.village_all,0)-coalesce(z4.village_all,0)/*-coalesce(z5.village_all,0)*/+coalesce(z6.village_all,0)-coalesce(z7.village_all,0),
  coalesce(z1.village_stove,0)-coalesce(z2.village_stove,0)+coalesce(z3.village_stove,0)-coalesce(z4.village_stove,0)/*-coalesce(z5.village_stove,0)*/+coalesce(z6.village_stove,0)-coalesce(z7.village_stove,0),
  coalesce(z1.village_heat,0)-coalesce(z2.village_heat,0)+coalesce(z3.village_heat,0)-coalesce(z4.village_heat,0)/*-coalesce(z5.village_heat,0)*/+coalesce(z6.village_heat,0)-coalesce(z7.village_heat,0),
  coalesce(z1.village_other,0)-coalesce(z2.village_other,0)+coalesce(z3.village_other,0)-coalesce(z4.village_other,0)/*-coalesce(z5.village_other,0)*/+coalesce(z6.village_other,0)-coalesce(z7.village_other,0),
  1, 1, vmmgg
 from rep_zvit_tmp as z1 , rep_zvit_tmp as z2 , rep_zvit_tmp as z3 , rep_zvit_tmp as z4 ,/* rep_zvit_tmp as z5 ,*/ rep_zvit_tmp as z6, rep_zvit_tmp as z7  
 where z1.mmgg = vmmgg and z1.ident ='saldo_b_dt' 
   and z2.mmgg = vmmgg and z2.ident ='saldo_b_kt'
   and z3.mmgg = vmmgg and z3.ident ='bill_sum'
   and z4.mmgg = vmmgg and z4.ident ='pay_sum'
--   and z5.mmgg = vmmgg and z5.ident ='pay_corsum'
   and z6.mmgg = vmmgg and z6.ident ='saldo_e_kt'
   and z7.mmgg = vmmgg and z7.ident ='saldo_e_dt';


 insert into rep_zvit_tmp (id_dep,ident, caption, num, unit, sum_all,
  town_all, town_stove,town_heat, town_other, village_all,village_stove, village_heat, village_other,
  gr_lvl, part_code, mmgg)
 select vdepartment,'checksum_nds','- - Контроль ПДВ','ff_3','грн.',
  coalesce(z1.sum_all,0)-coalesce(z2.sum_all,0)+coalesce(z3.sum_all,0)-coalesce(z4.sum_all,0)/*-coalesce(round(z5.sum_all/6,2),0)*/+coalesce(z6.sum_all,0)-coalesce(z7.sum_all,0),
  coalesce(z1.town_all,0)-coalesce(z2.town_all,0)+coalesce(z3.town_all,0)-coalesce(z4.town_all,0)/*-coalesce(round(z5.town_all/6,2),0)*/+coalesce(z6.town_all,0)-coalesce(z7.town_all,0),
  coalesce(z1.town_stove,0)-coalesce(z2.town_stove,0)+coalesce(z3.town_stove,0)-coalesce(z4.town_stove,0)/*-coalesce(round(z5.town_stove/6,2),0)*/+coalesce(z6.town_stove,0)-coalesce(z7.town_stove,0),
  coalesce(z1.town_heat,0)-coalesce(z2.town_heat,0)+coalesce(z3.town_heat,0)-coalesce(z4.town_heat,0)/*-coalesce(round(z5.town_heat/6,2),0)*/+coalesce(z6.town_heat,0)-coalesce(z7.town_heat,0),
  coalesce(z1.town_other,0)-coalesce(z2.town_other,0)+coalesce(z3.town_other,0)-coalesce(z4.town_other,0)/*-coalesce(round(z5.town_other/6,2),0)*/+coalesce(z6.town_other,0)-coalesce(z7.town_other,0),
  coalesce(z1.village_all,0)-coalesce(z2.village_all,0)+coalesce(z3.village_all,0)-coalesce(z4.village_all,0)/*-coalesce(round(z5.village_all/6,2),0)*/+coalesce(z6.village_all,0)-coalesce(z7.village_all,0),
  coalesce(z1.village_stove,0)-coalesce(z2.village_stove,0)+coalesce(z3.village_stove,0)-coalesce(z4.village_stove,0)/*-coalesce(round(z5.village_stove/6,2),0)*/+coalesce(z6.village_stove,0)-coalesce(z7.village_stove,0),
  coalesce(z1.village_heat,0)-coalesce(z2.village_heat,0)+coalesce(z3.village_heat,0)-coalesce(z4.village_heat,0)/*-coalesce(round(z5.village_heat/6,2),0)*/+coalesce(z6.village_heat,0)-coalesce(z7.village_heat,0),
  coalesce(z1.village_other,0)-coalesce(z2.village_other,0)+coalesce(z3.village_other,0)-coalesce(z4.village_other,0)/*-coalesce(round(z5.village_other/6,2),0)*/+coalesce(z6.village_other,0)-coalesce(z7.village_other,0),
  1, 1, vmmgg
 from rep_zvit_tmp as z1 , rep_zvit_tmp as z2 , rep_zvit_tmp as z3 , rep_zvit_tmp as z4 ,/* rep_zvit_tmp as z5 ,*/ rep_zvit_tmp as z6, rep_zvit_tmp as z7  
 where z1.mmgg = vmmgg and z1.ident ='saldo_b_dt_tax' 
   and z2.mmgg = vmmgg and z2.ident ='saldo_b_kt_tax'
   and z3.mmgg = vmmgg and z3.ident ='bill_sum_tax'
   and z4.mmgg = vmmgg and z4.ident ='pay_sum_tax'
--   and z5.mmgg = vmmgg and z5.ident ='pay_corsum'
   and z6.mmgg = vmmgg and z6.ident ='saldo_e_kt_tax'
   and z7.mmgg = vmmgg and z7.ident ='saldo_e_dt_tax';


  -----------------------------------------------------------------------------------------

  delete from rep_zvit_tbl where mmgg=vmmgg and id_dep = kodres;

  insert into rep_zvit_tbl (
            id_dep, ident, caption, num, unit, sum_all, town_all, town_stove, 
            town_heat, town_other, village_all, village_stove, village_heat, 
            village_other, gr_lvl, part_code, mmgg)

  select    kodres, ident, caption, num, unit, sum_all, town_all, town_stove, 
            town_heat, town_other, village_all, village_stove, village_heat, 
            village_other, gr_lvl, part_code, mmgg 
  from rep_zvit_tmp;



 END; -- end calculation

 

 if (pid_paccnt is null ) and (pid_town is null or (pid_town< 0 and kodres in (320,330) ) )and (kodres not in (241,242))  then

  tabl='/home/local/seb/'||kodres::varchar||'REA.TXT';
  del='@'; nul='0';


  SQL='copy  (select '||kodres::varchar||','''||to_char(vmmgg, 'YYYY-MM-DD') ||''' , s.seb_code, 
    coalesce(sum_all,0), coalesce(town_all,0), coalesce(town_stove,0), coalesce(town_heat,0), coalesce(town_other,0), coalesce(village_all,0),
    coalesce(village_stove,0), coalesce(village_heat,0), coalesce(village_other,0)
    from seb_zvit_lines_tbl as s 
    left join rep_zvit_tbl as z on (z.ident = s.ident and z.id_dep = '||kodres::varchar||'  and z.mmgg = '''||to_char(vmmgg, 'YYYY-MM-DD') || ''' ) order by seb_code)  to '||quote_literal(tabl)||' with delimiter as '||quote_literal(del)||' null as '|| quote_literal(nul);

  raise notice 'SQL - %', SQL;

  Execute SQL;

 end if;


RETURN 0;
end;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION public.rep_zvit_fun(date, integer, integer, integer)
  OWNER TO local;

