<?php

class M1_Complaints_Model_Item_Status extends Varien_Object
{
    const STATUS_OUT = 0;
    const STATUS_WAIT_FOR_PROTOCOL = 1;
    const STATUS_REGISTERED = 2;
    const STATUS_PREPARED = 3;
    const STATUS_REPORTED = 4;
    const STATUS_LOSS = 5;
    const STATUS_ACCEPTED = 6;
    const STATUS_ACCEPTED_AND_COMPENSATED = 7;
    const STATUS_APPEAL = 8;
    const STATUS_RABAT = 9;
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
            $this->_options[self::STATUS_WAIT_FOR_PROTOCOL] = $helper->__('waiting for protocol');
            $this->_options[self::STATUS_REGISTERED] = $helper->__('registered');
            $this->_options[self::STATUS_PREPARED] = $helper->__('prepared');
            $this->_options[self::STATUS_REPORTED] = $helper->__('reported');
            $this->_options[self::STATUS_LOSS] = $helper->__('loss');
            $this->_options[self::STATUS_ACCEPTED] = $helper->__('accepted');
            $this->_options[self::STATUS_ACCEPTED_AND_COMPENSATED] = $helper->__('accepted and compensated');
            $this->_options[self::STATUS_APPEAL] = $helper->__('appeal');
            $this->_options[self::STATUS_RABAT] = $helper->__('sell with discount');
        }
        // Changed: array_unshift() renumbered the status keys (every select option saved the wrong value)
        // and modified the cached options, adding another empty entry on each call. The + operator keeps the
        // keys and leaves $this->_options untouched.
        if ($isRequired) {
            return array(self::STATUS_OUT => ' ') + $this->_options;
        }

        return $this->_options;
    }

    public function getPriceByStatus($sid, $price)
    {
        return $sid > 4 ? 0 : $price;
    }
}