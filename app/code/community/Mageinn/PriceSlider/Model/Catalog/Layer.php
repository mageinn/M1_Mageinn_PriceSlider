<?php
/**
 * Mageinn_PriceSlider extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category    Mageinn
 * @package     Mageinn_PriceSlider
 * @copyright   Copyright (c) 2019 Mageinn. (http://mageinn.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

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
     * @return array|Mage_Catalog_Model_Resource_Eav_Mysql4_Attribute_Collection|Mage_Catalog_Model_Resource_Product_Attribute_Collection
     */
    public function getFilterableAttributes()
    {
        if (!Mage::helper('mageinn_priceslider')->isEnabled()) {
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
