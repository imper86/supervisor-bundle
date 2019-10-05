<?php
/**
 * Copyright: IMPER.INFO Adrian Szuszkiewicz
 * Date: 26.09.2019
 * Time: 19:43
 */

namespace Imper86\SupervisorBundle\Service;


use Symfony\Component\Process\Process;

class ConfigGenerator implements ConfigGeneratorInterface
{
    /**
     * @var array
     */
    private $config;
    /**
     * @var string
     */
    private $workspace;
    /**
     * @var string
     */
    private $projectDir;

    public function __construct(array $config, string $workspace, string $projectDir)
    {
        $this->config = $config;
        $this->workspace = $workspace;
        $this->projectDir = $projectDir;
    }

    public function generate(string $instance): void
    {
        if (!isset($this->config['instances'][$instance])) {
            throw new \InvalidArgumentException("Instance {$instance} is not configured");
        }

        $rootDir = $this->workspace . "/{$instance}";

        Process::fromShellCommandline("rm -rf {$rootDir}/worker/*")->run();
        @mkdir("{$rootDir}/worker", 0755, true);
        @mkdir("{$rootDir}/logs", 0755, true);

        $this->prepareMainConfig($rootDir);
        $this->prepareWorkerConfigs($rootDir, $this->config['instances'][$instance]['commands']);
    }

    private function prepareMainConfig(string $rootDir): void
    {
        $config = <<<CONFIG
[unix_http_server]
file={$rootDir}/supervisor.sock
chmod=0700

[supervisord]
logfile={$rootDir}/logs/supervisord.log
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

    private function prepareWorkerConfigs(string $rootDir, array $commandsConfig): void
    {
        $executable = $this->projectDir . '/./bin/console';

        foreach ($commandsConfig as $commandConfig) {
            $v2s = function (bool $val): string {
                return $val ? 'true' : 'false';
            };

            if (empty($commandConfig['worker_name'])) {
                $commandConfig['worker_name'] = sha1($commandConfig['command']);
            }

            $config = <<<CONFIG
[program:{$commandConfig['worker_name']}]
command={$executable} {$commandConfig['command']}
process_name=%(program_name)s%(process_num)02d
numprocs={$commandConfig['numprocs']}
startsecs={$commandConfig['startsecs']}
autorestart={$v2s($commandConfig['autorestart'])}
stopsignal={$commandConfig['stopsignal']}
stopasgroup={$v2s($commandConfig['stopasgroup'])}
stopwaitsecs={$commandConfig['stopwaitsecs']}
stdout_logfile={$rootDir}/logs/{$commandConfig['worker_name']}_stdout.log
stderr_logfile={$rootDir}/logs/{$commandConfig['worker_name']}_stderr.log
CONFIG;

            file_put_contents("{$rootDir}/worker/{$commandConfig['worker_name']}.conf", $config);
        }
    }
}
