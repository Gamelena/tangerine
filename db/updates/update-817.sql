-- Reemplazar por nombre de base de datos de admportal para gw ussd 
USE USSD;

DROP PROCEDURE IF EXISTS changeColumnUnlessExists;

DELIMITER ;;

CREATE PROCEDURE changeColumnUnlessExists(
    IN dbName tinytext,
    IN tableName tinytext,
    IN oldFieldName tinytext,
    IN newFieldName tinytext,
    IN newFieldDef text)
BEGIN
    IF NOT EXISTS (
        SELECT * FROM information_schema.COLUMNS
        WHERE column_name=newFieldName
        and table_name=tableName
        and table_schema=dbName
        )
    THEN
        set @ddl=CONCAT('ALTER TABLE `', dbName, '`.`', tableName,
            '` CHANGE `', oldFieldName, '` `', newFieldName ,'` ', newFieldDef);
        prepare stmt from @ddl;
        execute stmt;
    END IF;
END;
;;
DELIMITER ;

DROP PROCEDURE IF EXISTS addColumnUnlessExists;
DELIMITER ;;


CREATE PROCEDURE addColumnUnlessExists(
    IN dbName tinytext,
    IN tableName tinytext,
    IN fieldName tinytext,
    IN fieldDef text)
BEGIN
    IF NOT EXISTS (
        SELECT * FROM information_schema.COLUMNS
        WHERE column_name=fieldName
        and table_name=tableName
        and table_schema=dbName
        )
    THEN
        set @ddl=CONCAT('ALTER TABLE `',dbName,'`.`',tableName,
            '` ADD COLUMN `',fieldName,'` ',fieldDef);
        prepare stmt from @ddl;
        execute stmt;
    END IF;
END;
;;
DELIMITER ;


DELETE from acl_modules_actions WHERE acl_modules_id NOT IN (SELECT id FROM acl_modules);
DELETE from acl_modules_actions WHERE acl_actions_id NOT IN (SELECT id FROM acl_actions);
DELETE from acl_roles_modules_actions WHERE acl_roles_id NOT IN (SELECT id FROM acl_roles);
DELETE from acl_roles_modules_actions WHERE acl_modules_actions_id NOT IN (SELECT id FROM acl_modules_actions);


CALL addColumnUnlessExists(Database(), 'acl_modules', 'ownership', 'enum("0","1") NOT NULL DEFAULT "0" COMMENT "Indica si modulo usa modelo con reglas de owner, funciona con type=xml, ver Zwei_Db_Table::isOwner(item, user)"');
CALL addColumnUnlessExists(Database(), 'acl_roles', 'must_refresh', 'enum("0","1") NOT NULL DEFAULT "0"');
CALL addColumnUnlessExists(Database(), 'acl_users', 'must_refresh', 'enum("0","1") NOT NULL DEFAULT "0"');
CALL addColumnUnlessExists(Database(), 'acl_session', 'must_refresh', 'enum("0","1") NOT NULL DEFAULT "0"');
-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `acl_session`
--

DROP TABLE IF EXISTS `acl_session`;
CREATE TABLE IF NOT EXISTS `acl_session` (
  `id` char(32) NOT NULL DEFAULT '0',
  `acl_users_id` int(11) NOT NULL,
  `acl_roles_id` int(11) NOT NULL,
  `modified` int(11) DEFAULT NULL,
  `lifetime` int(11) DEFAULT NULL,
  `data` text,
  `ip` varchar(20) NOT NULL,
  `user_agent` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

