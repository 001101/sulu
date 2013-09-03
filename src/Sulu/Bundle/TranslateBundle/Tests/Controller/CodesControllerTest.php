<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\TranslateBundle\Tests\Controller;

use Doctrine\ORM\Tools\SchemaTool;
use Sulu\Bundle\CoreBundle\Tests\DatabaseTestCase;
use Sulu\Bundle\TranslateBundle\Entity\Catalogue;
use Sulu\Bundle\TranslateBundle\Entity\Code;
use Sulu\Bundle\TranslateBundle\Entity\Location;
use Sulu\Bundle\TranslateBundle\Entity\Package;
use Sulu\Bundle\TranslateBundle\Entity\Translation;
use Symfony\Component\HttpKernel\Client;

class CodesControllerTest extends DatabaseTestCase
{
    /**
     * @var array
     */
    protected static $entities;

    /**
     * @var SchemaTool
     */
    protected static $tool;

    /**
     * @var Package
     */
    private $package1;

    /**
     * @var Package
     */
    private $package2;

    /**
     * @var Location
     */
    private $location1;

    /**
     * @var Location
     */
    private $location2;

    /**
     * @var Catalogue
     */
    private $catalogue1;

    /**
     * @var Catalogue
     */
    private $catalogue2;

    /**
     * @var Catalogue
     */
    private $catalogue3;

    /**
     * @var Code
     */
    private $code1;

    /**
     * @var Translation
     */
    private $code1_t1;

    /**
     * @var Code
     */
    private $code2;

    /**
     * @var Translation
     */
    private $code2_t1;

    /**
     * @var Code
     */
    private $code3;

    /**
     * @var Translation
     */
    private $code3_t1;

    /**
     * @var Translation
     */
    private $code3_t2;

    /**
     * @var Code
     */
    private $code4;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var integer
     */
    private $pageSize;

    public function setUp()
    {
        // config section
        $this->client = static::createClient();
        $this->pageSize = 3;

        $this->setUpSchema();

        $this->package1 = new Package();
        $this->package1->setName('Package1');
        self::$em->persist($this->package1);

        $this->package2 = new Package();
        $this->package2->setName('Package2');
        self::$em->persist($this->package2);

        $this->location1 = new Location();
        $this->location1->setName('Location1')
            ->setPackage($this->package1);
        self::$em->persist($this->location1);

        $this->location2 = new Location();
        $this->location2->setName('Location2')
            ->setPackage($this->package2);
        self::$em->persist($this->location2);

        $this->catalogue1 = new Catalogue();
        $this->catalogue1->setLocale('EN')
            ->setPackage($this->package1);
        self::$em->persist($this->catalogue1);

        $this->catalogue2 = new Catalogue();
        $this->catalogue2->setLocale('DE')
            ->setPackage($this->package1);
        self::$em->persist($this->catalogue2);

        $this->catalogue3 = new Catalogue();
        $this->catalogue3->setLocale('FR')
            ->setPackage($this->package1);
        self::$em->persist($this->catalogue3);

        $this->code1 = new Code();
        $this->code1->setCode('test.code.1')
            ->setFrontend(0)
            ->setBackend(1)
            ->setLength(9)
            ->setPackage($this->package1)
            ->setLocation($this->location1);
        self::$em->persist($this->code1);

        self::$em->flush();

        $this->code1_t1 = new Translation();
        $this->code1_t1->setValue('Test Code 1')
            ->setCatalogue($this->catalogue2)
            ->setCode($this->code1);
        self::$em->persist($this->code1_t1);

        $this->code2 = new Code();
        $this->code2->setCode('test.code.2')
            ->setFrontend(1)
            ->setBackend(0)
            ->setLength(10)
            ->setPackage($this->package1)
            ->setLocation($this->location1);
        self::$em->persist($this->code2);

        self::$em->flush();

        $this->code2_t1 = new Translation();
        $this->code2_t1->setValue('Test Code 2')
            ->setCatalogue($this->catalogue1)
            ->setCode($this->code2);
        self::$em->persist($this->code2_t1);

        $this->code3 = new Code();
        $this->code3->setCode('test.code.3')
            ->setFrontend(1)
            ->setBackend(1)
            ->setLength(11)
            ->setPackage($this->package2)
            ->setLocation($this->location1);
        self::$em->persist($this->code3);

        self::$em->flush();

        $this->code3_t1 = new Translation();
        $this->code3_t1->setValue('Test Code 3')
            ->setCatalogue($this->catalogue1)
            ->setCode($this->code3);
        self::$em->persist($this->code3_t1);

        $this->code3_t2 = new Translation();
        $this->code3_t2->setValue('Test Code 3.1')
            ->setCatalogue($this->catalogue2)
            ->setCode($this->code3);
        self::$em->persist($this->code3_t2);

        $this->code4 = new Code();
        $this->code4->setCode('test.code.4')
            ->setFrontend(1)
            ->setBackend(1)
            ->setLength(12)
            ->setPackage($this->package1)
            ->setLocation($this->location1);
        self::$em->persist($this->code4);

        self::$em->flush();
    }

    public function setUpSchema()
    {
        self::$tool = new SchemaTool(self::$em);

        self::$entities = array(
            self::$em->getClassMetadata('Sulu\Bundle\TranslateBundle\Entity\Catalogue'),
            self::$em->getClassMetadata('Sulu\Bundle\TranslateBundle\Entity\Code'),
            self::$em->getClassMetadata('Sulu\Bundle\TranslateBundle\Entity\Location'),
            self::$em->getClassMetadata('Sulu\Bundle\TranslateBundle\Entity\Package'),
            self::$em->getClassMetadata('Sulu\Bundle\TranslateBundle\Entity\Translation'),
        );

        self::$tool->dropSchema(self::$entities);
        self::$tool->createSchema(self::$entities);
    }

    public function tearDown()
    {
        parent::tearDown();
        self::$tool->dropSchema(self::$entities);
    }

    public function testGetAll()
    {
        $this->client->request('GET', '/translate/api/codes');
        $response = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $this->assertEquals(4, $response->total);
        $this->assertEquals(4, sizeof($response->items));

        $this->assertEquals($this->code1->getCode(), $response->items[0]->code);
        $this->assertEquals(1, sizeof($response->items[0]->translations));

        $this->assertEquals($this->code2->getCode(), $response->items[1]->code);
        $this->assertEquals(1, sizeof($response->items[1]->translations));

        $this->assertEquals($this->code3->getCode(), $response->items[2]->code);
        $this->assertEquals(2, sizeof($response->items[2]->translations));

        $this->assertEquals($this->code4->getCode(), $response->items[3]->code);
        $this->assertEquals(0, sizeof($response->items[3]->translations));
    }

    public function testGetAllFiltered()
    {
        $this->client->request('GET', '/translate/api/codes?packageId=1');
        $response = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $this->assertEquals(3, sizeof($response->items));

        $this->assertEquals(1, $response->items[0]->id);
        $this->assertEquals(1, sizeof($response->items[0]->translations));

        $this->assertEquals(2, $response->items[1]->id);
        $this->assertEquals(1, sizeof($response->items[1]->translations));

        $this->assertEquals(4, $response->items[2]->id);
        $this->assertEquals(0, sizeof($response->items[2]->translations));

        $this->client->request('GET', '/translate/api/codes?catalogueId=2');
        $response = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $this->assertEquals(3, sizeof($response->items));

        $this->assertEquals(1, sizeof($response->items[0]->translations));
        $this->assertEquals(1, $response->items[0]->id);

        $this->assertEquals(0, sizeof($response->items[1]->translations));
        $this->assertEquals(2, $response->items[1]->id);

        $this->assertEquals(0, sizeof($response->items[2]->translations));
        $this->assertEquals(4, $response->items[2]->id);
    }

    public function testGetAllFilteredNonExistingPackage()
    {
        $this->client->request('GET', '/translate/api/codes?packageId=5');
        $response = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $this->assertEquals(0, sizeof($response->items));
        $this->assertEquals(0, $response->total);
    }

    public function testGetAllFilteredNonExistingCatalogue()
    {
        $this->client->request('GET', '/translate/api/codes?catalogueId=5');
        $response = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $this->assertEquals(0, sizeof($response->items));
        $this->assertEquals(0, $response->total);
    }

    public function testGetAllPagination()
    {
        $this->client->request('GET', '/translate/api/codes?pageSize=2&page=1');
        $response = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $this->assertEquals(2, sizeof($response->items));
        $this->assertEquals(2, $response->total);

        $this->client->request('GET', '/translate/api/codes?pageSize=2&page=2');
        $response = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $this->assertEquals(1, sizeof($response->items));
        $this->assertEquals(1, $response->total);
    }

    public function testGetAllOrder()
    {
        $this->client->request('GET', '/translate/api/codes?sortBy=id&sortOrder=desc');
        $response = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $this->assertEquals(4, $response->items[0]->id);
        $this->assertEquals(3, $response->items[1]->id);
        $this->assertEquals(2, $response->items[2]->id);
        $this->assertEquals(1, $response->items[3]->id);
    }

    public function testGetList()
    {
        $this->client->request('GET', '/translate/api/codes/list');
        $response = json_decode($this->client->getResponse()->getContent());

        $this->assertEquals(4, $response->total);
        $this->assertEquals($this->code1->getCode(), $response->items[0]->code);
        $this->assertEquals($this->code2->getCode(), $response->items[1]->code);
        $this->assertEquals($this->code3->getCode(), $response->items[2]->code);
        $this->assertEquals($this->code4->getCode(), $response->items[3]->code);
    }

    public function testGetListSorted()
    {
        $this->client->request('GET', '/translate/api/codes/list?sortBy=id&sortOrder=asc');
        $response = json_decode($this->client->getResponse()->getContent());

        $this->assertEquals(4, $response->total);
        $this->assertEquals($this->code1->getCode(), $response->items[0]->code);
        $this->assertEquals($this->code2->getCode(), $response->items[1]->code);
        $this->assertEquals($this->code3->getCode(), $response->items[2]->code);
        $this->assertEquals($this->code4->getCode(), $response->items[3]->code);

        $this->client->request('GET', '/translate/api/codes/list?sortBy=id&sortOrder=desc');
        $response = json_decode($this->client->getResponse()->getContent());

        $this->assertEquals(4, $response->total);
        $this->assertEquals($this->code4->getCode(), $response->items[0]->code);
        $this->assertEquals($this->code3->getCode(), $response->items[1]->code);
        $this->assertEquals($this->code2->getCode(), $response->items[2]->code);
        $this->assertEquals($this->code1->getCode(), $response->items[3]->code);

        $this->client->request('GET', '/translate/api/codes/list?sortBy=code&sortOrder=asc');
        $response = json_decode($this->client->getResponse()->getContent());

        $this->assertEquals(4, $response->total);
        $this->assertEquals($this->code1->getCode(), $response->items[0]->code);
        $this->assertEquals($this->code2->getCode(), $response->items[1]->code);
        $this->assertEquals($this->code3->getCode(), $response->items[2]->code);
        $this->assertEquals($this->code4->getCode(), $response->items[3]->code);

        $this->client->request('GET', '/translate/api/codes/list?sortBy=code&sortOrder=desc');
        $response = json_decode($this->client->getResponse()->getContent());

        $this->assertEquals(4, $response->total);
        $this->assertEquals($this->code4->getCode(), $response->items[0]->code);
        $this->assertEquals($this->code3->getCode(), $response->items[1]->code);
        $this->assertEquals($this->code2->getCode(), $response->items[2]->code);
        $this->assertEquals($this->code1->getCode(), $response->items[3]->code);

        $this->client->request('GET', '/translate/api/codes/list?sortBy=length&sortOrder=asc');
        $response = json_decode($this->client->getResponse()->getContent());

        $this->assertEquals(4, $response->total);
        $this->assertEquals($this->code1->getCode(), $response->items[0]->code);
        $this->assertEquals($this->code2->getCode(), $response->items[1]->code);
        $this->assertEquals($this->code3->getCode(), $response->items[2]->code);
        $this->assertEquals($this->code4->getCode(), $response->items[3]->code);

        $this->client->request('GET', '/translate/api/codes/list?sortBy=length&sortOrder=desc');
        $response = json_decode($this->client->getResponse()->getContent());

        $this->assertEquals(4, $response->total);
        $this->assertEquals($this->code4->getCode(), $response->items[0]->code);
        $this->assertEquals($this->code3->getCode(), $response->items[1]->code);
        $this->assertEquals($this->code2->getCode(), $response->items[2]->code);
        $this->assertEquals($this->code1->getCode(), $response->items[3]->code);
    }

    public function testGetListPageSize()
    {
        $this->client->request('GET', '/translate/api/codes/list?pageSize=' . $this->pageSize);
        $response = json_decode($this->client->getResponse()->getContent());

        $this->assertEquals($this->pageSize, count($response->items));
        $this->assertEquals($this->pageSize, $response->total);
        $this->assertEquals($this->code1->getCode(), $response->items[0]->code);
        $this->assertEquals($this->code2->getCode(), $response->items[1]->code);
        $this->assertEquals($this->code3->getCode(), $response->items[2]->code);

        $this->client->request('GET', '/translate/api/codes/list?pageSize=' . $this->pageSize . '&page=2');
        $response = json_decode($this->client->getResponse()->getContent());

        $this->assertEquals(1, count($response->items)); // only 1 item remaining
        $this->assertEquals(1, $response->total); // only 1 item remaining
        $this->assertEquals($this->code4->getCode(), $response->items[0]->code);
    }

    public function testGetListFields()
    {
        $this->client->request('GET', '/translate/api/codes/list?fields=code');
        $response = json_decode($this->client->getResponse()->getContent());

        $this->assertEquals(4, $response->total);
        $this->assertEquals($this->code1->getCode(), $response->items[0]->code);
        $this->assertFalse(isset($response->items[0]->id));
        $this->assertEquals($this->code2->getCode(), $response->items[1]->code);
        $this->assertFalse(isset($response->items[1]->id));
        $this->assertEquals($this->code3->getCode(), $response->items[2]->code);
        $this->assertFalse(isset($response->items[2]->id));
        $this->assertEquals($this->code4->getCode(), $response->items[3]->code);
        $this->assertFalse(isset($response->items[3]->id));

        $this->client->request('GET', '/translate/api/codes/list?fields=id,code,location_name');
        $response = json_decode($this->client->getResponse()->getContent());

        $this->assertEquals(4, $response->total);
        $this->assertEquals(1, $response->items[0]->id);
        $this->assertEquals($this->code1->getCode(), $response->items[0]->code);
        $this->assertEquals($this->code1->getLocation()->getName(), $response->items[0]->location_name);

        $this->assertEquals(2, $response->items[1]->id);
        $this->assertEquals($this->code2->getCode(), $response->items[1]->code);
        $this->assertEquals($this->code2->getLocation()->getName(), $response->items[1]->location_name);

        $this->assertEquals(3, $response->items[2]->id);
        $this->assertEquals($this->code3->getCode(), $response->items[2]->code);
        $this->assertEquals($this->code3->getLocation()->getName(), $response->items[2]->location_name);

        $this->assertEquals(4, $response->items[3]->id);
        $this->assertEquals($this->code4->getCode(), $response->items[3]->code);
        $this->assertEquals($this->code4->getLocation()->getName(), $response->items[3]->location_name);

        $this->client->request(
            'GET',
            '/translate/api/codes/list?fields=id,code,translations_value,translations_catalogue_locale'
        );
        $response = json_decode($this->client->getResponse()->getContent());

        $this->assertEquals(4, $response->total);
    }

    public function testGetListWhere()
    {
        $this->client->request('GET', '/translate/api/codes/list?packageId=1&catalogueId=1');
        $response = json_decode($this->client->getResponse()->getContent());

        $this->assertEquals(1, sizeof($response->items));
        $this->assertEquals(1, $response->total);

        $this->assertEquals($this->code2->getCode(), $response->items[0]->code);

        $this->client->request('GET', '/translate/api/codes/list?packageId=2');
        $response = json_decode($this->client->getResponse()->getContent());

        $this->assertEquals(1, sizeof($response->items));
        $this->assertEquals(1, $response->total);

        $this->assertEquals($this->code3->getCode(), $response->items[0]->code);

        $this->client->request('GET', '/translate/api/codes/list?locationId=1');
        $response = json_decode($this->client->getResponse()->getContent());

        $this->assertEquals(4, sizeof($response->items));
        $this->assertEquals(4, $response->total);

        $this->assertEquals($this->code1->getCode(), $response->items[0]->code);
        $this->assertEquals($this->code2->getCode(), $response->items[1]->code);
        $this->assertEquals($this->code3->getCode(), $response->items[2]->code);
        $this->assertEquals($this->code4->getCode(), $response->items[3]->code);
    }

    public function testGetListCombination()
    {
        $this->client->request(
            'GET',
            '/translate/api/codes/list?fields=id,code,translations_value,translations_catalogue_locale&packageId=1&catalogueId=1&pageSize=' . $this->pageSize . '&page=1&sortBy=code&sortOrder=desc'
        );
        $response = json_decode($this->client->getResponse()->getContent());

        $this->assertEquals(1, sizeof($response->items));
        $this->assertEquals(1, $response->total);

        $this->assertEquals($this->code2->getCode(), $response->items[0]->code);
        $this->assertEquals($this->catalogue1->getLocale(), $response->items[0]->translations_catalogue_locale);
        $this->assertEquals($this->code2_t1->getValue(), $response->items[0]->translations_value);
    }

    public function testGetId()
    {
        $this->client->request('GET', '/translate/api/codes/' . $this->code3->getId());
        $response = json_decode($this->client->getResponse()->getContent());

        $this->assertEquals($this->code3->getId(), $response->id);
        $this->assertEquals($this->code3->getCode(), $response->code);
        $this->assertEquals($this->code3->getBackend(), $response->backend);
        $this->assertEquals($this->code3->getFrontend(), $response->frontend);
        $this->assertEquals($this->code3->getLength(), $response->length);
        $this->assertEquals($this->code3->getLocation()->getId(), $response->location->id);
        $this->assertEquals($this->code3->getPackage()->getId(), $response->package->id);

        $this->assertEquals($this->code3_t1->getValue(), $response->translations[0]->value);
        $this->assertEquals($this->code3_t2->getValue(), $response->translations[1]->value);
    }

    public function testGetIdNotExisting()
    {
        $this->client->request('GET', '/translate/api/codes/5');
        $response = json_decode($this->client->getResponse()->getContent());

        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

    public function testPost()
    {
        $request = array(
            'code' => 'test.code.4',
            'frontend' => '0',
            'backend' => '0',
            'length' => '12',
            'package' => array(
                'id' => $this->package2->getId()
            ),
            'location' => array(
                'id' => $this->location2->getId()
            ),
            'translations' => array(
                array('value' => 'Translation 1', 'catalogue' => array('id' => 1)),
                array('value' => 'Translation 2', 'catalogue' => array('id' => 2))
            )
        );
        $this->client->request(
            'POST',
            '/translate/api/codes',
            $request
        );
        $response = json_decode($this->client->getResponse()->getContent());

        $this->assertEquals('test.code.4', $response->code);

        $this->client->request('GET', '/translate/api/codes/' . $response->id);
        $response = json_decode($this->client->getResponse()->getContent());

        $this->assertEquals($request['code'], $response->code);
        $this->assertEquals(($request['backend'] == "0") ? false : true, $response->backend);
        $this->assertEquals(($request['frontend'] == "0") ? false : true, $response->frontend);
        $this->assertEquals($request['length'], $response->length);
        $this->assertEquals($request['location']['id'], $response->location->id);
        $this->assertEquals($request['package']['id'], $response->package->id);

        $this->assertEquals(2, sizeof($response->translations));

        $this->assertEquals($request['translations'][0]['value'], $response->translations[0]->value);
        $this->assertEquals($request['translations'][0]['catalogue']['id'], $response->translations[0]->catalogue->id);

        $this->assertEquals($request['translations'][1]['value'], $response->translations[1]->value);
        $this->assertEquals($request['translations'][1]['catalogue']['id'], $response->translations[1]->catalogue->id);
    }

    public function testPostNullValues()
    {
        $r1 = array(
            'frontend' => '0',
            'backend' => '0',
            'length' => '12',
            'package' => array(
                'id' => $this->package2->getId()
            ),
            'location' => array(
                'id' => $this->location2->getId()
            )
        );
        $this->client->request(
            'POST',
            '/translate/api/codes',
            $r1
        );
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());

        $r2 = array(
            'code' => 'test.code.5',
            'frontend' => '0',
            'length' => '12',
            'package' => array(
                'id' => $this->package2->getId()
            ),
            'location' => array(
                'id' => $this->location2->getId()
            )
        );
        $this->client->request(
            'POST',
            '/translate/api/codes',
            $r2
        );
        $response = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());

        $r3 = array(
            'code' => 'test.code.6',
            'backend' => '0',
            'frontend' => '0',
            'length' => '12',
            'location' => array(
                'id' => $this->location2->getId()
            )
        );
        $this->client->request(
            'POST',
            '/translate/api/codes',
            $r3
        );
        $response = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());

        $r4 = array(
            'code' => 'test.code.7',
            'frontend' => '0',
            'backend' => '0',
            'length' => '12',
            'package' => array(
                'id' => $this->package2->getId()
            )
        );
        $this->client->request(
            'POST',
            '/translate/api/codes',
            $r4
        );
        $response = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());

        $r5 = array(
            'code' => 'test.code.8',
            'frontend' => '0',
            'backend' => '0',
            'package' => array(
                'id' => $this->package2->getId()
            ),
            'location' => array(
                'id' => $this->location2->getId()
            )
        );
        $this->client->request(
            'POST',
            '/translate/api/codes',
            $r5
        );
        $response = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testPut()
    {
        $request = array(
            'code' => 'test.code.4',
            'frontend' => '1',
            'backend' => '0',
            'length' => '20',
            'package' => array(
                'id' => $this->package2->getId()
            ),
            'location' => array(
                'id' => $this->location2->getId()
            ),
            'translations' => array(
                array('value' => 'Test Code 1.1', 'catalogue' => array('id' => 1)),
                array('value' => 'Test Code 1.2', 'catalogue' => array('id' => 2)),
                array('value' => 'Test Code 1.3', 'catalogue' => array('id' => 3)),
            )
        );
        $this->client->request(
            'PUT',
            '/translate/api/codes/1',
            $request
        );
        $response = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/translate/api/codes/1');
        $response = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $this->assertEquals(1, $response->id);
        $this->assertEquals($request['code'], $response->code);
        $this->assertEquals(($request['backend'] == "0") ? false : true, $response->backend);
        $this->assertEquals(($request['frontend'] == "0") ? false : true, $response->frontend);
        $this->assertEquals($request['length'], $response->length);
        $this->assertEquals($request['package']['id'], $response->package->id);
        $this->assertEquals($request['location']['id'], $response->location->id);

        $this->assertEquals(3, sizeof($response->translations));

        $this->assertEquals($request['translations'][0]['value'], $response->translations[0]->value);
        $this->assertEquals($request['translations'][0]['catalogue']['id'], $response->translations[0]->catalogue->id);

        $this->assertEquals($request['translations'][1]['value'], $response->translations[1]->value);
        $this->assertEquals($request['translations'][1]['catalogue']['id'], $response->translations[1]->catalogue->id);

        $this->assertEquals($request['translations'][2]['value'], $response->translations[2]->value);
        $this->assertEquals($request['translations'][2]['catalogue']['id'], $response->translations[2]->catalogue->id);

    }

    public function testPutNotExisting()
    {
        $request = array(
            'code' => 'test.code.4',
            'frontend' => '1',
            'backend' => '0',
            'length' => '20',
            'package' => array(
                'id' => $this->package2->getId()
            ),
            'location' => array(
                'id' => $this->location2->getId()
            )
        );
        $this->client->request(
            'PUT',
            '/translate/api/codes/125',
            $request
        );
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

    public function testPutNotExistingPackage()
    {
        $request = array(
            'code' => 'test.code.1',
            'frontend' => '1',
            'backend' => '0',
            'length' => '20',
            'package' => array(
                'id' => 5
            ),
            'location' => array(
                'id' => $this->location2->getId()
            )
        );
        $this->client->request(
            'PUT',
            '/translate/api/codes/1',
            $request
        );
        $this->assertEquals(500, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/translate/api/codes/1');
        $response = json_decode($this->client->getResponse()->getContent());

        $this->assertEquals($this->code1->getCode(), $response->code);
        $this->assertEquals($this->code1->getBackend(), $response->backend);
        $this->assertEquals($this->code1->getFrontend(), $response->frontend);
        $this->assertEquals($this->code1->getLength(), $response->length);
        $this->assertEquals($this->code1->getLocation()->getId(), $response->location->id);
        $this->assertEquals($this->code1->getPackage()->getId(), $response->package->id);
    }

    public function testPutNotExistingLocation()
    {
        $request = array(
            'code' => 'test.code.4',
            'frontend' => '1',
            'backend' => '0',
            'length' => '20',
            'package' => array(
                'id' => $this->package2->getId()
            ),
            'location' => array(
                'id' => 5
            )
        );
        $this->client->request(
            'PUT',
            '/translate/api/codes/1',
            $request
        );
        $this->assertEquals(500, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/translate/api/codes/1');
        $response = json_decode($this->client->getResponse()->getContent());

        $this->assertEquals($this->code1->getCode(), $response->code);
        $this->assertEquals($this->code1->getBackend(), $response->backend);
        $this->assertEquals($this->code1->getFrontend(), $response->frontend);
        $this->assertEquals($this->code1->getLength(), $response->length);
        $this->assertEquals($this->code1->getLocation()->getId(), $response->location->id);
        $this->assertEquals($this->code1->getPackage()->getId(), $response->package->id);
    }


    public function testDeleteById()
    {

        $client = static::createClient();

        $client->request('DELETE', '/translate/api/codes/1');
        $this->assertEquals('204', $client->getResponse()->getStatusCode());

    }

    public function testDeleteByIdNotExisting()
    {

        $client = static::createClient();

        $client->request('DELETE', '/translate/api/codes/4711');
        $this->assertEquals('404', $client->getResponse()->getStatusCode());

        $client->request('GET', '/translate/api/codes');
        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals(4, $response->total);
    }

}
