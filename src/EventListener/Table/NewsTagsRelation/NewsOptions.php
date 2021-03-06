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
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * This class handle the news options.
 */
class NewsOptions
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
     * Handle the news options.
     *
     * @param DataContainer $container The data container.
     *
     * @return array
     */
    public function handle(DataContainer $container)
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder
            ->select('n.id, n.headline')
            ->from('tl_news', 'n')
            ->orderBy('n.headline');

        $this->addFilterForEditAction($queryBuilder, $container);

        if ($container->activeRecord->id && !$container->activeRecord->archive) {
            return [];
        }

        $statement = $queryBuilder->execute();
        if (!$statement->rowCount()) {
            return [];
        }

        $options = [];
        foreach ($statement->fetchAll(\PDO::FETCH_OBJ) as $news) {
            $options[$news->id] = $news->headline;
        }

        return $options;
    }

    /**
     * Add the filter if edit the model.
     *
     * @param QueryBuilder  $queryBuilder The query builder.
     * @param DataContainer $container    The data container.
     *
     * @return void
     */
    private function addFilterForEditAction(QueryBuilder $queryBuilder, DataContainer $container)
    {
        if (!$container->activeRecord->id && !$container->activeRecord->archive) {
            return;
        }

        $queryBuilder
            ->where($queryBuilder->expr()->eq('n.pid', ':pid'))
            ->setParameter(':pid', $container->activeRecord->archive);
    }
}
