<?php

namespace Incompass\WorkerBundle\Command\Job;

use Doctrine\ORM\EntityManagerInterface;
use Incompass\WorkerBundle\Entity\Job;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ListCommand
 *
 * @package Incompass\WorkerBundle\Command\Job
 * @author  Joe Mizzi <joe@casechek.com>
 */
class ListCommand extends Command
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * ListCommand constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @internal param ObjectManager $objectManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    /**
     * Configure the cammand.
     */
    protected function configure()
    {
        $this->setName('worker:job:list');
        $this->setDescription('Lists jobs');
        $this->addOption('id', null,
            InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
            'Filter by ids');
        $this->addOption('event', null,
            InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
            'Filter by event');
        $this->addOption('status', null,
            InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
            'Filter by status');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $jobs = $this->entityManager->createQueryBuilder()
            ->select('j')
            ->from('WorkerBundle:Job', 'j');

        if ($ids = $input->getOption('id')) {
            $jobs->andWhere('j.id in (?1)');
            $jobs->setParameter(1, $ids);
        }

        if ($events = $input->getOption('event')) {
            $jobs->andWhere('j.event in (?2)');
            $jobs->setParameter(2, $events);
        }

        if ($statuses = $input->getOption('status')) {
            $jobs->andWhere('j.status in (?3)');
            $jobs->setParameter(3, $statuses);
        }

        $jobs = $jobs->getQuery()->getResult();

        $rows = [];
        /** @var Job $job */
        foreach ($jobs as $job) {
            $rows[] = [
                $job->getId(),
                $job->getEvent(),
                $job->getTries(),
                $job->getStatus(),
                $job->getLastStarted() ? $job->getLastStarted()->format('Y-m-d H:is') : '',
                $job->getLastFinished() ? $job->getLastFinished()->format('Y-m-d H:is') : '',
            ];
        }
        $table = new Table($output);
        $table->setHeaders([
            'id',
            'event',
            'tries',
            'status',
            'last_started',
            'last_finished',
        ])->setRows($rows);
        $table->render();
    }
}