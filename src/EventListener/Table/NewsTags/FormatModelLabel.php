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
use Symfony\Component\Translation\TranslatorInterface;

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
     * @param Adapter             $input      The input.
     */
    public function __construct(Connection $connection, TranslatorInterface $translator, Adapter $input)
    {
        $this->connection = $connection;
        $this->input      = $input;
        $this->translator = $translator;
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

        $label  = '<p>' . $this->trans('MOD.tl_news_tags') . ': ';
        $label .= '<br>&nbsp;&nbsp;' . $row['title'] . '</p>';

        $archiveNames = $this->fetchNewsArchiveNames($row['archives']);


        $label .= '<p style="margin-bottom: 0;">' . $this->trans('tl_news_tags.archives.0', 'tl_news_tags') . ': ';
        if (!\count($archiveNames)) {
            $label .= '<br>&nbsp;' . $this->trans('MSC.noResult');
        }
        if (\count($archiveNames)) {
            $label .= '<ul>';

            foreach ($archiveNames as $archiveName) {
                $label .= '<li>- ' . $archiveName . '</li>';
            }

            $label .= '</ul>';
        }
        $label .= '</p>';

        if ($row['tagLink']) {
            $page   = $this->fetchPageById($row['tagLinkFallback']);
            $label .= '<p>' . $this->trans('tl_news_tags.tagLinkFallback.0', 'tl_news_tags') . ': ';
            $label .= '<br>&nbsp;&nbsp;' . $page->title . '</p>';
        }

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

    /**
     * Fetch the page by id.
     *
     * @param string $pageId The page id.
     *
     * @return mixed|null
     */
    private function fetchPageById($pageId)
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder
            ->select('p.*')
            ->from('tl_page', 'p')
            ->where($queryBuilder->expr()->eq('p.id', ':pageId'))
            ->setParameter(':pageId', $pageId);

        $statement = $queryBuilder->execute();
        if (!$statement->rowCount()) {
            return null;
        }

        return $statement->fetch(\PDO::FETCH_OBJ);
    }

    /**
     * Translate the identifier.
     *
     * @param string $identifier The translation identifier.
     * @param string $domain     The translation domain.
     *
     * @return string
     */
    private function trans($identifier, $domain = 'default')
    {
        return $this->translator->trans($identifier, [], 'contao_' . $domain);
    }
}
