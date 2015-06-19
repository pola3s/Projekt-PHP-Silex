<?php
/**
 * Files model
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
 * Class FilesModel
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
class FilesModel
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
     * Count amount of files
     *
     * @param Integer $limit
     *
     * @access public
     * @return Integer
     */
    public function countFilesPages($limit)
    {
        $pagesCount = 0;
        $sql = 'SELECT COUNT(*) as pages_count FROM files';
        $result = $this->_db->fetchAssoc($sql);
        if ($result) {
            $pagesCount = ceil($result['pages_count'] / $limit);
        }
        return $pagesCount;
    }
    
    /**
     *Get files page
     *
     * @param Integer $page
     * @param Integer $limit
     * @param Integer $pagesCount
     *
     * @access public
     * @return Array
     */
    public function getFilesPage($page, $limit, $pagesCount)
    {
        if (($page <= 1) || ($page > $pagesCount)) {
            $page = 1;
        }
        $sql = 'SELECT * 
                FROM files
				ORDER BY id_file DESC
                LIMIT :start, :limit';
        $statement = $this->_db->prepare($sql);
        $statement->bindValue('start', ($page - 1) * $limit, \PDO::PARAM_INT);
        $statement->bindValue('limit', $limit, \PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchAll();
    }
    
    /**
     * Gets one file.
     *
     * @param Integer $id
     *
     * @access public
     * @return Array
     */
    public function getFile($id)
    {
        if (($id != '') && ctype_digit((string)$id)) {
            $sql = 'SELECT * FROM files WHERE id_file = ?';
            return $this->_db->fetchAssoc($sql, array((int) $id));
        } else {
            return array();
        }
    }
    /**
     * Check if category id exists
     *
     * @param Integer $id
     *
     * @access public
     * @return Array
     */
    public function checkCategoryId($id)
    {
        $sql = 'SELECT id_category FROM files WHERE id_file=?';
        return $this->_db->fetchAssoc($sql, array((int)$id));
    }
    
    /**
     * Gets one category.
     *
     * @param Integer $id_category
     *
     * @access public
     * @return Array
     */
    public function getCategory($id_category)
    {
        $sql = 'SELECT name FROM categories WHERE id_category = ?';
        return $this->_db->fetchAssoc($sql, array((int)$id_category));
    }
    
    /**
     * Check if user id exists
     *
     * @param Integer $id
     *
     * @access public
     * @return Array
     */
    public function checkUserId($id)
    {
        $sql = 'SELECT id_user FROM files WHERE id_file=?';
        return $this->_db->fetchAssoc($sql, array((int)$id));
    }
    
    /**
     * Gets file uploader name.
     *
     * @param Integer $id_user
     *
     * @access public
     * @return Array
     */
    public function getFileUploaderName($id_user)
    {
        $sql = 'SELECT login FROM users WHERE id_user=?';
        return $this->_db->fetchAssoc($sql, array((int)$id_user));
    }
    /**
     * Create new name file
     *
     * @param String $name
     *
     * @access public
     * @return string
     */
    public function createName($name)
    {
        $newName = '';
        $ext = pathinfo($name, PATHINFO_EXTENSION);
        $newName = $this->randomString(32) . '.' . $ext;

        while (!$this->isUniqueName($newName)) {
            $newName = $this->randomString(32) . '.' . $ext;
        }

        return $newName;
    }
    
    /**
     * Get random string
     *
     * @param integer $length
     *
     * @access protected
     * @return string
     */
    protected function randomString($length)
    {
        $string = '';
        $keys = array_merge(range(0, 9), range('a', 'z'));
        for ($i = 0; $i < $length; $i++) {
            $string .= $keys[array_rand($keys)];
        }
        return $string;
    }
    
    /**
     * Check if name id unique
     *
     * @param String $name
     *
     * @access public
     * @return bool
     */
    protected function isUniqueName($name)
    {
        $sql = 'SELECT COUNT(*) AS files_count FROM files WHERE name = ?';
        $result = $this->_db->fetchAssoc($sql, array($name));
        return !$result['files_count'];
    }
    /**
     * Check if user is logged
     *
     * @param Application $app
     *
     * @access protected
     * @return bool
     */
    protected function isLoggedIn(Application $app)
    {
        if (null === $user = $app['session']->get('user')) {
            return false;
        } else {
            return true;
        }
    }
    
    /**
     * Save file
     *
     * @param
     * $name String $name Array $data
     *
     * @access public
     * @return void
     */
    public function saveFile($name, $data)
    {
        $sql = 'INSERT INTO `files` (`name`,`title`, `id_category`, `description`, `id_user`) VALUES (?,?,?,?,?)';
        $this->_db->executeQuery($sql, array(
                $name,
                $data['title'],
                $data['category'],
                $data['description'],
                $data['id_user']
                ));
    }
    /**
     * Updates file
     *
     * @param Array $data, String $name
     *
     * @access public
     * @return void
     */
    public function editFile($name, $data)
    {
        if (isset($data['id_file']) && ctype_digit((string)$data['id_file'])) {
            $sql = 'UPDATE files SET title = ?, description = ?, id_category = ?, id_user = ? WHERE id_file = ?';
            $this->_db->executeQuery(
                $sql,
                array(
                    $data['title'],
                    $data['description'],
                    $data['category'],
                    $data['id_user'],
                    $data['id_file']
                )
            );
        } else {
            $sql = 'INSERT INTO `files` (`title`, `description`, `id_category`,  `id_user`) VALUES (?,?,?,?)';
            $this->_db->executeQuery(
                $sql,
                array(
                    $data['title'],
                    $data['description'],
                    $data['name'],
                    $data['id_user']
                )
            );
        }
    }
    
    /**
     * Check if photo name exists
     *
     * @param String $name
     *
     * @access public
     * @return bool
     */
    public function checkFileName($name)
    {
        $sql = 'SELECT * FROM files WHERE name=?';
        $result = $this->_db->fetchAll($sql, array($name));

        if ($result) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Get file by name
     *
     * @param String $name
     *
     * @access public
     * @return Array
     */
    public function getFileByName($name)
    {
        $sql = 'SELECT * FROM files WHERE name=?';
        return $this->_db->fetchAssoc($sql, array($name));
    }
    
    /**
     * Remove file
     *
     * @param String $name
     *
     * @access public
     * @return void
     */
    public function removeFile($name)
    {
        $sql = 'DELETE FROM `files` WHERE name = ?';
        $this->_db->executeQuery($sql, array($name));
    }

    
    /**
     *
     * Search one file
     *
     * @param String $name
     *
     * @access public
     * @return Array
     */
    public function searchFile($name)
    {
        $sql = 'SELECT * FROM files WHERE id_category = ?';
        return $this->_db-> fetchAll($sql, array($name));
    }
    
    /**
     * Check if file id exists
     *
     * @param Integer $id
     *
     * @access public
     * @return bool
     */
    public function checkFileId($id)
    {
        $sql = 'SELECT * FROM files WHERE id_file=?';
        $result = $this->_db->fetchAll($sql, array($id));

        if ($result) {
            return true;
        } else {
            return false;
        }
    }
}
