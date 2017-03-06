<?php

namespace Incompass\WorkerBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class JobEvent
 *
 * @package WorkerBundle\Event
 * @author  Joe Mizzi <joe@casechek.com>
 */
class JobEvent extends Event
{
    /**
     * @var array
     */
    protected $jobMessages = [];

    /** @var bool */
    protected $deleteJob = false;

    /**
     * @param $message
     */
    public function addJobMessage($message)
    {
        $this->jobMessages[] = $message;
    }

    /**
     * @return array
     */
    public function getJobMessages()
    {
        return $this->jobMessages;
    }
}