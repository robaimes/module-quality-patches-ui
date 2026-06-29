<?php

/**
 * Copyright © Rob Aimes - https://aimes.dev/
 * https://github.com/robaimes
 */

declare(strict_types=1);

namespace Aimes\QualityPatchesUi\Controller\Adminhtml\Index;

use Aimes\QualityPatchesUi\Model\UpdateNotification;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    // phpcs:ignore PSR12.Properties.ConstantVisibility.NotFound
    const ADMIN_RESOURCE = 'Aimes_QualityPatchesUi::view';

    /** @var PageFactory */
    private PageFactory $resultPageFactory;

    /** @var UpdateNotification */
    private UpdateNotification $updateNotification;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param UpdateNotification $updateNotification
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        UpdateNotification $updateNotification,
    ) {
        parent::__construct($context);

        $this->resultPageFactory = $resultPageFactory;
        $this->updateNotification = $updateNotification;
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();

        $this->updateNotification->addMessages();

        /** @var Page $resultPage */
        $resultPage->setActiveMenu('Aimes_QualityPatchesUi::quality_patches');
        $resultPage->getConfig()
            ->getTitle()
            ->prepend((string)__('Magento Quality Patches'));

        return $resultPage;
    }
}
