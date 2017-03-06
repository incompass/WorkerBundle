<?php

namespace Incompass\WorkerBundle\Command\Job;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManager;
use Exception;
use Incompass\WorkerBundle\Entity\Job;
use Incompass\WorkerBundle\Entity\Message;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class RunCommand
 *
 * @package WorkerBundle\Command
 * @author  Joe Mizzi <joe@casechek.com>
 */
class RunCommand extends Command
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * RunCommand constructor.
     *
     * @param EntityManager $entityManager
     * @param EventDispatcherInterface $eventDispatcher
     * @param $retries
     */
    public function __construct(
        EntityManager $entityManager,
        EventDispatcherInterface $eventDispatcher
    ) {
        parent::__construct(null);

        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Configure the command.
     */
    protected function configure()
    {
        $this->setName('worker:job:run');
        $this->addArgument(
            'id',
            InputArgument::OPTIONAL,
            'Id of a specific job to run'
        );
        $this->addOption(
            'retries',
            null,
            InputOption::VALUE_REQUIRED,
            'Number of times to retry a job',
            5
        );
        $this->setDescription('Start processing jobs');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->entityManager->getConnection()->beginTransaction();

        if ($id = $input->getArgument('id')) {
            $job = $this->entityManager->createQueryBuilder()
                ->select('j')
                ->from('WorkerBundle:Job', 'j')
                ->where('j.id = ?1')
                ->andWhere('j.status in (?2)')
                ->setParameter(1, $id)
                ->setParameter(
                    2,
                    [
                        Job::STATUS_NEW,
                        Job::STATUS_FAILED,
                    ]
                )
                ->setMaxResults(1)
                ->getQuery()
                ->setLockMode(LockMode::PESSIMISTIC_WRITE)
                ->getOneOrNullResult();
        } else {
            $job = $this->entityManager->createQueryBuilder()
                ->select('j')
                ->from('WorkerBundle:Job', 'j')
                ->orderBy('j.createdAt')
                ->where('j.status in (?1)')
                ->setParameter(
                    1,
                    [
                        Job::STATUS_NEW
                    ]
                )
                ->setMaxResults(1)
                ->getQuery()
                ->setLockMode(LockMode::PESSIMISTIC_WRITE)
                ->getOneOrNullResult();
        }

        /** @var Job|null $job */
        if ($job) {
            $job->start();
            $this->entityManager->persist($job);
            $this->entityManager->flush();
            $this->entityManager->getConnection()->commit();

            try {
                $this->eventDispatcher->dispatch(
                    $job->getEvent(),
                    $jobEvent = unserialize($job->getData())
                );
                if ($job->isDeleteOnFinish()) {
                    $this->entityManager->remove($job);
                } else {
                    foreach ($jobEvent->getJobMessages() as $eventMessage) {
                        $message = new Message();
                        $message->setMessage($eventMessage);
                        $job->addMessage($message);
                    }
                    $job->finish();
                    $this->entityManager->persist($job);
                }
            } catch (Exception $exception) {
                $this->failJob($job, $exception);
            }
            $this->entityManager->flush();
        } else {
            $this->entityManager->getConnection()->rollback();
        }
    }

    /**
     * @param Job $job
     * @param Exception $exception
     */
    protected function failJob(Job $job, Exception $exception = null)
    {
        if ($exception) {
            $message = new Message();
            $message->setMessage($exception->getMessage());
            $job->addMessage($message);

        }
        $job->finish(Job::STATUS_FAILED);
    }
}