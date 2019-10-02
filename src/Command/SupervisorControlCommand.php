<?php
/**
 * Copyright: IMPER.INFO Adrian Szuszkiewicz
 * Date: 26.09.2019
 * Time: 20:45
 */

namespace Imper86\SupervisorBundle\Command;


use Imper86\SupervisorBundle\Service\OperatorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SupervisorControlCommand extends Command
{
    public static $defaultName = 'i86:supervisor:control';
    /**
     * @var OperatorInterface
     */
    private $operator;
    private $config;

    public function __construct($config, OperatorInterface $operator)
    {
        parent::__construct(self::$defaultName);
        $this->operator = $operator;
        $this->config = $config;
    }

    protected function configure()
    {
        $this->setDescription('Control your supervisor process')
            ->addArgument('type', InputArgument::REQUIRED, 'operation type: start, stop or status')
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
        $type = $input->getArgument('type');
        $instances = $input->getOption('instance');

        if (empty($instances)) {
            $instances = array_keys($this->config['instances'] ?? []);
        }

        foreach ($instances as $instance) {
            switch ($type) {
                case 'restart':
                case 'start':
                    $this->operator->start($instance);
                    $io->success("[{$instance}] Process restarted");
                    break;
                case 'stop':
                    $this->operator->stop($instance);
                    $io->success("[{$instance}] Process killed");
                    break;
                case 'status':
                    $statusText = $this->operator->status($instance);

                    if (null === $statusText) {
                        $io->warning("Instance [{$instance}] is not running");
                    } else {
                        $io->writeln($statusText);
                    }
                    break;
                default:
                    $io->error('Wrong type argument');
            }
        }
    }
}
