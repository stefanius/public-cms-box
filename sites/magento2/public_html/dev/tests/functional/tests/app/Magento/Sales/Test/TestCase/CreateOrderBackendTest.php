<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\TestCase;

use Magento\Mtf\TestCase\Scenario;

/**
 * Preconditions:
 * 1. Create customer.
 * 2. Create product.
 *
 * Steps:
 * 1. Open Backend.
 * 2. Open Sales -> Orders.
 * 3. Click Create New Order.
 * 4. Select Customer created in preconditions.
 * 5. Add Product.
 * 6. Fill data according dataSet.
 * 7. Click Update Product qty.
 * 8. Fill data according dataSet.
 * 9. Click Get Shipping Method and rates.
 * 10. Fill data according dataSet.
 * 11. Submit Order.
 * 12. Perform all assertions.
 *
 * @group Order_Management_(CS)
 * @ZephyrId MAGETWO-28696
 */
class CreateOrderBackendTest extends Scenario
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'CS';
    const TEST_TYPE = 'acceptance_test';
    /* end tags */

    /**
     * Runs sales order on backend.
     *
     * @return void
     */
    public function test()
    {
        $this->executeScenario();
    }

    /**
     * Disable enabled config after test.
     *
     * @return void
     */
    public function tearDown()
    {
        if (isset($this->currentVariation['arguments']['configData'])) {
            $this->objectManager->create(
                'Magento\Config\Test\TestStep\SetupConfigurationStep',
                ['configData' => $this->currentVariation['arguments']['configData'], 'rollback' => true]
            )->run();
        }
        $this->objectManager->create('Magento\SalesRule\Test\TestStep\DeleteAllSalesRuleStep')->run();
        $this->objectManager->create('Magento\Tax\Test\TestStep\DeleteAllTaxRulesStep')->run();
    }
}
