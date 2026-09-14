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

            // Changed: Mage::getHelper() does not exist in Magento 1 (fatal error). The tab form sends nested
            // upload fields, so the uploader now gets the complaint[fileN] id; it used to look for a top-level
            // fileN upload and never found one. $_FILES['complaint'] is checked before use.
            $replacedFiles = array();
            foreach (array('file1', 'file2') as $field) {
                if (isset($_FILES['complaint']['tmp_name'][$field])
                    && file_exists($_FILES['complaint']['tmp_name'][$field])
                ) {
                    $previousFile = $model->getData($field);
                    $uploaded = Mage::helper('complaints/upload')->uploadFile($complaint, $field, 'complaint[' . $field . ']');
                    if ($uploaded && $previousFile) {
                        $replacedFiles[] = $previousFile;
                    }
                }
            }

            $model->addData($complaint);
            $returnAmount = $model->getReturnAmount();
            $returnAmountFormated = str_replace(array(',', ' '), array('.', ''), $returnAmount);
            $model->setReturnAmount((float)$returnAmountFormated);

            try {
                $model->save();

                // Added: a replaced document is removed only after the record points at the new one.
                foreach ($replacedFiles as $replacedFile) {
                    Mage::helper('complaints')->deleteComplaintFile($replacedFile);
                }
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

            // Changed: Mage::getHelper() does not exist in Magento 1 (fatal error); files are deleted from
            // the complaints directory on disk instead of a media URL.
            $helper = Mage::helper('complaints');
            if ($helper->isComplaintFileField($file) && $model->getData($file)) {
                $helper->deleteComplaintFile($model->getData($file));
                $model->setData($file, null)->save();
            }
        }
    }
}
