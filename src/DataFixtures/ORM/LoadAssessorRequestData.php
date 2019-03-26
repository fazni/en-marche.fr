<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Assessor\AssessorRequestFactory;
use AppBundle\Entity\AssessorOfficeEnum;
use AppBundle\Entity\AssessorRequest;
use AppBundle\Entity\VotePlace;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadAssessorRequestData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        /** @var VotePlace $votePlaceLilleWazemmes */
        $votePlaceLilleWazemmes = $this->getReference('vote-place-lille-wazemmes');

        /** @var VotePlace $votePlaceLilleJeanZay */
        $votePlaceLilleJeanZay = $this->getReference('vote-place-lille-jean-zay');

        $request1 = AssessorRequestFactory::createFromArray([
            'gender' => 'female',
            'lastName' => 'Kepoura',
            'firstName' => 'Adrienne',
            'birthdate' => '14-05-1973',
            'birthCity' => 'Lille',
            'address' => '4 avenue du peuple Belge',
            'postalCode' => '59000',
            'city' => 'Lille',
            'voteCity' => 'Lille',
            'officeNumber' => '59350_0108',
            'emailAddress' => 'adrienne.kepoura@example.fr',
            'phoneNumber' => '0612345678',
            'assessorCity' => 'Lille',
            'assessorPostalCode' => '59000',
            'office' => AssessorOfficeEnum::HOLDER,
        ]);

        $request1->addVotePlaceWish($votePlaceLilleWazemmes);
        $request1->addVotePlaceWish($votePlaceLilleJeanZay);
        $votePlaceLilleWazemmes->addAssessorRequest($request1);

        $manager->persist($request1);

        $request2 = AssessorRequestFactory::createFromArray([
            'gender' => 'male',
            'lastName' => 'Kepourapas',
            'firstName' => 'Adriano',
            'birthdate' => '14-05-1970',
            'birthCity' => 'Lille',
            'address' => '4 avenue du peuple Belge',
            'postalCode' => '59000',
            'city' => 'Lille',
            'voteCity' => 'Lille',
            'officeNumber' => '59350_0108',
            'emailAddress' => 'adriano.kepourapas@example.fr',
            'phoneNumber' => '0612345679',
            'assessorCity' => 'Lille',
            'assessorPostalCode' => '59000',
            'office' => AssessorOfficeEnum::SUBSTITUTE,
        ]);

        $request2->addVotePlaceWish($votePlaceLilleJeanZay);
        $votePlaceLilleJeanZay->addAssessorRequest($request2);

        $manager->persist($request2);

        $manager->flush();
    }

    private function createAssessorRequest(
        string $gender,
        string $lastName,
        string $firstName,
        string $birthDate,
        string $birthCity,
        string $address,
        string $postalCode,
        string $city,
        string $voteCity,
        string $officeNumber,
        string $emailAddress,
        string $phoneNumber,
        string $assessorCity,
        string $assessorPostalCode,
        string $office = AssessorOfficeEnum::SUBSTITUTE,
        string $birthName = null,
        string $assessorCountry = 'FR'
    ): AssessorRequest {
        $assessor = new AssessorRequest();

        $assessor->setGender($gender);
        $assessor->setLastName($lastName);
        $assessor->setFirstName($firstName);
        $assessor->setBirthName($birthName);
        $assessor->setBirthdate(new \DateTime($birthDate));
        $assessor->setBirthCity($birthCity);
        $assessor->setAddress($address);
        $assessor->setPostalCode($postalCode);
        $assessor->setCity($city);
        $assessor->setVoteCity($voteCity);
        $assessor->setOfficeNumber($officeNumber);
        $assessor->setEmailAddress($emailAddress);
        $assessor->getPhone()->setNationalNumber($phoneNumber);
        $assessor->setAssessorCity($assessorCity);
        $assessor->setAssessorPostalCode($assessorPostalCode);
        $assessor->setAssessorCountry($assessorCountry);
        $assessor->setOffice($office);

        return $assessor;
    }

    public function getDependencies()
    {
        return [
            LoadVotePlaceData::class,
        ];
    }
}
