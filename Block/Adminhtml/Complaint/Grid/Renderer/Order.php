<?php

class M1_Complaints_Block_Adminhtml_Complaint_Grid_Renderer_Order
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action
{
    public function render(Varien_Object $row)
    {
        $this->getColumn()->setActions(array(
            array(
                'url' => $this->getUrl('adminhtml/sales_order/view', array('order_id' => $row->getOrderId())),
                'caption' => $row->getIncrementId(),
            )
        ));
        return parent::render($row);
    }
}
