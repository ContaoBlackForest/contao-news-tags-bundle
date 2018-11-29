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

namespace BlackForest\Contao\News\Tags\EventListener\Table\NewsTags;

use Contao\CoreBundle\Framework\Adapter;
use Contao\Input;
use Doctrine\DBAL\Connection;

/**
 * This class handle for apply the news tags relations.
 */
class ApplyRelationsNewsModalView
{
    /**
     * The input.
     *
     * @var Input
     */
    private $input;

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
     * @param Adapter    $input      The input.
     */
    public function __construct(Connection $connection, Adapter $input)
    {
        $this->connection = $connection;
        $this->input      = $input;
    }

    /**
     * Handle for apply the news tags relations.
     *
     * @return void
     *
     * @throws \Doctrine\DBAL\DBALException Throws an exception if the database statement failed.
     */
    public function handle()
    {
        if (!$this->input->get('popup')
            || !$this->input->get('archiveId')
            || !$this->input->get('newsId')
            || !('tl_news_archive' === $this->input->get('archiveTable'))
            || !$this->input->post('FORM_SUBMIT')
            || !('' === $this->input->post('apply'))
        ) {
            return;
        }

        $this->applyRelation(\array_diff((array) $this->input->post('IDS'), $this->fetchAllTagsRelationByNews()));
        $this->removeRelation(\array_diff($this->fetchAllTagsRelationByNews(), (array) $this->input->post('IDS')));
    }

    /**
     * Fetch all news relation by filter news and archive.
     *
     * @return array
     */
    private function fetchAllTagsRelationByNews()
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder
            ->select('r.tag')
            ->from('tl_news_tags_relation', 'r')
            ->where($queryBuilder->expr()->eq('r.archive', ':archive'))
            ->setParameter(':archive', $this->input->get('archiveId'))
            ->andWhere($queryBuilder->expr()->eq('r.news', ':news'))
            ->setParameter(':news', $this->input->get('newsId'));

        $statement = $queryBuilder->execute();
        if (!$statement->rowCount()) {
            return [];
        }

        $idList = [];
        foreach ($statement->fetchAll(\PDO::FETCH_OBJ) as $relation) {
            $idList[] = $relation->tag;
        }

        return $idList;
    }

    /**
     * Apply the relation of news tags.
     *
     * @param array $new The new id list of tags.
     *
     * @return void
     *
     * @throws \Doctrine\DBAL\DBALException Throws an exception if can not insert new relation to data provider.
     */
    private function applyRelation(array $new)
    {
        if (!\count($new)) {
            return;
        }

        $data = [
            'tl_news_tags_relation.tstamp'  => \time(),
            'tl_news_tags_relation.archive' => $this->input->get('archiveId'),
            'tl_news_tags_relation.news'    => $this->input->get('newsId')
        ];

        foreach ($new as $id) {
            $data['tag'] = $id;

            $this->connection->insert('tl_news_tags_relation', $data);
        }
    }

    /**
     * Remove the unused relation.
     *
     * @param array $remove The ids list for remove.
     *
     * @return void
     *
     * @throws \Doctrine\DBAL\DBALException Throws an exception if the database call failed.
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException Throws an exception if arguments invalid.
     */
    private function removeRelation(array $remove)
    {
        if (!\count($remove)) {
            return;
        }

        $identifier = [
            'tl_news_tags_relation.archive' => $this->input->get('archiveId'),
            'tl_news_tags_relation.news'    => $this->input->get('newsId')
        ];

        foreach ($remove as $id) {
            $identifier['tl_news_tags_relation.tag'] = $id;

            $this->connection->delete('tl_news_tags_relation', $identifier);
        }
    }
}
