DROP TABLE IF EXISTS `brCorporations`;
CREATE TABLE `brCorporations` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `corporationID` int(11) NOT NULL,
  `corporationName` text NOT NULL,
  `allianceID` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
