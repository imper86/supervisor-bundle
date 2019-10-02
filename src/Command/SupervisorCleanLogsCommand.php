<?php
/**
 * Copyright: IMPER.INFO Adrian Szuszkiewicz
 * Date: 02.10.2019
 * Time: 18:01
 */

namespace Imper86\SupervisorBundle\Command;


use Imper86\SupervisorBundle\SupervisorParameter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Process\Process;

class SupervisorCleanLogsCommand extends Command
{
    public static $defaultName = 'i86:supervisor:clean:logs';
    private $config;
    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;

    public function __construct($config, ParameterBagInterface $parameterBag)
    {
        parent::__construct(self::$defaultName);
        $this->config = $config;
        $this->parameterBag = $parameterBag;
    }

    protected function configure()
    {
        $this->setDescription("Removes all log files");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $workspaceDir = $this->parameterBag->get(SupervisorParameter::WORKSPACE_DIRECTORY);

        foreach (array_keys($this->config['instances'] ?? []) as $instance) {
            Process::fromShellCommandline("rm {$workspaceDir}/{$instance}/logs/*")->run();
            Process::fromShellCommandline("rm {$workspaceDir}/{$instance}/supervisord.log")->run();
        }

        $io->success('Removed all log files');
    }
}
