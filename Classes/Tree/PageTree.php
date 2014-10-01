<?php
namespace Rattazonk\Extbasepages\Tree;


/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2014 Frederik Vosberg <frederik.vosberg@rattazonk.com>, Rattazonk
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
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

class PageTree {

	/**
	 * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage
	 * @inject
	 */
	protected $firstLevelPages;

	/**
	 * @var TYPO3\CMS\Extbase\Object\ObjectManagerInterface
	 * @inject
	 */
	protected $objectManager;

	/** @var boolean **/
	protected $initialized = FALSE;

	/** @var array **/
	protected $filters = array();

	/**
	 * @var Rattazonk\Extbasepages\Domain\Repository\PageRepository
	 * @inject
	 */
	protected $pageRepository;

	/**
	 * @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher
	 * @inject
	 */
	protected $signalSlotDispatcher;

	/** @var array **/
	protected $configuration = array(
		'hideChildrenOfHidden' => TRUE
	);

	public function getFirstLevelPages() {
		$this->ensureInitialization();
		return $this->firstLevelPages;
	}

	protected function ensureInitialization() {
		if( $this->initialized ) {
			return TRUE;
		}

		$firstLevelPages = $this->pageRepository->findByParent(
			(int) $GLOBALS['TSFE']->id
		);
		$firstLevelPages = $this->wrapTree( $firstLevelPages );
		$this->filterTree( $firstLevelPages );
		if( $this->getConfiguration('hideChildrenOfHidden') ) {
			$this->hideChildrenOfHidden( $firstLevelPages );
		}

		$this->firstLevelPages = $firstLevelPages;
		$this->initialized = TRUE;
	}

	protected function wrapTree( $currentLevel ) {
		$wrappedLevel = $this->objectManager->get( 'TYPO3\CMS\Extbase\Persistence\ObjectStorage' );

		foreach( $currentLevel as $page ) {
			$wrappedPage = $this->objectManager->get(
				'Rattazonk\Extbasepages\Tree\ElementWrapper',
				$page
			);
			$wrappedLevel->attach( $wrappedPage );

			// the wrapped children are stored in the wrapper of the page, the page itself stays clean
			$wrappedPage->setChildren(
				$this->wrapTree( $wrappedPage->getChildren() )
			);
		}

		return $wrappedLevel;
	}

	public function getFlattenedPages() {
		$this->ensureInitialization();

		$flattenedPages = $this->objectManager->get( 'TYPO3\CMS\Extbase\Persistence\ObjectStorage' );
		$this->forEachElement(function($element) use (&$flattenedPages) {
			$flattenedPages->attach( $element );
		});
		return $flattenedPages;
	}

	public function addFilter($filter) {
		$this->filters[] = $filter;
	}

	public function filterTree( $firstLevel ) {
		if( !$this->filtersInitialized ) {
			// the filter should call the addFilter method
			$this->signalSlotDispatcher->dispatch(__CLASS__, 'initFilters', array($this));
		}
		$filters = $this->filters;
		$this->forEachElement( function( $page ) use ($filters) {
				foreach( $filters as $filter ) {
					$filter->filter( $page );
					if( $page->wrappedElementIsHidden() ) { break; }
				}
			},
			$firstLevel
		);
	}

	protected function hideChildrenOfHidden( $firstLevelPages = NULL ) {
		$this->forEachElement(function($page){
			if( $page->wrappedElementIsHidden() ) {
				// hide children recursively
				$this->forEachElement(function($childrenToHide) {
						$childrenToHide->hideWrappedElement();
					}, $page->getChildren()
				);
				// stop searching, subtree already hidden
				return FALSE;
			}
			// Go through children
			return TRUE;
		}, $firstLevelPages);
	}

	public function forEachElement( $callback, $level = NULL ) {
		if( $level === NULL ) {
			$level = $this->getFirstLevelPages();
		}
		foreach( $level as $page ) {
			// you can skip the rendering of children
			// from the callback with returning FALSE (explicitly)
			if( $callback($page) !== FALSE ) {
				$children = $this->forEachElement( $callback, $page->getChildren() );
				$page->setChildren( $children );
			}
		}
		return $level;
	}

	public function getConfiguration( $name ) {
		if( isset($this->configuration[$name]) ) {
			return $this->configuration[$name];
		} else {
			return NULL;
		}
	}

	public function addConfiguration( $name, $value ) {
		$this->configuration[$name] = $value;
	}
}
