<?php

/**
 * Copyright © Rob Aimes - https://aimes.dev/
 * https://github.com/robaimes
 */

declare(strict_types=1);

namespace Aimes\QualityPatchesUi\Model\Widget;

use Aimes\QualityPatchesUi\Model\QualityPatches as PatchesModel;
use Hyva\AdminDashboardApi\Api\ConfigurationKeys;
use Hyva\AdminDashboardApi\Api\V1\WidgetContextInterface;
use Hyva\AdminDashboardApi\Api\V1\WidgetInstanceInterface;
use Hyva\AdminDashboardApi\Api\V1\WidgetTypeInterface;
use Magento\Framework\Phrase;

class QualityPatches implements WidgetTypeInterface
{
    private const string PROP_CATEGORIES = 'patch_categories';
    private const string PROP_STATUSES = 'patch_statuses';

    /**
     * @param PatchesModel $patches
     */
    public function __construct(
        private readonly PatchesModel $patches,
    ) {
    }

    /**
     * @param WidgetContextInterface $ctx
     *
     * @return array
     */
    public function getConfigurableProperties(
        WidgetContextInterface $ctx
    ): array {
        $patches = $this->patches->getAllPatches();

        return array_merge(
            $ctx->getConfigurableProperties(),
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
     * @param WidgetContextInterface $ctx
     *
     * @return array
     */
    public function getDisplayProperties(WidgetContextInterface $ctx): array
    {
        return $ctx->getDisplayProperties();
    }

    /**
     * @param WidgetContextInterface $ctx
     * @param WidgetInstanceInterface $widgetInstance
     *
     * @return mixed
     */
    public function getDisplayData(
        WidgetContextInterface $ctx,
        WidgetInstanceInterface $widgetInstance
    ): mixed {
        $allPatches = $this->patches->getAllPatches();
        $categories = $widgetInstance->getPropertyValue(ConfigurationKeys::CONFIGURABLE_PROPERTIES, self::PROP_CATEGORIES);
        $statuses = $widgetInstance->getPropertyValue(ConfigurationKeys::CONFIGURABLE_PROPERTIES, self::PROP_STATUSES) ?? [];
        $filteredPatches = array_filter($allPatches, function ($patch) use ($categories, $statuses) {
            return !empty(array_intersect(explode("\n", $patch['Category']), $categories))
                && in_array($patch['Status'], $statuses);
        });

        $data = [
            'headings' => $this->getHeadings(),
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
     * @param WidgetContextInterface $ctx
     * @param WidgetInstanceInterface|null $widgetInstance
     *
     * @return Phrase
     */
    public function getTitle(WidgetContextInterface $ctx, ?WidgetInstanceInterface $widgetInstance): Phrase
    {
        return $ctx->getTitle();
    }

    /**
     * @param WidgetContextInterface $ctx
     * @param WidgetInstanceInterface|null $widgetInstance
     *
     * @return array
     */
    public function getTrailingAction(WidgetContextInterface $ctx, ?WidgetInstanceInterface $widgetInstance): array
    {
        return $ctx->getTrailingAction();
    }

    /**
     * @param WidgetContextInterface $ctx
     * @param WidgetInstanceInterface|null $widgetInstance
     *
     * @return bool
     */
    public function isAllowed(WidgetContextInterface $ctx, ?WidgetInstanceInterface $widgetInstance): bool
    {
        return $ctx->isAllowed($widgetInstance);
    }

    /**
     * @param WidgetContextInterface $ctx
     * @param WidgetInstanceInterface $widgetInstance
     *
     * @return WidgetInstanceInterface]
     */
    public function beforeSave(WidgetContextInterface $ctx, WidgetInstanceInterface $widgetInstance): WidgetInstanceInterface
    {
        return $widgetInstance;
    }

    /**
     * @param WidgetContextInterface $ctx
     * @param WidgetInstanceInterface $widgetInstance
     *
     * @return WidgetInstanceInterface
     */
    public function afterSave(WidgetContextInterface $ctx, WidgetInstanceInterface $widgetInstance): WidgetInstanceInterface
    {
        return $widgetInstance;
    }

    /**
     * @return array
     */
    private function getHeadings(): array
    {
        return [
            __('Patch ID'),
            __('Status'),
            __('Category'),
            __('Title'),
        ];
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
