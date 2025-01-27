<?php
declare(strict_types = 1);

namespace Ineiman\RecentlyPurchased\Setup\Patch\Data;

use Magento\Banner\Model\BannerFactory;
use Magento\Banner\Model\ResourceModel\Banner as BannerResource;
use Magento\Banner\Model\Banner\Validator;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Psr\Log\LoggerInterface;

/**
 * Add dynamic block with Widget content
 */
class AddDynamicBlock implements DataPatchInterface, PatchRevertableInterface
{
    /**
     * Content of dynamic block
     */
    private const DEFAULT_CONTENT = '<div data-content-type="html" data-appearance="default"'
        . ' data-element="main">{{widget type="Ineiman\RecentlyPurchased\Block\Widget"'
        . ' title="Recently Purchased" template="Ineiman_RecentlyPurchased::widget/recently_purchased.phtml"'
        . ' cache_lifetime="0" products_count="10"}}</div>';

    /**
     * Constructor
     *
     * @param \Magento\Banner\Model\BannerFactory $bannerFactory
     * @param \Magento\Banner\Model\ResourceModel\Banner $bannerResource
     * @param \Magento\Banner\Model\Banner\Validator $bannerValidator
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        private readonly BannerFactory $bannerFactory,
        private readonly BannerResource $bannerResource,
        private readonly Validator $bannerValidator,
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $data = [
            'store_contents' => self::DEFAULT_CONTENT,
            'use_default_value' => true,
            'show_use_default_value' => true,
            'name' => 'Recently Purchased Widget',
            'is_enabled' => true,
            'types' => null,
            'store_id' => 0,
            'content_readonly' => false,
            'readonly' => false
        ];

        $model = $this->bannerFactory->create();
        $data = $this->bannerValidator->prepareSaveData($data);

        try {
            $model->setData($data);
            $this->bannerResource->save($model);

        } catch (\Exception $e) {
            $this->logger->error('Cannot save the dynamic block, Exception: ' . $e->getMessage());
            $this->logger->error($e->getTraceAsString());
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @inheritdoc
     */
    public function revert()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }
}
