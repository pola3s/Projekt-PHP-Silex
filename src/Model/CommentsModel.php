<?php
/**
 * Comments model
 *
 * PHP version 5
 *
 * @author   Paulina Serwińska <paulina.serwinska@gmail.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     wierzba.wzks.uj.edu.pl/~12_serwinska
 */
 
 
namespace Model;

use Doctrine\DBAL\DBALException;
use Silex\Application;

/**
 * Class CommentsModel
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

class CommentsModel
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
     * Get all comments for one file
     *
     * @param Integer $id_file
     *
     * @access public
     * @return Array
     */
    public function getCommentsList($id_file)
    {
        $sql = 'SELECT * FROM comments WHERE id_file = ?';
        return $this->_db->fetchAll($sql, array($id_file));
    }

    /**
     * Add one comment.
     *
     * @param  Array $data
     *
     * @access public
     * @return Void
     */
    public function addComment($data)
    {
        $sql = 'INSERT INTO comments 
            (content, published_date, id_file, id_user) 
            VALUES (?,?,?,?)';
        $this->_db
            ->executeQuery(
                $sql,
                array(
                    $data['content'],
                    $data['published_date'],
                    $data['id_file'],
                    $data['id_user']
                )
            );
    }
    /**
     * Check if comment id exists
     *
     * @param Integer $idcomment
     *
     * @access public
     * @return bool
     */
    public function checkCommentId($idcomment)
    {
        $sql = 'SELECT * FROM comments WHERE id_comment=?';
        $result = $this->_db->fetchAll($sql, array($idcomment));

        if ($result) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Gets one comment.
     *
     * @param Integer $id_comment
     *
     * @access public
     * @return Array
     */
    public function getComment($id_comment)
    {
        $sql = 'SELECT * FROM comments WHERE id_comment = ? LIMIT 1';
        return $this->_db->fetchAssoc($sql, array($id_comment));
    }
    
    /**
     * Updates one comment.
     *
     * @param Array $data
     * @patam Integer $id_comment
     *
     * @access public
     * @return Void
     */
    public function editComment($data, $id_comment)
    {

        if (isset($data['id_comment'])
        && ctype_digit((string)$data['id_comment'])) {
            $sql = 'UPDATE comments 
                SET content = ?
            WHERE id_comment = ?';
            $this->_db->executeQuery(
                $sql,
                array(
                    $data['content'],
                    $id_comment
                )
            );
        } else {
            $sql = 'INSERT INTO comments 
                (content) 
            VALUES (?)';
            $this->_db
                ->executeQuery(
                    $sql,
                    array(
                        $data['content'],
                    )
                );
        }
    }

    /**
     * Delete one comment.
     *
     * @param Array $data
     *
     * @access public
     * @return Void
     */
    public function deleteComment($data)
    {
        $sql = 'DELETE FROM `comments` WHERE `id_comment`= ?';
        $this->_db->executeQuery($sql, array($data['id_comment']));
    }
}
