DROP TABLE IF EXISTS JEUX CASCADE;
DROP TABLE IF EXISTS ADHERENT CASCADE;
DROP TABLE IF EXISTS EMPRUNT CASCADE;
DROP TABLE IF EXISTS CONSOMMABLES CASCADE;
DROP TABLE IF EXISTS VENTE CASCADE;
DROP TABLE IF EXISTS VENTE_CONSOMMABLES CASCADE;

CREATE OR REPLACE PROCEDURAL LANGUAGE plpgsql;

CREATE DOMAIN domain_categorie AS VARCHAR
    CHECK(
                VALUE ~ '3-6ans'
            OR VALUE ~ '6-8ans'
            OR VALUE ~ 'Famille'
            OR VALUE ~ 'Amateur'
            OR VALUE ~ 'Expert'
            OR VALUE ~ 'Solo ou à 2'
            OR VALUE ~ 'Ambiance'
        );

CREATE DOMAIN domain_etat AS VARCHAR
    CHECK(
                VALUE ~ 'Neuf'
            OR VALUE ~ 'Très bon'
            OR VALUE ~ 'Pon'
            OR VALUE ~ 'Passable'
            OR VALUE ~ 'Médiocre'
            OR VALUE ~ 'Incomplet'
            OR VALUE ~ 'Autre'
        );

CREATE DOMAIN domain_type_adhesion AS VARCHAR
    CHECK(
                VALUE ~ 'Journée'
            OR VALUE ~ 'Individuelle'
            OR VALUE ~ 'Familiale'
        );

CREATE TABLE JEUX(
                     uuidJeux VARCHAR(60) PRIMARY KEY NOT NULL,
                     nom VARCHAR(50) NOT NULL,
                     code VARCHAR(20) NOT NULL,
                     categorie domain_categorie NOT NULL,
                     etat domain_etat NOT NULL,
                     description VARCHAR(512) NULL,
                     isDisponible BOOLEAN NOT NULL,
                     date_achat DATE NULL,
                     date_creation TIMESTAMP NOT NULL,
                     date_modification TIMESTAMP NULL
);

CREATE TABLE ADHERENT (
                          uuidAdherent VARCHAR(60) NOT NULL,
                          nom VARCHAR(50) NOT NULL,
                          prenom VARCHAR(50) NOT NULL,
                          date_naissance DATE,
                          mail VARCHAR(100) NOT NULL,
                          date_premiere_cotisation DATE NOT NULL,
                          date_derniere_cotisation DATE NOT NULL,
                          telephone VARCHAR(16),
                          type_adhesion domain_type_adhesion NOT NULL,
                          personnes_rattachees VARCHAR(255),
                          autre VARCHAR(255),
                          date_creation timestamp NOT NULL,
                          date_modification timestamp NOT NULL,
                          PRIMARY KEY (uuidAdherent)
);

CREATE TABLE EMPRUNT(
                        uuidEmprunt VARCHAR(60) NOT NULL PRIMARY KEY,
                        uuidAdherent VARCHAR(60) NOT NULL,
                        uuidJeux VARCHAR(60) NOT NULL,
                        date_emprunt DATE NOT NULL,
                        date_retourprevu DATE NOT NULL,
                        date_retour DATE NULL,
                        date_creation TIMESTAMP NOT NULL,
                        date_modification TIMESTAMP NULL,
                        CONSTRAINT fk_EMPRUNT_ADHERENT FOREIGN KEY(uuidAdherent) REFERENCES ADHERENT(uuidAdherent),
                        CONSTRAINT fk_EMPRUNT_JEUX FOREIGN KEY(uuidJeux) REFERENCES JEUX(uuidJeux)
);

CREATE TABLE CONSOMMABLES(
                             uuidConsommables VARCHAR(60) NOT NULL PRIMARY KEY,
                             label VARCHAR(15) NOT NULL,
                             prix_unitaire NUMERIC(6,2) NOT NULL,
                             qte INTEGER NOT NULL,
                             date_creation TIMESTAMP NOT NULL,
                             date_modification TIMESTAMP NULL
);

CREATE TABLE VENTE(
                      uuidVente VARCHAR(60) NOT NULL PRIMARY,
                      uuidConsommables VARCHAR(60) NOT NULL,
                      uuidAdherent VARCHAR(60) NOT NULL,
                      label VARCHAR(15) NOT NULL,
                      prix_total NUMERIC(6,2) NOT NULL,
                      date_creation TIMESTAMP NOT NULL,
                      date_modification TIMESTAMP NULL,
                      CONSTRAINT fk_VENTE_ADHERENT FOREIGN KEY(uuidAdherent) REFERENCES ADHERENT(uuidAdherent),
                      CONSTRAINT fk_VENTE_CONSOMMABLES FOREIGN KEY(uuidConsommables) REFERENCES CONSOMMABLES(uuidConsommables)
);

CREATE TABLE VENTE_CONSOMMABLES(
                                   uuidVente VARCHAR(60) NOT NULL PRIMARY,
                                   uuidConsommables VARCHAR(60) NOT NULL,
                                   qte INTEGER NOT NULL,
                                   CONSTRAINT fk_VENTE_CONSOMMABLES_ADHERENT FOREIGN KEY(uuidAdherent) REFERENCES ADHERENT(uuidAdherent),
                                   CONSTRAINT fk_VENTE_CONSOMMABLES_CONSOMMABLES FOREIGN KEY(uuidConsommables) REFERENCES CONSOMMABLES(uuidConsommables),
                                   PRIMARY KEY(uuidVente,uuidConsommables)
);

----------------------------------------------FONCTIONS----------------------------------------------------------------------
CREATE OR REPLACE FUNCTION calculateSold(idvente VARCHAR) RETURNS NUMERIC(6,2) AS $$
    DECLARE
        total NUMERIC(6,2);
        r NUMERIC(6,2);
    BEGIN
        total =0;
        for r in SELECT DISTINCT prix_unitaire * vc.qte as prix FROM CONSOMMABLES c join VENTE_CONSOMMABLES vc on c.uuidConsommables = vc.uuidConsommables where uuidVente=$1
        LOOP
            total = total + r;
        END LOOP;
       RETURN total;
       -- SELECT uuidConsommables FROM VENTE WHERE uuidVente=idvente;
    END;
$$ LANGUAGE  plpgsql;

CREATE OR REPLACE  FUNCTION canSold(idvente VARCHAR) RETURNS BOOLEAN AS $$
    DECLARE
        r INTEGER;
    BEGIN
        for r in SELECT DISTINCT qte FROM VENTE v join CONSOMMABLES c on v.uuidConsommables = c.uuidConsommables WHERE uuidVente=$1
        LOOP
            IF r <= 0 THEN
                RETURN FALSE;
            END IF;
        END LOOP;
    END;
$$ LANGUAGE  plpgsql;
-- stat des jeux les plus vendus
-- verifier si le stock est suffisant lors d'un achat / emprunt
-- fonction qui prends en param une date de naissance et retourne les jeux auquels il peu les emprunter