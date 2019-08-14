<?php
/**
 * InfoBeans SwitchOwner Extension
 *
 * @category   Infobeans
 * @package    Infobeans_Switchowner
 * @version    1.0.1
 * @author     InfoBeans Technologies Limited http://www.infobeans.com/
 * @copyright  Copyright (c) 2016 InfoBeans Technologies Limited
 */

class Infobeans_Switchowner_Helper_Data extends Mage_Core_Helper_Abstract
{

    public function configNotificationEnabled()
    {
        return Mage::getStoreConfig('switchowner/notification/enabled');
    }

    /**
     * Is Allowed Action
     *
     * @return boolean
     */
    public function isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/switchowner');
    }

    /**
     * Retrieves is Enabled
     *
     * @return boolean
     */
    public function extEnabled()
    {
        return !Mage::getStoreConfig('advanced/modules_disable_output/Infobeans_Switchowner');
    }
}