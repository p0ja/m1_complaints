<?php

class M1_Complaints_Block_Adminhtml_Complaint_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {
        $model = Mage::registry('item_data');
        $orderItem = $model->getOrderItem();
        $itemId = $orderItem->getItemId();

        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getData('action'),
            'method' => 'post',
            'enctype' => 'multipart/form-data'
        ));
        $form->setUseContainer(true);
        $complaintsCount = Mage::getModel('complaints/item')->getComplaintsItemQty($itemId);

        // Changed: order, product and warehouse values are escaped before they are placed in the legend HTML
        // (stored XSS through product names or shipping descriptions).
        if ($model->getStockId()) {
            $storage = $this->escapeHtml($model->getStock()->getStockName());
        } else {
            $storage = '<span>No storage!</span>';
        }

        $qtyOrdered = (int)$orderItem->getQtyOrdered();
        $fieldset = $form->addFieldset('add_item_form', array(
            'legend' => $this->escapeHtml($this->__('Order number:') . ' ' . $model->getIncrementId()) .
                '<br/>Product name: ' . $this->escapeHtml($orderItem->getName()) .
                '<br/>Catalog number: ' . $this->escapeHtml($orderItem->getSku()) .
                '<br/>Order quantity: ' . $qtyOrdered . ' (qty in complaint: ' . (int)$complaintsCount . ')' .
                '<br/>Storage: ' . $storage .
                '<br/>Shippment: ' . $this->escapeHtml($model->getOrder()->getShippingDescription())
        ));

        if ($model->getId()) {
            $fieldset->addField('entity_id', 'hidden', array(
                'name' => 'entity_id',
            ));

            $fieldset->addField('order_item_id', 'hidden', array(
                'name' => 'order_item_id',
            ));

            $fieldset->addField('qty', 'hidden', array(
                'name' => 'qty',
            ));
        }

        $fieldset->addField('courier', 'text', array(
            'label' => Mage::helper('complaints')->__('Courier'),
            'name' => 'courier',
        ));

        $fieldset->addField('number', 'text', array(
            'label' => Mage::helper('complaints')->__('Number'),
            'name' => 'number',
        ));

        $fieldset->addField('complaint_date', 'date', array(
            'name' => 'complaint_date',
            'class' => 'validate-date2',
            'required' => false,
            'label' => Mage::helper('complaints')->__('Complaint create date'),
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
            'format' => 'yyyy-MM-dd',
        ));

        $fieldset->addField('shipment_date', 'date', array(
            'name' => 'shipment_date',
            'class' => 'validate-date2',
            'required' => false,
            'label' => Mage::helper('complaints')->__('Shipment date'),
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
            'format' => 'yyyy-MM-dd',
        ));

        $fieldset->addField('complaint_number', 'text', array(
            'label' => Mage::helper('complaints')->__('Complaint number'),
            'name' => 'complaint_number',
        ));

        $fieldset->addField('is_return', 'select', array(
            'label' => Mage::helper('complaints')->__('Has item returned?'),
            'name' => 'is_return',
            'options' => Mage::getSingleton('complaints/item_return')->toOptionArray(true),
        ));

        $fieldset->addField('client_shipment_number', 'text', array(
            'label' => Mage::helper('complaints')->__('Client shipment number'),
            'name' => 'client_shipment_number',
        ));

        $fieldset->addField('complaint_status', 'select', array(
            'label' => Mage::helper('complaints')->__('Complaint status'),
            'name' => 'complaint_status',
            'options' => Mage::getSingleton('complaints/item_status')->toOptionArray(true),
        ));

        $fieldset->addField('return_amount', 'text', array(
            'label' => Mage::helper('complaints')->__('Return amount'),
            'name' => 'return_amount',
            'class' => 'validate-number',
        ));

        $fieldset->addField('return_date', 'date', array(
            'name' => 'return_date',
            'class' => 'validate-date2',
            'required' => false,
            'label' => Mage::helper('complaints')->__('Return date'),
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
            'format' => 'yyyy-MM-dd',
        ));

        $fieldset->addField('comment', 'textarea', array(
            'label' => Mage::helper('complaints')->__('Comment'),
            'name' => 'comment',
        ));

        // Changed: Mage::getHelper() does not exist in Magento 1 (fatal error). The links now point to the
        // ACL-protected downloadAction() instead of a public media URL, and the file name is escaped (stored XSS).
        $fieldset->addField('file1', 'file', array(
            'label' => Mage::helper('complaints')->__('Damage report'),
            'required' => false,
            'name' => 'file1',
            'after_element_html' => Mage::helper('complaints')->getComplaintFileHtml(
                $model,
                'file1',
                $this->getUrl('*/*/*/', array('_current' => true, 'delete_file' => 'file1'))
            ),
        ));

        $fieldset->addField('file2', 'file', array(
            'label' => Mage::helper('complaints')->__('Complaint'),
            'required' => false,
            'name' => 'file2',
            'after_element_html' => Mage::helper('complaints')->getComplaintFileHtml(
                $model,
                'file2',
                $this->getUrl('*/*/*/', array('_current' => true, 'delete_file' => 'file2'))
            ),
        ));

        $fieldset->addField('rabat', 'text', array(
            'label' => Mage::helper('complaints')->__('Sell with rabat'),
            'name' => 'rabat',
        ));

        $info = "<div style=\"position:relative;width:500px;\" id=\"messages\">
                <ul class=\"messages\">
                <li class=\"notice-msg\"><ul><li>" .

            $this->__('Make sure that data encoding in the file is saved in one of supported encodings (UTF-8 or ANSI).')

            . "</li></ul></li></ul></div>";

        $fieldset->addField('info', 'label', array(
            'after_element_html' => $info,
        ));

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
