<?php

require_once 'abstract.php';

class Flagbit_Shell_ExportOrders extends Mage_Shell_Abstract
{

    /**
     * Run shell script.
     */
    function run()
    {

        $this->export();

    }

    /**
     * Start the xml export of products.
     *
     * @param
     */
    public function export()
    {
        Mage::app();
        Mage::app()->getStore()->setId(Mage_Core_Model_App::ADMIN_STORE_ID);

        $from = new DateTime('2023-01-1 00:00:00');
        $from = $from->format('Y-m-d H:i:s');

        $to = new DateTime('2023-06-15 23:59:9');
        $to = $to->format('Y-m-d H:i:s');

        $orders = Mage::getModel('sales/order')->getCollection()
        ->addAttributeToFilter('store_id', 9)         //danish store
        ->addAttributeToFilter('created_at', array('from'=>$from, 'to'=>$to)); //period limitation
        $fp = fopen('orders_export.csv', 'w');

        $header = array("Order number", "Customer ID", "First Name", "Last Name", "Email", "City", "Telephone", "Country", "Postcode", "Status", "VAT number", "Created at", "Total order amount");
        fputcsv($fp, $header);

        foreach ($orders as $order) {
            $billingAddress = $order->getBillingAddress();
            $countryCode = $billingAddress->getCountryId();
            $country = Mage::getModel('directory/country')->loadByCode($countryCode);
            $countryName = $country->getName();
            $customer_id = $order->getCustomerId();
            $customer = Mage::getModel('customer/customer')->load($customer_id);
            $taxvat = $customer->getData('taxvat');
            if($taxvat == ''){
                $taxvat = $billingAddress->getVatId();
            }
            $createdAt = $order->getCreatedAt();
            $orderNumber = $order->getIncrementId();
            $baseGrandTotal = $order->getBaseGrandTotal();


            $fields = array($orderNumber, $order->getId(), $billingAddress->getFirstname(), $billingAddress->getLastname(), $billingAddress->getEmail(), $billingAddress->getCity(), $billingAddress->getTelephone(), $countryName, $billingAddress->getPostcode(), $order->getStatus(), $taxvat, $createdAt, $baseGrandTotal);
            fputcsv($fp, $fields);
        }
    }



}

ini_set('display_errors', 1);
ini_set('memory_limit', '4096M');
$shell = new Flagbit_Shell_ExportOrders();
$shell->run();

