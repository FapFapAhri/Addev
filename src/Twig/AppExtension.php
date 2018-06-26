<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use \DateTime;

class AppExtension extends AbstractExtension
{
    public function getFilters()
    {
        return array(
            new TwigFilter('diff', array($this, 'diff')),
        );
    }

    public function diff($date, $format)
    {

        return $date->diff(new DateTime())->format($format);
    }
}