<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Console\Command;

use Magento\Framework\App\MaintenanceMode;
use Magento\Framework\Module\ModuleList;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for setting allowed IPs in maintenance mode
 */
class MaintenanceAllowIpsCommand extends AbstractSetupCommand
{
    /**
     * Names of input arguments or options
     */
    const INPUT_KEY_IP = 'ip';
    const INPUT_KEY_NONE = 'none';

    /**
     * @var MaintenanceMode $maintenanceMode
     */
    private $maintenanceMode;

    /**
     * Constructor
     *
     * @param MaintenanceMode $maintenanceMode
     */
    public function __construct(MaintenanceMode $maintenanceMode)
    {
        $this->maintenanceMode = $maintenanceMode;
        parent::__construct();
    }

    /**
     * Initialization of the command
     *
     * @return void
     */
    protected function configure()
    {
        $arguments = [
            new InputArgument(
                self::INPUT_KEY_IP,
                InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
                'Allowed IP addresses'
            ),
        ];
        $options = [
            new InputOption(
                self::INPUT_KEY_NONE,
                null,
                InputOption::VALUE_NONE,
                'Clear allowed IP addresses'
            ),
        ];
        $this->setName('maintenance:allow-ips')
            ->setDescription('Sets maintenance mode exempt IPs')
            ->setDefinition(array_merge($arguments, $options));
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getOption(self::INPUT_KEY_NONE)) {
            $addresses = $input->getArgument(self::INPUT_KEY_IP);
            if (!empty($addresses)) {
                $this->maintenanceMode->setAddresses(implode(',', $addresses));
                $output->writeln(
                    '<info>Set exempt IP-addresses: ' . implode(', ', $this->maintenanceMode->getAddressInfo()) .
                    '</info>'
                );
            }
        } else {
            $this->maintenanceMode->setAddresses('');
            $output->writeln('<info>Set exempt IP-addresses: none</info>');
        }
    }
}
