do $$ 
	begin
		--
		-- ajoute la colonne univ_city
		-- 
		perform column_name from information_schema.columns where table_name='laboratoires' and column_name='univ_city';
		if not found then
			alter table laboratoires add column univ_city text;
		end if;
		-- 
		perform column_name from information_schema.columns where table_name='laboratoires' and column_name='country';
		if not found then
			alter table laboratoires add column country char(2);
		end if;
		-- modifies la vue liste_villes
		create or replace view liste_villes as
			select distinct cities.city as key, cities.city as value 
				from 
					(select case 
						when laboratoires.univ_city is null then laboratoires.city 
						else laboratoires.univ_city end 
						as city 
					from laboratoires) 
				as cities order by cities.city;
		grant select (id, email) on users to stagesweb;
	end;
$$;
