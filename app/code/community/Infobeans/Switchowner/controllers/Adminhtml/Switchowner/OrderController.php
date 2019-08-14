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

class Infobeans_Switchowner_Adminhtml_Switchowner_OrderController extends Mage_Adminhtml_Controller_Action
{

    /**
     * Acl check for admin
     *
     * @return bool
     */
    protected function _isAllowed() 
    {
        return $this->_helper()->isAllowed();
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

    protected function _initAction() 
    {
        $this->loadLayout()
                ->_setActiveMenu('sales/order')
                ->_addBreadcrumb($this->_helper()->__('Switch Order Owner'), $this->_helper()->__('Switch Order Owner'));

        return $this;
    }

    public function getCustomerDataAction() 
    {
        $data = $this->getRequest()->getParams();
        $storeId = $data['storeid'];
        $websiteId = $data['websiteid'];
        $users = mage::getModel('customer/customer')->getCollection()
                ->addAttributeToSelect('email')
                ->addAttributeToSelect('firstname')
                ->addAttributeToSelect('middlename')
                ->addAttributeToSelect('lastname')
                ->addAttributeToSort('email', 'ASC');

        if ($storeId != "") {
            $users->addAttributeToFilter('store_id', $storeId);
        } else if ($websiteId != "") {
            $users->addAttributeToFilter('website_id', $websiteId);
        }

        $overwriteAddress = Mage::getStoreConfig('switchowner/address/override_billing_shipping');

        foreach ($users as $key => $value) {
            $customerData = Mage::getModel('customer/customer')->load($value->getId());

            //getting default billing address
            $customerBillingAddressId = $customerData->getDefaultBilling();

            //getting default shipping address
            $customerShippingAddressId = $customerData->getDefaultShipping();

            if (($customerBillingAddressId == "" || $customerShippingAddressId == "") && $overwriteAddress == 1) {
                $users->removeItemByKey($key);
            }
        }

        $customerOptions = "<select onchange='setCustomerId()' name='customer-list' id='customer-list' class='chosen-select' required style='widht:200px !important;' tabindex='2'><option value=''>Select Customer</option>";
        foreach ($users as $user) {
            $customerOptions = $customerOptions . '<option value="' . $user->getId() . '">' . $user->getEmail() .' ( '.$user->getFirstname().' '.$user->getMiddlename().' '.$user->getLastname().' )</option>';
        }

        $customerOptions = $customerOptions . "</select>";

        $this->getResponse()->clearHeaders()->setHeader('Content-type', 'application/json', true);
        $this->getResponse()->setBody(json_encode($customerOptions));
    }

    public function switchOwnerAction() 
    {
        $customerId = $this->getRequest()->getPost('customer_id');
        $sendEmail = $this->getRequest()->getPost('send_email') ? 1 : 0;
        $overwriteName = Mage::getStoreConfig('switchowner/address/override_name');
        $orderIds = $this->getRequest()->getParam('order_ids');
        $orderIds = explode(",", $orderIds);
        $overwriteAddress = Mage::getStoreConfig('switchowner/address/override_billing_shipping');
        $success = 0;
        $error = 0;
        $websiteError = 0;
        $websiteErrorMsg = "";
        $orderStatusError = 0;
        $orderStatusErrorMsg = "";
        $acountSharingOption =  Mage::getStoreConfig('customer/account_share/scope');
        $state = explode(",", Mage::getStoreConfig('switchowner/general/orderstate'));
        if ($customerId) {
            foreach ($orderIds as $orderId) {
                $canOwnerSwitch = 1;
                if ($customerId && $orderId) {
                    $order = Mage::helper('switchowner/order')->getOrder($orderId);
                    $orderState = $order->getStatus();
                    $customer = Mage::getModel('customer/customer')->load($customerId);
                    $newCustomerWebsiteId = $customer->getWebsiteId();
                    $oldCustomerWebsiteId = Mage::getModel('core/store')->load($order->getStoreId())->getWebsiteId();
                    if($acountSharingOption == 1 && $newCustomerWebsiteId != $oldCustomerWebsiteId) {
                        $canOwnerSwitch = 0;
                    }

                    if (!in_array($orderState, $state) && $canOwnerSwitch == 1) {
                        $order->switchOwner($customerId, $overwriteName, $sendEmail, $overwriteAddress);
                        $success++;
                    } else if ($canOwnerSwitch == 0) {
                        $websiteError++;
                        $websiteErrorMsg = $websiteErrorMsg.$order->getIncrementId().", ";
                    } else if (in_array($orderState, $state)) {
                        $orderStatusError++;
                        $orderStatusErrorMsg = $orderStatusErrorMsg.$order->getIncrementId().", ";
                    } else {
                        $error++;
                    }
                } else {
                    $error++;
                }
            }

            if (count($orderIds) > 1) {
                if ($success) {
                    $this->_getSession()->addSuccess($this->_helper()->__("%s Order owner were successfully switched.", $success));
                }

                if($websiteError) {
                    $this->_getSession()->addError($this->_helper()->__("Order owner can not be switched for %s as the selected customer belongs to the different website.", rtrim($websiteErrorMsg, ", ")));
                }

                if($orderStatusError) {
                    $this->_getSession()->addError($this->_helper()->__("Order owner can not be switched for %s as it can not be processed further.", rtrim($orderStatusErrorMsg, ", ")));
                }

                if ($error) {
                    $this->_getSession()->addError($this->_helper()->__("%s Order were not be updated due to some error.", $error));
                }

                $this->_redirect('adminhtml/sales_order/index');
            } else {
                $referer = Mage::helper('core/http')->getHttpReferer();
                        
                if ($success) {
                    $this->_getSession()->addSuccess($this->_helper()->__("Order owner was successfully switched."));
                }

                if($websiteError) {
                    $this->_getSession()->addError($this->_helper()->__("Order %s owner can not be switched as the selected customer belongs to the different website.", rtrim($websiteErrorMsg, ", ")));
                }

                if($orderStatusError) {
                    $this->_getSession()->addError($this->_helper()->__("Order %s owner can not be switched as it can not be processed further.", rtrim($orderStatusErrorMsg, ", ")));
                }

                if ($error) {
                    $this->_getSession()->addError($this->_helper()->__("Order was not be updated due to some error."));
                }
                
                $this->getResponse()->setRedirect($referer);
                return $this;
            }
        } else {
            $this->_getSession()->addError($this->_helper()->__("Some data was missed or your session was expired. Please try again."));
            if ($orderId = $this->getRequest()->getParam('order_id')) {
                $this->_redirect('adminhtml/sales_order/view', array('order_id' => $orderId));
            } else {
                $this->_redirect('adminhtml/sales_order/index');
            }
        }

        return;
    }
}