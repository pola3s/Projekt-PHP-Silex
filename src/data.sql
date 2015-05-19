
CREATE TABLE `files` (`id_file` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
  `name` CHAR(48) NOT NULL,
  `title` VARCHAR(45) NOT NULL,
  `category` VARCHAR(45) NOT NULL,
  `date` DATE NOT NULL,
  `description` TEXT NOT NULL,
  `id_user` VARCHAR(45) NOT NULL
);

CREATE TABLE `users` (
  `id_user` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `firstname` char(32) collate utf8_bin NOT NULL,
  `lastname` char(32) collate utf8_bin NOT NULL,
  `login` VARCHAR(100) NOT NULL DEFAULT '',
  `password` VARCHAR(255) NOT NULL DEFAULT '',
  `roles` VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `unique_username` (`login`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `roles` (
`id_role` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`role` CHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL
) ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_bin;

CREATE TABLE `users_roles` (
`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`id_user` INT UNSIGNED NOT NULL ,
`id_role` INT UNSIGNED NOT NULL
) ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_bin;



INSERT INTO `roles` (`id_role`, `role`) VALUES ('1', 'ROLE_ADMIN');
INSERT INTO `users_roles` (`id`, `id_user`, `id_role`) VALUES (NULL, '1', '1');

ALTER TABLE  `users` CHANGE  `password`  `password` CHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL;

INSERT INTO `users` (`id_user`, `firstname`, `lastname`, `login`, `password`, `roles`) 
VALUES ('1', 'John', 'Doe', 'john.doe', 'BFEQkknI/c+Nd7BaG7AaiyTfUFby/pkMHy3UsYqKqDcmvHoPRX/ame9TnVuOV2GrBH0JK9g4koW+CgTYI9mK+w==', 'ROLE_USER');

CREATE TABLE `comments` (
  `id_comment` int(11) NOT NULL AUTO_INCREMENT,
  `content` text CHARACTER SET latin1 NOT NULL,
  `published_date` date DEFAULT NULL,
  `id_file` int(11) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_comment`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8;

CREATE TABLE `grades` (
  `id_grade` INT NOT NULL AUTO_INCREMENT,
  `grade` INT NOT NULL,
  `id_user` VARCHAR(45) NOT NULL,
  `id_file` INT NOT NULL,
  PRIMARY KEY (`id_grade`));

INSERT INTO `12_serwinska`.`comments` (`id_comment`, `content`, `published_date`, `id_file`, `id_user`) VALUES ('2', 'To jest drugi komentarz', 'To jest pierwszy komentarz', '1', '1');
 
CREATE TABLE `12_serwinska`.`category` (
  `id_category` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`id_category`));
 
 
 CREATE TABLE `values` (
  `id_grade` INT NOT NULL,
  `value` INT NOT NULL,
  PRIMARY KEY (`id_grade`));

  
INSERT INTO `12_serwinska`.`values` (`id_grade`, `value`) VALUES ('1', '1');
INSERT INTO `12_serwinska`.`values` (`id_grade`, `value`) VALUES ('2', '2');
INSERT INTO `12_serwinska`.`values` (`id_grade`, `value`) VALUES ('3', '3');
INSERT INTO `12_serwinska`.`values` (`id_grade`, `value`) VALUES ('4', '4');
INSERT INTO `12_serwinska`.`values` (`id_grade`, `value`) VALUES ('5', '5');

DROP TABLE `12_serwinska`.`category`;

CREATE TABLE `12_serwinska`.`categories` (
  `id_category` INT NOT NULL,
  `name` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`id_category`));

  
INSERT INTO `12_serwinska`.`categories` (`id_category`, `name`) VALUES ('1', 'Portret');
INSERT INTO `12_serwinska`.`categories` (`id_category`, `name`) VALUES ('2', 'Fashion');

CREATE TABLE IF NOT EXISTS `roles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` char(32) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT INTO `roles` (`id`, `name`) VALUES
(1, 'ROLE_ADMIN'),
(2, 'ROLE_USER');

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `login` char(32) COLLATE utf8_bin NOT NULL,
  `password` char(128) COLLATE utf8_bin NOT NULL,
  `role_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_users_1` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

ALTER TABLE `users`
  ADD CONSTRAINT `FK_users_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);
  
  
INSERT INTO `12_serwinska`.`users` (`id`, `login`, `password`, `role_id`) VALUES ('1', 'TestAdmin', 'DJAhPVmfV76bEZ9xsW5O3oaN9o+zmwpRZ78XW5QspToIjtbBlAFSbd5v3l/QFdj1F5svzjMZ5tuQsugny0MnpA==', '1');
INSERT INTO `12_serwinska`.`users` (`id`, `login`, `password`, `role_id`) VALUES ('2', 'TestUser', '31sJZ7dGw9iFvJUqKIuS34JHj3D0MPLplLN+dxTq3vL3zz8pxkUSUCamau8UW1nGBOyNlQ0NE1NLWXYZNSV/Hg==', '2');