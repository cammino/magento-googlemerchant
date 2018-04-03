<?php
class Cammino_Googlemerchant_Model_Observer extends Varien_Object
{
	public function importCategories(Varien_Event_Observer $observer) {
		$category = Mage::getModel('googlemerchant/category');
		$category->importCategories();
	}

	public function injectRatingBlock(Varien_Event_Observer $observer) {
		$block = Mage::app()->getFrontController()->getAction()->getLayout()->createBlock("googlemerchant/rating");
		$blockContent = Mage::app()->getFrontController()->getAction()->getLayout()->getBlock('content');

		if ($blockContent) {
			$blockContent->append($block);
		}
	}
}