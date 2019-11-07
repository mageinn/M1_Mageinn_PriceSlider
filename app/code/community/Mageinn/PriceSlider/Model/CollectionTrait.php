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
 * Catalog and CatalogSearch collection trait
 *
 * @category    Mageinn
 * @package     Mageinn_PriceSlider
 * @author      Mageinn
 */
trait Mageinn_PriceSlider_Model_Collection_CollectionTrait
{
    /**
     * Specify category filter for product collection
     *
     * @param array $categories
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function addCategoryArrayFilter($categories)
    {
        $this->_productLimitationFilters['category_id'] = $categories;
        $this->_productLimitationFilters['category_is_anchor'] = 1;
        if ($this->getStoreId() == Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID) {
            $this->_applyZeroStoreProductLimitations();
        } else {
            $this->_applyProductLimitations();
        }
    }

    /**
     * Apply limitation filters to collection
     * Method allows using one time category product index table (or product website table)
     * for different combinations of store_id/category_id/visibility filter states
     * Method supports multiple changes in one collection object for this parameters
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    protected function _applyProductLimitations()
    {
        Mage::dispatchEvent('catalog_product_collection_apply_limitations_before', array(
            'collection'  => $this,
            'category_id' => isset($this->_productLimitationFilters['category_id'])
                ? $this->_productLimitationFilters['category_id']
                : null,
        ));
        $this->_prepareProductLimitationFilters();
        $this->_productLimitationJoinWebsite();
        $this->_productLimitationJoinPrice();
        $filters = $this->_productLimitationFilters;

        if (!isset($filters['category_id']) && !isset($filters['visibility'])) {
            return $this;
        }

        $conditions = array(
            'cat_index.product_id=e.entity_id',
            $this->getConnection()->quoteInto('cat_index.store_id=?', $filters['store_id'])
        );
        if (isset($filters['visibility']) && !isset($filters['store_table'])) {
            $conditions[] = $this->getConnection()
                ->quoteInto('cat_index.visibility IN(?)', $filters['visibility']);
        }

        if (!$this->getFlag('disable_root_category_filter')) {
            if(is_array($filters['category_id'])) {
                $conditions[] = $this->getConnection()
                    ->quoteInto('cat_index.category_id IN(?)', $filters['category_id']);
            } else {
                $conditions[] = $this->getConnection()
                    ->quoteInto('cat_index.category_id = ?', $filters['category_id']);
            }
        }

        if (isset($filters['category_is_anchor'])) {
            $conditions[] = $this->getConnection()
                ->quoteInto('cat_index.is_parent=?', $filters['category_is_anchor']);
        }

        $joinCond = join(' AND ', $conditions);
        $fromPart = $this->getSelect()->getPart(Zend_Db_Select::FROM);
        if (isset($fromPart['cat_index'])) {
            $fromPart['cat_index']['joinCondition'] = $joinCond;
            $this->getSelect()->setPart(Zend_Db_Select::FROM, $fromPart);
        }
        else {
            $this->getSelect()->join(
                array('cat_index' => $this->getTable('catalog/category_product_index')),
                $joinCond,
                array('cat_index_position' => 'position')
            );
        }

        $this->_productLimitationJoinStore();

        Mage::dispatchEvent('catalog_product_collection_apply_limitations_after', array(
            'collection' => $this
        ));

        return $this;
    }

    /**
     * Apply limitation filters to collection base on API
     * Method allows using one time category product table
     * for combinations of category_id filter states
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    protected function _applyZeroStoreProductLimitations()
    {
        $filters = $this->_productLimitationFilters;

        if(is_array($filters['category_id'])) {
            $conditions = array(
                'cat_pro.product_id=e.entity_id',
                $this->getConnection()->quoteInto('cat_pro.category_id IN(?)', $filters['category_id'])
            );
        } else {
            $conditions = array(
                'cat_pro.product_id=e.entity_id',
                $this->getConnection()->quoteInto('cat_pro.category_id=?', $filters['category_id'])
            );
        }
        $joinCond = join(' AND ', $conditions);

        $fromPart = $this->getSelect()->getPart(Zend_Db_Select::FROM);
        if (isset($fromPart['cat_pro'])) {
            $fromPart['cat_pro']['joinCondition'] = $joinCond;
            $this->getSelect()->setPart(Zend_Db_Select::FROM, $fromPart);
        }
        else {
            $this->getSelect()->join(
                array('cat_pro' => $this->getTable('catalog/category_product')),
                $joinCond,
                array('cat_index_position' => 'position')
            );
        }
        $this->_joinFields['position'] = array(
            'table' => 'cat_pro',
            'field' => 'position',
        );

        return $this;
    }
}
