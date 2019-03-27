<?php
/**
 * Catalog view layer model
 *
 * @author      Mageinn
 * @package     Mageinn_PriceSlider
 * @category    Mageinn
 */
class Mageinn_PriceSlider_Model_Catalog_Layer extends Mage_Catalog_Model_Layer
{
    /**
     * Get collection of all filterable attributes for layer products set
     *
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Attribute_Collection
     */
    public function getFilterableAttributes()
    {
        if(!Mage::helper('mageinn_priceslider')->isEnabled()) {
            return parent::getFilterableAttributes();
        }

        $setIds = $this->_getSetIds();
        if (!$setIds) {
            return array();
        }
        /** @var $collection Mage_Catalog_Model_Resource_Product_Attribute_Collection */
        $collection = Mage::getResourceModel('catalog/product_attribute_collection');
        $collection
            ->setItemObjectClass('catalog/resource_eav_attribute')
            ->setAttributeSetFilter($setIds)
            ->addFieldToFilter('attribute_code', array('neq' => 'price'))
            ->addStoreLabel(Mage::app()->getStore()->getId())
            ->setOrder('position', 'ASC');
        $collection = $this->_prepareAttributeCollection($collection);
        $collection->load();

        // Move price to the bottom
        $price = Mage::getModel('eav/entity_attribute')
            ->loadByCode('catalog_product', 'price');

        $collection->addItem($price);

        return $collection;
    }
}
