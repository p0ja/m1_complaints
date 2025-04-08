<?php

class M1_Complaints_Model_Mysql4_Item_Collection extends Mage_Sales_Model_Mysql4_Item_Collection
{
	public function _construct()
	{
		parent::_construct();
		$this->_init('complaints/item');
	}
    
    public function addComplaintToSelect($ws=true)
    {
        $this->getSelect()->joinLeft(
            array('oi' => $this->getTable('sales/order_item')),
            'main_table.order_item_id = oi.item_id',
			array('product_id','order_id','qty_ordered','product_options','sku','name', 'base_cost as purchase_amount')
		);
        $this->getSelect()->joinLeft(
            array('si' => $this->getTable('cataloginventory/stock_item')),
            'si.product_id = oi.product_id',
			array('product_id','stock_id','qty')
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
            array('date_add(sg.created_at, interval 30 day) as deadline')
        ); 
        if($ws){
        	$this->getSelect()->where('s.stock_code like "%'.M1_Complaints_Model_Item::COMPLAINT_MAGAZYN_SUFFIX.'%" and si.qty > 0');
        }
        $this->getSelect()->group('main_table.entity_id');
        
        return $this;
    }
    
    public function addItemIdFilter($array)
	{
        $select = $this->getSelect();
		if(is_array($array)) {
            $select->where('main_table.item_id in ('.implode(',',$array).')');
        } else {
            $select->where('main_table.item_id = 0');
        }

		return $this;
	}
	
    public function getSelectCountSql()
    {
        $this -> _renderFilters();

        $countSelect = clone $this -> getSelect();
        $countSelect -> reset(Zend_Db_Select::ORDER);
        $countSelect -> reset(Zend_Db_Select::LIMIT_COUNT);
        $countSelect -> reset(Zend_Db_Select::LIMIT_OFFSET);
        $countSelect -> reset(Zend_Db_Select::COLUMNS);
        $countSelect -> reset(Zend_Db_Select::GROUP);

        $countSelect -> from('', 'COUNT(DISTINCT main_table.entity_id)');

        return $countSelect;
    }
}
