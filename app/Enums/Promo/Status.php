<?php

namespace App\Enums\Promo;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class Status extends Enum
{
    const Inactive = 0;
    const Active = 1;
}
