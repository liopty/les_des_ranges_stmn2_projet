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
            OR VALUE ~ 'Pon'
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
    CONSTRAINT fk_VENTE_ADHERENT FOREIGN KEY (uuidAdherent) REFERENCES ADHERENT (uuidAdherent),
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
CREATE OR REPLACE FUNCTION calculateSold(idvente VARCHAR) RETURNS NUMERIC(6, 2) AS
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
    for r in SELECT DISTINCT qte
             FROM VENTE v
                      join CONSOMMABLES c on v.uuidConsommables = c.uuidConsommables
             WHERE uuidVente = $1
        LOOP
            IF r <= 0 THEN
                RETURN FALSE;
            END IF;
        END LOOP;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION topJeuxEmpruntes(nb BIGINT)
    RETURNS TABLE
            (
                nom        VARCHAR,
                nb_emprunt BIGINT
            )
as
$$
BEGIN
    return query
        SELECT *
        FROM (SELECT COUNT(j.nom) as nb_emprunt, j.nom
              FROM jeux j
                       JOIN emprunt e ON j.uuidJeux = e.uuidJeux
              GROUP BY j.nom) as table1
        ORDER BY nb_emprunt DESC
        LIMIT $1;
END;
$$ LANGUAGE plpgsql;



---------------------------- TRIGGERS ---------------------------------------------
CREATE OR REPLACE PROCEDURE checkinsertOrUpdateEmprunt() as
$$
BEGIN
    IF NEW.nuuidEmprunt IS NULL THEN
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
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trigger_checkinsertOrUpdateEmprunt
    BEFORE INSERT OR UPDATE
    ON emprunt
    FOR EACH ROW
EXECUTE PROCEDURE checkinsertOrUpdateEmprunt();

----

CREATE OR REPLACE PROCEDURE checkInsertOrUpdateJeux() as
$$
BEGIN
    IF NEW.uuidJeux IS NULL THEN
        RAISE EXCEPTION 'uuidJeux ne peut pas être NULL';
    END IF;
    IF NEW.nom IS NULL THEN
        RAISE EXCEPTION 'nom ne peut pas être NULL';
    END IF;
    IF EXISTS(SELECT 1 FROM jeux j WHERE NEW.code = j.code) OR (NEW.code IS NULL) THEN
        RAISE EXCEPTION 'code doit être unique, il ne doit pas déjà être assigné à un autre jeu';
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

END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trigger_checkInsertOrUpdateJeux
    BEFORE INSERT OR UPDATE
    ON jeux
    FOR EACH ROW
EXECUTE PROCEDURE checkInsertOrUpdateJeux();

-----

CREATE OR REPLACE PROCEDURE checkInsertOrUpdateAdherent() as
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

END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trigger_checkInsertOrUpdateAdherent
    BEFORE INSERT OR UPDATE
    ON adherent
    FOR EACH ROW
EXECUTE PROCEDURE checkInsertOrUpdateAdherent();

---

CREATE OR REPLACE PROCEDURE checkInsertOrUpdateVente() as
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

END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trigger_checkInsertOrUpdateVente
    BEFORE INSERT OR UPDATE
    ON VENTE
    FOR EACH ROW
EXECUTE PROCEDURE checkInsertOrUpdateVente();

---

CREATE OR REPLACE PROCEDURE checkInsertOrUpdateConsommables() as
$$
BEGIN
    IF NEW.uuidConsommables IS NULL THEN
        RAISE EXCEPTION 'uuidConsommables ne peut pas être NULL';
    END IF;
    IF (NEW.prix_unitaire IS NULL) OR (NEW.prix_unitaire < 0) THEN
        RAISE EXCEPTION 'prix_unitaire ne peut pas être NULL et doit être >= 0';
    END IF;
    IF (NEW.qte IS NULL) OR (NEW.qte < 0) THEN
        RAISE EXCEPTION 'qte ne peut pas être NULL et doit être >= 0';
    END IF;
    IF NEW.date_creation IS NULL THEN
        RAISE EXCEPTION 'date_creation ne peut pas être NULL';
    END IF;
    IF NEW.date_modification IS NULL THEN
        RAISE EXCEPTION 'date_modification ne peut pas être NULL';
    END IF;

END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trigger_checkInsertOrUpdateConsommables
    BEFORE INSERT OR UPDATE
    ON CONSOMMABLES
    FOR EACH ROW
EXECUTE PROCEDURE checkInsertOrUpdateConsommables();

---

CREATE OR REPLACE PROCEDURE checkInsertOrUpdateVenteConsommables() as
$$
BEGIN
    IF (NEW.qte IS NULL) OR (NEW.qte < 0) THEN
        RAISE EXCEPTION 'qte ne peut pas être NULL et doit être >= 0';
    END IF;
    IF NEW.date_creation IS NULL THEN
        RAISE EXCEPTION 'date_creation ne peut pas être NULL';
    END IF;
    IF NEW.date_modification IS NULL THEN
        RAISE EXCEPTION 'date_modification ne peut pas être NULL';
    END IF;

END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trigger_checkInsertOrUpdateVenteConsommables
    BEFORE INSERT OR UPDATE
    ON VENTE_CONSOMMABLES
    FOR EACH ROW
EXECUTE PROCEDURE checkInsertOrUpdateVenteConsommables();

--

