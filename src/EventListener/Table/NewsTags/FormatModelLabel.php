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
 * This handle the formatting of the model label.
 */
class FormatModelLabel
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
     * Handle the formatting of the model label.
     *
     * @param array $row The row data.
     *
     * @return string
     */
    public function handle(array $row)
    {
        if ($this->input->get('popup')) {
            return $row['title'];
        }

        $label  = '<p>' . $GLOBALS['TL_LANG']['MOD']['tl_news_tags'] . ': ';
        $label .= '<br>&nbsp;' . $row['title'] . '</p>';

        $archiveNames = $this->fetchNewsArchiveNames($row['archives']);

        $label .= '<p style="margin-bottom: 0;">' . $GLOBALS['TL_LANG']['tl_news_tags']['archives'][0] . ': ';
        if (!\count($archiveNames)) {
            $label .= '<br>&nbsp;' . $GLOBALS['TL_LANG']['MSC']['noResult'];
        }
        if (\count($archiveNames)) {
            $label .= '<ul>';

            foreach ($archiveNames as $archiveName) {
                $label .= '<li>- ' . $archiveName . '</li>';
            }

            $label .= '</ul>';
        }
        $label .= '</p>';

        return $label;
    }

    /**
     * Fetch the archive names.
     *
     * @param string $archives The archive list.
     *
     * @return array
     */
    private function fetchNewsArchiveNames($archives)
    {
        if (!$archives) {
            return [];
        }

        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder
            ->select('a.title')
            ->from('tl_news_archive', 'a')
            ->where($queryBuilder->expr()->in('a.id', ':archiveIds'))
            ->setParameter(':archiveIds', \array_map('\intval', \unserialize($archives)), Connection::PARAM_STR_ARRAY);

        $statement = $queryBuilder->execute();
        if (!$statement->rowCount()) {
            return [];
        }

        $archiveNames = [];
        foreach ($statement->fetchAll(\PDO::FETCH_OBJ) as $archive) {
            $archiveNames[] = $archive->title;
        }

        return $archiveNames;
    }
}
