<?php

class M1_Complaints_Block_Adminhtml_Complaint_Tab
    extends Mage_Adminhtml_Block_Template
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    public function _construct()
    {
        parent::_construct();

        $this->setTemplate('M1/complaints/tab.phtml');
    }

    public function getTabLabel()
    {
        return $this->__('Complaints');
    }

    public function getTabTitle()
    {
        return $this->__('Click here to view your custom tab content');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }
}
