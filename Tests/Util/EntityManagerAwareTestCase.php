<?php

namespace Incompass\WorkerBundle\Tests\Util;

use Doctrine\ORM\EntityManager;
use Incompass\WorkerBundle\Core\JobDispatcher;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;

/**
 * Class ContainerAwareTestCase
 *
 * @package Incompass\WorkerBundle\Tests\Util
 * @author  Joe Mizzi <joe@casechek.com>
 */
abstract class EntityManagerAwareTestCase extends KernelTestCase
{
    /** @var EntityManager */
    protected $entityManager;

    /** @var Application */
    protected $application;

    /** @var JobDispatcher */
    protected $jobDispatcher;

    /**
     * Boot the kernel and get the entity manager.
     */
    protected function setUp()
    {
        self::bootKernel();
        $this->application = new Application(self::$kernel);
        $this->application->setAutoExit(false);
        $this->application->run(
            new StringInput('doctrine:schema:drop --force'),
            new NullOutput()
        );
        $this->application->run(
            new StringInput('doctrine:schema:update --force'),
            new NullOutput()
        );
        $this->entityManager = self::$kernel->getContainer()->get(
            'doctrine'
        )->getManager();
        $this->jobDispatcher = self::$kernel->getContainer()->get(
            'worker.job_dispatcher'
        );
    }
}