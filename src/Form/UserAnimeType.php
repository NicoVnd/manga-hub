<?php

namespace App\Form;

use App\Entity\UserAnime;
use App\Enum\WatchingStatus;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Range;

class UserAnimeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'À voir'     => WatchingStatus::PLANNED,
                    'En cours'   => WatchingStatus::WATCHING,
                    'Vu'         => WatchingStatus::COMPLETED,
                    'En pause'   => WatchingStatus::ON_HOLD,
                    'Abandonné'  => WatchingStatus::DROPPED,
                ],
                'label' => 'Statut',
                'required' => false,
                'placeholder' => 'Aucun statut',
                'empty_data' => null,
            ])
            ->add('rating', IntegerType::class, [
                'required' => false,
                'label' => 'Note (1–10)',
                'constraints' => [new Range(min: 1, max: 10)],
                'attr' => ['min' => 1, 'max' => 10],
            ])
            ->add('comment', TextareaType::class, [
                'required' => false,
                'label' => 'Commentaire (visible publiquement si coché)',
            ])
            ->add('isPublic', CheckboxType::class, [
                'required' => false,
                'label' => 'Rendre public (commentaire/notation visibles)',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => UserAnime::class]);
    }
}
