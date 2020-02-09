<?php

namespace StraceOutUtils\Command;

use StraceOutUtils\Parser;
use StraceOutUtils\Processor\MysqlQueryResult;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Process extends Command
{
    private $processors = [];

    public function __construct()
    {
        $this->processors[] = new MysqlQueryResult;

        parent::__construct();
    }

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
            foreach ($this->processors as $processor) {
                $result = $processor->process($parts);

                if ($result) {
                    echo PHP_EOL;
                    echo ' -- Begin: HIT ---' . PHP_EOL;
                    echo $result . PHP_EOL;
                    echo ' -- End: HIT ---' . PHP_EOL;
                    echo PHP_EOL;
                }
            }

            echo $line;
        }

        return 0;
    }
}
