<?php
/**
* Microdata.php
*
* @category Cammino
* @package  Cammino_Googlemerchant
* @author   Cammino Digital <suporte@cammino.com.br>
* @license  http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
* @link     https://github.com/cammino/magento-googlemerchant
*/

class Cammino_Googlemerchant_Block_Microdata extends Mage_Core_Block_Template
{
    /**
    * Function responsible for review products
    *
    * @return null
    */
    protected function _toHtml()
    {
        $hideMicrodata = (bool) Mage::getStoreConfig('catalog/googlemerchant/hidemicrodata');
        
        if ($hideMicrodata) {
            return "";
        }

        try {
            $result = array();

            if ((Mage::app()->getFrontController()->getRequest()->getControllerName() == "product") &&
                (Mage::registry('current_product') != null)) {

                $product = Mage::registry('current_product');
                $currency = trim(Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol());
                $productPrice = $this->getProductPrice($product);
                $availability = $this->getProductAvailability($product);
                $ratingOb = Mage::getModel('rating/rating')->getEntitySummary($product->getId());

                $result[] = "<div itemscope itemtype=\"http://schema.org/Product\">";
                $result[] = sprintf("<meta itemprop=\"sku\" content=\"%s\">", $this->escapeHtml($product->getId()));
                $result[] = sprintf("<meta itemprop=\"name\" content=\"%s\">", $this->escapeHtml($product->getName()));

                /* Product Rating */
                if ($ratingOb->getSum() && $ratingOb->getCount()) {
                    $ratingSum = $ratingOb->getSum() / 20;      // Each star is equivalent to 20
                    $ratingCount = $ratingOb->getCount() / 2;   // 2 reviews by person
                    $rating = ($ratingSum / $ratingCount) / 2;  // Average 2 Reviews
                    
                    $result[] = "<div itemprop=\"aggregateRating\" itemscope itemtype=\"http://schema.org/AggregateRating\">";
                    $result[] = "<meta itemprop=\"bestRating\" content=\"5\">"; 
                    $result[] = "<meta itemprop=\"worstRating\" content=\"1\">";
                    $result[] = sprintf("<meta itemprop=\"ratingValue\" content=\"%s\">", $rating);
                    $result[] = sprintf("<meta itemprop=\"ratingCount\" content=\"%s\">", $ratingCount);
                    $result[] = "</div>";
                }

                $result[] = "<div itemprop=\"offers\" itemscope itemtype=\"http://schema.org/Offer\">";
                $result[] = sprintf("<meta itemprop=\"price\" content=\"%s\">", number_format($productPrice, 2, '.', ''));
                $result[] = sprintf("<meta itemprop=\"priceCurrency\" content=\"%s\">", $this->escapeHtml($currency));

                if ($availability) {
                    $result[] = "<meta itemprop=\"availability\" content=\"http://schema.org/InStock\">";
                } else {
                    $result[] = "<meta itemprop=\"availability\" content=\"http://schema.org/OutOfStock\">";
                }
                $result[] = "</div>";
                $result[] = "</div>";
            }

            return implode("\n", $result);
            
        } catch (Exception $ex) {
            return "";
        }
    }

    /**
    * Function responsible for get product and return price
    *
    * @param object $product Product object
    *
    * @return float
    */
    protected function getProductPrice($product)
    {
        $feed = Mage::getModel('googlemerchant/feed');
        $xml = $feed->getPriceNode($product);
        $piped = str_replace('<g:price>', '', $xml);
        $piped = str_replace('</g:price>', '', $piped);
        $piped = str_replace('<g:sale_price>', '|', $piped);
        $piped = str_replace('</g:sale_price>', '', $piped);
        $piped = str_replace('<g:sale_price_effective_date>', '|', $piped);
        $piped = str_replace('</g:sale_price_effective_date>', '', $piped);
        $priceParts = explode('|', $piped);

        var_dump($priceParts);

        if (count($priceParts) == 3) {
            $period = explode('/', $priceParts[2]);
            $currentDate = new DateTime();
            if (( strtotime($period[0]) >= $currentDate ) && ( strtotime($period[1]) <= $currentDate )) {
                return $priceParts[1];
            } else {
                return $priceParts[0];
            }
        } else if (count($priceParts) == 2) {
            return $priceParts[1];
        } else {
            return $priceParts[0];
        }
    }

    /**
    * Function responsible for get product availability
    *
    * @param object $product Product object
    *
    * @return boolean
    */
    protected function getProductAvailability($product)
    {
        $feed = Mage::getModel('googlemerchant/feed');
        $xml = $feed->getAvailabilityNode($product);
        $availability = str_replace('<g:availability>', '', $xml);
        $availability = str_replace('</g:availability>', '', $availability);

        return ($availability == "in stock");
    }

}