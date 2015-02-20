CREATE TABLE `brBattlePartyGroups` (
  `battlePartyGroupID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `battlePartyGroupName` mediumtext NOT NULL,
  `battlePartyGroupOrderKey` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`battlePartyGroupID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
