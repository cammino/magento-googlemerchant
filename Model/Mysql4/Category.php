<?php
class Cammino_Googlemerchant_Model_Mysql4_Category extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
		$this->_init('googlemerchant/category', 'category_id');
    }
}