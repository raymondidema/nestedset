<?php

class Category extends Raymondidema\Nestedset\Node { 
    protected $fillable = array('name', 'parent_id');

    public $timestamps = false;
}