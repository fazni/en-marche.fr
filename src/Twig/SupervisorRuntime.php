<?php

namespace AppBundle\Twig;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Entity\CommitteeMembership;
use AppBundle\Repository\CommitteeRepository;
use Twig\Extension\RuntimeExtensionInterface;

class SupervisorRuntime implements RuntimeExtensionInterface
{
    private $repository;

    public function __construct(CommitteeRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getSupervisedCommittee(Adherent $adherent): Committee
    {
        return current($this->repository->findCommitteesByPrivilege($adherent, [CommitteeMembership::COMMITTEE_SUPERVISOR]));
    }
}
