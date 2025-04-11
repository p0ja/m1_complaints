<?php

class M1_Complaints_Model_Item_Return extends Varien_Object
{
    const STATUS_BRAK = 0;
    const STATUS_ZAMOWIONY_PO_ODBIOR = 1;
    const STATUS_TAK = 2;
    const STATUS_NIE = 3;
    const STATUS_NIE_WROCI = 4;
    public $_options;

    public function getLabel($id)
    {
        $this->toOptionArray();
        return isset($this->_options[$id]) ? $this->_options[$id] : '';
    }

    public function toOptionArray($isRequired = false)
    {
        if (is_null($this->_options)) {
            $this->_options = array();
            $this->_options[self::STATUS_ZAMOWIONY_PO_ODBIOR] = __('ordered for collection');
            $this->_options[self::STATUS_TAK] = __('yes');
            $this->_options[self::STATUS_NIE] = __('no');
            $this->_options[self::STATUS_NIE_WROCI] = __('will not return');
        }
        if ($isRequired) {
            array_unshift($this->_options, array(self::STATUS_BRAK => " "));
        }
        return $this->_options;
    }

    public function getPriceByStatus($sid, $price)
    {
        return $sid > 4 ? 0 : $price;
    }
}