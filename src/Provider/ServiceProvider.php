<?php

namespace Trellis\StaticLdp\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Yaml\Yaml;
use Trellis\StaticLdp\Controller\ResourceController;
use Trellis\StaticLdp\TrellisConfiguration;

class ServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Container $app)
    {
        # Register the ResourceService
        $app['staticldp.resourcecontroller'] = function ($app) {
            return new ResourceController($app);
        };

        if (!isset($app['config'])) {
            $processor = new Processor();
            $confFile = $app['configDir'] . "/settings.yml";
            $userConf = file_exists($confFile) ? [Yaml::parse(file_get_contents($confFile))] : [];
            $app['config'] = $processor->processConfiguration(new TrellisConfiguration(), $userConf);
        }
    }
}
