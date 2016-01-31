<?php
/**
 * Created by IntelliJ IDEA.
 * User: jan.schumann
 * Date: 01.12.15
 * Time: 11:03
 */

namespace Koalamon\Bundle\Bundle\ConsoleBundle\Command;

use Koalamon\Bundle\IncidentDashboardBundle\Entity\Project;
use Koalamon\Bundle\IncidentDashboardBundle\Entity\System;
use Koalamon\Bundle\IncidentDashboardBundle\Entity\Tool;
use Koalamon\Bundle\IncidentDashboardBundle\Entity\User;
use Doctrine\Bundle\FixturesBundle\Command\LoadDataFixturesDoctrineCommand;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class LoadFixturesCommand extends LoadDataFixturesDoctrineCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('koalamon:fixtures:load');
    }
}
