<?php

/**
 * Copyright © Rob Aimes - https://aimes.dev/
 * https://github.com/robaimes
 */

declare(strict_types=1);

namespace Aimes\QualityPatchesUi\Model;

use Magento\CloudPatches\App\ContainerFactory;
use Magento\CloudPatches\Application;
use Magento\CloudPatches\Command\Process\ShowStatus;
use Magento\CloudPatches\Command\Status;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Serialize\Serializer\Json;
use ReflectionClass;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class QualityPatches
{
    /** @var ContainerFactory */
    private ContainerFactory $containerFactory;

    /** @var Filesystem */
    private Filesystem $filesystem;

    /** @var Json */
    private Json $json;

    /** @var array|null */
    private ?array $patches = null;

    /**
     * @param ContainerFactory $containerFactory
     * @param Filesystem $filesystem
     * @param Json $json
     */
    public function __construct(
        ContainerFactory $containerFactory,
        Filesystem $filesystem,
        Json $json,
    ) {
        $this->containerFactory = $containerFactory;
        $this->filesystem = $filesystem;
        $this->json = $json;
    }

    /**
     * Get all patches for the current Magento/software version
     *
     * @return array
     * @throws LocalizedException
     */
    public function getAllPatches()
    {
        if ($this->patches !== null) {
            return $this->patches;
        }

        // Since the quality patches tool is outside the Magento application, we have to emulate the CLI command
        $container = $this->containerFactory->create([
            'basePath' => $this->getCloudPatchesBaseDir(),
            'magentoBasePath' => $this->getMagentoRootDir(),
        ]);

        $application = new Application($container);

        $input = new ArrayInput([
            'command' => Status::NAME,
            '--format' => ShowStatus::FORMAT_JSON,
        ]);
        $input->setInteractive(false);

        $output = new BufferedOutput();

        try {
            $application->get(Status::NAME)->run($input, $output);
            $patchInfo = $this->json->unserialize($output->fetch());

            /** @var array $patchInfo */
            foreach ($patchInfo as &$patch) {
                $patch['Title'] = $this->removeNewlines($patch['Title']);
            }

            $this->patches = $patchInfo;
        } catch (ExceptionInterface) {
            $this->patches = [];
        }

        return $this->patches;
    }

    /**
     * Get patch by name/ID for the current Magento/software version
     *
     * @param string $id
     *
     * @return array|null
     * @throws LocalizedException
     */
    public function getPatchById(string $id): ?array
    {
        $patches = $this->getAllPatches();

        return array_find($patches, fn ($patch) => $patch['Id'] === $id);
    }

    /**
     * Get root path of the magento cloud patches package
     *
     * @return string
     * @throws LocalizedException
     */
    private function getCloudPatchesBaseDir(): string
    {
        $applicationReflection = new ReflectionClass(Application::class);
        $filepath = $applicationReflection->getFileName();

        if ($filepath === false) {
            throw new LocalizedException(__('Could not find Cloud Patches application'));
        }

        // phpcs:ignore Magento2.Functions.DiscouragedFunction.DiscouragedWithAlternative
        return dirname($filepath, 2);
    }

    /**
     * Get Magento root directory
     *
     * @return string
     */
    private function getMagentoRootDir(): string
    {
        return $this->filesystem->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath();
    }

    /**
     * Remove newline characters from the JSON output
     *
     * @param string $string
     *
     * @return string
     */
    private function removeNewlines(string $string): string
    {
        return str_replace("\n", '', $string);
    }
}
