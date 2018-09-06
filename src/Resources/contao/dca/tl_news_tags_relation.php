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

$GLOBALS['TL_DCA']['tl_news_tags_relation'] = [

    'config'  => [
        'dataContainer'               => 'Table',
        'enableVersioning'            => true,
        'onload_callback'             => [
            ['cb.table_news_tags_relation.permission', 'handlePermission']
        ],
        'sql' => [
            'keys' => [
                'id'                  => 'primary',
                'archive,news,tag'   => 'index'
            ]
        ],
        'backlink' => 'do=news&amp;table=tl_news_tags'
    ],

    'list' => [
        'sorting' => [
            'mode'                => 2,
            'fields'              => ['tag', 'archive', 'news'],
            'flag'                => 1,
            'panelLayout'         => 'sort;filter;search,limit'
        ],
        'label' => [
            'fields'              => [
                'archive:tl_news_archive.title',
                'news:tl_news.headline',
                'tag:tl_news_tags.title'
            ],
            'format'              => $GLOBALS['TL_LANG']['tl_news_tags_relation']['archive'][0] . ': %s<br>' .
                                     $GLOBALS['TL_LANG']['tl_news_tags_relation']['news'][0] . ': %s<br>' .
                                     $GLOBALS['TL_LANG']['tl_news_tags_relation']['tag'][0] . ': %s<br>'
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
                'label'           => &$GLOBALS['TL_LANG']['tl_news_tags_relation']['edit'],
                'href'            => 'act=edit',
                'icon'            => 'edit.svg',
                'button_callback' => ['cb.table_news_tags_relation.permission', 'handleButtonCanEdit']
            ],
            'copy' => [
                'label'           => &$GLOBALS['TL_LANG']['tl_news_tags_relation']['copy'],
                'href'            => 'act=copy',
                'icon'            => 'copy.svg',
                'button_callback' => ['cb.table_news_tags_relation.permission', 'handleButtonCanEdit']
            ],
            'delete' => [
                'label'           => &$GLOBALS['TL_LANG']['tl_news_tags_relation']['delete'],
                'href'            => 'act=delete',
                'icon'            => 'delete.svg',
                'attributes'      => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))' .
                                     'return false;Backend.getScrollOffset()"',
                'button_callback' => ['cb.table_news_tags_relation.permission', 'handleButtonCanDelete']
            ],
            'show' => [
                'label'           => &$GLOBALS['TL_LANG']['tl_news_tags_relation']['show'],
                'href'            => 'act=show',
                'icon'            => 'show.svg'
            ]
        ]
    ],

    'palettes' => [
        'default' => '{archive_legend},archive;{news_legend},news;{tag_legend},tag'
    ],

    'fields' => [
        'id' => [
            'sql'              => 'int(10) unsigned NOT NULL auto_increment'
        ],
        'tstamp' => [
            'sql'              => "int(10) unsigned NOT NULL default '0'"
        ],
        'archive' => [
            'label'            => &$GLOBALS['TL_LANG']['tl_news_tags_relation']['archive'],
            'exclude'          => true,
            'sorting'          => true,
            'filter'           => true,
            'search'           => true,
            'inputType'        => 'select',
            'options_callback' => ['cb.table_news_tags_relation.archives_options', 'handle'],
            'eval'             => [
                'multiple'           => false,
                'chosen'             => true,
                'mandatory'          => true,
                'includeBlankOption' => true,
                'submitOnChange'     => true
            ],
            'sql'              => "int(10) unsigned NOT NULL default '0'"
        ],
        'news' => [
            'label'            => &$GLOBALS['TL_LANG']['tl_news_tags_relation']['news'],
            'exclude'          => true,
            'sorting'          => true,
            'filter'           => true,
            'search'           => true,
            'inputType'        => 'select',
            'options_callback' => ['cb.table_news_tags_relation.news_options', 'handle'],
            'eval'             => [
                'multiple'           => false,
                'chosen'             => true,
                'mandatory'          => true,
                'includeBlankOption' => true,
                'submitOnChange'     => true
            ],
            'sql'              => "int(10) unsigned NOT NULL default '0'"
        ],
        'tag' => [
            'label'            => &$GLOBALS['TL_LANG']['tl_news_tags_relation']['tag'],
            'exclude'          => true,
            'sorting'          => true,
            'filter'           => true,
            'search'           => true,
            'inputType'        => 'select',
            'options_callback' => ['cb.table_news_tags_relation.tag_options', 'handle'],
            'eval'             => [
                'multiple'           => false,
                'chosen'             => true,
                'mandatory'          => true,
                'includeBlankOption' => true,
                'doNotCopy'          => true
            ],
            'sql'              => "int(10) unsigned NOT NULL default '0'"
        ]
    ]
];
