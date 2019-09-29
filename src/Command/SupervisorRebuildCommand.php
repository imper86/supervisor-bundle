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
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SupervisorRebuildCommand extends Command
{
    public static $defaultName = 'imper86:supervisor:rebuild';
    /**
     * @var ConfigGeneratorInterface
     */
    private $configGenerator;
    /**
     * @var OperatorInterface
     */
    private $operator;

    public function __construct(ConfigGeneratorInterface $configGenerator, OperatorInterface $operator)
    {
        parent::__construct(self::$defaultName);
        $this->configGenerator = $configGenerator;
        $this->operator = $operator;
    }

    protected function configure()
    {
        $this->setDescription('Overwrites current configuration files with new ones');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $this->operator->stop();

        $io->success('Stopped running process');

        $this->configGenerator->generate();

        $io->success('Generated new config files');
    }
}
