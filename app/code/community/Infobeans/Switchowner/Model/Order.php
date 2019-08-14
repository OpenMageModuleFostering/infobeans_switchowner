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

class Infobeans_Switchowner_Model_Order extends Mage_Sales_Model_Order
{
    protected $_ownerswitchHistoryCollection = null;

    /**
     * Customer
     *
     * @param $customerId
     * @return Mage_Customer_Model_Customer
     */
    protected function _getCustomer($customerId)
    {
        $customer = Mage::getModel('customer/customer')->load($customerId);
        return $customer;
    }

    /**
     * Helper
     *
     * @return Infobeans_Switchowner_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('switchowner');
    }

    public function getNameParts()
    {
        return array(
            'prefix',
            'firstname',
            'middlename',
            'lastname',
            'suffix',
        );
    }

    public function getPreviousCustomerName()
    {
        $nameParts = array();
        /** @var Infobeans_Switchowner_Model_History $lastItem */
        $lastItem = $this->getOwnerSwitchHistory()->getLastItem();
        if ($lastItem && $lastItem->hasDetails()){
            $from = $lastItem->getFromData();

            foreach ($this->getNameParts() as $key){
                $fromKey = 'customer_'.$key;
                if (isset($from[$fromKey]) && $from[$fromKey]){
                    $nameParts[] = $from[$fromKey];
                }
            }

            return implode(" ", $nameParts);
        }

        return false;
    }

    /**
     * Switch Order Owner
     *
     * @param $customerId
     * @param bool $overwriteName
     * @param bool $sendEmail
     * @return Infobeans_Switchowner_Model_Order
     */
    public function switchOwner($customerId, $overwriteName = 1, $sendEmail = true, $overwriteAddress="")
    {
        $customer = $this->_getCustomer($customerId);

        /** @var $history Infobeans_Switchowner_Model_History */
        $history = Mage::getModel('switchowner/history');
        $history->applyOrder($this, $sendEmail);
        $prevIsGuest = $this->getCustomerIsGuest();

        $order = Mage::getModel('sales/order')->load($this->getId());
        $oldCustomerGroup = $order->getCustomerGroupId();
        $newCustomerGroup = $customer->getGroupId();
        
        $user = Mage::getSingleton('admin/session'); 
        $adminUserId = $user->getUser()->getUserId();
        
        $history
            ->addDetails('customer_id', $this->getCustomerId(), $customerId)
            ->addDetails('customer_email', $this->getCustomerEmail(), $customer->getEmail())
            ->addDetails('customer_is_guest', $this->getCustomerIsGuest(), $customer->getIsGuest())
            ->addDetails('assignor', "", $adminUserId);

        $this
            ->setCustomerId($customerId)
            ->setCustomerEmail($customer->getEmail())
            ->setCustomerIsGuest(0)
            ->setCustomerGroupId($newCustomerGroup);

        if ($overwriteName == 1) {
            $nameParts = $this->getNameParts();
            foreach ($nameParts as $nameKey) {
                $dataKey = 'customer_' . $nameKey;
                $history->addDetails($dataKey, $this->getData($dataKey), $customer->getData($nameKey));
                $this->setData($dataKey, $customer->getData($nameKey));
            }
        }

        if ($sendEmail) {
            Mage::helper('switchowner/notify')->notifyCustomer($this, $customerId, $prevIsGuest);
        }

        // Overwrite billing/shipping address of customer
        if ($overwriteAddress == 1) {
            //getting default billing address
            $customerBillingAddressId = $customer->getDefaultBilling();
            $defaultBillingAddress = Mage::getModel('customer/address')->load($customerBillingAddressId)->getData();
            
            $billingData = array (
                'firstname' => isset($defaultBillingAddress['firstname']) ? $defaultBillingAddress['firstname'] : "",
                'middlename'=> isset($defaultBillingAddress['middlename']) ? $defaultBillingAddress['middlename'] : "",
                'lastname'=> isset($defaultBillingAddress['lastname']) ? $defaultBillingAddress['lastname'] : "",
                'suffix'=> isset($defaultBillingAddress['suffix']) ? $defaultBillingAddress['suffix'] : "",
                'prefix'=> isset($defaultBillingAddress['prefix']) ? $defaultBillingAddress['prefix'] : "",
                'company'=> isset($defaultBillingAddress['company']) ? $defaultBillingAddress['company'] : "",
                'street'=> isset($defaultBillingAddress['street']) ? $defaultBillingAddress['street'] : "",
                'city'=> isset($defaultBillingAddress['city']) ? $defaultBillingAddress['city'] : "", 
                'country_id'=> isset($defaultBillingAddress['country_id']) ? $defaultBillingAddress['country_id'] : "", 
                'region'=> isset($defaultBillingAddress['region']) ? $defaultBillingAddress['region'] : "",
                'region_id'=> isset($defaultBillingAddress['region_id']) ? $defaultBillingAddress['region_id'] : "",
                'postcode'=> isset($defaultBillingAddress['postcode']) ? $defaultBillingAddress['postcode'] : "", 
                'telephone'=> isset($defaultBillingAddress['telephone']) ? $defaultBillingAddress['telephone'] : "",
                'fax'=> isset($defaultBillingAddress['fax']) ? $defaultBillingAddress['fax'] : "", 
                'email'=>  $customer->getEmail(),
                'vat_id'=> isset($defaultBillingAddress['vat_id']) ? $defaultBillingAddress['vat_id'] : "",
            );
            
            
            //getting default shipping address
            $customerShippingAddressId = $customer->getDefaultShipping();
            $defaultShippingAddress = Mage::getModel('customer/address')->load($customerShippingAddressId)->getData();
            
            $shippingData = array (
                'firstname' => isset($defaultShippingAddress['firstname']) ? $defaultShippingAddress['firstname'] : "",
                'middlename'=> isset($defaultShippingAddress['middlename']) ? $defaultShippingAddress['middlename'] : "",
                'lastname'=> isset($defaultShippingAddress['lastname']) ? $defaultShippingAddress['lastname'] : "",
                'suffix'=> isset($defaultShippingAddress['suffix']) ? $defaultShippingAddress['suffix'] : "",
                'prefix'=> isset($defaultShippingAddress['prefix']) ? $defaultShippingAddress['prefix'] : "",
                'company'=> isset($defaultShippingAddress['company']) ? $defaultShippingAddress['company'] : "",
                'street'=> isset($defaultShippingAddress['street']) ? $defaultShippingAddress['street'] : "",
                'city'=> isset($defaultShippingAddress['city']) ? $defaultShippingAddress['city'] : "",
                'country_id'=> isset($defaultShippingAddress['country_id']) ? $defaultShippingAddress['country_id'] : "",
                'region'=> isset($defaultShippingAddress['region']) ? $defaultShippingAddress['region'] : "",
                'region_id'=> isset($defaultShippingAddress['region_id']) ? $defaultShippingAddress['region_id'] : "",
                'postcode'=> isset($defaultShippingAddress['postcode']) ? $defaultShippingAddress['postcode'] : "",
                'telephone'=> isset($defaultShippingAddress['telephone']) ? $defaultShippingAddress['telephone'] : "",
                'fax'=> isset($defaultShippingAddress['fax']) ? $defaultShippingAddress['fax'] : "",
                'email'=> $customer->getEmail(),
                'vat_id'=> isset($defaultShippingAddress['vat_id']) ? $defaultShippingAddress['vat_id'] : "",
            );
            
            try {
                // Modify billing/shipping address
                $billingId = $order->getBillingAddressId();
                if ($billingId) {
                    $billingAddress = Mage::getModel('sales/order_address')->load($billingId);
                    $billingAddress->addData($billingData);
                    $billingAddress->implodeStreetAddress()->save();
                }
                
                $shippingId = $order->getShippingAddressId();
                if($shippingId) {
                    $shippingAddress = Mage::getModel('sales/order_address')->load($shippingId);
                    $shippingAddress->addData($shippingData);        
                    $shippingAddress->implodeStreetAddress()->save();
                }
            } catch (Exception $e) {
                Mage::log($e, null, "IBswitchowner.log");
                Mage::getSingleton('core/session')->addError($e->getMessage());
                return;
            }
        }
        
        $this->save();
                
        // To display downloads in new owner account
        $items = $order->getAllItems();
        foreach ($items as $item) {
            if ($item->getProductType() == 'downloadable') {
                $downloadableLinks = Mage::getModel('downloadable/link_purchased')
                        ->getCollection()
                        ->addFieldToFilter('order_item_id', $item->getItemId());
                foreach ($downloadableLinks->getItems() as $link) {
                    $link->setCustomerId($customerId);
                    $link->save();
                }
            }
        }
        
        return $this;
    }

    public function isGuestOrder()
    {
        return $this->getCustomerIsGuest();
    }

    /**
     * History of Owner Switch
     *
     * @return Infobeans_Switchowner_Model_Mysql4_History_Collection
     */
    public function getOwnerSwitchHistory()
    {
        if (!$this->_ownerswitchHistoryCollection) {
            /** @var $collection  Infobeans_Switchowner_Model_Mysql4_History_Collection */
            $collection = Mage::getModel('switchowner/history')->getCollection();
            $collection
                ->addFieldToFilter('order_id', $this->getId())
                ->setOrder('assign_time', 'asc');

            $this->_ownerswitchHistoryCollection = $collection;
        }

        return $this->_ownerswitchHistoryCollection;
    }

    /**
     * Has Owner Switch History
     *
     * @return bool
     */
    public function hasOwnerSwitchHistory()
    {
        return !!$this->getOwnerSwitchHistory()->getSize();
    }
}