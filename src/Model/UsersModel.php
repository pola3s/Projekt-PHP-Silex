<?php

namespace Model;

use Doctrine\DBAL\DBALException;
use Silex\Application;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;


class UsersModel
{

   
    protected $_app;
   
    protected $_db;

    public function __construct(Application $app)
    {
        $this->_app = $app;
        $this->_db = $app['db'];
    }

   
    public function register($data, $password)
    {
        $role = 2;
        $query = 'INSERT INTO `users`
                  (`login`, `email`, `password`, `firstname`, `lastname`)
                  VALUES (?, ?, ?, ?, ?)';
        $this->_db->executeQuery(
            $query, array(
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

	 public function getUser($id)
    {
        if (($id != '') && ctype_digit((string)$id)) {
            $sql = 'SELECT * FROM users WHERE id_user = ?';
            return $this->_db->fetchAssoc($sql, array((int) $id));
        } else {
            return array();
        }
    }
	
	 public function getFileById($id)
    {
       $sql = 'SELECT * FROM files WHERE id_user = ?';
       return $this->_db->fetchAssoc($sql, array($id));
	   
	}
	
	public function getFileByUser($id)
    {
        $sql = 'SELECT * FROM files WHERE id_user= ?';
        return $this->_db->fetchAll($sql, array($id));
    }
	
	public function getAboutByUser($id)
    {
        $sql = 'SELECT * FROM abouts WHERE id_user= ?';
        return $this->_db->fetchAll($sql, array($id));
    }
	
	
	
	
   
    // public function register($data)
    // {
        // $check = $this->getUserByLogin($data['login']);

        // if (!$check) {
            // $users = "INSERT INTO `users` 
                // (`firstname`, `lastname`, `login`, `password`)
            // VALUES (?,?,?,?);";
            // $this->_db
                // ->executeQuery(
                    // $users,
                    // array(
                        // $data['firstname'],
                        // $data['lastname'],
                        // $data['login'],
                        // $data['password'])
                // );

            // $sql = "SELECT * 
                    // FROM users 
                    // WHERE login ='" . $data['login'] . "';";
            // $user = $this->_db->fetchAssoc($sql);

            // $addRole = 'INSERT INTO users_roles ( id_user, id_role ) 
                // VALUES(?, ?)';
            // $this->_db->executeQuery($addRole, array($user['id_user'], 2));
        // }
    // }

    
      


    public function changePassword($data, $id)
    {
        $sql = 'UPDATE `users` SET `password`=? WHERE `id_user`= ?';

        $this->_db->executeQuery($sql, array($data['new_password'], $id));
    }

    
    public function loadUserByLogin($login)
    {
        $data = $this->getUserByLogin($login);

        if (!$data) {
            throw new UsernameNotFoundException(
                sprintf('Username "%s" does not exist.', $login)
            );
        }

        $roles = $this->getUserRoles($data['id_user']);

        if (!$roles) {
            throw new UsernameNotFoundException(
                sprintf('Username "%s" does not exist.', $login)
            );
        }

        $user = array(
            'login' => $data['nickname'],
            'password' => $data['password'],
            'roles' => $roles
        );

        return $user;
    }

 
    public function getUserById($id)
    {
        $sql = 'SELECT * FROM users WHERE `id_user` = ? Limit 1';
        $this->_db->executeQuery($sql, array($user));
    }
	
	
    public function getUserById2($id_user)
    {
        $sql = 'SELECT * FROM users WHERE `id_user` = ?';
        return $this->_db->fetchAssoc($sql, array((string)$name));
    }

	public function login($data)
    {
        $user = $this->getUserByLogin($data['login']);

        if (count($user)) {
            if ($user['password'] == crypt($data['password'], $user['password'])) {
                return $user;
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    public function getUserByLogin($login)
    {
        $sql = 'SELECT * FROM users WHERE login = ?';
        return $this->_db->fetchAssoc($sql, array((string) $login));
    }

    public function getUserRoles($userId)
    {
        $sql = '
            SELECT
                roles.role
            FROM
                users_roles
            INNER JOIN
                roles
            ON users_roles.id_role=roles.id_role
            WHERE
                users_roles.id_user = ?
            ';

        $result = $this->_db->fetchAll($sql, array((string)$userId));

        $roles = array();
        foreach ($result as $row) {
            $roles[] = $row['role'];
        }

        return $roles;
    }

   
    public function addRole($iduser)
    {
        $sql = 'INSERT INTO `users_roles` (`id_user`, `id_role`) VALUES (?,?);';

        $this->_db->executeQuery($sql, array($iduser, '2'));
    }

   
    public function confirmUser($id)
    {
        $sql = 'UPDATE `users_roles` SET `id_role`="2" WHERE `id_user`= ?;';

        $this->_db->executeQuery($sql, array($id));
    }

    public function getIdCurrentUser($app)
    {

        $login = $this->getCurrentUser($app);
        $iduser = $this->getUserByLogin($login);

        return $iduser['id_user'];


    }
	
	
    public  function getUserList()
    {
        $sql = 'SELECT * FROM users';
        return $this->_db->fetchAll($sql);
    }

	
	 public function checkUserId($iduser)
    {
        $sql = 'SELECT * FROM users WHERE id_user=?';
        $result = $this->_db->fetchAll($sql, array($id));

        if ($result) {
            return true;
        } else {
            return false;
        }
    }
	
    

    protected function getCurrentUser($app)
    {
        $token = $app['security']->getToken();

        if (null !== $token) {
            $user = $token->getUser()->getUser();
        }

        return $user;
    }


    public function _isLoggedIn(Application $app)
    {
        if ('anon.' !== $user = $app['security']->getToken()->getUser()) {
            return true;
        } else {
            return false;
        }
    }
}