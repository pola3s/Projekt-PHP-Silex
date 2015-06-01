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
		$sql = 'SELECT * FROM about WHERE id_user = ?;';
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

	
 
	
}