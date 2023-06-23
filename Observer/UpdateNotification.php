<?php
/**
 * Copyright © Rob Aimes - https://aimes.dev/
 * https://github.com/robaimes
 */

namespace Aimes\QualityPatchesUi\Observer;

use Composer\InstalledVersions;
use Composer\Semver\Comparator;
use Exception;
use Magento\Framework\Composer\MagentoComposerApplicationFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;

class UpdateNotification implements ObserverInterface
{
    private ManagerInterface $messageManager;
    private MagentoComposerApplicationFactory $composerApplicationFactory;
    private Json $json;

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
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer): void
    {
        $packageReleaseNotesMapping = [
            'magento/magento-cloud-patches' => 'https://experienceleague.adobe.com/docs/commerce-cloud-service/user-guide/release-notes/cloud-patches.html',
            'magento/quality-patches' => 'https://experienceleague.adobe.com/docs/commerce-operations/tools/quality-patches-tool/release-notes.html',
        ];

        foreach ($packageReleaseNotesMapping as $packageName => $releaseNotesUrl) {
            $packageInfo = $this->getPackageInfo($packageName);
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
     * @return array
     */
    private function getPackageInfo(string $packageName): array
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
            return [];
        }

        return [
            'current_version' => $currentVersion,
            'latest_version' => $latestVersion,
        ];
    }
}
