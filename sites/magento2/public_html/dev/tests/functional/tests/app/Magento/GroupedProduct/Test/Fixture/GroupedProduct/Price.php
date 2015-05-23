<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GroupedProduct\Test\Fixture\GroupedProduct;

use Magento\Catalog\Test\Fixture\CatalogProductSimple\Price as ParentPrice;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Class Price
 *
 * Data keys:
 *  - preset (Price verification preset name)
 *  - value (Price value)
 */
class Price extends ParentPrice implements FixtureInterface
{
    /**
     * Preset for price
     *
     * @return array|null
     */
    public function getPreset()
    {
        $presets = [
            'starting-100' => [
                'compare_price' => [
                    'price_starting' => '100.00',
                ],
            ],
            'starting-560' => [
                'compare_price' => [
                    'price_starting' => '560.00',
                ],
            ],
        ];
        if (!isset($presets[$this->currentPreset])) {
            return null;
        }
        return $presets[$this->currentPreset];
    }
}
