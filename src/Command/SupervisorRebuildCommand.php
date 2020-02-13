<?php
/**
 * Copyright: IMPER.INFO Adrian Szuszkiewicz
 * Date: 26.09.2019
 * Time: 20:39
 */

namespace Imper86\SupervisorBundle\Command;


use Imper86\SupervisorBundle\Service\ConfigGeneratorInterface;
use Imper86\SupervisorBundle\Service\OperatorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SupervisorRebuildCommand extends Command
{
    public static $defaultName = 'i86:supervisor:rebuild';
    /**
     * @var ConfigGeneratorInterface
     */
    private $configGenerator;
    /**
     * @var OperatorInterface
     */
    private $operator;
    private $config;

    public function __construct($config, ConfigGeneratorInterface $configGenerator, OperatorInterface $operator)
    {
        parent::__construct(self::$defaultName);
        $this->configGenerator = $configGenerator;
        $this->operator = $operator;
        $this->config = $config;
    }

    protected function configure()
    {
        $this->setDescription('Overwrites current configuration files with new ones')
            ->addOption(
                'instance',
                'i',
                InputOption::VALUE_OPTIONAL|InputOption::VALUE_IS_ARRAY,
                'limit to only chosen instances',
                []
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $instances = $input->getOption('instance');

        if (empty($instances)) {
            $instances = array_keys($this->config['instances'] ?? []);
        }

        foreach ($instances as $instance) {
            $this->operator->stop($instance);
            $io->warning("[{$instance}] Stopped workers");

            $this->configGenerator->generate($instance);
            $io->success("[{$instance}] Generated new config files");

            if ('yes' === $io->ask('Do you want to start workers?', 'yes')) {
                $this->operator->start($instance);
                $io->success("[{$instance}] Started workers");
            }
        }

        return 0;
    }
}
