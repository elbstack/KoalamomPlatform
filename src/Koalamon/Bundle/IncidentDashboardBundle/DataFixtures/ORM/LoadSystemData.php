<?php

namespace Koalamon\Bundle\IncidentDashboardBundle\DataFixtures\ORM;

use Koalamon\Bundle\IncidentDashboardBundle\Entity\System;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Koalamon\Bundle\IncidentDashboardBundle\Entity\User;
use Koalamon\Bundle\IncidentDashboardBundle\Entity\Project;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class LoadSystemData extends AbstractFixture implements DependentFixtureInterface
{
    /**
     * @var Finder
     */
    private $fixtures;

    public function __construct()
    {
        $this->fixtures = Finder::create()
            ->files()
            ->name('system_*.json')
            ->in(__DIR__ . '/../../Resources/fixtures');
    }

    public function load(ObjectManager $manager)
    {
        foreach ($this->fixtures as $file) {
            /** @var SplFileInfo $file */
            $fixture = json_decode($file->getContents());
            $system = new System();
            $system->setIdentifier($fixture->identifier);
            $system->setName($fixture->name);
            $system->setUrl($fixture->url);
            /** @var Project $project */
            $project = $this->getReference('project-' . $fixture->project);
            $system->setProject($project);

            $manager->persist($system);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            'Koalamon\Bundle\IncidentDashboardBundle\DataFixtures\ORM\LoadProjectData',
        ];
    }
}
