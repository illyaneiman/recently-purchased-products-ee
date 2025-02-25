<?php
/**
 * Illia Neiman
 *
 * NOTICE OF LICENSE
 *
 * According to LICENCE file you are not allowed to copy, use or recreate this file in any ways.
 * Specially for eCommerce usage.
 *
 * @category Ineiman
 * @package RecentlyPurchased
 * @copyright Copyright (c) 2021-current Ineiman (https://github.com/illyaneiman)
 * @email kg.illya.ney@gmail.com
 */
declare(strict_types = 1);

namespace Ineiman\RecentlyPurchased\Model;

use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\Category;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

/**
 * Manage Recently Purchased Products
 */
class RecentlyPurchaseManagement
{
    /**#@+
     * Constants
     *
     * @type string
     */
    public const RECENTLY_PURCHASED_ATTRIBUTE = 'recently_purchase';
    public const CACHE_TAG = 'recently_purchased_cache_tag';
    /**#@- */

    /**
     * Construct
     *
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Catalog\Model\ResourceModel\Category $categoryResource
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     */
    public function __construct(
        private readonly CategoryFactory $categoryFactory,
        private readonly Category $categoryResource,
        private readonly CollectionFactory $productCollectionFactory
    ) {
    }

    /**
     * Get Product collection filtered and ordered by purchase date.
     *
     * @param int $productLimit
     * @param int|string|null $storeId
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getFilteredCollection(int $productLimit, int|string|null $storeId): Collection
    {
        $productCollection = $this->productCollectionFactory->create();

        if ($storeId) {
            $productCollection->setStoreId((int)$storeId);
        }

        $productCollection->addAttributeToSelect(
            RecentlyPurchaseManagement::RECENTLY_PURCHASED_ATTRIBUTE,
            'left'
        );
        $productCollection->addAttributeToFilter(
            RecentlyPurchaseManagement::RECENTLY_PURCHASED_ATTRIBUTE,
            ['lteq' => date('Y-m-d H:i:s')]
        );
        $productCollection->addAttributeToSelect('*');
        $productCollection->setOrder(RecentlyPurchaseManagement::RECENTLY_PURCHASED_ATTRIBUTE, 'DESC');

        if ($productLimit) {
            $productCollection->setPageSize($productLimit);
        }

        return $productCollection;
    }

    /**
     * Add category ids to collection filter.
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection
     * @param int $categoryId
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function addCategoriesToFilter(Collection $productCollection, int $categoryId): Collection
    {
        $category = $this->categoryFactory->create();
        $this->categoryResource->load($category, $categoryId);
        $categoryIds = explode(',', $category->getAllChildren());
        $productCollection->addCategoriesFilter(['in' => $categoryIds]);

        return $productCollection;
    }
}
