<?php

class M1_Complaints_Block_Adminhtml_Complaint_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {
        $model = Mage::registry('item_data');
        
        $form = new Varien_Data_Form(array('id' => 'edit_form',
        									'action' => $this->getData('action'),
        									'method' => 'post',
        									'enctype' => 'multipart/form-data'));
        $form->setUseContainer(true);
        $complaints_count = Mage::getModel('complaints/item')->getComplaintsItemQty($model->getOrderItem()->getItemId());

        $info = "<div style=\"position:relative;width:500px;\" id=\"messages\"><ul class=\"messages\"><li class=\"notice-msg\"><ul><li>" .
 $this->__('Make sure that data encoding in the file is consistent and saved in one of supported encodings (UTF-8 or ANSI).')
. "</li></ul></li></ul></div>";


        if ($model->getFile1()) {
            $file1 = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'complaints' . DS . urlencode($model->getFile1());
        }

        if ($model->getFile2()) {
            $file2 = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'complaints' . DS . urlencode($model->getFile2());
        }

        if ($model->getStockId()) {
            $storage = $model->getStock()->getStockName();
        } else {
            $storage = '<span>No storage!</span>';
        }

        $orderItem = $model->getOrderItem();
        $qtyOrdered = (int)$orderItem->getQtyOrdered();
        $fieldset = $form->addFieldset('add_item_form', array(
			'legend' => $this->__('Order number:').' ' . $model->getIncrementId() .
			'<br/>Product name: ' . $orderItem->getName() .
			'<br/>Catalog number: ' . $orderItem->getSku() .
			'<br/>Order quantity: '. $qtyOrdered . ' (qty in complaint: '.$complaints_count.')' .
			'<br/>Storage: '.$storage .
        	'<br/>Shippment: '.$model->getOrder()->getShippingDescription()
		));

        if ($model->getId()) {
        	$fieldset->addField('entity_id', 'hidden', array(
                'name' => 'entity_id',
            ));

        	$fieldset->addField('item_id', 'hidden', array(
                'name' => 'item_id',
            ));

            $fieldset->addField('qty', 'hidden', array(
                'name' => 'qty',
            ));
        }

        $fieldset->addField('courier', 'text', array(
            'label' => Mage::helper('complaints')->__('Courier'),
            'name'  => 'courier',
        ));

        $fieldset->addField('number', 'text', array(
            'label' => Mage::helper('complaints')->__('Number'),
            'name'  => 'nr_lp',
        ));

        $fieldset->addField('complaint_date', 'date', array(
            'name'     => 'complaint_date',
            'class'    => 'validate-date2',
            'required' => false,
        	'label'    => Mage::helper('complaints')->__('Complaint date'),
            'image'    => $this->getSkinUrl('images/grid-cal.gif'),
            'format'   => 'yyyy-MM-dd',
        ));

        $fieldset->addField('sent_date', 'date', array(
            'name'     => 'sent_date',
            'class'    => 'validate-date2',
            'required' => false,
        	'label'    => Mage::helper('complaints')->__('Shipped date'),
            'image'    => $this->getSkinUrl('images/grid-cal.gif'),
            'format'   => 'yyyy-MM-dd',
        ));

        $fieldset->addField('complaint_number', 'text', array(
            'label' => Mage::helper('complaints')->__('Complaint number'),
            'name'  => 'complaint_number',
        ));

        $fieldset->addField('is_return', 'select', array(
            'label' => Mage::helper('complaints')->__('Has item returned?'),
            'name'  => 'is_return',
            'options'   => Mage::getSingleton('complaints/item_return')->toOptionArray(true),
        ));

        $fieldset->addField('complaint_ship_id', 'text', array(
            'label' => Mage::helper('complaints')->__('Client ship id'),
            'name'  => 'complaint_ship_id',
        ));

        $fieldset->addField('complaint_status', 'select', array(
            'label' => Mage::helper('complaints')->__('Complaint status'),
            'name'  => 'complaint_status',
            'options'   => Mage::getSingleton('complaints/item_status')->toOptionArray(true),
        ));

        $fieldset->addField('return_amount', 'text', array(
            'label' => Mage::helper('complaints')->__('Return amount'),
            'name'  => 'return_amount',
            'class' => 'validate-number',
        ));

        $fieldset->addField('return_date', 'date', array(
            'name'     => 'return_date',
            'class'    => 'validate-date2',
            'required' => false,
        	'label'    => Mage::helper('complaints')->__('Return date'),
            'image'    => $this->getSkinUrl('images/grid-cal.gif'),
            'format'   => 'yyyy-MM-dd',
        ));

        $fieldset->addField('comment', 'textarea', array(
            'label' => Mage::helper('complaints')->__('Comment'),
            'name'  => 'comment',
        ));

        $fieldset->addField('info', 'label' , array(
            'after_element_html' => $info,
        ));

        $fieldset->addField('file1', 'file', array(
            'label'     => Mage::helper('complaints')->__('Demage report'),
            'required'  => false,
            'name'		=> 'file1',
            'after_element_html' => ($model->getFile1()?'<br /><a href="' . $file1. '">' . urldecode($model->getFile1()) . '</a><br /><p style="margin-top: 5px"><a href="'.$this->getUrl('*/*/*/', array('_current'=>true, 'delete_file'=>'file1')).'"><span class="error">'.Mage::helper('complaints')->__('Delete').'</span></a></p>':''),
        ));

        $fieldset->addField('file2', 'file', array(
            'label'    => Mage::helper('complaints')->__('Complaint'),
            'required' => false,
            'name'     => 'file2',
            'after_element_html' => ($model->getFile2()?'<br /><a href="' . $file2. '">' . urldecode($model->getFile2()) . '</a><br /><p style="margin-top: 5px"><a href="'.$this->getUrl('*/*/*/', array('_current'=>true, 'delete_file'=>'file2')).'"><span class="error">'.Mage::helper('complaints')->__('Delete').'</span></a></p>':''),
        ));

        $fieldset->addField('rabat', 'text', array(
        		'label' => Mage::helper('complaints')->__('Sell with rabat'),
        		'name'  => 'rabat',
        ));
        
        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }


}
