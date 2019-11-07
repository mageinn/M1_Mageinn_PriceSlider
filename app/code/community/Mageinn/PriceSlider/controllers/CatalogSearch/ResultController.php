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
 * Catalog Search Controller
 *
 * @author      Mageinn
 * @package     Mageinn_PriceSlider
 * @category    Mageinn
 */
require_once('app/code/core/Mage/CatalogSearch/controllers/ResultController.php');

class Mageinn_PriceSlider_CatalogSearch_ResultController
    extends Mage_CatalogSearch_ResultController
{
    public function indexAction()
    {
        if ($this->getRequest()->isXmlHttpRequest() && Mage::helper('mageinn_priceslider')->isEnabled()) {
            $response = array();

            $query = Mage::helper('catalogsearch')->getQuery();
            /* @var $query Mage_CatalogSearch_Model_Query */

            $query->setStoreId(Mage::app()->getStore()->getId());

            if ($query->getQueryText() != '') {
                if (Mage::helper('catalogsearch')->isMinQueryLength()) {
                    $query->setId(0)
                        ->setIsActive(1)
                        ->setIsProcessed(1);
                } else {
                    if ($query->getId()) {
                        $query->setPopularity($query->getPopularity() + 1);
                    } else {
                        $query->setPopularity(1);
                    }

                    if ($query->getRedirect()) {
                        $query->save();
                        $this->getResponse()->setRedirect($query->getRedirect());
                        return;
                    } else {
                        $query->prepare();
                    }
                }

                Mage::helper('catalogsearch')->checkNotes();

                $this->loadLayout();
                $this->_initLayoutMessages('catalog/session');
                $this->_initLayoutMessages('checkout/session');

                if (!Mage::helper('catalogsearch')->isMinQueryLength()) {
                    $query->save();
                }
                $response['status'] = 1;
                $viewpanel = $this->getLayout()->getBlock('catalogsearch.leftnav')->toHtml(); //Get the new Layered Menu
                $productlist = $this->getLayout()->getBlock('search_result_list')->toHtml(); //New product List
                $response['viewpanel'] = $viewpanel;
                $response['productlist'] = $productlist;
                $response['urlpattern'] = Mage::helper('mageinn_priceslider')->getCurrentUrl();
            } else {
                $response['status'] = 0;
            }

            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
            return;
        }

        parent::indexAction();
    }
}
