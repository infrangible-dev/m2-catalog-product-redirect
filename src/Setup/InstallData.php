<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductRedirect\Setup;

use Infrangible\CatalogProductRedirect\Model\Config\Source\Type;
use Infrangible\Core\Helper\Attribute;
use Infrangible\Core\Helper\Setup;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Set;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class InstallData
    implements InstallDataInterface
{
    /** @var EavSetupFactory */
    private $eavSetupFactory;

    /** @var Attribute */
    private $attributeHelper;

    /** @var Setup */
    private $setupHelper;

    /**
     * @param EavSetupFactory $eavSetupFactory
     * @param Attribute       $attributeHelper
     * @param Setup           $setupHelper
     */
    public function __construct(EavSetupFactory $eavSetupFactory, Attribute $attributeHelper, Setup $setupHelper)
    {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->attributeHelper = $attributeHelper;
        $this->setupHelper = $setupHelper;
    }

    /**
     * @throws \Exception
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        if (!$eavSetup->getAttributeId(Product::ENTITY, 'redirect_disabled')) {
            $eavSetup->addAttribute(
                Product::ENTITY, 'redirect_disabled', [
                                   'global'       => ScopedAttributeInterface::SCOPE_STORE,
                                   'type'         => Setup::ATTRIBUTE_TYPE_INT,
                                   'input'        => 'select',
                                   'label'        => 'Redirect Disabled',
                                   'required'     => 0,
                                   'source'       => Type::class,
                                   'user_defined' => 1
                               ]
            );
        }

        if (!$eavSetup->getAttributeId(Product::ENTITY, 'redirect_disabled_product_id')) {
            $eavSetup->addAttribute(
                Product::ENTITY, 'redirect_disabled_product_id', [
                                   'global'       => ScopedAttributeInterface::SCOPE_STORE,
                                   'type'         => Setup::ATTRIBUTE_TYPE_INT,
                                   'input'        => 'text',
                                   'label'        => 'Redirect Disabled Product',
                                   'required'     => 0,
                                   'user_defined' => 1
                               ]
            );
        }

        if (!$eavSetup->getAttributeId(Product::ENTITY, 'redirect_out_of_stock')) {
            $eavSetup->addAttribute(
                Product::ENTITY, 'redirect_out_of_stock', [
                                   'global'       => ScopedAttributeInterface::SCOPE_STORE,
                                   'type'         => Setup::ATTRIBUTE_TYPE_INT,
                                   'input'        => 'select',
                                   'label'        => 'Redirect Out of Stock',
                                   'required'     => 0,
                                   'source'       => Type::class,
                                   'user_defined' => 1
                               ]
            );
        }

        if (!$eavSetup->getAttributeId(Product::ENTITY, 'redirect_out_of_stock_product_id')) {
            $eavSetup->addAttribute(
                Product::ENTITY, 'redirect_out_of_stock_product_id', [
                                   'global'       => ScopedAttributeInterface::SCOPE_STORE,
                                   'type'         => Setup::ATTRIBUTE_TYPE_INT,
                                   'input'        => 'text',
                                   'label'        => 'Redirect Out of Stock Product',
                                   'required'     => 0,
                                   'user_defined' => 1
                               ]
            );
        }

        /** @var Set $attributeSet */
        foreach ($this->attributeHelper->getAttributeSetCollection() as $attributeSet) {
            $attributeSortOrder = 200;

            foreach ([
                'redirect_disabled',
                'redirect_disabled_product_id',
                'redirect_out_of_stock',
                'redirect_out_of_stock_product_id'
            ] as $attributeCode) {
                $groupId = $attributeSet->getDefaultGroupId();

                if ($groupId) {
                    $this->setupHelper->addProductAttributeToSetAndGroup(
                        $eavSetup,
                        $attributeCode,
                        strval($attributeSet->getId()),
                        strval($groupId),
                        $attributeSortOrder
                    );
                }

                $attributeSortOrder++;
            }
        }

        $setup->endSetup();
    }
}
