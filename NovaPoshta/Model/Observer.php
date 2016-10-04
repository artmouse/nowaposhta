<?php
class Ak_NovaPoshta_Model_Observer
{
    protected function _getConnection()
    {
        return Mage::getSingleton('core/resource')->getConnection('core_write');
    }

    public function saveShippingMethod($evt){
        $request = $evt->getRequest();
        $quote = $evt->getQuote();

        $city = $request->getParam('novaposhta_city',false);
        $warehouse = $request->getParam('novaposhta_warehouse',false);
        if (strlen($city)==0){
            return false;
        }
        $quote_id = $quote->getId();

        $city_data = array($quote_id => $city);
        $warehouse_data = array($quote_id => $warehouse);

        if($city_data){
            Mage::getSingleton('checkout/session')->setCityNova($city_data);
        }
        if($warehouse_data){
            Mage::getSingleton('checkout/session')->setWahehouseNova($warehouse_data);
        }
    }
    public function saveOrderAfterAll($evt){
        $order = $evt->getOrder();
        $quote = $evt->getQuote();
        $quote_id = $quote->getId();
        $warehouse = Mage::getSingleton('checkout/session')->getWahehouseNova();
        $city = Mage::getSingleton('checkout/session')->getCityNova();
        if(isset($warehouse[$quote_id]) and strlen($warehouse[$quote_id])>0){
            Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
            $city_name = Mage::getResourceModel('novaposhta/city_collection')->cityName($city[$quote_id]);
            $warehouse_name = Mage::getResourceModel('novaposhta/warehouse_collection')->warehouseName($warehouse[$quote_id]);
            $warehouse_label = $city_name.' - '.$warehouse_name;
            $data['address_id'] = $order->getId();
            $data['warehouse_id'] = $warehouse[$quote_id];
            $data['warehouse_label'] = $warehouse_label;
            $tableName  = Mage::getSingleton('core/resource')->getTableName('novaposhta_order_address');
            $connection = $this->_getConnection();
            $connection->beginTransaction();
            try {
                $connection->insertOnDuplicate($tableName, $data);
                $connection->commit();
            } catch (Exception $e) {
                $connection->rollBack();
                throw $e;
            }
            return true;
        }
    }

    public function getSalesOrderViewInfo(Varien_Event_Observer $observer) {
        $block = $observer->getBlock();
        // layout name should be same as used in app/design/adminhtml/default/default/layout/mymodule.xml
        if (($block->getNameInLayout() == 'order_info') && ($child = $block->getChild('novaposhta.order.info.custom.block'))) {
            $transport = $observer->getTransport();
            if ($transport) {
                $html = $transport->getHtml();
                $html .= $child->toHtml();
                $transport->setHtml($html);
            }
        }
    }
}