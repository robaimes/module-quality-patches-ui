<?php

/**
 * Copyright © Rob Aimes - https://aimes.dev/
 * https://github.com/robaimes
 */

declare(strict_types=1);

namespace Aimes\QualityPatchesUi\Model;

use Magento\CloudPatches\Application;
use Magento\CloudPatches\Command\Process\ShowStatus;
use Magento\CloudPatches\App\ContainerFactory;
use Magento\CloudPatches\Command\Status;
use Magento\Framework\App\Filesystem\DirectoryList;
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
        Json $json
    ) {
        $this->containerFactory = $containerFactory;
        $this->filesystem = $filesystem;
        $this->json = $json;
    }

    /**
     * Get all patches for the current Magento/software version
     *
     * @return array
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
            $this->patches = $this->json->unserialize($output->fetch());
        } catch (ExceptionInterface $exception) {
            $this->patches = [];
        }

        return $this->patches;
    }

    /**
     * Get root path of the magento cloud patches package
     *
     * @return string
     */
    private function getCloudPatchesBaseDir(): string
    {
        $applicationReflection = new ReflectionClass(Application::class);
        $filepath = $applicationReflection->getFileName();

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
     * Get patch by name/ID for the current Magento/software version
     *
     * @param string $id
     *
     * @return array|null
     */
    public function getPatchById(string $id): ?array
    {
        $patches = $this->getAllPatches();

        foreach ($patches as $key => $patch) {
            if ($patch['Id'] === $id) {
                return $patches[$key];
            }
        }

        return null;
    }
}
