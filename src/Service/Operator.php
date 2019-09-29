<?php
/**
 * Copyright: IMPER.INFO Adrian Szuszkiewicz
 * Date: 26.09.2019
 * Time: 20:26
 */

namespace Imper86\SupervisorBundle\Service;


use Imper86\SupervisorBundle\SupervisorParameter;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Process\Process;

class Operator implements OperatorInterface
{
    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;
    /**
     * @var string
     */
    private $configurationPath;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->parameterBag = $parameterBag;
        $this->configurationPath = $parameterBag->get(SupervisorParameter::WORKSPACE_DIRECTORY) . '/supervisord.conf';
    }

    public function stop(): void
    {
        $pidProcess = Process::fromShellCommandline("supervisorctl --configuration={$this->configurationPath} pid");
        $pidProcess->run();

        if ($pidProcess->isSuccessful()) {
            $pid = $pidProcess->getOutput();

            $killProcess = Process::fromShellCommandline("kill {$pid}");
            $killProcess->run();
        }
    }

    public function start(): void
    {
        $startProcess = Process::fromShellCommandline("supervisord --configuration={$this->configurationPath}");
        $startProcess->run();
    }

    public function status(): string
    {
        $statusProcess = Process::fromShellCommandline("supervisorctl --configuration={$this->configurationPath} status");
        $statusProcess->run();

        return $statusProcess->isSuccessful() ? $statusProcess->getOutput() : $statusProcess->getErrorOutput();
    }

}
