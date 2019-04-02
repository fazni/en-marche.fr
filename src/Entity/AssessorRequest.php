<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Validator\Recaptcha as AssertRecaptcha;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use libphonenumber\PhoneNumber;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="assessor_requests")
 * @ORM\Entity
 *
 * @Algolia\Index(autoIndex=false)
 */
class AssessorRequest
{
    use EntityTimestampableTrait;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(length=6)
     *
     * @Assert\NotBlank(message="common.gender.invalid_choice")
     * @Assert\Choice(
     *     callback={"AppBundle\ValueObject\Genders", "all"},
     *     message="common.gender.invalid_choice",
     *     strict=true
     * )
     */
    private $gender;

    /**
     * @var string
     *
     * @ORM\Column(length=50)
     *
     * @Assert\NotBlank(message="assessor.last_name.not_blank")
     * @Assert\Length(
     *     min=2,
     *     max=50,
     *     minMessage="assessor.last_name.min_length",
     *     maxMessage="assessor.last_name.max_length"
     * )
     */
    private $lastName;

    /**
     * @var string
     *
     * @ORM\Column(length=100)
     *
     * @Assert\NotBlank(message="assessor.first_name.not_blank")
     * @Assert\Length(
     *     min=2,
     *     max=100,
     *     minMessage="assessor.first_name.min_length",
     *     maxMessage="assessor.first_name.max_length"
     * )
     */
    private $firstName;

    /**
     * @var string|null
     *
     * @ORM\Column(length=50, nullable=true)
     *
     * @Assert\Length(
     *     min=2,
     *     max=50,
     *     minMessage="assessor.birth_name.min_length",
     *     maxMessage="assessor.birth_name.max_length"
     * )
     */
    private $birthName;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="date")
     *
     * @Assert\NotBlank(message="assessor.birthdate.not_blank")
     * @Assert\Range(max="-18 years", maxMessage="assessor.birthdate.minimum_required_age")
     */
    private $birthdate;

    /**
     * @var string
     *
     * @ORM\Column(length=15)
     *
     * @Assert\Length(max=15)
     */
    private $birthCity;

    /**
     * @var string
     *
     * @ORM\Column(length=150)
     *
     * @Assert\NotBlank(message="common.address.required")
     * @Assert\Length(max=150, maxMessage="common.address.max_length")
     */
    private $address;

    /**
     * @var string
     *
     * @ORM\Column(length=15)
     *
     * @Assert\Length(max=15)
     */
    private $postalCode;

    /**
     * @var string
     *
     * @ORM\Column(length=15)
     *
     * @Assert\Length(max=15)
     */
    private $city;

    /**
     * @var string
     *
     * @ORM\Column(length=15)
     *
     * @Assert\NotBlank(message="assessor.vote_city.not_blank")
     * @Assert\Length(max=15)
     */
    private $voteCity;

    /**
     * @var string
     *
     * @ORM\Column(length=10)
     *
     * @Assert\NotBlank(message="assessor.office_number.not_blank")
     * @Assert\Length(max=10)
     */
    private $officeNumber;

    /**
     * @var string
     *
     * @ORM\Column
     *
     * @Assert\NotBlank
     * @Assert\Email(message="common.email.invalid")
     * @Assert\Length(max=255, maxMessage="common.email.max_length")
     */
    private $emailAddress;

    /**
     * @var PhoneNumber
     *
     * @ORM\Column(type="phone_number")
     *
     * @Assert\NotBlank(message="common.phone_number.required")
     * @AssertPhoneNumber(defaultRegion="FR")
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(length=15)
     *
     * @Assert\NotBlank(message="assessor.assessor_city.not_blank")
     * @Assert\Length(max=15)
     */
    private $assessorCity;

    /**
     * @var string
     *
     * @ORM\Column(length=15)
     *
     * @Assert\Length(max=15)
     */
    private $assessorPostalCode;

    /**
     * @var string
     *
     * @ORM\Column(length=15)
     *
     * @Assert\NotBlank(message="assessor.office.invalid_choice")
     * @Assert\Choice(
     *     callback={"AppBundle\Entity\AssessorOfficeEnum", "toArray"},
     *     message="assessor.office.invalid_choice",
     *     strict=true
     * )
     */
    private $office = AssessorOfficeEnum::SUBSTITUTE;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="common.recaptcha.invalid_message")
     * @AssertRecaptcha
     */
    public $recaptcha = '';

    /**
     * @var VotePlace|null
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\VotePlace", inversedBy="assessorRequests")
     */
    private $votePlace;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\VotePlace")
     * @ORM\JoinTable(name="assessor_request_vote_place_wishes")
     */
    private $votePlaceWishes;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $processed = false;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $processedAt;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $disabled = false;

    public function __construct()
    {
        $this->phone = static::createPhoneNumber();
        $this->votePlaceWishes = new ArrayCollection();
    }

    public function process(VotePlace $votePlace): void
    {
        $votePlace->addAssessorRequest($this);

        if (VotePlace::MAX_ASSESSOR_REQUESTS == $votePlace->getAssessorRequests()->count()) {
            $votePlace->setFull(true);
        }

        $this->votePlace = $votePlace;
        $this->processed = true;
        $this->processedAt = new \DateTime();
    }

    public function unprocess(): void
    {
        if ($this->votePlace->isFull()) {
            $this->votePlace->setFull(false);
        }

        $this->votePlace->removeAssessorRequest($this);

        $this->votePlace = null;
        $this->processed = false;
        $this->processedAt = null;
    }

    private static function createPhoneNumber(int $countryCode = 33, string $number = null): PhoneNumber
    {
        $phone = new PhoneNumber();
        $phone->setCountryCode($countryCode);

        if ($number) {
            $phone->setNationalNumber($number);
        }

        return $phone;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getGender(): string
    {
        return $this->gender;
    }

    public function setGender(string $gender): void
    {
        $this->gender = $gender;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getBirthName(): ?string
    {
        return $this->birthName;
    }

    public function setBirthName(?string $birthName): void
    {
        $this->birthName = $birthName;
    }

    public function getBirthdate(): \DateTime
    {
        return $this->birthdate;
    }

    public function setBirthdate(\DateTime $birthdate): void
    {
        $this->birthdate = $birthdate;
    }

    public function getBirthCity(): ?string
    {
        return $this->birthCity;
    }

    public function setBirthCity(?string $birthCity): void
    {
        $this->birthCity = $birthCity;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function setAddress(string $address): void
    {
        $this->address = $address;
    }

    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): void
    {
        $this->postalCode = $postalCode;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(string $city): void
    {
        $this->city = $city;
    }

    public function getVoteCity(): string
    {
        return $this->voteCity;
    }

    public function setVoteCity(string $voteCity): void
    {
        $this->voteCity = $voteCity;
    }

    public function getOfficeNumber(): string
    {
        return $this->officeNumber;
    }

    public function setOfficeNumber(string $officeNumber): void
    {
        $this->officeNumber = $officeNumber;
    }

    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }

    public function setEmailAddress(string $emailAddress): void
    {
        $this->emailAddress = $emailAddress;
    }

    public function getPhone(): PhoneNumber
    {
        return $this->phone;
    }

    public function setPhone(PhoneNumber $phone): void
    {
        $this->phone = $phone;
    }

    public function getAssessorCity(): string
    {
        return $this->assessorCity;
    }

    public function setAssessorCity(string $assessorCity): void
    {
        $this->assessorCity = $assessorCity;
    }

    public function getOffice(): string
    {
        return $this->office;
    }

    public function setOffice(string $office): void
    {
        $this->office = $office;
    }

    public function getRecaptcha(): string
    {
        return $this->recaptcha;
    }

    public function setRecaptcha(string $recaptcha): void
    {
        $this->recaptcha = $recaptcha;
    }

    public function getVotePlace(): ?VotePlace
    {
        return $this->votePlace;
    }

    public function setVotePlace(VotePlace $votePlace): void
    {
        $this->votePlace = $votePlace;
    }

    public function getVotePlacesWishes(): Collection
    {
        return $this->votePlaceWishes;
    }

    public function addVotePlaceWish(VotePlace $votePlace): void
    {
        if (!$this->votePlaceWishes->contains($votePlace)) {
            $this->votePlaceWishes->add($votePlace);
        }
    }

    public function removeVotePlaceWish(VotePlace $votePlace): void
    {
        $this->votePlaceWishes->removeElement($votePlace);
    }

    public function getAssessorPostalCode(): string
    {
        return $this->assessorPostalCode;
    }

    public function setAssessorPostalCode(string $assessorPostalCode): void
    {
        $this->assessorPostalCode = $assessorPostalCode;
    }

    public function isProcessed(): bool
    {
        return $this->processed;
    }

    public function setProcessed(bool $processed): void
    {
        $this->processed = $processed;
    }

    public function getProcessedAt(): ?\DateTime
    {
        return $this->processedAt;
    }

    public function setProcessedAt(\DateTime $processedAt): void
    {
        $this->processedAt = $processedAt;
    }

    public function enable(): void
    {
        $this->disabled = false;
    }

    public function disable(): void
    {
        $this->disabled = true;
    }

    public function isDisabled(): bool
    {
        return $this->disabled;
    }
}
