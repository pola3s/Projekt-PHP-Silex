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

    public function getCategoriesDict()
    {
        $categories = $this->getCategories();
        $data = array();
        foreach ($categories as $row) {
            $data[$row['id_category']] = $row['name'];
        }
        return $data;
    }
	
	public function getCategories()
    {
        $sql = 'SELECT * FROM categories';
        return $this->_db->fetchAll($sql);
    }
	
	

	 
	
}