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
