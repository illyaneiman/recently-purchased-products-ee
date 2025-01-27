<?php
declare(strict_types = 1);

namespace Ineiman\RecentlyPurchased\Observer;

use Ineiman\RecentlyPurchased\Model\Config;
use Ineiman\RecentlyPurchased\Model\RecentlyPurchaseManagement;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Type;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\Sales\Model\Order\Item;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class UpdateRecentOrders to store recently purchased
 */
class UpdateRecentOrders implements ObserverInterface
{
    /**
     * @var array
     */
    private array $updatedGroupedProductsIds = [];

    /**
     * Update Recent Order constructor
     *
     * @param \Ineiman\RecentlyPurchased\Model\Config $config
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        private readonly Config $config,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly DateTime $dateTime,
        private readonly StoreManagerInterface $storeManager
    ) {
    }

    /**
     * Execute Observer to save the value for custom attribute recently purchased in the catalog_product_entity table
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        try {
            $storeId = $this->storeManager->getStore()->getId();
        } catch (NoSuchEntityException $e) {
            $storeId = null;
        }

        if ($this->config->isEnabled($storeId)) {
            $order = $observer->getEvent()->getOrder();

            foreach ($order->getAllVisibleItems() as $item) {
                /**
                 * Don`t need to save recently purchased attribute to configurable and bundle childs
                 */
                if (!$this->isAttributeSaveAllowedForItems($item)) {
                    continue;
                }

                $product = $this->getProductFromItem($item);

                if (!$product || !$product->getId()) {
                    continue;
                }

                $timeStamp = $this->getTimeStamp();
                $product->addAttributeUpdate(
                    RecentlyPurchaseManagement::RECENTLY_PURCHASED_ATTRIBUTE,
                    $timeStamp,
                    $storeId
                );
            }
        }
    }

    /**
     * Check if ordered item has parent (for configurable and bundle) or not.
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @return bool
     */
    private function isAttributeSaveAllowedForItems(Item $item): bool
    {
        return !($item->getProductType() === Type::TYPE_SIMPLE && $item->getParentItem());
    }

    /**
     * Get product from order item
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @return \Magento\Catalog\Api\Data\ProductInterface|null
     */
    private function getProductFromItem(Item $item): ?ProductInterface
    {
        return match ($item->getProductType()) {
            Type::TYPE_SIMPLE,
            Type::TYPE_BUNDLE,
            Configurable::TYPE_CODE => $item->getProduct(),
            Grouped::TYPE_CODE => $this->getGroupedProduct($item)
        };
    }

    /**
     * Load configurable product from child
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @return \Magento\Catalog\Api\Data\ProductInterface|null
     */
    private function getGroupedProduct(Item $item): ?ProductInterface
    {
        $itemOptions = $item->getProductOptions() ?? $item->getBuyRequest()->getData();
        $superProductConfig = $itemOptions['super_product_config'];
        $parentId = $superProductConfig['product_id'];

        /**
         * Each simple-item will trigger save for grouped product recently_purchased attribute.
         * So we save groupedProductId in array and the check if we need save recently_purchased attribute for it.
         */
        if (in_array($parentId, $this->updatedGroupedProductsIds)) {
            return null;
        }

        try {
            $product = $this->productRepository->getById((int)$parentId);
            $this->updatedGroupedProductsIds[] = $product->getId();
            return $product;
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * Get current time according to timezone
     *
     * @return false|string
     */
    private function getTimeStamp(): false|string
    {
        return $this->dateTime->gmtDate();
    }
}
