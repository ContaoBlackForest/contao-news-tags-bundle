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

namespace BlackForest\Contao\News\Tags\EventListener\Table\NewsArchive;

use Contao\BackendUser;
use Contao\Controller;
use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Image;
use Contao\StringUtil;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * This class provide the permission handling.
 */
class Permission
{
    /**
     * The backend user.
     *
     * @var BackendUser.
     */
    private $user;

    /**
     * The string util.
     *
     * @var StringUtil
     */
    private $stringUtil;

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
     * @param Adapter                  $stringUtil The string util.
     * @param Adapter                  $controller The contao controller.
     * @param Adapter                  $image      The contao image controller.
     */
    public function __construct(
        ContaoFrameworkInterface $framework,
        RequestStack $request,
        Adapter $stringUtil,
        Adapter $controller,
        Adapter $image
    ) {
        $this->stringUtil = $stringUtil;
        $this->controller = $controller;
        $this->image      = $image;

        if (!$request->getCurrentRequest()
            || !('contao_backend' === $request->getCurrentRequest()->get('_route'))
        ) {
            return;
        }

        $this->user = $framework->createInstance(BackendUser::class);
    }

    /**
     * Handle the permission of the global tags command.
     *
     * @param string $href       The href.
     * @param string $label      The label.
     * @param string $title      The title.
     * @param string $class      The icon.
     * @param string $attributes The attributes.
     *
     * @return string
     */
    public function handleGlobalTagsCommand($href, $label, $title, $class, $attributes)
    {
        return ($this->user->isAdmin || $this->user->hasAccess('create', 'newstagsp'))
            ?
            $this->renderButton($href, $label, $title, $class, $attributes)
            :
            '';
    }

    /**
     * Render the model command button.
     *
     * @param string $href       The href.
     * @param string $label      The label.
     * @param string $title      The title.
     * @param string $class      The class.
     * @param string $attributes The attributes.
     *
     * @return string
     */
    private function renderButton($href, $label, $title, $class, $attributes)
    {
        return \sprintf(
            '<a href="%s" class="%s" title="%s"%s>%s</a>',
            $this->controller->addToUrl($href),
            $class,
            $this->stringUtil->specialchars($title),
            $attributes,
            $label
        );
    }
}
