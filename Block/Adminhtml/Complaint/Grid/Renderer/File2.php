<?php

class M1_Complaints_Block_Adminhtml_Complaint_Grid_Renderer_File2
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action
{
    public function render(Varien_Object $row)
    {
        $path = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'complaints' . DS;
        $this->getColumn()->setActions(array(
            array(
                'url' => $path . urlencode($row->getFile2()),
                'caption' => urldecode($row->getFile2()),
            )
        ));
        return parent::render($row);
    }
}
