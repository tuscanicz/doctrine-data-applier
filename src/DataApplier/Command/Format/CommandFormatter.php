<?php

namespace DataApplier\Command\Format;

use Exception;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\OutputInterface;

class CommandFormatter
{
    public function configureOutputFormats(OutputInterface $output)
    {
        $output->getFormatter()->setStyle(
            CommandOutputFormatEnum::OUTPUT_ERROR,
            new OutputFormatterStyle('red')
        );
        $output->getFormatter()->setStyle(
            CommandOutputFormatEnum::OUTPUT_SUCCESS,
            new OutputFormatterStyle('green')
        );
    }

    /**
     * @param string $string
     * @return string
     */
    public function formatError($string)
    {
        return $this->formatString($string, CommandOutputFormatEnum::OUTPUT_ERROR);
    }

    /**
     * @param string $string
     * @return string
     */
    public function formatSuccess($string)
    {
        return $this->formatString($string, CommandOutputFormatEnum::OUTPUT_SUCCESS);
    }

    /**
     * @param string $string
     * @param string $format
     * @return string
     */
    public function formatString($string, $format)
    {
        if (CommandOutputFormatEnum::hasValue($format) === true) {

            return $this->format($string, $format);

        }

        throw new Exception('Unresolved output format: '.$format);
    }

    /**
     * @param string $string
     * @param string $format
     * @return string
     */
    private function format($string, $format)
    {
        return sprintf(
            '<%s>%s</%s>',
            $format,
            $string,
            $format
        );
    }
}
