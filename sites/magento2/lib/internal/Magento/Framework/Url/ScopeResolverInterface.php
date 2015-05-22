<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Url;

interface ScopeResolverInterface extends \Magento\Framework\App\ScopeResolverInterface
{
    /**
     * Retrieve area code
     *
     * @return string|null
     */
    public function getAreaCode();
}
