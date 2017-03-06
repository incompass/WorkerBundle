<?php

namespace Incompass\WorkerBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Incompass\TimestampableBundle\Entity\Timestampable;

/**
 * Class Job
 *
 * @package WorkerBundle\Entity
 * @author  Joe Mizzi <joe@casechek.com>
 *
 * @ORM\Table(name="worker_jobs")
 * @ORM\Entity()
 */
class Job
{
    use Timestampable;

    const STATUS_NEW = 'new'; // brand new job that has never been run.
    const STATE_PROCESSING = 'processing'; // current work in progress
    const STATUS_FAILED = 'failed'; // failed and not to be retried, possibly failed_retry the max times
    const STATUS_FINISHED = 'finished'; // completed successfully

    /**
     * @ORM\Column(type="integer", options={"unsigned"=true})
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var integer
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=30)
     *
     * @var string
     */
    private $event;

    /**
     * @ORM\Column(type="text")
     *
     * @var string
     */
    private $data;

    /**
     * @ORM\Column(type="integer", options={"unsigned"=true, "default"=0})
     *
     * @var integer
     */
    private $tries = 0;

    /**
     * @ORM\Column(type="string", length=30, options={"default"="new"})
     *
     * @var string
     */
    private $status = self::STATUS_NEW;

    /**
     * @ORM\Column(name="last_started", type="datetime", nullable=true)
     *
     * @var DateTime|null
     */
    private $lastStarted;

    /**
     * @ORM\Column(name="last_finished", type="datetime", nullable=true)
     *
     * @var DateTime|null
     */
    private $lastFinished;

    /**
     * @ORM\OneToMany(targetEntity="Incompass\WorkerBundle\Entity\Message", mappedBy="job", cascade={"persist", "remove"})
     *
     * @var PersistentCollection
     */
    private $messages;

    /**
     * @ORM\Column(name="delete_on_finish", type="boolean", options={"default"=false})
     *
     * @var boolean
     */
    private $deleteOnFinish = false;

    /**
     * @return mixed
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getEvent(): string
    {
        return $this->event;
    }

    /**
     * @param string $event
     * @return Job
     */
    public function setEvent(string $event): Job
    {
        $this->event = $event;

        return $this;
    }

    /**
     * @return string
     */
    public function getData(): string
    {
        return $this->data;
    }

    /**
     * @param string $data
     * @return Job
     */
    public function setData(string $data): Job
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return int
     */
    public function getTries(): int
    {
        return $this->tries;
    }

    /**
     * @return Job
     */
    protected function try(): Job
    {
        $this->tries++;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return Job
     */
    public function setStatus(string $status): Job
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return PersistentCollection|null $messages
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @param Message $message
     * @return $this
     */
    public function addMessage(Message $message)
    {
        $message->setJob($this);
        $this->messages[] = $message;
        return $this;
    }

    /**
     * Start a job
     */
    public function start()
    {
        $this->setStatus(Job::STATE_PROCESSING);
        $this->try();
        $this->lastStarted = new DateTime();
        $this->lastFinished = null;
    }

    /**
     * Finish a job
     * @param string|null $status
     */
    public function finish($status = null)
    {
        $this->setStatus($status ?: Job::STATUS_FINISHED);
        $this->lastFinished = new DateTime();
    }

    /**
     * @return DateTime|null
     */
    public function getLastStarted()
    {
        return $this->lastStarted;
    }

    /**
     * @return DateTime|null
     */
    public function getLastFinished()
    {
        return $this->lastFinished;
    }

    /**
     * @param bool $deleteOnFinish
     * @return Job
     */
    public function setDeleteOnFinish(bool $deleteOnFinish): Job
    {
        $this->deleteOnFinish = $deleteOnFinish;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDeleteOnFinish(): bool
    {
        return $this->deleteOnFinish;
    }
}