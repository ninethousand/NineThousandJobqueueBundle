<?php

namespace NineThousand\Bundle\NineThousandJobqueueBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;

class NineThousandJobqueueExtension extends Extension
{

    public function load(Array $config, ContainerBuilder $container)
    {
        if (! $container->hasDefinition('jobqueue')) {
            $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
            $loader->load('jobqueue.xml');
        } 
        
        $this->mergeExternalConfig($config, $container, 'jobqueue');
        
    }

    private function mergeExternalConfig($config, ContainerBuilder $container, $alias)
    {
        $mergedConfig = array();

        foreach ($config as $cnf)
        {
            $mergedConfig = array_merge($mergedConfig, $cnf);
        }
        
        if (isset($mergedConfig['adapter']['options']))
        {
            $container->setParameter($alias.'.adapter.options', $mergedConfig['adapter']['options']);
        }
        
        if (isset($mergedConfig['control']['class']))
        {
            $container->setParameter($alias.'.control.class', $mergedConfig['control']['class']);
        }
        
        if (isset($mergedConfig['job']['class']))
        {
            $container->setParameter($alias.'.job.class', $mergedConfig['job']['class']);
        }
        
        if (isset($mergedConfig['adapter']['class']))
        {
            $container->setParameter($alias.'.adapter.class', $mergedConfig['adapter']['class']);
        }
        
        if (isset($mergedConfig['ui']['options']))
        {
            $container->setParameter($alias.'.ui.options', $mergedConfig['ui']['options']);
        }
    }

    /**
     * Returns the base path for the XSD files.
     *
     * @return string The XSD base path
     */
    public function getXsdValidationBasePath()
    {
        return __DIR__.'/../Resources/config/schema';
    }

    public function getNamespace()
    {
        return 'http://www.collegedegrees.com/schema/dic/rank-tracker';
    }

}
