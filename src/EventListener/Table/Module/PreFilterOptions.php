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

namespace BlackForest\Contao\News\Tags\EventListener\Table\Module;

use Contao\DataContainer;
use Doctrine\DBAL\Connection;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * This class handle the pre filter options.
 */
class PreFilterOptions
{
    /**
     * The database connection.
     *
     * @var Connection
     */
    private $connection;

    /**
     * The translator.
     *
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * The constructor.
     *
     * @param Connection          $connection The database connection.
     * @param TranslatorInterface $translator The translator.
     */
    public function __construct(Connection $connection, TranslatorInterface $translator)
    {
        $this->connection = $connection;
        $this->translator = $translator;
    }

    /**
     * Handle the pre filter options.
     *
     * @param DataContainer $container The data container.
     *
     * @return array
     */
    public function handleOptions(DataContainer $container)
    {
        $activeRecord = $container->activeRecord;

        // @codingStandardsIgnoreStart
        $archiveList = @\unserialize($activeRecord->news_archives);
        // @codingStandardsIgnoreEnd
        if (!$archiveList) {
            return [];
        }

        $tags = $this->fetchAllTags($archiveList);
        if (!\count($tags)) {
            return [];
        }

        $options = [];
        foreach ($tags as $tag) {
            $options[$tag->id] = $tag->title;
        }

        return $options;
    }

    /**
     * Fetch all tags.
     *
     * @param array $archiveList The news archive list.
     *
     * @return array
     */
    private function fetchAllTags(array $archiveList)
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder
            ->select('t.*')
            ->from('tl_news_tags', 't')
            ->innerJoin('t', 'tl_news_tags_relation', 'r', 'r.tag = t.id')
            ->where($queryBuilder->expr()->in('r.archive', ':archiveList'))
            ->setParameter(':archiveList', \array_map('\intval', $archiveList), Connection::PARAM_STR_ARRAY)
            ->orderBy('t.title')
            ->groupBy('t.id');

        $statement = $queryBuilder->execute();
        if (!$statement->rowCount()) {
            return [];
        }

        return $statement->fetchAll(\PDO::FETCH_OBJ);
    }
}
