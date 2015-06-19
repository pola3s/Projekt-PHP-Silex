<?php
/**
 * Users model
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
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;

/**
 * Class CategoriesModel
 *
 * @category Model
 * @package  Model
 * @author   Paulina Serwińska <paulina.serwinska@gmail.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version  Release: <package_version>
 * @link     wierzba.wzks.uj.edu.pl/~12_serwinska
 * @uses     Doctrine\DBAL\DBALException
 * @uses     Silex\Application
 * @uses     Symfony\Component\Security\Core\Exception\UnsupportedUserException;
 * @uses     Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
 * @uses     Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;
 */

class UsersModel
{
    /**
     * Silex application object
     *
     * @access protected
     * @var    $_app Silex\Application
     */
    protected $app;
   
    /**
     * Database access object.
     *
     * @access protected
     * @var    $_db Doctrine\DBAL
     */
    protected $db;
    
    /**
     * Constructor
     *
     * @param Application $app
     *
     * @access public
     * @return void
     */
    public function __construct(Application $app)
    {
        $this->_app = $app;
        $this->_db = $app['db'];
    }

    /**
     * Puts one user to database.
     *
     * @param Array $data, String $password
     *
     * @access public
     * @return Void
     */
    public function register($data, $password)
    {
        $role = 2;
        $query = 'INSERT INTO `users`
                  (`login`, `email`, `password`, `firstname`, `lastname`)
                  VALUES (?, ?, ?, ?, ?)';
        $this->_db->executeQuery(
            $query,
            array(
                $data['login'],
                $data['email'],
                $password,
                $data['firstname'],
                $data['lastname']
            )
        );

        $queryTwo = "SELECT * FROM users
                   WHERE login =\"".$data['login']."\";";
        $user = $this->_db->fetchAssoc($queryTwo);

        $queryThree = 'INSERT INTO users_roles (`id`,`id_user`, `id_role` )
                   VALUES (NULL, ?, ?)';
        $this->_db->executeQuery($queryThree, array($user['id_user'], $role));
    }

    /**
     * Load user by login.
     *
     * @param String $login
     *
     * @access public
     * @return Array
     */
    public function loadUserByLogin($login)
    {
        $data = $this->getUserByLogin($login);

        if (!$data) {
            throw new UsernameNotFoundException(
                sprintf(
                    'Username "%s" does not exist.',
                    $login
                )
            );
        }

        $roles = $this->getUserRoles($data['id_user']);
	
        if (!$roles) {
            throw new UsernameNotFoundException(
                sprintf(
                    'Username "%s" does not exist.',
                    $login
                )
            );
        }

        $user = array(
            'login' => $data['login'],
            'password' => $data['password'],
            'roles' => $roles
        );

        return $user;
    }
    
    /**
     * Get user by login.
     *
     * @param String $login
     *
     * @access public
     * @return Array
     */
    public function getUserByLogin($login)
    {
        $sql = 'SELECT * FROM users WHERE login = ?';
        return $this->_db->fetchAssoc($sql, array((string) $login));
    }

    /**
     * Get user's role.
     *
     * @param String $userId
     *
     * @access public
     * @return Array
     */
    public function getUserRoles($userId)
    {
			 $sql = '
			SELECT
					roles.name
			FROM
					users_roles
			INNER JOIN
					roles
			ON users_roles.id_role=roles.id_role
			WHERE
					users_roles.id_user = ?
			';
		
        $result = $this->_db->fetchAll($sql, array((string) $userId));
		
        $roles = array();
        foreach ($result as $row) {
            $roles[] = $row['name'];
        }
		
        return $roles;
    }
    
    /**
     * Get current logged user id
     *
     * @param $app
     *
     * @access public
     * @return mixed
     */
    public function getIdCurrentUser($app)
    {

        $login = $this->getCurrentUser($app);
        $id_user = $this->getUserByLogin($login);

        return $id_user['id_user'];


    }
    
    /**
     * Get information about actual logged user
     *
     * @param $app
     *
     * @access protected
     * @return mixed
     */
    protected function getCurrentUser($app)
    {
        $token = $app['security']->getToken();

        if (null !== $token) {
            $user = $token->getUser()->getUsername();
        }

        return $user;
    }
    
    /**
     * Get information about user
     *
     * @param $id
     *
     * @access public
     * @return Array
     */
    public function getUser($id)
    {
        if (($id != '') && ctype_digit((string)$id)) {
            $sql = 'SELECT id_user, login, email, firstname, lastname FROM users WHERE id_user = ?';
            return $this->_db->fetchAssoc($sql, array((int) $id));
        } else {
            return array();
        }
    }
    /**
     * Get one user's files
     *
     * @param $id
     *
     * @access public
     * @return Array
     */
    public function getFileByUser($id)
    {
        $sql = 'SELECT * FROM files WHERE id_user= ?  ORDER BY id_file DESC ';
        return $this->_db->fetchAll($sql, array($id));
    }
    
    /**
     * Get one user's about
     *
     * @param Integer $id_user
     *
     * @access public
     * @return Array
     */
    public function getAboutByUser($id_user)
    {
        $sql = 'SELECT * FROM abouts WHERE id_user= ?';
        return $this->_db->fetchAssoc($sql, array($id_user));
    }
    
    /**
     * Check if user is logged
     *
     * @param Application $app
     *
     * @access public
     * @return bool
     */
    public function isLoggedIn(Application $app)
    {
        if ('anon.' !== $user = $app['security']->getToken()->getUser()) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Get list of all users
     *
     * @access public
     * @return Array
     */
    public function getUserList()
    {
        $sql = 'SELECT * FROM users';
        return $this->_db->fetchAll($sql);
    }
	

	
	 public function getUserRole($id_user)
    {
        $sql = 'SELECT * FROM users_roles WHERE id_user =?';
         return $this->_db->fetchAssoc($sql, array($id_user));
    }
	
	 public function getRoleName($id_role)
    {
        $sql = 'SELECT * FROM roles WHERE id_role =?';
         return $this->_db->fetchAssoc($sql, array($id_role));
    }
	
    
    /**
     * Checks if user's id exist
     *
     * @param Integer $id_user
     *
     * @access public
     * @return bool
     */
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
     * Updates information about user.
     *
     * @param Array $data Associative array contains all necessary information
     *
     * @access public
     * @return Void
     */
    public function updateUser($id, $data, $password)
    {
        if (isset($id) && ctype_digit((string)$id)) {
            $query = 'UPDATE `users`
                  SET `login`= ?,
                      `email`= ?,
                      `password`= ?,
                      `firstname`= ?,
                      `lastname`= ?
                  WHERE `id_user`= ?';

            $this->_db->executeQuery(
                $query,
                array(
                $data['login'],
                $data['email'],
                $password,
                $data['firstname'],
                $data['lastname'],
                $id
                )
            );
        } else {
        }

    }
	
	  public function editRole($id_role, $id_user)
    {
      
            $sql = 'UPDATE users_roles SET id_role = ? WHERE id_user = ?';
            $this->_db->executeQuery(
                $sql,
                array(	
						$id_role,
                        $id_user

                        )
            );
       
    }
	
}
