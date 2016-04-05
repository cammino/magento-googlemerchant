<?php
class Cammino_Googlemerchant_Model_Feed extends Mage_Core_Model_Abstract
{
	public function getXml() {
		$products = $this->getProducts();

		$xml = $this->getXmlStart();

		foreach ($products as $product) {
			$xml .= $this->getProductXml($product);
		}

		$xml .= $this->getXmlEnd();

		return $xml;
	}

	public function getXmlStart() {
		$xml  = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
		$xml .= "<rss xmlns:g=\"http://base.google.com/ns/1.0\" version=\"2.0\">\n";
		$xml .= "<channel>\n";
		$xml .= "<title><![CDATA[". Mage::getStoreConfig('general/store_information/name') ."]]></title>\n";
		$xml .= "<link><![CDATA[". Mage::getStoreConfig('web/unsecure/base_url') ."]]></link>\n";
		$xml .= "<description><![CDATA[". Mage::getStoreConfig('general/store_information/name') ." feed.]]></description>\n";
		return $xml;
	}

	public function getXmlEnd() {
		$xml  = "</channel>\n";
		$xml .= "</rss>";
		return $xml;
	}

	public function getProductXml($product) {
		$categories = $this->getGoogleCategory($product);

		if($this->hasCoupon($product)){
			$couponPrefix = "?coupon_code=" . $this->getCouponCode($product);
			$product = $this->getDiscount($product);
		}else{
			$couponPrefix = "";
		}

		if (is_array($categories)) {
			$xml  = "<item>\n";
			$xml .= "<title><![CDATA[". $product->getName() ."]]></title>\n";
			$xml .= "<link><![CDATA[". $product->getProductUrl() . $couponPrefix ."]]></link>\n";
			$xml .= "<description><![CDATA[". strip_tags(substr($product->getDescription(), 0, 5000)) ."]]></description>\n";
			$xml .= "<g:id>". $product->getId() ."</g:id>\n";
			$xml .= "<g:mpn>". $product->getSku() ."</g:mpn>\n";
			$xml .= "<g:condition>new</g:condition>\n";
			$xml .= $this->getPriceNode($product);
			$xml .= $this->getAvailabilityNode($product);
			$xml .= "<g:image_link><![CDATA[". (string)Mage::getModel('catalog/product_media_config')->getMediaUrl( $product->getImage() ) ."]]></g:image_link>\n";

			
			$xml .= $this->getBrandNode($product);
			$xml .= "<g:identifier_exists>FALSE</g:identifier_exists>\n";
			$xml .= $this->getCategoriesNode($categories, $product);
			$xml .= $this->getCustomLabelNode($product);
			$xml .= "</item>\n";
			return $xml;
		}
	}

	public function getCategoriesNode($categories, $product) {
		$xml  = "";
		if ($categories['googleCategory'] != "") {
			$xml .= "<g:google_product_category><![CDATA[". $categories['googleCategory'] ."]]></g:google_product_category>\n";
		}
		
		if ($product->getGooglemerchantProductType() != "") {
			$xml .= "<g:product_type><![CDATA[". $product->getGooglemerchantProductType() ."]]></g:product_type>\n"; 
		}else if ($categories['storeCategory'] != "") {
			$xml .= "<g:product_type><![CDATA[". $categories['storeCategory'] ."]]></g:product_type>\n"; 
		}

		return $xml;
	}

	public function getGoogleCategory($product) {
		$ids = $product->getCategoryIds();
		$categoryLevel = -1;
		$categories = "";
		
		foreach($ids as $id) {
			$category = Mage::getModel('catalog/category')->load($id);

			if ((strval($category->getGooglemerchantCategory()) != "") && (intval($category->getLevel()) > $categoryLevel)) {
				$categoryLevel = intval($category->getLevel());
				$categories = array(
					"googleCategory" => htmlentities($category->getGooglemerchantCategory(), ENT_COMPAT, 'UTF-8'),
					"storeCategory"	 => htmlentities($category->getName(), ENT_COMPAT, 'UTF-8')
				);
			}
		}

		return (empty($categories['googleCategory'])) ? false : $categories;
	}

	public function getPriceNode($product) {

		$xml = "";

		if ($product->getTypeId() == "simple") {
			return $this->getSimplePriceNode($product);
		} else if ($product->getTypeId() == "grouped") {
			return $this->getGroupedPriceNode($product);
		}
	}

	public function getSimplePriceNode($product) {

		$xml = "<g:price>". number_format($product->getPrice(), 2, '.', '') ."</g:price>\n";

		if ($product->getFinalPrice() < $product->getPrice()) {
			$xml .= "<g:sale_price>". number_format($product->getFinalPrice(), 2, '.', '') ."</g:sale_price>\n";

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

	public function getGroupedPriceNode($product) {
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

		return "<g:price>". number_format($minimal, 2, '.', '') ."</g:price>\n";
	}

	public function getAvailabilityNode($product) {

		if ($product->getTypeId() == "simple") {
			return $this->getSimpleAvailabilityNode($product);
		} else if ($product->getTypeId() == "grouped") {
			return $this->getGroupedAvailabilityNode($product);
		}		
	}

	public function getSimpleAvailabilityNode($product) {
		$stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product->getId());
		return "<g:availability>". ((($stock->getQty() > 0) && ($stock->getIsInStock() == "1")) ? 'in stock' : 'out of stock') ."</g:availability>\n";
	}

	public function getGroupedAvailabilityNode($product) {
		$associated = $this->getAssociatedProducts($product);
		$stock = 0;

		foreach($associated as $item) {
			$itemStock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($item->getId());

			if ($itemStock->getIsInStock() == "1") {
				$stock += $itemStock->getQty();
			}
		}

		return "<g:availability>". (($stock) ? 'in stock' : 'out of stock') ."</g:availability>\n";
	}

	public function getBrandNode($product) {
		$manufacturer = strval($product->getManufacturer());
		$xml = "";

		if ($manufacturer != "") {
			return "<g:brand>". $manufacturer ."</g:brand>\n";
		} else {
			return "";
		}
	}

	public function getCustomLabelNode($product) {

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

	public function getProducts() {
		$products = Mage::getModel('catalog/product')->getCollection();

		$products->addAttributeToSelect('*')
			->addAttributeToFilter('status', 1)
			->addAttributeToFilter('visibility', array('neq' => '1'))
			->addAttributeToFilter('type_id', array('in' => array('simple', 'grouped')))
			->addAttributeToSort('created_at', 'desc');

		return $products;
	}

	public function getAssociatedProducts($product) {
		$collection = $product->getTypeInstance(true)->getAssociatedProductCollection($product)
			->addAttributeToSelect('*')
			->addAttributeToFilter('status', 1);

		return $collection;
	}

	public function hasCoupon($product){
		$coupon = $product->getGooglemerchantCoupon();
		
		if($coupon != false && $coupon != ""){
			return true;
		}else{
			return false;
		}
	}

	public function getCouponCode($product){
		return $product->getGooglemerchantCoupon();
	}

	public function getDiscount($product){
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
