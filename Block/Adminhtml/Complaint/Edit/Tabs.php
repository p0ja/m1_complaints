<?php

class M1_Complaints_Block_Adminhtml_Complaint_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('item_tabs');
        $this->setDestElementId('edit_form');
    }

    protected function _beforeToHtml()
    {
        $model = Mage::registry('item_data');
        if (in_array($model->getNaStanie(), array(
            Mage_Sales_Model_Item_Status::STATUS_COMPLAINT,
            Mage_Sales_Model_Item_Status::STATUS_COMPLAINT_EXTERNAL
        ), true)) {

            $this->addTab('complaint_section', array(
                'label' => Mage::helper('complaints')->__('Complaint'),
                'title' => Mage::helper('complaints')->__('Complaint'),
                'content' => $this->getLayout()->createBlock('complaints/adminhtml_complaint_edit_tab_complaint')->toHtml(),
                'active' => false
            ));
        }

        return parent::_beforeToHtml();
    }

}