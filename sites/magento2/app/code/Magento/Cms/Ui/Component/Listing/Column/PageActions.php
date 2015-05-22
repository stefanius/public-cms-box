<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Cms\Block\Adminhtml\Page\Grid\Renderer\Action\UrlBuilder;
use Magento\Framework\UrlInterface;

/**
 * Class PageActions
 */
class PageActions extends Column
{
    /**
     * Url path
     */
    const URL_PATH = 'cms/page/edit';

    /**
     * @var UrlBuilder
     */
    protected $actionUrlBuilder;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;


    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlBuilder $actionUrlBuilder
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlBuilder $actionUrlBuilder,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->actionUrlBuilder = $actionUrlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @param array $items
     * @return array
     */
    public function prepareItems(array & $items)
    {
        foreach ($items as & $item) {
            if (isset($item['page_id'])) {
                $item[$this->getData('name')]['edit'] = [
                    'href' => $this->urlBuilder->getUrl(static::URL_PATH, ['page_id' => $item['page_id']]),
                    'label' => __('Edit'),
                    'hidden' => true
                ];
            }
            if (isset($item['identifier'])) {
                $item[$this->getData('name')]['preview'] = [
                    'href' => $this->actionUrlBuilder->getUrl(
                        $item['identifier'],
                        isset($item['_first_store_id']) ? $item['_first_store_id'] : null,
                        isset($item['store_code']) ? $item['store_code'] : null
                    ),
                    'label' => __('Preview')
                ];
            }
        }

        return $items;
    }
}
