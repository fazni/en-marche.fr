<?php

namespace AppBundle\Assessor;

use AppBundle\Entity\AssessorRequest;
use AppBundle\Entity\VotePlace;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;

class AssessorRequestFactory
{
    /** @var EntityManagerInterface */
    private $manager;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    public static function createFromArray(array $data): AssessorRequest
    {
        $assessor = AssessorRequest::create(
            $data['gender'],
            $data['lastName'],
            $data['firstName'],
            $data['birthdate'],
            $data['birthCity'],
            $data['address'],
            $data['postalCode'],
            $data['city'],
            $data['voteCity'],
            $data['officeNumber'],
            $data['emailAddress'],
            $data['phoneNumber'],
            $data['assessorCity'],
            $data['assessorPostalCode'],
            $data['office']
        );

        return $assessor;
    }

    public function createFromCommand(AssessorRequestCommand $assessorRequestCommand): AssessorRequest
    {
        $assessorRequest = new AssessorRequest();

        $assessorRequest->setGender($assessorRequestCommand->getGender());
        $assessorRequest->setLastName($assessorRequestCommand->getLastName());
        $assessorRequest->setFirstName($assessorRequestCommand->getFirstName());
        $assessorRequest->setBirthName($assessorRequestCommand->getBirthName());
        $assessorRequest->setBirthdate($assessorRequestCommand->getBirthdate());
        $assessorRequest->setBirthCity($assessorRequestCommand->getBirthCity());
        $assessorRequest->setAddress($assessorRequestCommand->getAddress());
        $assessorRequest->setPostalCode($assessorRequestCommand->getPostalCode());
        $assessorRequest->setCity($assessorRequestCommand->getCity());
        $assessorRequest->setOfficeNumber($assessorRequestCommand->getOfficeNumber());
        $assessorRequest->setPhone($assessorRequestCommand->getPhone());
        $assessorRequest->setEmailAddress($assessorRequestCommand->getEmailAddress());
        $assessorRequest->setBirthdate($assessorRequestCommand->getBirthdate());
        $assessorRequest->setVoteCity($assessorRequestCommand->getVoteCity());

        /** @var Collection $votePlaceWishes */
        $votePlaceWishes = $this->getVotePlacesWishesChoices($assessorRequestCommand->getVotePlaceWishes());
        $assessorRequest->setVotePlaceWishes($votePlaceWishes);

        $assessorRequest->setAssessorCity($assessorRequestCommand->getAssessorCity());
        $assessorRequest->setAssessorPostalCode($assessorRequestCommand->getAssessorPostalCode());
        $assessorRequest->setAssessorCountry($assessorRequestCommand->getAssessorCountry());

        return $assessorRequest;
    }

    private function getVotePlacesWishesChoices(array $ids): Collection
    {
        $references = new ArrayCollection();

        foreach ($ids as $id) {
            $references->add($this->manager->getPartialReference(VotePlace::class, $id));
        }

        return $references;
    }
}
