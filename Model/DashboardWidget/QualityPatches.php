<?php
/**
 * Copyright © Rob Aimes - https://aimes.dev/
 * https://github.com/robaimes
 */
declare(strict_types=1);

namespace Aimes\QualityPatchesUi\Model\DashboardWidget;

use Aimes\QualityPatchesUi\Model\QualityPatches as PatchesModel;
use Hyva\AdminDashboardFramework\Model\WidgetAuth;
use Hyva\AdminDashboardFramework\Model\WidgetConfig;
use Hyva\AdminDashboardFramework\Model\WidgetInstance\WidgetInstanceInterface;
use Hyva\AdminDashboardFramework\Model\WidgetType\AbstractWidgetType;

class QualityPatches extends AbstractWidgetType
{
    protected string $id = 'aimes_quality_patches_ui';

    public function __construct(
        WidgetAuth $widgetAuth,
        WidgetConfig $widgetConfig,
        private readonly PatchesModel $patches,
        string $id = '',
        array $data = []
    ) {
        parent::__construct($widgetAuth, $widgetConfig, $id, $data);
    }

    public function getConfigurableProperties(): array
    {
        return array_merge(
            parent::getConfigurableProperties(),
            [
                'patch_categories' => [
                    'label' => __('Patch Category'),
                    'input' => [
                        'type' => 'select',
                        'options' => $this->getUniqueCategories($this->patches->getAllPatches()),
                        'attributes' => [
                            'multiple' => true,
                            'required' => true,
                            'size' => 15,
                        ],
                    ],
                ],
            ]
        );
    }

    public function getDisplayData(WidgetInstanceInterface $widgetInstance)
    {
        $allPatches = $this->patches->getAllPatches();
        $categories = $widgetInstance->getPropertyValue(self::KEY_CONFIGURABLE_PROPERTIES, 'patch_categories');
        $filteredPatches = array_filter($allPatches, function ($patch) use ($categories) {
            return !empty(array_intersect(explode("\n", $patch['Category']), $categories));
        });
        $data = [
            'headings' => [__('Patch ID'), __('Status'), __('Category'), __('Title')],
            'rows' => [],
        ];

        foreach ($filteredPatches as $patch) {
            if (!isset($patch['Id'], $patch['Status'], $patch['Category'], $patch['Title'])) {
                continue;
            }

            $data['rows'][] = [
                'values' => [
                    $patch['Id'],
                    $patch['Status'],
                    str_replace("\n", ', ', $patch['Category']),
                    $patch['Title'],
                ],
            ];
        }

        return $data;
    }

    public function getUniqueCategories(array $patches): array
    {
        $categories = array_unique(explode("\n", implode("\n", array_column($patches, 'Category'))));
        sort($categories);

        return array_map(fn ($category) => ['label' => $category, 'value' => $category], $categories);
    }
}
