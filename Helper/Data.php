<?php

class M1_Complaints_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Filesystem directory for complaint documents.
     *
     * Changed: replaces getComplaintPath(), which returned a media URL that was passed to file_exists(),
     * unlink() and the uploader, so none of them worked. Documents also used to be stored under media/,
     * which the web server serves to anyone. var/ is closed by Magento's var/.htaccess, so the files
     * are now only reachable through the ACL-protected downloadAction(). Files uploaded before this
     * change stay in media/complaints/ and have to be moved to var/complaints/.
     *
     * @return string
     */
    public function getComplaintDir()
    {
        return Mage::getBaseDir('var') . DS . 'complaints' . DS;
    }

    /**
     * Absolute path of a stored complaint document, or null when there is no file name.
     *
     * Added: basename() keeps a tampered file name from pointing outside the complaints directory.
     *
     * @param string $fileName
     * @return string|null
     */
    public function getComplaintFilePath($fileName)
    {
        $fileName = basename((string)$fileName);

        return $fileName === '' ? null : $this->getComplaintDir() . $fileName;
    }

    /**
     * Admin URL that streams a complaint document through the controller.
     *
     * Added: replaces the public media URL. The full route is used instead of a relative one so the
     * link also works when the form is rendered as a tab inside another module's controller.
     *
     * @param int $complaintId
     * @param string $field file1 or file2
     * @return string
     */
    public function getComplaintFileUrl($complaintId, $field)
    {
        return Mage::helper('adminhtml')->getUrl('adminhtml/complaint/download', array(
            'entity_id' => (int)$complaintId,
            'file' => $field,
        ));
    }

    /**
     * Added: whitelist for the file field names accepted from request parameters.
     *
     * @param string $field
     * @return bool
     */
    public function isComplaintFileField($field)
    {
        return in_array($field, array('file1', 'file2'), true);
    }

    /**
     * Deletes a stored complaint document; a missing file is ignored.
     *
     * Added: the same delete code was copied into the controller and the observer, both using the media URL.
     *
     * @param string $fileName
     */
    public function deleteComplaintFile($fileName)
    {
        $path = $this->getComplaintFilePath($fileName);
        if ($path && file_exists($path)) {
            unlink($path);
        }
    }

    /**
     * Download and delete links for a stored document.
     *
     * Added: the file name comes from the upload and was printed unescaped (stored XSS), and the markup
     * was duplicated in the edit form and the tab form.
     *
     * @param M1_Complaints_Model_Item $model
     * @param string $field file1 or file2
     * @param string $deleteUrl
     * @return string
     */
    public function getComplaintFileHtml($model, $field, $deleteUrl)
    {
        $fileName = $model->getData($field);
        if (!$fileName) {
            return '';
        }

        return '<br /><a href="' . $this->escapeHtml($this->getComplaintFileUrl($model->getId(), $field)) . '">'
            . $this->escapeHtml($fileName) . '</a><br /><p style="margin-top: 5px"><a href="'
            . $this->escapeHtml($deleteUrl) . '"><span class="error">' . $this->__('Delete') . '</span></a></p>';
    }

    /**
     * Changed: takes the block that renders the form. A helper has no getData(), getSkinUrl() or getUrl(),
     * so the previous $this->getData('action') and $this->getSkinUrl() calls were fatal errors.
     * The legend values and file names are now escaped (stored XSS).
     *
     * @param M1_Complaints_Model_Item $model
     * @param Mage_Core_Block_Abstract $block
     * @return Varien_Data_Form
     */
    public function getComplaintForm($model, Mage_Core_Block_Abstract $block)
    {
        $form = new Varien_Data_Form(array(
            'id' => 'complaint_form',
            'action' => $block->getData('action'),
            'method' => 'post',
            'enctype' => 'multipart/form-data'
        ));

        $fieldset = $form->addFieldset('add_item_form', array(
            'legend' => $this->escapeHtml($this->__('Order number:') . ' ' . $model->getIncrementId()) .
                '<br/>' . $this->escapeHtml('Product name: ' . $model->getOrderItem()->getName()) .
                '<br/>' . $this->escapeHtml('Product number: ' . $model->getOrderItem()->getSku()) .
                '<br/>' . $this->escapeHtml('Quantity: ' . (int)$model->getOrderItem()->getQtyOrdered()) .
                '<br/>' . $this->escapeHtml('Shippment: ' . $model->getOrder()->getShippingDescription())
        ));

        if ($model->getId()) {
            $fieldset->addField('entity_id', 'hidden', array(
                'name' => 'complaint[entity_id]',
            ));

            $fieldset->addField('order_item_id', 'hidden', array(
                'name' => 'complaint[order_item_id]',
            ));

            $fieldset->addField('qty', 'hidden', array(
                'name' => 'complaint[qty]',
            ));
        }

        $fieldset->addField('courier', 'text', array(
            'label' => Mage::helper('complaints')->__('Courier'),
            'name' => 'complaint[courier]',
        ));

        $fieldset->addField('number', 'text', array(
            'label' => Mage::helper('complaints')->__('Number'),
            'name' => 'complaint[number]',
        ));

        $fieldset->addField('complaint_date', 'date', array(
            'name' => 'complaint[complaint_date]',
            'class' => 'validate-date2',
            'required' => false,
            'label' => Mage::helper('complaints')->__('Complaint create date'),
            'image' => $block->getSkinUrl('images/grid-cal.gif'),
            'format' => 'yyyy-MM-dd',
        ));

        $fieldset->addField('shipment_date', 'date', array(
            'name' => 'complaint[shipment_date]',
            'class' => 'validate-date2',
            'required' => false,
            'label' => Mage::helper('complaints')->__('Shipment date'),
            'image' => $block->getSkinUrl('images/grid-cal.gif'),
            'format' => 'yyyy-MM-dd',
        ));

        $fieldset->addField('complaint_number', 'text', array(
            'label' => Mage::helper('complaints')->__('Complaint number'),
            'name' => 'complaint[complaint_number]',
        ));

        $fieldset->addField('is_return', 'select', array(
            'label' => Mage::helper('complaints')->__('Has item returned?'),
            'name' => 'complaint[is_return]',
            'options' => Mage::getSingleton('complaints/item_return')->toOptionArray(true),
        ));

        $fieldset->addField('client_shipment_number', 'text', array(
            'label' => Mage::helper('complaints')->__('Client shipment number'),
            'name' => 'complaint[client_shipment_number]',
        ));

        $fieldset->addField('complaint_status', 'select', array(
            'label' => Mage::helper('complaints')->__('Complaint status'),
            'name' => 'complaint[complaint_status]',
            'options' => Mage::getSingleton('complaints/item_status')->toOptionArray(true),
        ));

        $fieldset->addField('return_amount', 'text', array(
            'label' => Mage::helper('complaints')->__('Return amount'),
            'name' => 'complaint[return_amount]',
            'class' => 'validate-number',
        ));

        $fieldset->addField('return_date', 'date', array(
            'name' => 'complaint[return_date]',
            'class' => 'validate-date2',
            'required' => false,
            'label' => Mage::helper('complaints')->__('Return date'),
            'image' => $block->getSkinUrl('images/grid-cal.gif'),
            'format' => 'yyyy-MM-dd',
        ));

        $fieldset->addField('file1', 'file', array(
            'label' => Mage::helper('complaints')->__('Complaint details'),
            'required' => false,
            'name' => 'complaint[file1]',
            'after_element_html' => $this->getComplaintFileHtml(
                $model,
                'file1',
                $block->getUrl('*/*/*/', array('_current' => true, 'delete_file' => 'file1'))
            ),
        ));

        $fieldset->addField('file2', 'file', array(
            'label' => Mage::helper('complaints')->__('Complaint'),
            'required' => false,
            'name' => 'complaint[file2]',
            'after_element_html' => $this->getComplaintFileHtml(
                $model,
                'file2',
                $block->getUrl('*/*/*/', array('_current' => true, 'delete_file' => 'file2'))
            ),
        ));

        $info = "<div style=\"position:relative;width:500px;\" id=\"messages\">
                <ul class=\"messages\">
                <li class=\"notice-msg\"><ul><li>" .

            $this->__('Make sure that data encoding in the file is saved in one of supported encodings (UTF-8 or ANSI).')

            . "</li></ul></li></ul></div>";

        $fieldset->addField('complaints-upload-info', 'label', array(
            'after_element_html' => $info,
        ));

        return $form;
    }
}
