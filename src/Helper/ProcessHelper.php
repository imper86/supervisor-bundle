<?php
/**
 * Author: Adrian Szuszkiewicz <me@imper.info>
 * Github: https://github.com/imper86
 * Date: 05.10.2019
 * Time: 14:38
 */

namespace Imper86\SupervisorBundle\Helper;


use Symfony\Component\Process\Process;

class ProcessHelper
{
    public static function generate(string $command): Process
    {
        if (method_exists(Process::class, 'fromShellCommandline')) {
            return Process::fromShellCommandline($command);
        }

        return new Process($command);
    }
}
