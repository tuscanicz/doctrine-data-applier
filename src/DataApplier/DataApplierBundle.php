<?php

namespace DataApplier;

use DataApplier\CompilerPass\DataApplierCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class DataApplierBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new DataApplierCompilerPass());
    }
}
