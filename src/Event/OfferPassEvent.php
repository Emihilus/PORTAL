<?php
namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

class OfferPassEvent extends Event
{
    public function __construct($dataCollectionArray)
    {
        $this->params = $dataCollectionArray;
    }
}