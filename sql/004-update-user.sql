--******************************************************************************
--
-- Gestion des offres de stage de M2 en Astro
-- (c) Raphaël Jacquot 2017
-- Fichier sous licence GPL-3
--
--******************************************************************************

--
-- Ajout d'une fonction pour modifier les informations de l'utilisateur
-- 

-- \c stages stagesadm

drop function if exists user_modify (text, text, text, text, bigint);
create or replace function user_modify (t_uid bigint, t_fname text, 
                                        t_lname text, t_email text, 
                                        t_phone text, t_id_labo bigint) returns boolean as $$
  begin
    update users set f_name = t_fname, l_name = t_lname, email = t_email, phone = t_phone, id_laboratoire = t_id_labo
           where id = t_uid;
    return 1;
  end;
$$ language plpgsql security definer; 
