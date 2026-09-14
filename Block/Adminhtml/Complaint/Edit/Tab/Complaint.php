<?php

class M1_Complaints_Block_Adminhtml_Complaint_Edit_Tab_Complaint extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $model = Mage::registry('complaint_data');
        if (!$model->getOrderItemId()) {
            $item = Mage::registry('item_data');
            $itemId = $item->getItem()->getItemId();

            // Changed: the record is created through the model. Helper_Sql (removed) built a raw INSERT with the
            // hard-coded table name complaints_items, which ignored the table prefix and the resource model.
            Mage::getModel('complaints/item')->setOrderItemId($itemId)->save();

            $model = Mage::getModel('complaints/item')->loadByOrderItemId($itemId);
        }
        foreach ($model->getData() as $key => $val) {
            $model["complaint[$key]"] = $model[$key];
        }

        // Changed: Mage::helper() instead of the non-existent Mage::getHelper(); the block is passed so the
        // helper can use getData('action'), getSkinUrl() and getUrl(), which a helper does not have.
        $form = Mage::helper('complaints')->getComplaintForm($model, $this);
        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
