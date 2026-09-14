<?php

class M1_Complaints_Model_Item extends Mage_Core_Model_Abstract
{
    const COMPLAINT_MAGAZYN_SUFFIX = "_complaint";
    const COMPLAINT_DEFAULT_DELAY = 30;

    /**
     * @var Mage_Sales_Model_Order_Item|null
     */
    protected $_orderItem;

    protected function _construct()
    {
        parent::_construct();
        $this->_init('complaints/item');
    }

    public function loadByOrderItemId($itemId)
    {
        $this->getResource()->loadByOrderItemId($this, $itemId);

        return $this;
    }

    /**
     * Changed: collection rows already carry increment_id from the joined order table, so the grid and the
     * export no longer load the order item and the order for every row. A single complaint reuses the order
     * of its cached order item instead of loading the order again.
     *
     * @return string
     */
    public function getIncrementId()
    {
        if ($this->hasData('increment_id')) {
            return $this->getData('increment_id');
        }

        $order = $this->getOrder();

        return $order ? (string)$order->getIncrementId() : '';
    }

    /**
     * Changed: the order item is cached per complaint; getIncrementId(), getOrder() and getOrderItem() each
     * loaded it again.
     *
     * @return Mage_Sales_Model_Order_Item
     */
    public function getSalesItem()
    {
        if ($this->_orderItem === null || (int)$this->_orderItem->getId() !== (int)$this->getOrderItemId()) {
            $this->_orderItem = Mage::getModel('sales/order_item')->load($this->getOrderItemId());
        }

        return $this->_orderItem;
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
        // Changed: shipment_date is the column (upgrade 0.1.4); shipping_date never existed, so the stored
        // date was ignored and the shipments were always searched.
        if ($this->getShipmentDate()) {

            return $this->getShipmentDate();
        }

        // Changed: collection rows carry the shipment date from the joined shipment grid
        // (Resource_Item_Collection::addComplaintToSelect), so rows no longer load the order and every shipment.
        if ($this->hasData('shipment_created_at')) {
            return $this->getData('shipment_created_at') ? $this->getData('shipment_created_at') : false;
        }

        // Changed: a single complaint looks up its own order. order_id is only set on collection rows, so the
        // previous load($this->getOrderId()) never found an order here. Shipments are no longer reloaded one by
        // one, and the ids are compared as integers.
        $order = $this->getOrder();
        if (!$order || !$order->getId()) {
            return false;
        }

        foreach ($order->getShipmentsCollection() as $shipment) {
            foreach ($shipment->getAllItems() as $item) {
                if ((int)$item->getOrderItemId() === (int)$this->getOrderItemId()) {

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
                // Changed: complaint_date is the column (upgrade 0.1.4); complaint_recived_at never existed.
                $dates_arr[$complaint->getId()] = $complaint->getComplaintDate();
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
