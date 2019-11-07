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
 * Ajax Block
 *
 * @category   Mageinn
 * @package    Mageinn_PriceSlider
 * @author     Mageinn
 */
class Mageinn_PriceSlider_Block_Ajax extends Mage_Core_Block_Template
{
    /**
     *
     * @return string
     */
    public function getCallbackJs()
    {
        return Mage::helper('mageinn_priceslider')->getCallback('mageinn_priceslider');
    }

    /**
     * Get current url
     *
     * @param array $query url parameters
     * @return string current url
     */
    public function getCurrentUrl()
    {
        return Mage::helper('mageinn_priceslider')->getCurrentUrl();
    }
}