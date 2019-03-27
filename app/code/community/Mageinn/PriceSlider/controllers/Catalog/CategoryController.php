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
 * Category Controller
 * 
 * @author      Mageinn
 * @package     Mageinn_PriceSlider
 * @category    Mageinn
 */
require_once('app/code/core/Mage/Catalog/controllers/CategoryController.php');
class Mageinn_PriceSlider_Catalog_CategoryController 
    extends Mage_Catalog_CategoryController {

    public function viewAction() 
    {
        if ($this->getRequest()->isXmlHttpRequest() && Mage::helper('mageinn_priceslider')->isEnabled()) { 
            $response = array();

            if ($category = $this->_initCatagory()) {
                $design = Mage::getSingleton('catalog/design');
                $settings = $design->getDesignSettings($category);

                // apply custom design
                if ($settings->getCustomDesign()) {
                    $design->applyCustomDesign($settings->getCustomDesign());
                }

                Mage::getSingleton('catalog/session')->setLastViewedCategoryId($category->getId());

                $update = $this->getLayout()->getUpdate();
                $update->addHandle('default');

                if (!$category->hasChildren()) {
                    $update->addHandle('catalog_category_layered_nochildren');
                }

                $this->addActionLayoutHandles();
                $update->addHandle($category->getLayoutUpdateHandle());
                $update->addHandle('CATEGORY_' . $category->getId());
                $this->loadLayoutUpdates();

                // apply custom layout update once layout is loaded
                if ($layoutUpdates = $settings->getLayoutUpdates()) {
                    if (is_array($layoutUpdates)) {
                        foreach ($layoutUpdates as $layoutUpdate) {
                            $update->addUpdate($layoutUpdate);
                        }
                    }
                }

                $this->generateLayoutXml()->generateLayoutBlocks();
                $viewpanel = $this->getLayout()->getBlock('catalog.leftnav')->toHtml();
                $productlist = $this->getLayout()->getBlock('product_list')->toHtml();
                $response['status'] = 1;
                $response['viewpanel'] = $viewpanel;
                $response['productlist'] = $productlist;
                $response['urlpattern'] = Mage::helper('mageinn_priceslider')->getCurrentUrl();
            } else {
                $response['status'] = 0;
            }
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
            return;
        }

        parent::viewAction();
    }
}
