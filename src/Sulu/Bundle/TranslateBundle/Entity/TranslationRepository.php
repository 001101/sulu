<?php
/*
* This file is part of the Sulu CMS.
*
* (c) MASSIVE ART WebServices GmbH
*
* This source file is subject to the MIT license that is bundled
* with this source code in the file LICENSE.
*/

namespace Sulu\Bundle\TranslateBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * Repository for the Translations, implementing some additional functions
 * for querying objects
 */
class TranslationRepository extends EntityRepository
{
	public function getTranslation($codeId, $catalogueId)
	{
		$dql = 'SELECT tr
				FROM SuluTranslateBundle:Translation tr
					JOIN tr.catalogue ca
					JOIN ca.package pa
					JOIN pa.codes co
				WHERE co.id = :codeId AND
					  ca.id = :catalogueId';

		$query = $this->getEntityManager()
			->createQuery($dql)
			->setParameters(
				array(
					'codeId' => $codeId,
					'catalogueId' => $catalogueId
				)
			);

		return $query->getSingleResult();
	}

	public function findFiltered($packageId, $locale, $backend = null, $frontend = null, $location = null)
	{
		$dql = 'SELECT tr
                    FROM SuluTranslateBundle:Translation tr
                        JOIN tr.catalogue ca
                        JOIN ca.package pa
                        JOIN tr.code co
                        LEFT JOIN co.location lo
                    WHERE ca.locale = :locale
                      AND pa.id = :packageId';

		// add additional conditions, if they are set
		if ($backend != null) {
			$dql .= '
                      AND co.backend = :backend';
		}

		if ($frontend != null) {
			$dql .= '
                      AND co.frontend = :frontend';
		}

		if ($location != null) {
			$dql .= '
                      AND lo.name = :location';
		}

		$query = $this->getEntityManager()
			->createQuery($dql)
			->setParameters(
				array(
					'packageId' => $packageId,
					'locale' => $locale
				)
			);

		// set the additional parameter, if they are set
		if ($backend != null) {
			$query->setParameter('backend', $backend);
		}

		if ($frontend != null) {
			$query->setParameter('frontend', $frontend);
		}

		if ($location != null) {
			$query->setParameter('location', $location);
		}

		return $query->getResult();
	}
}
