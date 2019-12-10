<?php

namespace PhpSolution\FunctionalTest\PHPUnit\Listener;

use PhpSolution\FunctionalTest\TestCase\ConsoleTestCase;
use PHPUnit\Framework\TestListener;
use PHPUnit\Framework\TestListenerDefaultImplementation;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Runner\TestHook;

/**
 * CommandLauncher
 */
class CommandLauncher implements TestHook, TestListener
{
    use TestListenerDefaultImplementation;

    /**
     * @var bool
     */
    private $wasCalled = false;

    /**
     * @var string
     */
    private $command;

    /**
     * @var array
     */
    private $parameters;

    /**
     * @param string $command
     * @param array  $parameters
     */
    public function __construct()
    {
        $argv = func_get_args();
        if (\count($argv) === 2 && \is_array($argv[1])) {
            [$command, $parameters] = $argv;
        } else {
            $command = $argv[0];
            $parameters = [];
            foreach (array_flip(\array_slice($argv, 1)) as $key => $value) {
                $data = explode('=', $key);
                if (\count($data) === 2) {
                    $parameters[$data[0]] = $data[1];
                    continue;
                }
                $parameters[$key] = $value;
            }
        }

        $this->command = $command;
        $this->parameters = $parameters;
    }

    /**
     * @param TestSuite $suite
     *
     * @throws \Exception
     */
    public function startTestSuite(TestSuite $suite): void
    {
        if ($this->wasCalled) {
            return;
        }
        $this->wasCalled = true;

        // By default, set output verbosity - quiet
        if (0 === count(array_intersect(array_keys($this->parameters), ['-q', '-v', '-vv', '-vvv']))) {
            $this->parameters['-q'] = true;
        }

        print ConsoleTestCase::runConsoleCommand($this->command, $this->parameters)->fetch();
    }
}
