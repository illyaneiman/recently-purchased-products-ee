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

namespace Ineiman\RecentlyPurchased\Block;

use Ineiman\RecentlyPurchased\Model\Config;
use Ineiman\RecentlyPurchased\Model\RecentlyPurchaseManagement;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogWidget\Block\Product\ProductsList;
use Magento\CatalogWidget\Model\Rule;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Url\EncoderInterface;
use Magento\Framework\View\LayoutFactory;
use Magento\Rule\Model\Condition\Sql\Builder as SqlBuilder;
use Magento\Widget\Helper\Conditions;

/**
 * Widget block to render Recently Purchased products
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Widget extends ProductsList
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var string[]
     */
    private $controllerNames = [
        'ajax',
        'category'
    ];

    /**
     * Construct
     *
     * @param \Ineiman\RecentlyPurchased\Model\Config $config
     * @param \Ineiman\RecentlyPurchased\Model\RecentlyPurchaseManagement $recentlyPurchaseManagement
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Rule\Model\Condition\Sql\Builder $sqlBuilder
     * @param \Magento\CatalogWidget\Model\Rule $rule
     * @param \Magento\Widget\Helper\Conditions $conditionsHelper
     * @param array $data
     * @param \Magento\Framework\Serialize\Serializer\Json|null $json
     * @param \Magento\Framework\View\LayoutFactory|null $layoutFactory
     * @param \Magento\Framework\Url\EncoderInterface|null $urlEncoder
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface|null $categoryRepository
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        private readonly Config $config,
        private readonly RecentlyPurchaseManagement $recentlyPurchaseManagement,
        Context $context,
        CollectionFactory $productCollectionFactory,
        Visibility $catalogProductVisibility,
        HttpContext $httpContext,
        SqlBuilder $sqlBuilder,
        Rule $rule,
        Conditions $conditionsHelper,
        array $data = [],
        Json $json = null,
        LayoutFactory $layoutFactory = null,
        EncoderInterface $urlEncoder = null,
        CategoryRepositoryInterface $categoryRepository = null
    ) {
        parent::__construct(
            $context,
            $productCollectionFactory,
            $catalogProductVisibility,
            $httpContext,
            $sqlBuilder,
            $rule,
            $conditionsHelper,
            $data,
            $json,
            $layoutFactory,
            $urlEncoder,
            $categoryRepository
        );
    }

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->addColumnCountLayoutDepend('empty', 6)
            ->addColumnCountLayoutDepend('1column', 5)
            ->addColumnCountLayoutDepend('2columns-left', 4)
            ->addColumnCountLayoutDepend('2columns-right', 4)
            ->addColumnCountLayoutDepend('3columns', 3);

        $this->addData([
            'cache_lifetime' => 0,
            'cache_tags' => [
                RecentlyPurchaseManagement::CACHE_TAG,
            ],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getBaseCollection(): Collection
    {
        return $this->getFilteredCollection();
    }

    /**
     * @inheritdoc
     */
    public function createCollection(): Collection
    {
        if (!$this->config->isEnabled($this->getStoreId())) {
            return $this->productCollectionFactory->create();
        }

        return $this->getFilteredCollection();
    }

    /**
     * Create product collection and filter it by recently_purchased attribute, product limit in widget and category
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    private function getFilteredCollection(): Collection
    {
        $productLimit = (int)$this->getData('products_count');
        $productCollection = $this->recentlyPurchaseManagement->getFilteredCollection(
            $productLimit,
            $this->getStoreId()
        );

        if ($categoryId = $this->getCategoryId()) {
            $productCollection = $this->recentlyPurchaseManagement->addCategoriesToFilter(
                $productCollection,
                $categoryId
            );
        }

        return $productCollection;
    }

    /**
     * Get category id if widget on category page.
     *
     * @return int|null
     */
    private function getCategoryId(): ?int
    {
        $request = $this->getRequest();
        $params = $request->getParams();
        $controllerName = $request->getControllerName();

        if ((in_array($controllerName, $this->controllerNames)) && isset($params['id'])) {
            return (int)$params['id'];
        }

        return null;
    }

    /**
     * Check if module enabled
     *
     * @return mixed
     */
    public function isEnabled(): mixed
    {
        return $this->config->isEnabled($this->getStoreId());
    }

    /**
     * Retrieve store id
     *
     * @return int|string|null
     */
    private function getStoreId(): int|string|null
    {
        try {
            $store = $this->_storeManager->getStore();
        } catch (NoSuchEntityException $e) {
            return null;
        }

        return $store->getId();
    }
}
