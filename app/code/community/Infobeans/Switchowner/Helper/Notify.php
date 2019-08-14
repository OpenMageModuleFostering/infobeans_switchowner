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

class Infobeans_Switchowner_Helper_Notify extends Mage_Core_Helper_Abstract
{

    /**
     * Notify Customer
     *
     * @param Infobeans_Switchowner_Model_Order $order
     * @param $customerId
     * @param $customerIsGuest
     * @return $this
     */
    public function notifyCustomer(Infobeans_Switchowner_Model_Order $order, $customerId, $customerIsGuest)
    {
        if (!Mage::helper('switchowner')->configNotificationEnabled()) {
            return $this;
        }

        $store = $order->getStore();
        $storeId = $order->getStoreId();
        $customer = Mage::getModel('customer/customer')->load($customerId);

        $customerPrevName = $order->getPreviousCustomerName();
        $customerIsGuest = $customerIsGuest && !$customerIsGuest;

        $vars = array(
            'order' => $order,
            'customer' => $customer,
            'store' => $store,
            'is_guest' => $customerIsGuest ? 1 : 0,
            'is_customer' => $customerIsGuest ? 0 : 1,
            'old_customer_name' => $customerPrevName,
        );

        $template = Mage::getStoreConfig('switchowner/notification/template', $storeId);
        $sender = Mage::getStoreConfig('switchowner/notification/identity', $storeId);
        $copyTo = Mage::getStoreConfig('switchowner/notification/copy_to', $storeId);
        $receivers = array($customer->getEmail());

        if ($copyTo) {
            $copyReceivers = explode(",", $copyTo);
            $receivers = array_merge($receivers, $copyReceivers);
        }

        foreach ($receivers as $receiver) {
            /** @var Mage_Core_Model_Email_Template $mailTemplate */
            $mailTemplate = Mage::getModel('core/email_template');
            try {

                $mailTemplate
                    ->setDesignConfig(array('area' => 'frontend', 'store' => $storeId))
                    ->sendTransactional(
                        $template,
                        $sender,
                        trim($receiver),
                        $customer->getName(),
                        $vars,
                        $storeId
                    );

            } catch (Exception $e) {

                Mage::logException($e);
            }
        }

        return $this;
    }

}