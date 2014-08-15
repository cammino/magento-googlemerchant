<?php
$installer = $this;
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$installer->startSetup();
$setup->addAttribute('catalog_category', 'googlemerchant_category', array(
	'group'				=> 'General',
	'input'				=> 'select',
	'type'				=> 'text',
	'label'				=> 'Google Merchant Categories',
	'source'			=> 'googlemerchant/source_category',
//	'backend'			=> 'eav/entity_attribute_backend_serialized',
	'visible'			=> 1,
	'required'			=> 0,
	'user_defined'		=> 1,
	'global'			=> Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
	'frontend_class'	=> 'googlemerchant-select'
));

$table = $installer->getConnection()
	->newTable($installer->getTable('googlemerchant/category'))
	->addColumn('category_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'identity'		=> true,
		'unsigned'		=> true,
		'nullable'		=> false,
		'primary'		=> true
	), 'Id')
	->addColumn('title', Varien_Db_Ddl_Table::TYPE_VARCHAR, 500, array(
		'nullable'		=> false
	), 'Title');

$installer->getConnection()->createTable($table);
$installer->run("ALTER TABLE `". $installer->getTable('googlemerchant/category') ."` CHANGE `category_id` `category_id` INT(10) AUTO_INCREMENT");
$installer->endSetup();

$category = Mage::getModel('googlemerchant/category');
$category->importCategories();
