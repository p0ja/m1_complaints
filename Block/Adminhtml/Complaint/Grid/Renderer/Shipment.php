<?php

class M1_Complaints_Block_Adminhtml_Complaint_Grid_Renderer_Shipment
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action
{
    public function render(Varien_Object $row)
    {
        if ($row->getSentDate()) {
            $sdate = date("d-m-Y", strtotime($row->getSentDate()));
        } else {
            $sdate = '';
        }
        return $sdate;
    }
}
