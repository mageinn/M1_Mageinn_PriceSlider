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
 * Layer category filter
 *
 * @author      Mageinn
 * @package     Mageinn_PriceSlider
 * @category    Mageinn
 */
class Mageinn_PriceSlider_Model_Catalog_Layer_Filter_Category extends Mage_Catalog_Model_Layer_Filter_Category
{
    protected $_activeItems = null;
    
    /**
     * Apply category filter to layer
     *
     * @param   Zend_Controller_Request_Abstract $request
     * @param   Mage_Core_Block_Abstract $filterBlock
     * @return  Mage_Catalog_Model_Layer_Filter_Category
     */
    public function apply(Zend_Controller_Request_Abstract $request, $filterBlock)
    {
        if(!Mage::helper('mageinn_priceslider')->isMultiselect()) {
            return parent::apply($request, $filterBlock);
        }
        
        $filter = $request->getParam($this->getRequestVar());
        if (!$filter) {
            return $this;
        }
        $categoryIds = explode(",", $filter);
        foreach($categoryIds as $cId) {
            if(!empty($cId)) {
                $this->_categoryId[] = (int) $cId;
            }
        }

        Mage::register('current_category_filter', $this->getCategory(), true);
        $this->_activeItems = $this->_getActiveItemsData($request);

        $this->getLayer()->getProductCollection()
                    ->addCategoryArrayFilter($this->_categoryId);
        
        if(!Mage::helper('mageinn_priceslider')->isHideActive()) {
            foreach($this->_categoryId as $catId) {
                $catId = (int) $catId;
                $this->_appliedCategory = Mage::getModel('catalog/category')
                    ->setStoreId(Mage::app()->getStore()->getId())
                    ->load($catId);

                if ($this->_isValidCategory($this->_appliedCategory)) {
                    $this->getLayer()->getState()->addFilter(
                        $this->_createItem($this->_appliedCategory->getName(), $filter)
                    );
                }
            }
        }

        return $this;
    }
    
    /**
     * Get fiter items count
     *
     * @return int
     */
    public function getItemsCount() 
    {
        if(!Mage::helper('mageinn_priceslider')->isMultiselect()) {
            return parent::getItemsCount();
        }
        
        if(!is_null($this->_activeItems)) {
            return count($this->_activeItems);
        } else {

            return count($this->getItems());
        }
    }
    
    /**
     * Get all filter items
     *
     * @return array
     */
    public function getItems()
    {
        if(!Mage::helper('mageinn_priceslider')->isMultiselect()) {
            return parent::getItems();
        }
        
        $items = array();
        if (!is_null($this->_activeItems)) {
            $items = $this->_activeItems;
        } else {
            if (is_null($this->_items)) {
                $this->_initItems();
            }
            $items = $this->_items;
        }
        
        $request = Mage::app()->getRequest();
        
        foreach($items as $item) {
            $item->setMultiFlag(1);
            $item->setOnUrl($this->_getOnUrl($request, $item->getValue()));
            $item->setOffUrl($this->_getOffUrl($request, $item->getValue()));
            $item->setChecked($this->_getCheckedState($request, $item->getValue()));
        }
        
        return $items;
    }

    /**
     * Get selected category object
     *
     * @return Mage_Catalog_Model_Category
     */
    public function getCategory()
    {
        if(!Mage::helper('mageinn_priceslider')->isMultiselect()) {
            return parent::getCategory();
        }
        
        return $this->getLayer()->getCurrentCategory();
    }
    
    
    /**
     * Get data array for building attribute filter items
     *
     * @return array
     */
    protected function _getActiveItemsData()
    {
        $key = $this->getLayer()->getStateKey().'_SUBCATEGORIES' . '_ACTIVE';
        $data = $this->getLayer()->getAggregator()->getCacheData($key);

        if ($data === null) {
            $data = array();
            
            $category   = $this->getCategory();
                
            /** @var $categoty Mage_Catalog_Model_Categeory */
            $categories = $category->getChildrenCategories();
             
            $this->getLayer()->getProductCollection()
                    ->addCountToCategories($categories);

            foreach ($categories as $category) {
                if ($category->getIsActive() && $category->getProductCount()) {
                    $item = new Varien_Object();
                    $item->setData(array(
                        'label' => Mage::helper('core')->escapeHtml($category->getName()),
                        'value' => $category->getId(),
                        'count' => $category->getProductCount()
                    ));
                    $data[] = $item;
                }
            }
            
            $tags = $this->getLayer()->getStateTags();
            $this->getLayer()->getAggregator()->saveCacheData($data, $key, $tags);
        }
        return $data;
    }
    
    /**
     * Get filter item ON url
     *
     * @param  Zend_Controller_Request_Abstract $request
     * @param  string $value
     * @return string
     */
    protected function _getOnUrl($request, $value)
    {
        $filter = $request->getParam($this->_requestVar);
        $optionIds = explode(",", $filter);
        $optionIds[] = $value;
        $result = implode(",", array_unique($optionIds));
        
        $query = array(
            $this->getRequestVar()=>$result,
            Mage::getBlockSingleton('page/html_pager')->getPageVarName() => null // exclude current page from urls
        );
        return Mage::getUrl('*/*/*', array('_current'=>true, '_use_rewrite'=>true, '_query'=>$query));
    }
    
    /**
     * Get filter item OFF url
     *
     * @param  Zend_Controller_Request_Abstract $request
     * @param  string $value
     * @return string
     */
    protected function _getOffUrl($request, $value)
    {
        $filter = $request->getParam($this->_requestVar); 
        $optionIds = explode(",", $filter);
        if(($key = array_search($value, $optionIds)) !== false) {
            unset($optionIds[$key]);
        }
        $result = implode(",", array_unique($optionIds));
        $query = array(
            Mage::getBlockSingleton('page/html_pager')->getPageVarName() => null // exclude current page from urls
        );
        
        if(!empty($result)) {
            $query[$this->getRequestVar()] = $result;
        } else {
            $query[$this->getRequestVar()] = null;
        }
        
        return Mage::getUrl('*/*/*', array('_current'=>true, '_use_rewrite'=>true, '_query'=>$query));
    }
    
    /**
     * 
     * @param Zend_Controller_Request_Abstract $request
     * @param  string $value
     * @return string
     */
    protected function _getCheckedState($request, $value)
    {
        $filter = $request->getParam($this->_requestVar);
        $optionIds = explode(",", $filter);
        if(($key = array_search($value, $optionIds)) !== false) {
            return true;
        }
        return false;
    }
}
