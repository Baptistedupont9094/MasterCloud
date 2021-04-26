/*Suppression de la data base si elle existe */
DROP DATABASE IF EXISTS mastercloud;

/*Création de la base de donnée*/
CREATE DATABASE mastercloud;

/*Utilisation de la base de donnée*/
USE mastercloud;

/*Création des tables*/
CREATE TABLE `musique`(
	`id`INT NOT NULL AUTO_INCREMENT,
	`nom` VARCHAR(255) NOT NULL,
	`artiste` VARCHAR(255) NOT NULL,
	`album` VARCHAR(255) NOT NULL,
	`genre` VARCHAR(255) NOT NULL,
	`nombre_likes` INT,
	`image` VARCHAR(255) NOT NULL,
	`source` VARCHAR(255) NOT NULL,
	PRIMARY KEY(`id`));

CREATE TABLE `playlist`(
	`id`INT NOT NULL AUTO_INCREMENT,
	`nom` VARCHAR(255) NOT NULL,
	`image` VARCHAR(255) NOT NULL,
	`est_privee` BOOLEAN,
	`nombre_likes` INT,
	PRIMARY KEY(`id`));

CREATE TABLE `utilisateur`(
	`id` INT NOT NULL AUTO_INCREMENT,
	`email` VARCHAR(255) NOT NULL,
	`nom` VARCHAR(80) NOT NULL,
	`mot_de_passe` VARCHAR(255) NOT NULL,
	`playlist_id` INT,
	`musique_id` INT,
	`est_connecte` BOOLEAN,
	PRIMARY KEY(`id`),
	CONSTRAINT fk_utilisateur_playlist
			FOREIGN KEY (playlist_id)
			REFERENCES playlist(id),
	CONSTRAINT fk_utilisateur_musique
			FOREIGN KEY (musique_id)
			REFERENCES musique(id)
	
);

CREATE TABLE `commentaire`(
	`id`INT NOT NULL AUTO_INCREMENT,
	`playlist_id` INT NOT NULL,
	`utilisateur_id` INT NOT NULL,
	`contenu` TEXT NOT NULL,
	PRIMARY KEY(`id`),
	CONSTRAINT fk_commentaire_utilisateur
			FOREIGN KEY (utilisateur_id)
			REFERENCES utilisateur(id),
	CONSTRAINT fk_commentaire_playlist
			FOREIGN KEY (playlist_id)
			REFERENCES playlist(id)
	
);

CREATE TABLE `playlist_musique`(
	`playlist_id` INT NOT NULL,
	`musique_id` INT NOT NULL,
	CONSTRAINT fk_playlist_musique_palylist
			FOREIGN KEY (playlist_id)
			REFERENCES playlist(id),
	CONSTRAINT fk_playlist_musique_musique
			FOREIGN KEY (musique_id)
			REFERENCES musique(id)
);






