<?php

/**
 * Copyright © Rob Aimes - https://aimes.dev/
 * https://github.com/robaimes
 */

namespace Aimes\QualityPatchesUi\Ui\DataProvider;

use Aimes\QualityPatchesUi\Model\QualityPatches;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteria;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Collection;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider;

class QualityPatchesProvider extends DataProvider
{
    /** @var QualityPatches */
    private QualityPatches $qualityPatches;

    /** @var array */
    private array $patchData = [];

    /**
     * @param $name
     * @param $primaryFieldName
     * @param $requestFieldName
     * @param ReportingInterface $reporting
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param RequestInterface $request
     * @param FilterBuilder $filterBuilder
     * @param QualityPatches $qualityPatches
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        ReportingInterface $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        QualityPatches $qualityPatches,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $reporting,
            $searchCriteriaBuilder,
            $request,
            $filterBuilder,
            $meta,
            $data
        );

        $this->qualityPatches = $qualityPatches;
    }

    /**
     * Get 'static' data from Magento cloud patches output
     *
     * Pagination, sorting and filtering is normally done via collection.
     *
     * @return array
     */
    public function getData(): array
    {
        $patches = $this->patchData;

        if (!$patches) {
            $this->patchData = $patches = $this->qualityPatches->getAllPatches();
        }

        $searchCriteria = $this->getSearchCriteria();

        $this->filterResults($patches, $searchCriteria);
        $this->sortResults($patches, $searchCriteria);

        return [
            'totalRecords' => count($patches),
            'items' => $this->getPaginationItems($patches, $searchCriteria),
        ];
    }

    /**
     * Filter results manually as we have no 'resource' or database
     *
     * @param array $results
     * @param SearchCriteria $searchCriteria
     *
     * @return void
     * @see Collection::addFieldToFilter
     */
    private function filterResults(array &$results, SearchCriteria $searchCriteria): void
    {
        $filterGroups = $searchCriteria->getFilterGroups();
        $filters = [];

        // This grid is assumed to match other grid components and as such functions as logical AND when filtering
        foreach ($filterGroups as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                $filters[] = $filter;
            }
        }

        /** @var Filter[] $filters */
        foreach ($filters as $filter) {
            foreach ($results as $index => $result) {
                if (!$result[$filter->getField()]) {
                    unset($results[$index]);
                }

                $filterValue = strtolower($filter->getValue());
                $dataValue = strtolower($result[$filter->getField()]);

                switch ($filter->getConditionType()) {
                    case 'eq':
                        if ($dataValue === $filterValue) {
                            continue 2;
                        }

                        break;
                    case 'like':
                        if (strpos($dataValue, trim($filterValue, '%')) !== false) {
                            continue 2;
                        }

                        break;
                    default:
                        break;
                }

                unset($results[$index]);
            }
        }
    }

    /**
     * Sort grid results manually as we have no 'resource' or database
     *
     * @param array $results
     * @param SearchCriteria $searchCriteria
     *
     * @return void
     */
    private function sortResults(array &$results, SearchCriteria $searchCriteria): void
    {
        $sortOrders = $searchCriteria->getSortOrders();

        foreach ($sortOrders as $sortOrder) {
            if (!$sortOrder->getField()) {
                continue;
            }

            usort($results, function ($itemA, $itemB) use ($sortOrder) {
                $comparisonFieldA = $itemA[$sortOrder->getField()];
                $comparisonFieldB = $itemB[$sortOrder->getField()];

                if (strtoupper($sortOrder->getDirection()) === Collection::SORT_ORDER_ASC) {
                    return $comparisonFieldA <=> $comparisonFieldB;
                } else {
                    return $comparisonFieldB <=> $comparisonFieldA;
                }
            });
        }
    }

    /**
     * Paginate results manually as we have no 'resource' or database
     *
     * @param array $items
     * @param SearchCriteria $searchCriteria
     *
     * @return array
     */
    private function getPaginationItems(array $items, SearchCriteria $searchCriteria): array
    {
        $pageSize = $searchCriteria->getPageSize();
        $pageCurrent = $searchCriteria->getCurrentPage();
        $pageOffset = ($pageCurrent - 1) * $pageSize;

        return array_slice($items, $pageOffset, $pageSize);
    }
}
