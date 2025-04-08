<?php

class M1_Complaints_Block_Adminhtml_Complaint_Grid_Renderer_Deadline
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action
{
    public function render(Varien_Object $row)
    {
        $deadline = strtotime($row->getDeadline());
        if (!$deadline) {
            return '';
        }

        $deadline_mark = 60 * 60 * 24 * 7;
        if (in_array($row->complaint_status, array(
                M1_Complaints_Model_Item_Status::STATUS_WAIT_FOR_PROTOCOL,
                M1_Complaints_Model_Item_Status::STATUS_REGISTERED,
                M1_Complaints_Model_Item_Status::STATUS_PREPARED
            ), true) && (time() > ($deadline - $deadline_mark))
        ) {
            $deadline_date = date("d-m-Y", $deadline);

            return "<span class=\"error\">$deadline_date</span>";
        }

        return date("d-m-Y", $deadline);
    }
}
