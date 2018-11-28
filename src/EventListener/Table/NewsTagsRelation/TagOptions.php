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

namespace BlackForest\Contao\News\Tags\EventListener\Table\NewsTagsRelation;

use Contao\DataContainer;
use Doctrine\DBAL\Connection;

/**
 * This class handle the tag options.
 */
class TagOptions
{
    /**
     * The database connection.
     *
     * @var Connection
     */
    private $connection;

    /**
     * The constructor.
     *
     * @param Connection $connection The database connection.
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Handle the tag options.
     *
     * @param DataContainer $container The data container.
     *
     * @return array
     */
    public function handle(DataContainer $container)
    {
        if (!$container->activeRecord->archive || !$container->activeRecord->news) {
            return [];
        }

        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder
            ->select('nt.id, nt.title')
            ->from('tl_news_tags', 'nt')
            ->where($queryBuilder->expr()->like('nt.archives', ':archives'))
            ->setParameter(':archives', '%"' . $container->activeRecord->archive . '"%')
            ->orderBy('nt.title');

        $statement = $queryBuilder->execute();
        if (!$statement->rowCount()) {
            return [];
        }

        $options = [];
        foreach ($statement->fetchAll(\PDO::FETCH_OBJ) as $tag) {
            $options[$tag->id] = $tag->title;
        }

        return $options;
    }
}
