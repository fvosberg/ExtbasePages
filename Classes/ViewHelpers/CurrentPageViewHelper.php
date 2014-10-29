<?php
namespace Rattazonk\Extbasepages\ViewHelpers;


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

class CurrentPageViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

  /**
   * @var Rattazonk\Extbasepages\Domain\Repository\PageRepository
   * @inject
   */
  protected $pageRepository;

  /**
   * @param string $as
   * @return string the rendered string
   **/
  public function render($as = 'page') {
    $this->templateVariableContainer->add($as, $this->getCurrentPage());
    $output = $this->renderChildren();
    $this->templateVariableContainer->remove( $as );
    return $output;
  }

  protected function getCurrentPage() {
    return $this->pageRepository->findCurrentPage();
  }

}