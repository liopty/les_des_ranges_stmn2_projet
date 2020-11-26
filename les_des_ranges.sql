DROP TABLE IF EXISTS JEUX CASCADE;
DROP TABLE IF EXISTS ADHERENT CASCADE;
DROP TABLE IF EXISTS EMPRUNT CASCADE;
DROP TABLE IF EXISTS CONSOMMABLES CASCADE;
DROP TABLE IF EXISTS VENTE CASCADE;
DROP TABLE IF EXISTS VENTE_CONSOMMABLES CASCADE;

CREATE OR REPLACE PROCEDURAL LANGUAGE plpgsql;

CREATE DOMAIN domain_categorie AS VARCHAR
    CHECK (
            VALUE ~ '3-6ans'
            OR VALUE ~ '6-8ans'
            OR VALUE ~ 'Famille'
            OR VALUE ~ 'Amateur'
            OR VALUE ~ 'Expert'
            OR VALUE ~ 'Solo ou à 2'
            OR VALUE ~ 'Ambiance'
        );

CREATE DOMAIN domain_etat AS VARCHAR
    CHECK (
            VALUE ~ 'Neuf'
            OR VALUE ~ 'Très bon'
            OR VALUE ~ 'Bon'
            OR VALUE ~ 'Passable'
            OR VALUE ~ 'Médiocre'
            OR VALUE ~ 'Incomplet'
            OR VALUE ~ 'Autre'
        );

CREATE DOMAIN domain_type_adhesion AS VARCHAR
    CHECK (
            VALUE ~ 'Journée'
            OR VALUE ~ 'Individuelle'
            OR VALUE ~ 'Familiale'
        );

CREATE TABLE JEUX
(
    uuidJeux          VARCHAR(60) PRIMARY KEY NOT NULL,
    nom               VARCHAR(50)             NOT NULL,
    code              VARCHAR(20)             NOT NULL,
    categorie         domain_categorie        NOT NULL,
    etat              domain_etat             NOT NULL,
    description       VARCHAR(512)            NULL,
    isDisponible      BOOLEAN                 NOT NULL,
    date_achat        DATE                    NULL,
    date_creation     TIMESTAMP               NOT NULL,
    date_modification TIMESTAMP               NULL
);

CREATE TABLE ADHERENT
(
    uuidAdherent             VARCHAR(60)          NOT NULL,
    nom                      VARCHAR(50)          NOT NULL,
    prenom                   VARCHAR(50)          NOT NULL,
    date_naissance           DATE,
    mail                     VARCHAR(100)         NOT NULL,
    date_premiere_cotisation DATE                 NOT NULL,
    date_derniere_cotisation DATE                 NOT NULL,
    telephone                VARCHAR(16),
    type_adhesion            domain_type_adhesion NOT NULL,
    personnes_rattachees     VARCHAR(255),
    autre                    VARCHAR(255),
    date_creation            timestamp            NOT NULL,
    date_modification        timestamp            NOT NULL,
    PRIMARY KEY (uuidAdherent)
);

CREATE TABLE EMPRUNT
(
    uuidEmprunt       VARCHAR(60) NOT NULL PRIMARY KEY,
    uuidAdherent      VARCHAR(60) NOT NULL,
    uuidJeux          VARCHAR(60) NOT NULL,
    date_emprunt      DATE        NOT NULL,
    date_retourprevu  DATE        NOT NULL,
    date_retour       DATE        NULL,
    date_creation     TIMESTAMP   NOT NULL,
    date_modification TIMESTAMP   NULL,
    CONSTRAINT fk_EMPRUNT_ADHERENT FOREIGN KEY (uuidAdherent) REFERENCES ADHERENT (uuidAdherent),
    CONSTRAINT fk_EMPRUNT_JEUX FOREIGN KEY (uuidJeux) REFERENCES JEUX (uuidJeux)
);

CREATE TABLE CONSOMMABLES
(
    uuidConsommables  VARCHAR(60)   NOT NULL PRIMARY KEY,
    label             VARCHAR(15)   NOT NULL,
    prix_unitaire     NUMERIC(6, 2) NOT NULL,
    qte               INTEGER       NOT NULL,
    date_creation     TIMESTAMP     NOT NULL,
    date_modification TIMESTAMP     NULL
);

CREATE TABLE VENTE
(
    uuidVente         VARCHAR(60)   NOT NULL PRIMARY KEY,
    uuidAdherent      VARCHAR(60)   NOT NULL,
    prix_total        NUMERIC(6, 2) NOT NULL,
    date_creation     TIMESTAMP     NOT NULL,
    date_modification TIMESTAMP     NULL,
    CONSTRAINT fk_VENTE_ADHERENT FOREIGN KEY (uuidAdherent) REFERENCES ADHERENT (uuidAdherent)
);

CREATE TABLE VENTE_CONSOMMABLES
(
    uuidVente        VARCHAR(60) NOT NULL,
    uuidConsommables VARCHAR(60) NOT NULL,
    qte              INTEGER     NOT NULL,
    CONSTRAINT fk_VENTE_CONSOMMABLES_VENTE FOREIGN KEY (uuidVente) REFERENCES VENTE (uuidVente),
    CONSTRAINT fk_VENTE_CONSOMMABLES_CONSOMMABLES FOREIGN KEY (uuidConsommables) REFERENCES CONSOMMABLES (uuidConsommables),
    PRIMARY KEY (uuidVente, uuidConsommables)
);

----------------------------------------------FONCTIONS----------------------------------------------------------------------
CREATE OR REPLACE FUNCTION calculateSold("idvente" VARCHAR) RETURNS NUMERIC(6, 2) AS
$$
DECLARE
    total NUMERIC(6, 2);
    r     NUMERIC(6, 2);
BEGIN
    total = 0;
    for r in SELECT DISTINCT prix_unitaire * vc.qte as prix
             FROM CONSOMMABLES c
                      join VENTE_CONSOMMABLES vc on c.uuidConsommables = vc.uuidConsommables
             where uuidVente = $1
        LOOP
            total = total + r;
        END LOOP;
    RETURN total;
    -- SELECT uuidConsommables FROM VENTE WHERE uuidVente=idvente;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION canSold(idvente VARCHAR) RETURNS BOOLEAN AS
$$
DECLARE
    r INTEGER;
BEGIN
    for r in SELECT DISTINCT c.qte - vc.qte
             FROM VENTE_CONSOMMABLES vc
                      join CONSOMMABLES c on vc.uuidConsommables = c.uuidConsommables
             WHERE uuidVente = $1
        LOOP
            IF r < 0 THEN
                RETURN FALSE;
            END IF;
        END LOOP;
    RETURN TRUE;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION topJeuxEmpruntes(nb INTEGER) RETURNS TABLE (nom VARCHAR, nb_emprunt BIGINT) as $$
BEGIN
    return query
        SELECT * FROM (SELECT  j.nom , COUNT(j.nom) as nb_emprunt FROM jeux j  JOIN emprunt e ON j.uuidJeux = e.uuidJeux GROUP BY j.nom ) AS x ORDER BY nb_emprunt DESC LIMIT $1;
END;
$$ LANGUAGE plpgsql;



---------------------------- TRIGGERS ---------------------------------------------
--- BEFORE INSERT / JEUX EMPRUNT check contraintes d'integrite TESTER
CREATE OR REPLACE FUNCTION checkinsertOrUpdateEmprunt() RETURNS trigger AS
$$
BEGIN
    IF NEW.uuidEmprunt IS NULL THEN
        RAISE EXCEPTION 'nuuidEmprunt ne peut pas être NULL';
    END IF;
    IF NEW.date_emprunt IS NULL THEN
        RAISE EXCEPTION 'date_emprunt ne peut pas être NULL';
    END IF;
    IF (NEW.date_retourprevu IS NULL) OR (NEW.date_retourprevu < NEW.date_emprunt) THEN
        RAISE EXCEPTION 'date_retourprevu ne peut pas être NULL et ne peut pas être antérieur à date_emprunt';
    END IF;
    IF NEW.date_retour < NEW.date_emprunt THEN
        RAISE EXCEPTION 'date_retour ne peut pas être antérieur à date_emprunt';
    END IF;
    IF NEW.date_creation IS NULL THEN
        RAISE EXCEPTION 'date_creation ne peut pas être NULL';
    END IF;
    IF NEW.date_modification IS NULL THEN
        RAISE EXCEPTION 'date_modification ne peut pas être NULL';
    END IF;
    IF ((SELECT isDisponible FROM JEUX WHERE JEUX.uuidJeux=NEW.uuidJeux) <> TRUE) AND (OLD IS NUll OR OLD.uuidJeux <> NEW.uuidJeux) THEN
        RAISE EXCEPTION 'Le jeu n est pas disponible';
    end if;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trigger_checkinsertOrUpdateEmprunt
    BEFORE INSERT OR UPDATE
    ON emprunt
    FOR EACH ROW
EXECUTE PROCEDURE checkinsertOrUpdateEmprunt();

--- BEFORE INSERT / JEUX ADHERENT check contraintes d'integrite TESTER

CREATE OR REPLACE FUNCTION checkInsertOrUpdateJeux() RETURNS trigger AS
$$DECLARE requestType VARCHAR ;
BEGIN
    requestType = TG_ARGV[0];
    IF NEW.uuidJeux IS NULL THEN
        RAISE EXCEPTION 'uuidJeux ne peut pas être NULL';
    END IF;
    IF NEW.nom IS NULL THEN
        RAISE EXCEPTION 'nom ne peut pas être NULL';
    END IF;

    IF requestType = 'insert' THEN
        IF EXISTS(SELECT 1 FROM jeux j WHERE NEW.code = j.code) OR (NEW.code IS NULL) THEN
            RAISE EXCEPTION 'code doit être unique, il ne doit pas déjà être assigné à un autre jeu';
        END IF;
    ELSIF requestType = 'update' THEN
        IF (EXISTS(SELECT 1 FROM jeux j WHERE NEW.code = j.code) AND OLD.code <> NEW.code) OR (NEW.code IS NULL) THEN
            RAISE EXCEPTION 'code doit être unique, il ne doit pas déjà être assigné à un autre jeu';
        END IF;
    END IF;
    IF NEW.isDisponible IS NULL THEN
        RAISE EXCEPTION 'isDisponible ne peut pas être NULL';
    END IF;
    IF NEW.date_creation IS NULL THEN
        RAISE EXCEPTION 'date_creation ne peut pas être NULL';
    END IF;
    IF NEW.date_creation < NEW.date_achat THEN
        RAISE EXCEPTION 'date_achat ne peut pas être postérieur à date_creation';
    END IF;
    IF NEW.date_modification IS NULL THEN
        RAISE EXCEPTION 'date_modification ne peut pas être NULL';
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trigger_checkInsertJeux
    BEFORE INSERT
    ON jeux
    FOR EACH ROW
EXECUTE PROCEDURE checkInsertOrUpdateJeux('insert');

CREATE TRIGGER trigger_checkUpdateJeux
    BEFORE UPDATE
    ON jeux
    FOR EACH ROW
EXECUTE PROCEDURE checkInsertOrUpdateJeux('update');

--- BEFORE INSERT / UPDATE ADHERENT check contraintes d'integrite TESTER

CREATE OR REPLACE FUNCTION checkInsertOrUpdateAdherent() RETURNS trigger AS
$$
BEGIN
    IF NEW.uuidAdherent IS NULL THEN
        RAISE EXCEPTION 'uuidJeux ne peut pas être NULL';
    END IF;
    IF current_date < NEW.date_naissance THEN
        RAISE EXCEPTION 'date_naissance ne peut pas être postérieur à date_creation';
    END IF;
    IF (NEW.date_premiere_cotisation IS NULL) OR (current_date < NEW.date_premiere_cotisation) THEN
        RAISE EXCEPTION 'date_premiere_cotisation ne peut pas être NULL et ne peut pas être postérieur à la date du jour';
    END IF;
    IF (NEW.date_derniere_cotisation IS NULL) OR (NEW.date_derniere_cotisation < NEW.date_premiere_cotisation) THEN
        RAISE EXCEPTION 'date_derniere_cotisation ne peut pas être NULL et ne peut pas être antérieur à date_premiere_cotisation';
    END IF;
    IF (NEW.type_adhesion = 'Familiale') AND (NEW.personnes_rattachees IS NULL) THEN
        RAISE EXCEPTION 'personnes_rattachees ne peut pas être NULL si type_adhesion = Familiale';
    END IF;
    IF NEW.date_creation IS NULL THEN
        RAISE EXCEPTION 'date_creation ne peut pas être NULL';
    END IF;
    IF NEW.date_modification IS NULL THEN
        RAISE EXCEPTION 'date_modification ne peut pas être NULL';
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trigger_checkInsertOrUpdateAdherent
    BEFORE INSERT OR UPDATE
    ON adherent
    FOR EACH ROW
EXECUTE PROCEDURE checkInsertOrUpdateAdherent();

--- BEFORE INSERT / UPDATE VENTE check contraintes d'integrite TESTER

CREATE OR REPLACE FUNCTION checkInsertOrUpdateVente() RETURNS trigger AS
$$
BEGIN
    IF NEW.uuidVente IS NULL THEN
        RAISE EXCEPTION 'uuidVente ne peut pas être NULL';
    END IF;
    IF NEW.date_creation IS NULL THEN
        RAISE EXCEPTION 'date_creation ne peut pas être NULL';
    END IF;
    IF NEW.date_modification IS NULL THEN
        RAISE EXCEPTION 'date_modification ne peut pas être NULL';
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trigger_checkInsertOrUpdateVente
    BEFORE INSERT OR UPDATE
    ON VENTE
    FOR EACH ROW
EXECUTE PROCEDURE checkInsertOrUpdateVente();

--- BEFORE INSERT / UPDATE Consommables check contraintes d'integrite TESTER

CREATE OR REPLACE FUNCTION checkInsertOrUpdateConsommables() RETURNS trigger AS
$$
BEGIN
    IF NEW.uuidConsommables IS NULL THEN
        RAISE EXCEPTION 'uuidConsommables ne peut pas être NULL';
    END IF;
    IF (NEW.prix_unitaire IS NULL) OR (NEW.prix_unitaire < 0) THEN
        RAISE EXCEPTION 'prix_unitaire ne peut pas être NULL et doit être >= 0';
    END IF;
    IF (NEW.qte IS NULL) OR (NEW.qte < 0) THEN
        RAISE EXCEPTION 'qte ne peut pas être NULL et doit être >= 0 Cette erreur peut s afficher si le Stock n est pas suffisant pour creer la vente';
    END IF;
    IF NEW.date_creation IS NULL THEN
        RAISE EXCEPTION 'date_creation ne peut pas être NULL';
    END IF;
    IF NEW.date_modification IS NULL THEN
        RAISE EXCEPTION 'date_modification ne peut pas être NULL';
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trigger_checkInsertOrUpdateConsommables
    BEFORE INSERT OR UPDATE
    ON CONSOMMABLES
    FOR EACH ROW
EXECUTE PROCEDURE checkInsertOrUpdateConsommables();

--- CHECK BEFORE INSERT / UPDATE VENTE_CONSOMMABLES -> qte > 0 TESTER

CREATE OR REPLACE FUNCTION checkInsertOrUpdateVenteConsommables() RETURNS trigger AS
$$
BEGIN
    IF (NEW.qte IS NULL) OR (NEW.qte < 0) THEN
        RAISE EXCEPTION 'qte ne peut pas être NULL et doit être >= 0';
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trigger_checkInsertOrUpdateVenteConsommables
    BEFORE INSERT OR UPDATE
    ON VENTE_CONSOMMABLES
    FOR EACH ROW
EXECUTE PROCEDURE checkInsertOrUpdateVenteConsommables();

-- CHECK BEFORT INSERT VENTE_CONSOMMABLE SI IL Y A ASSEZ DE STOCK TESTER

CREATE OR REPLACE FUNCTION checkOnInsertCanSoldConsommables() RETURNS trigger AS
$trigger_checkOnInsertCanSoldConsommables$
    BEGIN
        IF (canSold(NEW.uuidVente) <> TRUE ) THEN
            RAISE EXCEPTION 'Stock non suffisant pour effectuer cette commande !';
        END IF;
        RETURN NEW;
    END;
$trigger_checkOnInsertCanSoldConsommables$ LANGUAGE  plpgsql;

CREATE TRIGGER trigger_checkOnInsertCanSoldConsommables
    BEFORE INSERT
    ON VENTE_CONSOMMABLES
    FOR EACH ROW
EXECUTE PROCEDURE checkOnInsertCanSoldConsommables();

-- UPDATE PRIX_TOTAL AFTER INSERT VENTE_CONSOMMABLE TESTER

CREATE OR REPLACE FUNCTION checkAfterInsertVENTE_CONSOMMABLES() RETURNS trigger AS
$checkAfterInsertVENTE_CONSOMMABLES$
BEGIN
    UPDATE VENTE SET prix_total=calculateSold(NEW.uuidVente) WHERE VENTE.uuidVente=NEW.uuidVente;
    UPDATE CONSOMMABLES SET qte= qte - (SELECT qte FROM VENTE_CONSOMMABLES vc WHERE vc.uuidVente = NEW.uuidVente AND vc.uuidConsommables = NEW.uuidConsommables) WHERE uuidConsommables = NEW.uuidConsommables;
    RETURN NEW;
END;
$checkAfterInsertVENTE_CONSOMMABLES$ LANGUAGE  plpgsql;

CREATE TRIGGER trigger_checkBeforInsertVENTE_CONSOMMABLES
    AFTER INSERT
    ON VENTE_CONSOMMABLES
    FOR EACH ROW
EXECUTE PROCEDURE checkAfterInsertVENTE_CONSOMMABLES();

-- Change le IsDisponible des jeux lors des changement sur les enprunts TESTER

CREATE OR REPLACE FUNCTION updateJeuDispo() RETURNS trigger AS
$$
    DECLARE requestType VARCHAR ;
BEGIN
    requestType = TG_ARGV[0];
    IF requestType = 'insert' THEN
        UPDATE JEUX j SET isDisponible = false WHERE NEW.uuidJeux = j.uuidJeux;
    END IF;
    IF (requestType = 'update') THEN
        IF (NEW.date_retour IS NOT NULL) AND (OLD.date_retour IS NULL) THEN
            UPDATE JEUX j SET isDisponible = true WHERE OLD.uuidJeux = j.uuidJeux;
        ELSIF (NEW.date_retour IS NULL) AND (OLD.uuidJeux <> NEW.uuidJeux) THEN
            UPDATE JEUX j SET isDisponible = true WHERE OLD.uuidJeux = j.uuidJeux;
            UPDATE JEUX j SET isDisponible = false WHERE NEW.uuidJeux = j.uuidJeux;
        END IF;
    END IF;
    IF requestType = 'delete' THEN
        UPDATE JEUX j SET isDisponible = true WHERE OLD.uuidJeux = j.uuidJeux;
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trigger_insert_updateJeuDispo
    AFTER INSERT
    ON EMPRUNT
    FOR EACH ROW
EXECUTE PROCEDURE updateJeuDispo('insert');

CREATE TRIGGER trigger_update_updateJeuDispo
    AFTER UPDATE
    ON EMPRUNT
    FOR EACH ROW
EXECUTE PROCEDURE updateJeuDispo('update');

CREATE TRIGGER trigger_delete_updateJeuDispo
    AFTER DELETE
    ON EMPRUNT
    FOR EACH ROW
EXECUTE PROCEDURE updateJeuDispo('delete');

--
--------------------------------JEUX DE TEST--------------------------------------------------
INSERT INTO CONSOMMABLES(uuidConsommables, label, prix_unitaire, qte, date_creation, date_modification) VALUES ('1','coca',1.5,10,'2016-06-22 19:10:25-07','2016-06-22 19:10:25-07');
INSERT INTO CONSOMMABLES(uuidConsommables, label, prix_unitaire, qte, date_creation, date_modification) VALUES ('2','chips',1.0,30,'2016-06-22 19:10:25-07','2016-06-22 19:10:25-07');
INSERT INTO CONSOMMABLES(uuidConsommables, label, prix_unitaire, qte, date_creation, date_modification) VALUES ('3','cafe',2,3,'2016-06-22 19:10:25-07','2016-06-22 19:10:25-07');

INSERT INTO ADHERENT(uuidAdherent, nom, prenom, date_naissance, mail, date_premiere_cotisation, date_derniere_cotisation, telephone, type_adhesion, personnes_rattachees, autre, date_creation, date_modification) VALUES ('1','MARCHAL','Bob','1999-06-22','bob@gmail.com','2005-06-22','2015-06-22',null,'Journée',null,null,'2016-06-22 19:10:25-07','2016-06-22 19:10:25-07');
INSERT INTO ADHERENT(uuidAdherent, nom, prenom, date_naissance, mail, date_premiere_cotisation, date_derniere_cotisation, telephone, type_adhesion, personnes_rattachees, autre, date_creation, date_modification) VALUES ('2','HENRIQUES','Paul','1998-06-22','paul@gmail.com','2005-06-22','2015-06-22',null,'Journée',null,null,'2017-06-22 19:10:25-07','2017-06-22 19:10:25-07');
INSERT INTO ADHERENT(uuidAdherent, nom, prenom, date_naissance, mail, date_premiere_cotisation, date_derniere_cotisation, telephone, type_adhesion, personnes_rattachees, autre, date_creation, date_modification) VALUES ('3','NAJULI','Elina','1997-06-22','elina@gmail.com','2005-06-22','2015-06-22',null,'Journée',null,null,'2018-06-22 19:10:25-07','2018-06-22 19:10:25-07');

INSERT INTO JEUX(uuidJeux, nom, code, categorie, etat, description, isDisponible, date_achat, date_creation, date_modification) VALUES ('1','UNO','1','Famille','Incomplet','Un bon jeu',true,'2000-11-19','2016-06-22 19:10:25-07','2016-06-22 19:10:25-07');
INSERT INTO JEUX(uuidJeux, nom, code, categorie, etat, description, isDisponible, date_achat, date_creation, date_modification) VALUES ('2','Monopoly','2','Famille','Incomplet','Un bon jeu',true,'2000-11-19','2016-06-22 19:10:25-07','2016-06-22 19:10:25-07');
INSERT INTO JEUX(uuidJeux, nom, code, categorie, etat, description, isDisponible, date_achat, date_creation, date_modification) VALUES ('3','Echec','3','Famille','Incomplet','Un bon jeu',true,'2000-11-19','2016-06-22 19:10:25-07','2016-06-22 19:10:25-07');
INSERT INTO JEUX(uuidJeux, nom, code, categorie, etat, description, isDisponible, date_achat, date_creation, date_modification) VALUES ('4','Les trois petits cochons','4','3-6ans','Incomplet','Un bon jeu',true,'2000-11-19','2016-06-22 19:10:25-07','2016-06-22 19:10:25-07');
INSERT INTO JEUX(uuidJeux, nom, code, categorie, etat, description, isDisponible, date_achat, date_creation, date_modification) VALUES ('5','Croque carrote','5','Famille','Incomplet','Un bon jeu',true,'2000-11-19','2016-06-22 19:10:25-07','2016-06-22 19:10:25-07');

INSERT INTO VENTE(uuidVente, uuidAdherent, prix_total, date_creation, date_modification) VALUES ('1','1',0,'2020-11-19','2020-11-19');
INSERT INTO VENTE(uuidVente, uuidAdherent, prix_total, date_creation, date_modification) VALUES ('2','1',0,'2020-11-19','2020-11-19');

INSERT INTO VENTE_CONSOMMABLES(uuidVente, uuidConsommables, qte) VALUES ('1','1','3');
INSERT INTO VENTE_CONSOMMABLES(uuidVente, uuidConsommables, qte) VALUES ('1','2','3');
INSERT INTO VENTE_CONSOMMABLES(uuidVente, uuidConsommables, qte) VALUES ('2','3','2');

INSERT INTO  EMPRUNT(uuidEmprunt, uuidAdherent, uuidJeux, date_emprunt, date_retourprevu, date_retour, date_creation, date_modification) VALUES ('1','1','3','2019-11-19','2019-11-22',null,'2016-06-22 19:10:25-07','2016-06-22 19:10:25-07');
UPDATE EMPRUNT SET date_retour='2019-11-24' WHERE TRUE;
INSERT INTO  EMPRUNT(uuidEmprunt, uuidAdherent, uuidJeux, date_emprunt, date_retourprevu, date_retour, date_creation, date_modification) VALUES ('2','1','3','2019-11-19','2019-11-22',null,'2016-06-22 19:10:25-07','2016-06-22 19:10:25-07');
UPDATE EMPRUNT SET date_retour='2019-11-24' WHERE TRUE;
INSERT INTO  EMPRUNT(uuidEmprunt, uuidAdherent, uuidJeux, date_emprunt, date_retourprevu, date_retour, date_creation, date_modification) VALUES ('3','2','1','2019-11-19','2019-11-22',null,'2016-06-22 19:10:25-07','2016-06-22 19:10:25-07');
UPDATE EMPRUNT SET date_retour='2019-11-24' WHERE TRUE;
INSERT INTO  EMPRUNT(uuidEmprunt, uuidAdherent, uuidJeux, date_emprunt, date_retourprevu, date_retour, date_creation, date_modification) VALUES ('4','2','5','2019-11-19','2019-11-22',null,'2016-06-22 19:10:25-07','2016-06-22 19:10:25-07');
UPDATE EMPRUNT SET date_retour='2019-11-24' WHERE TRUE;
INSERT INTO  EMPRUNT(uuidEmprunt, uuidAdherent, uuidJeux, date_emprunt, date_retourprevu, date_retour, date_creation, date_modification) VALUES ('5','3','3','2019-11-19','2019-11-22',null,'2016-06-22 19:10:25-07','2016-06-22 19:10:25-07');
UPDATE EMPRUNT SET date_retour='2019-11-24' WHERE TRUE;
INSERT INTO  EMPRUNT(uuidEmprunt, uuidAdherent, uuidJeux, date_emprunt, date_retourprevu, date_retour, date_creation, date_modification) VALUES ('6','3','5','2019-11-19','2019-11-22',null,'2016-06-22 19:10:25-07','2016-06-22 19:10:25-07');

UPDATE EMPRUNT SET date_retour='2019-11-24' WHERE TRUE;
-- select calculateSold(Cast(1 as VarChar)); RETURN 5.5
-- select canSold(Cast(1 as VarChar)); RETURN TRUE
-- select canSold(Cast(2 as VarChar)); RETURN FALSE
-- select topJeuxEmpruntes(4); RETURN ECHEC 3 : CROQUE CARROTE 2 ; UNO 1