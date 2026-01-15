<?php

namespace App\FactureYaTimbradoBundle;

use App\FactureYaTimbradoBundle\DependencyInjection\Compiler\SoapClientCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class FactureYaTimbradoBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
        $container->addCompilerPass(new SoapClientCompilerPass());
    }
    
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
