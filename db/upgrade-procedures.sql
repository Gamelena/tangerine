USE `oferta_sugerida`;

DELIMITER $$
--
-- Procedimientos
--
DROP PROCEDURE IF EXISTS `addColumnUnlessExists`$$
CREATE PROCEDURE `addColumnUnlessExists`(
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
END$$

DROP PROCEDURE IF EXISTS `dropColumnIfExists`$$
CREATE PROCEDURE `dropColumnIfExists`(
    IN dbName tinytext,
    IN tableName tinytext,
    IN fieldName tinytext)
BEGIN
    IF EXISTS (
        SELECT * FROM information_schema.COLUMNS
        WHERE column_name=fieldName
        and table_name=tableName
        and table_schema=dbName
        )
    THEN
        set @ddl=CONCAT('ALTER TABLE `',dbName,'`.`',tableName,
            '` DROP COLUMN `',fieldName);
        prepare stmt from @ddl;
        execute stmt;
    END IF;
END$$

DROP PROCEDURE IF EXISTS `changeColumnUnlessExists`$$
CREATE PROCEDURE `changeColumnUnlessExists`(
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
END$$

DROP PROCEDURE IF EXISTS `addPrimaryUnlessExists`$$
CREATE PROCEDURE `addPrimaryUnlessExists`(
    IN dbName tinytext,
    IN tableName tinytext,
    IN fieldName tinytext
)
BEGIN
    IF NOT EXISTS (
        SELECT * FROM information_schema.TABLE_CONSTRAINTS
        WHERE TABLE_NAME=tableName
        AND CONSTRAINT_TYPE='PRIMARY KEY'
        AND CONSTRAINT_SCHEMA=dbName
        )
    THEN
        set @ddl=CONCAT('ALTER TABLE `', dbName, '`.`', tableName,
            '` ADD PRIMARY KEY (`', fieldName, '`)');
        
        prepare stmt from @ddl;
        execute stmt;
    END IF;
END$$


DROP PROCEDURE IF EXISTS `addUniqueKeyUnlessExists`$$
CREATE PROCEDURE `addUniqueKeyUnlessExists`(
    IN dbName tinytext,
    IN tableName tinytext,
    IN fieldName tinytext
)
BEGIN
    IF NOT EXISTS (
        SELECT * FROM information_schema.TABLE_CONSTRAINTS
        WHERE TABLE_NAME=tableName
        AND CONSTRAINT_TYPE='UNIQUE'
        AND CONSTRAINT_SCHEMA=dbName
        )
    THEN
        set @ddl=CONCAT('ALTER TABLE `', dbName, '`.`', tableName,
            '` ADD UNIQUE (`', fieldName, '`)');
        
        prepare stmt from @ddl;
        execute stmt;
    END IF;
END$$

DELIMITER ;
