<?php

class M1_Complaints_Block_Adminhtml_Complaint_Grid_Renderer_Complaintnr
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action
{
    public function render(Varien_Object $row)
    {
        // Changed: complaint_number replaces the Polish nr_reklamacji column (upgrade 0.1.4), and the
        // user-entered value is escaped (stored XSS).
        return $this->escapeHtml($row->getComplaintNumber());
    }
}
