<?php
/**
 * Copyright: IMPER.INFO Adrian Szuszkiewicz
 * Date: 26.09.2019
 * Time: 20:26
 */

namespace Imper86\SupervisorBundle\Service;


use Imper86\SupervisorBundle\SupervisorParameter;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Process\Process;

class Operator implements OperatorInterface
{
    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;
    private $config;

    public function __construct($config, ParameterBagInterface $parameterBag)
    {
        $this->parameterBag = $parameterBag;
        $this->config = $config;
    }

    public function stop(string $instance): void
    {
        $configPath = $this->getConfigurationPath($instance);
        $pidProcess = Process::fromShellCommandline("supervisorctl --configuration={$configPath} pid");
        $pidProcess->run();

        if ($pidProcess->isSuccessful()) {
            $pid = $pidProcess->getOutput();

            $killProcess = Process::fromShellCommandline("kill {$pid}");
            $killProcess->run();
        }
    }

    public function start(string $instance): void
    {
        $this->stop($instance);

        $configPath = $this->getConfigurationPath($instance);
        $startProcess = Process::fromShellCommandline("supervisord --configuration={$configPath}");
        $startProcess->run();
    }

    public function restart(string $instance): void
    {
        $this->start($instance);
    }

    public function status(string $instance): ?string
    {
        $configPath = $this->getConfigurationPath($instance);
        $statusProcess = Process::fromShellCommandline("supervisorctl --configuration={$configPath} status");
        $statusProcess->run();

        return $statusProcess->isSuccessful() ? $statusProcess->getOutput() : $statusProcess->getErrorOutput();
    }

    private function getConfigurationPath(string $instance): string
    {
        if (!isset($this->config['instances'][$instance])) {
            throw new \InvalidArgumentException("Instance {$instance} is not defined");
        }

        return $this->parameterBag->get(SupervisorParameter::WORKSPACE_DIRECTORY) . "/{$instance}/supervisord.conf";
    }
}
