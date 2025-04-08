<?php

class M1_Complaints_Block_Adminhtml_Complaint_Grid_Renderer_Product 
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action
{
    public function render(Varien_Object $row)
    {
        $product = Mage::getModel('salesext29/item')->load($row->getItemId());
        $this->getColumn()->setActions(array(array(
            'url'     => $this->getUrl('salesext29/adminhtml_item/edit', array('entity_id' => $product->getId())),
            'caption' => "Edycja",
        )));
        echo $row->getFullName();
        echo " ";
        return parent::render($row);
    }
}
