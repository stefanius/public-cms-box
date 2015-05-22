<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\UiComponent\Config;

use ArrayObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Config\CacheInterface;
use Magento\Framework\View\Element\UiComponent\ArrayObjectFactory;
use Magento\Framework\View\Element\UiComponent\Config\FileCollector\AggregatedFileCollectorFactory;
use Magento\Framework\View\Element\UiComponent\Config\Provider\Component\Definition as ComponentDefinition;

/**
 * Class Manager
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Manager implements ManagerInterface
{
    /**
     * ID in the storage cache
     */
    const CACHE_ID = 'ui_component_configuration_data';

    /**
     * Configuration provider for UI component
     *
     * @var ComponentDefinition
     */
    protected $componentConfigProvider;

    /**
     * DOM document merger
     *
     * @var DomMergerInterface
     */
    protected $domMerger;

    /**
     * Factory for UI config reader
     *
     * @var ReaderFactory
     */
    protected $readerFactory;

    /**
     * Component data
     *
     * @var ArrayObject
     */
    protected $componentsData;

    /**
     * Components pool
     *
     * @var ArrayObject
     */
    protected $componentsPool;

    /**
     * Factory for ArrayObject
     *
     * @var ArrayObjectFactory
     */
    protected $arrayObjectFactory;

    /**
     * @var AggregatedFileCollectorFactory
     */
    protected $aggregatedFileCollectorFactory;

    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * @var UiReaderInterface[]
     */
    protected $uiReader;

    /**
     * Constructor
     *
     * @param ComponentDefinition $componentConfigProvider
     * @param DomMergerInterface $domMerger
     * @param ReaderFactory $readerFactory
     * @param ArrayObjectFactory $arrayObjectFactory
     * @param AggregatedFileCollectorFactory $aggregatedFileCollectorFactory
     * @param CacheInterface $cache
     */
    public function __construct(
        ComponentDefinition $componentConfigProvider,
        DomMergerInterface $domMerger,
        ReaderFactory $readerFactory,
        ArrayObjectFactory $arrayObjectFactory,
        AggregatedFileCollectorFactory $aggregatedFileCollectorFactory,
        CacheInterface $cache
    ) {
        $this->componentConfigProvider = $componentConfigProvider;
        $this->domMerger = $domMerger;
        $this->readerFactory = $readerFactory;
        $this->arrayObjectFactory = $arrayObjectFactory;
        $this->componentsData = $this->arrayObjectFactory->create();
        $this->aggregatedFileCollectorFactory = $aggregatedFileCollectorFactory;
        $this->cache = $cache;
    }

    /**
     * Get component data
     *
     * @param string $name
     * @return array
     */
    public function getData($name)
    {
        return (array) $this->componentsData->offsetGet($name);
    }

    /**
     * Has component data
     *
     * @param string $name
     * @return bool
     */
    protected function hasData($name)
    {
        return $this->componentsData->offsetExists($name);
    }

    /**
     * Prepare the initialization data of UI components
     *
     * @param string $name
     * @return ManagerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function prepareData($name)
    {
        if ($name === null || $this->hasData($name)) {
            throw new LocalizedException(
                new \Magento\Framework\Phrase(
                    'Initialization error component, check the '
                    . 'spelling of the name or the correctness of the call.'
                )
            );
        }
        $this->componentsPool = $this->arrayObjectFactory->create();

        $cacheID = static::CACHE_ID . '_' . $name;
        $cachedPool = $this->cache->load($cacheID);
        if ($cachedPool === false) {
            $this->prepare($name);
            $this->cache->save($this->componentsPool->serialize(), $cacheID);
        } else {
            $this->componentsPool->unserialize($cachedPool);
        }
        $this->componentsData->offsetSet($name, $this->componentsPool);

        return $this;
    }

    /**
     * To create the raw  data components
     *
     * @param string $component
     * @return array
     */
    public function createRawComponentData($component)
    {
        $componentData = $this->componentConfigProvider->getComponentData($component);
        $componentData[Converter::DATA_ATTRIBUTES_KEY] = isset($componentData[Converter::DATA_ATTRIBUTES_KEY])
            ? $componentData[Converter::DATA_ATTRIBUTES_KEY]
            : [];
        $componentData[Converter::DATA_ARGUMENTS_KEY] = isset($componentData[Converter::DATA_ARGUMENTS_KEY])
            ? $componentData[Converter::DATA_ARGUMENTS_KEY]
            : [];

        return [
            ManagerInterface::COMPONENT_ATTRIBUTES_KEY => $componentData[Converter::DATA_ATTRIBUTES_KEY],
            ManagerInterface::COMPONENT_ARGUMENTS_KEY => $componentData[Converter::DATA_ARGUMENTS_KEY],
        ];
    }

    /**
     * Get UIReader and collect base files configuration
     *
     * @param string $name
     * @return UiReaderInterface
     */
    public function getReader($name)
    {
        if (!isset($this->uiReader[$name])) {
            $this->domMerger->unsetDom();
            $this->uiReader[$name] =  $this->readerFactory->create(
                [
                    'fileCollector' => $this->aggregatedFileCollectorFactory->create(
                        ['searchPattern' => sprintf(ManagerInterface::SEARCH_PATTERN, $name)]
                    ),
                    'domMerger' => $this->domMerger
                ]
            );
        }

        return $this->uiReader[$name];
    }

    /**
     * Initialize the new component data
     *
     * @param string $name
     * @return void
     */
    protected function prepare($name)
    {
        $componentData = $this->getReader($name)->read();
        $componentsPool = reset($componentData);
        $componentsPool = reset($componentsPool);
        $componentsPool[Converter::DATA_ATTRIBUTES_KEY] = array_merge(
            ['name' => $name],
            $componentsPool[Converter::DATA_ATTRIBUTES_KEY]
        );
        $components = $this->createDataForComponent(key($componentData), [$componentsPool]);
        $this->addComponentIntoPool($name, reset($components));
    }

    /**
     * Create data for component instance
     *
     * @param string $name
     * @param array $componentsPool
     * @return array
     */
    protected function createDataForComponent($name, array $componentsPool)
    {
        $createdComponents = [];
        $rootComponent = $this->createRawComponentData($name);
        foreach ($componentsPool as $key => $component) {
            $resultConfiguration = [ManagerInterface::CHILDREN_KEY => []];
            $instanceName = $this->createName($component, $key, $name);
            $resultConfiguration[ManagerInterface::COMPONENT_ARGUMENTS_KEY] = $this->mergeArguments(
                $component,
                $rootComponent
            );
            unset($component[Converter::DATA_ARGUMENTS_KEY]);
            $resultConfiguration[ManagerInterface::COMPONENT_ATTRIBUTES_KEY] = $this->mergeAttributes(
                $component,
                $rootComponent
            );
            unset($component[Converter::DATA_ATTRIBUTES_KEY]);
            // Create inner components
            foreach ($component as $subComponentName => $subComponent) {
                $resultConfiguration[ManagerInterface::CHILDREN_KEY] = array_merge(
                    $resultConfiguration[ManagerInterface::CHILDREN_KEY],
                    $this->createDataForComponent($subComponentName, $subComponent)
                );
            }
            $createdComponents[$instanceName] = $resultConfiguration;
        }

        return $createdComponents;
    }

    /**
     * Add a component into pool
     *
     * @param string $instanceName
     * @param array $configuration
     * @return void
     */
    protected function addComponentIntoPool($instanceName, array $configuration)
    {
        $this->componentsPool->offsetSet($instanceName, $configuration);
    }

    /**
     * Merge component arguments
     *
     * @param array $componentData
     * @param array $rootComponentData
     * @return array
     */
    protected function mergeArguments(array $componentData, array $rootComponentData)
    {
        $baseArguments = isset($rootComponentData[ManagerInterface::COMPONENT_ARGUMENTS_KEY])
            ? $rootComponentData[ManagerInterface::COMPONENT_ARGUMENTS_KEY]
            : [];
        $componentArguments = isset($componentData[Converter::DATA_ARGUMENTS_KEY])
            ? $componentData[Converter::DATA_ARGUMENTS_KEY]
            : [];

        return array_replace_recursive($baseArguments, $componentArguments);
    }

    /**
     * Merge component attributes
     *
     * @param array $componentData
     * @param array $rootComponentData
     * @return array
     */
    protected function mergeAttributes(array $componentData, array $rootComponentData)
    {
        $baseAttributes = isset($rootComponentData[ManagerInterface::COMPONENT_ATTRIBUTES_KEY])
            ? $rootComponentData[ManagerInterface::COMPONENT_ATTRIBUTES_KEY]
            : [];
        $componentAttributes = isset($componentData[Converter::DATA_ATTRIBUTES_KEY])
            ? $componentData[Converter::DATA_ATTRIBUTES_KEY]
            : [];
        unset($componentAttributes['noNamespaceSchemaLocation']);

        return array_replace_recursive($baseAttributes, $componentAttributes);
    }

    /**
     * Create name component instance
     *
     * @param array $componentData
     * @param string|int $key
     * @param string $componentName
     * @return string
     */
    protected function createName(array $componentData, $key, $componentName)
    {
        return isset($componentData[Converter::DATA_ATTRIBUTES_KEY][Converter::NAME_ATTRIBUTE_KEY])
            ? $componentData[Converter::DATA_ATTRIBUTES_KEY][Converter::NAME_ATTRIBUTE_KEY]
            : sprintf(ManagerInterface::ANONYMOUS_TEMPLATE, $componentName, $key);
    }
}
