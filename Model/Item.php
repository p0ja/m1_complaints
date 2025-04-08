<?php

class M1_Complaints_Model_Item extends Mage_Core_Model_Abstract
{
    const COMPLAINT_MAGAZYN_SUFFIX = "_complaint";
    const COMPLAINT_DEFAULT_DELAY = 30;

    public function _construct()
    {
        parent::_construct();
        $this->_init('complaints/item');
    }

    public function loadByOrderItemId($itemId)
    {
        $this->getResource()->loadByOrderItemId($this, $itemId);

        return $this;
    }

    public function getIncrementId()
    {
        $order_item = $this->getSalesItem();
        if (!$order_item) {

            return '';
        }

        return Mage::getModel('sales/order')->load($order_item->getOrderId())->getIncrementId();
    }

    public function getSalesItem()
    {
        return Mage::getModel('sales/order_item')->load($this->getOrderItemId());
    }

    public function getOrder()
    {
        $sales_item = $this->getSalesItem();
        if (isset($sales_item)) {

            return $sales_item->getOrder();
        }

        return null;
    }

    public function getOrderItem()
    {
        $sales_item = $this->getSalesItem();
        if (isset($sales_item)) {

            return $sales_item;
        }

        return null;
    }

    public function getDeadline()
    {
        if (!$this->getSentDate()) {
            return '';
        }

        $delay = Mage::getStoreConfig('complaintsconfig/complaints/delay');
        if (!$delay) {
            $delay = self::COMPLAINT_DEFAULT_DELAY;
        }

        $deadline = strtotime($this->getSentDate()) + (60 * 60 * 24 * $delay);

        return date("Y-m-d", $deadline);
    }

    public function getSentDate()
    {
        if ($this->getShippingDate()) {

            return $this->getShippingDate();
        }

        $order = Mage::getModel('sales/order')->load($this->getOrderId());
        $shipments = $order->getShipmentsCollection();
        foreach ($shipments as $shipment) {
            $shipment = Mage::getModel('sales/order_shipment')->load($shipment->getId());

            foreach ($shipment->getAllItems() as $item) {
                if ($item->getOrderItemId() === $this->getOrderItemId()) {

                    return $shipment->getCreatedAt();
                }
            }
        }
        return false;
    }

    public function getComplaintsItemQty($itemId, $trans = false)
    {
        $complaints = Mage::getModel('complaints/item')->getCollection()
            ->addComplaintToSelect($trans)
            ->addFieldToFilter('main_table.order_item_id', array('eq' => $itemId));

        if ($trans) {
            $complaints->getSelect()->joinLeft(
                array('stp' => $complaints->getTable('AdvancedStock/StockTransfer_Product')),
                'main_table.st_id = stp.stp_transfer_id and stp.stp_product_id = oi.product_id',
                array('stp_qty_transfered')
            );
            $complaints->getSelect()->where('main_table.stock_id = si.stock_id');
        }

        return count($complaints);
    }

    public function getComplaintsDates($itemId, $trans = false)
    {
        $complaints = Mage::getModel('complaints/item')->getCollection()
            ->addComplaintToSelect($trans)
            ->addFieldToFilter('main_table.order_item_id', array('eq' => $itemId));
        $dates_arr = array();

        if ($trans) {
            $complaints->getSelect()->joinLeft(
                array('stp' => $complaints->getTable('AdvancedStock/StockTransfer_Product')),
                'main_table.st_id = stp.stp_transfer_id and stp.stp_product_id = oi.product_id',
                array('stp_qty_transfered')
            );
            $complaints->getSelect()->where('main_table.stock_id = si.stock_id');

            foreach ($complaints as $complaint) {
                $dates_arr[$complaint->getId()] = $complaint->getTransfer()->getStCreatedAt();
            }
        } else {
            foreach ($complaints as $complaint) {
                $dates_arr[$complaint->getId()] = $complaint->getComplaintRecivedAt();
            }
        }

        return $dates_arr;
    }

    public function getTransfer()
    {
        return Mage::getModel('AdvancedStock/StockTransfer')->load($this->getStId());
    }

    public function getStock()
    {
        return Mage::getModel('AdvancedStock/Warehouse')->load($this->getStockId());
    }
}
