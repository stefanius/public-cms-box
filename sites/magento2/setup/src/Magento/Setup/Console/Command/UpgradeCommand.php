<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Setup\Model\InstallerFactory;
use Magento\Setup\Model\ConsoleLogger;

/**
 * Command for updating installed application after the code base has changed
 */
class UpgradeCommand extends AbstractSetupCommand
{
    /**
     * Installer service factory
     *
     * @var InstallerFactory
     */
    private $installerFactory;

    /**
     * Constructor
     *
     * @param InstallerFactory $installerFactory
     */
    public function __construct(InstallerFactory $installerFactory)
    {
        $this->installerFactory = $installerFactory;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('setup:upgrade')
            ->setDescription(
                'Upgrades installed application after the code base has changed, '
                . 'including DB schema and data'
            );
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $installer = $this->installerFactory->create(new ConsoleLogger($output));
        $installer->updateModulesSequence();
        $installer->installSchema();
        $installer->installDataFixtures();
    }
}
