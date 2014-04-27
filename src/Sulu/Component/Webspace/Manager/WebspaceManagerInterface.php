<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Component\Webspace\Manager;

use Sulu\Component\Webspace\Portal;
use Sulu\Component\Webspace\Webspace;
use Sulu\Component\Webspace\WebspaceCollection;

/**
 * Defines the methods for the WebspaceManager
 */
interface WebspaceManagerInterface
{
    /**
     * Returns the webspace with the given key
     * @param $key string The key to search for
     * @return Webspace
     */
    public function findWebspaceByKey($key);

    /**
     * Returns the portal with the given key
     * @param string $key The key to search for
     * @return Portal
     */
    public function findPortalByKey($key);

    /**
     * Returns the portal with the given url (which has not necessarily to be the main url)
     * @param string $url The url to search for
     * @param string $environment The environment in which the url should be searched
     * @return array|null
     */
    public function findPortalInformationByUrl($url, $environment);

    /**
     * Returns all possible urls for resourcelocator
     * @param string $resourceLocator
     * @param string $environment
     * @param string $languageCode
     * @param null|string $webspaceKey
     * @return array
     */
    public function findUrlsByResourceLocator($resourceLocator, $environment, $languageCode, $webspaceKey = null);

    /**
     * Returns all the webspaces managed by this specific instance
     * @return WebspaceCollection
     */
    public function getWebspaceCollection();
}
