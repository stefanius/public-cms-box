<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DomDocument;

/**
 * DOM document factory
 */
class Factory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create empty DOM document instance.
     *
     * @return \DOMDocument
     */
    public function createDomDocument()
    {
        return $this->_objectManager->create('DOMDocument', []);
    }
}
