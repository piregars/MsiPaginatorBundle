<?php

namespace Msi\Bundle\PaginatorBundle\Paginator;

class PaginatorFactory
{
    public function create(array $options = array())
    {
        return new Paginator($options);
    }
}
