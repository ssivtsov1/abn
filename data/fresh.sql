select t.*,s.name,p.represent_name from ind_pack_header t
join prs_runner_sectors as s on (s.id = t.id_sector)
left join prs_persons as p on (p.id = t.id_runner)


left join act_plan_cache_tbl as p4 on (trim(sww.name) = trim(p4.sector) and p4.year=Extract(YEAR from $1) and p4.month=Extract(MONTH from $1))
