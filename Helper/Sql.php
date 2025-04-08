<?php

class M1_Complaints_Helper_Sql extends Mage_Core_Helper_Abstract
{
    public function writeData($tableName, $binds)
    {
        $parameters = array_keys($binds);
        $write = Mage::getSingleton("core/resource")->getConnection("core_write");
        $query = "INSERT INTO $tableName";
        $query .= "(" . implode(',', $parameters) . ") VALUES ";
        $query .= "(:" . implode(', :', $parameters) . ")";

        $write->query($query, $binds);
    }
}
