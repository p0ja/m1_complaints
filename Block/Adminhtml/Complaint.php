<?php

class M1_Complaints_Block_Adminhtml_Complaint extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_complaint';
        $this->_blockGroup = 'complaints';
        $this->_headerText = $this->__('Complaint elements list');

        parent::__construct();

        $this->_removeButton('add');
    }
}
