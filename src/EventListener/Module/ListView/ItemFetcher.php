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

namespace BlackForest\Contao\News\Tags\EventListener\Module\ListView;

use Contao\Controller;
use Contao\CoreBundle\Framework\Adapter;
use Contao\Input;
use Contao\ModuleNewsList;
use Contao\NewsModel;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * This class provide methods for get filter news.
 */
class ItemFetcher
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
     * The controller.
     *
     * @var Controller
     */
    private $controller;

    /**
     * The input provider.
     *
     * @var Input
     */
    private $input;

    /**
     * The news repository.
     *
     * @var NewsModel
     */
    private $repository;

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
     * @param Adapter      $controller   The controller.
     * @param Adapter      $input        The input provider.
     * @param Adapter      $repository   The news repository.
     * @param string       $urlSuffix    The url suffix.
     */
    public function __construct(
        RequestStack $requestStack,
        Connection $connection,
        Adapter $controller,
        Adapter $input,
        Adapter $repository,
        $urlSuffix
    ) {
        $this->requestStack = $requestStack;
        $this->connection   = $connection;
        $this->controller   = $controller;
        $this->input        = $input;
        $this->repository   = $repository;
        $this->urlSuffix    = $urlSuffix;
    }

    /**
     * Count the news items.
     *
     * @param array          $archives The news archives.
     * @param string         $featured The news archive filter news by featured.
     * @param ModuleNewsList $list     The news list.
     *
     * @return int
     */
    public function countItems(array $archives, $featured, ModuleNewsList $list)
    {
        $preFilter = '';
        if (!$this->input->get('filterTag') && !$list->newsTagsPreFilter) {
            return $this->repository->countPublishedByPids($archives, $featured);
        } elseif (!$this->input->get('filterTag')) {
            $preFilter = $list->newsTagsPreFilter;
        }

        $matchedNewsIds = $this->matchNewsIds($archives, $featured, null, null, $preFilter);
        if (!\count($matchedNewsIds)) {
            $this->redirectWithoutFilter();
        }

        $options = [
            'column' => ['tl_news.id IN (' . \implode(',', $matchedNewsIds) . ')']
        ];

        return $this->repository->countPublishedByPids($archives, $featured, $options);
    }

    /**
     * Fetch the news items.
     *
     * @param array          $archives The news archives.
     * @param string         $featured The news archive filter news by featured.
     * @param integer        $limit    The limit of news.
     * @param integer        $offset   The offset for start news.
     * @param ModuleNewsList $list     The news list.
     *
     * @return \Contao\Model\Collection|NewsModel|NewsModel[]|null
     */
    public function fetchItems(array $archives, $featured, $limit, $offset, ModuleNewsList $list)
    {
        $preFilter = '';
        if (!$this->input->get('filterTag') && !$list->newsTagsPreFilter) {
            return $this->repository->findPublishedByPids($archives, $featured, $limit, $offset);
        } elseif (!$this->input->get('filterTag')) {
            $preFilter = $list->newsTagsPreFilter;
        }

        $matchedNewsIds = $this->matchNewsIds($archives, $featured, $limit, $offset, $preFilter);
        if (!\count($matchedNewsIds)) {
            $this->redirectWithoutFilter();
        }

        $options = [
            'column' => ['tl_news.id IN (' . \implode(',', $matchedNewsIds) . ')']
        ];

        return $this->repository->findPublishedByPids($archives, $featured, 0, 0, $options);
    }

    /**
     * Match the news identifier.
     *
     * @param array  $archives      The archive list.
     * @param string $featured      Determine for feature news.
     * @param null   $limit         The limit of news.
     * @param null   $offset        The start offset.
     * @param string $tagIdentifier The tag identifier so such for pre filter.
     *
     * @return array
     */
    private function matchNewsIds(array $archives, $featured, $limit = null, $offset = null, $tagIdentifier = '')
    {
        $findFeatured = ('featured' === $featured) ? true : (('unfeatured' === $featured) ? false : null);

        $time = new \DateTime();
        $time->setTimestamp($time->getTimestamp() - ($time->getTimestamp() % 60));

        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder
            ->select('n.id')
            ->from('tl_news', 'n')
            ->innerJoin('n', 'tl_news_tags_relation', 'r', 'r.news = n.id')
            ->innerJoin('r', 'tl_news_tags', 't', 't.id = r.tag')
            ->where($queryBuilder->expr()->in('n.pid', ':archives'))
            ->andWhere($queryBuilder->expr()->eq('n.published', ':published'))
            ->andWhere(
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->eq('n.start', ':empty'),
                    $queryBuilder->expr()->lte('n.start', ':startTime')
                )
            )
            ->andWhere(
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->eq('n.stop', ':empty'),
                    $queryBuilder->expr()->gt('n.stop', ':startTime')
                )
            )
            ->andWhere(
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->eq('t.alias', ':tagAlias'),
                    $queryBuilder->expr()->eq('t.id', ':tagIdentifier')
                )
            )
            ->setParameter(':archives', \array_map('\intval', $archives), Connection::PARAM_STR_ARRAY)
            ->setParameter(':published', 1)
            ->setParameter(':empty', '')
            ->setParameter(':startTime', $time->getTimestamp())
            ->setParameter(':stopTime', ($time->getTimestamp() + 60))
            ->setParameter(':tagAlias', $this->input->get('filterTag'))
            ->setParameter(':tagIdentifier', $tagIdentifier)
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        if (!(null === $findFeatured)) {
            $queryBuilder
                ->andWhere($queryBuilder->expr()->eq('n.featured', ':featured'))
                ->setParameter(':featured', $findFeatured ? 1 : '');
        }

        $statement = $queryBuilder->execute();

        $matchingIds = [];
        foreach ($statement->fetchAll(\PDO::FETCH_OBJ) as $news) {
            $matchingIds[] = (int) $news->id;
        }

        return $matchingIds;
    }

    /**
     * Redirect to the page with out the filter parameter.
     *
     * @return void
     */
    private function redirectWithoutFilter()
    {
        $pathInfo = $this->requestStack->getCurrentRequest()->getPathInfo();
        if ($this->urlSuffix) {
            $pathInfo = \substr($pathInfo, 0, -\strlen($this->urlSuffix));
        }

        if ($this->input->get('filterTag')) {
            $pathInfo = \substr($pathInfo, 0, -\strlen('/filterTag/' . $this->input->get('filterTag')));
        }

        $this->controller
            ->redirect($this->requestStack->getCurrentRequest()->getBaseUrl() . $pathInfo . $this->urlSuffix);
    }
}
