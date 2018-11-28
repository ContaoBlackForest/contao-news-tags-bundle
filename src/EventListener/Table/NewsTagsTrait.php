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

namespace BlackForest\Contao\News\Tags\EventListener\Table;

use Contao\BackendUser;
use Contao\Controller;
use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Image;
use Contao\Input;
use Contao\StringUtil;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Trait for table tl_news_tags and tl_news_tags_relation.
 */
trait NewsTagsTrait
{
    /**
     * The backend user.
     *
     * @var BackendUser.
     */
    private $user;

    /**
     * The session.
     *
     * @var SessionBagInterface
     */
    private $session;

    /**
     * The string util.
     *
     * @var StringUtil
     */
    private $stringUtil;

    /**
     * The input provider.
     *
     * @var Input
     */
    private $input;

    /**
     * The contao controller.
     *
     * @var Controller
     */
    private $controller;

    /**
     * The contao image controller.
     *
     * @var Image
     */
    private $image;

    /**
     * The constructor.
     *
     * @param ContaoFrameworkInterface $framework  The framework.
     * @param RequestStack             $request    The request.
     * @param SessionInterface         $session    The session.
     * @param Adapter                  $stringUtil The string util.
     * @param Adapter                  $input      The input.
     * @param Adapter                  $controller The contao controller.
     * @param Adapter                  $image      The contao image controller.
     */
    public function __construct(
        ContaoFrameworkInterface $framework,
        RequestStack $request,
        SessionInterface $session,
        Adapter $stringUtil,
        Adapter $input,
        Adapter $controller,
        Adapter $image
    ) {
        $this->stringUtil = $stringUtil;
        $this->input      = $input;
        $this->controller = $controller;
        $this->image      = $image;

        if (!$request->getCurrentRequest()
            || !('contao_backend' === $request->getCurrentRequest()->get('_route'))
        ) {
            return;
        }

        $this->user    = $framework->createInstance(BackendUser::class);
        $this->session = $session->getBag('contao_backend');
    }

    /**
     * Handle the user permission of the news tags.
     *
     * @return void
     */
    public function handlePermission()
    {
        if ($this->user->isAdmin) {
            return;
        }

        $this->checkPermissionToAdd();
        $this->prepareShowPermission();
        $this->prepareMultiplePermission();
        $this->prepareDefaultPermission();
    }

    /**
     * Check permission to add new record.
     *
     * @return void
     */
    abstract protected function checkPermissionToAdd();

    /**
     * Prepare the permission for the show action.
     *
     * @return void
     */
    abstract protected function prepareShowPermission();

    /**
     * Prepare the multiple model action permission.
     *
     * @return void
     */
    abstract protected function prepareMultiplePermission();

    /**
     * Prepare the default permission.
     *
     * @return void
     */
    abstract protected function prepareDefaultPermission();
}
