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
use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\NewsArchiveModel;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * This class handle the archives options.
 */
class ArchivesOptions
{
    /**
     * The repository for news archive.
     *
     * @var NewsArchiveModel
     */
    private $repository;

    /**
     * The backend user.
     *
     * @var BackendUser
     */
    private $user;

    /**
     * The constructor.
     *
     * @param ContaoFrameworkInterface $framework  The framework.
     * @param RequestStack             $request    The request.
     * @param Adapter                  $repository The repository for news archive.
     */
    public function __construct(
        ContaoFrameworkInterface $framework,
        RequestStack $request,
        Adapter $repository
    ) {
        $this->repository = $repository;

        if (!$request->getCurrentRequest()
            || !('contao_backend' === $request->getCurrentRequest()->get('_route'))
        ) {
            return;
        }

        $this->user = $framework->createInstance(BackendUser::class);
    }

    /**
     * Handle the archives options.
     *
     * @return array
     */
    public function handle()
    {
        if ($this->user->isAdmin) {
            $objArchive = $this->repository->findAll();
        } else {
            $objArchive = $this->repository->findMultipleByIds($this->user->news);
        }

        $return = [];

        if ($objArchive !== null) {
            while ($objArchive->next()) {
                $return[$objArchive->id] = $objArchive->title;
            }
        }

        return $return;
    }
}
