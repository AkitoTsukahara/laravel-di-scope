<?php

declare(strict_types=1);

namespace DIScope\Visualization\Formatters;

use DIScope\Visualization\Graph;

interface FormatterInterface
{
    public function format(Graph $graph): string;

    public function getFileExtension(): string;
}
