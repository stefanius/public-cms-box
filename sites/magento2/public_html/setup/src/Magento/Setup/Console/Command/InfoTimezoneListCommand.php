<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Magento\Setup\Model\Lists;

/**
 * Command prints list of available timezones
 */
class InfoTimezoneListCommand extends Command
{
    /**
     * List model provides lists of available options for currency, language locales, timezones
     *
     * @var Lists
     */
    private $lists;

    /**
     * @param Lists $lists
     */
    public function __construct(Lists $lists)
    {
        $this->lists = $lists;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('info:timezone:list')
            ->setDescription('Prints list of available timezones');

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->lists->getTimezoneList() as $key => $timezone) {
            $output->writeln($key . ' => ' . $timezone);
        }
    }
}
