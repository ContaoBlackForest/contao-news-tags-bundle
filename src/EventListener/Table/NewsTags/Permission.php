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

use Contao\BackendUser;
use Contao\Controller;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Image;
use Contao\Input;
use Contao\StringUtil;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

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
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function handlePermission()
    {
        if ($this->user->isAdmin) {
            return;
        }

        // Check permissions to add feeds
        if (!$this->user->hasAccess('create', 'newstagsp')) {
            $GLOBALS['TL_DCA']['tl_news_tags']['config']['closed'] = true;
        }

        $this->prepareShowPermission();
        $this->prepareMultiplePermission();
        $this->prepareDefaultPermission();
    }

    /**
     * Prepare the permission for the show action.
     *
     * @return void
     *
     * @throws AccessDeniedException Throws an exception if the user has not enough permission.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function prepareShowPermission()
    {
        if (!(\in_array($this->input->get('act'), ['edit', 'copy', 'delete', 'show']))) {
            return;
        }

        if ((!$this->user->hasAccess('delete', 'newstagsp')
             && 'delete' === $this->input->get('act'))
            || !\in_array(
                $this->input->get('id'),
                $GLOBALS['TL_DCA']['tl_news_tags']['list']['sorting']['root']
            )
        ) {
            throw new AccessDeniedException(
                \sprintf(
                    'Not enough permissions to %s news feed ID %s.',
                    $this->input->get('act'),
                    $this->input->get('id')
                )
            );
        }
    }

    /**
     * Prepare the multiple model action permission.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function prepareMultiplePermission()
    {
        if (!(\in_array($this->input->get('act'), ['editAll', 'deleteAll', 'overrideAll']))) {
            return;
        }

        $session = $this->session->all();
        if (!$this->user->hasAccess('delete', 'newstagsp') && $this->input->get('act') == 'deleteAll') {
            $session['CURRENT']['IDS'] = array();
        } else {
            $session['CURRENT']['IDS'] = \array_intersect(
                (array) $session['CURRENT']['IDS'],
                $GLOBALS['TL_DCA']['tl_news_tags']['list']['sorting']['root']
            );
        }

        $this->session->replace($session);
    }

    /**
     * Prepare the default permission.
     *
     * @return void
     *
     * @throws AccessDeniedException Throws an exception if the user has not enough permission.
     */
    private function prepareDefaultPermission()
    {
        if (\strlen(Input::get('act'))) {
            throw new AccessDeniedException(
                \sprintf(
                    'Not enough permissions to %s news feeds.',
                    $this->input->get('act')
                )
            );
        }
    }

    /**
     * Handle the permission of button if user can edit models.
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
    public function handleButtonCanEdit(array $row, $href, $label, $title, $icon, $attributes)
    {
        return $this->user->canEditFieldsOf('tl_news_tags')
            ?
            $this->renderButton($row, $href, $label, $title, $icon, $attributes)
            :
            $this->image->getHtml(\preg_replace('/\.svg$/i', '_.svg', $icon)) . ' ';
    }

    /**
     * Handle the permission of button if user can delete models.
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
    public function handleButtonCanDelete(array $row, $href, $label, $title, $icon, $attributes)
    {
        return $this->user->hasAccess('delete', 'newstagsp')
            ?
            $this->renderButton($row, $href, $label, $title, $icon, $attributes)
            :
            $this->image->getHtml(\preg_replace('/\.svg$/i', '_.svg', $icon)) . ' ';
    }

    /**
     * Render the model command button.
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
            $this->controller->addToUrl($href . '&amp;id=' . $row['id']),
            $this->stringUtil->specialchars($title),
            $attributes,
            $this->image->getHtml($icon, $label)
        );
    }
}
