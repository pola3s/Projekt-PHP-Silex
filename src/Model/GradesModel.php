<?php
/**
 * Grades model
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

/**
 * Class GradesModel
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

class GradesModel
{

   
/**
     * Database access object.
     *
     * @access protected
     * @var $_db Doctrine\DBAL
     */
    protected $db;
    
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
     * Gets all grades of one file
     *
     * @param Integer $id
     * @access public
     * @return Array
     */
    public function getGrades($id)
    {
        $sql = 'SELECT AVG(grade) FROM grades WHERE id_file = ?;';
        return $this->_db->fetchAssoc($sql, array($id));
    }
    
    
    /**
     * Change key in grades array
     *
     * @access public
     * @return Array
     */
    public function getGradesDict()
    {
        $grades = $this->getGradesList();
        $data = array();
        foreach ($grades as $row) {
            $data[$row['id_grade']] = $row['value'];
        }
        return $data;
    }
    
        
    /**
     * Gets all grades
     *
     * @access public
     * @return Array
     */
    public function getGradesList()
    {
        $sql = 'SELECT * FROM 12_serwinska.values;';
        return $this->_db->fetchAll($sql);
    }
    
    /**
     * Checks if grade is added by one user to one file
     *
     * @param Integer $id_file
     * @param Integer $id_user
     *
     * @access public
     * @return Array
     */
    public function checkGrade($id_file, $id_user)
    {
         $sql = 'SELECT * FROM grades WHERE id_file = ? AND id_user = ? ';
         return $this->_db->fetchAssoc($sql, array($id_file, $id_user));
    }
    
	  public function checkGradeAdded($id, $id_user)
    {
         $sql = 'SELECT * FROM grades WHERE id_file = ? AND id_user = ? ';
         return $this->_db->fetchAssoc($sql, array($id, $id_user));
    }
	
	
	public function checkUserId($id_user)
    {
        $sql = 'SELECT * FROM users WHERE id_user=?';
        $result = $this->_db->fetchAll($sql, array($id_user));

        if ($result) {
            return true;
        } else {
            return false;
        }
    }
	
    /**
     * Adds grade to one file
     *
     * @param Array $data
     *
     * @access public
     * @return void
     */
    public function addGrade($data)
    {
        $sql = 'INSERT INTO grades VALUES (?,?,?,?)';
        $this->_db->executeQuery(
            $sql,
            array($data['id_grade'], $data['grade'], $data['id_user'],
            $data['id_file'])
        );
    }
}
