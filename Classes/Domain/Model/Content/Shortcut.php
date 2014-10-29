<?php
namespace Rattazonk\Extbasepages\Domain\Model\Content;


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

/**
 * Page
 */
class Shortcut extends \Rattazonk\Extbasepages\Domain\Model\Content {

  /**
   * @var Rattazonk\Extbasepages\Domain\Repository\ContentRepository
   * @inject
   */
  protected $contentRepository;

  /**
   * @var TYPO3\CMS\Extbase\Persistence\ObjectStorage
   * @inject
   */
  protected $recordsStorage;
 
  /**
   * @var boolean
   */
  protected $recordsInitialized = FALSE;

  /**
   * uninitialized records from db
   *
   * @var array
   */
  protected $records = array();

  /**
   * @return TYPO3\CMS\Extbase\Persistence\ObjectStorage
   */
  public function getRecords() {
    if( !$this->recordsInitialized ){
      $this->initRecords();
    }
    return $this->recordsStorage;
  }

  protected function initRecords() {
    foreach( $this->records as $record ){
      $recordUid = $this->getUidFromRecordString( $record );
      $recordObject = $this->contentRepository->findByUid( $recordUid );
      $this->recordsStorage->attach( $recordObject );
    }

    $this->recordsInitialized = TRUE;
  }

  /**
   * @var string $recordString - string from the db
   * @return int
   */
  protected function getUidFromRecordString( $recordString ) {
    $lastUnderscorePos = strrpos($recordString, '_');
    return substr($recordString, ++$lastUnderscorePos);
  }
}
