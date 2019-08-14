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

class Infobeans_Switchowner_Model_Mysql4_Detail extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('switchowner/detail', 'detail_id');
    }
}