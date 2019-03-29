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

class Cammino_Googlemerchant_Model_Source_Category extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    /**
    * Function responsible for get all category options
    *
    * @return object
    */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = Mage::getResourceModel('googlemerchant/category_collection')->load()->toOptionArray();
        }

        array_unshift($this->_options, array("value" => "", "label" => Mage::helper('catalog')->__('-- Please Select --')));

        return $this->_options;
    }
    /**
    * Function responsible for call function 'getAllOptions()'
    *
    * @return object
    */
    public function toOptionArray()
    {
        return $this->getAllOptions();
    }

}
