<?php
/**
 * Copyright: IMPER.INFO Adrian Szuszkiewicz
 * Date: 26.09.2019
 * Time: 20:26
 */

namespace Imper86\SupervisorBundle\Service;


use Symfony\Component\Process\Process;

class Operator implements OperatorInterface
{
    /**
     * @var array
     */
    private $config;
    /**
     * @var string
     */
    private $workspace;

    public function __construct(array $config, string $workspace)
    {
        $this->config = $config;
        $this->workspace = $workspace;
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

        return "{$this->workspace}/{$instance}/supervisord.conf";
    }
}
