<?php

class M1_Complaints_Block_Adminhtml_Complaint_Grid_Renderer_File1 
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action
{
    public function render(Varien_Object $row)
    {
        $path = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'complaints' . DS;
        $this->getColumn()->setActions(array(array(
            'url'     => $path . urlencode($row->getFile1()),
            'caption' => urldecode($row->getFile1()),
        )));
        return parent::render($row);
    }
}
