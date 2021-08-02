<?php
namespace App\Overrides;

use Illuminate\Support\MessageBag;
use Illuminate\Validation\Validator;


class ApiValidator extends Validator 
{
    protected function addError(string $attribute, string $rule, array $parameters): void
    {
        $message = $this->getMessage($attribute, $rule);

        $message = $this->doReplacements($message, $attribute, $rule, $parameters);

        $customMessage = new MessageBag();

        $customMessage->merge(['code' => strtolower("{$attribute}_{$rule}")]);
        $customMessage->merge(['message' => $message]);

        $this->messages->add($attribute, $customMessage);//dd($this->messages);
    }    
}
