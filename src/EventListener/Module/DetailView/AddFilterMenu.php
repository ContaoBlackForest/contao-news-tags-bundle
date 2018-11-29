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

namespace BlackForest\Contao\News\Tags\EventListener\Module\DetailView;

use Contao\CoreBundle\Framework\Adapter;
use Contao\FrontendTemplate;
use Contao\Input;
use Contao\Module;
use Contao\Template;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;

/**
 * This handle for add the tags filter menu in the news list.
 */
class AddFilterMenu
{
    /**
     * The request stack.
     *
     * @var RequestStack
     */
    private $requestStack;

    /**
     * The database connection.
     *
     * @var Connection
     */
    private $connection;

    /**
     * The session bag.
     *
     * @var AttributeBag
     */
    private $sessionBag;

    /**
     * The input provider.
     *
     * @var Input
     */
    private $input;

    /**
     * The url suffix.
     *
     * @var string
     */
    private $urlSuffix;

    /**
     * The constructor.
     *
     * @param RequestStack $requestStack The request stack.
     * @param Connection   $connection   The database connection.
     * @param AttributeBag $sessionBag   The session bag.
     * @param Adapter      $input        The input provider.
     * @param string       $urlSuffix    The url suffix.
     */
    public function __construct(
        RequestStack $requestStack,
        Connection $connection,
        AttributeBag $sessionBag,
        Adapter $input,
        $urlSuffix
    ) {
        $this->requestStack = $requestStack;
        $this->connection   = $connection;
        $this->sessionBag   = $sessionBag;
        $this->input        = $input;
        $this->urlSuffix    = $urlSuffix;
    }

    /**
     * Handle for add the tags filter menu in the news list.
     *
     * @param Template $template The template.
     * @param array    $data     The article data.
     * @param Module   $module   The module.
     *
     * @return void
     */
    public function handle(Template $template, array $data, Module $module)
    {
        if (!('contao_frontend' === $this->requestStack->getCurrentRequest()->get('_route'))
            || !('newsreader' === $module->type)
            || !$module->newsTagsShow
        ) {
            return;
        }

        $tags = $this->fetchAllTags($data['id']);
        if (!\count($tags)) {
            return;
        }

        $backPathInfo = $this->findBackPathInfo();
        $this->prepareFallbackPath($tags);


        $data = [
            'backPathInfo' => $backPathInfo,
            'urlSuffix'    => $this->urlSuffix,
            'moduleId'     => $module->id,
            'tags'         => $tags
        ];

        $filterTemplate = new FrontendTemplate('news_detail_tags_show');
        $filterTemplate->setData($data);

        $template->hasShowTags = true;
        $template->showTags    = $filterTemplate->parse();
    }

    /**
     * Fetch all tags.
     *
     * @param string $newsId The news id.
     *
     * @return array
     */
    private function fetchAllTags($newsId)
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder
            ->select('t.*')
            ->from('tl_news_tags', 't')
            ->innerJoin('t', 'tl_news_tags_relation', 'r', 'r.tag = t.id')
            ->innerJoin('r', 'tl_news', 'n', 'n.id = r.news')
            ->where($queryBuilder->expr()->in('n.id', ':newsId'))
            ->setParameter(':newsId', $newsId)
            ->orderBy('t.title')
            ->groupBy('t.id');

        $statement = $queryBuilder->execute();
        if (!$statement->rowCount()) {
            return [];
        }

        return $statement->fetchAll(\PDO::FETCH_OBJ);
    }

    /**
     * Find the back path info to the list view from the referer.
     *
     * @return string
     */
    private function findBackPathInfo()
    {
        $listViewBackPath = $this->sessionBag->get('news-tags-last-list-view');
        if (!$listViewBackPath) {
            return '';
        }

        $currentRequest = $this->requestStack->getCurrentRequest();
        if (!$currentRequest->headers->has('referer')) {
            return '';
        }

        $backPathInfo = '';
        foreach ((array) $currentRequest->headers->get('referer') as $referer) {
            if (!\strpos($referer, $listViewBackPath . '/filterTag/')) {
                continue;
            }

            $backPathInfo = $listViewBackPath;

            break;
        }

        return $backPathInfo;
    }

    /**
     * Prepare the fallback path info for each tag.
     *
     * @param array $tags The tag list.
     *
     * @return void
     */
    private function prepareFallbackPath(array $tags)
    {
        $currentRequest = $this->requestStack->getCurrentRequest();

        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder
            ->select('p.alias')
            ->from('tl_page', 'p')
            ->where($queryBuilder->expr()->eq('p.id', ':pageId'));

        $fallback = [];
        foreach ($tags as $tag) {
            if (!$tag->tagLinkFallback) {
                continue;
            }

            if (\array_key_exists($tag->tagLinkFallback, $fallback)) {
                $tag->tagLinkFallbackPath = $fallback[$tag->tagLinkFallback];
            }

            $queryBuilder->setParameter(':pageId', $tag->tagLinkFallback);

            $statement = $queryBuilder->execute();
            if (!$statement->rowCount()) {
                $tag->tagLinkFallbackPath = null;
            }

            $page = $statement->fetch(\PDO::FETCH_OBJ);

            $fallbackPath  = $currentRequest->getBaseUrl();
            $fallbackPath .= $currentRequest->getLocale() ? '/' . $currentRequest->getLocale() : '';
            $fallbackPath .= '/' . $page->alias;

            $tag->tagLinkFallbackPath = $fallbackPath;

            $fallback[$tag->tagLinkFallback] = $fallbackPath;
        }
    }
}
