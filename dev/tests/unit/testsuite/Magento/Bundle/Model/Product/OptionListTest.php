<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Model\Product;

class OptionListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Bundle\Model\Product\OptionList
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $typeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $optionFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $linkListMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataObjectHelperMock;

    protected function setUp()
    {
        $this->typeMock = $this->getMock('\Magento\Bundle\Model\Product\Type', [], [], '', false);
        $this->optionFactoryMock = $this->getMock(
            '\Magento\Bundle\Api\Data\OptionInterfaceFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->dataObjectHelperMock = $this->getMockBuilder('\Magento\Framework\Api\DataObjectHelper')
            ->disableOriginalConstructor()
            ->getMock();
        $this->linkListMock = $this->getMock('\Magento\Bundle\Model\Product\LinksList', [], [], '', false);
        $this->model = new \Magento\Bundle\Model\Product\OptionList(
            $this->typeMock,
            $this->optionFactoryMock,
            $this->linkListMock,
            $this->dataObjectHelperMock
        );
    }

    public function testGetItems()
    {
        $optionId = 1;
        $optionData = ['title' => 'test title'];
        $productSku = 'product_sku';

        $productMock = $this->getMock('\Magento\Catalog\Api\Data\ProductInterface');
        $productMock->expects($this->once())->method('getSku')->willReturn($productSku);

        $optionMock = $this->getMock(
            '\Magento\Bundle\Model\Option',
            ['getOptionId', 'getData', 'getTitle', 'getDefaultTitle'],
            [],
            '',
            false
        );
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $optionsCollMock = $objectManager->getCollectionMock(
            'Magento\Bundle\Model\Resource\Option\Collection',
            [$optionMock]
        );
        $this->typeMock->expects($this->once())
            ->method('getOptionsCollection')
            ->with($productMock)
            ->willReturn($optionsCollMock);

        $optionMock->expects($this->exactly(2))->method('getOptionId')->willReturn($optionId);
        $optionMock->expects($this->once())->method('getData')->willReturn($optionData);
        $optionMock->expects($this->once())->method('getTitle')->willReturn(null);
        $optionMock->expects($this->once())->method('getDefaultTitle')->willReturn($optionData['title']);

        $linkMock = $this->getMock('\Magento\Bundle\Api\Data\LinkInterface');
        $this->linkListMock->expects($this->once())
            ->method('getItems')
            ->with($productMock, $optionId)
            ->willReturn([$linkMock]);
        $newOptionMock = $this->getMock('\Magento\Bundle\Api\Data\OptionInterface');
        $this->dataObjectHelperMock->expects($this->once())
            ->method('populateWithArray')
            ->with($newOptionMock, $optionData, '\Magento\Bundle\Api\Data\OptionInterface')
            ->willReturnSelf();
        $newOptionMock->expects($this->once())->method('setOptionId')->with($optionId)->willReturnSelf();
        $newOptionMock->expects($this->once())
            ->method('setTitle')
            ->with($optionData['title'])
            ->willReturnSelf();
        $newOptionMock->expects($this->once())->method('setSku')->with($productSku)->willReturnSelf();
        $newOptionMock->expects($this->once())
            ->method('setProductLinks')
            ->with([$linkMock])
            ->willReturnSelf();
        $this->optionFactoryMock->expects($this->once())->method('create')->willReturn($newOptionMock);

        $this->assertEquals(
            [$newOptionMock],
            $this->model->getItems($productMock)
        );
    }
}
