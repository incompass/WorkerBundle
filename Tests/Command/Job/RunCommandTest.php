<?php

namespace Incompass\WorkerBundle\Tests\Command\Job;

use Exception;
use Incompass\WorkerBundle\Command\Job\RunCommand;
use Incompass\WorkerBundle\Entity\Job;
use Incompass\WorkerBundle\Event\JobEvent;
use Incompass\WorkerBundle\Tests\Util\CommandTestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class RunCommandTest
 *
 * @package Incompass\WorkerBundle\Tests\Command\Job
 * @author  Joe Mizzi <joe@casechek.com>
 */
class RunCommandTest extends CommandTestCase
{
    /** @var string */
    protected $commandClass = RunCommand::class;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /**
     * Setup the event dispatcher.
     */
    protected function setUp()
    {
        parent::setUp();
    }

    /**
     * @test
     */
    public function it_runs_a_job_with_an_id()
    {
        $this->jobDispatcher->dispatch('some.event', new JobEvent());
        /** @var Job $job */
        $job = $this->entityManager->getRepository(Job::class)->findOneBy(
            [
                'event' => 'some.event',
            ]
        );
        $this->eventDispatcher->addListener('some.event', function(JobEvent $jobEvent) {
            self::assertNotEmpty($jobEvent);
        });
        $this->commandTester->execute(
            [
                'command' => $this->command->getName(),
                'id' => $job->getId(),
            ]
        );
        self::assertEquals(Job::STATUS_FINISHED, $job->getStatus());
    }

    /**
     * @test
     */
    public function it_runs_a_job()
    {
        $here = false;
        $job = $this->executeWithListenerCallback(function(JobEvent $jobEvent) use (&$here) {
            $here = true;
        });
        self::assertTrue($here);
        self::assertEquals(Job::STATUS_FINISHED, $job->getStatus());
        self::assertNotEmpty($job->getLastStarted());
        self::assertNotEmpty($job->getLastFinished());
        self::assertEquals(1, $job->getTries());
    }

    /**
     * @test
     */
    public function it_deletes_a_job_if_delete_job_set()
    {
        $here = false;
        $job = $this->executeWithListenerCallback(function(JobEvent $jobEvent) use (&$here) {
            $here = true;
        }, true);
        self::assertNull($job);
        self::assertTrue($here);
    }

    /**
     * @test
     */
    public function it_adds_messages_to_a_job()
    {
        $job = $this->executeWithListenerCallback(function(JobEvent $jobEvent) {
            $jobEvent->addJobMessage('some message');
            $jobEvent->addJobMessage('some other message');
        });
        self::assertCount(2, $job->getMessages());
    }

    /**
     * @test
     */
    public function it_fails_on_exception_thrown()
    {
        $job = $this->executeWithListenerCallback(function(JobEvent $jobEvent) {
            throw new Exception('Any exception');
        });
        self::assertEquals(Job::STATUS_FAILED, $job->getStatus());
        self::assertEquals('Any exception', $job->getMessages()->first()->getMessage());
    }

    /**
     * @test
     */
    public function it_does_nothing_on_no_job_found()
    {
        $this->commandTester->execute(
            [
                'command' => $this->command->getName(),
                'id' => 999999,
            ]
        );
        self::assertEquals(0, $this->commandTester->getStatusCode());
    }

    /**
     * @param callable $callback
     * @return null|Job|object
     */
    protected function executeWithListenerCallback(callable $callback, $delete = false)
    {
        $this->jobDispatcher->dispatch('some.event', new JobEvent(), $delete);
        $this->eventDispatcher->addListener('some.event', $callback);
        $this->commandTester->execute(
            [
                'command' => $this->command->getName(),
            ]
        );
        return $this->entityManager->getRepository(Job::class)->findOneBy(
            [
                'event' => 'some.event',
            ]
        );
    }

    /**
     * @return RunCommand
     */
    protected function getCommandConstructor()
    {
        $this->eventDispatcher = self::$kernel->getContainer()->get(
            'event_dispatcher'
        );
        return new RunCommand($this->entityManager, $this->eventDispatcher);
    }
}