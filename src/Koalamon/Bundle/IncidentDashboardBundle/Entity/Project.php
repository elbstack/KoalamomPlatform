<?php

namespace Koalamon\Bundle\IncidentDashboardBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Koalamon\Bundle\IncidentDashboardBundle\Validator\Constraints as KoalamonAssert;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Event
 *
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="identifier_idx", columns={"identifier"})})
 * @ORM\Entity(repositoryClass="Koalamon\Bundle\IncidentDashboardBundle\Entity\ProjectRepository")
 */
class Project implements \JsonSerializable
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @KoalamonAssert\IsIdentifier
     * @Assert\Length(
     *      min = "3",
     *      max = "10",
     *      minMessage = "The identifier must be at least {{ limit }} characters length",
     *      maxMessage = "The identifier cannot be longer than {{ limit }} characters length"
     * )
     *
     * @ORM\Column(name="identifier", type="string", length=255)
     */
    private $identifier;

    /**
     * @var string
     * @Assert\Length(
     *      min = "3",
     *      max = "100",
     *      minMessage = "The name must be at least {{ limit }} characters length",
     *      maxMessage = "The name cannot be longer than {{ limit }} characters length"
     * )
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="image", type="string", length=255, nullable=true)
     */
    private $image;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=255, nullable=true)
     */
    private $url;

    /**
     * @var string
     *
     * @ORM\Column(name="api_key", type="string", length=255)
     */
    private $apiKey;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="openIncidentCount", type="integer", nullable=true)
     */
    private $openIncidentCount = 0;

    /**
     * @var string
     *
     * @ORM\Column(name="public", type="boolean", nullable=true)
     */
    private $public = false;

    /**
     * @var string
     *
     * @ORM\Column(name="eventCount", type="integer", nullable=true)
     */
    private $eventCount = 0;

    /**
     * @var UserRole[]
     *
     * @ORM\OneToMany(targetEntity="UserRole", mappedBy="project")
     */
    private $userRoles;

    /**
     * @var Tool[]
     *
     * @ORM\OneToMany(targetEntity="Tool", mappedBy="project")
     * @ORM\OrderBy({"name" = "ASC"})
     */
    private $tools;

    /**
     * @ORM\OneToMany(targetEntity="EventIdentifier", mappedBy="project")
     */
    private $eventIdentifiers;

    /**
     * @ORM\OneToMany(targetEntity="System", mappedBy="project")
     * @ORM\OrderBy({"name" = "ASC"})
     */
    private $systems;

    /**
     * @ORM\OneToMany(targetEntity="Koalamon\Bundle\InformationBundle\Entity\Information", mappedBy="project")
     */
    private $informations;

    /**
     * @ORM\OneToMany(targetEntity="Translation", mappedBy="project")
     */
    private $translations;

    /**
     * @ORM\Column(type="datetime")
     */
    private $lastStatusChange;

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    public function __construct()
    {
        $this->apiKey = $this->generateApiKey();
    }

    private function generateApiKey()
    {
        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
    }

    /**
     * @return User[]
     */
    public function getUserRoles()
    {
        $sortedRoles = $this->userRoles->toArray();

        usort($sortedRoles, function (UserRole $a, UserRole $b) {
            return ($a->getUser()->getUsername() < $b->getUser()->getUsername()) ? -1 : 1;
        });

        return $sortedRoles;
    }

    public function getUserRole(User $user)
    {
        foreach ($this->userRoles as $userRole) {
            if ($userRole->getUser() == $user) {
                return $this->userRole;
            }
        }
    }

    public function addUserRole(UserRole $userRole)
    {
        $this->userRoles[] = $userRole;
    }

    /**
     * @return Tool[]
     */
    public function getTools($onlyActive = true)
    {
        if ($onlyActive) {
            $tools = array();
            foreach ($this->tools as $tool) {
                if ($tool->isActive()) {
                    $tools[] = $tool;
                }
            }
            return $tools;
        }
        return $this->tools;
    }

    /**
     * @return System[]
     */
    public function getSystems()
    {
        return $this->systems;
    }

    public function getMainSystems()
    {
        $systems = [];

        foreach ($this->getSystems() as $system) {
            if (!$system->getParent()) {
                $systems[] = $system;
            }
        }

        return $systems;
    }

    /**
     * @return mixed
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * @param string $identifier
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @param string $slackWebhook
     */
    public function setSlackWebhook($slackWebhook)
    {
        $this->slackWebhook = $slackWebhook;
    }

    /**
     * @param mixed $owner
     */
    public function setOwner(User $owner)
    {
        $this->owner = $owner;
    }

    public function addSystem(System $system)
    {
        $this->systems[] = $system;
    }

    /**
     * @return string
     */
    public function incOpenIncidentCount()
    {
        $this->openIncidentCount++;
    }

    /**
     * @param string $openIncidentCount
     */
    public function decOpenIncidentCount()
    {
        $this->openIncidentCount = max(0, $this->openIncidentCount - 1);
    }

    /**
     * @return string
     */
    public function getOpenIncidentCount()
    {
        return $this->openIncidentCount;
    }

    public function setOpenIncidentCount($count)
    {
        $this->openIncidentCount = $count;
    }

    /**
     * @return \DateTime
     */
    public function getLastStatusChange()
    {
        return $this->lastStatusChange;
    }

    /**
     * @param mixed $lastStatusChange
     */
    public function setLastStatusChange(\DateTime $lastStatusChange)
    {
        $this->lastStatusChange = $lastStatusChange;
    }

    /**
     * @return EventIdentifier[]
     */
    public function getEventIdentifiers()
    {
        return $this->eventIdentifiers;
    }

    /**
     * @return \stdClass
     */
    function jsonSerialize()
    {
        return [
            "api_key" => $this->getApiKey(),
            "name" => $this->getName(),
            "identifier" => $this->getIdentifier()
        ];
    }

    /**
     * @return integer
     */
    public function getEventCount()
    {
        $count = 0;
        foreach ($this->getEventIdentifiers() as $identifer) {
            $count += $identifer->getEventCount();
        }

        return $count;
    }

    /**
     * @return integer
     */
    public function getFailedEventCount()
    {
        $count = 0;
        foreach ($this->getEventIdentifiers() as $identifer) {
            $count += $identifer->getFailedEventCount();
        }

        return $count;
    }

    public function getFailureCount()
    {
        $count = 0;
        foreach ($this->getEventIdentifiers() as $identifier) {
            $count += $identifier->getFailureCount();
        }
        return $count;
    }

    /**
     * @return integer
     */
    public function getFailureRate()
    {
        $eventCount = $this->getEventCount();

        if ($eventCount == 0) {
            return 0;
        }
        return $this->getFailedEventCount() / $eventCount * 100;
    }

    /**
     * @return string
     */
    public function isPublic()
    {
        return $this->public;
    }

    /**
     * @param string $public
     */
    public function setPublic($public)
    {
        $this->public = $public;
    }

}

