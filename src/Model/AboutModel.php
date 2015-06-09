<?php

 
namespace Model;

use Silex\Application;


class AboutModel
{
    protected $_db;

    public function __construct(Application $app)
    {
        $this->_db = $app['db'];
    }

    public function getAbout($id)
    {
		$sql = 'SELECT * FROM abouts WHERE id_user = ?;';
		return $this->_db->fetchAssoc($sql, array($id));
    }
	
	
	public function addAbout($data)
    {
        $sql = 'INSERT INTO abouts
            (email, phone, description, website, city, id_user) 
            VALUES (?,?,?,?,?,?)';
        $this->_db
            ->executeQuery(
                $sql, 
                array(
                    $data['email'], 
                    $data['phone'], 
                    $data['description'], 
					$data['website'], 
					$data['city'], 
                    $data['id_user']
                )
            );
    }

	public function checkUserId($iduser)
    {
        $sql = 'SELECT * FROM users WHERE id_user=?';
        $result = $this->_db->fetchAll($sql, array($iduser));

        if ($result) {
            return true;
        } else {
            return false;
        }
    }

	public function saveAbout($data, $id_user)
    {
        $sql = 'INSERT INTO abouts
            (email, phone, description, website, city, id_user) 
            VALUES (?,?,?,?,?, ?)';
        $this->_db
            ->executeQuery(
                $sql, 
                array(
                    $data['email'], 
                    $data['phone'], 
                    $data['description'], 
                    $data['website'],
					$data['city'],
					$id_user
                )
            );
    }
	
	public function editAbout($data, $id_user)
	{
		if (isset($data['id_user']) && ctype_digit((string)$data['id_user'])) {
		   $sql = 'UPDATE abouts SET email = ?, phone = ?, description = ?, website = ?, city = ? WHERE id_user = ?';
		   $this->_db->executeQuery($sql, array( 
						$data['email'], 
						$data['phone'], 
						$data['description'], 
						$data['website'],
						$data['city'],
						$id_user
						
						)
					);
		} else {
		   $sql = 'INSERT INTO `abouts` (`email`, `phone`, `description`, `website`, `city` ) VALUES (?, ?, ?, ?, ?)';
		   $this->_db->executeQuery($sql, array(
							$data['email'], 
							$data['phone'], 
							$data['description'], 
							$data['website'],
							$data['city']
							
							)
						);
		}
	}

	
 
	
}