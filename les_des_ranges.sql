DROP TABLE IF EXISTS ADHERENT CASCADE;

CREATE OR REPLACE PROCEDURAL LANGUAGE plpgsql;

CREATE TABLE ADHERENT (
uuidAdherent VARCHAR(60) NOT NULL,
nom VARCHAR(50) NOT NULL,
prenom VARCHAR(50) NOT NULL,
date_naissance DATE,
mail VARCHAR(100) NOT NULL,
date_premiere_cotisation DATE NOT NULL,
date_derniere_cotisation DATE NOT NULL,
telephone VARCHAR(16),
type_adhesion VARCHAR(20) NOT NULL,
personnes_rattachees VARCHAR(255),
autre VARCHAR(255),
date_creation timestamp NOT NULL,
date_modification timestamp NOT NULL,
PRIMARY KEY (uuidADHERENT)
);
