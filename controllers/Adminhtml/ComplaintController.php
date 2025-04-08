<?php

class M1_Complaints_Adminhtml_ComplaintController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->_initAction();
        $layout = $this->getLayout();
        $this->_addContent($layout->createBlock('complaints/adminhtml_complaint', 'item'));
        $this->renderLayout();
    }

    protected function _initAction()
    {
        $this->loadLayout()->_setActiveMenu('sales/order/complaints');
        return $this;

    }

    public function gridAction()
    {
        $this->loadLayout();

        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('complaints/adminhtml_complaint_grid')->toHtml()
        );
    }

    public function newAction()
    {
        $request = $this->getRequest();
        $orderId = $request->getParam('order_id');
        $orderItemId = $request->getParam('order_item_id');

        try {
            $complaint = Mage::getModel('complaints/item')
                ->setOrderItemId($orderItemId)
                ->setReklamacjaDataZgloszenia(date("Y-m-d"))
                ->save();

            if ($complaint->getId()) {
                $this->_redirect('complaints/adminhtml_complaint/edit', array('entity_id' => $complaint->getId()));
            } else {
                $this->_redirect('adminhtml/sales_order/view', array('order_id' => $orderId));
            }
        } catch (Exception $ex) {
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('An error occured : %s', $ex->getMessage()));
        }
    }

    public function editAction()
    {
        $id = $this->getRequest()->getParam('entity_id');
        if (!$id) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('complaints')->__('This item not exist')
            );
            $this->_redirect('*/*/');
            return;
        }
        $model = Mage::getModel('complaints/item')->load($id);

        if (isset($model) && $model->getOrderItemId()) {
            if ($file = $this->getRequest()->getParam('delete_file')) {
                switch ($file) {
                    case "file1":
                        if ($model->getFile1()) {
                            $file1 = Mage::getBaseDir('media') . DS . 'complaints' . DS . $model->getFile1();
                            if (file_exists($file1)) {
                                unlink($file1);
                            }
                            $model->setFile1()->save();
                        }
                        break;
                    case "file2":
                        if ($model->getFile2()) {
                            $file2 = Mage::getBaseDir('media') . DS . 'complaints' . DS . $model->getFile2();
                            if (file_exists($file2)) {
                                unlink($file2);
                            }
                            $model->setFile2()->save();
                        }
                        break;
                }
            }

            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }
            $model['order_id'] = $model->getOrder()->getId();
            Mage::register('item_data', $model);
            $this->_initAction()
                ->_addContent($this->getLayout()->createBlock('complaints/adminhtml_complaint_edit'));
            $this->renderLayout();
        }
    }

    public function saveAction()
    {
        $request = $this->getRequest();
        if ($data = $request->getPost()) {
            $model = Mage::getModel('complaints/item');
            if ($request->getParam('order_item_id')) {
                $model->loadByOrderItemId($request->getParam('order_item_id'));
            }

            $complaintsPath = Mage::getBaseDir('media') . DS . 'complaints' . DS;
            if (isset($_FILES['file1']['name']) && (file_exists($_FILES['file1']['tmp_name']))) {
                Mage::getHelper('complaints/upload')->uploadFile($data, 'file1');
                if ($model->getFile1()) {
                    $file1 = $complaintsPath . $model->getFile1();
                    if (file_exists($file1)) {
                        unlink($file1);
                    }
                }
            }
            if (isset($_FILES['file2']['name']) && (file_exists($_FILES['file2']['tmp_name']))) {
                Mage::getHelper('complaints/upload')->uploadFile($data, 'file2');
                if ($model->getFile2()) {
                    $file2 = $complaintsPath . $model->getFile2();
                    if (file_exists($file2)) {
                        unlink($file2);
                    }
                }
            }

            $model->addData($data);
            $returnAmountFormated = str_replace(array(',', ' '), array('.', ''), $model->getReturnAmount());
            $rabatFormated = str_replace(array(',', ' '), array('.', ''), $model->getRabat());
            $model->setReturnAmount((float)$returnAmountFormated);
            $model->setRabat((float)$rabatFormated);
            $model->setComplaintStatus((int)$model->getComplaintStatus());

            $isRabatStatus = $model->getComplaintStatus() === M1_Complaints_Model_Item_Status::STATUS_RABAT;
            if ($isRabatStatus && $model->getRabat()) {
                $model = $this->_backToSellWithDiscount($model);
            }

            try {
                $model->save();

                $message = Mage::helper('complaints')->__('Record saved successfully.');
                Mage::getSingleton('adminhtml/session')->addSuccess($message);
                Mage::getSingleton('adminhtml/session')->setFormData(false);
                if ($request->getParam('back')) {
                    $this->_redirect('*/*/edit', array('entity_id' => $model->getEntityId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;

            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('entity_id' => $request->getParam('entity_id')));
                return;
            }

        }
        $this->_redirect('*/*/');
    }

    protected function _backToSellWithDiscount($complaint)
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
                array('eq' => $orderPreparationWarehouse->getStockCode() . $suffix))
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
            } else {
                if ($transfer->getStId()) {
                    $transfer = Mage::getModel('AdvancedStock/StockTransfer')->load($transfer->getStId());
                    $transfer->delete();

                    return $complaint;
                }
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
        $transfer->setStName('de-reklamacja_itemID#' . $orderItem->getId());
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
                $transfer->setStComments("Utworzone przez " . $user->getName());
            }
            $transfer->save();
        }

        return $transfer;
    }

    private function getUser()
    {
        return Mage::getSingleton('admin/session')->getUser();
    }

    public function deleteAction()
    {
        if ($id = $this->getRequest()->getParam('entity_id')) {
            try {
                $model = Mage::getModel('complaints/item');
                $model->load($id);
                $model->delete();

                $message = Mage::helper('complaints')->__('Record saved successfully.');
                Mage::getSingleton('adminhtml/session')->addSuccess($message);

                $this->_redirect('*/*/');
                return;

            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('entity_id' => $id));
                return;
            }
        }
        $message = Mage::helper('complaints')->__('Record is missing.');
        Mage::getSingleton('adminhtml/session')->addError($message);
        $this->_redirect('*/*/');
    }

    public function csvexportAction()
    {
        $request = $this->getRequest();
        $items = $request->getPost('item_ids', array());
        $file = Mage::getModel('complaints/item_excel')->exportItems($items);
        $this->_prepareDownloadResponse($file, file_get_contents(Mage::getBaseDir('export') . '/' . $file));
    }

    public function createAction()
    {
        $request = $this->getRequest();
        $orderId = $request->getParam('order_id');
        $orderItemId = $request->getParam('order_item_id');

        try {
            $orderItem = mage::getModel('sales/order_item')->load($orderItemId);
            if ($orderItem->getReservedQty() > 0) {
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
            Mage::getModel('complaints/item')
                ->setOrderItemId($orderItemId)
                ->setReklamacjaDataZgloszenia(date("Y-m-d"))
                ->save();
        } catch (Exception $ex) {
            $message = $this->__('An error occured : %s', $ex->getMessage());
            Mage::getSingleton('adminhtml/session')->addSuccess($message);
        }

        $this->_redirect('adminhtml/sales_order/view', array('order_id' => $orderId));
    }

    protected function _initItem($idFieldName = 'entity_id')
    {
        $itemId = (int)$this->getRequest()
            ->getParam($idFieldName);
        $item = Mage::getModel('complaints/item');

        if ($itemId) {
            $item->load($itemId);
        }

        Mage::register('item_data', $item);

        return $this;
    }
}
