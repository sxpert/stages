do $$
	begin
		--
		-- upgrade 1 :
		-- table des pays
		-- 
		perform table_name from information_schema.tables where table_schema='public' and table_type='BASE TABLE' and table_name='countries';
		if not found then
			-- create the table
			create table countries (
				iso2	char(2),
				name	text
			); 
			create unique index pk__countries__iso2 on countries ( iso2 );
			alter table countries add primary key using index pk__countries__iso2;
			
			grant select on countries to stagesweb;

			-- insert some countries
		
			insert into countries ( iso2, names ) values
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
