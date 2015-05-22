<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Console\Command;

use Magento\Setup\Model\AdminAccount;
use Magento\Setup\Console\Command\AdminUserCreateCommand;
use Magento\Setup\Mvc\Bootstrap\InitParamListener;
use Symfony\Component\Console\Tester\CommandTester;

class AdminUserCreateCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Model\InstallerFactory
     */
    private $installerFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|AdminUserCreateCommand
     */
    private $command;

    public function setUp()
    {
        $this->installerFactoryMock = $this->getMock('Magento\Setup\Model\InstallerFactory', [], [], '', false);
        $this->command = new AdminUserCreateCommand($this->installerFactoryMock);
    }

    public function testExecute()
    {
        $options = [
            '--' . AdminAccount::KEY_USER => 'user',
            '--' . AdminAccount::KEY_PASSWORD => '123123q',
            '--' . AdminAccount::KEY_EMAIL => 'test@test.com',
            '--' . AdminAccount::KEY_FIRST_NAME => 'John',
            '--' . AdminAccount::KEY_LAST_NAME => 'Doe',
        ];
        $data = [
            AdminAccount::KEY_USER => 'user',
            AdminAccount::KEY_PASSWORD => '123123q',
            AdminAccount::KEY_EMAIL => 'test@test.com',
            AdminAccount::KEY_FIRST_NAME => 'John',
            AdminAccount::KEY_LAST_NAME => 'Doe',
            InitParamListener::BOOTSTRAP_PARAM => null,
        ];
        $commandTester = new CommandTester($this->command);
        $installerMock = $this->getMock('Magento\Setup\Model\Installer', [], [], '', false);
        $installerMock->expects($this->once())->method('installAdminUser')->with($data);
        $this->installerFactoryMock->expects($this->once())->method('create')->willReturn($installerMock);
        $commandTester->execute($options);
        $this->assertEquals('Created admin user user' . PHP_EOL, $commandTester->getDisplay());
    }

    public function testGetOptionsList()
    {
        /* @var $argsList \Symfony\Component\Console\Input\InputArgument[] */
        $argsList = $this->command->getOptionsList();
        $this->assertEquals(AdminAccount::KEY_EMAIL, $argsList[2]->getName());
    }

    /**
     * @dataProvider validateDataProvider
     * @param bool[] $options
     * @param string[] $errors
     */
    public function testValidate(array $options, array $errors)
    {
        $inputMock = $this->getMockForAbstractClass('Symfony\Component\Console\Input\InputInterface', [], '', false);
        $index = 0;
        foreach ($options as $option) {
            $inputMock->expects($this->at($index++))->method('getOption')->willReturn($option);
        }
        $this->assertEquals($errors, $this->command->validate($inputMock));
    }

    /**
     * @return array
     */
    public function validateDataProvider()
    {
        return [
            [[false, true, true, true, true], ['Missing option ' . AdminAccount::KEY_USER]],
            [
                [true, false, false, true, true],
                ['Missing option ' . AdminAccount::KEY_PASSWORD, 'Missing option ' . AdminAccount::KEY_EMAIL],
            ],
            [[true, true, true, true, true], []],
        ];
    }
}
