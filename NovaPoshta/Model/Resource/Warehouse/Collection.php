<?php
class Ak_NovaPoshta_Model_Resource_Warehouse_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    public function _construct()
    {
        $this->_init('novaposhta/warehouse');
    }
    public function fetchAll()
    {
        return $this->setOrder('id', Varien_Data_Collection_Db::SORT_ORDER_ASC)->_toOptionArray('id', 'ref');
    }
    public function warehouseName($id)
    {
        $name = $this->getItemById($id)->getData('address_ru');
        return $name;
    }
}
