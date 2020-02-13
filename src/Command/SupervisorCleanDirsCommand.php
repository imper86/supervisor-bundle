<?php
/**
 * Copyright: IMPER.INFO Adrian Szuszkiewicz
 * Date: 02.10.2019
 * Time: 17:46
 */

namespace Imper86\SupervisorBundle\Command;


use Imper86\SupervisorBundle\Helper\ProcessHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;

class SupervisorCleanDirsCommand extends Command
{
    public static $defaultName = 'i86:supervisor:clean:dirs';

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
        parent::__construct(self::$defaultName);
        $this->config = $config;
        $this->workspace = $workspace;
    }

    protected function configure()
    {
        $this->setDescription('Removes every directory in workspace, which is not currently configured');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $finder = new Finder();
        $finder->directories()->depth(0)->in($this->workspace);

        foreach ($finder->getIterator() as $info) {
            if (!isset($this->config['instances'][$info->getFilename()])) {
                $dirsToRemove[] = $info->getPathname();
            }
        }

        if (!empty($dirsToRemove)) {
            foreach ($dirsToRemove as $dir) {
                if ('yes' === $io->ask("Do you really want to remove {$dir}?", 'yes')) {
                    ProcessHelper::generate("rm -rf {$dir}")->run();

                    $io->warning("Removed {$dir}");
                }
            }
        }

        return 0;
    }
}
