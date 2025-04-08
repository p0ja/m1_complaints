<?php

class M1_Complaints_Block_Adminhtml_Complaint_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();

        $this->setId('complaintGrid');
        $this->setDefaultSort('shippment_date');
        $this->setUseAjax(true);
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('complaints/item')->getCollection()->addComplaintToSelect(false);
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() === 'order' && $column->getFilter()->getValue() === 'NULL') {
            $this->getCollection()->addFieldToFilter('o.increment_id', array('null'=>true));
        } else {
            parent::_addColumnFilterToCollection($column);
        }

        return $this;
    }
    
    protected function _prepareColumns()
    {
        $delay = Mage::getStoreConfig('complaintsconfig/complaints/delay');
        if(!$delay) {
            $delay = 30;
        }

        $this->addColumn('id', array(
            'header'   => Mage::helper('complaints')->__('ID'),
			'index'     => 'main_table.entity_id',
            'width'    => '10px',
            'renderer' => 'complaints/adminhtml_complaint_grid_renderer_id'
        ));
		
	    $this->addColumn('courier', array(
            'header'   => Mage::helper('complaints')->__('Courier'),
			'index'     => 'courier',
            'width'    => '100px',
        ));
		
        $this->addColumn('order', array(
            'header'   => Mage::helper('complaints')->__('Increment ID'),
			'index'     => 'o.increment_id',
            'width'    => '50px',
            'renderer' => 'complaints/adminhtml_complaint_grid_renderer_order'
        ));
		
        $this->addColumn('name', array(
            'header'    => Mage::helper('complaints')->__('Product Name'),
            'index'     => 'name',
        	'filter_index' => 'oi.name',
            'width'    => '500px',
        ));
		
        $this->addColumn('purchase_cost', array(
            'header'    => Mage::helper('complaints')->__('Purchase Cost'),
            'index'     => 'purchase_cost',
            'align'     => 'right',
            'width'    => '50px',
            'renderer' => 'complaints/adminhtml_complaint_grid_renderer_cost'
        ));
        
        $this->addColumn('shipment_date', array(
            'header'    => Mage::helper('complaints')->__('Shipment Date'),
            'index'		=> 'shipment_date',
			'type'		=> 'datetime',
			'width'     => '50px',
            'renderer' => 'complaints/adminhtml_complaint_grid_renderer_shipment'
        ));

        $this->addColumn('complaint_number', array(
            'header'    => Mage::helper('complaints')->__('Complaint Number'),
            'index'     => '`main_table`.complaint_number',
			'width'     => '50px',
        	'renderer' => 'complaints/adminhtml_complaint_grid_renderer_complaintnr'
        ));
		
        $this->addColumn('deadline', array(
            'header'    => Mage::helper('complaints')->__('Deadline'),
            'index'		=> "date_add(shipment_date, interval $delay day)",
            'type'      => 'datetime',
            'width'     => '50px',
            'renderer' => 'complaints/adminhtml_complaint_grid_renderer_deadline'
        ));
		
        $this->addColumn('complaint_status', array(
            'header'    => Mage::helper('complaints')->__('Complaint Status'),
            'index'     => 'complaint_status',
            'type'  => 'options',
			'width'    => '200px',
            'options'   => Mage::getSingleton('complaints/item_status')->toOptionArray(),
        ));
		
        return parent::_prepareColumns();
    }

    protected function _getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

	protected function _prepareMassaction()
    {
		$this->setMassactionIdField('entity_id');
		$this->getMassactionBlock()->setFormFieldName('item_ids');
		
        $this->getMassactionBlock()->addItem(
      		'export', array(
                'label'=>$this->__('Export to file'),
			    'url'=>$this->getUrl('*/*/csvexport')
            )
        );

        parent::_prepareMassaction();
	}

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('entity_id' => $row->getEntityId()));
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }
}
