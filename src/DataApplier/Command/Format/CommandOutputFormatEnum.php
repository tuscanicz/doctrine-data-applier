<?php

namespace DataApplier\Command\Format;

use Enum\AbstractEnum;

class CommandOutputFormatEnum extends AbstractEnum
{
    const OUTPUT_ERROR = 'error';
    const OUTPUT_SUCCESS = 'success';
}
