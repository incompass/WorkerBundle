<?php

namespace Incompass\WorkerBundle\Tests;

use Doctrine\ORM\EntityManager;
use Incompass\WorkerBundle\Command\Job\ListCommand;
use Incompass\WorkerBundle\Command\Job\MessagesCommand;
use Incompass\WorkerBundle\Command\Job\RunCommand as JobRunCommand;
use Incompass\WorkerBundle\Command\RunCommand;
use Incompass\WorkerBundle\Core\JobDispatcher;
use Incompass\WorkerBundle\DependencyInjection\WorkerExtension;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class WorkerExtensionTest
 *
 * @package Incompass\TimestampableBundle\Tests
 * @author  Joe Mizzi <joe@casechek.com>
 */
class WorkerExtensionTest extends TestCase
{
    /**
     * @var ContainerBuilder
     */
    protected $container;

    /**
     * Setup the tests.
     */
    protected function setUp()
    {
        $extension = new WorkerExtension();
        $this->container = new ContainerBuilder();
        $this->container->registerExtension($extension);
        $this->container->loadFromExtension('worker');
        $entityManager = \Mockery::mock(EntityManager::class);
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $entityManager->shouldReceive('getConnection->getConfiguration->setSQLLogger')
            ->withAnyArgs();
        $this->container->set('doctrine.orm.entity_manager', $entityManager);
        $eventDispatcher = \Mockery::mock(EventDispatcherInterface::class);
        $this->container->set('event_dispatcher', $eventDispatcher);
        $this->container->compile();
    }

    /**
     * @test
     */
    public function it_adds_the_commands()
    {
        self::assertInstanceOf(RunCommand::class, $this->container->get('worker.run_command'));
        self::assertInstanceOf(JobRunCommand::class, $this->container->get('worker.job.run_command'));
        self::assertInstanceOf(ListCommand::class, $this->container->get('worker.job.list_command'));
        self::assertInstanceOf(MessagesCommand::class, $this->container->get('worker.job.messages_command'));
    }

    /**
     * @test
     */
    public function it_adds_the_dispatcher()
    {
        self::assertInstanceOf(JobDispatcher::class, $this->container->get('worker.job_dispatcher'));
    }
}