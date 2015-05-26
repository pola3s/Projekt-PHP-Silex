<?php

namespace Model;

use Doctrine\DBAL\DBALException;
use Silex\Application;

class FilesModel
{

    protected $_db;
	
	
    public function __construct(Application $app)
    {
        $this->_db = $app['db'];
    }

    public function saveFile($name, $data)
    {
        $sql = 'INSERT INTO `files` (`name`,`title`, `category`, `description`, `id_user`) VALUES (?,?,?,?,?)';
        $this->_db->executeQuery($sql, array(
				$name,
				$data['title'],
				$data['category'],
				$data['description'],
				$data['id_user']
				));
    }
	
	public function saveFile2($name, $data)
	{
    if (isset($data['id_file']) && ctype_digit((string)$data['id_file'])) {
       $sql = 'UPDATE files SET title = ?, description = ?, category =?, id_user = ? WHERE id_file = ?';
       $this->_db->executeQuery($sql, array($data['title'], $data['description'], $data['category'], $data['id_user'], $data['id_file']));
    } else {
       $sql = 'INSERT INTO `files` (`title`, `category`, `description`, `id_user`) VALUES (?,?,?,?)';
       $this->_db->executeQuery($sql, array($data['title'], $data['description'], $data['category'], $data['id_user']));
    }
	}

    public function createName($name)
    {
        $newName = '';
        $ext = pathinfo($name, PATHINFO_EXTENSION);
        $newName = $this->_randomString(32) . '.' . $ext;

        while(!$this->_isUniqueName($newName)) {
            $newName = $this->_randomString(32) . '.' . $ext;
        }

        return $newName;
    }

    protected function _randomString($length)
    {
        $string = '';
        $keys = array_merge(range(0, 9), range('a', 'z'));
        for ($i = 0; $i < $length; $i++) {
            $string .= $keys[array_rand($keys)];
        }
        return $string;
    }

    protected function _isUniqueName($name)
    {
        $sql = 'SELECT COUNT(*) AS files_count FROM files WHERE name = ?';
        $result = $this->_db->fetchAssoc($sql, array($name));
        return !$result['files_count'];
    }

	protected function _isLoggedIn(Application $app)
{
    if (null === $user = $app['session']->get('user')) {
        return false;
    } else {
        return true;
    }
}
	
	public function getFiles()
    {
        $sql = 'SELECT * FROM files';
        return $this->_db->fetchAll($sql);
	}
	
	public function getUserByLogin($login)
	{
	$sql = 'SELECT * FROM users WHERE login = ?';
	return $this->_db->fetchAssoc($sql, array((string) $this->_app->escape($login)));
	}
	
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
	
	public function getFilesPage($page, $limit, $pagesCount)
    {
        if (($page <= 1) || ($page > $pagesCount)) {
            $page = 1;
        }
        $sql = 'SELECT * 
                FROM files 
                LIMIT :start, :limit';
        $statement = $this->_db->prepare($sql);
        $statement->bindValue('start', ($page - 1) * $limit, \PDO::PARAM_INT);
        $statement->bindValue('limit', $limit, \PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchAll();
    }
	
	 public function getFile($id)
    {
        if (($id != '') && ctype_digit((string)$id)) {
            $sql = 'SELECT * FROM files WHERE id_file = ?';
            return $this->_db->fetchAssoc($sql, array((int) $id));
        } else {
            return array();
        }
    }
	
	
	
	 public function getUserByFile($id)
    {
        if (($id != '') && ctype_digit((string)$id)) {
            $sql = 'SELECT * FROM files WHERE id_file = ?';
            return $this->_db->fetchAssoc($sql, array((int) $id));
        } else {
            return array();
        }
    }

	
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
	
	public function checkUserId($id)
    {
        $sql = 'SELECT id_user FROM files WHERE id_file=?';
        return $this->_db->fetchAssoc($sql, array((int)$id));
    }
	
	 public function getFileUploaderName($id_user)
    {
        $sql = 'SELECT login FROM users WHERE id_user=?';
        return $this->_db->fetchAssoc($sql, array((int)$id_user));
    }
	
	public function checkCategoryId($id)
    {
        $sql = 'SELECT category FROM files WHERE id_file=?';
        return $this->_db->fetchAssoc($sql, array((int)$id));
    }
	
	public function getCategory($id_category)
    {
        $sql = 'SELECT name FROM categories WHERE id_category = ?';
        return $this->_db->fetchAssoc($sql, array((int)$id_category));
    }
	
	
	
	
	
	 public function getUserById($id)
    {
        $sql = 'SELECT * FROM users WHERE `id_user` = ? Limit 1';
        return $this->_db->fetchAssoc($sql, array((int)$id));
    }
	
	
	
	 public function getFileByName($name)
    {
        $sql = 'SELECT * FROM files WHERE name=?';
        return $this->_db->fetchAssoc($sql, array($name));
    }
	
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

	

	
	 public function removeFile($name)
    {
        $sql = 'DELETE FROM `files` WHERE name = ?';
        $this->_db->executeQuery($sql, array($name));
    }
	

	
	// public function saveFile2($data)
	// {
		// if (isset($data['id_file']) && ctype_digit((string)$data['id_file'])) {
			// $sql = 'UPDATE files SET title = ?, category = ?,  description = ? WHERE id_file = ?';
			// $this->_db->executeQuery($sql, array($data['title'], $data['category'], $data['description'], $data['id_file']));
		// } else {
			// $sql = 'INSERT INTO files (title, category, description) VALUES (?,?)';
			// $this->_db->executeQuery($sql, array($data['title'], $data['category'], $data['description']));
		// }
	// }
	
	public function getFileByUser($id)
    {
        $sql = 'SELECT * FROM files WHERE id_user= ?';
        return $this->_db->fetchAssoc($sql, array($file));
    }
	
	
		
	
	 
	 
	


	
		
      
	
}