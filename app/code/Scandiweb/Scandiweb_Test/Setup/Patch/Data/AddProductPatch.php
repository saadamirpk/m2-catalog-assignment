<?PHP
	declare(strict_types=1);

    namespace Scandiweb\Scandiweb_Test\Setup\Patch\Data;

    use Magento\Catalog\Api\Data\ProductInterfaceFactory;
    use Magento\Catalog\Api\ProductRepositoryInterface;
    use Magento\Catalog\Model\Product;
    use Magento\Catalog\Model\Product\Attribute\Source\Status;
    use Magento\Catalog\Model\Product\Type;
    use Magento\Catalog\Model\Product\Visibility;
    use Magento\Eav\Setup\EavSetup;
    use Magento\Framework\App\State;
    use Magento\Framework\Setup\ModuleDataSetupInterface;
    use Magento\Framework\Setup\Patch\DataPatchInterface;
    use Magento\Framework\Setup\Patch\PatchRevertableInterface;
    use Magento\Store\Model\StoreManagerInterface;
    use Magento\InventoryApi\Api\Data\SourceItemInterface;
    use Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory;
    use Magento\InventoryApi\Api\SourceItemsSaveInterface;

    class AddProductPatch
        implements DataPatchInterface, PatchRevertableInterface
    {
        protected ModuleDataSetupInterface $setup;
        protected ProductInterfaceFactory $productInterfaceFactory;
        protected ProductRepositoryInterface $productRepository;
        protected State $appState;
        protected EavSetup $eavSetup;
        protected StoreManagerInterface $storeManager;
        protected SourceItemInterfaceFactory $sourceItemFactory;
        protected SourceItemsSaveInterface $sourceItemsSaveInterface;
        protected CategoryLinkManagementInterface $categoryLink;
        protected array $sourceItems = [];

        public function __construct(
            ModuleDataSetupInterface $setup,
            ProductInterfaceFactory $productInterfaceFactory,
            ProductRepositoryInterface $productRepository,
            State $appState,
            StoreManagerInterface $storeManager,
            EavSetup $eavSetup,
            SourceItemInterfaceFactory $sourceItemFactory,
            SourceItemsSaveInterface $sourceItemsSaveInterface,
            CategoryLinkManagementInterface $categoryLink
            ) {
                $this->appState = $appState;
                $this->productInterfaceFactory = $productInterfaceFactory;
                $this->productRepository = $productRepository;
                $this->setup = $setup;
                $this->eavSetup = $eavSetup;
                $this->storeManager = $storeManager;
                $this->sourceItemFactory = $sourceItemFactory;
                $this->sourceItemsSaveInterface = $sourceItemsSaveInterface;
                $this->categoryLink = $categoryLink;
        }

        public function apply()
        {
            $this->appState->emulatedAreaCode('adminHtml',[$this,'execute']);
        }

        public function execute()
        {
            //create product
            $product = $this->productInterfaceFactory->create();

            //check if already exist
            if ($product->getIdBySku('sample-product123')) {
                return;
            }

            //get eav id
            $attributeSetId = $this->eavSetup->getAttributeSetId(Product::ENTITY, 'Default')

            //default attributes

            $product->setTypeId(Type::TYPE_SIMPLE)
                ->setAttributeSetId($attributeSetId)
                ->setName('Sample Product')
                ->setSku('sample-product123')
                ->setUrlKey('sample-product123')
                ->setPrice(14.99)
                ->setVisibility(Visibility::VISIBILITY_BOTH)
                ->setStatus(Status::STATUS_ENABLED);

            //save product
            $product = $this->productRepository->save($product);

            //attach to category
            $this->categoryLink->assignProductToCategories($product->getSku(), [2]);
        }

        public static function getDependencies()
        {
            return [
                SomeDependency::class
            ];
        }

        public function revert()
        {
        }

        public function getAliases()
        {
            return [];
        }
    }