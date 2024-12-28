<?php
/**
 * Copyright © Rob Aimes - https://aimes.dev/
 * https://github.com/robaimes
 */

namespace Aimes\QualityPatchesUi\Model;

use Aimes\Substratum\Model\ComposerVersion;
use Magento\Framework\Message\ManagerInterface;;

class UpdateNotification
{
    public function __construct(
        private readonly ManagerInterface $messageManager,
        private readonly ComposerVersion $composerVersioning,
    ) {
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
            $isUpdateAvailable = $this->composerVersioning->isUpdateAvailable($packageName);

            if (!$isUpdateAvailable) {
                continue;
            }

            $packageInfo = $this->composerVersioning->getPackageInfo($packageName);
            $packageInfo['release_notes_url'] = $releaseNotesUrl;

            $this->messageManager->addComplexNoticeMessage(
                'qualityPatchesUpdateNotification',
                $packageInfo
            );
        }
    }
}
