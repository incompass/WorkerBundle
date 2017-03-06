<?php

namespace Incompass\WorkerBundle\Tests\Command;

use Incompass\WorkerBundle\Command\RunCommand;
use Incompass\WorkerBundle\Entity\Job;
use Incompass\WorkerBundle\Event\JobEvent;
use Incompass\WorkerBundle\Tests\Util\CommandTestCase;

/**
 * Class RunCommandTest
 *
 * @package Incompass\WorkerBundle\Tests\Command
 * @author  Joe Mizzi <joe@casechek.com>
 */
class RunCommandTest extends CommandTestCase
{
    protected $commandClass = RunCommand::class;

    /**
     * @test
     */
    public function it_runs_jobs()
    {
        for ($i = 1; $i <= 5; $i++) {
            $this->jobDispatcher->dispatch('some.event', new JobEvent());
        }
        $this->commandTester->execute(
            [
                'command' => $this->command->getName(),
                '--exit' => true,
                '--working-dir' => __DIR__.'/../../'
            ]
        );
        $jobs = $this->entityManager->getRepository(Job::class)->findAll();
        /** @var Job $job */
        foreach ($jobs as $job) {
            self::assertEquals(Job::STATUS_FINISHED, $job->getStatus());
        }
    }
}