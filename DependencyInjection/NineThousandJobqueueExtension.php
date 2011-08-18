<?php

namespace NineThousand\Bundle\NineThousandJobqueueBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;

class NineThousandJobqueueExtension extends Extension
{

    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('jobqueue.xml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        
        $container->setParameter('jobqueue.job.class', $config['job']['class']);
        $container->setParameter('jobqueue.control.class', $config['control']['class']);
        $container->setParameter('jobqueue.adapter.class', $config['adapter']['class']);
        $container->setParameter('jobqueue.adapter.options', $config['adapter']['options']);
        $container->setParameter('jobqueue.ui.options', $config['ui']);
        
        
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
        return 'http://www.ninethousand.org/schema/dic/jobqueue';
    }

}
