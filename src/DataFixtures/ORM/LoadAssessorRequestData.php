<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\AssessorOfficeEnum;
use AppBundle\Entity\AssessorRequest;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadAssessorRequestData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $votePlaceLilleWazemmes = $this->getReference('vote-place-lille-wazemmes');
        $votePlaceLilleJeanZay = $this->getReference('vote-place-lille-jean-zay');
        $votePlaceBobigny = $this->getReference('vote-place-bobigny-blanqui');

        $manager->persist($unmatchedrequest1 = $this->createAssessorRequest(
           'female',
           'Kepoura',
           'Adrienne',
           '14-05-1973',
           'Lille',
           '4 avenue du peuple Belge',
           '59000',
           'Lille',
           'Lille',
           '59350_0108',
           'adrienne.kepoura@example.fr',
           '0612345678',
           'Lille',
            '59000',
            AssessorOfficeEnum::HOLDER
        ));

        $manager->persist($matchedrequest1 = $this->createAssessorRequest(
            'male',
            'Hytté',
            'Prosper',
            '10-07-1989',
            'Paris',
            '72 Rue du Faubourg Saint-Martin',
            '93008',
            'Paris',
            'Bobigny',
            '93008_0005',
            'prosper.hytte@example.fr',
            '0612345678',
            'Bobigny',
            '93008',
            AssessorOfficeEnum::SUBSTITUTE
        ));

        $manager->persist($matchedrequest2 = $this->createAssessorRequest(
            'male',
            'Luc',
            'Ratif',
            '04-02-1992',
            'Paris',
            '70 Rue Saint-Martin',
            '93008',
            'Paris',
            'Bobigny',
            '93008_0005',
            'luc.ratif@example.fr',
            '0612345678',
            'Bobigny',
            '93008',
            AssessorOfficeEnum::HOLDER
        ));

        $manager->persist($matchedrequest3 = $this->createAssessorRequest(
            'female',
            'Coptère',
            'Elise',
            '14-01-1986',
            'Lille',
            ' Pl. du Théâtre',
            '59000',
            'Lille',
            'Lille',
            '59350_0108',
            'elise.coptere@example.fr',
            '0612345678',
            'Lille',
            '59000',
            AssessorOfficeEnum::HOLDER
        ));

        $manager->persist($this->createAssessorRequest(
            'male',
            'Sahalor',
            'Aubin',
            '12-08-1986',
            'Lille',
            ' Pl. du Théâtre',
            '59000',
            'Lille',
            'Lille',
            '59350_0108',
            'aubin.sahalor@example.fr',
            '0612345678',
            'Lille',
            '59000',
            AssessorOfficeEnum::HOLDER,
            null
        ));

        $unmatchedrequest1->addVotePlaceWish($votePlaceLilleWazemmes);
        $unmatchedrequest1->addVotePlaceWish($votePlaceLilleJeanZay);

        $matchedrequest1->addVotePlaceWish($votePlaceBobigny);
        $matchedrequest1->process($votePlaceBobigny);

        $matchedrequest2->addVotePlaceWish($votePlaceBobigny);
        $matchedrequest2->process($votePlaceBobigny);

        $matchedrequest3->addVotePlaceWish($votePlaceLilleWazemmes);
        $matchedrequest3->process($votePlaceLilleWazemmes);

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
        bool $disabled = false
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
        $assessor->setOffice($office);
        $assessor->setAssessorPostalCode($assessorPostalCode);

        if ($disabled) {
            $assessor->disable();
        }

        return $assessor;
    }

    public function getDependencies()
    {
        return [
            LoadVotePlaceData::class,
        ];
    }
}
