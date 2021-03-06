<?php

namespace Koalamon\Bundle\InformationBundle\Entity;

use Koalamon\Bundle\IncidentDashboardBundle\Entity\Project;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Validator\Constraints\DateTime;

class InformationRepository extends EntityRepository
{
    public function findCurrentInformation(Project $project)
    {
        $qb = $this->createQueryBuilder('i');
        $qb->where("i.project = :project");
        $qb->andWhere("i.endDate >= :endDate");

        $qb->orderBy('i.id', 'ASC');

        $qb->setParameter('project', $project);
        $qb->setParameter('endDate', date("Y-m-d H:i:s", time()));

        return $qb->getQuery()->getResult();
    }
}
