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

require_once('app/code/community/Mageinn/PriceSlider/Model/CollectionTrait.php');

/**
 * Fulltext collection
 *
 * @category    Mageinn
 * @package     Mageinn_PriceSlider
 * @author      Mageinn
 */
class Mageinn_PriceSlider_Model_CatalogSearch_Resource_Fulltext_Collection extends Mage_CatalogSearch_Model_Resource_Fulltext_Collection
{
    use Mageinn_PriceSlider_Model_Collection_CollectionTrait;
}
