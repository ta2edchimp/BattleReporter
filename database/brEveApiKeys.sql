DROP TABLE IF EXISTS `brEveApiKeys`;
CREATE TABLE `brEveApiKeys` (
  `brApiKeyID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `brApiKeyName` varchar(25) NOT NULL DEFAULT '',
  `brApiKeyOwnerID` int(11) NOT NULL,
  `brApiKeyActive` tinyint(1) NOT NULL DEFAULT '1',
  `keyID` int(11) NOT NULL DEFAULT '0',
  `vCode` char(64) NOT NULL DEFAULT '',
  PRIMARY KEY (`brApiKeyID`),
  KEY `brEveApiKey_IX_ApiKeyByNameOwnerAndActive` (`brApiKeyName`,`brApiKeyOwnerID`,`brApiKeyActive`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
