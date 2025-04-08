<?php

class M1_Complaints_Block_Adminhtml_Complaint_Edit_Tab_Complaint extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $model = Mage::registry('complaint_data');
        if (!$model->getOrderItemId()){
            $item = Mage::registry('item_data');

            $data = array('order_item_id' => $item->getItem()->getItemId());
            Mage::getHelper('complaints/sql')->writeData('complaints_items', $data);

            $model = Mage::getModel('complaints/item')->loadByOrderItemId($item->getItem()->getItemId());
        }
        foreach($model->getData() as $key => $val){
            $model["complaint[$key]"] = $model[$key];
        }

        $form = Mage::getHelper('complaints/data')->getComplaintForm($model);
        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
