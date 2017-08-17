\set VERBOSITY terse
create extension pgcrypto;
\c stages_prod stagesadm
\i /srv/webs/stages/prod/sql/000-base.sql
--\i /srv/webs/stages/prod/sql/000-contents.sql
\i /srv/webs/stages/prod/sql/001-messages.sql
\i /srv/webs/stages/prod/sql/002-passwords.sql
\i /srv/webs/stages/prod/sql/003-villes.sql


