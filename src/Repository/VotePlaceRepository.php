<?php

namespace AppBundle\Repository;

use AppBundle\Assessor\Filter\VotePlaceFilters;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\AssessorRequest;
use AppBundle\Entity\VotePlace;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;

class VotePlaceRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, VotePlace::class);
    }

    /**
     * @return VotePlace[]
     */
    public function findMatchingProposals(Adherent $manager, VotePlaceFilters $filters): array
    {
        if (!$manager->isAssessorManager()) {
            return [];
        }

        $qb = $this->createQueryBuilder('vp');

        $filters->apply($qb, 'vp');

        return $this->addAndWhereManagedBy($qb, $manager)
            ->getQuery()
            ->getResult()
        ;
    }

    public function countMatchingProposals(Adherent $manager, VotePlaceFilters $filters): int
    {
        if (!$manager->isAssessorManager()) {
            return 0;
        }

        $qb = $this->createQueryBuilder('vp');

        $filters->apply($qb, 'vp');

        return $this->addAndWhereManagedBy($qb, $manager)
            ->select('COUNT(DISTINCT vp.id)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function findMatchingVotePlaces(AssessorRequest $assessorRequest): array
    {
        $qb = $this->createQueryBuilder('vp');

        $qb
            ->where('vp.full = :full')
            ->setParameter('full', false)
            ->andWhere('SUBSTRING(vp.postalCode, 1, 2) = :postalCode')
            ->setParameter('postalCode', substr($assessorRequest->getPostalCode(), 0, 2))
            ->andWhere('vp.city = :city')
            ->setParameter('city', $assessorRequest->getCity())
            ->addOrderBy('vp.name', 'ASC')
        ;

        return $qb->getQuery()->getResult();
    }

    private function addAndWhereManagedBy(QueryBuilder $qb, Adherent $assessorManager): QueryBuilder
    {
        $codesFilter = $qb->expr()->orX();

        foreach ($assessorManager->getAssessorManagedArea()->getCodes() as $key => $code) {
            if (is_numeric($code)) {
                // Postal code prefix
                $codesFilter->add(
                    $qb->expr()->like('vp.postalCode', ':code'.$key)
                );
                $qb->setParameter('code'.$key, $code.'%');
            }
        }

        return $qb->andWhere($codesFilter);
    }
}
