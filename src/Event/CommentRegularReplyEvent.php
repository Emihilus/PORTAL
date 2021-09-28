<?php
namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

class CommentRegularReplyEvent extends Event
{
    public function __construct($parametersCollection)
    {
        $this->params = $parametersCollection;
    }
}