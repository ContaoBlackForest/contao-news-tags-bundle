<?php

/**
 * This file is part of contaoblackforest/contao-news-tags-bundle.
 *
 * (c) 2014-2018 The Contao Blackforest team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contaoblackforest/contao-news-tags-bundle
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2014-2018 The Contao Blackforest team.
 * @license    https://github.com/contaoblackforest/contao-news-tags-bundle/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

/*
 * Add fields to the palette.
 */

Contao\CoreBundle\DataContainer\PaletteManipulator::create()
    ->addField(['newsTagsFilter'], 'config_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('newslist', 'tl_module');

Contao\CoreBundle\DataContainer\PaletteManipulator::create()
    ->addField(['newsTagsShow'], 'config_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('newsreader', 'tl_module');

/*
 * Add fields.
 */

$GLOBALS['TL_DCA']['tl_module']['fields']['newsTagsFilter'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['newsTagsFilter'],
    'exclude'   => true,
    'filter'    => true,
    'flag'      => 1,
    'inputType' => 'checkbox',
    'eval'      => ['doNotCopy' => true, 'tl_class' => 'w50 clr m12'],
    'sql'       => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['newsTagsShow'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['newsTagsShow'],
    'exclude'   => true,
    'filter'    => true,
    'flag'      => 1,
    'inputType' => 'checkbox',
    'eval'      => ['doNotCopy' => true, 'tl_class' => 'w50 clr m12'],
    'sql'       => "char(1) NOT NULL default ''"
];
