<?php

class M1_Complaints_Block_Adminhtml_Complaint_Grid_Renderer_Product
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action
{
    public function render(Varien_Object $row)
    {
        $product = Mage::getModel('sales/item')->load($row->getItemId());
        $this->getColumn()->setActions(array(
            array(
                'url' => $this->getUrl('sales/adminhtml_item/edit',
                    array(
                        'entity_id' => $product->getId()
                    )
                ),
                'caption' => "Edit",
            )
        ));

        return parent::render($row);
    }
}
