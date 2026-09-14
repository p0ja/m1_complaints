<?php

class M1_Complaints_Block_Adminhtml_Complaint_Tab
    extends Mage_Adminhtml_Block_Template
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    public function _construct()
    {
        parent::_construct();

        $this->setTemplate('M1/complaints/tab.phtml');
    }

    public function getTabLabel()
    {
        return $this->__('Complaints');
    }

    /**
     * Changed: the title was placeholder text from a code template.
     */
    public function getTabTitle()
    {
        return $this->__('Complaints');
    }

    /**
     * Changed: the tab is only shown to admin roles allowed to manage complaints, the same ACL resource the
     * controller checks.
     */
    public function canShowTab()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/order/complaints');
    }

    public function isHidden()
    {
        return false;
    }

    /**
     * Added for tab.phtml: the order shown on the order view page.
     *
     * @return Mage_Sales_Model_Order|null
     */
    public function getOrder()
    {
        return Mage::registry('current_order');
    }

    /**
     * Added for tab.phtml: number of complaints registered for an order item.
     *
     * @param int $orderItemId
     * @return int
     */
    public function getComplaintCount($orderItemId)
    {
        return (int)Mage::getModel('complaints/item')->getComplaintsItemQty((int)$orderItemId);
    }

    /**
     * Added for tab.phtml: creates a complaint for an order item and opens it for editing (newAction).
     *
     * @param int $orderItemId
     * @return string
     */
    public function getNewComplaintUrl($orderItemId)
    {
        return $this->getUrl('adminhtml/complaint/new', array(
            'order_id' => $this->getOrder()->getId(),
            'order_item_id' => (int)$orderItemId,
        ));
    }
}
