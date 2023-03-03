<?php

/**
 * Copyright © Rob Aimes - https://aimes.dev/
 * https://github.com/robaimes
 */

namespace Aimes\QualityPatchesUi\Model\Source;

use Aimes\QualityPatchesUi\Model\QualityPatches;
use Magento\Framework\Data\OptionSourceInterface;

class PatchFilter implements OptionSourceInterface
{
    /** @var QualityPatches */
    private QualityPatches $qualityPatches;

    /** @var string */
    private string $filterKey;

    /**
     * @param QualityPatches $qualityPatches
     * @param string $filterKey
     */
    public function __construct(
        QualityPatches $qualityPatches,
        string $filterKey
    ) {
        $this->qualityPatches = $qualityPatches;
        $this->filterKey = $filterKey;
    }

    /**
     * Get statuses available from patch list
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        $patches = $this->qualityPatches->getAllPatches();
        $options = [];
        $values = [];

        foreach ($patches as $patch) {
            $values[] = $patch[$this->filterKey];
        }

        $values = array_unique($values);

        foreach ($values as $value) {
            $options[] = [
                'label' => $value,
                'value' => $value,
            ];
        }

        return $options;
    }
}
