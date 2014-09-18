<?php
class Cammino_Googlemerchant_Model_Source_Category extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = Mage::getResourceModel('googlemerchant/category_collection')->load()->toOptionArray();
        }

        array_unshift($this->_options, array("value" => "", "label" => Mage::helper('catalog')->__('-- Please Select --')));

        return $this->_options;
    }

    public function toOptionArray()
    {
        return $this->getAllOptions();
    }

}
