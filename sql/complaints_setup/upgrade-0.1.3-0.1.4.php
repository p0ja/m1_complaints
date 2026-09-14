<?php

/**
 * Renames the Polish columns to the English names the code uses.
 *
 * Added: the refactored forms, grid, export and models read and write shipment_date, number,
 * complaint_number, and so on, but the table still had the original Polish columns. Magento saves
 * only fields that match a table column, so most of the complaint form was silently discarded.
 * Each column is renamed only if it still exists, so the script is safe on databases where it was
 * already renamed by hand. Definitions are unchanged from the install and upgrade scripts.
 */
$installer = $this;

$installer->startSetup();

$connection = $installer->getConnection();
$tblItem = $this->getTable('complaints/item');

$renames = array(
    'data_wysylki' => array('shipment_date', 'date NULL default NULL'),
    'nr_lp' => array('number', 'varchar(254) NULL default NULL'),
    'kwota_zwrotu' => array('return_amount', 'float NULL default 0'),
    'data_zwrotu' => array('return_date', 'date NULL default NULL'),
    'nr_reklamacji' => array('complaint_number', 'varchar(254) NULL default NULL'),
    'reklamacja_data_zgloszenia' => array('complaint_date', 'date NULL default NULL'),
    'reklamacja_nr_listu' => array('client_shipment_number', 'varchar(254) NULL default NULL'),
    'komentarz' => array('comment', 'text NULL default NULL'),
);

foreach ($renames as $oldName => $column) {
    list($newName, $definition) = $column;
    if ($connection->tableColumnExists($tblItem, $oldName) && !$connection->tableColumnExists($tblItem, $newName)) {
        $connection->query(sprintf(
            'ALTER TABLE %s CHANGE COLUMN %s %s %s',
            $connection->quoteIdentifier($tblItem),
            $connection->quoteIdentifier($oldName),
            $connection->quoteIdentifier($newName),
            $definition
        ));
    }
}

$installer->endSetup();
