<?php

class M1_Complaints_Model_Observer
{
    static protected $_singletonFlag = false;

    public function saveComplaintTabData($observer)
    {
        if (!self::$_singletonFlag) {
            self::$_singletonFlag = true;

            $complaint = $observer->getEvent()->getItem();
            $model = Mage::getModel('complaints/item');
            if (!$complaint['order_item_id']) {

                return;
            }

            $model->loadByOrderItemId($complaint['order_item_id']);
            if (!$model->getOrderItemId()) {
                $model = Mage::getSingleton('complaints/item');
                $model->setOrderItemId($complaint['order_item_id'])->save();
            }

            $files = $_FILES['complaint']['name'];

            if (isset($files['file1']) && (file_exists($_FILES['complaint']['tmp_name']['file1']))) {
                Mage::getHelper('complaints/upload')->uploadFile($complaint, 'file1');
            }
            if (isset($files['file2']) && (file_exists($_FILES['complaint']['tmp_name']['file2']))) {
                Mage::getHelper('complaints/upload')->uploadFile($complaint, 'file2');
            }

            $model->addData($complaint);
            $returnAmount = $model->getReturnAmount();
            $returnAmountFormated = str_replace(array(',', ' '), array('.', ''), $returnAmount);
            $model->setReturnAmount((float)$returnAmountFormated);

            try {
                $model->save();
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
    }

    public function delComplaintFile($observer)
    {
        $event = $observer->getEvent();
        $file = $event->getFile();
        $item_id = $event->getId();

        if ($file && $item_id) {
            $model = Mage::getModel('complaints/item');
            $model->loadByOrderItemId($item_id);
            if (!$model->getOrderItemId()) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('complaints')->__('This item not exist')
                );

                return;
            }

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
    }
}
