<?php
declare(strict_types = 1);

namespace Ineiman\RecentlyPurchased\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Config data for Recently Purchased
 */
class Config
{
    /**#@+
     * System configurations paths
     *
     * @type string
     */
    public const XML_PATH_ENABLED = 'ineiman_recently_purchased/recently_purchased_group/enabled';
    /**#@- */

    /**
     * Construct
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    /**
     * Get is Recently Purchase module enabled
     *
     * @param int|string|null $storeId
     * @return mixed
     */
    public function isEnabled(int|string $storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
