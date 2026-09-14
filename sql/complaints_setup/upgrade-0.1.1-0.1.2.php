<?php

$installer = $this;

$installer->startSetup();

// Changed: the column added in 0.1.1 is komentarz, not comments, so this upgrade failed.
$installer->run("

ALTER TABLE `{$this->getTable('complaints/item')}` ADD COLUMN `rabat` DECIMAL(12,4) NOT NULL DEFAULT '0.0000' AFTER `komentarz`;

");

$installer->endSetup();