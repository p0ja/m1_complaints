<?php

/**
 * Changed: renamed from M1_Complaints_Model_Mysql4_Item. Magento 1.6+ names resource models Model_Resource_*
 * and extends Mage_Core_Model_Resource_Db_Abstract; the Mysql4 classes are deprecated aliases.
 */
class M1_Complaints_Model_Resource_Item extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('complaints/item', 'entity_id');
    }

    /**
     * Changed: loads the record in one query. Before, a first query fetched order_item_id by order_item_id
     * and a second query loaded the record by that same value.
     *
     * @param M1_Complaints_Model_Item $item
     * @param int $itemId
     * @return $this
     */
    public function loadByOrderItemId(M1_Complaints_Model_Item $item, $itemId)
    {
        $this->load($item, (int)$itemId, 'order_item_id');
        if (!$item->getId()) {
            $item->setData(array());
        }

        return $this;
    }

    /**
     * Stores empty date fields as NULL instead of an invalid empty date.
     *
     * Changed: shipping_date and complaint_recived_date are not columns, so only return_date was handled;
     * the real columns are shipment_date and complaint_date (upgrade 0.1.4). A field is only touched when it
     * is part of the data being saved, so a partial save cannot clear a stored date, and the parent
     * _beforeSave() is now called.
     *
     * @param Mage_Core_Model_Abstract $object
     * @return $this
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        foreach (array('shipment_date', 'complaint_date', 'return_date') as $dateField) {
            if ($object->hasData($dateField) && !$object->getData($dateField)) {
                $object->setData($dateField, new Zend_Db_Expr('NULL'));
            }
        }

        return parent::_beforeSave($object);
    }
}
