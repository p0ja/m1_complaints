<?php

class M1_Complaints_Helper_Orders extends Mage_Core_Helper_Abstract
{
    public function setOrderItem($orderItem, $order, $discount)
    {
        $price = $orderItem->getPrice();
        $basePrice = $order->getBasePrice();
        $rowTotal = $orderItem->getRowTotal();
        $baseRowTotal = $orderItem->getBaseRowTotal();
        $rowInvoiced = $orderItem->getRowInvoiced();
        $baseRowInvoiced = $orderItem->getBaseRowInvoiced();
        $taxInvoiced = $orderItem->getTaxInvoiced();
        $baseTaxInvoiced = $orderItem->getBaseTaxInvoiced();
        $rowTotalInclTax = $orderItem->getRowTotalInclTax();
        $baseRowTotalInclTax = $orderItem->getBaseRowTotalInclTax();
        $taxAmountFromOrder = $orderItem->getTaxAmount();
        $baseTaxAmount = $orderItem->getBaseTaxAmount();
        $priceInclTax = $orderItem->getPriceInclTax();
        $basePriceInclTax = $orderItem->getBasePriceInclTax();

        $orderItem->setPrice($price - ($price * $discount));
        $orderItem->setBasePrice($basePrice - ($basePrice * $discount));
        $orderItem->setRowTotal($rowTotal - ($orderItem->getRowTotal() * $discount));
        $orderItem->setBaseRowTotal($baseRowTotal - ($baseRowTotal * $discount));
        $orderItem->setRowInvoiced($rowInvoiced - ($rowInvoiced * $discount));
        $orderItem->setBaseRowInvoiced($baseRowInvoiced - ($baseRowInvoiced * $discount));
        $orderItem->setTaxInvoiced($taxInvoiced - ($taxInvoiced * $discount));
        $orderItem->setBaseTaxInvoiced($baseTaxInvoiced - ($baseTaxInvoiced * $discount));
        $orderItem->setRowTotalInclTax($rowTotalInclTax - ($rowTotalInclTax * $discount));
        $orderItem->setBaseRowTotalInclTax($baseRowTotalInclTax - ($baseRowTotalInclTax * $discount));
        $orderItem->setTaxAmount($taxAmountFromOrder - ($taxAmountFromOrder * $discount));
        $orderItem->setAmountRefunded(-$rowTotal);
        $orderItem->setBaseAmountRefunded(-$rowTotal);
        $orderItem->setBaseTaxAmount($baseTaxAmount - ($baseTaxAmount * $discount));
        $orderItem->setPriceInclTax($priceInclTax - ($priceInclTax * $discount));
        $orderItem->setBasePriceInclTax($basePriceInclTax - ($basePriceInclTax * $discount));
        $orderItem->save();

        $order->setTaxRefunded($order->getTaxRefunded() - $taxAmountFromOrder);
        $order->setBaseTaxRefunded($order->getBaseTaxRefunded() - $taxAmountFromOrder);
        $order->save();
    }
}
