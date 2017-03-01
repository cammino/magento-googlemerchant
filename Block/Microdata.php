<?php
class Cammino_Googlemerchant_Block_Microdata extends Mage_Core_Block_Template
{
    protected function _toHtml() {

        try {
            $result = array();

            if ((Mage::app()->getFrontController()->getRequest()->getControllerName() == "product") &&
                (Mage::registry('current_product') != null)) {

                $product = Mage::registry('current_product');
                $currency = trim(Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol());
                $productPrice = $this->getProductPrice($product);
                $productPrice = $this->calcInCashRule($productPrice);
                $availability = $this->getProductAvailability($product);
                $RatingOb = Mage::getModel('rating/rating')->getEntitySummary($product->getId());

                $result[] = "<div itemscope itemtype=\"http://schema.org/Product\">";
                $result[] = sprintf("<meta itemprop=\"sku\" content=\"%s\">", $this->escapeHtml($product->getId()));
                $result[] = sprintf("<meta itemprop=\"name\" content=\"%s\">", $this->escapeHtml($product->getName()));

                /* Product Rating */
                if($RatingOb->getSum() && $RatingOb->getCount()){
                    $ratingSum = $RatingOb->getSum() / 20;      // Cada estrela equivale a 20
                    $ratingCount = $RatingOb->getCount() / 2;   // 2 avaliacoes por pessoa
                    $rating = ($ratingSum / $ratingCount) / 2;  // Media das 2 avaliacoes
                    
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

    protected function getProductPrice($product) {
        $price = 0;
        $now   = Mage::getModel('core/date')->date('Y-m-d 00:00:00');
        
        if ($product->getTypeId() == "simple") {

            $price = $product->getPrice();
            
            if ($product->getSpecialPrice() > 0 && ($now >= $product->getSpecialFromDate() && ( ($product->getSpecialToDate() == "") || $now <= $product->getSpecialToDate()))) {
                $price = $product->getSpecialPrice();
            }

        } else if ($product->getTypeId() == "grouped") {

            $associated = $this->getAssociatedProducts($product);
            $prices = array();
            $minimal = 0;

            foreach($associated as $item) {
                if ($item->getPrice() > 0) {
                    array_push($prices, $item->getPrice());
                }
            }

            rsort($prices, SORT_NUMERIC);

            if (count($prices) > 0) {
                $minimal = end($prices);    
            }

            $price = $minimal;
        } else if ($product->getTypeId() == "bundle") {
            $optionCollection = $product->getTypeInstance(true)->getOptionsIds($product);
            $selectionsCollection = Mage::getModel('bundle/selection')->getCollection();
            $selectionsCollection->getSelect()->where('option_id in (?)', $optionCollection)->where('is_default = ?', 1);
            $defaultPrice = 0;

            foreach ($selectionsCollection as $_selection) {
                $_selectionProduct = Mage::getModel('catalog/product')->load($_selection->getProductId());
                $_selectionPrice = $product->getPriceModel()->getSelectionFinalTotalPrice(
                    $product,
                    $_selectionProduct,
                    0,
                    $_selection->getSelectionQty(),
                    false,
                    true
                );
                $defaultPrice += ($_selectionPrice * $_selection->getSelectionQty());
            }

            return $price = ($defaultPrice);
            }
    
        return $price;
    }

    protected function getProductAvailability($product) {

        if ($product->getTypeId() == "simple") {

            $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product->getId());
            //return (($stock->getQty() > 0) && ($stock->getIsInStock() == "1"));
            return (($stock->getQty() > 0) && ($stock->getIsInStock() == "1")) || ($stock->getManageStock() == "0");

        } else if ($product->getTypeId() == "grouped") {

            $associated = $this->getAssociatedProducts($product);
            $stock = 0;

            foreach($associated as $item) {
                $itemStock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($item->getId());

                if ($itemStock->getIsInStock() == "1") {
                    $stock += $itemStock->getQty();
                }
            }

            return ($stock > 0);
        } else if ($product->getTypeId() == "bundle") {
            $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product->getId());
            return ($stock->getIsInStock() == "1") || ($stock->getManageStock() == "0");
        }
    }

    protected function getAssociatedProducts($product) {
        $collection = $product->getTypeInstance(true)->getAssociatedProductCollection($product)
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('status', 1);

        return $collection;
    }

    public function calcInCashRule($price) {

        $inCashRuleId = strval(Mage::getStoreConfig('catalog/googlemerchant/incashruleid'));

        if (!empty($inCashRuleId)) {
            $rule = Mage::getModel('salesrule/rule')->load($inCashRuleId);
            $discountPrice = ((100 - floatval($rule["discount_amount"])) / 100) * $price;
            return $discountPrice;
        } else {
            return $price;
        }
    }

}