<?php

namespace Msi\Bundle\PaginatorBundle\Paginator;

use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\Request;

class PaginatorFactory
{
    private $router;
    private $templating;

    public function __construct(EngineInterface $templating, RouterInterface $router, Request $request)
    {
        $this->router = $router;
        $this->templating = $templating;
        $this->request = $request;
    }

    public function create()
    {
        return new Paginator($this->templating, $this->router, $this->request);
    }
}
