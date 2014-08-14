<?php
class Cammino_Googlemerchant_Model_Category extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('googlemerchant/category');
    }

    public function importCategories() {
        $locale = strval(Mage::getStoreConfig('catalog/googlemerchant/taxonomylocale')) == "" ? "pt-BR" : strval(Mage::getStoreConfig('catalog/googlemerchant/taxonomylocale'));
        $url = "http://www.google.com/basepages/producttype/taxonomy.". $locale .".txt";
        $file = Mage::getBaseDir('var') . "/taxonomy.txt";
        $content = file_get_contents($url);

        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $sql = "DELETE FROM googlemerchant_category;
        		ALTER TABLE googlemerchant_category AUTO_INCREMENT = 1;
        		INSERT INTO googlemerchant_category (title) VALUES ('" . str_replace("\n", "'),('", str_replace("'", "\'", $content)) . "');
        		DELETE FROM googlemerchant_category WHERE title LIKE '#%' OR title LIKE '';";
        $write->query($sql);
    }
}