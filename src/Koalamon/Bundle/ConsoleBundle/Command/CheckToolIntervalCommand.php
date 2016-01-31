<?php

namespace Koalamon\Bundle\Bundle\ConsoleBundle\Command;

use Koalamon\Bundle\IncidentDashboardBundle\Entity\Event;
use Koalamon\Bundle\IncidentDashboardBundle\Entity\EventIdentifier;
use Koalamon\Bundle\IncidentDashboardBundle\Util\ProjectHelper;
use Koalamon\Component\Command\CheckToolIntervalCommandInterface;
use Koalamon\Component\Command\ToolToCheckIntervalForInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckToolIntervalCommand extends ContainerAwareCommand implements CheckToolIntervalCommandInterface
{
    protected function configure()
    {
        $this
            ->setName('koalamon:toolinterval:check')
            ->setDescription('Checks if the tools where called in the mandatory interval');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();

        $tools = $em->getRepository('KoalamonIncidentDashboardBundle:Tool')->findBy(["active" => true]);

        foreach ($tools as $tool) {
            if (!is_null($tool->getInterval()) && ($tool->getInterval() != 0)) {

                $eventIdentifier = $em->getRepository('KoalamonIncidentDashboardBundle:EventIdentifier')->findOneBy(
                    ["project" => $tool->getProject(), "tool" => $tool],
                    ['lastEvent' => 'desc']
                );

                if (is_null($eventIdentifier)) {
                    continue;
                }

                $event = $eventIdentifier->getLastEvent();

                $interval = $tool->getInterval() + 1;
                $now = new \DateTime();
                $lastEventLimit = $now->sub(new \DateInterval("PT".$interval."M"));

                $newEvent = new Event();

                $identifierString = 'koalamon_tool_intervalcheck_'.$tool->getIdentifier();

                $identifier = $em->getRepository('KoalamonIncidentDashboardBundle:EventIdentifier')->findOneBy(
                    array("project" => $tool->getProject(), "identifier" => $identifierString)
                );

                if (is_null($identifier)) {
                    $identifier = new EventIdentifier();
                    $identifier->setProject($tool->getProject());
                    $identifier->setIdentifier($identifierString);
                    $em->persist($identifier);
                    $em->flush();
                }

                $newEvent->setEventIdentifier($identifier);
                $newEvent->setSystem('Koalamon');
                $newEvent->setType('koalamon_intervalcheck');
                $newEvent->setUnique(false);

                if ($event->getCreated() < $lastEventLimit) {
                    $newEvent->setStatus(Event::STATUS_FAILURE);

                    $message = "The tool '".$tool->getName()."' did not send any events since ".$event->getCreated(
                        )->format("d.m.Y H:i:m").".";
                    $newEvent->setMessage($message);

                    $output->writeln(
                        "project_id: ".$event->getEventIdentifier()->getProject()->getId()." -- ".$message
                    );
                } else {
                    $newEvent->setStatus(Event::STATUS_SUCCESS);
                    $newEvent->setMessage("");
                }

                $dispatcher = $this->getContainer()->get('event_dispatcher');
                ProjectHelper::addEvent($this->getContainer()->get("Router"), $em, $newEvent, $dispatcher);
            }
        }
    }
}
