<?php

namespace Koalamon\Bundle\ScreenshotBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ScreenshotCommand extends ContainerAwareCommand
{
    const IMAGE_DIR = '/images/screenshots/';

    protected function configure()
    {
        $this
            ->setName('koalamon:screenshots:create')
            ->setDescription('creates the screenshots of all systems.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $systems = $em->getRepository('KoalamonIncidentDashboardBundle:System')->findAll();
        $this->createScreenshots($systems, $output);
    }


    private function createScreenshots($systems, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();

        $webDir = $this->getContainer()->getParameter('assetic.write_to');
        $imageDir = $webDir . self::IMAGE_DIR;

        $phantomExec = $this->getExecutable('phantomjs');
        $timeoutExec = $this->getExecutable('timeout');

        if ($phantomExec == '') {
            $output->writeln("<error>No PhantomJS executeable found in \$PATH. Did you install it?</error>");
        }

        if ($timeoutExec == '') {
            $timeoutCommand = '';
            $output->writeln("<info>No timeout executable found. Run screenshot command without timeout.</info>");
        } else {
            $timeoutCommand = 'timeout 30s';
            $output->writeln("Timeout executable found. Run screenshot command with" . $timeoutCommand);
        }

        foreach ($systems as $system) {

            if (!$system->getParent()) {
                if ($system->getUrl()) {

                    $imageName = time() . rand(1, 100000000) . '.png';

                    $command = "$timeoutCommand $phantomExec "
                        . __DIR__ . "/simpleshot.js "
                        . $system->getUrl() . " "
                        . $imageDir . $imageName;

                    $output->writeln("Creating screenshot for " . $system->getUrl());

                    exec($command, $commandOutput, $commandStatus);

                    if ($commandStatus == 0) {
                        if ($system->getImage() && file_exists($imageDir . $system->getImage())) {
                            unlink($imageDir . $system->getImage());
                        }

                        $system->setImage($imageName);
                        $em->persist($system);
                        $em->flush();
                    }
                }
            }
        }
    }

    /**
     * @return string phantomjs executeable
     */
    private function getExecutable($program)
    {
        $command = "which " . (string)$program;
        exec($command, $commandOutput, $commandStatus);
        $commandString = reset($commandOutput);
        return (string)$commandString;
    }
}
