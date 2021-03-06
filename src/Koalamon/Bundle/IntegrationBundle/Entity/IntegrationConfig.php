<?php

namespace Koalamon\Bundle\IntegrationBundle\Entity;

use Koalamon\Bundle\IncidentDashboardBundle\Entity\Project;
use Koalamon\Bundle\IncidentDashboardBundle\Entity\System;
use Doctrine\ORM\Mapping as ORM;

/**
 * Event
 *
 * @ORM\Table()
 * @ORM\Entity()
 */
class IntegrationConfig
{
    const STATUS_SELECTED = 'selected';
    const STATUS_ALL = 'all';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Koalamon\Bundle\IncidentDashboardBundle\Entity\Project", inversedBy="koalaPingSystems")
     * @ORM\JoinColumn(name="project_id", referencedColumnName="id")
     **/
    private $project;

    /**
     * @var string
     * @ORM\Column(name="integration", type="string", length=255)
     */
    private $integration;

    /**
     * @ORM\Column(name="status", type="string")
     */
    private $status = self::STATUS_SELECTED;

    /**
     * @ORM\Column(name="useSaaS", type="boolean")
     */
    private $useSaaS = true;

    /**
     * @var string
     * @ORM\Column(name="options", type="text", nullable=true)
     */
    private $options;

    /**
     * @return Project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * @param mixed $project
     */
    public function setProject($project)
    {
        $this->project = $project;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getIntegration()
    {
        return $this->integration;
    }

    /**
     * @param string $integration
     */
    public function setIntegration($integration)
    {
        $this->integration = $integration;
    }

    /**
     * @return boolean
     */
    public function getUseSaaS()
    {
        return $this->useSaaS;
    }

    /**
     * @param boolean $useSaaS
     */
    public function setUseSaaS($useSaaS)
    {
        $this->useSaaS = $useSaaS;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return unserialize($this->options);
    }

    /**
     * @param array $options
     */
    public function setOptions(array $options)
    {
        $this->options = serialize($options);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }
}
