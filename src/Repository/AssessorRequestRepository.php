<?php

namespace AppBundle\Repository;

use AppBundle\Assessor\Filter\AssessorRequestFilters;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\AssessorRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;

class AssessorRequestRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, AssessorRequest::class);
    }

    public function findMatchingRequests(Adherent $manager, AssessorRequestFilters $filters): array
    {
        if (!$manager->isAssessorManager()) {
            return [];
        }

        $qb = $this->createQueryBuilder('ar');

        $filters->apply($qb, 'ar');

        $requests = $this->addAndWhereManagedBy($qb, $manager)
            ->getQuery()
            ->getArrayResult()
        ;

        foreach ($requests as $k => $request) {
            $requests[$k] = [
                'data' => $request,
                'matchingProxiesCount' => 1,
            ];
        }

        return $requests;
    }

    public function countMatchingRequests(Adherent $manager, AssessorRequestFilters $filters): int
    {
        if (!$manager->isAssessorManager()) {
            return 0;
        }

        $qb = $this->createQueryBuilder('ar');

        $filters->apply($qb, 'ar');

        return (int) $this->addAndWhereManagedBy($qb, $manager)
            ->select('COUNT(DISTINCT ar.id)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function isManagedBy(Adherent $manager, AssessorRequest $assessorRequest): bool
    {
        if (!$manager->isAssessorManager()) {
            return false;
        }

        $qb = $this->createQueryBuilder('ar')
            ->select('COUNT(ar)')
            ->where('ar.id = :id')
            ->setParameter('id', $assessorRequest->getId())
        ;

        return (bool) $this->addAndWhereManagedBy($qb, $manager)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function findMatchingProxies(AssessorRequest $assessorRequest): array
    {
        $qb = $this->createQueryBuilder('vp');

        return $qb->getQuery()->getResult();
    }

    private function addAndWhereManagedBy(QueryBuilder $qb, Adherent $assessorManager): QueryBuilder
    {
        $codesFilter = $qb->expr()->orX();

        foreach ($assessorManager->getAssessorManagedArea()->getCodes() as $key => $code) {
            if (is_numeric($code)) {
                // Postal code prefix
                $codesFilter->add(
                    $qb->expr()->like('ar.assessorPostalCode', ':code'.$key)
                );
                $qb->setParameter('code'.$key, $code.'%');
            }
        }

        return $qb->andWhere($codesFilter);
    }
}
