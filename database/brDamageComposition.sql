DROP TABLE IF EXISTS `brDamageComposition`;
CREATE TABLE `brDamageComposition` (
  `brDamageCompositionID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `brReceivingCombatantID` int(11) NOT NULL DEFAULT '0',
  `brDealingCombatantID` int(11) NOT NULL DEFAULT '0',
  `brDamageDealt` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`brDamageCompositionID`),
  KEY `brDamageComposition_IX_brReceivingCombatantID` (`brReceivingCombatantID`),
  KEY `brDamageComposition_IX_brDealingCombatantID` (`brDealingCombatantID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
