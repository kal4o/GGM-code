<?php

require_once 'abstract.php';


class GGMgastro_Shell_ImportPremiumPriceAttributeTest extends Mage_Shell_Abstract
{

    /**
     * Run shellscript.
     */
    public function run()
    {
        $this->updateBulgariaPriceTest();
    }

    /**
     * @param null $productId
     * @param null $sapUpdate
     * @throws Exception
     */
    private function updateBulgariaPriceTest()
    {
        $file = fopen('src/test_sku_list.csv', 'r');
        while ($data = fgetcsv($file, 10000, ",")) {
            $productSkuArray[] = $data[0];
        }
        //var_dump($productSkuArray);
        foreach ($productSkuArray as $productSku) {
            //echo "success";
            //var_dump($productSku);
            $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $productSku);

            //$product->setPremiumProductPriceUpdate('10');
            $product->setData('premium_product_price_update','10');
            var_dump($product);
        }
    }


}

ini_set('display_errors', 1);
ini_set('memory_limit', '4096M');
$shell = new GGMgastro_Shell_ImportPremiumPriceAttributeTest();
$shell->run();