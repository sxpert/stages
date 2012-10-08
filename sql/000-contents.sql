--******************************************************************************
--
-- Gestion des offres de stage de M2 en Astro
-- (c) Raphaël Jacquot 2011
-- Fichier sous licence GPL-3
--
--*****************************************************************************/


insert into type_offre (code, description, denom_prop, denom_dir, has_year) values 
       ('MR', 'Master Recherche', 'Stages', 'Directeur de Stage', true);

insert into m2 (short_desc, description, ville) values ('A2P', 'Astrophysique, Plasmas et Planètes', 'Grenoble');
insert into m2 (short_desc, description, ville) values ('Astro75', 'Astrophysique', 'Paris');
insert into m2 (short_desc, description, ville) values ('DSG', 'Dynamique des Systèmes Gravitationnels', 'Paris');
insert into m2 (short_desc, description, ville) values ('OSAE', 'Outils et Systèmes de l''Astronomie de de l''Espace', 'Paris');
insert into m2 (short_desc, description, ville) values ('Astro67', 'Astrophysique UNISTRA', 'Strasbourg');
insert into m2 (short_desc, description, ville) values ('ASEP', 'Astrophysique, Sciences de l''Espace, Planétologie', 'Toulouse');
insert into m2 (short_desc, description, ville) values ('TSI', 'Techniques Spatiales et Instrumentation', 'Toulouse');
insert into m2 (short_desc, description, ville) values ('OMEGA', 'Optique, Dynamique, Images, Astrophysique', 'Nice');
insert into m2 (short_desc, description, ville) values ('AER', 'Astrophysique, Energie, Rayonnement', 'Marseille');
insert into m2 (short_desc, description, ville) values ('APC', 'Astrophysique et Physique Corpusculaire', 'Bordeaux');
insert into m2 (short_desc, description, ville) values ('PF', 'Physique Fondamentale', 'Lyon');

insert into categories(description) values 
       ('Cosmologie, Univers primordial, origine et évolution des grandes structures de l''Univers et des galaxies'),
       ('Astrophysique des hautes énergies, objets compacts, astroparticules, ondes gravitationnelles'),
       ('Physique et chimie des milieux interstellaires et circumstellaires'),
       ('Formation, structure et évolution des étoiles'),
       ('Exoplanetes: origine, structure et évolution des systèmes planétaires, planétologie comparée'),
       ('Système solaire: origine, composition, évolution, structure physico-chimique et dynamique de ses objets et des astromateriaux; cosmochimie'),
       ('Physique du soleil et de l''héliosphere'),
       ('Planétologie : physique, dynamique et chimie des atmosphères planétaires'),
       ('Géophysique : Terre, atmosphère, ionosphère, magnétosphère'),
       ('Processus physiques en astrophysique'),
       ('Systèmes de référence spatio-temporels'),
       ('Instrumentation pour les grands observatoires au sol et dans l''espace'),
       ('Autres');

insert into statuts (description) values
       ('Chercheur ou enseignant-chercheur en poste'),
       ('Ingénieur de recherche'),
       ('Chercheur en contrat post-doctoral');

insert into nature_stage values 
       (1, 'Instrumentation'),
       (2, 'Observations'),
       (3, 'Simulations numériques'),
       (4, 'Traitement de données'),
       (5, 'Modélisation'),
       (6, 'Expérimentation en laboratoire');

insert into pay_states values
       ( 1, 'Acquise'),
       ( 2, 'En cours de négociation');
