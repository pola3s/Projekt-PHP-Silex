<?php

 
namespace Model;

use Silex\Application;


class CategoriesModel
{
    protected $_db;

    public function __construct(Application $app)
    {
        $this->_db = $app['db'];
    }

    public function getCategories()
    {
		$sql = 'SELECT name FROM categories;';
		return $this->_db->fetchAll($sql);
    }
	
	

	 
	
}