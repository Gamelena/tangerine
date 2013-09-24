CREATE DATABASE IF NOT EXISTS `admportal`;

USE `admportal`;

GRANT ALL ON admportal.* to admportal_user identified by 'admportal_pass';
GRANT ALL ON admportal.* to admportal_user@localhost identified by 'admportal_pass';