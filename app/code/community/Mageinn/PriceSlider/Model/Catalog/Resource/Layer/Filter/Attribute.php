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
 * Mageinn_PriceSlider_Model_Catalog_Resource_Layer_Filter_Attribute
 *
 * @author      Mageinn
 * @package     Mageinn_PriceSlider
 * @category    Mageinn
 */
class Mageinn_PriceSlider_Model_Catalog_Resource_Layer_Filter_Attribute extends Mage_Catalog_Model_Resource_Layer_Filter_Attribute
{
    /**
     * Apply attribute filter to product collection
     *
     * @param Mage_Catalog_Model_Layer_Filter_Attribute $filter
     * @param int $value
     * @return Mage_Catalog_Model_Resource_Layer_Filter_Attribute
     */
    public function applyFilterToCollection($filter, $value)
    {
        $collection = $filter->getLayer()->getProductCollection();
        $attribute = $filter->getAttributeModel();
        $connection = $this->_getReadAdapter();
        $tableAlias = $attribute->getAttributeCode() . '_idx';

        $values = explode(",", $value);
        $cleaned = array();
        foreach ($values as $v) {
            $v = (int)$v;
            if ($v) {
                $cleaned[] = $v;
            }
        }

        $conditions = array(
            "{$tableAlias}.entity_id = e.entity_id",
            $connection->quoteInto("{$tableAlias}.attribute_id = ?", $attribute->getAttributeId()),
            $connection->quoteInto("{$tableAlias}.store_id = ?", $collection->getStoreId()),
            "{$tableAlias}.value IN (" . implode(",", $cleaned) . ")"
        );

        $collection->getSelect()->join(
            array($tableAlias => $this->getMainTable()),
            implode(' AND ', $conditions),
            array()
        );

        $collection->getSelect()->group('e.entity_id');

        return $this;
    }

    /**
     * Retrieve array with products counts per attribute option
     *
     * @param Mage_Catalog_Model_Layer_Filter_Attribute $filter
     * @return array
     */
    public function getCount($filter)
    {
        // clone select from collection with filters
        $select = clone $filter->getLayer()->getProductCollection()->getSelect();
        // reset columns, order and limitation conditions
        $select->reset(Zend_Db_Select::COLUMNS);
        $select->reset(Zend_Db_Select::ORDER);
        $select->reset(Zend_Db_Select::GROUP);
        $select->reset(Zend_Db_Select::LIMIT_COUNT);
        $select->reset(Zend_Db_Select::LIMIT_OFFSET);

        if (isset($_REQUEST['price'])) {
            $interval = explode('-', $_REQUEST['price']);
            list($from, $to) = $interval;
            $wheres = $select->getPart('where');

            if (!empty($from) && !$this->_searchSubstring('min_price >=', $wheres)) {
                $select->where('price_index.min_price' . ' >= ' . $from);
            }

            if (!empty($to) && !$this->_searchSubstring('min_price <', $wheres)) {
                $select->where('price_index.min_price' . ' < ' . $to);
            }
        }

        $connection = $this->_getReadAdapter();
        $attribute  = $filter->getAttributeModel();
        $tableAlias = sprintf('%s_idx', $attribute->getAttributeCode());
        $conditions = array(
            "{$tableAlias}.entity_id = e.entity_id",
            $connection->quoteInto("{$tableAlias}.attribute_id = ?", $attribute->getAttributeId()),
            $connection->quoteInto("{$tableAlias}.store_id = ?", $filter->getStoreId()),
        );

        $select
            ->join(
                array($tableAlias => $this->getMainTable()),
                join(' AND ', $conditions),
                array('value', 'count' => new Zend_Db_Expr("COUNT(DISTINCT {$tableAlias}.entity_id)")))
            ->group(array("{$tableAlias}.value"));
        return $connection->fetchPairs($select);
    }

    /**
     * @param $keyword
     * @param $array
     * @return bool|int|string
     */
    protected function _searchSubstring($keyword, $array){
        foreach($array as $key => $arrayItem){
            if( stristr( $arrayItem, $keyword ) ){
                return $key;
            }
        }

        return false;
    }
}
