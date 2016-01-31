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

class LoadProjectData extends AbstractFixture implements DependentFixtureInterface
{
    /**
     * @var Finder
     */
    private $finder;

    public function __construct()
    {
        $this->fixtures = Finder::create()
            ->files()
            ->name('project_*.json')
            ->in(__DIR__ . '/../../Resources/fixtures');
    }

    public function load(ObjectManager $manager)
    {
        foreach ($this->fixtures as $file) {
            /** @var SplFileInfo $file */
            $fixture = json_decode($file->getContents());
            $project = new Project();
            $project->setName($fixture->name);
            $project->setDescription($fixture->description);
            $project->setIdentifier($fixture->identifier);
            $project->setSlackWebhook($fixture->slackWebhook);
            $project->setOwner($this->getReference('user-' . $fixture->owner));

            if (isset($fixture->users)) {
                foreach ($fixture->users as $userName) {
                    /** @var User $user */
                    $user = $this->getReference('user-' . $userName);
                    $project->addUser($user);
                }
            }

            $manager->persist($project);

            $this->addReference('project-' . $project->getIdentifier(), $project);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            'Koalamon\Bundle\IncidentDashboardBundle\DataFixtures\ORM\LoadUserData',
        ];
    }
}
