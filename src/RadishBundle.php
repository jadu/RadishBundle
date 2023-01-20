<?php

namespace Radish\RadishBundle;

use Radish\RadishBundle\Command\ConsumeCommand;
use Radish\RadishBundle\Command\PollCommand;
use Radish\RadishBundle\Command\SetupCommand;
use Radish\RadishBundle\DependencyInjection\Compiler\RegisterMiddlewarePass;
use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class RadishBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new RegisterMiddlewarePass());
    }

    public function registerCommands(Application $application)
    {
        $application->add($this->container->get(ConsumeCommand::class));
        $application->add($this->container->get(PollCommand::class));
        $application->add($this->container->get(SetupCommand::class));
    }

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
