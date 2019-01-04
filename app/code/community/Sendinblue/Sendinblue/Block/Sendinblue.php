<?php
/**
* @author Sendinblue plateform <contact@sendinblue.com>
* @copyright  2013-2014 Sendinblue
* URL:  https:www.sendinblue.com
* Do not edit or add to this file if you wish to upgrade Sendinblue Magento plugin to newer
* versions in the future. If you wish to customize Sendinblue magento plugin for your
* needs then we can't provide a technical support.
**/

class Sendinblue_Sendinblue_Block_Sendinblue extends Mage_Core_Block_Template
{
    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }  
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
    
    protected function _toHtml()
    {
        $sendinblueData = Mage::getModel('sendinblue/sendinblue');
        $getEnableStatus = $sendinblueData->getEnableStatus();  
        $getTrackingStatus = $sendinblueData->getTrackingStatus();
        $getOrderStatus = $sendinblueData->getOrderSmsStatus();
        $sibLists = $sendinblueData->getUserlists();
        $getUserLists = !empty($sibLists) ? array($sibLists) : array();

        $attributesName = $sendinblueData->allAttributesName();
        $afterArrayMerge = array();

        $lastOrderId = Mage::getSingleton('checkout/session')->getLastRealOrderId();
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $order = Mage::getModel('sales/order')->loadByIncrementId($lastOrderId);
        $dataDisplay = $order->getBillingAddress()->getData();
        $orderData = $order->getData();
        $custmerData = $customer->getData();
        $localeCode = Mage::app()->getLocale()->getLocaleCode();
        $referenceNumber = !empty($orderData['increment_id']) ? $orderData['increment_id'] : '';
        $orderprice = !empty($orderData['grand_total']) ? $orderData['grand_total'] : '0';
        $currencyCode = !empty($orderData['base_currency_code']) ? $orderData['base_currency_code'] : '';
        $smsMobile = '';
        if ($getEnableStatus && $getOrderStatus) {
            if (!empty($dataDisplay['telephone']) && !empty($dataDisplay['country_id'])) {
                $countryCode = $sendinblueData->getCountryCode($dataDisplay['country_id']);
                $smsMobile = $sendinblueData->checkMobileNumber($dataDisplay['telephone'], $countryCode);
            }

            $ordCreateDate = !empty($orderData['created_at']) ? $orderData['created_at'] : '';
            if ($localeCode == 'fr_FR') {
                $orderCreatedDate = date('d/m/Y', strtotime($ordCreateDate));
            }
            else {
                $orderCreatedDate = date('m/d/Y', strtotime($ordCreateDate));
            }

            $totalPay = $orderprice.' '.$currencyCode;
            $senderData = $sendinblueData->getSendSmsOrderSubject();
            $msgbody = $sendinblueData->getSendSmsmOrderMessage();
            $firstName = str_replace('{first_name}', $dataDisplay['firstname'], $msgbody);
            $lastName = str_replace('{last_name}', $dataDisplay['lastname']."\r\n", $firstName);
            $procuctPrice = str_replace('{order_price}', $totalPay, $lastName);
            $orderDate = str_replace('{order_date}', $orderCreatedDate."\r\n", $procuctPrice);
            $msgbody = str_replace('{order_reference}', $referenceNumber, $orderDate);

            $sendSmsData = array();
            $sendSmsData['to'] = $smsMobile;
            $sendSmsData['from'] = $senderData;
            $sendSmsData['text'] = $msgbody;
            if (!empty($senderData) && !empty($msgbody) && !empty($smsMobile)) {
                $sendinblueData->sendSmsApi($sendSmsData);
            }
        }

        $allData = array_merge($dataDisplay, $custmerData);
        $afterArrayMerge = $sendinblueData->mergeMyArray($attributesName, $allData);
        $client = (!empty($custmerData['firstname'])|| !empty($custmerData['firstname'])) ? 1 : 0 ;
        
        $afterArrayMerge['CLIENT'] = $client;
        $email = !empty($custmerData['email']) ? $custmerData['email'] : ''; // for email address
        $costomerInformation = Mage::getModel('newsletter/subscriber')->loadByEmail($email);
        $nlStatus = $costomerInformation->getStatus();

        if ($nlStatus == 1 && !empty($email)) {
            $sendinblueData->emailAdd($email, $afterArrayMerge, $nlStatus);
        }

        if ($getEnableStatus == 1 && $getTrackingStatus == 1 && $nlStatus == 1) {
            $valueConfig = $sendinblueData->getApiConfigValue();
            if (isset($valueConfig['data']['date_format']) && $valueConfig['data']['date_format'] == 'dd-mm-yyyy') {
                $date = date('d-m-Y', strtotime($orderData['created_at']));
            }
            else {
                $date = date('m-d-Y', strtotime($orderData['created_at']));
            }

            $fName = !empty($custmerData['firstname']) ? $custmerData['firstname'] : '';
            $lName = !empty($custmerData['lastname']) ? $custmerData['lastname'] : '';
            $attributesValues = array("PRENOM" => $fName, "NOM" => $lName, "ORDER_ID" => $referenceNumber, "ORDER_DATE" => $date, "ORDER_PRICE" => $orderprice);
            $sendinblueData->importTransactionalData($email, $attributesValues, $getUserLists);

        }
    }
}
