CREATE DATABASE IF NOT EXISTS `tangerine`;

USE `tangerine`;

GRANT ALL ON tangerine.* to tangerine_user identified by 'tangerine_pass';
GRANT ALL ON tangerine.* to tangerine_user@localhost identified by 'tangerine_pass';
