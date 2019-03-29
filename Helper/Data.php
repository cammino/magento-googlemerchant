<?php
/**
* Data.php
*
* @category Cammino
* @package  Cammino_Googlemerchant
* @author   Cammino Digital <suporte@cammino.com.br>
* @license  http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
* @link     https://github.com/cammino/magento-googlemerchant
*/

class Cammino_Googlemerchant_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
    * Function responsible for check if has google merchant coupon and return true or false
    *
    * @param object $product Product object
    *
    * @return boolean
    */
    public function hasCoupon($product)
    {
        $coupon = $product->getGooglemerchantCoupon();
    
        if ($coupon != false && $coupon != "") {
            return true;
        } else {
            return false;
        }
    }

    /**
    * Function responsible for get coupon code and return object
    *
    * @param object $product Product object
    *
    * @return object
    */
    public function getCouponCode($product)
    {
        return $product->getGooglemerchantCoupon();
    }

    /**
    * Function responsible for get product discount and return object
    *
    * @param object $product Product object
    *
    * @return object
    */
    public function getDiscount($product)
    {
        $quote = Mage::getModel('sales/quote')->setStoreId(1);

        $stockItem = Mage::getModel('cataloginventory/stock_item');
        $stockItem->assignProduct($product)
            ->setData('stock_id', 1)
            ->setData('store_id', 1);

        $stockItem->setUseConfigManageStock(false);
        $stockItem->setManageStock(false);

        $quote->addProduct($product,1);
        $quote->getShippingAddress()->setCountryId('BR');
        $quote->setCouponCode($this->getCouponCode($product));
        $quote->collectTotals();

        $product->setFinalPrice($quote->getGrandTotal());

        return $product;
    }
}
