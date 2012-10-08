<?php

namespace Msi\Bundle\PaginatorBundle\Twig\Extension;

use Symfony\Component\DependencyInjection\ContainerInterface;

class PaginatorExtension extends \Twig_Extension
{
    private $environment;

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getFunctions()
    {
        return array(
            'msi_paginator_render' => new \Twig_Function_Method($this, 'renderPaginator', array('is_safe' => array('html'))),
        );
    }

    public function initRuntime(\Twig_Environment $environment)
    {
        $this->environment = $environment;
    }

    public function getName()
    {
        return 'msi_paginator';
    }

    public function renderPaginator($paginator)
    {
        $numPages = $paginator->countPages();
        $options = $paginator->getOptions();

        if ($numPages < 2) return;

        $pagination = array();
        // previous
        if ($paginator->getPage() != 1) {
            $pagination[] = array('attr' => array(), 'url' => $this->generateUrl($paginator->getPage() - 1), 'label' => '«');
        } else {
            $pagination[] = array('attr' => array('class' => 'disabled'), 'url' => $this->generateUrl(1), 'label' => '«');
        }
        // first
        if ($paginator->getPage() > 4) {
            $pagination[] = array('attr' => array(), 'url' => $this->generateUrl(1), 'label' => 1);
            $pagination[] = array('attr' => array('class' => 'disabled'), 'label' => '...', 'url' => '#');
        }
        // middle
        if ($numPages > 1) {
            for ($i=$paginator->getPage() - 4; $i < $paginator->getPage() - 4 + 7; $i++) {
                if ($i + 1 == $paginator->getPage()) {
                    $pagination[] = array('attr' => array('class' => 'active'), 'url' => $this->generateUrl($i + 1), 'label' => $i + 1);
                } else if ($i >= 0 && $i <= $numPages - 1) {
                    $pagination[] = array('attr' => array(), 'url' => $this->generateUrl($i + 1), 'label' => $i + 1);
                }
            }
        }
        // last
        if ($paginator->getPage() < $numPages - 3) {
            $pagination[] = array('attr' => array('class' => 'disabled'), 'label' => '...', 'url' => '#');
            $pagination[] = array('attr' => array(), 'url' => $this->generateUrl($numPages), 'label' => $numPages);
        }
        // next
        if ($paginator->getPage() != $numPages) {
            $pagination[] = array('attr' => array(), 'url' => $this->generateUrl($paginator->getPage() + 1), 'label' => '»');
        } else {
            $pagination[] = array('attr' => array('class' => 'disabled'), 'url' => $this->generateUrl($numPages), 'label' => '»');
        }

        if ($paginator->getPage() > $numPages) return;

        return $this->environment->render('MsiPaginatorBundle:Pagination:'.$options['template'].'.html.twig', array('paginator' => $paginator, 'pagination' => $pagination));
    }

    protected function generateUrl($page)
    {
        $request = $this->container->get('request');

        $parameters = array_merge($request->query->all(), array('page' => $page));

        return $this->container->get('router')->generate($request->attributes->get('_route'), $parameters);
    }
}
