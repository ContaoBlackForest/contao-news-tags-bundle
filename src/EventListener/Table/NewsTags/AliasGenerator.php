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
use Contao\DataContainer;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;

/**
 * This class generate the alias.
 */
class AliasGenerator
{
    /**
     * The database connection.
     *
     * @var Connection
     */
    private $connection;

    /**
     * The string util.
     *
     * @var StringUtil
     */
    private $stringUtil;

    /**
     * The constructor.
     *
     * @param Connection $connection The database connection.
     * @param Adapter    $stringUtil The string util.
     */
    public function __construct(
        Connection $connection,
        Adapter $stringUtil
    ) {
        $this->connection = $connection;
        $this->stringUtil = $stringUtil;
    }

    /**
     * Handle the alias auto generating.
     *
     * @param string        $value     The value.
     * @param DataContainer $container The data container.
     *
     * @return string
     */
    public function handle($value, DataContainer $container)
    {
        if (!('' === $value)) {
            return $value;
        }

        $alias = $this->stringUtil->generateAlias($container->activeRecord->title);

        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder
            ->select('t.id', 't.alias')
            ->from('tl_news_tags', 't')
            ->where($queryBuilder->expr()->like('t.alias', ':likeAlias'))
            ->setParameter(':likeAlias', $alias . '%')
            ->orderBy('t.alias');

        $statement = $queryBuilder->execute();
        if (!$statement->rowCount()) {
            return $alias;
        }

        $foundAlias = $statement->fetchAll(\PDO::FETCH_OBJ);

        return $this->prepareAlias($alias, $foundAlias, $container);
    }

    /**
     * Prepare the alias.
     *
     * @param string        $alias      The alias.
     * @param array         $foundAlias The found alias.
     * @param DataContainer $container  The data container.
     *
     * @return string
     */
    private function prepareAlias($alias, array $foundAlias, DataContainer $container)
    {
        $compare = [];
        foreach ($foundAlias as $compareAlias) {
            if ($container->activeRecord->id === $compareAlias->id) {
                continue;
            }

            if ($alias === $compareAlias->alias) {
                $compare[] = $compareAlias->alias;
            }

            // Filter for alias who end with an integer.
            $count = (int) \substr($compareAlias->alias, \strlen($alias . '-'));
            if (!$count) {
                continue;
            }

            $compare[] = $compareAlias->alias;
        }

        if (!\in_array($alias, $compare)) {
            return $alias;
        }

        if (!$compare
            || ((1 === \count($compare)) && (\in_array($alias, $compare)))
        ) {
            return $alias . '-1';
        }

        return $alias . '-' . (\count($compare) + 1);
    }
}
