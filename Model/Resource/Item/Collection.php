<?php

/**
 * Changed: renamed from M1_Complaints_Model_Mysql4_Item_Collection (Magento 1.6+ naming). It extended
 * Mage_Sales_Model_Mysql4_Item_Collection, the collection of another module's sales/item entity, although it
 * only uses the generic collection API; it now extends the core database collection.
 */
class M1_Complaints_Model_Resource_Item_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('complaints/item');
    }

    public function addComplaintToSelect($ws = true)
    {
        // Changed: the deadline used a hard-coded 30 days and ignored the configured delay period.
        $delay = (int)Mage::getStoreConfig('complaintsconfig/complaints/delay');
        if ($delay <= 0) {
            $delay = M1_Complaints_Model_Item::COMPLAINT_DEFAULT_DELAY;
        }

        $this->getSelect()->joinLeft(
            array('oi' => $this->getTable('sales/order_item')),
            'main_table.order_item_id = oi.item_id',
            array(
                'product_id',
                'order_id',
                'qty_ordered',
                'product_options',
                'sku',
                'name',
                // Changed: aliased as purchase_cost, the name the grid column and the export read;
                // purchase_amount was never used, so the purchase cost was always empty.
                'base_cost as purchase_cost'
            )
        );
        $this->getSelect()->joinLeft(
            array('si' => $this->getTable('cataloginventory/stock_item')),
            'si.product_id = oi.product_id',
            array('product_id', 'stock_id', 'qty')
        );
        $this->getSelect()->joinLeft(
            array('s' => $this->getTable('cataloginventory/stock')),
            'main_table.stock_id = s.stock_id',
            array('stock_code')
        );
        $this->getSelect()->joinLeft(
            array('o' => $this->getTable('sales/order')),
            'oi.order_id = o.entity_id',
            array('increment_id')
        );
        $this->getSelect()->joinLeft(
            array('ssi' => $this->getTable('sales/shipment_item')),
            'oi.item_id = ssi.order_item_id',
            array('parent_id')
        );
        $this->getSelect()->joinLeft(
            array('sg' => $this->getTable('sales/shipment_grid')),
            'ssi.parent_id = sg.entity_id',
            array(
                // Added: lets M1_Complaints_Model_Item::getSentDate() use the joined shipment date instead of
                // loading the order and its shipments for every grid and export row.
                'created_at as shipment_created_at',
                'date_add(sg.created_at, interval ' . $delay . ' day) as deadline'
            )
        );
        if ($ws) {
            $this->getSelect()->where('s.stock_code like ?', '%' . M1_Complaints_Model_Item::COMPLAINT_MAGAZYN_SUFFIX . '%')
                ->where('si.qty > 0');
        }
        $this->getSelect()->group('main_table.entity_id');

        return $this;
    }

    public function addItemIdFilter($array)
    {
        $select = $this->getSelect();
        // The ids are complaint entity_ids from the grid mass action (see Grid::_prepareMassaction),
        // so never trust them as SQL.
        $complaintIds = is_array($array) ? array_filter(array_map('intval', $array)) : array();
        if ($complaintIds) {
            $select->where('main_table.entity_id IN (?)', $complaintIds);
        } else {
            $select->where('main_table.entity_id = 0');
        }

        return $this;
    }

    public function getSelectCountSql()
    {
        $this->_renderFilters();

        $countSelect = clone $this->getSelect();
        $countSelect->reset(Zend_Db_Select::ORDER);
        $countSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $countSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        $countSelect->reset(Zend_Db_Select::COLUMNS);
        $countSelect->reset(Zend_Db_Select::GROUP);

        $countSelect->from('', 'COUNT(DISTINCT main_table.entity_id)');

        return $countSelect;
    }
}
