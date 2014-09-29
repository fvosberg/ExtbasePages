<?php

namespace Rattazonk\Extbasepages\Tests\Unit\Tree;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Frederik Vosberg <frederik.vosberg@rattazonk.com>, Rattazonk
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Test case for class \Rattazonk\Extbasepages\Tree\PageTree.
 *
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 * @author Frederik Vosberg <frederik.vosberg@rattazonk.com>
 */
class PageTreeTest extends \TYPO3\CMS\Core\Tests\UnitTestCase {
	/**
	 * @var \Rattazonk\Extbasepages\Tree\PageTree
	 */
	protected $subject = NULL;

	/**
	 * @var Tx_Phpunit_Framework
	 */
	protected $testingFramework;

	/** @var int **/
	protected $currentPageUid;
	
	protected $pageRepositoryMock;

	protected function setUp() {
		$this->objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
			'TYPO3\CMS\Extbase\Object\ObjectManager'
		);
		$this->subject = $this->objectManager->get('Rattazonk\Extbasepages\Tree\PageTree');
		$this->testingFramework = new \Tx_Phpunit_Framework('foobar');

		$this->currentPageUid = $this->testingFramework->createFakeFrontEnd();
		$this->pageRepositoryMock = $this->getMockBuilder(
				'Rattazonk\Extbasepages\Domain\Repository\PageRepository'
			)->disableOriginalConstructor()
			->setMethods(array('findByParent'))
			->getMock();

		$this->inject( 
			$this->subject,
			'pageRepository',
			$this->pageRepositoryMock
		);
	}

	protected function tearDown() {
		unset( $this->subject );
		$this->testingFramework->cleanUp();
		unset( $this->testingFramework );
	}

	/**
	 * @test
	 */
	public function initializedUnderCurrentPageUid() {
		$this->pageRepositoryMock->expects($this->once())
			->method('findByParent')
			->with( $this->equalTo( $this->currentPageUid ) )
			->will( $this->returnValue(array()));

		$this->subject->getFirstLevelPages();
	}

	/**
	 * @test
	 */
	public function wrapPagesRecursively() {
		$pageMocks = $this->initTestTree();

		$firstLevel = $this->subject->getFirstLevelPages();

		$this->assertInstanceOf('\TYPO3\CMS\Extbase\Persistence\ObjectStorage', $firstLevel, 'Page tree returns not an object storage for the first level');
		$this->assertCount(2, $firstLevel, 'The first level hasnt 2 children');
		$firstLevel->rewind();
		$wrappedOne = $firstLevel->current();
		$this->isWrappedPage( $wrappedOne, $pageMocks['one'], 'One isnt wrapped' );

		$wrappedOneChildren = $wrappedOne->getChildren();
		$this->assertInstanceOf(
			'\TYPO3\CMS\Extbase\Persistence\ObjectStorage',
			$wrappedOneChildren,
			'Children of wrapped one are not in an object storage'
		);
		$this->assertCount(2, $wrappedOneChildren);
		$wrappedOneChildren->rewind();
		$wrappedOneOne = $wrappedOneChildren->current();
		$this->isWrappedPage( $wrappedOneOne, $pageMocks['oneOne'] );

		$wrappedOneOneChildren = $wrappedOneOne->getChildren();
		$this->assertInstanceOf('\TYPO3\CMS\Extbase\Persistence\ObjectStorage', $wrappedOneOneChildren);
		$this->assertCount(0, $wrappedOneOneChildren);

		$wrappedOneChildren->next();
		$wrappedOneTwo = $wrappedOneChildren->current();
		$this->isWrappedPage( $wrappedOneTwo, $pageMocks['oneTwo'] );

		$wrappedOneTwoChildren = $wrappedOneTwo->getChildren();
		$this->assertInstanceOf('\TYPO3\CMS\Extbase\Persistence\ObjectStorage', $wrappedOneTwoChildren);
		$this->assertCount(1, $wrappedOneTwoChildren);
		$wrappedOneTwoChildren->rewind();
		$this->isWrappedPage( $wrappedOneTwoChildren->current(), $pageMocks['oneTwoOne'] );


		$firstLevel->next();
		$wrappedTwo = $firstLevel->current();
		$this->isWrappedPage( $wrappedTwo, $pageMocks['two'] );

		$wrappedTwoChildren = $wrappedTwo->getChildren();
		$this->assertInstanceOf('\TYPO3\CMS\Extbase\Persistence\ObjectStorage', $wrappedTwoChildren);
		$this->assertCount(1, $wrappedTwoChildren);
		$wrappedTwoChildren->rewind();
		$this->isWrappedPage( $wrappedTwoChildren->current(), $pageMocks['twoOne'] );
	}

	protected function initTestTree() {
		$pageMocks['one'] = $this->getMock(
			'Rattazonk\Extbasepages\Domain\Model\Page',
			array('getChildren')
		);
		$pageMocks['oneOne'] = $this->getMock(
			'Rattazonk\Extbasepages\Domain\Model\Page',
			array('getChildren')
		);
		$pageMocks['oneTwo'] = $this->getMock(
			'Rattazonk\Extbasepages\Domain\Model\Page',
			array('getChildren')
		);
		$pageMocks['oneTwoOne'] = $this->getMock(
			'Rattazonk\Extbasepages\Domain\Model\Page',
			array('getChildren')
		);
		$pageMocks['two'] = $this->getMock(
			'Rattazonk\Extbasepages\Domain\Model\Page',
			array('getChildren')
		);
		$pageMocks['twoOne'] = $this->getMock(
			'Rattazonk\Extbasepages\Domain\Model\Page',
			array('getChildren')
		);
		$this->pageCount = 6;

		$this->returnsAsChildren( $pageMocks['one'], array($pageMocks['oneOne'], $pageMocks['oneTwo']) );
		$this->returnsAsChildren( $pageMocks['oneOne'] );
		$this->returnsAsChildren( $pageMocks['oneTwo'], array($pageMocks['oneTwoOne']) );
		$this->returnsAsChildren( $pageMocks['oneTwoOne'] );
		$this->returnsAsChildren( $pageMocks['two'], array($pageMocks['twoOne']) );
		$this->returnsAsChildren( $pageMocks['twoOne'] );

		$this->pageRepositoryMock->expects($this->once())
			->method('findByParent')
			->with($this->equalTo( $this->currentPageUid ))
			->will($this->returnValue( array($pageMocks['one'], $pageMocks['two']) ));

		return $pageMocks;
	}

	protected function returnsAsChildren( $page, $children = array() ) {
		$page->expects($this->any())
			->method('getChildren')
			->will($this->returnValue( $children ));
	}

	protected function isWrappedPage( $wrapped, $page, $message = '' ) {
		$this->assertInstanceOf( '\Rattazonk\Extbasepages\Tree\ElementWrapper', $wrapped, $message );
		$this->assertSame( $page, $wrapped->getWrappedElement(), $message );
	}

	/**
	 * @test
	 */
	public function treeFilter() {
		$pageMocks = $this->initTestTree();

		$filter = $this->getMockForAbstractClass(
			'Rattazonk\Extbasepages\Tree\Filter\AbstractFilter',
			array(), '', TRUE, TRUE, TRUE,
			array('filter')
		);

		$filter->expects($this->exactly( $this->pageCount ))
			->method('filter');

		$this->subject->addFilter( $filter );
		$this->subject->getFirstLevelPages();
	}
}
