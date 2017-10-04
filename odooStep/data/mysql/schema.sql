
# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

#-----------------------------------------------------------------------------
#-- ODOOSTEP_CONFIG
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `ODOOSTEP_CONFIG`;


CREATE TABLE `ODOOSTEP_CONFIG`
(
	`ID` INTEGER  NOT NULL AUTO_INCREMENT,
	`URL` VARCHAR(255)  NOT NULL,
	`DB` VARCHAR(255)  NOT NULL,
	`USERNAME` VARCHAR(255)  NOT NULL,
	`PASSWORD` VARCHAR(255)  NOT NULL,
	PRIMARY KEY (`ID`)
)ENGINE=InnoDB ;
#-----------------------------------------------------------------------------
#-- ODOOSTEP_STEP
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `ODOOSTEP_STEP`;


CREATE TABLE `ODOOSTEP_STEP`
(
	`ID` INTEGER  NOT NULL AUTO_INCREMENT,
	`STEP_ID` VARCHAR(255)  NOT NULL,
	`PRO_UID` VARCHAR(32) default '',
	`NOMBRE` VARCHAR(128)  NOT NULL,
	`MODEL` VARCHAR(128)  NOT NULL,
	`METHOD` VARCHAR(128)  NOT NULL,
	`PARAMETERS` MEDIUMTEXT,
	`KW_PARAMETERS` MEDIUMTEXT,
	PRIMARY KEY (`ID`)
)ENGINE=InnoDB ;
# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
