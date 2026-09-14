<?php

$installer = $this;

$installer->startSetup();

$tblItem = $this->getTable('complaints/item');
$tblSalesItem = $this->getTable('sales/item');

// Changed: the migration filter read "na_stanie => 1", which is not valid SQL, so the install failed.
// It now copies the items in complaint status, using the same statuses Block_Adminhtml_Complaint_Edit_Tabs
// treats as a complaint.
$complaintStatuses = (int)Mage_Sales_Model_Item_Status::STATUS_COMPLAINT . ','
    . (int)Mage_Sales_Model_Item_Status::STATUS_COMPLAINT_EXTERNAL;

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
	WHERE si.`na_stanie` IN ($complaintStatuses);
");

$installer->endSetup();