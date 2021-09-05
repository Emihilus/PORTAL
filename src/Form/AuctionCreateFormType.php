<?php

namespace App\Form;

use App\Entity\Auction;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class AuctionCreateFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('description')
            ->add('startingPrice', MoneyType::class, [
                'currency' => 'PLN',
                'mapped' => false,
                'data' => 1,
                'label' => 'Cena wywoławcza',
                'attr' => ['min' => 1]
                ])
            ->add('endsAt', ChoiceType::class, [
                'choices' => [
                    '1 day' => 86400,
                    '5 days' => 432000,
                    '7 days' => 604800
                ],
                'data' => 432000,
                'label' => 'expires in'
            ])
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
