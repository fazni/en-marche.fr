<?php

namespace AppBundle\Assessor;

use AppBundle\Assessor\Filter\AssessorRequestFilters;
use AppBundle\Assessor\Filter\VotePlaceFilters;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\AssessorRequest;
use AppBundle\Entity\VotePlace;
use AppBundle\Repository\AssessorRequestRepository;
use AppBundle\Repository\VotePlaceRepository;
use Doctrine\ORM\EntityManagerInterface;

class AssessorManager
{
    private $assessorRequestRepository;
    private $votePlaceRepository;
    private $manager;
    private $dispatcher;

    public function __construct(
        AssessorRequestRepository $assessorRequestRepository,
        VotePlaceRepository $votePlaceRepository,
        EntityManagerInterface $manager
    ) {
        $this->assessorRequestRepository = $assessorRequestRepository;
        $this->votePlaceRepository = $votePlaceRepository;
        $this->manager = $manager;
    }

    public function processAssessorRequest(
        AssessorRequest $request,
        VotePlace $proxy = null,
        bool $notify = false,
        bool $flush = true
    ): void {
        $request->process($proxy);

        if ($flush) {
            $this->manager->flush();
        }
    }

    public function unprocessProcurationRequest(AssessorRequest $request, bool $flush = true): void
    {
        $request->unprocess();

        if ($flush) {
            $this->manager->flush();
        }
    }

    public function enableAssessorRequest(AssessorRequest $assessorRequest, bool $flush = true): void
    {
        $assessorRequest->enable();

        if ($flush) {
            $this->manager->flush();
        }
    }

    public function disableAssessorRequest(AssessorRequest $assessorRequest, bool $flush = true): void
    {
        $assessorRequest->disable();

        if ($flush) {
            $this->manager->flush();
        }
    }

    public function getMatchingVotePlaces(AssessorRequest $request): array
    {
        return $this->votePlaceRepository->findMatchingVotePlaces($request);
    }

    public function getAssessorRequestProposal(int $id, Adherent $manager): ?AssessorRequest
    {
        $assessorRequest = $this->assessorRequestRepository->find($id);

        if (!$assessorRequest instanceof AssessorRequest) {
            return null;
        }

        if (!$this->assessorRequestRepository->isManagedBy($manager, $assessorRequest)) {
            return null;
        }

        return $assessorRequest;
    }

    public function getAssessorRequest(int $id, Adherent $manager): ?AssessorRequest
    {
        $request = $this->assessorRequestRepository->find($id);

        if (!$request instanceof AssessorRequest) {
            return null;
        }

        if (!$this->assessorRequestRepository->isManagedBy($manager, $request)) {
            return null;
        }

        return $request;
    }

    public function getAssessorRequests(Adherent $manager, AssessorRequestFilters $filters): array
    {
        return $this->assessorRequestRepository->findMatchingRequests($manager, $filters);
    }

    public function countAssessorRequests(Adherent $manager, AssessorRequestFilters $filters): int
    {
        return $this->assessorRequestRepository->countMatchingRequests($manager, $filters);
    }

    public function getVotePlacesProposals(Adherent $manager, VotePlaceFilters $filters): array
    {
        return $this->votePlaceRepository->findMatchingProposals($manager, $filters);
    }

    public function countVotePlacesProposals(Adherent $manager, VotePlaceFilters $filters)
    {
        return $this->votePlaceRepository->countMatchingProposals($manager, $filters);
    }
}
