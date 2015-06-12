<?php
/**
 * Categories model
 *
 * PHP version 5
 *
 * @category Model
 * @package  Model
 * @author   Paulina Serwińska <paulina.serwinska@gmail.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     wierzba.wzks.uj.edu.pl/~12_serwinska
 */
 
 
namespace Model;

use Doctrine\DBAL\DBALException;
use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CategoriesModel
 *
 * @category Model
 * @package  Model
 * @author   Paulina Serwińska <paulina.serwinska@gmail.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version  Release: <package_version>
 * @link     wierzba.wzks.uj.edu.pl/~12_serwinska
 * @uses Doctrine\DBAL\DBALException
 * @uses Silex\Application
 */


class CategoriesModel
{
	/**
     * Database access object.
     *
     * @access protected
     * @var $_db Doctrine\DBAL
     */
    protected $_db;
	
	/**
     * Class constructor.
     *
     * @param Application $app Silex application object
     *
     * @access public
     */
    public function __construct(Application $app)
    {
        $this->_db = $app['db'];
    }
	
	/**
     * Gets all categories.
     *
     * @access public
     * @return Array
     */
	public function getCategories()
    {
        $sql = 'SELECT * FROM categories';
        return $this->_db->fetchAll($sql);
    }
	
	public function getCategoriesList()
    {
        $sql = 'SELECT name FROM categories';
        return $this->_db->fetchAll($sql);
    }
	
	
	/**
     * Adds new category
     *
     * @param Array $data 
     *
     * @access public
     * @return Void
     */
	public function addCategory($data)
    {
        $sql = 'INSERT INTO categories (name) VALUES (?)';
        $this->_db->executeQuery(
            $sql, array(
				$data['name']
			)
        );
    }
	
	/**
     * Gets one category.
     *
     * @param Integer $id_category
     *
     * @access public
     * @return array
     */
	public function getCategory($id_category)
	{
		if (($id_category != '') && ctype_digit((string)$id_category)) {
			$sql = 'SELECT id_category, name FROM categories WHERE id_category= ?';
			return $this->_db->fetchAssoc($sql, array((int) $id_category));
		}
	}
	
	public function getCategoryById($id_category)
	{
		$sql = 'SELECT category FROM categories WHERE id_category = ?';
		return $this->_db->fetchAssoc($sql, array((int) $id_category));
		
	}
	
	/**
     * Updates name of category.
     *
     * @param Array $data, Integer $id_category
     *
     * @access public
     * @return Void
     */
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
	
	
    /**
     * Delete category.
     *
     * @param Integer $id_category
     *
     * @access public
     * @return void
     */
	public function deleteCategory($id_category)
    {
        $sql = 'DELETE FROM categories WHERE id_category = ?';
        $this->_db->executeQuery($sql, array($id_category));
    }
	
    /**
     * Change key in categories array
     *
     * @access public
     * @return Array 
     */
	public function getCategoriesDict()
    {
        $categories = $this->getCategories();
		
        $data = array();
        foreach ($categories as $row) {
            $data[$row['id_category']] = $row['name'];
        }
        return $data;
    }
	
	public function getCategoriesDict2()
    {
        $categories = $this->getCategories2();
		
        $data = array();
        foreach ($categories as $row) {
            $data[$row['id_category']] = $row['name'];
        }
        return $data;
    }
	
	public function getCategories2()
    {
        $sql = 'SELECT DISTINCT categories.* FROM categories JOIN files ON files.id_category=categories.id_category';
        return $this->_db->fetchAll($sql);
    }
	
	// public function getCategories()
    // {
        // $sql = 'SELECT * FROM categories';
        // return $this->_db->fetchAll($sql);
    // }
	
	public function getCategoriesId()
    {
        
        $sql = 'SELECT DISTINCT id_category FROM files';
        return $this->_db->fetchAll($sql);
    }
	
	public function getCategoriesName($categoryId)
    {
        $sql = 'SELECT nameFROM categories WHERE id_category = ?';
        return $this->_db->fetchAll($sql, array($id_category));
		
	
    }
	
	/**
     * Gets one category's name
     *
     * @param Integer $id_category
     *
     * @access public
     * @return array
     */
	public function getCategoryName($id_category)
    {
        $sql = 'SELECT name FROM categories WHERE id_category = ? ';
        return $this->_db->fetchAssoc($sql, array($id_category));
    }
	
	/**
     * Check if category id exists
     *
     * @param Integer $id_category 
     *
     * @access public
     * @return bool 
     */
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

	public function addAction(Application $app, $data)
	{
		 $this->addCategory($data);
                  
            $app['session']->getFlashBag()->add(
                'message', array(
                      'type' => 'success',
                      'content' => 'Kategoria została dodana'
                  )
            );
            return $app->redirect(
                $app['url_generator']->generate(
                    'categories'
                ), 
                301
            );
	}
}