<?php
/**
 * Copyright: IMPER.INFO Adrian Szuszkiewicz
 * Date: 26.09.2019
 * Time: 20:25
 */

namespace Imper86\SupervisorBundle\Service;


interface OperatorInterface
{
    public function stop(string $instance): void;

    public function start(string $instance): void;

    public function restart(string $instance): void;

    public function status(string $instance): ?string;
}
