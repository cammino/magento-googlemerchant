<?php
/**
 * Feed.php
 *
 * @category Cammino
 * @package  Cammino_Googlemerchant
 * @author   Cammino Digital <suporte@cammino.com.br>
 * @license  http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @link     https://github.com/cammino/magento-googlemerchant
 */

class Cammino_Googlemerchant_Model_Feed extends Mage_Core_Model_Abstract
{
    private $_helper;

    /**
    * Function responsible for construct 'cammino_googlemerchant'
    *
    * @return null
    */
    function __construct()
    {
        $this->_helper = Mage::helper("cammino_googlemerchant");
    }

    /**
    * Function responsible for get xml
    *
    * @return object
    */
    public function getXml()
    {
        $products = $this->getProducts();
        $xml = $this->getXmlStart();

        foreach ($products as $product) {
            $xml .= $this->getProductXml($product);
        }

        $xml .= $this->getXmlEnd();

        return $xml;
    }

    /**
    * Function responsible for start function 'getXml()'
    *
    * @return object
    */
    public function getXmlStart()
    {
        $xml  = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
        $xml .= "<rss xmlns:g=\"http://base.google.com/ns/1.0\" version=\"2.0\">\n";
        $xml .= "<channel>\n";
        $xml .= "<title><![CDATA[". Mage::getStoreConfig('general/store_information/name') ."]]></title>\n";
        $xml .= "<link><![CDATA[". Mage::getStoreConfig('web/unsecure/base_url') ."]]></link>\n";
        $xml .= "<description><![CDATA[". Mage::getStoreConfig('general/store_information/name') ." feed.]]></description>\n";

        return $xml;
    }

    /**
    * Function responsible to finish function 'getXml()'
    *
    * @return object
    */
    public function getXmlEnd()
    {
        $xml  = "</channel>\n";
        $xml .= "</rss>";
        return $xml;
    }

    /**
    * Function responsible for get xml products
    *
    * @param object $product Product object
    *
    * @return object
    */
    public function getProductXml($product)
    {
        if($product->getGooglemerchantDisable() == "1") {
            return "";
        }

        $categories = $this->getGoogleCategory($product);

        if ($this->_helper->hasCoupon($product)) {
            $couponPrefix = "?coupon_code=" . $this->_helper->getCouponCode($product);
            $product = $this->_helper->getDiscount($product);
        } else {
            $couponPrefix = "";
        }

        if (is_array($categories)) {
            $xml  = "<item>\n";
            $xml .= "<title><![CDATA[". $product->getName() ."]]></title>\n";
            $xml .= "<link><![CDATA[". $product->getProductUrl() . $couponPrefix ."]]></link>\n";
            $xml .= $this->getProductDescription($product);
            $xml .= "<g:id>". $product->getId() ."</g:id>\n";
            $xml .= "<g:mpn>". $product->getSku() ."</g:mpn>\n";

            if (strval($product->getEan()) != "") {
                $xml .= "<g:gtin>".$product->getEan()."</g:gtin>\n";
            }

            $xml .= "<g:condition>new</g:condition>\n";
            $xml .= $this->getPriceNode($product);
            $xml .= $this->getBilletPriceNode($product);
            $xml .= $this->getInstallmentNode($product);
            $xml .= $this->getAvailabilityNode($product);
            $xml .= "<g:image_link><![CDATA[".$this->getProductImage($product)."]]></g:image_link>\n";
            $xml .= $this->getAllImageProduct($product);

            $xml .= $this->getBrandNode($product);
            $xml .= "<g:identifier_exists>FALSE</g:identifier_exists>\n";
            $xml .= "<recomendable>" . (((strval($product->getGooglemerchantRecomendable()) == "1") || (strval($product->getGooglemerchantRecomendable()) == "")) ? "true" : "false") . "</recomendable>\n";

            $xml .= $this->getCategoriesNode($categories, $product);
            $xml .= $this->getCustomLabelNode($product);
            $xml .= $this->getSizeNode($product);
            $xml .= $this->getAdditionalNodes($product);

            $xml .= "</item>\n";

            return $xml;
        }
    }

    /**
    * Function responsible for get nodes category
    *
    * @param object $categories Product object
    *
    * @param object $product    Product object
    *
    * @return object
    */
    public function getCategoriesNode($categories, $product)
    {
        $xml  = "";
        if ($categories['googleCategory'] != "") {
            $xml .= "<g:google_product_category><![CDATA[". $categories['googleCategory'] ."]]></g:google_product_category>\n";
        }

        if ($product->getGooglemerchantProductType() != "") {
            $xml .= "<g:product_type><![CDATA[". $product->getGooglemerchantProductType() ."]]></g:product_type>\n";
        } else if ($categories['storeCategory'] != "") {
                   $xml .= "<g:product_type><![CDATA[". $categories['storeCategory'] ."]]></g:product_type>\n";
        }

        return $xml;
    }

    /**
    * Function responsible for get google category
    *
    * @param object $product Product object
    *
    * @return object
    */
    public function getGoogleCategory($product)
    {
        $ids = $product->getCategoryIds();
        $categoryLevel = -1;
        $categories = "";

        foreach ($ids as $id) {
            $category = Mage::getModel('catalog/category')->load($id);

            if ((strval($category->getGooglemerchantCategory()) != "") && (intval($category->getLevel()) > $categoryLevel)) {
                $categoryLevel = intval($category->getLevel());
                $categories = array(
                    "googleCategory" => htmlentities($category->getGooglemerchantCategory(), ENT_COMPAT, 'UTF-8'),
                    "storeCategory" => htmlentities($category->getName(), ENT_COMPAT, 'UTF-8')
                );
            }
        }

        return (empty($categories['googleCategory'])) ? false : $categories;
    }

    /**
    * Function responsible for get installment node and return object
    *
    * @param object $product Product object
    *
    * @return object
    */
    public function getInstallmentNode($product)
    {
        $xml = "";
        $installments = Mage::getSingleton('installments/standard')->getInstallments($product->getFinalPrice());
        if ( isset($installments) && $installments["value"] > 0  ) {
            $xml = "<g:installment>
                <g:months>" . $installments["qty"] . "</g:months>
                <g:amount>" . $installments["value"] . " BRL</g:amount>
            </g:installment>";
        }
        return $xml;
    }

    /**
    * Function responsible for get price nodes
    *
    * @param object $product Product object
    *
    * @return null
    */
    public function getPriceNode($product)
    {
        $xml = "";

        if ($product->getTypeId() == "simple" || $product->getTypeId() == 'configurable') {
            return $this->getSimplePriceNode($product);
        } else if ($product->getTypeId() == "grouped") {
                return $this->getGroupedPriceNode($product);
        } else if ($product->getTypeId() == "bundle") {
                return $this->getBundlePriceNode($product);
        }
    }

    /**
    * Function responsible for get price node billet
    *
    * @param object $product Product object
    *
    * @return object
    */
    public function getBilletPriceNode($product)
    {
        $modules = Mage::getConfig()->getNode('modules')->children();
        $modulesArray = (array) $modules;

        if (isset($modulesArray['Cammino_Billetdiscount'])) {
            $helper = Mage::helper("billetdiscount");
            $discountPercent = $helper->getPercentDiscount($product->getFinalPrice());
            $price = $product->getFinalPrice() - ($product->getFinalPrice() * ($discountPercent / 100));
            $price = number_format($price, 2, '.', '');

            return "<g:billet_price>". $price ."</g:billet_price>\n";
        } else {
            return "";
        }
    }

    /**
    * Function responsible for get simple price of node
    *
    * @param object $product Product object
    *
    * @return object
    */
    public function getSimplePriceNode($product)
    {
        $xml = "<g:price>". number_format($this->calcInCashRule($product->getPrice()), 2, '.', '') ."</g:price>\n";

        if ($product->getFinalPrice() < $product->getPrice()) {
            $xml .= "<g:sale_price>". number_format($this->calcInCashRule($product->getFinalPrice()), 2, '.', '') ."</g:sale_price>\n";

            if (($product->getSpecialFromDate() != "") && ($product->getSpecialToDate() != "")) {
                $specialFromDate = date('c', strtotime($product->getSpecialFromDate()));
                $dateOffset = (23*60*60)+(59*60)+59; // for add 23:59 to end date
                $specialToDate = date('c', (strtotime($product->getSpecialToDate())+$dateOffset));
                $currentDate = new DateTime();

                if ((strtotime($product->getSpecialToDate())+$dateOffset) >= $currentDate->getTimestamp()) {
                    $xml .= "<g:sale_price_effective_date>". $specialFromDate .'/'. $specialToDate ."</g:sale_price_effective_date>\n";
                }
            }
        }

        return $xml;
    }

    /**
    * Function responsible for get grouped price node
    *
    * @param object $product Product object
    *
    * @return object
    */
    public function getGroupedPriceNode($product)
    {
        $associated = $this->getAssociatedProducts($product);
        $prices = array();
        $minimal = 0;

        foreach ($associated as $item) {
            if ($item->getFinalPrice() > 0) {
                array_push($prices, $item->getFinalPrice());
            }
        }

        rsort($prices, SORT_NUMERIC);

        if (count($prices) > 0) {
            $minimal = end($prices);
        }

        $minimal = $this->calcInCashRule($minimal);

        return "<g:price>". number_format($minimal, 2, '.', '') ."</g:price>\n";
    }

    /**
    * Function responsible for get bundle price of node
    *
    * @param object $product Product object
    *
    * @return object
    */
    public function getBundlePriceNode($product)
    {
        // default price is the same of list screen
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

        $defaultPrice = $this->calcInCashRule($defaultPrice);

        return "<g:price>". number_format($defaultPrice, 2, '.', '') ."</g:price>\n";
    }
    
    /**
    * Function responsible for call cash rule
    *
    * @param object $price Product object
    *
    * @return float
    */
    public function calcInCashRule($price)
    {
        $inCashRuleId = strval(Mage::getStoreConfig('catalog/googlemerchant/incashruleid'));

        if (!empty($inCashRuleId)) {
            $rule = Mage::getModel('salesrule/rule')->load($inCashRuleId);
            $discountPrice = ((100 - floatval($rule["discount_amount"])) / 100) * $price;
            return $discountPrice;
        } else {
            return $price;
        }
    }

    /**
    * Function responsible for get node availability
    *
    * @param object $product Product object
    *
    * @return object
    */
    public function getAvailabilityNode($product)
    {
        if ($product->getTypeId() == "simple") {
            return $this->getSimpleAvailabilityNode($product);
        } else if ($product->getTypeId() == "configurable") {

            return $this->getConfigurableAvailabilityNode($product);

        } else if ($product->getTypeId() == "grouped") {

            return $this->getGroupedAvailabilityNode($product);

        } else if ($product->getTypeId() == "bundle") {

            return $this->getBundleAvailabilityNode($product);
        }
    }

    /**
    * Function responsible for get simple node availability
    *
    * @param object $product Product object
    *
    * @return object
    */
    public function getSimpleAvailabilityNode($product)
    {
        $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product->getId());

        return "<g:availability>". ((($stock->getQty() > 0) && ($stock->getIsInStock() == "1")) || ($stock->getManageStock() == "0") || (intval($stock->getBackorders()) == 1) || (intval($stock->getBackorders()) == 2) ? 'in stock' : 'out of stock') ."</g:availability>\n";
    }

    /**
    * Function responsible to configure node availability
    *
    * @param object $product Product object
    *
    * @return float
    */
    public function getConfigurableAvailabilityNode($product)
    {
        $fatherStock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product->getId());
        $_product = Mage::getModel('catalog/product')->load($product);
        $childProducts = Mage::getModel('catalog/product_type_configurable')->getUsedProducts(null, $product);

        $stock = null;

        if ($fatherStock->getIsInStock() == "0") {
            $stock = 0;
        }
        else {
            foreach ($childProducts as $child) {
                $itemStock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($child->getId());

                if ( ($itemStock->getIsInStock() == "1") || (intval($itemStock->getBackorders()) == 1) || (intval($itemStock->getBackorders()) == 2) ){
                    $stock += $itemStock->getQty();
                }
            }
        }

        return "<g:availability>". (($stock) ? 'in stock' : 'out of stock') ."</g:availability>\n";
    }

    /**
    * Function responsible for get bundle availability node
    *
    * @param object $product Product object
    *
    * @return object
    */
    public function getBundleAvailabilityNode($product)
    {
        $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product->getId());

        return "<g:availability>". (($stock->getIsInStock() == "1") || ($stock->getManageStock() == "0") ? 'in stock' : 'out of stock') ."</g:availability>\n";
    }

    /**
    * Function responsible for get grouped availability node
    *
    * @param object $product Product object
    *
    * @return object
    */
    public function getGroupedAvailabilityNode($product)
    {
        $associated = $this->getAssociatedProducts($product);
        $stock = 0;

        foreach ($associated as $item) {
                $itemStock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($item->getId());

            if ($itemStock->getIsInStock() == "1") {
                $stock += $itemStock->getQty();
            }
        }

        return "<g:availability>". (($stock) ? 'in stock' : 'out of stock') ."</g:availability>\n";
    }

    /**
    * Function responsible for get brand node
    *
    * @param object $product Product object
    *
    * @return string
    */
    public function getBrandNode($product)
    {
        $manufacturer = strval($product->getManufacturer());
        $xml = "";

        if ($manufacturer != "") {
            return "<g:brand>". $manufacturer ."</g:brand>\n";
        } else {
            return "";
        }
    }

    /**
    * Function responsible for get node custom label
    *
    * @param object $product Product object
    *
    * @return string
    */
    public function getCustomLabelNode($product)
    {

        $customlabel = "";

        if ($product->getGooglemerchantCustomlabel_0() != "") {
            $customlabel .= "<g:custom_label_0>". $product->getGooglemerchantCustomlabel_0() ."</g:custom_label_0>";
        }

        if ($product->getGooglemerchantCustomlabel_1() != "") {
            $customlabel .= "<g:custom_label_1>". $product->getGooglemerchantCustomlabel_1() ."</g:custom_label_1>";
        }

        if ($product->getGooglemerchantCustomlabel_2() != "") {
            $customlabel .= "<g:custom_label_2>". $product->getGooglemerchantCustomlabel_2(). "</g:custom_label_2>";
        }

        if ($product->getGooglemerchantCustomlabel_3() != "") {
            $customlabel .= "<g:custom_label_3>". $product->getGooglemerchantCustomlabel_3(). "</g:custom_label_3>";
        }

        if ($product->getGooglemerchantCustomlabel_4() != "") {
            $customlabel .= "<g:custom_label_4>". $product->getGooglemerchantCustomlabel_4(). "</g:custom_label_4>";
        }

        return $customlabel;
    }

    /**
    * Function responsible for get products
    *
    * @return object
    */
    public function getProducts()
    {
        $products = Mage::getModel('catalog/product')->getCollection();

        $products->addAttributeToSelect('*')
            ->addAttributeToFilter('status', 1)
            ->addAttributeToFilter('visibility', array('neq' => '1'))
            ->addAttributeToFilter('type_id', array('in' => array('simple', 'grouped', 'bundle', 'configurable')))
            ->addAttributeToSort('created_at', 'desc');

        return $products;
    }

    /**
    * Function responsible for get associated products
    *
    * @param object $product Product object
    *
    * @return object
    */
    public function getAssociatedProducts($product)
    {
        $collection = $product->getTypeInstance(true)->getAssociatedProductCollection($product)
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('status', 1);

        return $collection;
    }

    /**
    * Function responsible for get product image
    *
    * @param object $product Product object
    *
    * @return string
    */
    public function getProductImage($product)
    {
        $merchantImage = $product->getGooglemerchantImage();

        if ($merchantImage != "" && $merchantImage != null && $merchantImage != "no_selection") {

            return (string) Mage::getModel('catalog/product_media_config')->getMediaUrl($product->getGooglemerchantImage());

        } else {

            return (string) Mage::getModel('catalog/product_media_config')->getMediaUrl($product->getImage());
        }
    }

    /**
    * Function responsible for get product description
    *
    * @param object $product Product object
    *
    * @return string
    */
    public function getProductDescription($product)
    {
        return "<description><![CDATA[". strip_tags(substr($product->getDescription(), 0, 5000)) ."]]></description>\n";
    }

    /**
    * Function responsible for get additional nodes
    *
    * @param object $product Product object
    *
    * @return string
    */
	public function getAdditionalNodes($product){
		$helper = Mage::helper("cammino_googlemerchant/custom");
		return $helper->getAdditionalNodes($product);
	}

    /**
    * Function responsible for get all image product
    *
    * @param object $product Product object
    *
    * @return object
    */
    public function getAllImageProduct($product)
    {
        $product = Mage::getModel('catalog/product')->load($product->getId()); // load product
        $medias = $product->getMediaAttributes(); // all images

        foreach ($medias as $index=>$media) {
                $xml .= "<g:image_". $index . "><![CDATA[". (string) Mage::getModel('catalog/product_media_config')->getMediaUrl($product->{'get'.$index}()) . "]]></g:image_". $index . ">";
        }

        return $xml;
    }

    /**
    * Function responsible for get size of product
    *
    * @param object $product Product object
    *
    * @return string
    */
    public function getSizeNode($product)
    {
        $attributeSize = Mage::getStoreConfig('catalog/googlemerchant/attributesize');
        $xml = "";

        if (($attributeSize != null) && ($attributeSize != "")) {
            $sizes = array();
            if ($product->isConfigurable()) {
                $associatedProducts = $product->getTypeInstance(true)->getUsedProducts(null, $product);
                foreach ($associatedProducts as $associated) {
                    $size = $associated->getAttributeText($attributeSize);
                    if (strval($size) != '') {
                        $sizes[] = $associated->getAttributeText($attributeSize);
                    }
                }
                if (!empty($sizes)) {
                    $xml = "<g:size>". implode(",", $sizes) ."</g:size>\n";
                }
            } else {
                $size = $product->getAttributeText($attributeSize);
                if (($size != null) && ($size != "")) {
                    $xml = "<g:size>". $size ."</g:size>\n";
                }
            }
        }

        return $xml;
    }
}
