<?php

namespace Incompass\WorkerBundle\Command\Job;

use Doctrine\Common\Persistence\ObjectManager;
use Incompass\WorkerBundle\Entity\Job;
use Incompass\WorkerBundle\Entity\Message;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class MessagesCommand
 *
 * @package WorkerBundle\Command
 * @author  Joe Mizzi <joe@casechek.com>
 */
class MessagesCommand extends Command
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * MessagesCommand constructor.
     *
     * @param ObjectManager $objectManager
     */
    public function __construct(ObjectManager $objectManager)
    {
        parent::__construct(null);
        $this->objectManager = $objectManager;
    }

    /**
     * Configure the command.
     */
    protected function configure()
    {
        $this->setName('worker:job:messages');
        $this->addArgument('id', InputArgument::REQUIRED);
        $this->setDescription('Lists messages for a job');
    }

    /**
     * Execute the command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $job = $this->objectManager->find(
            Job::class,
            $input->getArgument('id')
        );
        $table = new Table($output);
        $table->setHeaders(
            [
                'id',
                'job_id',
                'message',
                'created_at',
                'updated_at',
            ]
        );
        $rows = [];
        /** @var Message $message */
        foreach ($job->getMessages() as $message) {
            $rows[] = [
                $message->getId(),
                $message->getJob()->getId(),
                $message->getMessage(),
                $message->getCreatedAt()->format('Y-m-d H:i:s'),
                $message->getUpdatedAt()->format('Y-m-d H:i:s'),
            ];
        }
        $table->setRows($rows);
        $table->render();
    }
}