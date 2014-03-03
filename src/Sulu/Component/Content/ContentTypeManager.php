<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Component\Content;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\DependencyInjection\ContainerInterface;


class ContentTypeManager extends ContainerAware implements ContentTypeManagerInterface
{

    /**
     * @var The prefix to load the content from. Default value is given in configuration and set to: 'sulu.content.types.'
     */
    private $prefix;


    /**
     * @param ContainerInterface $container
     * @param String $prefix
     */
    public function __construct(ContainerInterface $container, $prefix)
    {
        $this->setContainer($container);
        $this->prefix = $prefix;
    }

    /**
     * @param $contentTypeName A String with the name of the content to load.
     * @return ContentTypeInterface
     */
    public function get($contentTypeName = '')
    {
        return $this->container->get($this->prefix . $contentTypeName);
    }
}