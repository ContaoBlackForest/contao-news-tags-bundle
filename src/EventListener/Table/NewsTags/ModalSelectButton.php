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
use Contao\StringUtil;

/**
 * This handles the select buttons for the modal view.
 */
class ModalSelectButton
{
    /**
     * The input.
     *
     * @var Input
     */
    private $input;

    /**
     * The string util.
     *
     * @var StringUtil
     */
    private $stringUtil;

    /**
     * The constructor.
     *
     * @param Adapter $input      The input.
     * @param Adapter $stringUtil The string util.
     */
    public function __construct(Adapter $input, Adapter $stringUtil)
    {
        $this->input      = $input;
        $this->stringUtil = $stringUtil;
    }

    /**
     * Handles the select buttons for the modal view.
     *
     * @param array $buttons The buttons.
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function handle(array $buttons)
    {
        if (!$this->input->get('popup')) {
            return $buttons;
        }

        $applyButton = \sprintf(
            '<button type="%s" name="%s" id="%s" class="%s" accesskey="%s">%s</button>',
            'submit',
            'apply',
            'apply',
            'tl_submit',
            's',
            $this->stringUtil->specialchars($GLOBALS['TL_LANG']['MSC']['apply'])
        );

        return ['apply' => $applyButton];
    }
}
