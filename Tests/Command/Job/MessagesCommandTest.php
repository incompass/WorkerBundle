<?php

namespace Incompass\WorkerBundle\Tests\Command\Job;

use Incompass\WorkerBundle\Command\Job\MessagesCommand;
use Incompass\WorkerBundle\Entity\Job;
use Incompass\WorkerBundle\Entity\Message;
use Incompass\WorkerBundle\Event\JobEvent;
use Incompass\WorkerBundle\Tests\Util\CommandTestCase;

/**
 * Class MessagesCommandTest
 *
 * @package Incompass\WorkerBundle\Tests\Command\Job
 * @author  Joe Mizzi <joe@casechek.com>
 */
class MessagesCommandTest extends CommandTestCase
{
    /** @var string */
    protected $commandClass = MessagesCommand::class;

    /**
     * @test
     */
    public function it_lists_messages_for_a_job()
    {
        $this->jobDispatcher->dispatch('some.event', new JobEvent());
        /** @var Job $job */
        $job = $this->entityManager->getRepository(Job::class)->findOneBy([
            'event' => 'some.event'
        ]);
        $message = new Message();
        $message->setMessage('some message');
        $job->addMessage($message);
        $this->entityManager->persist($job);
        $this->entityManager->persist($message);
        $this->entityManager->flush();
        $this->commandTester->execute([
            'command' => $this->command->getName(),
            'id' => $job->getId()
        ]);
        $output = $this->commandTester->getDisplay();
        self::assertContains('some message', $output);
    }
}