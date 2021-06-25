<?php
$installer = $this;
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$installer->startSetup();

// cria nota com o link das categorias existentes
$setup->updateAttribute('catalog_category', 'googlemerchant_category','note', '');

$installer->endSetup();