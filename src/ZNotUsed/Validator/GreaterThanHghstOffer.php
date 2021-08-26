<?php
namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class GreaterThanHghstOffer extends Constraint
{
    public $message = 'Value of your offer ("{{ string }}") is smaller than highest offer for this piec wendarniczy';
    public $mode = 'strict'; // If the constraint has configuration options, define them as public properties

    public function validatedBy()
    {
        return static::class.'Validator';
    }
}