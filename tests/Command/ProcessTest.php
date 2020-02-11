<?php

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use StraceOutUtils\Command;
use PHPUnit\Framework\TestCase;

class ProcessTest extends TestCase
{
    public function testExecute()
    {
        $application = new Application;
        $application->add(new Command\Process());
        $command = $application->find('process');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'file' => 'tests/fixtures/brk-before.txt'
        ]);
        $output = $commandTester->getDisplay();
        $this->assertEquals(
            $output,
            file_get_contents('tests/fixtures/brk-after.txt')
        );

        $commandTester->execute([
            'file' => 'tests/fixtures/recvfrom-x-before.txt'
        ]);
        $output = $commandTester->getDisplay();
        $this->assertEquals(
            $output,
            file_get_contents('tests/fixtures/recvfrom-x-after.txt')
        );
    }
}
