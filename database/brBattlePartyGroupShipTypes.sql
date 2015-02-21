DROP TABLE IF EXISTS `brBattlePartyGroupShipTypes`;
CREATE TABLE `brBattlePartyGroupShipTypes` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `shipTypeID` int(11) NOT NULL,
  `brBattlePartyGroupID` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
