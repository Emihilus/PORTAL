<?php
namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class CBagService
{
    public static $exVar;

    public function __construct(ParameterBagInterface $params)
    {
        static::$exVar = $params->get('validation_OfferValueMax');
    }
}