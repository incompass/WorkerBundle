<?php

namespace Incompass\WorkerBundle\Tests\Core;

use Incompass\WorkerBundle\Core\JobDispatcher;
use Incompass\WorkerBundle\Entity\Job;
use Incompass\WorkerBundle\Event\JobEvent;
use Incompass\WorkerBundle\Tests\Util\EntityManagerAwareTestCase;

/**
 * Class JobDispatcherTest
 *
 * @package Incompass\WorkerBundle\Tests\Core
 * @author  Joe Mizzi <joe@casechek.com>
 */
class JobDispatcherTest extends EntityManagerAwareTestCase
{
    /**
     * @test
     */
    public function it_dispatches_a_job()
    {
        $this->jobDispatcher->dispatch('some.event', new JobEvent());
        /** @var Job $job */
        $job = $this->entityManager->getRepository(Job::class)->findOneBy([
            'event' => 'some.event'
        ]);
        self::assertNotNull($job);
        self::assertNotNull($job->getData());
        self::assertFalse($job->isDeleteOnFinish());
    }

    /**
     * @test
     */
    public function it_dispatches_a_job_with_delete_on_finish()
    {
        $this->jobDispatcher->dispatch('some.event', new JobEvent(), true);
        /** @var Job $job */
        $job = $this->entityManager->getRepository(Job::class)->findOneBy([
            'event' => 'some.event'
        ]);
        self::assertNotNull($job);
        self::assertNotNull($job->getData());
        self::assertTrue($job->isDeleteOnFinish());
    }
}