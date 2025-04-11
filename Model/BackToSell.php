<?php

class M1_Complaints_Model_BackToSell extends Mage_Core_Model_Abstract
{
    /**
     * @param M1_Complaints_Model_Item $complaint
     * @return M1_Complaints_Model_Item
     */
    public function backToSellWithDiscount($complaint)
    {
        $complaint = Mage::getModel('complaints/item')->load($complaint->getId());
        $discount = $complaint->getRabat() / 100;
        $orderItemId = $complaint->getOrderItemId();
        $orderItem = Mage::getModel('sales/order_item')->load($orderItemId);
        $order = $complaint->getOrder();
        $orderPreparationWarehouse = $orderItem->getPreparationWarehouse();
        $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProductWarehouse(
            $orderItem->getProductId(),
            $orderPreparationWarehouse->getId()
        );

        if ($orderItem->getReservedQty() > 0) {
            $this->releaseReservation($orderItem, $stockItem);
        }

        $suffix = M1_Complaints_Model_Item::COMPLAINT_MAGAZYN_SUFFIX;
        $source_stock = Mage::getModel('AdvancedStock/Warehouse')->getCollection()
            ->addFieldToFilter(
                'stock_code',
                array('eq' => $orderPreparationWarehouse->getStockCode() . $suffix)
            )
            ->getFirstItem();

        if ($source_stock->getQty() > 0) {
            $transfer = $this->createReturnComplaintToStockTransfer($source_stock, $orderItem);
            $productId = $orderItem->getProductId();
            $transfer->addProduct($productId, 1);

            if ($transfer->getStId() && count($transfer->getProducts())) {
                $transfer->save();
                $stockItem->setStockOrderedQty($stockItem->getStockOrderedQty() + 1)->save();
                $transfer->apply();
                $transfer->updateStatus();
                if ($transfer->getStStatus() === MDN_AdvancedStock_Model_StockTransfer::STATUS_COMPLETE) {
                    $date = Mage::getModel('core/date')->date("Y-m-d H:i:s");
                    $transfer->setStTransferedAt($date)->save();
                }
            } else if ($transfer->getStId()) {
                $transfer = Mage::getModel('AdvancedStock/StockTransfer')->load($transfer->getStId());
                $transfer->delete();

                return $complaint;
            }
        }

        $product = Mage::getModel('catalog/product')->load($orderItem->getProductId());
        if ($product->getFinalPrice() - $orderItem->getPrice() < 0.02) {
            Mage::getHelper('complaints/orders')->setOrderItem($orderItem, $order, $discount);
        }

        if ($stockItem->getAvailableQty() > 0) {
            Mage::helper('AdvancedStock/Product_Base')->updateStocks($product);
        }

        return $complaint;
    }

    public function updateStock($orderItem)
    {
        //release reservation
        $orderItem->getErpOrderItem()->setReservedQty($orderItem->getReservedQty() - 1)->save();
        $orderPreparationWarehouse = $orderItem->getPreparationWarehouse();
        $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProductWarehouse(
            $orderItem->getProductId(),
            $orderPreparationWarehouse->getId()
        );
        if (isset($stockItem) && $orderItem->getProductId()) {
            $value = Mage::helper('AdvancedStock/Product_Reservation')->getReservedQtyForStock(
                $stockItem,
                $orderItem->getProductId()
            );
            $stockItem->setStockReservedQty($value)->save();
        }
        //update stock for product
        if ($stockItem->getAvailableQty() > 0) {
            $product = mage::getModel('catalog/product')->load($orderItem->getProductId());
            Mage::helper('AdvancedStock/Product_Base')->updateStocks($product);
        }
    }

    private function releaseReservation($orderItem, $stockItem)
    {
        $orderItem->getErpOrderItem()->setReservedQty($orderItem->getReservedQty() - 1)->save();
        if (isset($stockItem) && $orderItem->getProductId()) {
            $value = Mage::helper('AdvancedStock/Product_Reservation')->getReservedQtyForStock(
                $stockItem,
                $orderItem->getProductId()
            );
            $stockItem->setStockReservedQty($value)->save();
        }
    }

    private function createReturnComplaintToStockTransfer($sourceStock, $orderItem)
    {
        $transfer = Mage::getModel('AdvancedStock/StockTransfer');
        $transfer->setStName('de-complaints_itemID#' . $orderItem->getId());
        $transfer->setStSourceWarehouse($sourceStock->getStockId());
        $orderPreparationWarehouse = $orderItem->getPreparationWarehouse();
        $target_stock = Mage::getModel('AdvancedStock/Warehouse')->getCollection()
            ->addFieldToFilter('stock_code', array('eq' => $orderPreparationWarehouse->getStockCode()))
            ->getFirstItem();
        if (isset($target_stock) && $target_stock->getStockId()) {
            $transfer->setStTargetWarehouse($target_stock->getStockId());
            $transfer->setStStatus(MDN_AdvancedStock_Model_StockTransfer::STATUS_NEW);
            $user = Mage::getModel('admin/user')->load($this->getUser()->getId());
            if (isset($user) && $user->getId()) {
                $transfer->setStComments("Cretated by " . $user->getName());
            }
            $transfer->save();
        }

        return $transfer;
    }

    private function getUser()
    {
        return Mage::getSingleton('admin/session')->getUser();
    }
}
