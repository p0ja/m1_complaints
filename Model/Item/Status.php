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
            $this->_options = array();
            $this->_options[self::STATUS_WAIT_FOR_PROTOCOL] = __('waiting for protocol');
            $this->_options[self::STATUS_REGISTERED] = __('registered');
            $this->_options[self::STATUS_PREPARED] = __('prepared');
            $this->_options[self::STATUS_REPORTED] = __('reported');
            $this->_options[self::STATUS_LOSS] = __('loss');
            $this->_options[self::STATUS_ACCEPTED] = __('accepted');
            $this->_options[self::STATUS_ACCEPTED_AND_COMPENSATED] = __('accepted and compensated');
            $this->_options[self::STATUS_APPEAL] = __('appeal');
            $this->_options[self::STATUS_RABAT] = __('sell with discount');
        }
        if ($isRequired) {
            array_unshift($this->_options, array(self::STATUS_OUT => ' '));
        }

        return $this->_options;
    }

    public function getPriceByStatus($sid, $price)
    {
        return $sid > 4 ? 0 : $price;
    }
}