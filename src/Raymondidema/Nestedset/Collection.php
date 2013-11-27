<?php namespace Raymondidema\Nestedset;

use \Illuminate\Database\Eloquent\Collection as BaseCollection;

class Collection extends BaseCollection {

    /**
     * Convert list of nodes to dictionary with specified key.
     *
     * If no key is specified then "parent_id" is used.
     *
     * @param string $key 
     *
     * @return  array
     */
    public function toDictionary($key = null)
    {
        if (empty($this->items)) {
            return array();
        }

        if ($key === null) {
            $key = $this->first()->getParentIdName();
        }

        $result = array();

        foreach ($this->items as $item) {
            $result[$item->$key][] = $item;
        }

        return $result;
    }

    /**
     * Build tree from node list.
     *
     * To succesfully build tree "id", "_lft" and "parent_id" keys must present.
     * 
     * If {@link rootNodeId} is provided, the tree will contain only descendants
     * of the node with such primary key value.
     * 
     * @param integer $rootNodeId
     *
     * @return  Collection
     */
    public function toTree($rootNodeId = null)
    {
        $dictionary = $this->toDictionary();
        $result = new static();

        // If root node is not specified we take parent id of node with
        // least lft value as root node id.
        if ($rootNodeId === null) 
        {
            $leastValue = null;

            foreach ($this->items as $item) {
                if ($leastValue === null || $item->getLft() < $leastValue)
                {
                    $leastValue = $item->getLft();
                    $rootNodeId = $item->getParentId();
                }
            }
        }

        $result->items = isset($dictionary[$rootNodeId]) ? $dictionary[$rootNodeId] : array();

        if (empty($result->items)) 
        {
            return $result;
        }

        foreach ($this->items as $item) 
        {
            $key = $item->getKey();

            $children = new BaseCollection(isset($dictionary[$key]) ? $dictionary[$key] : array());
            $item->setRelation('children', $children);
        }

        return $result;
    }
}