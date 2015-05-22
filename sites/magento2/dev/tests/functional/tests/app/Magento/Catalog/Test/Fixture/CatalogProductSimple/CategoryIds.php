<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Fixture\CatalogProductSimple;

use Magento\Catalog\Test\Fixture\Category;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Class CategoryIds
 * Create and return Category
 */
class CategoryIds implements FixtureInterface
{
    /**
     * Names and Ids of the created categories
     *
     * @var array
     */
    protected $data;

    /**
     * Fixtures of category
     *
     * @var array
     */
    protected $categories;

    /**
     * @constructor
     * @param FixtureFactory $fixtureFactory
     * @param array $params
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function __construct(
        FixtureFactory $fixtureFactory,
        array $params,
        array $data = []
    ) {
        $this->params = $params;

        if (!empty($data['category'])
            && empty($data['presets'])
            && $data['category'] instanceof Category
        ) {
            /** @var Category $category */
            $category = $data['category'];
            if (!$category->hasData('id')) {
                $category->persist();
            }
            $this->data[] = $category->getName();
            $this->categories[] = $category;
        } elseif (isset($data['presets'])) {
            $presets = explode(',', $data['presets']);
            foreach ($presets as $preset) {
                if (trim($preset) == '-') {
                    $this->data[] = '';
                    continue;
                }
                $category = $fixtureFactory->createByCode('category', ['dataSet' => $preset]);
                if (!isset($data['new_category']) || $data['new_category'] !== 'yes') {
                    $category->persist();
                }

                /** @var Category $category */
                $this->data[] = $category->getName();
                $this->categories[] = $category;
            }
        }
    }

    /**
     * Persist custom selections products
     *
     * @return void
     */
    public function persist()
    {
        //
    }

    /**
     * Return prepared data set
     *
     * @param string|null $key
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getData($key = null)
    {
        return $this->data;
    }

    /**
     * Return data set configuration settings
     *
     * @return array
     */
    public function getDataConfig()
    {
        return $this->params;
    }

    /**
     * Return category array
     *
     * @return array
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * Get id of categories
     *
     * @return array
     */
    public function getIds()
    {
        $ids = [];
        foreach ($this->categories as $category) {
            $ids[] = $category->getId();
        }

        return $ids;
    }
}
