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
 * Mageinn_PriceSlider_Model_Catalog_Resource_Layer_Filter_Price
 *
 * @author      Mageinn
 * @package     Mageinn_PriceSlider
 * @category    Mageinn
 */
class Mageinn_PriceSlider_Model_Catalog_Resource_Layer_Filter_Price extends Mage_Catalog_Model_Resource_Layer_Filter_Price
{
    /**
     * Apply price range filter to product collection
     *
     * @param Mage_Catalog_Model_Layer_Filter_Price $filter
     * @return Mage_Catalog_Model_Resource_Layer_Filter_Price
     */
    public function applyPriceRange($filter)
    {
        if (!Mage::helper('mageinn_priceslider')->isEnabled()) {
            return parent::applyPriceRange($filter);
        }

        $interval = $filter->getInterval();
        if (!$interval) {
            return $this;
        }

        list($from, $to) = $interval;
        if ($from === '' && $to === '') {
            return $this;
        }

        $select = $filter->getLayer()->getProductCollection()->getSelect();
        $priceExpr = $this->_getPriceExpression($filter, $select, false);

        if ($to !== '') {
            $to = (float)$to;
            $to += self::MIN_POSSIBLE_PRICE;
        }

        $filter->getLayer()->setMinPrice($filter->getLayer()->getProductCollection()->getMinPrice());
        $filter->getLayer()->setMaxPrice($filter->getLayer()->getProductCollection()->getMaxPrice());

        if ($from !== '') {
            $select->where($priceExpr . ' >= ' . $this->_getComparingValue($from, $filter));
        }

        if ($to !== '') {
            $select->where($priceExpr . ' < ' . $this->_getComparingValue($to, $filter));
        }

        return $this;
    }

    /**
     * Get comparing value sql part
     *
     * @param float $price
     * @param Mage_Catalog_Model_Layer_Filter_Price $filter
     * @param bool $decrease
     * @return float
     */
    protected function _getComparingValue($price, $filter, $decrease = true)
    {
        if (!Mage::helper('mageinn_priceslider')->isEnabled()) {
            return parent::_getComparingValue($price, $filter, $decrease);
        }

        $currencyRate = $filter->getLayer()->getProductCollection()->getCurrencyRate();
        $result = $price / $currencyRate;
        return sprintf('%F', $result);
    }
}
