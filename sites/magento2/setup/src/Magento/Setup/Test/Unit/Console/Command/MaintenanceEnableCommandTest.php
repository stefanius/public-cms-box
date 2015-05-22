<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Console\Command;

use Magento\Setup\Console\Command\MaintenanceEnableCommand;
use Symfony\Component\Console\Tester\CommandTester;

class MaintenanceEnableCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\MaintenanceMode|\PHPUnit_Framework_MockObject_MockObject
     */
    private $maintenanceMode;

    /**
     * @var MaintenanceEnableCommand
     */
    private $command;

    public function setUp()
    {
        $this->maintenanceMode = $this->getMock('Magento\Framework\App\MaintenanceMode', [], [], '', false);
        $this->command = new MaintenanceEnableCommand($this->maintenanceMode);
    }

    /**
     * @param array $input
     * @param string $expectedMessage
     * @dataProvider executeDataProvider
     */
    public function testExecute(array $input, $expectedMessage)
    {
        $return = isset($input['--ip']) ? ($input['--ip'] !== ['none'] ? $input['--ip'] : []) : [];
        $this->maintenanceMode
            ->expects($this->any())
            ->method('getAddressInfo')
            ->willReturn($return);
        $tester = new CommandTester($this->command);
        $tester->execute($input);
        $this->assertEquals($expectedMessage, $tester->getDisplay());

    }

    /**
     * return array
     */
    public function executeDataProvider()
    {
        return [
            [
                ['--ip' => ['127.0.0.1', '127.0.0.2']],
                'Enabled maintenance mode' . PHP_EOL .
                'Set exempt IP-addresses: 127.0.0.1, 127.0.0.2' . PHP_EOL
            ],
            [
                ['--ip' => ['none']],
                'Enabled maintenance mode' . PHP_EOL .
                'Set exempt IP-addresses: none' . PHP_EOL
            ],
            [
                [],
                'Enabled maintenance mode' . PHP_EOL
            ],
        ];
    }
}
