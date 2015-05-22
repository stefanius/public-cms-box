<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\TestStep;

use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Customer\Test\Fixture\Address;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Class FillBillingInformationStep
 * Fill billing information
 */
class FillBillingInformationStep implements TestStepInterface
{
    /**
     * Onepage checkout page
     *
     * @var CheckoutOnepage
     */
    protected $checkoutOnepage;

    /**
     * Address fixture
     *
     * @var Address
     */
    protected $billingAddress;

    /**
     * Customer fixture
     *
     * @var Customer
     */
    protected $customer;

    /**
     * Checkout method
     *
     * @var string
     */
    protected $checkoutMethod;

    /**
     * @constructor
     * @param CheckoutOnepage $checkoutOnepage
     * @param Address $billingAddress
     * @param Customer $customer
     * @param string $checkoutMethod
     */
    public function __construct(
        CheckoutOnepage $checkoutOnepage,
        Address $billingAddress,
        Customer $customer,
        $checkoutMethod
    ) {
        $this->checkoutOnepage = $checkoutOnepage;
        $this->billingAddress = $billingAddress;
        $this->customer = $customer;
        $this->checkoutMethod = $checkoutMethod;
    }

    /**
     * Fill billing address
     *
     * @return void
     */
    public function run()
    {
        $customer = $this->checkoutMethod === 'register' ? $this->customer : null;
        $this->checkoutOnepage->getBillingBlock()->fillBilling($this->billingAddress, $customer);
        $this->checkoutOnepage->getBillingBlock()->clickContinue();
    }
}
