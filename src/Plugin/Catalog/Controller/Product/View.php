<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductRedirect\Plugin\Catalog\Controller\Product;

use Infrangible\Core\Helper\Attribute;
use Infrangible\Core\Helper\Database;
use Infrangible\Core\Helper\Stores;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\Result\Forward;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class View
{
    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /** @var Stores */
    protected $storeHelper;

    /*** @var ResultFactory */
    protected $resultFactory;

    /** @var Attribute */
    private $attributeHelper;

    /** @var Database */
    private $databaseHelper;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param Stores                     $storeHelper
     * @param ResultFactory              $resultFactory
     * @param Attribute                  $attributeHelper
     * @param Database                   $databaseHelper
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        Stores $storeHelper,
        ResultFactory $resultFactory,
        Attribute $attributeHelper,
        Database $databaseHelper
    ) {
        $this->productRepository = $productRepository;
        $this->storeHelper = $storeHelper;
        $this->resultFactory = $resultFactory;
        $this->attributeHelper = $attributeHelper;
        $this->databaseHelper = $databaseHelper;
    }

    /**
     * @return Forward|Redirect
     * @throws NoSuchEntityException
     * @throws \Exception
     */
    public function aroundExecute(\Magento\Catalog\Controller\Product\View $subject, callable $proceed)
    {
        $dbAdapter = $this->databaseHelper->getDefaultConnection();

        /** @var Http $request */
        $request = $subject->getRequest();

        $productId = (int) $request->getParam('id');
        $storeId = $this->storeHelper->getStore()->getId();
        if (!is_int($storeId)) {
            $storeId = (int) $storeId;
        }

        /** @var Product $product */
        $product = $this->productRepository->getById($productId, false, $storeId);

        $status = $product->getStatus();
        $isInStock = $product->isAvailable();

        if ($status == Status::STATUS_DISABLED) {
            $httpResponseCode = $this->attributeHelper->getAttributeValue(
                $dbAdapter,
                Product::ENTITY,
                'redirect_disabled',
                $productId,
                $storeId,
                false
            );

            $redirectProductId = $this->attributeHelper->getAttributeValue(
                $dbAdapter,
                Product::ENTITY,
                'redirect_disabled_product_id',
                $productId,
                $storeId
            );

            if ($httpResponseCode == 1) {
                $request->setParam('id', $redirectProductId);
            } elseif ($httpResponseCode > 1) {
                /** @var Product $redirectProduct */
                $redirectProduct = $this->productRepository->getById($redirectProductId, false, $storeId);

                /** @var Redirect $resultRedirect */
                $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

                $redirectUrl = $redirectProduct->getProductUrl();

                $resultRedirect->setUrl($redirectUrl);
                $resultRedirect->setHttpResponseCode($httpResponseCode);

                return $resultRedirect;
            }
        } elseif (!$isInStock) {
            $httpResponseCode = $this->attributeHelper->getAttributeValue(
                $dbAdapter,
                Product::ENTITY,
                'redirect_out_of_stock',
                $productId,
                $storeId,
                false
            );

            $redirectProductId = $this->attributeHelper->getAttributeValue(
                $dbAdapter,
                Product::ENTITY,
                'redirect_out_of_stock_product_id',
                $productId,
                $storeId
            );

            if ($httpResponseCode == 1) {
                $request->setParam('id', $redirectProductId);
            } elseif ($httpResponseCode > 1) {
                /** @var Product $redirectProduct */
                $redirectProduct = $this->productRepository->getById($redirectProductId, false, $storeId);

                /** @var Redirect $resultRedirect */
                $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

                $redirectUrl = $redirectProduct->getProductUrl();

                $resultRedirect->setUrl($redirectUrl);
                $resultRedirect->setHttpResponseCode($httpResponseCode);

                return $resultRedirect;
            }
        }

        return $proceed();
    }
}
