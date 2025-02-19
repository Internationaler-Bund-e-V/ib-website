<?php

declare(strict_types=1);

use ApacheSolrForTypo3\Solrconsole\Command\SolrfalQueueDeleteCommand;
use ApacheSolrForTypo3\Solrconsole\Command\SolrfalQueueGetCommand;
use ApacheSolrForTypo3\Solrconsole\Command\SolrfalQueueIndexCommand;
use ApacheSolrForTypo3\Solrconsole\Command\SolrfalQueueProgressCommand;
use ApacheSolrForTypo3\Solrconsole\Command\SolrfalQueueResetErrorsCommand;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return function (ContainerConfigurator $configurator, ContainerBuilder $containerBuilder) {
    if ($containerBuilder->hasDefinition('ApacheSolrForTypo3\Solrfal\Command\ResetQueueErrorsCommand')) {
        $solrfalCommands = [
            SolrfalQueueDeleteCommand::class => 'solrfal:queue:delete',
            SolrfalQueueGetCommand::class => 'solrfal:queue:get',
            SolrfalQueueIndexCommand::class => 'solrfal:queue:index',
            SolrfalQueueProgressCommand::class => 'solrfal:queue:progress',
            SolrfalQueueResetErrorsCommand::class => 'solrfal:queue:reset-errors',
        ];

        foreach ($solrfalCommands as $class => $command) {
            $definition = new Definition($class);
            $definition->setTags([
                'console.command' => [
                    [
                        'command' => $command,
                        'schedulable' => false,
                    ],
                ],
            ]);
            $containerBuilder->addDefinitions([
                $class => $definition,
            ]);
        }
    }
};
