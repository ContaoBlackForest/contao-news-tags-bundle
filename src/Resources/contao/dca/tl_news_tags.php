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

$GLOBALS['TL_DCA']['tl_news_tags'] = [

    'config' => [
        'dataContainer'               => 'Table',
        'enableVersioning'            => true,
        'onload_callback'             => [
            ['cb.table_news_tags.permission', 'handlePermission']
        ],
        'sql' => [
            'keys' => [
                'id'    => 'primary',
                'alias' => 'index'
            ]
        ],
        'backlink' => 'do=news'
    ],

    'list' => [
        'sorting' => [
            'mode'                => 1,
            'fields'              => ['title'],
            'flag'                => 1,
            'panelLayout'         => 'filter;search,limit'
        ],
        'label' => [
            'fields'              => ['title'],
            'format'              => '%s'
        ],
        'global_operations' => [
            'all' => [
                'label'           => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'            => 'act=select',
                'class'           => 'header_edit_all',
                'attributes'      => 'onclick="Backend.getScrollOffset()" accesskey="e"'
            ],
        ],
        'operations' => [
            'edit' => [
                'label'           => &$GLOBALS['TL_LANG']['tl_news_tags']['edit'],
                'href'            => 'act=edit',
                'icon'            => 'edit.svg',
                'button_callback' => ['cb.table_news_tags.permission', 'handleButtonCanEdit']
            ],
            'copy' => [
                'label'           => &$GLOBALS['TL_LANG']['tl_news_tags']['copy'],
                'href'            => 'act=copy',
                'icon'            => 'copy.svg',
                'button_callback' => ['cb.table_news_tags.permission', 'handleButtonCanEdit']
            ],
            'delete' => [
                'label'           => &$GLOBALS['TL_LANG']['tl_news_tags']['delete'],
                'href'            => 'act=delete',
                'icon'            => 'delete.svg',
                'attributes'      => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))' .
                                     'return false;Backend.getScrollOffset()"',
                'button_callback' => ['cb.table_news_tags.permission', 'handleButtonCanDelete']
            ],
            'show' => [
                'label'           => &$GLOBALS['TL_LANG']['tl_news_tags']['show'],
                'href'            => 'act=show',
                'icon'            => 'show.svg'
            ]
        ]
    ],

    'palettes' => [
        'default' => '{title_legend},title,alias;{archives_legend},archives;{note_legend:hide},note'
    ],

    'fields' => [
        'id' => [
            'sql'              => 'int(10) unsigned NOT NULL auto_increment'
        ],
        'tstamp' => [
            'sql'              => "int(10) unsigned NOT NULL default '0'"
        ],
        'title' => [
            'label'            => &$GLOBALS['TL_LANG']['tl_news_tags']['title'],
            'exclude'          => true,
            'search'           => true,
            'inputType'        => 'text',
            'eval'             => ['mandatory' => true, 'unique' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql'              => "varchar(255) NOT NULL default ''"
        ],
        'alias' => [
            'label'            => &$GLOBALS['TL_LANG']['tl_news_tags']['alias'],
            'exclude'          => true,
            'search'           => true,
            'inputType'        => 'text',
            'eval'             => ['rgxp' => 'alias', 'unique' => true, 'maxlength' => 128, 'tl_class' => 'w50 clr'],
            'save_callback'    => [
                ['cb.table_news_tags.alias_generator', 'handle']
            ],
            'sql'              => "varchar(128) COLLATE utf8_bin NOT NULL default ''"
        ],
        'archives' => [
            'label'            => &$GLOBALS['TL_LANG']['tl_news_tags']['archives'],
            'exclude'          => true,
            'search'           => true,
            'inputType'        => 'checkbox',
            'options_callback' => ['cb.table_news_tags.archives_options', 'handle'],
            'eval'             => ['multiple' => true, 'mandatory' => true],
            'sql'              => 'blob NULL'
        ],
        'note' => [
            'label'            => &$GLOBALS['TL_LANG']['tl_news_tags']['note'],
            'exclude'          => true,
            'search'           => true,
            'inputType'        => 'textarea',
            'eval'             => ['style' => 'height:60px', 'tl_class' => 'clr'],
            'sql'              => 'text NULL'
        ]
    ]
];
