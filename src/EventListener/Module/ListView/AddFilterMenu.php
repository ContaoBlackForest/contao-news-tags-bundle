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

use Contao\CoreBundle\Framework\Adapter;
use Contao\FrontendTemplate;
use Contao\Input;
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
     *
     * @return void
     */
    public function handle(Template $template)
    {
        if (!('contao_frontend' === $this->requestStack->getCurrentRequest()->get('_route'))
            || !('newslist' === $template->type)
            || !$template->newsTagsFilter
        ) {
            return;
        }

        $tags = $this->fetchAllTags($template->archives, $template->news_featured);
        if (!\count($tags)) {
            return;
        }

        $pathInfo = \urldecode($this->requestStack->getCurrentRequest()->getPathInfo());
        if ($this->urlSuffix) {
            $pathInfo = \substr($pathInfo, 0, -\strlen($this->urlSuffix));
        }

        if ($this->input->get('filterTag')) {
            $pathInfo = \substr($pathInfo, 0, -\strlen('/filterTag/' . $this->input->get('filterTag')));
        }

        $data = [
            'pathInfo'  => $this->requestStack->getCurrentRequest()->getBaseUrl() . $pathInfo,
            'urlSuffix' => $this->urlSuffix,
            'moduleId'  => $template->id,
            'tags'      => $tags,
            'active'    => $this->input->get('filterTag')
        ];

        $filterTemplate = new FrontendTemplate('news_list_tags_filter');
        $filterTemplate->setData($data);

        $template->articles = \array_merge([$filterTemplate->parse()], $template->articles);

        $this->addListViewToSession($data['pathInfo']);
    }

    /**
     * Fetch all tags.
     *
     * @param array  $archives The news archives.
     * @param string $featured Filter the news list by featured news.
     *
     * @return array
     */
    private function fetchAllTags(array $archives, $featured)
    {
        $findFeatured = ('featured' === $featured) ? true : (('unfeatured' === $featured) ? false : null);

        $time = new \DateTime();
        $time->setTimestamp($time->getTimestamp() - ($time->getTimestamp() % 60));

        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder
            ->select('t.*')
            ->from('tl_news_tags', 't')
            ->innerJoin('t', 'tl_news_tags_relation', 'r', 'r.tag = t.id')
            ->innerJoin('r', 'tl_news', 'n', 'n.id = r.news')
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
            ->setParameter(':archives', \array_map('\intval', $archives), Connection::PARAM_STR_ARRAY)
            ->setParameter(':published', 1)
            ->setParameter(':empty', '')
            ->setParameter(':startTime', $time->getTimestamp())
            ->setParameter(':stopTime', ($time->getTimestamp() + 60))
            ->orderBy('t.title')
            ->groupBy('t.id');

        if (!(null === $findFeatured)) {
            $queryBuilder
                ->andWhere($queryBuilder->expr()->eq('n.featured', ':featured'))
                ->setParameter(':featured', $findFeatured ? 1 : '');
        }

        $statement = $queryBuilder->execute();
        if (!$statement->rowCount()) {
            return [];
        }

        return $statement->fetchAll(\PDO::FETCH_OBJ);
    }

    /**
     * Add the relative list view url to the session.
     * For find the relative list view url by detail page to go back, when generate the tag list as link list.
     *
     * @param string $listViewUrl The relative list view url.
     *
     * @return void
     */
    private function addListViewToSession($listViewUrl)
    {
        $this->sessionBag->set('news-tags-last-list-view', $listViewUrl);
    }
}
