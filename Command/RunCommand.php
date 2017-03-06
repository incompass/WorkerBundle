<?php

namespace Incompass\WorkerBundle\Command;

use Doctrine\ORM\EntityManager;
use Incompass\WorkerBundle\Entity\Job;
use SplObjectStorage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * Class RunCommand
 *
 * @package WorkerBundle\Command
 * @author  Joe Mizzi <joe@casechek.com>
 */
class RunCommand extends Command
{
    /**
     * @var SplObjectStorage
     */
    private $processes;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * RunCommand constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        parent::__construct(null);
        $this->processes = new SplObjectStorage();
        $this->entityManager = $entityManager;

        // need to disable the logger for memory savings on the main thread.
        $this->entityManager->getConnection()->getConfiguration()->setSQLLogger(
            null
        );
    }

    /**
     * Configure the command.
     */
    protected function configure()
    {
        $this->setName('worker:run');
        $this->addOption(
            'retries',
            null,
            InputOption::VALUE_REQUIRED,
            'Number of times to retry a job',
            5
        );
        $this->addOption(
            'processes',
            null,
            InputOption::VALUE_REQUIRED,
            'Number of parallel process to run',
            5
        );
        $this->addOption(
            'exit',
            null,
            InputOption::VALUE_NONE,
            'Exit when there are no new jobs'
        );
        $this->addOption(
            'working-dir',
            null,
            InputOption::VALUE_REQUIRED,
            'Working directory for processes'
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
        ProgressBar::setPlaceholderFormatterDefinition(
            'remaining_jobs',
            function (ProgressBar $bar, OutputInterface $output) {
                return $this->getJobCount();
            }
        );
        ProgressBar::setPlaceholderFormatterDefinition(
            'processes',
            function (ProgressBar $bar, OutputInterface $output) {
                return count($this->processes);
            }
        );
        $progress = new ProgressBar($output);
        $progress->setFormat(
            '[%bar%] %memory:6s% Jobs:%remaining_jobs% Processes:%processes%'
        );
        $progress->start();

        $processes = $input->getOption('processes');
        while (true) {
            $jobCount = $this->getJobCount();
            // start process if there aren't x current process
            if (count($this->processes) < $processes && $jobCount) {
                while (count($this->processes) <= $processes && $jobCount) {
                    $process = new Process('php bin/console worker:job:run');
                    if ($workingDir = $input->getOption('working-dir')) {
                        $process->setWorkingDirectory($workingDir);
                    }
                    $this->processes->attach($process);
                    $process->start();
                }
            } elseif (count($this->processes) == 0 && $input->getOption(
                    'exit'
                )
            ) {
                break;
            }

            /** @var Process $process */
            foreach ($this->processes as $process) {
                if (!$process->isRunning()) {
                    $this->processes->detach($process);
                    $cwd = $process->getWorkingDirectory();
                    $output = $process->getOutput();
                    $progress->advance();
                }
            }

            $this->entityManager->clear();
            gc_collect_cycles();
            usleep(100);
        }
    }

    /**
     * @return mixed
     */
    protected function getJobCount()
    {
        return $this->entityManager->createQueryBuilder()
            ->select('count(j.id)')
            ->from('WorkerBundle:Job', 'j')
            ->where('j.status in (?1)')
            ->setParameter(
                1,
                [
                    Job::STATUS_NEW
                ]
            )
            ->getQuery()->getSingleScalarResult();
    }
}