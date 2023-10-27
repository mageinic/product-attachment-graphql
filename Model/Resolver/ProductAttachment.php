<?php
/**
 * MageINIC
 * Copyright (C) 2023 MageINIC <support@mageinic.com>
 *
 * NOTICE OF LICENSE
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see https://opensource.org/licenses/gpl-3.0.html.
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category MageINIC
 * @package MageINIC_ProductAttachmentGraphQl
 * @copyright Copyright (c) 2023 MageINIC (https://www.mageinic.com/)
 * @license https://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author MageINIC <support@mageinic.com>
 */

namespace MageINIC\ProductAttachmentGraphQl\Model\Resolver;

use MageINIC\ProductAttachment\Api\Data\ProductAttachmentInterface;
use MageINIC\ProductAttachment\Api\ProductAttachmentRepositoryInterface as AttachmentRepository;
use Magento\Framework\Api\SearchCriteriaBuilder as SearchCriteria;
use Magento\Framework\App\Area;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Api\SortOrderBuilder;

/**
 * ProductAttachment Resolver class
 */
class ProductAttachment implements ResolverInterface
{
    /**
     * Media path
     */
    public const MEDIA_FOLDER = 'mageINIC/product_attachments/';

    /**
     * @var SearchCriteria
     */
    protected SearchCriteria $searchCriteriaBuilder;

    /**
     * @var Repository
     */
    protected Repository $assetRepo;

    /**
     * @var AttachmentRepository
     */
    private AttachmentRepository $attachmentRepository;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var File
     */
    private File $file;

   /**
    * @var SortOrderBuilder
    */
    private SortOrderBuilder $sortOrderBuilder;

    /**
     * ProductAttachment constructor.
     *
     * @param StoreManagerInterface $storeManager
     * @param SearchCriteria $searchCriteriaBuilder
     * @param AttachmentRepository $attachmentRepository
     * @param File $file
     * @param Repository $assetRepo
     * @param SortOrderBuilder $sortOrderBuilder
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        SearchCriteria        $searchCriteriaBuilder,
        AttachmentRepository  $attachmentRepository,
        File                  $file,
        Repository            $assetRepo,
        SortOrderBuilder      $sortOrderBuilder
    ) {
        $this->storeManager = $storeManager;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->attachmentRepository = $attachmentRepository;
        $this->assetRepo = $assetRepo;
        $this->file = $file;
        $this->sortOrderBuilder = $sortOrderBuilder;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field       $field,
        $context,
        ResolveInfo $info,
        array       $value = null,
        array       $args = null
    ) {

        $attachment = [];
        try {
            $product = $value['model'];

            $baseUrl = $this->storeManager->getStore()
                    ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . self::MEDIA_FOLDER;
            $storeId = $this->storeManager->getStore()->getId();

                $sortOrder = $this->sortOrderBuilder
                    ->setField('sort_order')
                    ->setDirection('ASC')
                    ->create();
                $searchCriteria = $this->searchCriteriaBuilder
                    ->addFilter('store_id', $storeId)
                    ->addFilter('product_id', $product->getId(), 'in')
                    ->addFilter('active', true)
                    ->addSortOrder($sortOrder)
                    ->create();
                $attachmentDetails = $this->attachmentRepository->getList($searchCriteria);
            foreach ($attachmentDetails->getItems() as $item) {
                $attachment['items'][] = [
                    'title' => $item->getName(),
                    'description' => $item->getDescription(),
                    'icon' => $this->getFileIcon($item),
                    'url' => $baseUrl . $item->getUploadedFile()
                ];
            }
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }

        return $attachment;
    }

    /**
     * Get File Icon
     *
     * @param ProductAttachmentInterface $item
     * @return string
     */
    public function getFileIcon(ProductAttachmentInterface $item): string
    {
        $file = $item->getUploadedFile();
        $fileInfo = $this->file->getPathInfo($file);
        $type = $fileInfo['extension'];
        $area = ['area' => Area::AREA_FRONTEND];
        $fileTypes = [
            'csv' => 'MageINIC_ProductAttachment/images/csv.svg',
            'pdf' => 'MageINIC_ProductAttachment/images/pdf.svg',
            'pptx' => 'MageINIC_ProductAttachment/images/ppt.svg',
            'txt' => 'MageINIC_ProductAttachment/images/txt.svg',
            'doc' => 'MageINIC_ProductAttachment/images/word.svg'
        ];

        return $this->assetRepo->getUrlWithParams($fileTypes[$type] ?? '', $area);
    }
}
