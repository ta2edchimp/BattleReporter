CREATE TABLE `brVideos` (
  `videoID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `battleReportID` int(11) DEFAULT NULL,
  `videoUrl` text,
  PRIMARY KEY (`videoID`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;