<?php

/**
 * Copyright © Rob Aimes - https://aimes.dev/
 * https://github.com/robaimes
 */

namespace Aimes\QualityPatchesUi\Ui\DataProvider;

use Aimes\QualityPatchesUi\Model\QualityPatches;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider;

class QualityPatchesProvider extends DataProvider
{
    /** @var array */
    private array $patchData = [];

    /** @var QualityPatches */
    private QualityPatches $qualityPatches;

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
     * Filtering and sorting are currently not supported due to this data not being collection based.
     *
     * @return array
     */
    public function getData(): array
    {
        $patches = $this->patchData;

        if (!$patches) {
            $this->patchData = $patches = $this->qualityPatches->getAllPatches();
        }

        return [
            'totalRecords' => count($patches),
            'items' => $this->getPaginationItems($patches),
        ];
    }

    /**
     * Because our data is static (i.e. not a collection) we need to manually do the pagination
     *
     * @param array $items
     *
     * @note Shamelessly borrowed from Magento core
     * @see \Magento\ImportExport\Ui\DataProvider\ExportFileDataProvider::getData
     * @return array
     */
    private function getPaginationItems(array $items): array
    {
        $paging = $this->request->getParam('paging');
        $pageSize = (int) ($paging['pageSize'] ?? 0);
        $pageCurrent = (int) ($paging['current'] ?? 0);
        $pageOffset = ($pageCurrent - 1) * $pageSize;

        return array_slice($items, $pageOffset, $pageSize);
    }
}
