<?php

namespace App\Form;

use App\Entity\Participant;
use App\Entity\Site;
use App\Entity\Sortie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Blank;
use Symfony\Component\Validator\Constraints\Text;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Vich\UploaderBundle\Form\Type\VichImageType;



class ProfilFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('pseudo', TextType::class, [
            'required' => false,
        ])
        ->add('nom', TextType::class, [
            'required' => false,
        ])
        ->add('prenom', TextType::class, [
            'required' => false,
        ])
        ->add('telephone', TextType::class, [
            'required' => false,
        ])
        ->add('mail', EmailType::class, [
            'required' => false,
        ])
        ->add('mot_de_passe', RepeatedType::class, [
            'type' => PasswordType::class,
            'required' => false,
            'invalid_message' => 'Les saisies pour le mot de passe doivent être identiques.',
            'options' => ['attr' => ['class' => 'password-field']],
            
            
            'first_options'  => ['label' => 'Mot de passe : ', 'empty_data' => ''],
            'second_options' => ['label' => 'Confirmez le mot de passe : ', 'empty_data' => ''],
        ])
        ->add('site', EntityType::class, [
            'class' => Site::class,
            'choice_label' => 'nom_site',
            'empty_data' => '---------------',
            'required' => false,
        ])
        ->add('imageProfil', VichImageType::class, [
            'label' => 'Photo de profil : ',
            'required' => false,
            'allow_delete' => true,
            'delete_label' => 'Supprimer la photo de profil',
            'download_label' => '...',
            'download_uri' => true,
            'image_uri' => true,
            'imagine_pattern' => '...',
            'asset_helper' => true,
        ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
}
