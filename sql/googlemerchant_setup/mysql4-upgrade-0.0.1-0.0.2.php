<?php
$installer = $this;
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$installer->startSetup();

// transforma a categoria em tipo texto
$setup->updateAttribute('catalog_category', 'googlemerchant_category','frontend_input', 'text');
// cria nota com o link das categorias existentes
$setup->updateAttribute('catalog_category', 'googlemerchant_category','note', 'Acesse a <a target="_blank" href="https://www.google.com/basepages/producttype/taxonomy-with-ids.pt-BR.txt">lista de categorias do Google Merchant</a>');

// Migra input de categoria para aba de informações gerais
$entityTypeId     = $setup->getEntityTypeId('catalog_category');
$attributeSetId   = $setup->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = $setup->getAttributeGroupId($entityTypeId, $attributeSetId, 'General Information');

$setup->addAttributeToGroup(
    $entityTypeId,
    $attributeSetId,
    $attributeGroupId,
    'googlemerchant_category',
    '99'					
);

$installer->endSetup();