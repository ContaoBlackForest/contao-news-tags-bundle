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
 * Extend the news tables.
 */

$GLOBALS['BE_MOD']['content']['news']['tables'] = array_merge(
    $GLOBALS['BE_MOD']['content']['news']['tables'],
    ['tl_news_tags', 'tl_news_tags_relation']
);

/*
 * Hooks.
 */

$GLOBALS['TL_HOOKS']['parseTemplate'][]      = ['cb.module_news_list.add_filter_menu', 'handle'];
$GLOBALS['TL_HOOKS']['parseArticles'][]      = ['cb.module_news_detail.add_filter_menu', 'handle'];
$GLOBALS['TL_HOOKS']['newsListCountItems'][] = ['cb.module_news_list.item_fetcher', 'countItems'];
$GLOBALS['TL_HOOKS']['newsListFetchItems'][] = ['cb.module_news_list.item_fetcher', 'fetchItems'];

/*
 * Add permissions.
 */

$GLOBALS['TL_PERMISSIONS'][] = 'newstagsp';
$GLOBALS['TL_PERMISSIONS'][] = 'newstagsrelationp';
