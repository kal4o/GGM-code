<?php

require_once 'abstract.php';


class GGMgastro_Shell_UpdatePremiumProductsPriceTest extends Mage_Shell_Abstract
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
    
    private function updateBulgariaPriceTest($productId="81858")
    {
        //$file = fopen('src/test_sku_list.csv', 'r');
        //while ($data = fgetcsv($file, 10000, ",")) {
            //$productSkuArray[] = $data[0];
        //}
        //var_dump($productSkuArray);
        //foreach ($productSkuArray as $productSku) {
            //var_dump($productSku);
            //$product = Mage::getModel('catalog/product')->loadByAttribute('sku', $productSku);
            //$products = Mage::getModel('flagbit_countrybasedpricematrix/priceMatrix')->getCollection();
            $product = Mage::getModel('catalog/product')->load($productId);
            $priceMatrixCollection = Mage::getResourceModel('flagbit_countrybasedpricematrix/priceMatrix_collection');
            $priceUpdateValue = $product->getData('premium_product_price_update');
            $priceUpdatePercent = $priceUpdateValue/100 + 1;
            $null = $priceUpdateValue == 'NULL';
            if(!$null) {
                
                $priceMatrixCollection->addFilterByProduct($product)
                ->setOrder('website_id', 'ASC')
                ->addOrder('country_id', 'ASC');

                foreach ($priceMatrixCollection as $priceMatrixItem) {
                    //var_dump($product->getData('sku'));
                    //var_dump($priceMatrixItem);
                    $itemData = $priceMatrixItem->getData();
                    $newPrice = $itemData['price'] * $priceUpdatePercent;
                    //var_dump($itemData['price']);
                    switch ($itemData['country_id']) {

                        case 'BG':

                        case 'HU':

                        case 'RO':

                        case 'GR':

                        case 'DK':

                        case 'NO':

                            $priceMatrix[] = [

                                'price' => number_format($newPrice, 2, '.', ''),

                                'website_id' => 0,

                                'country_id' => $itemData['country_id'],

                                'special_price' => '',

                            ];
                            
                            break;
                    }
                }
            
            //$this->savePriceMatrix($product, $priceMatrix);
            var_dump($priceMatrix);
            $priceMatrix = [];
            var_dump($product->getData('sku'));
            }
            
        //}
    }


    private function savePriceMatrix(Mage_Catalog_Model_Product $product, array $priceMatrixArray)
    {
        /** @var Flagbit_CountryBasedPriceMatrix_Model_PriceMatrixManager $priceMatrixManager */
        $priceMatrixManager = Mage::getSingleton('flagbit_countrybasedpricematrix/priceMatrixManager');

        /** @var Flagbit_CountryBasedPriceMatrix_Model_PriceMatrix[] $priceMatrix */
        $priceMatrix = $priceMatrixManager->getPriceMatrixItems($priceMatrixArray, $product);


        $blacklistCountries = $product->getPriceImportBlacklistCountry();
        if ($blacklistCountries) {
            $blacklistCountries = explode(',', $blacklistCountries);
            foreach ($priceMatrix as $item) {
                if (in_array($item->getCountryId(), $blacklistCountries, true)) {
                    // Set flag to keep previous value
                    $item->setKeepCountryPrice(true);
                }
            }
        }
        // Keep old Price Matrix Items
        $priceMatrix = $this->combineWitOldCountryPrices($product, $priceMatrix);

        $product->setPriceMatrix($priceMatrix);

        // Save Price Matrix
        $priceMatrixManager->saveProductPriceMatrix($product);
        $product->save();
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @param $newPriceMatrix
     * @return array
     */
    private function combineWitOldCountryPrices(Mage_Catalog_Model_Product $product, $newPriceMatrix)
    {
        $countryIds = [];
        foreach ($newPriceMatrix as $priceMatrix) {
            $countryIds[] = $priceMatrix->getCountryId();
        }

        /** @var Flagbit_CountryBasedPriceMatrix_Model_Resource_PriceMatrix_Collection $oldPriceMatrix */
        $oldPriceMatrix = Mage::getModel('flagbit_countrybasedpricematrix/priceMatrix')->getCollection();
        $oldPriceMatrix
            ->addFieldToFilter('product_id', $product->getId())
            ->addFieldToFilter('country_id', ['nin' => $countryIds]);

        /** @var Flagbit_CountryBasedPriceMatrix_Model_PriceMatrix $priceMatrix */
        foreach ($oldPriceMatrix as $priceMatrix) {
            $priceMatrix->setKeepCountryPrice(true);
        }

        return array_merge($oldPriceMatrix->getItems(), $newPriceMatrix);
    }

}

ini_set('display_errors', 1);
ini_set('memory_limit', '4096M');
$shell = new GGMgastro_Shell_UpdatePremiumProductsPriceTest();
$shell->run();