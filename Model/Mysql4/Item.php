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

    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        if (!$object->getShippingDate()) {
            $object->setShippingDate(new Zend_Db_Expr('NULL'));
        }
        if (!$object->getComplaintRecivedDate()) {
            $object->setComplaintRecivedDate(new Zend_Db_Expr('NULL'));
        }
        if (!$object->getReturnDate()) {
            $object->setReturnDate(new Zend_Db_Expr('NULL'));
        }
    }
}