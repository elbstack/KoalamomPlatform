<?php

namespace Koalamon\Bundle\Integration\KoalaPingBundle\Entity;

use Koalamon\Bundle\IncidentDashboardBundle\Entity\System;
use Doctrine\ORM\Mapping as ORM;

/**
 * Event
 *
 * @ORM\Table()
 * @ORM\Entity()
 */
class Integration
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

}
