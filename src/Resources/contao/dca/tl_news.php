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
 * Add model operation button.
 */

$GLOBALS['TL_DCA']['tl_news']['list']['operations']['selectTags'] = [
    'label'           => &$GLOBALS['TL_LANG']['tl_news']['selectTags'],
    'href'            => 'act=select&amp;table=tl_news_tags',
    'icon'            => 'filter-apply.svg',
    'button_callback' => ['cb.table_news.select_tags_model_command', 'handle']
];
