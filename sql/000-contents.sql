--******************************************************************************
--
-- Gestion des offres de stage de M2 en Astro
-- (c) Raphaël Jacquot 2011
-- Fichier sous licence GPL-3
--
--*****************************************************************************/


insert into type_offre (code, description, denom_prop, denom_dir, has_year) values 
       ('MR', 'Master Recherche', 'Stages', 'Directeur de Stage', true);

insert into m2 (short_desc, description, ville, url_logo) values ('A2P',		'Astrophysique, Plasmas et Planètes',											'Grenoble',		'/images/logos-m2/ujf-phitem-ipag-h86.png');
insert into m2 (short_desc, description, ville, url_logo) values ('DSG&Aphi',	'Astronomie et Astrophysique',													'Paris',		'/images/logos-m2/aphi-dsg.h86.png');
insert into m2 (short_desc, description, ville, url_logo) values ('Planeto',	'Planétologie IDF',																'Paris',		'');
insert into m2 (short_desc, description, ville, url_logo) values ('SAE',		'Sciences de l''Atmosphere et de l''Espace',									'Orléans',		'');
insert into m2 (short_desc, description, ville, url_logo) values ('Astro67',	'Astrophysique UNISTRA',														'Strasbourg',	'/images/logos-m2/unistra-obs-h86.png');
insert into m2 (short_desc, description, ville, url_logo) values ('ASEP',		'Astrophysique, Sciences de l''Espace, Planétologie',							'Toulouse',		'/images/logos-m2/uni-tls-paul-sabatier-h86.2013.jpg');
--insert into m2 (short_desc, description, ville, url_logo) values ('TSI',		'Techniques Spatiales et Instrumentation',										'Toulouse',		'');
insert into m2 (short_desc, description, ville, url_logo) values ('IMAG2E',		'Ingénierie et Modélisation Géophysique Espace et Environnement',				'Nice',			'/images/logos-m2/unice-lagrange-imag2e-oca-h86.png');
insert into m2 (short_desc, description, ville, url_logo) values ('P3TMA',		'Physique Théorique et Mathématique, Physique des Particules et Astrophyque',	'Marseille',	'/images/logos-m2/aix-marseille-fac-sciences-h86.png');
insert into m2 (short_desc, description, ville, url_logo) values ('APC',		'Astrophysique et Physique Corpusculaire',										'Bordeaux',		'/images/logos-m2/lab-h86.png');
insert into m2 (short_desc, description, ville, url_logo) values ('PF',			'Physique Fondamentale',														'Lyon',			'/images/logos-m2/CRA-ENSLyon-UCL_L1-ObsLyon-h86.png');
insert into m2 (short_desc, description, ville, url_logo) values ('CCP',		'Cosmos, Champs et Particules',													'Montpellier',	'/images/logos-m2/montpellier-ccp-h86.png');


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
