<?php

namespace StraceOutUtils\Command;

use StraceOutUtils\Parser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Process extends Command
{
    protected function configure()
    {
        parent::configure();
        $this
            ->setName('process')
            ->setDescription('Process the strace out');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $parser = new Parser;
        while ($line = fgets(STDIN)) {
            $parts = $parser->parse($line);
        }
        return 0;
    }
}
