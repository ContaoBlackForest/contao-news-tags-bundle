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

use Contao\BackendUser;
use Contao\Controller;
use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Image;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManager;

/**
 * This handle the model command for select tags.
 */
class SelectTagsModelCommand
{
    /**
     * The database connection.
     *
     * @var Connection
     */
    private $connection;

    /**
     * The image controller.
     *
     * @var Image
     */
    private $image;

    /**
     * The backend user.
     *
     * @var BackendUser
     */
    private $user;

    /**
     * The controller.
     *
     * @var Controller
     */
    private $controller;

    /**
     * The string util.
     *
     * @var StringUtil
     */
    private $stringUtil;

    /**
     * The security token.
     *
     * @var CsrfToken
     */
    private $token;

    /**
     * The constructor.
     *
     * @param ContaoFrameworkInterface $framework    The framework.
     * @param RequestStack             $request      The request.
     * @param Connection               $connection   The database connection.
     * @param Adapter                  $image        The image controller.
     * @param Adapter                  $controller   The controller.
     * @param Adapter                  $stringUtil   The string util.
     * @param CsrfTokenManager         $tokenManager The token manager.
     * @param string                   $tokenName    The token name.
     */
    public function __construct(
        ContaoFrameworkInterface $framework,
        RequestStack $request,
        Connection $connection,
        Adapter $image,
        Adapter $controller,
        Adapter $stringUtil,
        CsrfTokenManager $tokenManager,
        $tokenName
    ) {
        $this->connection = $connection;
        $this->image      = $image;
        $this->controller = $controller;
        $this->stringUtil = $stringUtil;

        if (!$request->getCurrentRequest()
            || !('contao_backend' === $request->getCurrentRequest()->get('_route'))
        ) {
            return;
        }

        $this->token = $tokenManager->getToken($tokenName);
        $this->user  = $framework->createInstance(BackendUser::class);
    }

    /**
     * Handle the model command for select tags.
     *
     * @param array  $row        The row date.
     * @param string $href       The href.
     * @param string $label      The label.
     * @param string $title      The title.
     * @param string $icon       The icon.
     * @param string $attributes The attributes.
     *
     * @return string
     */
    public function handle(array $row, $href, $label, $title, $icon, $attributes)
    {
        if (!$this->user->canEditFieldsOf('tl_news')
            || !$this->hasArchiveTags($row['pid'])
        ) {
            return '';
        }

        return $this->renderButton($row, $href, $label, $title, $icon, $attributes);
    }

    /**
     * Determine if the news archive has configured tags.
     *
     * @param string $archiveId The news archive id.
     *
     * @return bool
     */
    private function hasArchiveTags($archiveId)
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder
            ->select('t.id')
            ->from('tl_news_tags', 't')
            ->where($queryBuilder->expr()->like('t.archives', ':archives'))
            ->setParameter(':archives', '%"' . $archiveId . '"%');

        $statement = $queryBuilder->execute();

        return (bool) $statement->rowCount();
    }


    /**
     * Render the model command for select tags.
     *
     * @param array  $row        The row date.
     * @param string $href       The href.
     * @param string $label      The label.
     * @param string $title      The title.
     * @param string $icon       The icon.
     * @param string $attributes The attributes.
     *
     * @return string
     */
    private function renderButton(array $row, $href, $label, $title, $icon, $attributes)
    {
        return \sprintf(
            '<a href="%s" title="%s"%s>%s</a>',
            $this->generateUrl($href, $row['pid'], $row['id']),
            $this->stringUtil->specialchars($title),
            $this->generateOnClick($attributes, $row['id']),
            $this->image->getHtml($icon, $label)
        );
    }

    /**
     * Generate the button url.
     *
     * @param string $href      The href.
     * @param string $archiveId The news archive id.
     * @param string $newsId    The news id.
     *
     * @return string
     */
    private function generateUrl($href, $archiveId, $newsId)
    {
        return $this->controller->addToUrl(
            \sprintf(
                '%s&amp;popup=1&amp;archiveTable=tl_news_archive&amp;archiveId=%s&amp;newsId=%s&amp;rt=%s',
                $href,
                $archiveId,
                $newsId,
                $this->token->getValue()
            ),
            true,
            ['id']
        );
    }

    /**
     * Generate the button on click event listener.
     *
     * @param string $attributes The attributes.
     * @param string $newsId     The news id.
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function generateOnClick($attributes, $newsId)
    {
        $title = \sprintf($GLOBALS['TL_LANG']['tl_news']['selectTags'][1], $newsId);

        return \sprintf(
            '%s  onClick=\'Backend.openModalIframe({"title":"%s","url":this.href}); event.preventDefault();\'',
            $attributes,
            $title
        );
    }
}
