<?php
declare(strict_types = 1);

namespace Ineiman\RecentlyPurchased\Setup\Patch\Data;

use Ineiman\RecentlyPurchased\Model\RecentlyPurchaseManagement;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\Backend\Datetime;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;

/**
 * Class for AddRecentlyPurchaseProductAttribute
 */
class AddRecentlyPurchaseProductAttribute implements DataPatchInterface, PatchRevertableInterface
{
    /**
     * Recently purchased custom attribute constructor
     *
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
     * @param \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly EavSetupFactory $eavSetupFactory
    ) {
    }

    /**
     * Apply custom attribute
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Validate_Exception
     * @throws \Magento\Framework\Validator\ValidateException
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $eavSetup->addAttribute(
            Product::ENTITY,
            RecentlyPurchaseManagement::RECENTLY_PURCHASED_ATTRIBUTE,
            [
                'type' => 'datetime',
                'label' => 'Recently Purchased',
                'input' => 'date',
                'source' => '',
                'frontend' => '',
                'required' => false,
                'backend' => Datetime::class,
                'sort_order' => '30',
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'default' => null,
                'visible' => true,
                'user_defined' => true,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'unique' => false,
                'apply_to' => '',
                'group' => 'General',
                'used_in_product_listing' => true,
                'used_for_sort_by' => true,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
                'option' => [
                    'values' => []
                ]
            ]
        );

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @inheritdoc
     */
    public function revert()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $eavSetup->removeAttribute(
            Product::ENTITY,
            RecentlyPurchaseManagement::RECENTLY_PURCHASED_ATTRIBUTE
        );

        $this->moduleDataSetup->getConnection()->endSetup();
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
