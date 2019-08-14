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

class Infobeans_Switchowner_Block_Adminhtml_Customer_Info extends Mage_Adminhtml_Block_Template
{
    /**
     * Helper
     *
     * @return Infobeans_Switchowner_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('switchowner');
    }

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('infobeans/switchowner/order/info.phtml');
    }

    public function getSwitchOwnerUrl()
    {
        return $this->getUrl('adminhtml/switchowner_order/switchOwner');
    }

    public function getOrderIds()
    {
        $orderIds = $this->getRequest()->getParam('order_ids');
        if ($orderIds && is_array($orderIds)){
            return implode(",", $orderIds);
        } else {
            return $this->getRequest()->getParam('order_id');
        }
    }

    public function getCustomersData()
    {     
        $actionName = Mage::app()->getFrontController()->getAction()->getFullActionName();
        $users = mage::getModel('customer/customer')->getCollection()
        ->addAttributeToSelect('email')
        ->addAttributeToSelect('firstname')
        ->addAttributeToSelect('middlename')
        ->addAttributeToSelect('lastname')
        ->addAttributeToSort('email', 'ASC');
        
        $orderId = $this->getRequest()->getParam('order_id');
        $websiteId = "";
        $accountSharingOption =  Mage::getStoreConfig('customer/account_share/scope');
        if ($orderId != "" && $accountSharingOption == 1 && $actionName == 'adminhtml_sales_order_view') {
            $order = Mage::getModel('sales/order')->load($orderId);
            $websiteId = Mage::getModel('core/store')->load($order->getStoreId())->getWebsiteId();
            if ($websiteId != "") {
                $users->addAttributeToFilter('website_id', $websiteId);
            }
        }
        
        $overwriteAddress = Mage::getStoreConfig('switchowner/address/override_billing_shipping');
        foreach ($users as $key => $value) {
            $customerData = Mage::getModel('customer/customer')->load($value->getId());
            //getting default billing address
            $customerBillingAddressId =  $customerData->getDefaultBilling();
            //getting default shipping address
            $customerShippingAddressId = $customerData->getDefaultShipping();
            if (($customerBillingAddressId == "" || $customerShippingAddressId == "") && $overwriteAddress == 1 ) {
                $users->removeItemByKey($key);
            }
        }
        
        $customers = array();
        foreach ($users as $user) {
            $customers[$user->getId()] = $user->getEmail()." ( ".$user->getFirstname()." ".$user->getMiddlename()." ".$user->getLastname()." )";
        }
        
        return $customers;
    }
    
    public function getWebsitesData()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $websiteId = "";
        $actionName = Mage::app()->getFrontController()->getAction()->getFullActionName();
        $accountSharingOption =  Mage::getStoreConfig('customer/account_share/scope');
        if ($orderId != "" && $accountSharingOption == 1  && $actionName == 'adminhtml_sales_order_view') {
            $order = Mage::getModel('sales/order')->load($orderId);
            $websiteId = Mage::getModel('core/store')->load($order->getStoreId())->getWebsiteId();
        }
        
        $storeModel = Mage::getSingleton('adminhtml/system_store');
        /* @var $storeModel Mage_Adminhtml_Model_System_Store */
        $websiteCollection = $storeModel->getWebsiteCollection();
        $options = array();

        if ($websiteId != "") {
            $websiteCollection = Mage::getResourceModel('core/website_collection')->load();
            foreach ($websiteCollection as $key => $value) {
                if ($websiteId != $value->getId()) {
                    $websiteCollection->removeItemByKey($key);
                }
            }
        }
        
        foreach ($websiteCollection as $website) {
            $websiteShow = false;
            foreach ($storeModel->getGroupCollection() as $group) {
                if ($group->getWebsiteId() != $website->getId()) {
                    continue;
                }
                $groupShow = false;
                foreach ($storeModel->getStoreCollection() as $store) {
                    if ($store->getGroupId() != $group->getId()) {
                        continue;
                    }
                    if (!$websiteShow) {
                        $websiteShow = true;
                        $options['website_' . $website->getCode()] = array(
                            'label'    => $website->getName(),
                            'id'    => 'website_'.$website->getId(),
                            'style'    => 'padding-left:6px; background:#DDD; font-weight:bold;',
                        );
                    }
                    if (!$groupShow) {
                        $groupShow = true;
                        $options['group_' . $group->getId() . '_open'] = array(
                            'is_group'  => true,
                            'is_close'  => false,
                            'label'     => $group->getName(),
                            'id'    => 'group_'.$group->getId(),
                            'style'     => 'padding-left:32px;'
                        );
                    }
                    $options['store_' . $store->getCode()] = array(
                        'label'    => $store->getName(),
                        'id'    => 'store_'.$store->getId(),
                        'style'    => '',
                    );
                }
                if ($groupShow) {
                    $options['group_' . $group->getId() . '_close'] = array(
                        'is_group'  => true,
                        'is_close'  => true,
                    );
                }
            }
        }
        return $options;
    }
}