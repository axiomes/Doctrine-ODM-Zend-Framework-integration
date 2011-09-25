<?php

namespace Axiomes\Paginator\Adapter;
use Doctrine\ODM\MongoDB\Query\Builder;

class Odm implements \Zend_Paginator_Adapter_Interface{

    /**
     * @var \Doctrine\MongoDB\Query\Builder
     */
    protected $qb;

    /**
     * internal result's count cache
     * @var int
     */
    protected $count;

    /**
     * @param \Doctrine\ODM\MongoDB\Query\Builder $qb
     */
    public function __construct(Builder $qb = null){
        $this->qb = $qb;
    }

    /**
     * @param \Doctrine\ODM\MongoDB\Query\Builder|null $qb
     * @return \Axiomes\Paginator\Adapter\Odm
     */
    public function setQueryBuilder(Builder $qb = null){
        $this->qb = $qb;
        return $this;
    }
    
    /**
     * Returns an collection of items for a page.
     *
     * @param  integer $offset Page offset
     * @param  integer $itemCountPerPage Number of items per page
     * @return array
     */
    public function getItems($offset, $itemCountPerPage)
    {
        return $this->qb->skip($offset)->limit($itemCountPerPage)->getQuery()->getIterator();
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     */
    public function count()
    {
        if(is_null($this->count)) $this->count = $this->qb->getQuery()->count();
        return $this->count;
    }
}
