<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api\Test\Unit\Config;

use Magento\Framework\Api\Config\Converter;

class ConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Api\Config\Converter
     */
    protected $_converter;

    /**
     * Initialize parameters
     */
    protected function setUp()
    {
        $this->_converter = new \Magento\Framework\Api\Config\Converter();
    }

    /**
     * Test invalid data
     */
    public function testInvalidData()
    {
        $result = $this->_converter->convert(['invalid data']);
        $this->assertEmpty($result);
    }

    /**
     * Test empty data
     */
    public function testConvertNoElements()
    {
        $result = $this->_converter->convert(new \DOMDocument());
        $this->assertEmpty($result);
    }

    /**
     * Test converting valid data object config
     */
    public function testConvert()
    {
        $expected = [
            'Magento\Tax\Api\Data\TaxRateInterface' => [
            ],
            'Magento\Catalog\Api\Data\ProductInterface' => [
                'stock_item' => [
                    Converter::DATA_TYPE => 'Magento\CatalogInventory\Api\Data\StockItemInterface',
                    Converter::RESOURCE_PERMISSIONS => [],
                ],
            ],
            'Magento\Customer\Api\Data\CustomerInterface' => [
                'custom_1' => [
                    Converter::DATA_TYPE => 'Magento\Customer\Api\Data\CustomerCustom',
                    Converter::RESOURCE_PERMISSIONS => [],
                ],
                'custom_2' => [
                    Converter::DATA_TYPE => 'Magento\CustomerExtra\Api\Data\CustomerCustom2',
                    Converter::RESOURCE_PERMISSIONS => [],
                ],
            ],
            'Magento\Customer\Api\Data\CustomerInterface2' => [
                'custom_with_permission' => [
                    Converter::DATA_TYPE => 'Magento\Customer\Api\Data\CustomerCustom',
                    Converter::RESOURCE_PERMISSIONS => [
                        'Magento_Customer::manage',
                    ],
                ],
                'custom_with_multiple_permissions' => [
                    Converter::DATA_TYPE => 'Magento\CustomerExtra\Api\Data\CustomerCustom2',
                    Converter::RESOURCE_PERMISSIONS => [
                        'Magento_Customer::manage',
                        'Magento_Customer::manage2',
                    ],
                ],
            ],
        ];

        $xmlFile = __DIR__ . '/_files/service_data_attributes.xml';
        $dom = new \DOMDocument();
        $dom->loadXML(file_get_contents($xmlFile));
        $result = $this->_converter->convert($dom);
        $this->assertEquals($expected, $result);
    }
}
