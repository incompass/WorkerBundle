<?php

namespace Incompass\WorkerBundle\DependencyInjection;

use Incompass\WorkerBundle\Command\Job\ListCommand;
use Incompass\WorkerBundle\Command\Job\MessagesCommand;
use Incompass\WorkerBundle\Command\Job\RunCommand;
use Incompass\WorkerBundle\Command\RunCommand as WorkerRunCommand;
use Incompass\WorkerBundle\Core\JobDispatcher;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

/**
 * Class WorkerExtension
 *
 * @package WorkerBundle\DependencyInjection
 * @author  Joe Mizzi <joe@casechek.com>
 */
class WorkerExtension extends Extension
{
    /**
     * @param array $configs
     * @param ContainerBuilder $container
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $this->setCommandDefinition($container, WorkerRunCommand::class);
        $this->setCommandDefinition(
            $container,
            RunCommand::class,
            'job',
            [new Reference('event_dispatcher')]
        );
        $this->setCommandDefinition($container, ListCommand::class, 'job');
        $this->setCommandDefinition($container, MessagesCommand::class, 'job');
        $this->addJobDispatcherService($container);
    }

    /**
     * @param ContainerBuilder $container
     * @param $commandClass
     * @param null $prefix
     * @param array $additionalArguments
     */
    private function setCommandDefinition(
        ContainerBuilder $container,
        $commandClass,
        $prefix = null,
        array $additionalArguments = []
    ) {
        $definition = new Definition($commandClass);
        $definition->addTag('console.command');
        $definition->setArguments(
            array_merge(
                [new Reference('doctrine.orm.entity_manager')],
                $additionalArguments
            )
        );

        $reflection = new \ReflectionClass($commandClass);
        $converter = new CamelCaseToSnakeCaseNameConverter();
        $name = 'worker.'.($prefix ? ($prefix.'.') : '').$converter->normalize(
                $reflection->getShortName()
            );
        $container->setDefinition($name, $definition);
    }

    /**
     * @param ContainerBuilder $container
     */
    private function addJobDispatcherService(ContainerBuilder $container)
    {
        $definition = new Definition(JobDispatcher::class);
        $definition->setAutowired(true);
        $definition->setArguments(
            [new Reference('doctrine.orm.entity_manager')]
        );
        $container->setDefinition('worker.job_dispatcher', $definition);
    }
}