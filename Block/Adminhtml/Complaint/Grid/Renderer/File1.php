<?php

class M1_Complaints_Block_Adminhtml_Complaint_Grid_Renderer_File1
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action
{
    public function render(Varien_Object $row)
    {
        $complaintPath = Mage::getHelper('complaints/data')->getComplaintPath();
        $this->getColumn()->setActions(array(
            array(
                'url' => $complaintPath . urlencode($row->getFile1()),
                'caption' => urldecode($row->getFile1()),
            )
        ));

        return parent::render($row);
    }
}
