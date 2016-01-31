<?php

namespace Koalamon\Bundle\IncidentDashboardBundle\DataFixtures\ORM;

use Koalamon\Bundle\IncidentDashboardBundle\Entity\System;
use Koalamon\Bundle\IncidentDashboardBundle\Entity\Tool;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Koalamon\Bundle\IncidentDashboardBundle\Entity\User;
use Koalamon\Bundle\IncidentDashboardBundle\Entity\Project;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class LoadToolData extends AbstractFixture implements DependentFixtureInterface
{
    /**
     * @var Finder
     */
    private $finder;

    public function __construct()
    {
        $this->fixtures = Finder::create()
            ->files()
            ->name('tool_*.json')
            ->in(__DIR__ . '/../../Resources/fixtures');
    }

    public function load(ObjectManager $manager)
    {
        foreach ($this->fixtures as $file) {
            /** @var SplFileInfo $file */
            $fixture = json_decode($file->getContents());
            $tool = new Tool();
             $tool->setIdentifier($fixture->identifier);
             $tool->setName($fixture->name);
             $tool->setImage($fixture->image);
             $tool->setNotify($fixture->notify);
             $tool->setDescription($fixture->description);

            /** @var Project $project */
            $project = $this->getReference('project-' . $fixture->project);
            $tool->setProject($project);

            $manager->persist($tool);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            'Koalamon\Bundle\IncidentDashboardBundle\DataFixtures\ORM\LoadProjectData'
        ];
    }
}
