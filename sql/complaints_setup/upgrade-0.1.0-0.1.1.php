<?php

$installer = $this;

$installer->startSetup();

$tblItem = $this->getTable('complaints/item');
$tblSalesItem = $this->getTable('sales/item');
$tblCatalogInventoryStock = $this->getTable('cataloginventory_stock');
$tblReceptionLocations = $this->getTable('reception_locations');

$installer->run("

ALTER TABLE `$tblItem` ADD COLUMN `stock_id` int(10) unsigned DEFAULT NULL AFTER `entity_id`;
ALTER TABLE `$tblItem` CHANGE COLUMN `item_id` `item_id` int(10) unsigned DEFAULT NULL;
ALTER TABLE `$tblItem` ADD COLUMN `st_id` int(10) unsigned DEFAULT NULL AFTER `item_id`;
ALTER TABLE `$tblItem` ADD COLUMN `order_item_id` int(10) unsigned DEFAULT NULL AFTER `st_id`;
ALTER TABLE `$tblItem` ADD COLUMN `komentarz` text DEFAULT NULL;

UPDATE `$tblItem` ci,`$tblSalesItem` si SET ci.komentarz=si.komentarz,ci.order_item_id=si.item_id WHERE si.entity_id=ci.item_id;
UPDATE `$tblItem` ci,`$tblCatalogInventoryStock` cis SET ci.stock_id = cis.stock_id
	WHERE ci.stock_id IS NULL AND cis.stock_code = CONCAT(
	(SELECT stock_code FROM `$tblCatalogInventoryStock` cis,`$tblReceptionLocations` kol 
		WHERE kol.stock_id=cis.stock_id AND kol.location_id=2),'_complaints');

");

$installer->endSetup();