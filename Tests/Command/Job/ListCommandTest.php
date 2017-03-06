<?php

namespace Incompass\WorkerBundle\Tests\Command\Job;

use Incompass\WorkerBundle\Command\Job\ListCommand;
use Incompass\WorkerBundle\Entity\Job;
use Incompass\WorkerBundle\Event\JobEvent;
use Incompass\WorkerBundle\Tests\Util\CommandTestCase;

/**
 * Class ListCommandTest
 *
 * @package Incompass\WorkerBundle\Tests\Command\Job
 * @author  Joe Mizzi <joe@casechek.com>
 */
class ListCommandTest extends CommandTestCase
{
    /** @var string */
    protected $commandClass = ListCommand::class;

    /**
     * @test
     */
    public function it_lists_jobs()
    {
        $this->jobDispatcher->dispatch('some.event', new JobEvent());
        $this->commandTester->execute([
            'command' => $this->command->getName(),
        ]);
        $output = $this->commandTester->getDisplay();
        self::assertContains('some.event', $output);
    }

    /**
     * @test
     */
    public function it_lists_jobs_by_ids()
    {
        for ($i = 1; $i <= 10; $i++) {
            $this->jobDispatcher->dispatch('some.event', new JobEvent());
        }
        /** @var Job[] $jobs */
        $jobs = $this->entityManager->getRepository(Job::class)->findAll();
        $ids = [];
        for ($i = 0; $i <= 2; $i++) {
            $ids[] = $jobs[$i]->getId();
        }
        $this->commandTester->execute([
            'command' => $this->command->getName(),
            '--id' => $ids
        ]);
        $output = $this->commandTester->getDisplay();
        self::assertLessThanOrEqual(7, substr_count($output, PHP_EOL));
    }

    /**
     * @test
     */
    public function it_lists_jobs_by_event()
    {
        for ($i = 1; $i <= 5; $i++) $this->jobDispatcher->dispatch('some.event', new JobEvent());
        for ($i = 1; $i <= 5; $i++) $this->jobDispatcher->dispatch('some.other_event', new JobEvent());
        $this->commandTester->execute([
            'command' => $this->command->getName(),
            '--event' => 'some.event'
        ]);
        $output = $this->commandTester->getDisplay();
        self::assertEquals(5, substr_count($output, 'some.event'));
        self::assertEmpty(substr_count($output, 'some.other_event'));
    }

    /**
     * @test
     */
    public function it_lists_jobs_by_status()
    {
        for ($i = 1; $i <= 10; $i++) $this->jobDispatcher->dispatch('some.event', new JobEvent());
        /** @var Job[] $jobs */
        $jobs = $this->entityManager->getRepository(Job::class)->findAll();
        for ($i = 0; $i <= 4; $i++) {
            $jobs[$i]->setStatus(Job::STATUS_FAILED);
            $this->entityManager->persist($jobs[$i]);
        }
        $this->entityManager->flush();
        $this->commandTester->execute([
            'command' => $this->command->getName(),
            '--status' => Job::STATUS_FAILED
        ]);
        $output = $this->commandTester->getDisplay();
        self::assertLessThanOrEqual(9, substr_count($output, PHP_EOL));
    }

}