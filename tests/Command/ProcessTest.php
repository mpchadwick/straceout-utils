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
            'file' => 'tests/fixtures/recvfrom-x-before-1.txt'
        ]);
        $output = $commandTester->getDisplay();
        $this->assertEquals(
            $output,
            file_get_contents('tests/fixtures/recvfrom-x-after-1.txt')
        );

        $commandTester->execute([
            'file' => 'tests/fixtures/recvfrom-x-before-2.txt'
        ]);
        $output = $commandTester->getDisplay();
        $this->assertEquals(
            $output,
            file_get_contents('tests/fixtures/recvfrom-x-after-2.txt')
        );

        // Test processing query results with NULL values
        $commandTester->execute([
            'file' => 'tests/fixtures/recvfrom-x-before-3.txt'
        ]);
        $output = $commandTester->getDisplay();
        $this->assertEquals(
            $output,
            file_get_contents('tests/fixtures/recvfrom-x-after-3.txt')
        );

        // Test processing query results with no rows
        $commandTester->execute([
            'file' => 'tests/fixtures/recvfrom-x-before-4.txt'
        ]);
        $output = $commandTester->getDisplay();
        $this->assertEquals(
            $output,
            file_get_contents('tests/fixtures/recvfrom-x-after-4.txt')
        );

        $commandTester->execute([
            'file' => 'tests/fixtures/recvfrom-x-before-5.txt'
        ]);
        $output = $commandTester->getDisplay();
        $this->assertEquals(
            $output,
            file_get_contents('tests/fixtures/recvfrom-x-after-5.txt')
        );
    }

    public function testExecuteWithWrongFile()
    {
        $this->expectException(InvalidArgumentException::class);
        $application = new Application;
        $application->add(new Command\Process());
        $command = $application->find('process');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'file' => 'I DONT EXIST'
        ]);
        $commandTester->getDisplay();
    }
}
