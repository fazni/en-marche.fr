<?php

namespace AppBundle\Form;

use AppBundle\Assessor\AssessorRequestCommand;
use AppBundle\Assessor\AssessorRequestEnum;
use AppBundle\Entity\AssessorOfficeEnum;
use AppBundle\Entity\VotePlace;
use Doctrine\ORM\EntityManagerInterface;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AssessorRequestType extends AbstractType
{
    private $manager;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        switch ($options['transition']) {
            case AssessorRequestEnum::TRANSITION_FILL_PERSONAL_INFO:
                $builder
                    ->add('gender', GenderType::class, [
                        'label' => false,
                    ])
                    ->add('lastName', TextType::class, [
                        'label' => false,
                    ])
                    ->add('firstName', TextType::class, [
                        'label' => false,
                    ])
                    ->add('birthName', TextType::class, [
                        'label' => false,
                    ])
                    ->add('address', TextType::class, [
                        'filter_emojis' => true,
                    ])
                    ->add('postalCode', TextType::class, [
                        'required' => false,
                    ])
                    ->add('city', TextType::class, [
                        'required' => false,
                        'error_bubbling' => true,
                    ])
                    ->add('voteCity', TextType::class, [
                        'required' => false,
                        'error_bubbling' => true,
                    ])
                    ->add('officeNumber', TextType::class, [
                        'required' => false,
                    ])
                    ->add('phone', PhoneNumberType::class, [
                        'required' => false,
                        'widget' => PhoneNumberType::WIDGET_COUNTRY_CHOICE,
                    ])
                    ->add('emailAddress', EmailType::class)
                    ->add('birthdate', BirthdayType::class, [
                        'widget' => 'choice',
                        'years' => $options['years'],
                        'placeholder' => [
                            'year' => 'AAAA',
                            'month' => 'MM',
                            'day' => 'JJ',
                        ],
                    ])
                    ->add('birthCity', TextType::class)
                ;

                $this->addSubmitButton($builder, AssessorRequestEnum::TRANSITION_FILL_PERSONAL_INFO);

                break;
            case AssessorRequestEnum::TRANSITION_FILL_ASSESSOR_INFO:
                $builder
                    ->add('assessorCity', TextType::class, [
                        'label' => false,
                    ])
                    ->add('assessorPostalCode', TextType::class)
                    ->add('assessorCountry', UnitedNationsCountryType::class, [
                        'placeholder' => 'Pays',
                    ])
                    ->add('office', ChoiceType::class, [
                        'label' => false,
                        'choices' => AssessorOfficeEnum::toArray(),
                    ])
                    ->add('votePlaceWishes', ChoiceType::class, [
                        'choice_loader' => new CallbackChoiceLoader(function () {
                            return self::getVotePlacesWishesChoices();
                        }),
                        'required' => false,
                        'multiple' => true,
                    ])
                ;

                $this->addSubmitButton($builder, AssessorRequestEnum::TRANSITION_FILL_ASSESSOR_INFO);

                break;
            case AssessorRequestEnum::TRANSITION_VALID_SUMMARY:
                $this->addSubmitButton($builder, AssessorRequestEnum::TRANSITION_VALID_SUMMARY);

                break;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $years = range((int) date('Y') - 15, (int) date('Y') - 120);

        $resolver
            ->setDefaults([
                'data_class' => AssessorRequestCommand::class,
                'validation_groups' => function (Options $options) {
                    return $options['transition'];
                },
                'years' => array_combine($years, $years),
            ])
            ->setRequired('transition')
            ->setAllowedValues('transition', AssessorRequestEnum::TRANSITIONS)
        ;
    }

    private function addSubmitButton(FormBuilderInterface $builder, string $step)
    {
        $builder->add($step, SubmitType::class, [
            'label' => 'Continuer',
        ]);
    }

    private function getVotePlacesWishesChoices(): array
    {
        /** @var VotePlace $votePlaces */
        $votePlaces = $this->manager->getRepository(VotePlace::class)->findAll();

        /** @var VotePlace $votePlace */
        foreach ($votePlaces as $votePlace) {
            $choices[$votePlace->getName()] = $votePlace->getId();
        }

        return $choices ?? [];
    }
}
