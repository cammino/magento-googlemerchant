<?php
/**
 * Category.php
 *
 * @category Cammino
 * @package  Cammino_Googlemerchant
 * @author   Cammino Digital <suporte@cammino.com.br>
 * @license  http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @link     https://github.com/cammino/magento-googlemerchant
 */

class Cammino_Googlemerchant_Model_Category extends Mage_Core_Model_Abstract
{
    /**
    * Function responsible for get init google merchant category
    *
    * @return null
    */
    public function _construct()
    {
        parent::_construct();
        $this->_init('googlemerchant/category');
    }

    /**
    * Function responsible for import google merchant category
    *
    * @return null
    */
    public function importCategories()
    {
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