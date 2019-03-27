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
 * @copyright   Copyright (c) 2016 Mageinn. (http://mageinn.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * This class is required for common functions getting used around this module.
 * 
 * @author      Mageinn
 * @package     Mageinn_PriceSlider
 * @category    Mageinn
 */
class Mageinn_PriceSlider_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_MAGEINN_SLIDER_ENABLED   = 'mageinn_priceslider/general/enabled';
    const XML_PATH_MAGEINN_AJAX_MULTI       = 'mageinn_priceslider/general/multiselect';
    const XML_PATH_MAGEINN_AJAX_HIDEACTIVE  = 'mageinn_priceslider/general/hide_active';
    const XML_PATH_MAGEINN_AJAX_LAYERED     = 'mageinn_priceslider/general/layered';
    const XML_PATH_MAGEINN_AJAX_TOOLBAR     = 'mageinn_priceslider/general/toolbar';
    const XML_PATH_MAGEINN_AJAX_CALLBACK    = 'mageinn_priceslider/general/callback';
    const XML_PATH_MAGEINN_AJAX_RWDCALLBACK = 'mageinn_priceslider/general/rwdcallback';
    const XML_PATH_MAGEINN_SLIDER_STEP      = 'mageinn_priceslider/general/step';
    const XML_PATH_MAGEINN_SLIDER_WNUMB     = 'mageinn_priceslider/general/wnumb';
    
    /**
     * Checks if the module is enabled
     * 
     * @return bool 
     */
    public function isEnabled() 
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_MAGEINN_SLIDER_ENABLED);
    }
    
    /**
     * Checks if multiple select mode is enabled
     * 
     * @return bool 
     */
    public function isMultiselect() 
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_MAGEINN_AJAX_MULTI);
    }
    
    /**
     * Checks if active filters should be hidden
     * 
     * @return bool 
     */
    public function isHideActive() 
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_MAGEINN_AJAX_HIDEACTIVE);
    }
    
    /**
     * Checks if AJAX is enabled for Layered Navigation
     * 
     * @return bool 
     */
    public function isAjaxLayered() 
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_MAGEINN_AJAX_LAYERED);
    }
    
    /**
     * Checks if AJAX is enabled for toolbar
     * 
     * @return bool 
     */
    public function isAjaxToolbar() 
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_MAGEINN_SLIDER_ENABLED);
    }
    
    /**
     * Get AJAX callback
     * 
     * @return bool 
     */
    public function getCallback() 
    {
        return Mage::getStoreConfig(self::XML_PATH_MAGEINN_AJAX_CALLBACK);
    }
    
    /**
     * Checks if RWD callback is enabled 
     * 
     * @return bool 
     */
    public function isRwdCallback() 
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_MAGEINN_AJAX_RWDCALLBACK);
    }
    
    /**
     * Get Slider Step
     * 
     * @return int 
     */
    public function getStep() 
    {
        return (int) Mage::getStoreConfig(self::XML_PATH_MAGEINN_SLIDER_STEP);
    }

    /**
     * Get wNumb format
     *
     * @return string
     */
    public function getWnumbFormat()
    {
        return "{" . Mage::getStoreConfig(self::XML_PATH_MAGEINN_SLIDER_WNUMB) . "}";
    }
    
    /**
     * Get current url
     *
     * @param array $query url parameters
     * @return string current url
     */
    public function getCurrentUrl($query = array('price' => '_price_'))
    {
        $params['_query'] = $query;
        $params['_current'] = true;
        $params['_use_rewrite'] = true;
        return Mage::getUrl('*/*/*', $params);
    }
}
	 