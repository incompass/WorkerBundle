<?php

namespace Incompass\WorkerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Incompass\TimestampableBundle\Entity\Timestampable;

/**
 * Class Message
 *
 * @package WorkerBundle\Entity
 * @author  Joe Mizzi <joe@casechek.com>
 *
 * @ORM\Table(name="worker_job_messages")
 * @ORM\Entity()
 */
class Message
{
    use Timestampable;

    /**
     * @ORM\Column(type="integer", options={"unsigned"=true})
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var integer
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     *
     * @var string
     */
    private $message;

    /**
     * @ORM\ManyToOne(targetEntity="Incompass\WorkerBundle\Entity\Job", inversedBy="messages", cascade={"persist", "remove"})
     *
     * @var Job
     */
    private $job;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     * @return Message
     */
    public function setMessage(string $message): Message
    {
        $this->message = $message;
        return $this;
    }

    /**
     * @return Job
     */
    public function getJob(): Job
    {
        return $this->job;
    }

    /**
     * @param Job $job
     * @return Message
     */
    public function setJob(Job $job): Message
    {
        $this->job = $job;
        return $this;
    }
}