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

namespace BlackForest\Contao\News\Tags\Test;

use BlackForest\Contao\News\Tags\BlackForestContaoNewsTagsBundle;
use BlackForest\Contao\News\Tags\DependencyInjection\BlackForestContaoNewsTagsExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Resource\ComposerResource;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class BlackForestContaoNewsTagsBundleTest
 *
 * @covers \BlackForest\Contao\News\Tags\BlackForestContaoNewsTagsBundle
 */
class BlackForestContaoNewsTagsBundleTest extends TestCase
{
    public function testCanBeInstantiated()
    {
        $bundle = new BlackForestContaoNewsTagsBundle();

        $this->assertInstanceOf(BlackForestContaoNewsTagsBundle::class, $bundle);
    }

    public function testReturnsTheContainerExtension()
    {
        $extension = (new BlackForestContaoNewsTagsBundle())->getContainerExtension();

        $this->assertInstanceOf(BlackForestContaoNewsTagsExtension::class, $extension);
    }

    /**
     * @covers \BlackForest\Contao\News\Tags\DependencyInjection\BlackForestContaoNewsTagsExtension::load
     */
    public function testLoadExtensionConfiguration()
    {
        $extension = (new BlackForestContaoNewsTagsBundle())->getContainerExtension();
        $container = new ContainerBuilder();

        $extension->load([], $container);

        $this->assertInstanceOf(ComposerResource::class, $container->getResources()[0]);
        $this->assertInstanceOf(FileResource::class, $container->getResources()[1]);
        $this->assertInstanceOf(FileResource::class, $container->getResources()[2]);
        $this->assertInstanceOf(FileResource::class, $container->getResources()[3]);
        $this->assertInstanceOf(FileResource::class, $container->getResources()[4]);
        $this->assertSame(
            \dirname(\dirname(__DIR__)) . '/src/Resources/config/table/news-archive.yml',
            $container->getResources()[1]->getResource()
        );
        $this->assertSame(
            \dirname(\dirname(__DIR__)) . '/src/Resources/config/table/news-tags.yml',
            $container->getResources()[2]->getResource()
        );
        $this->assertSame(
            \dirname(\dirname(__DIR__)) . '/src/Resources/config/table/news-tags-relation.yml',
            $container->getResources()[3]->getResource()
        );
        $this->assertSame(
            \dirname(\dirname(__DIR__)) . '/src/Resources/config/services.yml',
            $container->getResources()[4]->getResource()
        );
    }
}
