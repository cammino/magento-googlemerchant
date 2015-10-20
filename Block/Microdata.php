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
                $availability = $this->getProductAvailability($product);

                $result[] = "<div itemscope itemtype=\"http://schema.org/Product\">";
                $result[] = sprintf("<meta itemprop=\"sku\" content=\"%s\">", $this->escapeHtml($product->getId()));
                $result[] = sprintf("<meta itemprop=\"name\" content=\"%s\">", $this->escapeHtml($product->getName()));
                $result[] = sprintf("<meta itemprop=\"price\" content=\"%s\">", number_format($productPrice, 2, '.', ''));
                $result[] = sprintf("<meta itemprop=\"priceCurrency\" content=\"%s\">", $this->escapeHtml($currency));

                if ($availability) {
                    $result[] = "<meta itemprop=\"availability\" content=\"http://schema.org/InStock\">";
                } else {
                    $result[] = "<meta itemprop=\"availability\" content=\"http://schema.org/OutOfStock\">";
                }

                $result[] = "</div>";
            }

            return implode("\n", $result);
            
        } catch (Exception $ex) {
        	return "";
        }
    }

    protected function getProductPrice($product) {
        $price = 0;

        if ($product->getTypeId() == "simple") {

            $price = $product->getPrice();

            if ($product->getSpecialPrice() > 0) {
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
        }

        return $price;
    }

    protected function getProductAvailability($product) {

        if ($product->getTypeId() == "simple") {

            $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product->getId());
            return (($stock->getQty() > 0) && ($stock->getIsInStock() == "1"));

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
        }
    }

    protected function getAssociatedProducts($product) {
        $collection = $product->getTypeInstance(true)->getAssociatedProductCollection($product)
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('status', 1);

        return $collection;
    }

}