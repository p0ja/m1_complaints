<?php

class M1_Complaints_Helper_Upload extends Mage_Core_Helper_Abstract
{
    /**
     * Stores an uploaded complaint document and writes its file name into $data[$field].
     *
     * Changed:
     * - saves into the non-public var/complaints/ directory; the uploader used to receive a media URL;
     * - setAllowRenameFiles(true): a second upload with the same name no longer overwrites an existing document;
     * - the name is cleaned with Varien_File_Uploader::getCorrectFileName() instead of urlencode();
     * - $fileId allows nested upload fields such as complaint[file1] sent by the tab form;
     * - returns whether the upload succeeded, so callers remove the previous file only after a new one is stored;
     * - array_map('trim') replaces create_function(), which is deprecated since PHP 7.2.
     *
     * @param array $data form data, receives the stored file name
     * @param string $field model field: file1 or file2
     * @param string|null $fileId upload field id, defaults to $field
     * @return bool
     */
    public function uploadFile(array &$data, $field = 'file1', $fileId = null)
    {
        $fileId = $fileId ? $fileId : $field;

        try {
            $filetypes = array_filter(array_map(
                'trim',
                explode(',', Mage::getStoreConfig('complaintsconfig/complaints/filetypes'))
            ));

            $uploader = new Varien_File_Uploader($fileId);
            $uploader->setAllowedExtensions($filetypes);
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(false);

            $prefix = !empty($data['order_item_id']) ? (int)$data['order_item_id'] . '_' : '';
            $fileName = $prefix . Varien_File_Uploader::getCorrectFileName($this->_getOriginalFileName($fileId));

            $result = $uploader->save(Mage::helper('complaints')->getComplaintDir(), $fileName);
            $data[$field] = $result['file'];

            return true;
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

        return false;
    }

    /**
     * Client-side name of an upload, for plain (file1) and nested (complaint[file1]) field ids.
     *
     * @param string $fileId
     * @return string
     */
    protected function _getOriginalFileName($fileId)
    {
        if (preg_match('/^(.+?)\[(.+?)\]$/', $fileId, $matches)) {
            return isset($_FILES[$matches[1]]['name'][$matches[2]]) ? $_FILES[$matches[1]]['name'][$matches[2]] : '';
        }

        return isset($_FILES[$fileId]['name']) ? $_FILES[$fileId]['name'] : '';
    }
}
