<?php

namespace OroCRM\Bundle\MarketingListBundle\Tests\Unit\Provider;

use OroCRM\Bundle\MarketingListBundle\Entity\MarketingList;
use OroCRM\Bundle\MarketingListBundle\Provider\MarketingListItemVirtualRelationProvider;

class MarketingListItemVirtualRelationProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $doctrineHelper;

    /**
     * @var MarketingListItemVirtualRelationProvider
     */
    protected $provider;

    protected function setUp()
    {
        $this->doctrineHelper = $this->getMockBuilder('Oro\Bundle\EntityBundle\ORM\DoctrineHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->provider = new MarketingListItemVirtualRelationProvider($this->doctrineHelper);
    }

    /**
     * @dataProvider fieldDataProvider
     * @param string $className
     * @param string $fieldName
     * @param MarketingList $marketingList
     * @param bool $supported
     */
    public function testIsVirtualRelation($className, $fieldName, $marketingList, $supported)
    {
        $this->assertRepositoryCall($className, $marketingList);
        $this->assertEquals($supported, $this->provider->isVirtualRelation($className, $fieldName));
    }

    /**
     * @return array
     */
    public function fieldDataProvider()
    {
        $marketingList = $this->getMockBuilder('OroCRM\Bundle\MarketingListBundle\Entity\MarketingList')
            ->disableOriginalConstructor()
            ->getMock();

        return [
            'incorrect class incorrect field' => ['stdClass', 'test', null, false],
            'incorrect class correct field' => [
                'stdClass',
                MarketingListItemVirtualRelationProvider::FIELD_NAME,
                null,
                false
            ],
            'incorrect field' => ['stdClass', 'test', $marketingList, false],
            'correct' => ['stdClass', MarketingListItemVirtualRelationProvider::FIELD_NAME, $marketingList, true],
        ];
    }

    public function testGetVirtualRelationsNoRelations()
    {
        $className = 'stdClass';

        $this->assertRepositoryCall($className, null);
        $result = $this->provider->getVirtualRelations($className);

        $this->doctrineHelper->expects($this->never())
            ->method('getSingleEntityIdentifierFieldName');
        $this->assertEmpty($result);
    }

    public function testGetVirtualRelations()
    {
        $className = 'stdClass';
        $marketingList = $this->getMockBuilder('OroCRM\Bundle\MarketingListBundle\Entity\MarketingList')
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertRepositoryCall($className, $marketingList);
        $this->doctrineHelper->expects($this->once())
            ->method('getSingleEntityIdentifierFieldName')
            ->with($className)
            ->will($this->returnValue('id'));

        $result = $this->provider->getVirtualRelations($className);
        $this->assertArrayHasKey(MarketingListItemVirtualRelationProvider::FIELD_NAME, $result);
    }

    /**
     * @return array
     */
    public function relationsDataProvider()
    {
        $marketingList = $this->getMockBuilder('OroCRM\Bundle\MarketingListBundle\Entity\MarketingList')
            ->disableOriginalConstructor()
            ->getMock();

        return [
            'incorrect class incorrect field' => ['stdClass', null, false],
            'correct' => ['stdClass', $marketingList, true],
        ];
    }

    /**
     * @dataProvider fieldDataProvider
     * @param string $className
     * @param string $fieldName
     * @param MarketingList $marketingList
     * @param bool $supported
     */
    public function tesGetVirtualRelationQueryUnsupportedClass($className, $fieldName, $marketingList, $supported)
    {
        $this->assertRepositoryCall($className, $marketingList);
        if ($supported) {
            $this->doctrineHelper->expects($this->once())
                ->method('getSingleEntityIdentifierFieldName')
                ->with($className)
                ->will($this->returnValue('id'));
        }

        $result = $this->provider->getVirtualRelationQuery($className, $fieldName);

        if ($supported) {
            $this->assertNotEmpty($result);
        } else {
            $this->assertNotEmpty($result);
        }
    }

    protected function assertRepositoryCall($className, $marketingList)
    {
        $repository = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectRepository')
            ->getMock();
        $repository->expects($this->once())
            ->method('findOneBy')
            ->with(['entity' => $className])
            ->will($this->returnValue($marketingList));

        $this->doctrineHelper->expects($this->once())
            ->method('getEntityRepository')
            ->with('OroCRMMarketingListBundle:MarketingList')
            ->will($this->returnValue($repository));
    }
}
