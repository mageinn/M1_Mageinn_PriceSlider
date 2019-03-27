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
 * Mageinn_PriceSlider_Model_Catalog_Layer_Filter_Attribute
 * 
 * @author      Mageinn
 * @package     Mageinn_PriceSlider
 * @category    Mageinn
 */
class Mageinn_PriceSlider_Model_Catalog_Layer_Filter_Attribute extends Mage_Catalog_Model_Layer_Filter_Attribute
{
    protected $_activeItems = null;
    
    /**
     * Get option text from frontend model by option id
     *
     * @param   int $optionId
     * @return  string|bool
     */
    protected function _getOptionText($optionId)
    {
        if(!Mage::helper('mageinn_priceslider')->isMultiselect()) {
            return parent::_getOptionText($optionId);
        }
                
        $optionIds = explode(",", $optionId);
        $options = array();
        foreach($optionIds as $oId) {
            if($oId){
                $options[$oId] = $this->getAttributeModel()->getFrontend()->getOption($oId);
            }
        }
        return $options;
    }

    /**
     * Apply attribute option filter to product collection
     *
     * @param   Zend_Controller_Request_Abstract $request
     * @param   Varien_Object $filterBlock
     * @return  Mage_Catalog_Model_Layer_Filter_Attribute
     */
    public function apply(Zend_Controller_Request_Abstract $request, $filterBlock)
    {
        if(!Mage::helper('mageinn_priceslider')->isMultiselect()) {
            return parent::apply($request, $filterBlock);
        }
        
        $filter = $request->getParam($this->_requestVar);
        if (is_array($filter)) {
            return $this;
        }
        $text = $this->_getOptionText($filter);
        if ($filter && (count($text) || strlen($text))) {
            $this->_activeItems = $this->_getActiveItemsData($request);
            $this->_getResource()->applyFilterToCollection($this, $filter);
            if(!Mage::helper('mageinn_priceslider')->isHideActive()) {
                foreach($text as $oId => $t) {
                    if(!empty($t)) {
                        $this->getLayer()->getState()
                                ->addFilter($this->_createItem($t, $oId));
                    }
                }
            }
            $this->_items = array();
        }
        return $this;
    }
    
    /**
     * Create filter item object
     *
     * @param   string $label
     * @param   mixed $value
     * @param   int $count
     * @return  Mage_Catalog_Model_Layer_Filter_Item
     */
    protected function _createItem($label, $value, $count=0)
    {
        $request = Mage::app()->getRequest();
        
        return Mage::getModel('catalog/layer_filter_item')
            ->setFilter($this)
            ->setLabel($label)
            ->setOffUrl($this->_getOffUrl($request, $value))
            ->setValue($value)
            ->setCount($count);
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
     * Get data array for building attribute filter items
     *
     * @return array
     */
    protected function _getActiveItemsData()
    {
        $attribute = $this->getAttributeModel();
        $this->_requestVar = $attribute->getAttributeCode();

        $key = $this->getLayer()->getStateKey().'_active_'.$this->_requestVar;
        $data = $this->getLayer()->getAggregator()->getCacheData($key);

        if ($data === null) {
            $options = $attribute->getFrontend()->getSelectOptions();
            $optionsCount = $this->_getResource()->getCount($this);
            $data = array();
            foreach ($options as $option) {
                if (is_array($option['value'])) {
                    continue;
                }
                if (Mage::helper('core/string')->strlen($option['value'])) {
                    // Check filter type
                    if ($this->_getIsFilterableAttribute($attribute) == self::OPTIONS_ONLY_WITH_RESULTS) {
                        if (!empty($optionsCount[$option['value']])) {
                            $item = new Varien_Object();
                            $item->setData(array(
                                'label' => $option['label'],
                                'value' => $option['value'],
                                'count' => $optionsCount[$option['value']]
                            ));
                            $data[] = $item;
                        }
                    }
                    else {
                        $item = new Varien_Object();
                        $item->setData(array(
                            'label' => $option['label'],
                            'value' => $option['value'],
                            'count' => isset($optionsCount[$option['value']]) ? $optionsCount[$option['value']] : 0
                        ));
                        $data[] = $item;
                    }
                }
            }

            $tags = array(
                Mage_Eav_Model_Entity_Attribute::CACHE_TAG.':'.$attribute->getId()
            );

            $tags = $this->getLayer()->getStateTags($tags);
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

    /**
     * Get data array for building attribute filter items
     *
     * @return array
     */
    protected function _getItemsData1()
    {
        $attribute = $this->getAttributeModel();
        $this->_requestVar = $attribute->getAttributeCode();

        $key = $this->getLayer()->getStateKey().'_'.$this->_requestVar;
        $data = $this->getLayer()->getAggregator()->getCacheData($key);

        if ($data === null) {
            $options = $attribute->getFrontend()->getSelectOptions();
            $optionsCount = $this->_getResource()->getCount($this);
            $data = array();
            foreach ($options as $option) {
                if (is_array($option['value'])) {
                    continue;
                }
                if (Mage::helper('core/string')->strlen($option['value'])) {
                    // Check filter type
                    if ( !Mage::helper('mageinn_priceslider')->isMultiselect() && $this->_getIsFilterableAttribute($attribute) == self::OPTIONS_ONLY_WITH_RESULTS ) {
                        if (!empty($optionsCount[$option['value']])) {
                            $data[] = array(
                                'label' => $option['label'],
                                'value' => $option['value'],
                                'count' => $optionsCount[$option['value']],
                            );
                        }
                    }
                    else {
                        $data[] = array(
                            'label' => $option['label'],
                            'value' => $option['value'],
                            'count' => isset($optionsCount[$option['value']]) ? $optionsCount[$option['value']] : 0,
                        );
                    }
                }
            }

            $tags = array(
                Mage_Eav_Model_Entity_Attribute::CACHE_TAG.':'.$attribute->getId()
            );

            $tags = $this->getLayer()->getStateTags($tags);
            $this->getLayer()->getAggregator()->saveCacheData($data, $key, $tags);
        }
        return $data;
    }
}
