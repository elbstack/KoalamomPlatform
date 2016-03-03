<?php

namespace Koalamon\Bundle\ConsoleBundle\Command;

use Koalamon\Bundle\IncidentDashboardBundle\Entity\Event;
use Koalamon\Bundle\IncidentDashboardBundle\Entity\EventIdentifier;
use Koalamon\Bundle\IncidentDashboardBundle\Util\ProjectHelper;
use Koalamon\Component\Command\CheckToolIntervalCommandInterface;
use Koalamon\Component\Command\ToolToCheckIntervalForInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FixBrokenEventIdentifierCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('koalamon:project:eventidentifier:fix')
            ->setDescription('Repairs the event identifiers.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("\n  <info>Fixing event identifiers ...</info>\n");

        $projects = $this->getContainer()
            ->get('doctrine')
            ->getRepository('KoalamonIncidentDashboardBundle:Project')
            ->findAll();

        $removedIdentifiers = 0;

        foreach ($projects as $project) {
            $eventIdentifiers = $project->getEventIdentifiers();

            foreach ($eventIdentifiers as $eventIdentifier) {
                $event = $eventIdentifier->getLastEvent();
                try {
                    $event->getStatus();
                } catch (\Exception $e) {
                    $this->getContainer()
                        ->get('doctrine')
                        ->getManager()
                        ->remove($eventIdentifier);
                    $removedIdentifiers++;
                }
            }
            $this->getContainer()
                ->get('doctrine')
                ->getManager()
                ->flush();
        }

        $output->writeln("\n  <info>Done</info> " . $removedIdentifiers . " identifiers(s) deleted.\n");
    }
}
