DROP TABLE IF EXISTS `brUserPermissions`;
CREATE TABLE `brUserPermissions` (
  `brUserPermissionsID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `brUserID` int(11) NOT NULL,
  `brPermission` char(10) NOT NULL DEFAULT '',
  PRIMARY KEY (`brUserPermissionsID`),
  KEY `brUserID` (`brUserID`,`brUserPermissionsID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
