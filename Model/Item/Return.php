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
            // Changed: translated through the module helper, so M1_Complaints.csv applies; the global __()
            // function is deprecated and does not use the module's translation file.
            $helper = Mage::helper('complaints');
            $this->_options = array();
            $this->_options[self::STATUS_ZAMOWIONY_PO_ODBIOR] = $helper->__('ordered for collection');
            $this->_options[self::STATUS_TAK] = $helper->__('yes');
            $this->_options[self::STATUS_NIE] = $helper->__('no');
            $this->_options[self::STATUS_NIE_WROCI] = $helper->__('will not return');
        }
        // Changed: array_unshift() renumbered the option keys (every select option saved the wrong value)
        // and modified the cached options, adding another empty entry on each call. The + operator keeps the
        // keys and leaves $this->_options untouched.
        if ($isRequired) {
            return array(self::STATUS_BRAK => ' ') + $this->_options;
        }

        return $this->_options;
    }

    public function getPriceByStatus($sid, $price)
    {
        return $sid > 4 ? 0 : $price;
    }
}