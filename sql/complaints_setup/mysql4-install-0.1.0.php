<?php

$installer = $this;

$installer->startSetup();

$tblItem = $this->getTable('complaints/item');
$tblSalesItem = $this->getTable('sales/item');

$installer->run("

CREATE TABLE IF NOT EXISTS `$tblItem` (
  `entity_id` int(10) unsigned NOT NULL auto_increment,
  `item_id` int(10) unsigned NOT NULL,
  `courier` varchar(254) NULL default NULL,
  `data_wysylki` date NULL default NULL,
  `nr_lp` varchar(254) NULL default NULL,
  `is_return` int(4) NULL default NULL,
  `kwota_zwrotu` float NULL default 0,
  `data_zwrotu` date NULL default NULL,
  `nr_reklamacji` varchar(254) NULL default NULL,
  `reklamacja_data_zgloszenia` date NULL default NULL,
  `reklamacja_nr_listu` varchar(254) NULL default NULL,
  `complaint_status` int(4) NULL default NULL,
  `file1` varchar(255) NULL default NULL, 
  `file2` varchar(255) NULL default NULL, 
  PRIMARY KEY  (`entity_id`),
  INDEX (`item_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO $tblItem(`item_id`,`kwota_zwrotu`,`data_zwrotu`,`nr_reklamacji`,`reklamacja_data_zgloszenia`,`reklamacja_nr_listu`) 
	SELECT `entity_id`,`kwota_zwrotu`,`data_zwrotu`,`nr_reklamacji`,`reklamacja_data_zgloszenia`,`reklamacja_nr_listu` 
	FROM $tblSalesItem as si
	WHERE si.`na_stanie` => 1;  
");

$installer->endSetup();