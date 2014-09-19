do $$
	begin
		--
		-- check if we have a country table
		-- 
		perform table_name from information_schema.tables where table_schema='public' and table_type='BASE TABLE' and table_name='countries';
		if not found then
			raise info 'table countries not found, creating';
			-- table was not found. create it and then add some values
			-- create the table
			create table countries (
				iso2	char(2),
				name	text
			); 
			create unique index pk__countries__iso2 on countries ( iso2 );
			alter table countries add primary key using index pk__countries__iso2;
			
			grant select on countries to stagesweb;

			-- insert some countries
			raise info 'adding stuff in table countries';
			insert into countries ( iso2, name ) values
				('CH', 'Suisse' ),
				('CL', 'Chili' ),
				('DE', 'Allemagne' ),
				('ES', 'Espagne' ),
				('FR', 'France' ),
				('GB', 'Grande Bretagne' ),
				('IT', 'Italie' );
		end if;
	end;
$$;

--
-- add the visible column to laboratoires
do $$
	begin
		-- add visible column to laboratoires
		-- by default, make them all visible
        perform column_name from information_schema.columns where table_name='laboratoires' and column_name='visible';
        if not found then
            alter table laboratoires add column visible boolean default true;
        end if;
	
	end;
$$;

--
-- create or upgrade views
-- this can be run every time, it just updates to the latest version of the view
-- 
do $$
	begin
		-- view for list of countries
		raise info 'updating liste_pays';
		create or replace view liste_pays as
			select iso2 as key, name as value from countries order by value;

		grant select on liste_pays to stagesweb;

		-- liste des laboratoires
		raise info 'updating liste_labos';
		create or replace view liste_labos as select id as key,
			case when ((sigle is not null) and (char_length(sigle)>0)) then sigle || ' - ' else '' end || description as value from laboratoires
			where visible=true
			order by value;

	end;
$$;
