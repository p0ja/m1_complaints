<?php

class M1_Complaints_Helper_Upload extends Mage_Core_Helper_Abstract
{
    public function uploadFile(array &$data, $file = 'file1')
    {
        try {
            $filetypes = explode(',', Mage::getStoreConfig('complaintsconfig/complaints/filetypes'));
            array_walk($filetypes, create_function('&$val', '$val = trim($val);'));

            $uploader = new Varien_File_Uploader($file);
            $uploader->setAllowedExtensions($filetypes);
            $uploader->setAllowRenameFiles(false);
            $uploader->setFilesDispersion(false);
            $_FILES[$file]['name'] = $data['item_id'] . urlencode($_FILES[$file]['name']);
            $complaintsPath = Mage::getHelper('complaints/data')->getComplaintPath();

            $uploader->save($complaintsPath, $_FILES[$file]['name']);

            $data[$file] = $_FILES[$file]['name'];
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
    }
}
