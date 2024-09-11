<?php

namespace App\Form;

use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Site;
use App\Entity\Sortie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreationSortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomSortie')
            ->add('dateHeureDebut', null, [
                'widget' => 'single_text',
            ])
            ->add('duree')
            ->add('dateClotureInscription', null, [
                'widget' => 'single_text',
            ])
            ->add('nbIncriptionsMax')
            ->add('infosSortie')
            ->add('dateHeureFin', null, [
                'widget' => 'single_text',
            ])
            ->add('urlPhoto')
            ->add('lieu', EntityType::class, [
                'class' => Lieu::class,
                'choice_label' => 'id',
            ])
            ->add('etat', EntityType::class, [
                'class' => Etat::class,
                'choice_label' => 'id',
            ])
            ->add('organisateur', EntityType::class, [
                'class' => Participant::class,
                'choice_label' => 'id',
            ])
            ->add('participants', EntityType::class, [
                'class' => Participant::class,
                'choice_label' => 'id',
                'multiple' => true,
            ])
            ->add('siteOrganisateur', EntityType::class, [
                'class' => Site::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
