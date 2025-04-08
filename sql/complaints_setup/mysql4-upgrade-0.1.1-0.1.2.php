<?php

$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE `{$this->getTable('complaints/item')}` ADD COLUMN `rabat` DECIMAL(12,4) NOT NULL DEFAULT '0.0000' AFTER `comments`;

");

$installer->endSetup();