<?php
/**
 * Copyright © Rob Aimes - https://aimes.dev/
 * https://github.com/robaimes
 */

namespace Aimes\QualityPatchesUi\Model;

use Composer\InstalledVersions;
use Composer\Semver\Comparator;
use Exception;
use Magento\Framework\Composer\MagentoComposerApplicationFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;

class UpdateNotification
{
    /** @var ManagerInterface */
    private ManagerInterface $messageManager;

    /** @var MagentoComposerApplicationFactory */
    private MagentoComposerApplicationFactory $composerApplicationFactory;

    /** @var Json */
    private Json $json;

    /**
     * @param ManagerInterface $messageManager
     * @param MagentoComposerApplicationFactory $composerApplicationFactory
     * @param Json $json
     */
    public function __construct(
        ManagerInterface $messageManager,
        MagentoComposerApplicationFactory $composerApplicationFactory,
        Json $json
    ) {
        $this->messageManager = $messageManager;
        $this->composerApplicationFactory = $composerApplicationFactory;
        $this->json = $json;
    }

    /**
     * Add messages to inform the user that an update is available, and therefore more patches that may assist
     *
     * @return void
     */
    public function addMessages(): void
    {
        $packageReleaseNotesMapping = [
            'magento/magento-cloud-patches' => 'https://experienceleague.adobe.com/docs/commerce-cloud-service/user-guide/release-notes/cloud-patches.html',
            'magento/quality-patches' => 'https://experienceleague.adobe.com/docs/commerce-operations/tools/quality-patches-tool/release-notes.html',
        ];

        foreach ($packageReleaseNotesMapping as $packageName => $releaseNotesUrl) {
            $packageInfo = $this->getPackageInfo($packageName);

            if (!$packageInfo) {
                continue;
            }

            $isOutdated = Comparator::greaterThan($packageInfo['latest_version'], $packageInfo['current_version']);

            if (!$isOutdated) {
                continue;
            }

            $this->messageManager->addComplexNoticeMessage(
                'composerPackageUpdateNotification',
                [
                    'package_name' => $packageName,
                    'current_version' => $packageInfo['current_version'],
                    'latest_version' => $packageInfo['latest_version'],
                    'release_notes_url' => $releaseNotesUrl,
                ]
            );
        }
    }

    /**
     * @param string $packageName
     *
     * @return array|null
     */
    private function getPackageInfo(string $packageName): ?array
    {
        $application = $this->composerApplicationFactory->create();
        $arguments = [
            'command' => 'outdated',
            'package' => $packageName,
            '--format' => 'json',
        ];

        try {
            $currentVersion = InstalledVersions::getPrettyVersion($packageName);
            $composerResult = $this->json->unserialize($application->runComposerCommand($arguments));
            $latestVersion = $composerResult['latest'];
        } catch (Exception $exception) {
            return null;
        }

        return [
            'current_version' => $currentVersion,
            'latest_version' => $latestVersion,
        ];
    }
}
