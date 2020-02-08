<?php

namespace StraceOutUtils\Command;

use Symfony\Component\Console\Command\Command;

class Process extends Command
{
    protected function configure()
    {
        parent::configure();
        $this
            ->setName('process')
            ->setDescription('Process the strace out');
    }
}
