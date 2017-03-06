<?php

namespace Incompass\WorkerBundle\Core;

use Doctrine\Common\Persistence\ObjectManager;
use Incompass\WorkerBundle\Entity\Job;
use Incompass\WorkerBundle\Event\JobEvent;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class JobDispatcher
 *
 * @package Incompass\WorkerBundle\Core
 * @author  Joe Mizzi <joe@casechek.com>
 */
class JobDispatcher
{
    /** @var ObjectManager */
    protected $objectManager;

    /**
     * JobDispatcher constructor.
     *
     * @param ObjectManager $objectManager
     */
    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param $eventName
     * @param JobEvent|null|Event $event
     * @param bool $deleteOnFinish
     */
    public function dispatch($eventName, JobEvent $event, $deleteOnFinish = false)
    {
        $job = new Job();
        $job->setEvent($eventName);
        $job->setData(serialize($event));
        $job->setDeleteOnFinish($deleteOnFinish);
        $this->objectManager->persist($job);
        $this->objectManager->flush();
    }
}