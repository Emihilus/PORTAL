<?php

namespace App\Form;

use App\Entity\Auction;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Vich\UploaderBundle\Form\Type\VichFileType;

class AuctionCreateFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('description')
            ->add('startingPrice', null)
            ->add('endsAt', ChoiceType::class, [
                'choices' => [
                    '1 day' => 86400,
                    '5 days' => 432000,
                    '7 days' => 604800
                ],
                'data' => 432000,
                'label' => 'expires in'
            ])
            // ->add('token', HiddenType::class)
            /*->add('imageFile', VichFileType::class)*/
            ->add('Submit', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Auction::class,
            'allow_extra_fields' => true
        ]);
    }
}
