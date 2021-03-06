<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model;

use Magento\Catalog\Model\Category;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Helper\ObjectManager;

class CategoryUrlPathGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator */
    protected $categoryUrlPathGenerator;

    /** @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $storeManager;

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $scopeConfig;

    /** @var \Magento\Catalog\Api\CategoryRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $categoryRepository;

    /** @var \Magento\Catalog\Model\Category|\PHPUnit_Framework_MockObject_MockObject */
    protected $category;

    protected function setUp()
    {
        $categoryMethods = [
            '__wakeup',
            'getUrlPath',
            'getParentId',
            'getLevel',
            'dataHasChangedFor',
            'getUrlKey',
            'getStoreId',
            'getId',
            'formatUrlKey',
            'getName',
            'isObjectNew'
        ];
        $this->category = $this->getMock('Magento\Catalog\Model\Category', $categoryMethods, [], '', false);
        $this->storeManager = $this->getMock('Magento\Store\Model\StoreManagerInterface');
        $this->scopeConfig = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->categoryRepository = $this->getMock('Magento\Catalog\Api\CategoryRepositoryInterface');

        $this->categoryUrlPathGenerator = (new ObjectManager($this))->getObject(
            'Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator',
            [
                'storeManager' => $this->storeManager,
                'scopeConfig' => $this->scopeConfig,
                'categoryRepository' => $this->categoryRepository,
            ]
        );
    }

    /**
     * @dataProvider getUrlPathDataProvider
     * @param int $parentId
     * @param string $urlPath
     * @param int $level
     * @param string $urlKey
     * @param bool $dataChangedForUrlKey
     * @param bool $dataChangedForParentId
     * @param string $result
     */
    public function testGetUrlPath(
        $parentId,
        $urlPath,
        $level,
        $urlKey,
        $dataChangedForUrlKey,
        $dataChangedForParentId,
        $result
    ) {
        $this->category->expects($this->any())->method('getParentId')->will($this->returnValue($parentId));
        $this->category->expects($this->any())->method('isObjectNew')->will($this->returnValue(false));
        $this->category->expects($this->any())->method('getLevel')->will($this->returnValue($level));
        $this->category->expects($this->any())->method('getUrlPath')->will($this->returnValue($urlPath));
        $this->category->expects($this->any())->method('getUrlKey')->will($this->returnValue($urlKey));
        $this->category->expects($this->any())->method('dataHasChangedFor')
            ->will($this->returnValueMap([['url_key', $dataChangedForUrlKey], ['parent_id', $dataChangedForParentId]]));

        $this->assertEquals($result, $this->categoryUrlPathGenerator->getUrlPath($this->category));
    }

    /**
     * @return array
     */
    public function getUrlPathDataProvider()
    {
        $noGenerationLevel = CategoryUrlPathGenerator::MINIMAL_CATEGORY_LEVEL_FOR_PROCESSING - 1;
        return [
            [Category::TREE_ROOT_ID, 'url-path', $noGenerationLevel, '', false, false, ''],
            ['parent_id', 'url-path', $noGenerationLevel, '', false, false, 'url-path'],
            ['parent_id', 'url-path', $noGenerationLevel, 'url-key', true, false, 'url-key'],
            ['parent_id', 'url-path', $noGenerationLevel, 'url-key', false, true, 'url-key'],
        ];
    }

    /**
     * @return array
     */
    public function getUrlPathWithParentDataProvider()
    {
        $requireGenerationLevel = CategoryUrlPathGenerator::MINIMAL_CATEGORY_LEVEL_FOR_PROCESSING;
        $noGenerationLevel = CategoryUrlPathGenerator::MINIMAL_CATEGORY_LEVEL_FOR_PROCESSING - 1;
        return [
            ['url-key', false, $requireGenerationLevel, 'parent_id', 'parent-path', 'parent-path/url-key'],
            ['url-key', false, $requireGenerationLevel, Category::TREE_ROOT_ID, null, 'url-key'],
            ['url-key', true, $noGenerationLevel, Category::TREE_ROOT_ID, null, 'url-key'],
        ];
    }

    /**
     * @dataProvider getUrlPathWithParentDataProvider
     * @param string $urlKey
     * @param bool $isCategoryNew
     * @param bool $level
     * @param int $parentCategoryParentId
     * @param string $parentUrlPath
     * @param string $result
     */
    public function testGetUrlPathWithParent(
        $urlKey,
        $isCategoryNew,
        $level,
        $parentCategoryParentId,
        $parentUrlPath,
        $result
    ) {
        $urlPath = null;
        $parentLevel = CategoryUrlPathGenerator::MINIMAL_CATEGORY_LEVEL_FOR_PROCESSING - 1;
        $this->category->expects($this->any())->method('getParentId')
            ->will($this->returnValue('parent_id'));
        $this->category->expects($this->any())->method('getLevel')
            ->will($this->returnValue($level));
        $this->category->expects($this->any())->method('getUrlPath')->will($this->returnValue($urlPath));
        $this->category->expects($this->any())->method('getUrlKey')->will($this->returnValue($urlKey));
        $this->category->expects($this->any())->method('isObjectNew')->will($this->returnValue($isCategoryNew));

        $methods = ['__wakeup', 'getUrlPath', 'getParentId', 'getLevel', 'dataHasChangedFor', 'load'];
        $parentCategory = $this->getMock('Magento\Catalog\Model\Category', $methods, [], '', false);
        $parentCategory->expects($this->any())->method('getParentId')
            ->will($this->returnValue($parentCategoryParentId));
        $parentCategory->expects($this->any())->method('getLevel')->will($this->returnValue($parentLevel));
        $parentCategory->expects($this->any())->method('getUrlPath')->will($this->returnValue($parentUrlPath));
        $parentCategory->expects($this->any())->method('dataHasChangedFor')
            ->will($this->returnValueMap([['url_key', false], ['path_ids', false]]));

        $this->categoryRepository->expects($this->once())->method('get')->with('parent_id')
            ->will($this->returnValue($parentCategory));

        $this->assertEquals($result, $this->categoryUrlPathGenerator->getUrlPath($this->category));
    }

    /**
     * @return array
     */
    public function getUrlPathWithSuffixDataProvider()
    {
        return [
            ['url-path', 1, null, '.html', 'url-path.html'],
            ['url-path', null, 1, '.html', 'url-path.html'],
        ];
    }

    /**
     * @dataProvider getUrlPathWithSuffixDataProvider
     * @param string $urlPath
     * @param int $storeId
     * @param int $categoryStoreId
     * @param string $suffix
     * @param string $result
     */
    public function testGetUrlPathWithSuffixAndStore($urlPath, $storeId, $categoryStoreId, $suffix, $result)
    {
        $this->category->expects($this->any())->method('getStoreId')->will($this->returnValue($categoryStoreId));
        $this->category->expects($this->once())->method('getParentId')->will($this->returnValue(123));
        $this->category->expects($this->once())->method('getUrlPath')->will($this->returnValue($urlPath));
        $this->category->expects($this->exactly(2))->method('dataHasChangedFor')
            ->will($this->returnValueMap([['url_key', false], ['path_ids', false]]));

        $passedStoreId = $storeId ? $storeId : $categoryStoreId;
        $this->scopeConfig->expects($this->once())->method('getValue')
            ->with(CategoryUrlPathGenerator::XML_PATH_CATEGORY_URL_SUFFIX, ScopeInterface::SCOPE_STORE, $passedStoreId)
            ->will($this->returnValue($suffix));

        $this->assertEquals(
            $result,
            $this->categoryUrlPathGenerator->getUrlPathWithSuffix($this->category, $storeId)
        );
    }

    public function testGetUrlPathWithSuffixWithoutStore()
    {
        $urlPath = 'url-path';
        $storeId = null;
        $currentStoreId = 1;
        $suffix = '.html';
        $result = 'url-path.html';

        $this->category->expects($this->any())->method('getStoreId')->will($this->returnValue($storeId));
        $this->category->expects($this->once())->method('getParentId')->will($this->returnValue('parent_id'));
        $this->category->expects($this->once())->method('getUrlPath')->will($this->returnValue($urlPath));
        $this->category->expects($this->exactly(2))->method('dataHasChangedFor')
            ->will($this->returnValueMap([['url_key', false], ['path_ids', false]]));

        $store = $this->getMock('Magento\Store\Model\Store', [], [], '', false);
        $store->expects($this->once())->method('getId')->will($this->returnValue($currentStoreId));
        $this->storeManager->expects($this->once())->method('getStore')->will($this->returnValue($store));
        $this->scopeConfig->expects($this->once())->method('getValue')
            ->with(CategoryUrlPathGenerator::XML_PATH_CATEGORY_URL_SUFFIX, ScopeInterface::SCOPE_STORE, $currentStoreId)
            ->will($this->returnValue($suffix));

        $this->assertEquals(
            $result,
            $this->categoryUrlPathGenerator->getUrlPathWithSuffix($this->category, $storeId)
        );
    }

    public function testGetCanonicalUrlPath()
    {
        $this->category->expects($this->once())->method('getId')->will($this->returnValue(1));
        $this->assertEquals(
            'catalog/category/view/id/1',
            $this->categoryUrlPathGenerator->getCanonicalUrlPath($this->category)
        );
    }

    /**
     * @return array
     */
    public function generateUrlKeyDataProvider()
    {
        return [
            ['url-key', null, 'url-key'],
            ['', 'category-name', 'category-name'],
        ];
    }

    /**
     * @dataProvider generateUrlKeyDataProvider
     * @param string $urlKey
     * @param string $name
     * @param string $result
     */
    public function testGenerateUrlKey($urlKey, $name, $result)
    {
        $this->category->expects($this->once())->method('getUrlKey')->will($this->returnValue($urlKey));
        $this->category->expects($this->any())->method('getName')->will($this->returnValue($name));
        $this->category->expects($this->once())->method('formatUrlKey')->will($this->returnArgument(0));

        $this->assertEquals($result, $this->categoryUrlPathGenerator->generateUrlKey($this->category));
    }
}
