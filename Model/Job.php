<?php

class Cammino_Googlemerchant_Model_Job
{
    public function createxml() {
        $minuteInterval = Mage::getStoreConfig('catalog/googlemerchant/xmlmininterval');
        $currentDateTimeString = Mage::getModel('core/date')->date('Y-m-d H:i:s');
        $currentTimestamp = Mage::getModel('core/date')->timestamp(time());

        $lastCreatedAt = Mage::getStoreConfig('catalog/googlemerchant/xmllastcreated');
        if (empty($lastCreatedAt)) {
            Mage::getModel('core/date')->date('Y-m-d H:i:s');
        }
        $lastTimestamp = Mage::getModel('core/date')->timestamp(strtotime($lastCreatedAt));

        Mage::log($currentDateTimeString, null, 'jobmerchant.log');
        Mage::log($currentTimestamp, null, 'jobmerchant.log');

        Mage::log($lastCreatedAt, null, 'jobmerchant.log');
        Mage::log($lastTimestamp, null, 'jobmerchant.log');

        Mage::log(($currentTimestamp - $lastTimestamp), null, 'jobmerchant.log');

        if (($currentTimestamp - $lastTimestamp) == $minuteInterval) {
            Mage::log('entrou', null, 'jobmerchant.log');
        }

        Mage::getModel('core/config')->saveConfig('catalog/googlemerchant/xmllastcreated', $currentDateTimeString, 'default');
        Mage::app()->getCacheInstance()->cleanType('config');

    }

}