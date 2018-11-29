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

use Contao\BackendUser;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * This class handle the archives options.
 */
class ArchivesOptions
{
    /**
     * The backend user.
     *
     * @var BackendUser
     */
    private $user;

    /**
     * The database connection.
     *
     * @var Connection
     */
    private $connection;

    /**
     * The constructor.
     *
     * @param ContaoFrameworkInterface $framework  The framework.
     * @param RequestStack             $request    The request.
     * @param Connection               $connection The database connection.
     */
    public function __construct(
        ContaoFrameworkInterface $framework,
        RequestStack $request,
        Connection $connection
    ) {
        $this->connection = $connection;

        if (!$request->getCurrentRequest()
            || !('contao_backend' === $request->getCurrentRequest()->get('_route'))
        ) {
            return;
        }

        $this->user = $framework->createInstance(BackendUser::class);
    }

    /**
     * Handle the archives options.
     *
     * @return array
     */
    public function handle()
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder
            ->select('na.id', 'na.title')
            ->from('tl_news_archive', 'na')
            ->orderBy('na.title');
        if (!$this->user->isAdmin) {
            $queryBuilder
                ->where($queryBuilder->expr()->in('na.id', ':ids'))
                ->setParameter(':ids', \array_map('\intval', $this->user->news), Connection::PARAM_STR_ARRAY);
        }

        $statement = $queryBuilder->execute();
        if (!$statement->rowCount()) {
            return [];
        }

        $taggedArchive = $this->getTaggedArchive();
        if (!\count($taggedArchive)) {
            return [];
        }

        $options = [];
        foreach ($statement->fetchAll(\PDO::FETCH_OBJ) as $archive) {
            if (!\in_array($archive->id, $taggedArchive)) {
                continue;
            }

            $options[$archive->id] = $archive->title;
        }

        return $options;
    }

    /**
     * Get the tagged archives.
     *
     * @return array
     */
    private function getTaggedArchive()
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder
            ->select('nt.archives')
            ->from('tl_news_tags', 'nt');

        $statement = $queryBuilder->execute();
        if (!$statement->rowCount()) {
            return [];
        }

        $taggedArchive = [];
        foreach ($statement->fetchAll(\PDO::FETCH_OBJ) as $tag) {
            if (!($archives = \unserialize($tag->archives))) {
                continue;
            }

            $taggedArchive = \array_unique(\array_merge_recursive($taggedArchive, $archives));
        }

        return $taggedArchive;
    }
}
