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
use Contao\Input;
use Doctrine\DBAL\Connection;

/**
 * This class handle for manipulate news tags, if load the table in modal view for select tags for news.
 */
class ManipulateNewsModalView
{
    /**
     * The database connection.
     *
     * @var Connection
     */
    private $connection;

    /**
     * The input.
     *
     * @var Input
     */
    private $input;

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
     * Handle for manipulate news tags, if load the table in modal view for select tags for news.
     *
     * @param DataContainer $container The data container.
     *
     * @return void
     */
    public function handle(DataContainer $container)
    {
        if (!$this->input->get('popup')
            || !$this->input->get('archiveId')
            || !$this->input->get('newsId')
            || !('tl_news_archive' === $this->input->get('archiveTable'))
        ) {
            return;
        }

        $this->removePanel($container->table);
        $this->addFilter($container->table, $this->input->get('archiveId'));
        $this->loadCss();
        $this->injectSelectModelsScript();
    }

    /**
     * Remove the panel.
     *
     * @param string $providerName The data provider name.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function removePanel($providerName)
    {
        $GLOBALS['TL_DCA'][$providerName]['list']['sorting']['panelLayout'] = '';
    }

    /**
     * Filter the tags for used in the archive.
     *
     * @param string $providerName The data provider name.
     * @param string $archiveId    The archive id.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function addFilter($providerName, $archiveId)
    {
        $GLOBALS['TL_DCA'][$providerName]['list']['sorting']['filter'] = \array_merge(
            (array) $GLOBALS['TL_DCA'][$providerName]['list']['sorting']['filter'],
            [
                ['archives LIKE ?', '%%"' . $archiveId . '"%%']
            ]
        );
    }

    /**
     * Load the css from bundle directory.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function loadCss()
    {
        $GLOBALS['TL_CSS'][] = '/bundles/blackforestcontaonewstags/css/news-select-tags-modal.css';
    }

    /**
     * Inject javascript for add checked state for available relation.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function injectSelectModelsScript()
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
            return;
        }

        $selector = '';
        foreach ($statement->fetchAll(\PDO::FETCH_OBJ) as $relation) {
            if ($selector) {
                $selector .= ', ';
            }

            $selector .= '#ids_' . $relation->tag;
        }

        $GLOBALS['TL_MOOTOOLS'][] = "
        <script>
            var checkbox = document.querySelectorAll('${selector}');
            checkbox.forEach(function(e) {
                e.checked = true;
            });

            var formSubmit = document.querySelector('input[name=FORM_SUBMIT]');
            formSubmit.value = 'tl_apply';
        </script>
        ";
    }
}
