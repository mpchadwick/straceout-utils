<?php

namespace StraceOutUtils\Command;

use StraceOutUtils\Parser;
use StraceOutUtils\Processor\MysqlQueryResult;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
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
            ->setDescription('Process the strace out')
            ->addArgument('file', InputArgument::REQUIRED, 'File with strace out');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $parser = new Parser;
        $fp = fopen($input->getArgument('file'), 'r');
        if (!$fp) {
            throw new \Exception('Could not open file');
        }

        while (($line = fgets($fp)) !== false) {
            $parts = $parser->parse($line);
            foreach ($this->processors as $processor) {
                $result = $processor->process($parts);

                if ($result) {
                    $output->writeln('-- Begin -- ' . get_class($processor));
                    $output->writeln($result);
                    $output->writeln('-- End -- ' . get_class($processor));
                }
            }

            $output->writeln($parts['line']);
        }

        return 0;
    }
}
