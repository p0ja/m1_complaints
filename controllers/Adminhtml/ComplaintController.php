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
                $complaintPath = Mage::getHelper('complaints/data')->getComplaintPath();

                switch ($file) {
                    case "file1":
                        if ($model->getFile1()) {
                            $file1 = $complaintPath . $model->getFile1();
                            if (file_exists($file1)) {
                                unlink($file1);
                            }
                            $model->setFile1()->save();
                        }
                        break;
                    case "file2":
                        if ($model->getFile2()) {
                            $file2 = $complaintPath . $model->getFile2();
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

            $complaintsPath = Mage::getHelper('complaints/data')->getComplaintPath();
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
                $model = Mage::getModel('complaints/backToSell')->backToSellWithDiscount($model);
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
                Mage::getModel('complaints/backToSell')->updateStock($orderItem);
            }
            Mage::getModel('complaints/item')
                ->setOrderItemId($orderItemId)
                ->setReklamacjaDataZgloszenia(date("Y-m-d"))
                ->save();
        } catch (Exception $ex) {
            $message = $this->__('An error occured') . ' : ' . $ex->getMessage();
            Mage::getSingleton('adminhtml/session')->addSuccess($message);
        }

        $this->_redirect('adminhtml/sales_order/view', array('order_id' => $orderId));
    }
}
