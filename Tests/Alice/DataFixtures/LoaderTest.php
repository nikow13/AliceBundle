<?php

/*
 * This file is part of the Hautelook\AliceBundle package.
 *
 * (c) Baldur Rensch <brensch@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hautelook\AliceBundle\Tests\Alice\DataFixtures;

use Hautelook\AliceBundle\Alice\DataFixtures\Loader;

/**
 * @coversDefaultClass Hautelook\AliceBundle\Alice\DataFixtures\Loader
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class LoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @cover ::__construct
     */
    public function testConstruct()
    {
        $aliceLoaderProphecy = $this->prophesize('Nelmio\Alice\Fixtures\Loader');

        $loader = new Loader($aliceLoaderProphecy->reveal(), ['dummyProcessor'], false);

        $this->assertEquals(['dummyProcessor'], $loader->getProcessors());
        $this->assertFalse($loader->getPersistOnce());


        $aliceLoaderProphecy = $this->prophesize('Nelmio\Alice\Fixtures\Loader');

        $loader = new Loader($aliceLoaderProphecy->reveal(), [], true);

        $this->assertEquals([], $loader->getProcessors());
        $this->assertTrue($loader->getPersistOnce());
    }

    /**
     * @cover ::load
     * @cover ::persist
     */
    public function testLoadEmptyFixturesSet()
    {
        $aliceLoaderProphecy = $this->prophesize('Nelmio\Alice\Fixtures\Loader');

        $persisterProphecy = $this->prophesize('Nelmio\Alice\PersisterInterface');
        
        $loader = new Loader($aliceLoaderProphecy->reveal(), ['dummyProcessor'], false);
        $objects = $loader->load($persisterProphecy->reveal(), []);

        $this->assertEquals([], $objects);
    }

    /**
     * @cover ::load
     * @cover ::persist
     */
    public function testLoadWithFixtures()
    {
        $object = new \stdClass();

        $aliceLoaderProphecy = $this->prophesize('Nelmio\Alice\Fixtures\Loader');
        $aliceLoaderProphecy->load('random/file')->willReturn([$object]);

        $persisterProphecy = $this->prophesize('Nelmio\Alice\PersisterInterface');
        $persisterProphecy->persist([$object])->shouldBeCalled();

        $loader = new Loader($aliceLoaderProphecy->reveal(), [], false);
        $objects = $loader->load($persisterProphecy->reveal(), ['random/file']);

        $this->assertEquals([$object], $objects);
    }

    /**
     * @cover ::load
     * @cover ::persist
     */
    public function testLoadWithPersistOnceAtFalse()
    {
        $objects = [
            new \stdClass(),
            new \stdClass(),
        ];

        $aliceLoaderProphecy = $this->prophesize('Nelmio\Alice\Fixtures\Loader');
        $aliceLoaderProphecy->load('random/file1')->willReturn([$objects[0]]);
        $aliceLoaderProphecy->load('random/file2')->willReturn([$objects[0]]);

        $persisterProphecy = $this->prophesize('Nelmio\Alice\PersisterInterface');
        $persisterProphecy->persist([$objects[0]])->shouldBeCalled();
        $persisterProphecy->persist([$objects[1]])->shouldBeCalled();

        $loader = new Loader($aliceLoaderProphecy->reveal(), [], false);
        $objects = $loader->load(
            $persisterProphecy->reveal(),
            [
                'random/file1',
                'random/file2',
            ]
        );

        $this->assertEquals($objects, $objects);
    }

    /**
     * @cover ::load
     * @cover ::persist
     */
    public function testLoadWithPersistOnceAtTrue()
    {
        $objects = [
            new \stdClass(),
            new \stdClass(),
        ];

        $aliceLoaderProphecy = $this->prophesize('Nelmio\Alice\Fixtures\Loader');
        $aliceLoaderProphecy->load('random/file1')->willReturn([$objects[0]]);
        $aliceLoaderProphecy->load('random/file2')->willReturn([$objects[0]]);

        $persisterProphecy = $this->prophesize('Nelmio\Alice\PersisterInterface');
        $persisterProphecy->persist($objects)->shouldBeCalled();

        $loader = new Loader($aliceLoaderProphecy->reveal(), [], true);
        $objects = $loader->load(
            $persisterProphecy->reveal(),
            [
                'random/file1',
                'random/file2',
            ]
        );

        $this->assertEquals($objects, $objects);
    }

    /**
     * @cover ::load
     * @cover ::persist
     */
    public function testLoadWithFixturesAndProcessors()
    {
        $object = new \stdClass();

        $aliceLoaderProphecy = $this->prophesize('Nelmio\Alice\Fixtures\Loader');
        $aliceLoaderProphecy->load('random/file')->willReturn([$object]);

        $persisterProphecy = $this->prophesize('Nelmio\Alice\PersisterInterface');
        $persisterProphecy->persist([$object])->shouldBeCalled();

        $processorProphecy = $this->prophesize('Nelmio\Alice\ProcessorInterface');
        $processorProphecy->preProcess($object)->shouldBeCalled();
        $processorProphecy->postProcess($object)->shouldBeCalled();

        $loader = new Loader($aliceLoaderProphecy->reveal(), [$processorProphecy->reveal()], false);
        $objects = $loader->load($persisterProphecy->reveal(), ['random/file']);

        $this->assertEquals([$object], $objects);
    }
}
