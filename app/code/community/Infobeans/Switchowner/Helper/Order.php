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

class Infobeans_Switchowner_Helper_Order extends Mage_Core_Helper_Abstract
{
    
    public function isGuestOrder($orderId = null)
    {
        return !$this->getOrder($orderId)->getCustomerId();
    }

    public function getOrder($orderId = null)
    {
        if (!$orderId) {
            $orderId = Mage::app()->getRequest()->getParam('order_id');
        }

        $order = Mage::getModel('switchowner/order')->load($orderId);
        if ($order->getId()) {
            return $order;
        } else {
            return null;
        }
    }
}