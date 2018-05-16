-- Este script es para uso en una version futura, es la estructura actual con integridad referencial en DB

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';


-- -----------------------------------------------------
-- Table `web_icons`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `web_icons` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(50) NOT NULL,
  `image` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table `acl_modules`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `acl_modules` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `parent_id` INT(10) UNSIGNED NULL DEFAULT NULL COMMENT 'Id de Módulo padre',
  `title` CHAR(200) NULL DEFAULT NULL COMMENT 'Nombre',
  `module` CHAR(200) NULL DEFAULT NULL COMMENT 'Url de módulo, puede ser archivo XML, URl de controlador o módulo ZF',
  `tree` ENUM('0','1') NOT NULL DEFAULT '1',
  `refresh_on_load` ENUM('0','1') NOT NULL DEFAULT '0' COMMENT 'Define si módulo debe ser refrescado al reseleccionar la pestaña, en caso contrario mantendrá el estatus de como se dejó al abandonarla.',
  `type` ENUM('xml','zend_module','iframe') NOT NULL DEFAULT 'xml',
  `approved` ENUM('0','1') NOT NULL DEFAULT '1',
  `order` TINYINT(4) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Orden en que aparece en arbol',
  `root` ENUM('0','1') NOT NULL DEFAULT '0' COMMENT 'Define si solo puede acceder perfil ROOT (en configuracion por defecto acl_roles_id = 1)',
  `ownership` ENUM('0','1') NOT NULL DEFAULT '0' COMMENT 'Indica si modulo usa modelo con reglas de owner, funciona con type=xml, ver Gamelena_Db_Table::isOwner(item, user)',
  `icons_id` INT(11) NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `module` (`module` ASC),
  INDEX `fk_acl_modules_web_icons_idx` (`icons_id` ASC),
  CONSTRAINT `fk_acl_modules_web_icons`
    FOREIGN KEY (`icons_id`)
    REFERENCES `web_icons` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table `acl_actions`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `acl_actions` (
  `id` VARCHAR(10) NOT NULL,
  `title` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table `acl_modules_actions`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `acl_modules_actions` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `acl_modules_id` INT(10) UNSIGNED NOT NULL,
  `acl_actions_id` VARCHAR(10) NOT NULL,
  PRIMARY KEY (`id`),
 CONSTRAINT `fk_acl_modules_actions_acl_modules1`
    FOREIGN KEY (`acl_modules_id`)
    REFERENCES `acl_modules` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_acl_modules_actions_acl_actions1`
    FOREIGN KEY (`acl_actions_id`)
    REFERENCES `acl_actions` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table `acl_roles`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `acl_roles` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `role_name` CHAR(64) NOT NULL,
  `description` CHAR(100) CHARACTER SET 'utf8' NOT NULL,
  `approved` ENUM('0','1') CHARACTER SET 'utf8' NOT NULL DEFAULT '1',
  `must_refresh` ENUM('0','1') CHARACTER SET 'utf8' NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `role_name` (`role_name` ASC))
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table `acl_users`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `acl_users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `acl_roles_id` INT(11) NOT NULL,
  `user_name` CHAR(64) NOT NULL,
  `password` CHAR(200) NOT NULL,
  `first_names` CHAR(50) NOT NULL,
  `last_names` CHAR(50) NOT NULL,
  `email` CHAR(50) NOT NULL,
  `approved` ENUM('0','1') NOT NULL,
  `photo` VARCHAR(256) NULL DEFAULT NULL,
  `must_refresh` ENUM('0','1') CHARACTER SET 'utf8' NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `user_name` (`user_name` ASC),
  INDEX `fk_acl_users_acl_roles1_idx` (`acl_roles_id` ASC),
  CONSTRAINT `fk_acl_users_acl_roles1`
    FOREIGN KEY (`acl_roles_id`)
    REFERENCES `acl_roles` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table `acl_roles_modules_actions`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `acl_roles_modules_actions` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `acl_modules_actions_id` INT(10) UNSIGNED NOT NULL,
  `acl_roles_id` INT(11) NOT NULL,
  `permission` ENUM('ALLOW','DENY') CHARACTER SET 'utf8' NOT NULL DEFAULT 'ALLOW',
  PRIMARY KEY (`id`),
  UNIQUE KEY (`acl_modules_actions_id` , `acl_roles_id`),
  CONSTRAINT `fk_acl_roles_modules_actions_acl_modules_actions1`
    FOREIGN KEY (`acl_modules_actions_id`)
    REFERENCES `acl_modules_actions` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_acl_roles_modules_actions_acl_roles1`
    FOREIGN KEY (`acl_roles_id`)
    REFERENCES `acl_roles` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table `web_settings`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `web_settings` (
  `id` CHAR(255) NOT NULL DEFAULT '',
  `list` CHAR(255) NOT NULL,
  `value` CHAR(255) NOT NULL DEFAULT '0',
  `type` CHAR(255) NOT NULL DEFAULT '',
  `description` TEXT NOT NULL,
  `ord` INT(11) NOT NULL DEFAULT '0',
  `group` CHAR(255) NOT NULL,
  `function` CHAR(255) NOT NULL,
  `approved` ENUM('0','1') NOT NULL,
  `path` VARCHAR(50) NOT NULL,
  `url` VARCHAR(256) NULL DEFAULT NULL,
  `regExp` VARCHAR(60) NOT NULL,
  `invalidMessage` VARCHAR(50) NOT NULL,
  `promptMessage` VARCHAR(50) NOT NULL,
  `formatter` VARCHAR(25) NOT NULL,
  `xml_children` TEXT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `group` (`group` ASC))
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table `acl_groups`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `acl_groups` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(45) NULL DEFAULT NULL,
  `description` VARCHAR(45) NULL DEFAULT NULL,
  `approved` ENUM('0','1') NULL DEFAULT '1',
  PRIMARY KEY (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table `acl_users_groups`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `acl_users_groups` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `acl_users_id` INT(11) NOT NULL,
  `acl_groups_id` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`acl_users_id`, `acl_groups_id`),
  CONSTRAINT `fk_acl_users_groups_acl_users1`
    FOREIGN KEY (`acl_users_id`)
    REFERENCES `acl_users` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_acl_users_groups_acl_groups1`
    FOREIGN KEY (`acl_groups_id`)
    REFERENCES `acl_groups` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table `acl_groups_modules_actions`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `acl_groups_modules_actions` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `acl_modules_actions_id` INT(10) UNSIGNED NOT NULL,
  `acl_groups_id` INT(11) NOT NULL,
  `acl_modules_item_id` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`acl_modules_actions_id`, `acl_groups_id`, `acl_modules_item_id`),
  CONSTRAINT `fk_acl_groups_modules_actions_acl_modules_actions1`
    FOREIGN KEY (`acl_modules_actions_id`)
    REFERENCES `acl_modules_actions` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_acl_groups_modules_actions_acl_groups1`
    FOREIGN KEY (`acl_groups_id`)
    REFERENCES `acl_groups` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table `log_book`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `log_book` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user` CHAR(40) NOT NULL,
  `table` CHAR(40) NOT NULL,
  `action` CHAR(40) NOT NULL,
  `condition` CHAR(200) NOT NULL,
  `acl_roles_id` INT(11) NOT NULL,
  `ip` CHAR(200) NOT NULL,
  `stamp` DATETIME NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Placeholder table for view `tables_logged`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `tables_logged` (`id` INT, `title` INT);

-- -----------------------------------------------------
-- View `tables_logged`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `tables_logged`;
CREATE  OR REPLACE VIEW `tables_logged` AS select distinct `log_book`.`table` AS `id`,`log_book`.`table` AS `title` from `log_book` order by `log_book`.`table`;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
