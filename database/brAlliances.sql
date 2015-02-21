DROP TABLE IF EXISTS `brAlliances`;
CREATE TABLE `brAlliances` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `allianceID` int(11) NOT NULL,
  `allianceName` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
