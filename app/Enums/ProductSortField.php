<?php

namespace App\Enums;

enum ProductSortField: string
{
    case Relevance = 'relevance';
    case Price = 'price';
    case CreatedAt = 'created_at';
}
