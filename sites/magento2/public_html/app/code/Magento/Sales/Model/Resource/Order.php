<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Resource;

use Magento\Framework\App\Resource as AppResource;
use Magento\Framework\Math\Random;
use Magento\SalesSequence\Model\Manager;
use Magento\Sales\Model\Resource\EntityAbstract as SalesResource;
use Magento\Sales\Model\Resource\Order\Handler\Address as AddressHandler;
use Magento\Sales\Model\Resource\Order\Handler\State as StateHandler;
use Magento\Sales\Model\Spi\OrderResourceInterface;

/**
 * Flat sales order resource
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Order extends SalesResource implements OrderResourceInterface
{
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'sales_order_resource';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'resource';

    /**
     * @var StateHandler
     */
    protected $stateHandler;

    /**
     * @var AddressHandler
     */
    protected $addressHandler;

    /**
     * Model Initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('sales_order', 'entity_id');
    }

    /**
     * @param \Magento\Framework\Model\Resource\Db\Context $context
     * @param Attribute $attribute
     * @param Manager $sequenceManager
     * @param EntitySnapshot $entitySnapshot
     * @param AddressHandler $addressHandler
     * @param StateHandler $stateHandler
     * @param null $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\Resource\Db\Context $context,
        Attribute $attribute,
        Manager $sequenceManager,
        EntitySnapshot $entitySnapshot,
        AddressHandler $addressHandler,
        StateHandler $stateHandler,
        $resourcePrefix = null
    ) {
        $this->stateHandler = $stateHandler;
        $this->addressHandler = $addressHandler;
        parent::__construct($context, $attribute, $sequenceManager, $entitySnapshot, $resourcePrefix);
    }

    /**
     * Count existent products of order items by specified product types
     *
     * @param int $orderId
     * @param array $productTypeIds
     * @param bool $isProductTypeIn
     * @return array
     */
    public function aggregateProductsByTypes($orderId, $productTypeIds = [], $isProductTypeIn = false)
    {
        $adapter = $this->getReadConnection();
        $select = $adapter->select()->from(
            ['o' => $this->getTable('sales_order_item')],
            ['o.product_type', new \Zend_Db_Expr('COUNT(*)')]
        )->joinInner(
            ['p' => $this->getTable('catalog_product_entity')],
            'o.product_id=p.entity_id',
            []
        )->where(
            'o.order_id=?',
            $orderId
        )->group(
            'o.product_type'
        );
        if ($productTypeIds) {
            $select->where(sprintf('(o.product_type %s (?))', $isProductTypeIn ? 'IN' : 'NOT IN'), $productTypeIds);
        }
        return $adapter->fetchPairs($select);
    }

    /**
     * Process items dependency for new order, returns qty of affected items;
     *
     * @param \Magento\Sales\Model\Order $object
     * @return int
     */
    protected function calculateItems(\Magento\Sales\Model\Order $object)
    {
        $itemsCount = 0;
        if (!$object->getId()) {
            foreach ($object->getAllItems() as $item) {
                /** @var  \Magento\Sales\Model\Order\Item $item */
                $parent = $item->getQuoteParentItemId();
                if ($parent && !$item->getParentItem()) {
                    $item->setParentItem($object->getItemByQuoteItemId($parent));
                } elseif (!$parent) {
                    $itemsCount++;
                }
            }
        }
        return $itemsCount;
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        /** @var \Magento\Sales\Model\Order $object */
        $this->addressHandler->removeEmptyAddresses($object);
        $this->stateHandler->check($object);
        if (!$object->getId()) {
            /** @var \Magento\Store\Model\Store $store */
            $store = $object->getStore();
            $name = [
                $store->getWebsite()->getName(),
                $store->getGroup()->getName(),
                $store->getName(),
            ];
            $object->setStoreName(implode("\n", $name));
            $object->setTotalItemCount($this->calculateItems($object));
        }
        $object->setData(
            'protect_code',
            substr(md5(uniqid(Random::getRandomNumber(), true) . ':' . microtime(true)), 5, 6)
        );
        $isNewCustomer = !$object->getCustomerId() || $object->getCustomerId() === true;
        if ($isNewCustomer && $object->getCustomer()) {
            $object->setCustomerId($object->getCustomer()->getId());
        }
        return parent::_beforeSave($object);
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function processRelations(\Magento\Framework\Model\AbstractModel $object)
    {
        /** @var \Magento\Sales\Model\Order $object */
        $this->addressHandler->process($object);

        if (null !== $object->getItems()) {
            /** @var \Magento\Sales\Model\Order\Item $item */
            foreach ($object->getItems() as $item) {
                $item->setOrderId($object->getId());
                $item->setOrder($object);
                $item->save();
            }
        }
        if (null !== $object->getPayments()) {
            /** @var \Magento\Sales\Model\Order\Payment $payment */
            foreach ($object->getPayments() as $payment) {
                $payment->setParentId($object->getId());
                $payment->setOrder($object);
                $payment->save();
            }
        }
        if (null !== $object->getStatusHistories()) {
            /** @var \Magento\Sales\Model\Order\Status\History $statusHistory */
            foreach ($object->getStatusHistories() as $statusHistory) {
                $statusHistory->setParentId($object->getId());
                $statusHistory->save();
                $statusHistory->setOrder($object);
            }
        }
        if (null !== $object->getRelatedObjects()) {
            foreach ($object->getRelatedObjects() as $relatedObject) {
                $relatedObject->save();
                $relatedObject->setOrder($object);
            }
        }
        return parent::processRelations($object);
    }
}
