<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ServiceTasksBundle\Task;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DefaultTask
{
    /**
     * The DI Container we get the services from.
     *
     * @var ContainerInterface
     */
    protected $container;

    /** @var OutputInterface $output */
    protected $output;

    /** @var ArrayCollection $runParameters */
    protected $runParameters;

    /**
     * DefaultTask constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $router = $container->get('router');
        $context = $router->getContext();
        $env = $container->getParameter('kernel.environment');
        $appName = $container->getParameter('app_name');
        $mealmatchHosts = array(
            'dev' => 'mealmatch.local',
            'test' => 'mealmatch.local',
            'stage' => 'mealmatch-stage.frb.io',
            'prod' => 'mealmatch.de',
        );
        $context->setHost($mealmatchHosts[$env]);
        $context->setScheme('https');
    }

    public function runWithParameters(ArrayCollection $parameters)
    {
        $this->runParameters = $parameters;
        /** @var string $taskName */
        $taskName = $parameters->get('taskName');

        /** @var OutputInterface $output */
        $output = $parameters->get('output');
        $this->output = $output;

        $output->writeln(
            array(
                'RunWithParameters' => json_encode($parameters->toArray()),
            )
        );
    }

    protected function sendMail($message)
    {
        $this->container->get('swiftmailer.mailer')->send($message);
    }
}
