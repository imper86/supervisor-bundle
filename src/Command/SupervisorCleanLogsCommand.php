<?php
/**
 * Copyright: IMPER.INFO Adrian Szuszkiewicz
 * Date: 02.10.2019
 * Time: 18:01
 */

namespace Imper86\SupervisorBundle\Command;


use Imper86\SupervisorBundle\Helper\ProcessHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SupervisorCleanLogsCommand extends Command
{
    public static $defaultName = 'i86:supervisor:clean:logs';

    /**
     * @var array
     */
    private $config;
    /**
     * @var string
     */
    private $workspace;

    public function __construct($config, string $workspace)
    {
        parent::__construct(self::$defaultName);
        $this->config = $config;
        $this->workspace = $workspace;
    }

    protected function configure()
    {
        $this->setDescription("Removes all log files");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        foreach (array_keys($this->config['instances'] ?? []) as $instance) {
            ProcessHelper::generate("rm {$this->workspace}/{$instance}/logs/*")->run();
            ProcessHelper::generate("rm {$this->workspace}/{$instance}/supervisord.log")->run();
        }

        $io->success('Removed all log files');

        return 0;
    }
}
