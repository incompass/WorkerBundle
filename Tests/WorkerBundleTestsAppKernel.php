<?php

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Incompass\TimestampableBundle\TimestampableBundle;
use Incompass\WorkerBundle\WorkerBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Class AppKernel
 *
 * @package Incompass\Tests
 * @author  Joe Mizzi <joe@casechek.com>
 */
class WorkerBundleTestsAppKernel extends Kernel
{
    /**
     * @return array
     */
    public function registerBundles()
    {
        $bundles = [
            // Dependencies
            new FrameworkBundle(),
            new DoctrineBundle(),
            new WorkerBundle(),
            new TimestampableBundle()
        ];

        return $bundles;
    }

    /**
     * @param LoaderInterface $loader
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        // We don't need that Environment stuff, just one config
        $loader->load(__DIR__.'/config.yml');
    }

    /**
     * @return string
     */
    public function getCacheDir()
    {
        return dirname(__DIR__).'/var/'.$this->environment.'/cache';
    }

    /**
     * @return string
     */
    public function getLogDir()
    {
        return dirname(__DIR__).'/var/'.$this->environment.'/logs';
    }
}