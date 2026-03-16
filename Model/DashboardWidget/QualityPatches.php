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
    private const string PROP_CATEGORIES = 'patch_categories';
    private const string PROP_STATUSES = 'patch_statuses';

    /** @var string */
    protected string $id = 'aimes_quality_patches_ui';

    /**
     * @param WidgetAuth $widgetAuth
     * @param WidgetConfig $widgetConfig
     * @param PatchesModel $patches
     * @param string $id
     * @param array $data
     */
    public function __construct(
        WidgetAuth $widgetAuth,
        WidgetConfig $widgetConfig,
        private readonly PatchesModel $patches,
        string $id = '',
        array $data = []
    ) {
        parent::__construct($widgetAuth, $widgetConfig, $id, $data);
    }

    /**
     * @return array
     */
    public function getConfigurableProperties(): array
    {
        $patches = $this->patches->getAllPatches();

        return array_merge(
            parent::getConfigurableProperties(),
            [
                self::PROP_CATEGORIES => [
                    'label' => __('Patch Category'),
                    'input' => [
                        'type' => 'select',
                        'options' => $this->getUniqueValues($patches, 'Category'),
                        'attributes' => [
                            'multiple' => true,
                            'required' => true,
                            'size' => 15,
                        ],
                    ],
                ],
                self::PROP_STATUSES => [
                    'label' => __('Patch Status'),
                    'note' => __('Status reported from the Magento Quality Patch tools may be inaccurate if your installed versions are outdated, or patches are applied via other means.'),
                    'input' => [
                        'type' => 'select',
                        'options' => $this->getUniqueValues($patches, 'Status'),
                        'attributes' => [
                            'multiple' => true,
                            'required' => true,
                            'size' => 3,
                        ],
                    ],
                ]
            ]
        );
    }

    /**
     * @param WidgetInstanceInterface $widgetInstance
     *
     * @return array
     */
    public function getDisplayData(WidgetInstanceInterface $widgetInstance)
    {
        $allPatches = $this->patches->getAllPatches();
        $categories = $widgetInstance->getPropertyValue(self::KEY_CONFIGURABLE_PROPERTIES, self::PROP_CATEGORIES);
        $statuses = $widgetInstance->getPropertyValue(self::KEY_CONFIGURABLE_PROPERTIES, self::PROP_STATUSES) ?? [];
        $filteredPatches = array_filter($allPatches, function ($patch) use ($categories, $statuses) {
            return !empty(array_intersect(explode("\n", $patch['Category']), $categories))
                && in_array($patch['Status'], $statuses);
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

    /**
     * @param array $patches
     * @param string $key
     *
     * @return array
     */
    public function getUniqueValues(array $patches, string $key): array
    {
        $values = array_unique(explode("\n", implode("\n", array_column($patches, $key))));
        sort($values);

        return array_map(fn ($value) => ['label' => $value, 'value' => $value], $values);
    }
}
