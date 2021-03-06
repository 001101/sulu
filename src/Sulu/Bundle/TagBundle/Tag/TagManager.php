<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\TagBundle\Tag;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Sulu\Bundle\TagBundle\Entity\TagRepository;
use Sulu\Bundle\TagBundle\Event\TagDeleteEvent;
use Sulu\Bundle\TagBundle\Event\TagEvents;
use Sulu\Bundle\TagBundle\Event\TagMergeEvent;
use Sulu\Bundle\TagBundle\Tag\Exception\TagAlreadyExistsException;
use Sulu\Bundle\TagBundle\Tag\Exception\TagNotFoundException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Responsible for centralized Tag Management.
 */
class TagManager implements TagManagerInterface
{
    /**
     * The repository for communication with the database.
     *
     * @var TagRepository
     */
    private $tagRepository;

    /**
     * @var ObjectManager
     */
    private $em;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(
        TagRepositoryInterface $tagRepository,
        ObjectManager $em,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->tagRepository = $tagRepository;
        $this->em = $em;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Loads all the tags managed in this system.
     *
     * @return TagInterface[]
     */
    public function findAll()
    {
        return $this->tagRepository->findAllTags();
    }

    /**
     * Loads the tag with the given id.
     *
     * @param $id number The id of the tag
     *
     * @return TagInterface
     */
    public function findById($id)
    {
        return $this->tagRepository->findTagById($id);
    }

    /**
     * Loads the tag with the given name.
     *
     * @param $name
     *
     * @return TagInterface
     */
    public function findByName($name)
    {
        return $this->tagRepository->findTagByName($name);
    }

    /**
     * {@inheritdoc}
     */
    public function findOrCreateByName($name, $userId)
    {
        $tag = $this->findByName($name);

        if (!$tag) {
            $tag = $this->save(['name' => $name], $userId);
        }

        return $tag;
    }

    /**
     * {@inheritdoc}
     */
    public function save($data, $userId, $id = null)
    {
        $name = $data['name'];

        try {
            // load existing tag if id is given and create a new one otherwise
            if ($id) {
                $tag = $this->tagRepository->findTagById($id);
                if (!$tag) {
                    throw new TagNotFoundException($id);
                }
            } else {
                $tag = $this->tagRepository->createNew();
            }

            // update data
            $tag->setName($name);

            if (!$id) {
                $this->em->persist($tag);
            }

            $this->em->flush();

            return $tag;
        } catch (UniqueConstraintViolationException $exc) {
            throw new TagAlreadyExistsException($name);
        }
    }

    /**
     * Deletes the given Tag.
     *
     * @param number $id The tag to delete
     *
     * @throws Exception\TagNotFoundException
     */
    public function delete($id)
    {
        $tag = $this->tagRepository->findTagById($id);

        if (!$tag) {
            throw new TagNotFoundException($id);
        }

        $this->em->remove($tag);
        $this->em->flush();

        // throw an tag.delete event
        $event = new TagDeleteEvent($tag);
        $this->eventDispatcher->dispatch(TagEvents::TAG_DELETE, $event);
    }

    /**
     * Merges the source tag into the destination tag.
     * The source tag will be deleted.
     *
     * @param number $srcTagIds The source tags, which will be removed afterwards
     * @param number $destTagId The destination tag, which will replace the source tag
     *
     * @throws Exception\TagNotFoundException
     *
     * @return TagInterface The new Tag, which is valid for all given tags
     */
    public function merge($srcTagIds, $destTagId)
    {
        $srcTags = [];

        $destTag = $this->tagRepository->findTagById($destTagId);
        if (!$destTag) {
            throw new TagNotFoundException($destTagId);
        }

        foreach ($srcTagIds as $srcTagId) {
            $srcTag = $this->tagRepository->findTagById($srcTagId);

            if (!$srcTag) {
                throw new TagNotFoundException($srcTagId);
            }

            $this->em->remove($srcTag);

            $srcTags[] = $srcTag;
        }

        $this->em->flush();

        // throw an tag.merge event
        $event = new TagMergeEvent($srcTags, $destTag);
        $this->eventDispatcher->dispatch(TagEvents::TAG_MERGE, $event);

        return $destTag;
    }

    /**
     * Resolves tag ids to names.
     *
     * @param $tagIds
     *
     * @return array
     */
    public function resolveTagIds($tagIds)
    {
        $resolvedTags = [];

        foreach ($tagIds as $tagId) {
            $tag = $this->findById($tagId);
            if (null !== $tag) {
                $resolvedTags[] = $tag->getName();
            }
        }

        return $resolvedTags;
    }

    /**
     * Resolves tag names to ids.
     *
     * @param $tagNames
     *
     * @return array
     */
    public function resolveTagNames($tagNames)
    {
        $resolvedTags = [];

        foreach ($tagNames as $tagName) {
            $tag = $this->findByName($tagName);
            if (null !== $tag) {
                $resolvedTags[] = $tag->getId();
            }
        }

        return $resolvedTags;
    }
}
