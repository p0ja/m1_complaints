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

    /**
     * Restrict the controller to admin roles granted the complaints ACL resource (etc/adminhtml.xml).
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/order/complaints');
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
            // Changed: complaint_date replaces the Polish reklamacja_data_zgloszenia column (upgrade 0.1.4).
            $complaint = Mage::getModel('complaints/item')
                ->setOrderItemId($orderItemId)
                ->setComplaintDate(date("Y-m-d"))
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
            // Changed: Mage::getHelper() does not exist in Magento 1 (fatal error); files are deleted from
            // the complaints directory on disk instead of a media URL.
            $file = $this->getRequest()->getParam('delete_file');
            $helper = Mage::helper('complaints');
            if ($helper->isComplaintFileField($file) && $model->getData($file)) {
                $helper->deleteComplaintFile($model->getData($file));
                $model->setData($file, null)->save();
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

            // Changed: Mage::getHelper() does not exist in Magento 1 (fatal error). A replaced document is
            // now deleted only when the new upload succeeded and the record was saved; before, a failed
            // upload still deleted the old file while the record kept pointing at it.
            $replacedFiles = array();
            foreach (array('file1', 'file2') as $field) {
                if (isset($_FILES[$field]['tmp_name']) && file_exists($_FILES[$field]['tmp_name'])) {
                    $previousFile = $model->getData($field);
                    if (Mage::helper('complaints/upload')->uploadFile($data, $field) && $previousFile) {
                        $replacedFiles[] = $previousFile;
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

                foreach ($replacedFiles as $replacedFile) {
                    Mage::helper('complaints')->deleteComplaintFile($replacedFile);
                }

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

    /**
     * Streams a complaint document to the browser.
     *
     * Added: documents moved from the public media/ directory to var/complaints/, so they are served here,
     * behind the admin login and the _isAllowed() ACL check.
     */
    public function downloadAction()
    {
        $helper = Mage::helper('complaints');
        $field = $this->getRequest()->getParam('file');
        $model = Mage::getModel('complaints/item')->load((int)$this->getRequest()->getParam('entity_id'));
        $path = $helper->isComplaintFileField($field) ? $helper->getComplaintFilePath($model->getData($field)) : null;

        if (!$model->getId() || !$path || !is_file($path)) {
            Mage::getSingleton('adminhtml/session')->addError($helper->__('File not found.'));
            $this->_redirect('*/*/');
            return;
        }

        $this->_prepareDownloadResponse(basename($path), array('type' => 'filename', 'value' => $path));
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
            // Changed: complaint_date replaces the Polish reklamacja_data_zgloszenia column (upgrade 0.1.4).
            Mage::getModel('complaints/item')
                ->setOrderItemId($orderItemId)
                ->setComplaintDate(date("Y-m-d"))
                ->save();
        } catch (Exception $ex) {
            $message = $this->__('An error occured') . ' : ' . $ex->getMessage();
            Mage::getSingleton('adminhtml/session')->addSuccess($message);
        }

        $this->_redirect('adminhtml/sales_order/view', array('order_id' => $orderId));
    }
}
