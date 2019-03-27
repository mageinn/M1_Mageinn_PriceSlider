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
 * Ajax Block
 *
 * @category   Mageinn
 * @package    Mageinn_PriceSlider
 * @author     Mageinn
 */
class Mageinn_PriceSlider_Block_Price extends Mage_Core_Block_Template
{
    protected $_toPrice;
    protected $_fromPrice;
    protected $_currFromPrice;
    protected $_currToPrice;
    protected $_isEnabled;
    protected $_layer;

    
    public function __construct()
    {
        $this->init();
        $this->setPrices();
        parent::__construct();
    }
    
    /**
     * Set layer depending on the page
     * 
     * @return  Mageinn_PriceSlider_Block_Price
     */
    public function init()
    {
        $attribute = Mage::getModel('eav/entity_attribute')
                ->loadByCode('catalog_product', 'price');
        $_category = Mage::registry('current_category');
        if($_category) {
            $this->_isEnabled = $attribute->getIsFilterable();
            if(!$this->_isEnabled){
                return $this;
            }
            $this->_layer = Mage::getSingleton('catalog/layer');
        } else {
            $this->_isEnabled =  $attribute->getIsFilterableInSearch();
            if(!$this->_isEnabled){
                return $this;
            }

            $this->_layer = Mage::getSingleton('catalogsearch/layer');
        }
             
        return $this;
    }
    
    /*
    * Set prices
     * 
    * @return  Mageinn_PriceSlider_Block_Price
    */
    public function setPrices()
    {
        // Get selected prices
        $priceRange = $this->getRequest()->getParam('price');
        
        if($priceRange) {
            $filterParams = explode('-', $this->getRequest()->getParam('price'));
            
            $this->_currFromPrice   = $filterParams[0];
            $this->_currToPrice     = $filterParams[1];
        }
        
        return $this;
    }

    /*
    * 
    * @return int
    */
    public function getCurrFromPrice()
    {
        if($this->_currFromPrice > 0) {
            $min = $this->_currFromPrice;
        } else {
            $min = $this->getFromPrice();
        }
        return $min;
    }

    /*
    * 
    * @return int
    */
    public function getCurrToPrice()
    {
        if($this->_currToPrice > 0) {
            $max = $this->_currToPrice;
        } else{
            $max = $this->getToPrice();
        }
        return $max;
    }
    
    /**
     * Get the actual From price
     * @return number
     */
    public function getFromPrice()
    {
        if($this->_layer->getMinPrice()) {
            return floor($this->_layer->getMinPrice());
        } else {
            return floor($this->_layer->getProductCollection()->getMinPrice());
        }
    }
    
    /**
     * Get the actual To price
     * @return number
     */
    public function getToPrice()
    {
        if($this->_layer->getMaxPrice()) {
            return floor($this->_layer->getMaxPrice());
        } else {
            return floor($this->_layer->getProductCollection()->getMaxPrice());
        }
    }
    
    /**
     * 
     * @return string
     */
    public function getCurrencyPattern()
    {
        $full = explode(".", Mage::app()->getStore()->formatPrice(123, false));
        return $full[0];
    }
    
    /**
     *
     * @return int
     */
    public function getStep()
    {
        return Mage::helper('mageinn_priceslider')->getStep();
    }

    /**
     *
     * @return string
     */
    public function getFormat()
    {
        return Mage::helper('mageinn_priceslider')->getWnumbFormat();
    }
    
    /**
     * Check if price attribute is filterable
     * 
     * @return type
     */
    public function getIsEnabled()
    {
        return $this->_isEnabled;
    }
}