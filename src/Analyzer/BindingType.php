<?php

declare(strict_types=1);

namespace DIScope\Analyzer;

enum BindingType: string
{
    case BIND = 'bind';
    case SINGLETON = 'singleton';
    case INSTANCE = 'instance';
    case CONTEXTUAL = 'contextual';
    case ALIAS = 'alias';
}
