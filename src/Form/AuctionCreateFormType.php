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
            ->add('title',null,['label'=>'Tytuł'])
            ->add('description',null,['label'=>'Opis'])
            ->add('startingPrice', MoneyType::class, [
                'mapped' => false,
                'currency' => 'PLN',
                'divisor' => 100,
                'data' => 100,
                'label' => 'Cena wywoławcza',
                'attr' => [
                    'min' => 100,
                    'required' => true
                    ]
                ])
            ->add('endsAt', ChoiceType::class, [
                'choices' => [
                    '1 dzień' => 86400,
                    '5 dni' => 432000,
                    '7 dni' => 604800
                ],
                'data' => 432000,
                'label' => 'Koniec za'
            ])
            ->add('Submit', SubmitType::class, ['label'=>'Wyślij', 
            'attr' =>
            ['class' => 'btn-warning']
            ])
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
