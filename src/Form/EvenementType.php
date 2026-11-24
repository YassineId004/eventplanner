<?php

namespace App\Form;

use App\Entity\Evenement;
use App\Entity\Categorie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\{
    TextType,
    TextareaType,
    DateTimeType,
    IntegerType,
    SubmitType
};
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class EvenementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class)
            ->add('description', TextareaType::class)
            ->add('dateDebut', DateTimeType::class)
            ->add('dateFin', DateTimeType::class)
            ->add('lieu', TextType::class)
            ->add('capaciteMax', IntegerType::class)
            ->add('image', TextType::class, [
                'required' => false,
            ])
            ->add('categorie', EntityType::class, [
                'class' => Categorie::class,
                'choice_label' => 'nom',
            ])
            ->add('save', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Evenement::class,
        ]);
    }
}
