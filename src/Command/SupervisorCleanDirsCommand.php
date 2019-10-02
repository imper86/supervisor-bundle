<?php
/**
 * Copyright: IMPER.INFO Adrian Szuszkiewicz
 * Date: 02.10.2019
 * Time: 17:46
 */

namespace Imper86\SupervisorBundle\Command;


use Imper86\SupervisorBundle\SupervisorParameter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;

class SupervisorCleanDirsCommand extends Command
{
    public static $defaultName = 'i86:supervisor:clean:dirs';
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
        $this->setDescription('Removes every directory in workspace, whis is not in instances configuration');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $workspaceDir = $this->parameterBag->get(SupervisorParameter::WORKSPACE_DIRECTORY);
        $finder = new Finder();
        $finder->directories()->depth(0)->in($workspaceDir);

        foreach ($finder->getIterator() as $info) {
            if (!isset($this->config['instances'][$info->getFilename()])) {
                $dirsToRemove[] = $info->getPathname();
            }
        }

        if (!empty($dirsToRemove)) {
            foreach ($dirsToRemove as $dir) {
                if ('yes' === $io->ask("Do you really want to remove {$dir}?", 'yes')) {
                    Process::fromShellCommandline("rm -rf {$dir}")->run();
                    $io->warning("Removed {$dir}");
                }
            }
        }
    }
}
