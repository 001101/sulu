<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Component\Content\Block;

use PHPCR\NodeInterface;
use Sulu\Component\Content\ComplexContentType;
use Sulu\Component\Content\ContentTypeInterface;
use Sulu\Component\Content\ContentTypeManagerInterface;
use Sulu\Component\Content\Exception\UnexpectedPropertyType;
use Sulu\Component\Content\PropertyInterface;

/**
 * content type for block
 */
class BlockContentType extends ComplexContentType
{
    /**
     * @var ContentTypeManagerInterface
     */
    private $contentTypeManager;

    /**
     * template for form generation
     * @var string
     */
    private $template;

    function __construct(ContentTypeManagerInterface $contentTypeManager, $template)
    {
        $this->contentTypeManager = $contentTypeManager;
        $this->template = $template;
    }

    /**
     * returns type of ContentType
     * PRE_SAVE or POST_SAVE
     * @return int
     */
    public function getType()
    {
        return ContentTypeInterface::PRE_SAVE;
    }

    /**
     * reads the value for given property
     * from the node + sets the value of the property
     * @param NodeInterface $node
     * @param PropertyInterface $property
     * @param string $webspaceKey
     * @param string $languageCode
     * @param string $segmentKey
     * @throws \Sulu\Component\Content\Exception\UnexpectedPropertyType
     * @return mixed
     */
    public function read(
        NodeInterface $node,
        PropertyInterface $property,
        $webspaceKey,
        $languageCode,
        $segmentKey
    )
    {
        if ($property->getIsBlock()) {
            /** @var BlockPropertyInterface $blockProperty */
            $blockProperty = $property;
            while (!($blockProperty instanceof BlockPropertyInterface)) {
                $blockProperty = $blockProperty->getProperty();
            }

            /** @var PropertyInterface $subProperty */
            foreach ($blockProperty->getChildProperties() as $subProperty) {
                $contentType = $this->contentTypeManager->get($subProperty->getContentTypeName());
                $contentType->read(
                    $node,
                    new BlockPropertyWrapper($subProperty, $property),
                    $webspaceKey,
                    $languageCode,
                    $segmentKey
                );
            }
        } else {
            throw new UnexpectedPropertyType($property, $this);
        }
    }

    /**
     * sets the value of the property with the data given
     * @param mixed $data
     * @param PropertyInterface $property
     * @param $webspaceKey
     * @param string $languageCode
     * @param string $segmentKey
     * @throws \Sulu\Component\Content\Exception\UnexpectedPropertyType
     * @return mixed
     */
    public function readForPreview(
        $data,
        PropertyInterface $property,
        $webspaceKey,
        $languageCode,
        $segmentKey
    )
    {
        if ($property->getIsBlock()) {
            $property->setValue($data);
        } else {
            throw new UnexpectedPropertyType($property, $this);
        }
    }

    /**
     * save the value from given property
     * @param NodeInterface $node
     * @param PropertyInterface $property
     * @param int $userId
     * @param string $webspaceKey
     * @param string $languageCode
     * @param string $segmentKey
     * @throws \Sulu\Component\Content\Exception\UnexpectedPropertyType
     * @return mixed
     */
    public function write(
        NodeInterface $node,
        PropertyInterface $property,
        $userId,
        $webspaceKey,
        $languageCode,
        $segmentKey
    )
    {
        if ($property->getIsBlock()) {
            /** @var BlockPropertyInterface $blockProperty */
            $blockProperty = $property;
            while (!($blockProperty instanceof BlockPropertyInterface)) {
                $blockProperty = $blockProperty->getProperty();
            }

            /** @var PropertyInterface $subProperty */
            foreach ($blockProperty->getChildProperties() as $subProperty) {
                $contentType = $this->contentTypeManager->get($subProperty->getContentTypeName());
                $contentType->write(
                    $node,
                    new BlockPropertyWrapper($subProperty, $property),
                    $userId,
                    $webspaceKey,
                    $languageCode,
                    $segmentKey
                );
            }
        } else {
            throw new UnexpectedPropertyType($property, $this);
        }
    }

    /**
     * remove property from given node
     * @param NodeInterface $node
     * @param PropertyInterface $property
     * @param string $webspaceKey
     * @param string $languageCode
     * @param string $segmentKey
     */
    public function remove(
        NodeInterface $node,
        PropertyInterface $property,
        $webspaceKey,
        $languageCode,
        $segmentKey
    )
    {
        // TODO: Implement remove() method.
    }

    /**
     * returns a template to render a form
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }
}
