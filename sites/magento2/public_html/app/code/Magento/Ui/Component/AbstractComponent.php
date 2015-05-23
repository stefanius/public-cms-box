<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component;

use Magento\Framework\Object;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponent\JsConfigInterface;
use Magento\Framework\View\Element\UiComponent\DataSourceInterface;

/**
 * Abstract class AbstractComponent
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
abstract class AbstractComponent extends Object implements UiComponentInterface, JsConfigInterface
{
    /**
     * Render context
     *
     * @var ContextInterface
     */
    protected $context;

    /**
     * @var UiComponentInterface[]
     */
    protected $components;

    /**
     * @var array
     */
    protected $componentData = [];

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentInterface[] $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        array $components = [],
        array $data = []
    ) {
        $this->context = $context;
        $this->components = $components;
        parent::__construct($data);
    }

    /**
     * Get component context
     *
     * @return ContextInterface
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Prepare component configuration
     *
     * @return void
     */
    public function prepare()
    {
        //
    }

    /**
     * Produce and return block's html output
     *
     * @return string
     */
    public function toHtml()
    {
        $this->render();
    }

    /**
     * Render component
     *
     * @return string
     */
    public function render()
    {
        $result = $this->getContext()->getRenderEngine()->render($this, $this->getTemplate());
        return $result;
    }

    /**
     * Get component name
     *
     * @return string
     */
    public function getName()
    {
        return $this->getData('name');
    }

    /**
     * Add component
     *
     * @param string $name
     * @param UiComponentInterface $component
     * @return void
     */
    public function addComponent($name, UiComponentInterface $component)
    {
        $this->components[$name] = $component;
    }

    /**
     * @param string $name
     * @return UiComponentInterface
     */
    public function getComponent($name)
    {
        return isset($this->components[$name]) ? $this->components[$name] : null;
    }

    /**
     * Get components
     *
     * @return UiComponentInterface[]
     */
    public function getChildComponents()
    {
        return $this->components;
    }

    /**
     * Get template
     *
     * @return string
     */
    public function getTemplate()
    {
        return $this->getData('template') . '.xhtml';
    }

    /**
     * Render child component
     *
     * @param string $name
     * @return string
     */
    public function renderChildComponent($name)
    {
        $result = null;
        if (isset($this->components[$name])) {
            $result = $this->components[$name]->render();
        }
        return $result;
    }

    /**
     * Component data setter
     *
     * @param string|array $key
     * @param mixed $value
     * @return void
     */
    public function setData($key, $value = null)
    {
        parent::setData($key, $value);
    }

    /**
     * Component data getter
     *
     * @param string $key
     * @param string|int $index
     * @return mixed
     */
    public function getData($key = '', $index = null)
    {
        return parent::getData($key, $index);
    }

    /**
     * Set component configuration
     *
     * @return void
     */
    protected function prepareConfiguration()
    {
        $config = $this->getDefaultConfiguration();
        if ($this->hasData('config')) {
            $config = array_replace_recursive($config, $this->getData('config'));
        }

        $this->setData('config', $config);
    }

    /**
     * Get default parameters
     *
     * @return array
     */
    protected function getDefaultConfiguration()
    {
        return [];
    }

    /**
     * Get JS configuration
     *
     * @param UiComponentInterface $component
     * @param null|string $extends
     * @return array
     */
    protected function getConfiguration(UiComponentInterface $component, $extends = null)
    {
        $jsConfig = (array) $component->getData('js_config');
        if (isset($jsConfig['extends'])) {
            return $jsConfig;
        } else if (null !== $extends) {
            $jsConfig['extends'] = $extends;
        } else {
            $jsConfig['extends'] = $component->getContext()->getNamespace();
        }

        return $jsConfig;
    }

    /**
     * Get JS config
     *
     * @return array|string
     */
    public function getJsConfig()
    {
        return (array) $this->getData('config');
    }

    /**
     * @return array
     */
    public function getDataSourceData()
    {
        $dataSources = [];
        foreach ($this->getChildComponents() as $component) {
            if ($component instanceof DataSourceInterface) {
                $dataSources[] = [
                    'type' => $component->getComponentName(),
                    'name' => $component->getName(),
                    'dataScope' => $component->getContext()->getNamespace(),
                    'config' => [
                        'data' => $component->getDataProvider()->getData()
                    ]
                ];
            }
        }
        return $dataSources;
    }
}
