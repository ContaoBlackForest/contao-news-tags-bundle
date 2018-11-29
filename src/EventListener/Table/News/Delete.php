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

namespace BlackForest\Contao\News\Tags\EventListener\Table\News;

use Contao\DataContainer;
use Doctrine\DBAL\Connection;

/**
 * This handle if a news model delete.
 */
class Delete
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
     * Handle if a news model delete.
     *
     * @param DataContainer $container The data container.
     *
     * @return void
     *
     * @throws \Doctrine\DBAL\DBALException Throws an exception if the delete failed.
     */
    public function handle(DataContainer $container)
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder
            ->select('r.id')
            ->from('tl_news_tags_relation', 'r')
            ->where($queryBuilder->expr()->eq('r.news', ':newsId'))
            ->setParameter(':newsId', $container->activeRecord->id);

        $statement = $queryBuilder->execute();
        if (!$statement->rowCount()) {
            return;
        }

        foreach ($statement->fetchAll(\PDO::FETCH_OBJ) as $relation) {
            $this->connection->delete('tl_news_tags_relation', ['tl_news_tags_relation.id' => $relation->id]);
        }
    }
}
