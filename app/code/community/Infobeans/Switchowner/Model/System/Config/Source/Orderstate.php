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

class Infobeans_Switchowner_Model_System_Config_Source_Orderstate
{
    /**
     * Options order status
     *
     * @return array
     */
    public function toOptionArray()
    {
        
        $collection = Mage::getModel('sales/order_status')->getCollection()->joinStates();
        $stateArray = array();
        
        foreach ($collection as $status) {
            $stateArray[] = array('value' => $status->getStatus(), 'label' =>  $status->getLabel());
        }
        
        return $stateArray; 
    }
}