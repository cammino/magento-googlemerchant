<?php
class Cammino_Googlemerchant_Block_Autocomplete extends Mage_Adminhtml_Block_Catalog_Category_Tab_Attributes {

    protected function _getAdditionalElementHtml($element)
    {
        if($element->getId() == 'googlemerchant_category') {
            return '
<style>
#group_4googlemerchant_category_autocomplete {
    position: absolute;
    width: 300px;
    height: 200px;
    left: 498px;
    top: 1206px;
    background: #fff;
    border: 1px solid #ddd;
    padding: 10px;
    overflow: auto;
}
#group_4googlemerchant_category_autocomplete ul li {
    cursor: pointer;
    padding: 4px 8px;
}
#group_4googlemerchant_category_autocomplete ul li:hover {
    background: #f0f0f0;
}
</style>
<div id="group_4googlemerchant_category_autocomplete"></div>
<script type="text/javascript">
new Ajax.Request(\'/googlemerchant/feed/categories\', {
    method:\'get\',
    onSuccess: function(transport) {
        var response = transport.responseText;
        var items = response.split("\n");
        new Autocompleter.Local(\'group_4googlemerchant_category\', \'group_4googlemerchant_category_autocomplete\', items, {});
    },
    onFailure: function() {}
});
</script>
            ';
        }
        
    }

}
?>