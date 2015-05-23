<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Block\Adminhtml\Product\Edit\Tab\Super\Config\Attribute;

use Magento\Mtf\Client\Element\SuggestElement;
use Magento\Catalog\Test\Fixture\CatalogProductAttribute;

/**
 * Class AttributeSelector
 * Form Attribute Search on Product page
 */
class AttributeSelector extends SuggestElement
{
    /**
     * Checking exist configurable attribute in search result
     *
     * @param CatalogProductAttribute $productAttribute
     * @return bool
     */
    public function isExistAttributeInSearchResult(CatalogProductAttribute $productAttribute)
    {
        return $this->isExistValueInSearchResult($productAttribute->getFrontendLabel());
    }
}
