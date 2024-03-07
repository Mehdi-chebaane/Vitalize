<?php

namespace App\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class CustomTwigExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('firstTwoLines', [$this, 'getFirstTwoLines']),
        ];
    }

    public function getFirstTwoLines($text)
    {
        $lines = explode(PHP_EOL, $text);
        $firstTwoLines = array_slice($lines, 0, 2);
        $result = implode(PHP_EOL, $firstTwoLines);

        return $result;
    }
}