<?php

class M1_Complaints_Model_Mysql4_Item extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('complaints/item', 'entity_id');
    }

    public function loadByOrderItemId(M1_Complaints_Model_Item $item, $itemId)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable(), array('order_item_id'))
            ->where('order_item_id=:order_item_id')->limit(1);

        if ($id = $this->_getReadAdapter()->fetchOne($select, array('order_item_id' => $itemId))) {
            $this->load($item, $id, 'order_item_id');
        } else {
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