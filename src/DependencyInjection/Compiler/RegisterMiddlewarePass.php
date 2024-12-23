<?php

namespace Radish\RadishBundle\DependencyInjection\Compiler;

use Radish\Middleware\MiddlewareRegistry;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class RegisterMiddlewarePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition(MiddlewareRegistry::class);

        $middlewares = $container->findTaggedServiceIds('radish.middleware');
        foreach ($middlewares as $id => $tags) {
            foreach ($tags as $tag) {
                // Add the middleware service to the registry
                $definition->addMethodCall('register', [$tag['middleware'], $id]);
            }
        }
    }
}
