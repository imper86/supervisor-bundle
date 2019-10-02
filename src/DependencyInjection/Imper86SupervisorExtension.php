<?php
/**
 * Copyright: IMPER.INFO Adrian Szuszkiewicz
 * Date: 26.09.2019
 * Time: 19:36
 */

namespace Imper86\SupervisorBundle\DependencyInjection;


use Imper86\SupervisorBundle\Command\SupervisorCleanDirsCommand;
use Imper86\SupervisorBundle\Command\SupervisorCleanLogsCommand;
use Imper86\SupervisorBundle\Command\SupervisorControlCommand;
use Imper86\SupervisorBundle\Command\SupervisorRebuildCommand;
use Imper86\SupervisorBundle\Service\ConfigGeneratorInterface;
use Imper86\SupervisorBundle\Service\OperatorInterface;
use Imper86\SupervisorBundle\SupervisorParameter;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class Imper86SupervisorExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter(SupervisorParameter::WORKSPACE_DIRECTORY, $config['workspace_directory']);

        $injectConfigServices = [
            ConfigGeneratorInterface::class,
            OperatorInterface::class,
            SupervisorRebuildCommand::class,
            SupervisorControlCommand::class,
            SupervisorCleanDirsCommand::class,
            SupervisorCleanLogsCommand::class,
        ];

        foreach ($injectConfigServices as $service) {
            $container->getDefinition($service)->setArgument(0, $config);
        }
    }

}
