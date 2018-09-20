su postgres -c "psql -d stages_prod -c '\i 005-config-vars.sql'"
su postgres -c "psql -d stages_prod -c '\i 006-config.sql'"
