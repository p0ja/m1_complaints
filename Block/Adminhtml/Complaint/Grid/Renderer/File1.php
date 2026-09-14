<?php

class M1_Complaints_Block_Adminhtml_Complaint_Grid_Renderer_File1
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action
{
    public function render(Varien_Object $row)
    {
        // Changed: Mage::getHelper() does not exist in Magento 1 (fatal error). The link goes through the
        // ACL-protected downloadAction() instead of a public media URL, and the caption is escaped because
        // the Action renderer prints it as raw HTML (stored XSS through the uploaded file name).
        $this->getColumn()->setActions(array(
            array(
                'url' => Mage::helper('complaints')->getComplaintFileUrl($row->getId(), 'file1'),
                'caption' => $this->escapeHtml($row->getFile1()),
            )
        ));

        return parent::render($row);
    }
}
