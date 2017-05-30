<?php

namespace DataApplier\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class DataApplierCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $definition = $container->findDefinition('data_applier.command.data_applier_handler');
        $taggedServices = $container->findTaggedServiceIds('doctrine.data_applier');
        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall('addTaggedDataApplier', [new Reference($id)]);
        }
    }
}
