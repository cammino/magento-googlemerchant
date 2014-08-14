<?php
class Cammino_Googlemerchant_Model_Observer extends Varien_Object
{
	public function importCategories(Varien_Event_Observer $observer) {
		$category = Mage::getModel('googlemerchant/category');
		$category->importCategories();
	}
}