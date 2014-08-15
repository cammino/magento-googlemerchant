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
		$xml  = "<item>\n";
		$xml .= "<title><![CDATA[". $product->getName() ."]]></title>\n";
		$xml .= "<link><![CDATA[". $product->getProductUrl() ."]]></link>\n";
		$xml .= "<description><![CDATA[". substr($product->getDescription(), 0, 5000) ."]]></description>\n";
		$xml .= "<g:id>". $product->getId() ."</g:id>\n";
		$xml .= "<g:mpn>". $product->getSku() ."</g:mpn>\n";
		$xml .= "<g:condition>new</g:condition>\n";
		$xml .= $this->getPriceNode($product);
		$xml .= $this->getAvailabilityNode($product);
		$xml .= "<g:image_link><![CDATA[". (string)Mage::helper('catalog/image')->init($product, 'image')->resize(500,500) ."]]></g:image_link>\n";
		$xml .= $this->getBrandNode($product);
		$xml .= "<g:identifier_exists>FALSE</g:identifier_exists>\n";
		$xml .= $this->getCategoriesNode($product);
		$xml .= "</item>\n";
		return $xml;
	}

	public function getCategoriesNode($product) {
		$ids = $product->getCategoryIds();
		$categoryLevel = -1;
		$googleCategory = "";
		$storeCategory = "";

		foreach($ids as $id) {
			$category = Mage::getModel('catalog/category')->load($id);

			if ((strval($category->getGooglemerchantCategory()) != "") && (intval($category->getLevel()) > $categoryLevel)) {
				$categoryLevel = intval($category->getLevel());
				$googleCategory = htmlentities($category->getGooglemerchantCategory(), ENT_COMPAT, 'UTF-8');
				$storeCategory = htmlentities($category->getName(), ENT_COMPAT, 'UTF-8');
			}
		}

		$xml  = "";

		if ($googleCategory != "") {
			$xml .= "<g:google_product_category><![CDATA[". $googleCategory ."]]></g:google_product_category>\n";
		}

		if ($storeCategory != "") {
			$xml .= "<g:product_type><![CDATA[". $storeCategory ."]]></g:product_type>\n"; 
		}

		return $xml;
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

		if ($product->getSpecialPrice() > 0) {			
			$xml .= "<g:sale_price>". number_format($product->getSpecialPrice(), 2, '.', '') ."</g:sale_price>\n";

			if(($product->getSpecialFromDate() != "") && ($product->getSpecialToDate() != "")) {
				$specialFromDate = date('c', strtotime($product->getSpecialFromDate()));
				$specialToDate = date('c', strtotime($product->getSpecialToDate()));
				$xml .= "<g:sale_price_effective_date>". $specialFromDate .'/'. $specialToDate ."</g:sale_price_effective_date>\n";
			}
		}

		return $xml;
	}

	public function getGroupedPriceNode($product) {
		$associated = $product->getTypeInstance(true)->getAssociatedProducts($product);
		$minimal = 0;

		foreach($associated as $item) {
			if ($item->getPrice() > $minimal) {
				$minimal = $item->getPrice();
			}
		}

		return "<g:price>". number_format($minimal, 2, '.', '') ."</g:price>\n";
	}

	public function getAvailabilityNode($product) {
		$stock = (Mage::getModel('cataloginventory/stock_item')->loadByProduct($product->getId())->getQty() > 0) ? true : false;
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

	public function getProducts() {
		$products = Mage::getModel('catalog/product')->getCollection();

		$products->addAttributeToSelect('*')
			->addAttributeToFilter('status', 1)
			->addAttributeToFilter('visibility', array('neq' => '1'))
			->addAttributeToSort('created_at', 'desc');

		return $products;
	}

}