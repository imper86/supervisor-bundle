<?php
/**
 * Copyright: IMPER.INFO Adrian Szuszkiewicz
 * Date: 26.09.2019
 * Time: 19:43
 */

namespace Imper86\SupervisorBundle\Service;


use Imper86\SupervisorBundle\SupervisorParameter;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Process\Process;

class ConfigGenerator implements ConfigGeneratorInterface
{
    /**
     * @var array
     */
    private $config;
    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;

    public function __construct(array $config, ParameterBagInterface $parameterBag)
    {
        $this->config = $config;
        $this->parameterBag = $parameterBag;
    }

    public function generate(): void
    {
        $rootDir = $this->parameterBag->get(SupervisorParameter::WORKSPACE_DIRECTORY);

        Process::fromShellCommandline("rm -rf {$rootDir}/worker/*")->run();
        @mkdir("{$rootDir}/worker", 0755, true);
        @mkdir("{$rootDir}/logs", 0755, true);

        $this->prepareMainConfig($rootDir);
        $this->prepareWorkerConfigs($rootDir);
    }

    private function prepareMainConfig(string $rootDir): void
    {
        $config = <<<CONFIG
[unix_http_server]
file={$rootDir}/supervisor.sock
chmod=0700

[supervisord]
logfile={$rootDir}/supervisord.log
pidfile={$rootDir}/supervisor.pid

[rpcinterface:supervisor]
supervisor.rpcinterface_factory=supervisor.rpcinterface:make_main_rpcinterface

[supervisorctl]
serverurl=unix://{$rootDir}/supervisor.sock

[include]
files={$rootDir}/worker/*.conf
CONFIG;

        file_put_contents("{$rootDir}/supervisord.conf", $config);
    }

    private function prepareWorkerConfigs(string $rootDir): void
    {
        $executable = $this->parameterBag->get('kernel.project_dir') . '/./bin/console';

        foreach ($this->config['commands'] as $commandConfig) {
            $config = <<<CONFIG
[program:{$commandConfig['worker_name']}]
command={$executable} {$commandConfig['command']}
process_name=%(program_name)s%(process_num)02d
numprocs=1
startsecs=0
autorestart=true
stopsignal=INT
stopasgroup=true
stopwaitsecs=60
stdout_logfile={$rootDir}/logs/{$commandConfig['worker_name']}_stdout.log
stderr_logfile={$rootDir}/logs/{$commandConfig['worker_name']}_stderr.log
CONFIG;

            file_put_contents("{$rootDir}/worker/{$commandConfig['worker_name']}.conf", $config);
        }
    }
}
