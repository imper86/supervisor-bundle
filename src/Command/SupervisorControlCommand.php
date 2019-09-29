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
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SupervisorControlCommand extends Command
{
    public static $defaultName = 'imper86:supervisor:control';
    /**
     * @var OperatorInterface
     */
    private $operator;

    public function __construct(OperatorInterface $operator)
    {
        parent::__construct(self::$defaultName);
        $this->operator = $operator;
    }

    protected function configure()
    {
        $this->setDescription('Control your supervisor process')
            ->addArgument('type', InputArgument::REQUIRED, 'operation type: start, stop or status');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $type = $input->getArgument('type');

        switch ($type) {
            case 'start':
                $this->operator->start();
                $io->success('Process started');
                break;
            case 'stop':
                $this->operator->stop();
                $io->success('Process killed');
                break;
            case 'status':
                $io->writeln($this->operator->status());
                break;
            default:
                $io->error('Wrong type argument');
        }
    }
}
