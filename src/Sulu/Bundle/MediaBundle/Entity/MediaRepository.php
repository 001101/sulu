<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\MediaBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\Query\ResultSetMapping;

/**
 * MediaRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class MediaRepository extends EntityRepository implements MediaRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function findMediaById($id, $asArray = false)
    {
        try {
            $qb = $this->createQueryBuilder('media')
                ->leftJoin('media.type', 'type')
                ->leftJoin('media.collection', 'collection')
                ->leftJoin('media.files', 'file')
                ->leftJoin('file.fileVersions', 'fileVersion')
                ->leftJoin('fileVersion.tags', 'tag')
                ->leftJoin('fileVersion.meta', 'fileVersionMeta')
                ->leftJoin('fileVersion.contentLanguages', 'fileVersionContentLanguage')
                ->leftJoin('fileVersion.publishLanguages', 'fileVersionPublishLanguage')
                ->leftJoin('media.creator', 'creator')
                ->leftJoin('creator.contact', 'creatorContact')
                ->leftJoin('media.changer', 'changer')
                ->leftJoin('changer.contact', 'changerContact')
                ->addSelect('type')
                ->addSelect('collection')
                ->addSelect('file')
                ->addSelect('tag')
                ->addSelect('fileVersion')
                ->addSelect('fileVersionMeta')
                ->addSelect('fileVersionContentLanguage')
                ->addSelect('fileVersionPublishLanguage')
                ->addSelect('creator')
                ->addSelect('changer')
                ->addSelect('creatorContact')
                ->addSelect('changerContact')
                ->where('media.id = :mediaId');

            $query = $qb->getQuery();
            $query->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true);
            $query->setParameter('mediaId', $id);

            if ($asArray) {
                if (isset($query->getArrayResult()[0])) {
                    return $query->getArrayResult()[0];
                } else {
                    return null;
                }
            } else {
                return $query->getSingleResult();
            }
        } catch (NoResultException $ex) {
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function findMedia($filter = array(), $limit = null, $offset = null)
    {
        try {
            // validate given filter array
            $collection = array_key_exists('collection', $filter) ? $filter['collection'] : null;
            $ids = array_key_exists('ids', $filter) ? $filter['ids'] : null;
            $types = array_key_exists('types', $filter) ? $filter['types'] : null;
            $paginator = array_key_exists('paginator', $filter) ? $filter['paginator'] : true;
            $search = array_key_exists('search', $filter) ? $filter['search'] : null;

            // if empty array of ids is requested return empty array of medias
            if ($ids !== null && sizeof($ids) === 0) {
                return array();
            }

            $qb = $this->createQueryBuilder('media')
                ->leftJoin('media.type', 'type')
                ->leftJoin('media.collection', 'collection')
                ->innerJoin('media.files', 'file')
                ->innerJoin('file.fileVersions', 'fileVersion', 'WITH', 'fileVersion.version = file.version')
                ->leftJoin('fileVersion.tags', 'tag')
                ->leftJoin('fileVersion.meta', 'fileVersionMeta')
                ->leftJoin('fileVersion.contentLanguages', 'fileVersionContentLanguage')
                ->leftJoin('fileVersion.publishLanguages', 'fileVersionPublishLanguage')
                ->leftJoin('media.creator', 'creator')
                ->leftJoin('creator.contact', 'creatorContact')
                ->leftJoin('media.changer', 'changer')
                ->leftJoin('changer.contact', 'changerContact')
                ->addSelect('type')
                ->addSelect('collection')
                ->addSelect('file')
                ->addSelect('tag')
                ->addSelect('fileVersion')
                ->addSelect('fileVersionMeta')
                ->addSelect('fileVersionContentLanguage')
                ->addSelect('fileVersionPublishLanguage')
                ->addSelect('creator')
                ->addSelect('changer')
                ->addSelect('creatorContact')
                ->addSelect('changerContact');

            if ($ids !== null) {
                $qb->andWhere('media.id IN (:mediaIds)');
            }

            if ($collection !== null) {
                $qb->andWhere('collection.id = :collection');
            }

            if ($types !== null) {
                $qb->andWhere('type.name IN (:types)');
            }

            if ($search !== null) {
                $qb->andWhere('fileVersionMeta.title LIKE :search');
            }

            if ($limit !== null) {
                $qb->setMaxResults($limit);
            }

            if ($offset !== null) {
                $qb->setFirstResult($offset);
            }

            $query = $qb->getQuery();
            $query->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true);
            if ($collection !== null) {
                $query->setParameter('collection', $collection);
            }
            if ($ids !== null) {
                $query->setParameter('mediaIds', $ids);
            }
            if ($types !== null) {
                $query->setParameter('types', $types);
            }
            if ($search !== null) {
                $query->setParameter('search', '%' . $search . '%');
            }

            if (!$paginator) {
                return $query->getResult();
            }

            return new Paginator($query);
        } catch (NoResultException $ex) {
            return null;
        }
    }

    /**
     * Returns the most recent version of a media for the specified
     * filename within a collection
     *
     * @param String $filename
     * @param int $collectionId
     *
     * @return Media
     */
    public function findMediaWithFilenameInCollectionWithId($filename, $collectionId)
    {
        $qb = $this->createQueryBuilder('media')
            ->innerJoin('media.files', 'files')
            ->innerJoin('files.fileVersions', 'versions', 'WITH', 'versions.version = files.version')
            ->join('media.collection', 'collection')
            ->where('collection.id = :collectionId')
            ->andWhere('versions.name = :filename')
            ->orderBy('versions.created')
            ->setMaxResults(1)
            ->setParameter('filename', $filename)
            ->setParameter('collectionId', $collectionId);
        $result = $qb->getQuery()->getResult();

        if (count($result) > 0) {
            return $result[0];
        }

        return null;
    }

    /**
     * @param $collectionId
     * @param $limit
     * @param $offset
     * @return array
     */
    public function findMediaByCollectionId($collectionId, $limit, $offset)
    {
        // count media in collection
        $qb = $this->createQueryBuilder('media')
            ->select('count(media.id) as counter')
            ->join('media.collection', 'collection')
            ->where('collection.id = :collectionId')
            ->setParameter('collectionId', $collectionId);
        $count = $qb->getQuery()->getSingleScalarResult();

        $qb = $this->createQueryBuilder('media')
            ->innerJoin('media.files', 'files')
            ->innerJoin('files.fileVersions', 'versions', 'WITH', 'versions.version = files.version')
            ->join('media.collection', 'collection')
            ->where('collection.id = :collectionId')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->setParameter('collectionId', $collectionId);

        $query = $qb->getQuery();

        $paginator = new Paginator($query);

        return ['media' => $paginator, 'count' => $count];
    }
}
