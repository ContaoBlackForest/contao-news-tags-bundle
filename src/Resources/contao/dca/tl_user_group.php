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
 * Extend the default palettes.
 */

Contao\CoreBundle\DataContainer\PaletteManipulator::create()
    ->addField(
        ['newstagsp', 'newstagsrelationp'],
        'news_legend',
        Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND
    )
    ->applyToPalette('default', 'tl_user_group');

/*
 * Add fields to tl_user_group
 */

$GLOBALS['TL_DCA']['tl_user_group']['fields']['newstagsp']         = [
    'label'     => &$GLOBALS['TL_LANG']['tl_user']['newstagsp'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'options'   => ['create', 'delete'],
    'reference' => &$GLOBALS['TL_LANG']['MSC'],
    'eval'      => ['multiple' => true],
    'sql'       => 'blob NULL'
];
$GLOBALS['TL_DCA']['tl_user_group']['fields']['newstagsrelationp'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_user']['newstagsp'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'options'   => ['create', 'delete'],
    'reference' => &$GLOBALS['TL_LANG']['MSC'],
    'eval'      => ['multiple' => true],
    'sql'       => 'blob NULL'
];
