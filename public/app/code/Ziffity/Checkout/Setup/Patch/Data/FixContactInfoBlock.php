<?php
namespace Ziffity\Checkout\Setup\Patch\Data;

use Magento\Cms\Api\Data\BlockInterface;
use Magento\Cms\Model\BlockFactory;
use Magento\Cms\Model\ResourceModel\Block\CollectionFactory as BlockCollection;
use Magento\Cms\Model\BlockRepository;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class FixContactInfoBlock implements DataPatchInterface
{

    /**
     * Enable Cms Block
     */
    const ENABLE = 1;

    /**
     * @var BlockCollection
     */
    protected $blockCollection;

    /**
     * @var BlockRepository
     */
    private $blockRepository;

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;
    /**
     * @var BlockFactory
     */
    private $blockFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param BlockFactory $blockFactory
     * @param BlockRepository $blockRepository
     * @param BlockCollection $blockCollection
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        BlockFactory $blockFactory,
        BlockRepository $blockRepository,
        BlockCollection $blockCollection
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->blockFactory = $blockFactory;
        $this->blockRepository = $blockRepository;
        $this->blockCollection = $blockCollection;
    }

    /**
     * This patch fixes the issue with the particular block_id = 19 in cms_block.
     *
     * @return void
     * @throws CouldNotDeleteException
     * @throws CouldNotSaveException
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();
        $result = $this->deleteBlockFromTable('success-page-contact-info');
        if ($result) {
            $blockData = [
                'title' => 'Contact Information',
                'identifier' => 'success-page-contact-info',
                'content' => '<div class="contact-info">
                            <strong>DisplayFrames Customer Service</strong>
                            <p><strong>Phone:</strong> 123-456-7890</p>
                            <p><strong>Email:</strong> contactus@displayframes.com</p>
                        </div>',
                'is_active' => 1,
                'stores' => [0],
            ];
            /** @var BlockInterface $newBlock */
            $newBlock = $this->blockFactory->create();
            $newBlock->setData($blockData);
            $newBlock->setIsActive(self::ENABLE);
            $newBlock->setStores([0]);
            $newBlock->setSortOrder(0);
            $this->blockRepository->save($newBlock);
        }
        $this->moduleDataSetup->endSetup();
    }

    /**
     * This function deletes the duplicate entry from the cms_block table.
     *
     * @param string $identifier
     * @return false|true
     * @throws CouldNotDeleteException
     */
    public function deleteBlockFromTable($identifier)
    {
        $collection = $this->blockCollection->create();
        $collection->addFieldToFilter('identifier',$identifier);
        if ($collection->getFirstItem()) {
            $model = $collection->getFirstItem();
            $this->blockRepository->delete($model);
            return true;
        }
        return false;
    }

    public static function getDependencies()
    {
        return [AddContactInfo::class];
    }

    public function getAliases()
    {
        return [];
    }
}
