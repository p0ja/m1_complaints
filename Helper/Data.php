<?php

class M1_Complaints_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getComplaintPath()
    {
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'complaints' . DS;
    }

    public function getComplaintForm($model)
    {
        $form = new Varien_Data_Form(array(
            'id' => 'complaint_form',
            'action' => $this->getData('action'),
            'method' => 'post',
            'enctype' => 'multipart/form-data'
        ));

        $fieldset = $form->addFieldset('add_item_form', array(
            'legend' => $this->__('Order number:') . ' ' . $model->getIncrementId() .
                '<br/>Product name: ' . $model->getOrderItem()->getName() .
                '<br/>Product number: ' . $model->getOrderItem()->getSku() .
                '<br/>Quantity: ' . (int)$model->getOrderItem()->getQtyOrdered() .
                '<br/>Shippment: ' . $model->getOrder()->getShippingDescription()
        ));

        if ($model->getId()) {
            $fieldset->addField('entity_id', 'hidden', array(
                'name' => 'complaint[entity_id]',
            ));

            $fieldset->addField('order_item_id', 'hidden', array(
                'name' => 'complaint[order_item_id]',
            ));

            $fieldset->addField('qty', 'hidden', array(
                'name' => 'complaint[qty]',
            ));
        }

        $fieldset->addField('courier', 'text', array(
            'label' => Mage::helper('complaints')->__('Courier'),
            'name' => 'complaint[courier]',
        ));

        $fieldset->addField('number', 'text', array(
            'label' => Mage::helper('complaints')->__('Number'),
            'name' => 'complaint[number]',
        ));

        $fieldset->addField('complaint_date', 'date', array(
            'name' => 'complaint[complaint_date]',
            'class' => 'validate-date2',
            'required' => false,
            'label' => Mage::helper('complaints')->__('Complaint create date'),
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
            'format' => 'yyyy-MM-dd',
        ));

        $fieldset->addField('shipment_date', 'date', array(
            'name' => 'complaint[shipment_date]',
            'class' => 'validate-date2',
            'required' => false,
            'label' => Mage::helper('complaints')->__('Shipment date'),
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
            'format' => 'yyyy-MM-dd',
        ));

        $fieldset->addField('complaint_number', 'text', array(
            'label' => Mage::helper('complaints')->__('Complaint number'),
            'name' => 'complaint[complaint_number]',
        ));

        $fieldset->addField('is_return', 'select', array(
            'label' => Mage::helper('complaints')->__('Has item returned?'),
            'name' => 'complaint[is_return]',
            'options' => Mage::getSingleton('complaints/item_return')->toOptionArray(true),
        ));

        $fieldset->addField('client_shipment_number', 'text', array(
            'label' => Mage::helper('complaints')->__('Client shipment number'),
            'name' => 'complaint[client_shipment_number]',
        ));

        $fieldset->addField('complaint_status', 'select', array(
            'label' => Mage::helper('complaints')->__('Complaint status'),
            'name' => 'complaint[complaint_status]',
            'options' => Mage::getSingleton('complaints/item_status')->toOptionArray(true),
        ));

        $fieldset->addField('return_amount', 'text', array(
            'label' => Mage::helper('complaints')->__('Return amount'),
            'name' => 'complaint[return_amount]',
            'class' => 'validate-number',
        ));

        $fieldset->addField('return_date', 'date', array(
            'name' => 'complaint[return_date]',
            'class' => 'validate-date2',
            'required' => false,
            'label' => Mage::helper('complaints')->__('Return date'),
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
            'format' => 'yyyy-MM-dd',
        ));

        $complaintPath = Mage::getHelper('complaints/data')->getComplaintPath();
        if ($model->getFile1()) {
            $file1 = $complaintPath . urlencode($model->getFile1());
        }

        if ($model->getFile2()) {
            $file2 = $complaintPath . urlencode($model->getFile2());
        }

        $fieldset->addField('file1', 'file', array(
            'label' => Mage::helper('complaints')->__('Complaint details'),
            'required' => false,
            'name' => 'complaint[file1]',
            'after_element_html' => ($model->getFile1() ? '<br /><a href="' . $file1 . '">' .
                urldecode($model->getFile1()) . '</a><br /><p style="margin-top: 5px"><a href="' . $this->getUrl('*/*/*/',
                    array(
                        '_current' => true,
                        'delete_file' => 'file1'
                    )) . '"><span class="error">' . Mage::helper('complaints')->__('Delete') . '</span></a></p>' : ''),
        ));

        $fieldset->addField('file2', 'file', array(
            'label' => Mage::helper('complaints')->__('Complaint'),
            'required' => false,
            'name' => 'complaint[file2]',
            'after_element_html' => ($model->getFile2() ? '<br /><a href="' . $file2 . '">' .
                urldecode($model->getFile2()) . '</a><br /><p style="margin-top: 5px"><a href="' . $this->getUrl('*/*/*/',
                    array(
                        '_current' => true,
                        'delete_file' => 'file2'
                    )) . '"><span class="error">' . Mage::helper('complaints')->__('Delete') . '</span></a></p>' : ''),
        ));

        $info = "<div style=\"position:relative;width:500px;\" id=\"messages\">
                <ul class=\"messages\">
                <li class=\"notice-msg\"><ul><li>" .

            $this->__('Make sure that data encoding in the file is saved in one of supported encodings (UTF-8 or ANSI).')

            . "</li></ul></li></ul></div>";

        $fieldset->addField('complaints-upload-info', 'label', array(
            'after_element_html' => $info,
        ));

        return $form;
    }
}
