<?php

namespace Msi\Bundle\PaginatorBundle\Paginator;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Collections\Collection;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class Paginator
{
    protected $options;

    protected $page;

    protected $limit;

    protected $length;

    protected $data;

    protected $result;

    public function __construct(array $options = array())
    {
        $this->page = 1;
        $this->limit = 10;
        $this->data = null;
        $this->result = null;

        $resolver = new OptionsResolver();
        $this->setDefaultOptions($resolver);
        $this->options = $resolver->resolve($options);
    }

    public function paginate($data, $page, $limit)
    {
        $this->setLimit($limit);
        $this->setPage($page);
        $this->setData($data);
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
        if ($this->page == $this->countPages()) {
            return $this->length;
        }
        else {
            return $this->limit * $this->page;
        }
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

    public function getData()
    {
        return $this->data;
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

    public function getLimit()
    {
        return $this->limit;
    }

    public function setLimit($limit)
    {
        $this->limit = $limit;

        return $this;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function getPage()
    {
        return $this->page;
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

    public function setLength($length)
    {
        $this->length = $length;

        return $this;
    }

    protected function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'attr' => array('class' => 'pagination pull-right'),
        ));
    }
}
