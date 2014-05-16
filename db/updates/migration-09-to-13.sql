DELIMITER $$

DROP PROCEDURE IF EXISTS `upgradeAdmportal09to13`$$

CREATE PROCEDURE `upgradeAdmportal09to13`(
    IN dbOld tinytext, 
    IN dbNew tinytext
)
BEGIN
    DECLARE done INT DEFAULT 0;
    
    DECLARE xacl_roles_id, xacl_modules_id INT;
    DECLARE xpermission CHAR(16);

    DECLARE curOldPermissions CURSOR FOR SELECT acl_roles_id, acl_modules_id, permission FROM dbOld.`acl_permissions`;
    DECLARE CONTINUE HANDLER FOR SQLSTATE '02000' SET done = 1;

    SET @dbOld=dbOld, @dbNew=dbNew;
    -- 
    -- Structure for table `acl_session`
    -- 


    set @query = concat('DROP TABLE IF EXISTS ', @dbNew ,'.acl_session');

    PREPARE stmt FROM @query;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;

    set @query = concat('
    CREATE TABLE IF NOT EXISTS ', @dbNew ,'.`acl_session` (
      `id` char(32) NOT NULL DEFAULT "0",
      `acl_users_id` int(11) NOT NULL,
      `acl_roles_id` int(11) NOT NULL,
      `modified` int(11) DEFAULT NULL,
      `lifetime` int(11) DEFAULT NULL,
      `data` text,
      `ip` varchar(20) NOT NULL,
      `user_agent` varchar(255) NOT NULL,
      `must_refresh` enum("0","1") NOT NULL DEFAULT "0",
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
    ');

    PREPARE stmt FROM @query;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;

    -- 
    -- Structure for table dbNew.`acl_actions`
    -- 
    set @query = concat('
    DROP TABLE IF EXISTS ', @dbNew ,'.`acl_actions`;
    ');

    PREPARE stmt FROM @query;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;

    set @query = concat('
    CREATE TABLE IF NOT EXISTS ', @dbNew ,'.`acl_actions` (
      `id` varchar(10) NOT NULL,
      `title` varchar(50) NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1
    ');

    PREPARE stmt FROM @query;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
    -- 
    -- Data for table `acl_actions`
    -- 
    set @query = concat('
    INSERT INTO ', @dbNew ,'.`acl_actions` (`id`, `title`) VALUES
      ("EDIT", "Editar"),
      ("ADD", "Agregar"),
      ("DELETE", "Eliminar"),
      ("LIST", "Listar")
    ');

    PREPARE stmt FROM @query;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
    -- 
    -- Structure for table `acl_groups`
    -- 
    set @query = concat('
    DROP TABLE IF EXISTS ', @dbNew ,'.`acl_groups`;');

    PREPARE stmt FROM @query;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;

    set @query = concat('
    CREATE TABLE IF NOT EXISTS ', @dbNew ,'.`acl_groups` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `title` varchar(45) DEFAULT NULL,
      `description` varchar(45) DEFAULT NULL,
      `approved` enum("0","1") DEFAULT "1",
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
    ');

    PREPARE stmt FROM @query;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt; 
    
    
    -- 
    -- Structure for table `acl_modules`
    -- 
    set @query = concat('
    DROP TABLE IF EXISTS `', @dbNew ,'`.`acl_modules`;');

    PREPARE stmt FROM @query;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;

    set @query = concat('
    CREATE TABLE IF NOT EXISTS `', @dbNew ,'`.`acl_modules` (
      `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
      `parent_id` int(10) unsigned DEFAULT NULL COMMENT "Id de Modulo padre",
      `title` char(200) DEFAULT NULL COMMENT "Nombre",
      `module` char(200) DEFAULT NULL COMMENT "Url de modulo, puede ser archivo XML, URl de controlador o modulo ZF",
      `tree` enum("0","1") NOT NULL DEFAULT "1",
      `refresh_on_load` enum("0","1") NOT NULL DEFAULT "0" COMMENT "Define si modulo debe ser refrescado al reseleccionar la pestana, en caso contrario mantendrÃ¡ el estatus de como se dejÃ³ al abandonarla.",
      `type` enum("xml","zend_module","legacy","iframe") NOT NULL DEFAULT "xml",
      `approved` enum("0","1") NOT NULL,
      `order` tinyint(4) unsigned NOT NULL DEFAULT "0" COMMENT "Orden en que aparece en arbol",
      `root` enum("0","1") CHARACTER SET utf8 NOT NULL DEFAULT "0" COMMENT "Define si solo puede acceder perfil ROOT (en configuracion por defecto acl_roles_id = 1)",
      `icons_id` int(11) DEFAULT NULL,
      `ownership` enum("0","1") NOT NULL DEFAULT "0",
      PRIMARY KEY (`id`),
      UNIQUE KEY `module` (`module`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
    ');

    PREPARE stmt FROM @query;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;

    -- 
    -- Data for table `acl_modules`
    -- 
    set @query = concat('
    INSERT INTO `', @dbNew ,'`.`acl_modules` 
        (`id`, `parent_id`, `title`, `module`, `tree`, `refresh_on_load`, `type`, `approved`, `order`, `root`, `icons_id`) 
    SELECT `id`, IF(`parent_id` != "0", `parent_id`, NULL), `title`, `module`, `tree`, "0", IF (`linkable` != "0", `type`, ""), 
        `approved`, `order`, `root`, IF(`parent_id` = "1" OR `parent_id` IS NULL OR `linkable`="1", "2", "1") 
        FROM `', @dbOld ,'`.`acl_modules` ;
    ');


    PREPARE stmt FROM @query;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;

    set @query = concat('
    INSERT INTO ', @dbNew ,'.`acl_modules` 
        (`parent_id`, `title`, `module`, `tree`, `refresh_on_load`, `type`, `approved`, `order`, `root`, `icons_id`) VALUES
        ((SELECT id FROM ', @dbOld ,'.acl_modules WHERE title="Configuraci&oacute;n" AND (parent_id IS NULL OR parent_id = "0") LIMIT 1), "&Iacute;conos", "icons.xml", "1", "0", "xml", "1", 7, "1", "16"),
        ((SELECT id FROM ', @dbOld ,'.acl_modules WHERE module="settings.xml" LIMIT 1), "Avanzado", "settings-advanced.xml", "1", "0", "xml", "1", 0, "1", "4");
    ');

    PREPARE stmt FROM @query;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;

        -- 
    -- Data for table `acl_modules_actions`
    -- 
    -- 
    -- Structure for table `acl_modules_actions`
    -- 
    set @query = concat('
    DROP TABLE IF EXISTS `', @dbNew ,'`.`acl_modules_actions`;');

    PREPARE stmt FROM @query;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;

    set @query = concat('
    CREATE TABLE IF NOT EXISTS `', @dbNew ,'`.`acl_modules_actions` (
      `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
      `acl_modules_id` int(11) NOT NULL,
      `acl_actions_id` varchar(10) NOT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `acl_modules_id` (`acl_modules_id`,`acl_actions_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
    ');

    PREPARE stmt FROM @query;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
    set @query = concat('
    INSERT INTO `', @dbNew ,'`.`acl_modules_actions` (`id`, `acl_modules_id`, `acl_actions_id`) VALUES
    (1, (SELECT id FROM acl_modules WHERE title="Configuraci&oacute;n" AND (parent_id IS NULL) LIMIT 1), "LIST"),
    (2, (SELECT id FROM acl_modules WHERE module="personal-info.xml" LIMIT 1), "EDIT"),
    (3, (SELECT id FROM acl_modules WHERE module="personal-info.xml" LIMIT 1), "LIST"),
    (4, (SELECT id FROM acl_modules WHERE module="modules.xml" LIMIT 1), "ADD"),
    (5, (SELECT id FROM acl_modules WHERE module="modules.xml" LIMIT 1), "DELETE"),
    (6, (SELECT id FROM acl_modules WHERE module="modules.xml" LIMIT 1), "EDIT"),
    (7, (SELECT id FROM acl_modules WHERE module="modules.xml" LIMIT 1), "LIST"),
    (8, (SELECT id FROM acl_modules WHERE module="users.xml" LIMIT 1), "ADD"),
    (9, (SELECT id FROM acl_modules WHERE module="users.xml" LIMIT 1), "DELETE"),
    (10, (SELECT id FROM acl_modules WHERE module="users.xml" LIMIT 1), "EDIT"),
    (11, (SELECT id FROM acl_modules WHERE module="users.xml" LIMIT 1), "LIST"),
    (12, (SELECT id FROM acl_modules WHERE module="phpinfo.xml" LIMIT 1), "LIST"),
    (13, (SELECT id FROM acl_modules WHERE module="roles.xml" LIMIT 1), "ADD"),
    (14, (SELECT id FROM acl_modules WHERE module="roles.xml" LIMIT 1), "DELETE"),
    (15, (SELECT id FROM acl_modules WHERE module="roles.xml" LIMIT 1), "EDIT"),
    (16, (SELECT id FROM acl_modules WHERE module="roles.xml" LIMIT 1), "LIST"),
    (17, (SELECT id FROM acl_modules WHERE module="settings.xml" LIMIT 1), "EDIT"),
    (18, (SELECT id FROM acl_modules WHERE module="settings.xml" LIMIT 1), "LIST"),
    (19, (SELECT id FROM acl_modules WHERE module="permissions.xml" LIMIT 1), "LIST"),
    (20, (SELECT id FROM acl_modules WHERE module="icons.xml" LIMIT 1), "ADD"),
    (21, (SELECT id FROM acl_modules WHERE module="icons.xml" LIMIT 1), "DELETE"),
    (22, (SELECT id FROM acl_modules WHERE module="icons.xml" LIMIT 1), "EDIT"),
    (23, (SELECT id FROM acl_modules WHERE module="icons.xml" LIMIT 1), "LIST"),
    (24, (SELECT id FROM acl_modules WHERE module="settings-advanced.xml" LIMIT 1), "ADD"),
    (25, (SELECT id FROM acl_modules WHERE module="settings-advanced.xml" LIMIT 1), "DELETE"),
    (26, (SELECT id FROM acl_modules WHERE module="settings-advanced.xml" LIMIT 1), "EDIT"),
    (27, (SELECT id FROM acl_modules WHERE module="settings-advanced.xml" LIMIT 1), "LIST");
    ');

    PREPARE stmt FROM @query;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
    
    set @query = concat(
        'INSERT IGNORE INTO `', @dbNew ,'`.`acl_modules_actions` (`acl_modules_id`, `acl_actions_id`) 
        SELECT DISTINCT `acl_modules_id`, `permission` FROM `', @dbOld ,'`.`acl_permissions`'
    );
    
    PREPARE stmt FROM @query;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
    
    -- 
    -- Structure for table dbNew.`acl_groups_modules_actions`
    -- 
    set @query = concat('
    DROP TABLE IF EXISTS ', @dbNew ,'.`acl_groups_modules_actions`;');

    PREPARE stmt FROM @query;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;

    set @query = concat('
    CREATE TABLE IF NOT EXISTS ', @dbNew ,'.`acl_groups_modules_actions` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `acl_modules_actions_id` int(10) NOT NULL,
      `acl_groups_id` int(11) NOT NULL,
      `acl_modules_item_id` int(11) NOT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `acl_modules_actions_id` (`acl_modules_actions_id`,`acl_groups_id`,`acl_modules_item_id`),
      KEY `fk_acl_groups_modules_actions_acl_modules_actions1` (`acl_modules_actions_id`),
      KEY `fk_acl_groups_modules_actions_acl_groups1` (`acl_groups_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
    ');

    PREPARE stmt FROM @query;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;

    -- 
    -- Structure for table `web_icons`
    -- 
    set @query = concat('
    DROP TABLE IF EXISTS ', @dbNew ,'.`web_icons`;');

    PREPARE stmt FROM @query;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;

    set @query = concat('
    CREATE TABLE IF NOT EXISTS ', @dbNew ,'.`web_icons` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `title` varchar(50) NOT NULL,
      `image` varchar(50) NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
    ');

    PREPARE stmt FROM @query;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
    -- 
    -- Data for table `web_icons`
    -- 
    set @query = concat('
    INSERT INTO ', @dbNew ,'.`web_icons` (`id`, `title`, `image`) VALUES
    (1, "Sphere Green", "033d4c25green-sphere.png"),
    (2, "Sphere Blue", "225ca2a1step4c.png"),
    (3, "Sphere Red", "23cf88ccred-sphere-2.png"),
    (4, "Sphere Yellow", "dedbeb01yellow-sphere.png"),
    (5, "Keys", "a3093747roles.png"),
    (6, "Module", "03e490e9blockdevice.png"),
    (7, "Roles", "547353feuser-group-icon.png"),
    (8, "Teams", "630e0daesocial-networking-package.jpg"),
    (9, "Online", "ab789f0auser-online.png"),
    (10, "Chart", "325d3cbfarea-chart-256.png"),
    (11, "Magnifier", "905dbb5bwindows-7-magnifier.png"),
    (12, "Settings", "e0d79f05setting-icon.png"),
    (13, "Settings 2", "91732629iphone-settings-icon.png"),
    (14, "Audit", "751f9170audit.png"),
    (15, "Setup", "0d7df408setup-l.png"),
    (16, "USSD", "c62b4507bitdefender-ussd-wipe-stopper.png"),
    (17, "User", "a4c40f07actions-im-user-icon.png");
    ');

    PREPARE stmt FROM @query;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;

    -- 
    -- Structure for table `acl_roles` 
    -- 
    set @query = concat('
    DROP TABLE IF EXISTS ', @dbNew ,'.`acl_roles`;');

    PREPARE stmt FROM @query;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;

    set @query = concat('
    CREATE TABLE IF NOT EXISTS ', @dbNew ,'.`acl_roles` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `role_name` varchar(64) NOT NULL,
      `description` char(100) NOT NULL,
      `approved` enum("0","1") NOT NULL DEFAULT "1",
      `must_refresh` enum("0","1") NOT NULL DEFAULT "0",
      PRIMARY KEY (`id`),
      UNIQUE KEY `role_name` (`role_name`)
    ) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;
    ');

    PREPARE stmt FROM @query;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;

    -- 
    -- Data for table `acl_roles`
    -- 
    set @query = concat('
    INSERT INTO ', @dbNew ,'.`acl_roles` (`id`, `role_name`, `description`, `approved`) 
    SELECT `id`, `role_name`, `description`, `approved` FROM  ', @dbOld ,'.`acl_roles`;
    ');

    PREPARE stmt FROM @query;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;

    -- 
    -- Structure for table `acl_users`
    -- 

    set @query = concat('
    DROP TABLE IF EXISTS ', @dbNew ,'.`acl_users`;');

    PREPARE stmt FROM @query;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;

    set @query = concat('
    CREATE TABLE IF NOT EXISTS ', @dbNew ,'.`acl_users` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `acl_roles_id` int(4) NOT NULL,
      `user_name` varchar(64) NOT NULL,
      `password` varchar(200) NOT NULL,
      `first_names` varchar(50) NOT NULL,
      `last_names` varchar(50) NOT NULL,
      `email` varchar(50) NOT NULL,
      `approved` enum("0","1") NOT NULL DEFAULT "0",
      PRIMARY KEY (`id`),
      UNIQUE KEY `user_name` (`user_name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
    ');

    PREPARE stmt FROM @query;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;

    -- 
    -- Data for table `acl_users`
    -- 

    set @query = concat('
    INSERT INTO ', @dbNew ,'.`acl_users` 
        (`id`, `acl_roles_id`, `user_name`, `password`, `first_names`, `last_names`, `email`, `approved`) 
        SELECT `id`, `acl_roles_id`, `user_name`, `password`, `first_names`, `last_names`, `email`, `approved` 
        FROM ', @dbOld ,'.`acl_users`;
    ');

    PREPARE stmt FROM @query;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;

    -- 
    -- Structure for table `acl_users_groups`
    -- 
    set @query = concat('
    DROP TABLE IF EXISTS ', @dbNew ,'.`acl_users_groups`;');

    PREPARE stmt FROM @query;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;

    set @query = concat('
    CREATE TABLE IF NOT EXISTS ', @dbNew ,'.`acl_users_groups` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `acl_users_id` int(11) NOT NULL,
      `acl_groups_id` int(11) NOT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `acl_users_id` (`acl_users_id`,`acl_groups_id`),
      KEY `fk_acl_users_groups_acl_users1` (`acl_users_id`),
      KEY `fk_acl_users_groups_acl_groups1` (`acl_groups_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
    ');

    PREPARE stmt FROM @query;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
    -- 
    -- Structure for table `log_book`
    -- 
    set @query = concat('
    DROP TABLE IF EXISTS ', @dbNew ,'.`log_book`;');

    PREPARE stmt FROM @query;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;

    set @query = concat('
    CREATE TABLE IF NOT EXISTS ', @dbNew ,'.`log_book` (
      `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
      `user` char(40) NOT NULL,
      `table` char(40) NOT NULL,
      `action` char(40) NOT NULL,
      `condition` char(200) NOT NULL,
      `acl_roles_id` int(11) NOT NULL,
      `ip` char(200) NOT NULL,
      `stamp` datetime NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB AUTO_INCREMENT=134 DEFAULT CHARSET=utf8;
    ');

    PREPARE stmt FROM @query;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
    -- 
    -- Structure for table `tables_logged`
    -- 

    --
    -- Estructura para la vista `tables_logged`
    --
    set @query = concat('
    DROP TABLE IF EXISTS ', @dbNew ,'.`tables_logged`;');

    PREPARE stmt FROM @query;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;

    set @query = concat('
    DROP VIEW IF EXISTS ', @dbNew ,'.`tables_logged`;');

    PREPARE stmt FROM @query;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;

    set @query = concat('
    CREATE VIEW ', @dbNew ,'.`tables_logged` AS select distinct `log_book`.`table` AS `id`,`log_book`.`table` AS `title` from `log_book` order by `log_book`.`table`;
    ');

    PREPARE stmt FROM @query;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;


    -- 
    -- Structure for table `web_settings`
    -- 
    set @query = concat('
    DROP TABLE IF EXISTS ', @dbNew ,'.`web_settings`;');

    PREPARE stmt FROM @query;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;

    set @query = concat('
    CREATE TABLE IF NOT EXISTS ', @dbNew ,'.`web_settings` (
      `id` char(255) NOT NULL DEFAULT "",
      `list` char(255) NOT NULL,
      `value` char(255) NOT NULL DEFAULT "0",
      `type` char(255) NOT NULL DEFAULT "",
      `description` text NOT NULL,
      `ord` int(11) NOT NULL DEFAULT "0",
      `group` char(255) NOT NULL,
      `function` char(255) NOT NULL,
      `approved` enum("0","1") NOT NULL,
      `path` varchar(50) NOT NULL,
      `url` varchar(256) DEFAULT NULL,
      `regExp` varchar(60) NOT NULL,
      `invalidMessage` varchar(50) NOT NULL,
      `promptMessage` varchar(50) NOT NULL,
      `formatter` varchar(25) NOT NULL,
      `xml_children` text NOT NULL,
      PRIMARY KEY (`id`),
      KEY `group` (`group`)
    ) ENGINE=MyIsam DEFAULT CHARSET=latin1;
    ');

    PREPARE stmt FROM @query;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;

    -- 
    -- Data for table `web_settings`
    -- 
    set @query = concat('
    INSERT INTO `', @dbNew ,'`.`web_settings` 
      (`id`, `list`, `value`, `type`, `description`, `ord`, `group`, `function`, `approved`, `path`, `url`, `regExp`, `invalidMessage`, `promptMessage`, `formatter`, `xml_children`) 
      VALUES
      ("credits", "", "&copy; Zweicom 2014", "dijit-form-validation-text-box", "", "2", "Admin", "", "1", "", NULL, "", "", "", "", ""),
      ("query_log", "", "1", "dijit-form-check-box", "", "1", "Debug", "", "1", "", NULL, "", "", "", "", ""),
      ("titulo_adm", "", (SELECT `value` FROM `', @dbOld ,'`.`web_settings` WHERE id="titulo_adm" LIMIT 1) , "dijit-form-validation-text-box", "", "1", "Admin", "", "1", "", NULL, "", "", "", "", ""),
      ("transactions_log", "", "1", "dijit-form-check-box", "", "1", "Debug", "", "1", "", NULL, "", "", "", "", ""),
      ("url_logo_oper", "", (SELECT `value` FROM `', @dbOld ,'`.`web_settings` WHERE id="url_logo_oper" LIMIT 1), "dojox-form-uploader", "", "3", "Admin", "", "1", "{ROOT_DIR}/public/upfiles/", "{BASE_URL}/upfiles/corporative/", "", "", "", "formatImage", \'&lt;thumb height="56" path="{ROOT_DIR}/public/upfiles/corporative/" /&gt;\'),
      ("url_logo_zweicom", "", "b28576bblogo-zweicom-26x34.png", "dojox-form-uploader", "", "3", "Admin", "", "1", "{ROOT_DIR}/public/upfiles/", "{BASE_URL}/upfiles/corporative/", "", "", "", "formatImage", \'&lt;thumb height="18" path="{ROOT_DIR}/public/upfiles/corporative/" /&gt;\');
    ');

    PREPARE stmt FROM @query;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;

    -- 
    -- Structure for table `acl_roles_modules_actions`
    -- 

    set @query = concat('
    DROP TABLE IF EXISTS ', @dbNew ,'.`acl_roles_modules_actions`;');

    PREPARE stmt FROM @query;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;

    set @query = concat('
    CREATE TABLE IF NOT EXISTS `', @dbNew ,'`.`acl_roles_modules_actions` (
      `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
      `acl_roles_id` int(11) NOT NULL,
      `acl_modules_actions_id` int(11) NOT NULL,
      `permission` enum("ALLOW","DENY") CHARACTER SET utf8 NOT NULL DEFAULT "ALLOW",
      PRIMARY KEY (`id`),
      UNIQUE KEY `acl_roles_id` (`acl_roles_id`,`acl_modules_actions_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
    ');

    PREPARE stmt FROM @query;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;

    
    set @query = concat('
    INSERT INTO `', @dbNew , '`.`acl_roles_modules_actions` (`acl_roles_id`, `acl_modules_actions_id`)
        (SELECT `acl_permissions`.`acl_roles_id`, `acl_modules_actions`.`id` 
            FROM ', @dbOld , '.`acl_permissions` INNER JOIN `', @dbNew , '`.`acl_modules_actions`
            ON 
            `acl_permissions`.`acl_modules_id` = `acl_modules_actions`.`acl_modules_id` AND
            `acl_permissions`.`permission` = `acl_modules_actions`.`acl_actions_id`)
    ');

    PREPARE stmt FROM @query;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;

    set @query = concat('
    INSERT INTO `', @dbNew , '`.`acl_roles_modules_actions` (`acl_roles_id`, `acl_modules_actions_id`) 
        VALUES
        ("1", (SELECT id FROM `', @dbNew , '`.`acl_modules_actions` WHERE acl_actions_id="LIST" AND acl_modules_id = (SELECT id FROM acl_modules WHERE module = "icons.xml"))),
        ("1", (SELECT id FROM `', @dbNew , '`.`acl_modules_actions` WHERE acl_actions_id="EDIT" AND acl_modules_id = (SELECT id FROM acl_modules WHERE module = "icons.xml"))),
        ("1", (SELECT id FROM `', @dbNew , '`.`acl_modules_actions` WHERE acl_actions_id="ADD" AND acl_modules_id = (SELECT id FROM acl_modules WHERE module = "icons.xml"))),
        ("1", (SELECT id FROM `', @dbNew , '`.`acl_modules_actions` WHERE acl_actions_id="DELETE" AND acl_modules_id = (SELECT id FROM acl_modules WHERE module = "icons.xml"))),
        ("1", (SELECT id FROM `', @dbNew , '`.`acl_modules_actions` WHERE acl_actions_id="LIST" AND acl_modules_id = (SELECT id FROM acl_modules WHERE module = "settings-advanced.xml"))),
        ("1", (SELECT id FROM `', @dbNew , '`.`acl_modules_actions` WHERE acl_actions_id="EDIT" AND acl_modules_id = (SELECT id FROM acl_modules WHERE module = "settings-advanced.xml"))),
        ("1", (SELECT id FROM `', @dbNew , '`.`acl_modules_actions` WHERE acl_actions_id="ADD" AND acl_modules_id = (SELECT id FROM acl_modules WHERE module = "settings-advanced.xml"))),
        ("1", (SELECT id FROM `', @dbNew , '`.`acl_modules_actions` WHERE acl_actions_id="DELETE" AND acl_modules_id = (SELECT id FROM acl_modules WHERE module = "settings-advanced.xml")))
    ');

    PREPARE stmt FROM @query;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
    -- 
    -- Data for table `acl_roles_modules_actions`
    -- 

/*
    OPEN curOldPermissions;

    REPEAT
        FETCH curOldPermissions INTO xacl_roles_id, xacl_modules_id, xpermission;
        IF NOT done THEN
            set @query = concat('
                INSERT INTO ', @dbNew ,'.`acl_roles_modules_actions` (`acl_modules_actions_id`, `acl_roles_id`)
                VALUES (
                    (SELECT id FROM ', @dbNew ,'.`acl_modules_actions_id` WHERE `acl_modules_id`=',xacl_modules_id, ' AND `acl_actions_id`=', xpermission, ' LIMIT 1),' 
                    , xacl_roles_id, '
                );
            ');

            PREPARE stmt FROM @query;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;
        END IF;
    UNTIL done END REPEAT;

    CLOSE curOldPermissions;
*/
END$$

DELIMITER ;


CALL upgradeAdmportal09to13('gw_promo', 'maqueta');


