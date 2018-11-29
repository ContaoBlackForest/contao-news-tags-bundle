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

use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\Input;
use BlackForest\Contao\News\Tags\EventListener\Table\NewsTagsTrait;

/**
 * This class provide the permission handling for table news tags relation.
 */
class Permission
{
    use NewsTagsTrait;

    /**
     * Check permission to add relation for tags.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function checkPermissionToAdd()
    {
        if ($this->user->hasAccess('create', 'newstagsrelationp')) {
            return;
        }

        $GLOBALS['TL_DCA']['tl_news_tags_relation']['config']['closed'] = true;
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
    protected function prepareShowPermission()
    {
        if (!(\in_array($this->input->get('act'), ['edit', 'copy', 'delete', 'show']))) {
            return;
        }

        if ((!$this->user->hasAccess('delete', 'newstagsrelationp')
             && 'delete' === $this->input->get('act'))
            || !\in_array(
                $this->input->get('id'),
                $GLOBALS['TL_DCA']['tl_news_tags_relation']['list']['sorting']['root']
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
    protected function prepareMultiplePermission()
    {
        if (!(\in_array($this->input->get('act'), ['editAll', 'deleteAll', 'overrideAll']))) {
            return;
        }

        $session = $this->session->all();
        if (!$this->user->hasAccess('delete', 'newstagsrelationp') && $this->input->get('act') == 'deleteAll') {
            $session['CURRENT']['IDS'] = array();
        } else {
            $session['CURRENT']['IDS'] = \array_intersect(
                (array) $session['CURRENT']['IDS'],
                $GLOBALS['TL_DCA']['tl_news_tags_relation']['list']['sorting']['root']
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
    protected function prepareDefaultPermission()
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
        return $this->user->canEditFieldsOf('tl_news_tags_relation')
            ?
            $this->renderModelButton($row, $href, $label, $title, $icon, $attributes)
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
        return $this->user->hasAccess('delete', 'newstagsrelationp')
            ?
            $this->renderModelButton($row, $href, $label, $title, $icon, $attributes)
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
    private function renderModelButton(array $row, $href, $label, $title, $icon, $attributes)
    {
        return \sprintf(
            '<a href="%s" title="%s"%s>%s</a>',
            $this->controller->addToUrl($href . '&amp;id=' . $row['id']. '&amp;rt=' . $this->token->getValue()),
            $this->stringUtil->specialchars($title),
            $attributes,
            $this->image->getHtml($icon, $label)
        );
    }
}
