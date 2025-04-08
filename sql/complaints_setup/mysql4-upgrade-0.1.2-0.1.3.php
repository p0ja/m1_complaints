<?php

$installer = $this;

$installer->startSetup();

$installer->run("

	UPDATE `core_config_data` SET `path`='complaintsconfig/complaints/delay' WHERE  `path` LIKE '%sales/complaints/delay%';
	UPDATE `core_config_data` SET `path`='complaintsconfig/complaints/filetypes' WHERE  `path` LIKE '%sales/complaints/filetypes%';

");

$installer->endSetup();