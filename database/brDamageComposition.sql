DROP TABLE IF EXISTS `brDamageComposition`;
CREATE TABLE `brDamageComposition` (
  `brDamageCompositionID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `brReceivingCombatantID` int(11) NOT NULL DEFAULT '0',
  `brDealingCombatantID` int(11) NOT NULL DEFAULT '0',
  `brDamageDealt` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`brDamageCompositionID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
