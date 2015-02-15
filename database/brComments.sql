DROP TABLE IF EXISTS `brComments`;
CREATE TABLE `brComments` (
  `commentID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `commentUserID` int(11) NOT NULL DEFAULT '-1',
  `commentTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `commentMessage` longtext NOT NULL,
  `battleReportID` int(11) NOT NULL,
  `commentDeleteUserID` int(11) NOT NULL DEFAULT '0',
  `commentDeleteTime` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`commentID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;