<?php

namespace Incompass\WorkerBundle\Tests\Util;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class CommandTestCase
 *
 * @package Incompass\WorkerBundle\Tests\Util
 * @author  Joe Mizzi <joe@casechek.com>
 */
abstract class CommandTestCase extends EntityManagerAwareTestCase
{
    /** @var Application */
    protected $application;

    /** @var string */
    protected $commandClass;

    /** @var CommandTester */
    protected $commandTester;

    /** @var  Command */
    protected $command;

    /**
     * Boot the kernel
     */
    protected function setUp()
    {
        parent::setUp();
        if (!isset($this->commandClass)) {
            throw new Exception('Must set command class on CommandTestCase');
        }
        if ($constructor = $this->getCommandConstructor()) {
            $this->command = $constructor;
        } else {
            $this->command = new $this->commandClass($this->entityManager);
        }
        $this->application->add($this->command);
        $this->commandTester = new CommandTester($this->command);
    }

    /**
     * @return null|callable
     */
    protected function getCommandConstructor()
    {
        return null;
    }
}