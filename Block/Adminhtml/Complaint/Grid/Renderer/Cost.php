<?php

class M1_Complaints_Block_Adminhtml_Complaint_Grid_Renderer_Cost
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action
{
    public function render(Varien_Object $row)
    {
        return Mage::helper('core')->currency($row->getPurchaseCost(), true, false);
    }
}
