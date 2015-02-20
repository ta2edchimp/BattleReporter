DROP TABLE IF EXISTS `brBattles`;
CREATE TABLE `brBattles` (
  `battleReportID` int(11) NOT NULL AUTO_INCREMENT,
  `brTitle` text,
  `brStartTime` int(11) NOT NULL DEFAULT '0',
  `brEndTime` int(11) NOT NULL DEFAULT '0',
  `solarSystemID` int(11) NOT NULL DEFAULT '0',
  `brPublished` tinyint(1) NOT NULL DEFAULT '0',
  `brCreatorUserID` int(11) NOT NULL DEFAULT '0',
  `brCreateTime` int(11) NOT NULL DEFAULT '0',
  `brDeleteUserID` int(11) NOT NULL DEFAULT '0',
  `brDeleteTime` int(11) DEFAULT NULL,
  `brUniquePilotsTeamA` smallint(6) NOT NULL DEFAULT '0',
  `brUniquePilotsTeamB` smallint(6) NOT NULL DEFAULT '0',
  `brUniquePilotsTeamC` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`battleReportID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
