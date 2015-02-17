DROP TABLE IF EXISTS `brVideos`;
CREATE TABLE `brVideos` (
  `videoID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `battleReportID` int(11) DEFAULT NULL,
  `videoUrl` text,
  `videoPoVCombatantID` int(11) DEFAULT NULL,
  PRIMARY KEY (`videoID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;