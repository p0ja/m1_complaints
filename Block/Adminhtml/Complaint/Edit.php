<?php

class M1_Complaints_Block_Adminhtml_Complaint_Edit
    extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_objectId = 'entity_id';
        $this->_blockGroup = 'complaints';
        $this->_controller = 'adminhtml_complaint';

        parent::__construct();

        $this->_addButton('saveandcontinue', array(
            'label' => Mage::helper('complaints')->__('Save and continue'),
            'onclick' => 'saveAndContinueEdit()',
            'class' => 'save',
        ));

        $this->_formScripts[] = "
		Validation.add('validate-date2', 'Please enter a valid date.', function(v) {
            var ar = v.split('-');
            if(ar.length > 2){
                if((ar[2].length == 2) && (ar[0].length == 2)) v = '20' + v;
                ar = v.split('-');
            }
            if(ar.length > 3){return false;}
   	        var test = new Date(ar[0],ar[1],ar[2],0,0,0);
    	    return Validation.get('IsEmpty').test(v) || !isNaN(test);
	    });
        function saveAndContinueEdit(back){
            editForm.submit($('edit_form').action+'back/edit/');
        }
	    ";
    }

    public function getHeaderText()
    {
        $orderId = Mage::registry('item_data')->getOrderId();
        $order = Mage::getModel('sales/order')->load($orderId);
        $url = $this->getUrl('adminhtml/sales_order/view', array('order_id' => $order->getId()));
        $linkHtml = "<a href=$url>{$order->getIncrementId()}</a>";

        return Mage::helper('complaints')->__('Edit complaint order item %s', $linkHtml);
    }
}
