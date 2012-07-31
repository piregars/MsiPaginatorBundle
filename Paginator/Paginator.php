<?php

namespace Msi\Bundle\PaginatorBundle\Paginator;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Collections\Collection;

class Paginator
{
    private $page;
    private $limit;
    private $length;
    private $parameters;
    private $data;
    private $result;

    private $router;
    private $templating;
    private $request;

    public function __construct(EngineInterface $templating, RouterInterface $router, Request $request)
    {
        $this->page = 1;
        $this->limit = 10;
        $this->data = null;
        $this->result = null;
        $this->parameters = array();

        $this->router = $router;
        $this->templating = $templating;
        $this->request = $request;
    }

    public function genUrl($page)
    {
        $parameters = array_merge($this->parameters, array('page' => $page));

        return $this->router->generate($this->request->attributes->get('_route'), $parameters);
    }

    public function countPages()
    {
        return ceil($this->length / $this->limit);
    }

    public function getFrom()
    {
        return $this->limit * $this->page - $this->limit + 1;
    }

    public function getTo()
    {
        if ($this->page == $this->countPages())
            return $this->length;
        else
            return $this->limit * $this->page;
    }

    public function getResult()
    {
        if (null === $this->result) {
            $offset = ($this->page - 1) * $this->limit;

            if ($this->data instanceof Collection) {

                $this->result = $this->data->slice($offset, $this->limit);
            } else {
                $this->data->setFirstResult($offset);
                $this->data->setMaxResults($this->limit);

                $this->result = new ArrayCollection($this->data->getQuery()->execute());
            }
        }

        return $this->result;
    }

    public function setLimit($limit)
    {
        $this->limit = $limit;

        return $this;
    }

    public function setData($data)
    {
        if ($data instanceof Collection) {
            $this->data = $data;
            $this->length = $this->data->count();
        } else {
            $this->data = $data;
            $qbClone = clone $this->data;
            $qbClone->select($qbClone->expr()->count('a.id'));
            $result = $qbClone->getQuery()->getOneOrNullResult();
            $this->length = $result[1];
        }

        return $this;
    }

    public function setPage($page)
    {
        $this->page = $page;

        return $this;
    }

    public function getLength()
    {
        return $this->length;
    }

    public function setParameters($parameters)
    {
        $this->parameters = $parameters;

        return $this;
    }

    public function render()
    {
        $numPages = $this->countPages();

        if ($numPages < 2) return;

        $pagination = array();
        // previous
        if ($this->page != 1) {
            $pagination[] = array('path' => $this->genUrl($this->page - 1), 'label' => 'Prev');
        } else {
            $pagination[] = array('class' => 'disabled', 'path' => $this->genUrl(1), 'label' => 'Prev');
        }
        // first
        if ($this->page > 4) {
            $pagination[] = array('path' => $this->genUrl(1), 'label' => 1);
            $pagination[] = array('class' => 'disabled', 'label' => '...', 'path' => '#');
        }
        // middle
        if ($numPages > 1) {
            for ($i=$this->page - 4; $i < $this->page - 4 + 7; $i++) {
                if ($i + 1 == $this->page) {
                    $pagination[] = array('class' => 'active', 'path' => $this->genUrl($i + 1), 'label' => $i + 1);
                } else if ($i >= 0 && $i <= $numPages - 1) {
                    $pagination[] = array('path' => $this->genUrl($i + 1), 'label' => $i + 1);
                }
            }
        }
        // last
        if ($this->page < $numPages - 3) {
            $pagination[] = array('class' => 'disabled', 'label' => '...', 'path' => '#');
            $pagination[] = array('path' => $this->genUrl($numPages), 'label' => $numPages);
        }
        // next
        if ($this->page != $numPages) {
            $pagination[] = array('path' => $this->genUrl($this->page + 1), 'label' => 'Next');
        } else {
            $pagination[] = array('class' => 'disabled', 'path' => $this->genUrl($numPages), 'label' => 'Next');
        }

        if ($this->page > $numPages) return;

        return $this->templating->render('MsiPaginatorBundle::pagination.html.twig', array('pagination' => $pagination));
    }
}
