<?php

declare(strict_types=1);

namespace Reang\ExamBeReang\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Swiftoms\General\Helper\GraphQlSearchCriteria;
use Swiftoms\Product\Model\ProductRepository;
use Magento\Catalog\Model\Product\Type;

class GetProductReang implements ResolverInterface
{
    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var CompanyRepositoryInterface
     */
    protected $companyRepository;

    /**
     * @var GraphQlSearchCriteria
     */
    protected $searchCriteriaHelper;

    /**
     * @var Type
     */
    protected $type;

    /**
     * @param ProductRepository $productRepository
     * @param GraphQlSearchCriteria $searchCriteriaHelper
     * @param Type $type
     */
    public function __construct(
        ProductRepository $productRepository,
        GraphQlSearchCriteria $searchCriteriaHelper,
        Type $type
    ) {
        $this->productRepository = $productRepository;
        $this->searchCriteriaHelper = $searchCriteriaHelper;
        $this->type = $type;
    }

    /**
     * @inheritDoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ) {
        /** @var ContextInterface $context */
        if ($args['currentPage'] < 1) {
            throw new GraphQlInputException(__('currentPage value must be greater than 0.'));
        }

        if ($args['pageSize'] < 1) {
            throw new GraphQlInputException(__('pageSize value must be greater than 0.'));
        }

        $searchCriteria = $this->searchCriteriaHelper->build($args);
        $searchResult = $this->productRepository->getList($searchCriteria);

        $items = [];
        foreach ($searchResult->getItems() as $key => $item) {
            $products[] = [
                'entity_id' => $item->getId(),
                'sku' => $item->getSku(),
                'name' => $item->getName(),
                'price' => $item->getPrice(),
                'status' => $item->getStatus(),
                'description' => $item->getDescription(),
                'short_description' => $item->getShortDescription(),
                'weight' => $item->getWeight(),
                'dimension_package_height' => $item->getDimensionPackageHeight(),
                'dimension_package_length' => $item->getDimensionPackageLength(),
                'dimension_package_width' => $item->getDimensionPackageWidth()
            ];
        }

        $data = [
            'total_count' => $searchResult->getTotalCount(),
            'items' => $products,
            'page_info' => [
                'page_size' => $searchCriteria->getPageSize(),
                'current_page' => $searchCriteria->getCurrentPage(),
                'total_pages' => ceil($searchResult->getTotalCount() / $searchCriteria->getPageSize())
            ]
        ];

        return $data;
    }
}
