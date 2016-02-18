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

class FixIncidentCountCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('koalamon:project:incidentcount:fix')
            ->setDescription('Resets the incident counts.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("\n  <info>Fixing incident count ...</info>\n");

        $projects = $this->getContainer()
            ->get('doctrine')
            ->getRepository('KoalamonIncidentDashboardBundle:Project')
            ->findAll();

        $fixedProjetcs = 0;

        foreach ($projects as $project) {
            $eventIdentifiers = $project->getEventIdentifiers();
            $incidentCount = 0;
            foreach ($eventIdentifiers as $eventIdentifier) {
                if (!$eventIdentifier->isKnownIssue() && $eventIdentifier->getLastEvent()->getStatus() == Event::STATUS_FAILURE) {
                    $incidentCount++;
                }
            }

            if ($project->getOpenIncidentCount() != $incidentCount) {
                $project->setOpenIncidentCount($incidentCount);
                $fixedProjetcs++;
                $em = $this->getContainer()->get('doctrine')->getManager();
                $em->persist($project);
                $em->flush();
                $output->writeln('   - Corrected count for project ' . $project->getName()) . '.';
            }
        }

        $output->writeln("\n  <info>Done</info> " . $fixedProjetcs . " Project(s) corrected.\n");
    }
}
