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
        $sql = 'SELECT * FROM categories';
        return $this->_db->fetchAll($sql);
    }
	
	public function addCategory($data)
    {
        $sql = 'INSERT INTO categories (name) VALUES (?)';
        $this->_db->executeQuery(
            $sql, array(
				$data['name']
			)
        );
    }
	
	public function getCategory($id_category)
	{
		if (($id_category != '') && ctype_digit((string)$id_category)) {
			$sql = 'SELECT id_category, name FROM categories WHERE id_category= ?';
			return $this->_db->fetchAssoc($sql, array((int) $id_category));
		} else {
			return array();
		}
	}
	
	public function editCategory($data, $id_category)
	{
		if (isset($data['id_category']) && ctype_digit((string)$data['id_category'])) {
		   $sql = 'UPDATE categories SET name = ? WHERE id_category = ?';
		   $this->_db->executeQuery($sql, array($data['name'], $data['id_category']));
		} else {
		   $sql = 'INSERT INTO `categories` (`name`) VALUES (?)';
		   $this->_db->executeQuery($sql, array($data['name']));
		}
	}
	
	public function deleteCategory($id_category)
    {
        $sql = 'DELETE FROM categories WHERE id_category = ?';
        $this->_db->executeQuery($sql, array($id_category));
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
	
	public function getCategoriesList()
    {
        $sql = 'SELECT id_category, name FROM categories';
        return $this->_db->fetchAll($sql);
    }

	public function getCategoryName($id_category)
    {
        $sql = 'SELECT name FROM categories WHERE id_category = ? ';
        return $this->_db->fetchAssoc($sql, array($id_category));
    }
	
	public function checkCategoryId($id_category)
    {
        $sql = 'SELECT * FROM categories WHERE id_category=?';
        $result = $this->_db->fetchAll($sql, array($id_category));

        if ($result) {
            return true;
        } else {
            return false;
        }
    }

	
}