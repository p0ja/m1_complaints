<?php

class M1_Complaints_Block_Adminhtml_Complaint_Grid_Renderer_Action extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action
{
    public function render(Varien_Object $row)
    {
        $this->getColumn()->setActions(array(
            array(
                'url' => $this->getUrl('*/*/edit', array('entity_id' => $row->getId())),
                'caption' => Mage::helper('complaints')->__('Edit'),
            )
        ));
        return parent::render($row);
    }
}
