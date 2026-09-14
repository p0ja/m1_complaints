<?php

class M1_Complaints_Model_Item_Excel extends Mage_Sales_Model_Excel_Abstract
{
    public $_statusModel;
    // Added: was assigned in exportItems() without being declared.
    public $_returnModel;

    public function exportItems($items)
    {
        $this->_statusModel = Mage::getModel('complaints/item_status');
        $this->_returnModel = Mage::getModel('complaints/item_return');
        $fileName = 'export_' . date("Ymd_His") . '.xls';
        $this->_workbook = new Spreadsheet_Excel_Writer();
        $this->_workbook->send($fileName);
        $worksheet =& $this->_workbook->addWorksheet('complaints');
        $j = 0;
        $this->writeHeadRow($worksheet, $j);

        $j++;
        $collection = Mage::getModel('complaints/item')->getCollection()
            ->addComplaintToSelect()
            ->addItemIdFilter($items);

        foreach ($collection as $item) {
            $this->writeItem($item, $worksheet, $j);
            $j++;
        }
        $this->_workbook->close();

        return $fileName;
    }

    protected function getHeadRowValues()
    {
        return array(
            iconv('UTF-8', 'CP1250//TRANSLIT', 'ID'),
            iconv('UTF-8', 'CP1250//TRANSLIT', 'Kurier'),
            iconv('UTF-8', 'CP1250//TRANSLIT', 'Nr zamówienia'),
            iconv('UTF-8', 'CP1250//TRANSLIT', 'Nazwa produktu'),
            iconv('UTF-8', 'CP1250//TRANSLIT', 'Data wysyłki'),
            iconv('UTF-8', 'CP1250//TRANSLIT', 'Nr LP'),
            iconv('UTF-8', 'CP1250//TRANSLIT', 'Data zgłoszenia'),
            iconv('UTF-8', 'CP1250//TRANSLIT', 'Nr reklamacji'),
            iconv('UTF-8', 'CP1250//TRANSLIT', 'Uszkodzony wrócił'),
            iconv('UTF-8', 'CP1250//TRANSLIT', 'Numer LP klienta'),
            iconv('UTF-8', 'CP1250//TRANSLIT', 'Data'),
            iconv('UTF-8', 'CP1250//TRANSLIT', 'Status'),
            iconv('UTF-8', 'CP1250//TRANSLIT', 'Koszt zakupu'),
            iconv('UTF-8', 'CP1250//TRANSLIT', 'Kwota zwrotu'),
            iconv('UTF-8', 'CP1250//TRANSLIT', 'Data zwrotu'),
            iconv('UTF-8', 'CP1250//TRANSLIT', 'Komentarz'),
            iconv('UTF-8', 'CP1250//TRANSLIT', 'Protokół'),
            iconv('UTF-8', 'CP1250//TRANSLIT', 'Reklamacja'),
        );
    }

    /**
     * One export row; the order matches getHeadRowValues().
     *
     * Changed:
     * - getExtItem() is not defined anywhere (a leftover of the salesext29 module), so every export ended in a
     *   fatal error; SKU, product name and comment now come from the columns the collection already joins;
     * - "Numer LP klienta" printed number again instead of client_shipment_number;
     * - "Kwota zwrotu" (return_amount) was missing, which shifted every following column;
     * - getReturDate() was a typo, so the return date was always empty.
     *
     * @param M1_Complaints_Model_Item $item
     * @return array
     */
    protected function getCommonItemValues($item)
    {
        return array(
            iconv('UTF-8', 'CP1250//TRANSLIT', $item->getSku()),
            iconv('UTF-8', 'CP1250//TRANSLIT', $item->getCourier()),
            $item->getIncrementId(),
            iconv('UTF-8', 'CP1250//TRANSLIT', $item->getName()),
            $item->getSentDate(),
            $item->getNumber(),
            $item->getComplaintDate(),
            $item->getComplaintNumber(),
            iconv('UTF-8', 'CP1250//TRANSLIT', $this->_returnModel->getLabel($item->getIsReturn())),
            $item->getClientShipmentNumber(),
            $item->getDeadline(),
            iconv('UTF-8', 'CP1250//TRANSLIT', $this->_statusModel->getLabel($item->getComplaintStatus())),
            $item->getPurchaseCost() ?: null,
            $item->getReturnAmount(),
            $item->getReturnDate() ? array(
                'value' => $this->getExcelDate($item->getReturnDate()),
                'format' => 'YYYY-MM-DD'
            ) : null,
            iconv('UTF-8', 'CP1250//TRANSLIT', $item->getComment()),
            $item->getFile1(),
            $item->getFile2(),
        );
    }
}
