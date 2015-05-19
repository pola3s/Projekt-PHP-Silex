<?php

 
namespace Model;

use Silex\Application;


class GradesModel
{
    protected $_db;

    public function __construct(Application $app)
    {
        $this->_db = $app['db'];
    }

    public function getGrades($id)
    {
		$sql = 'SELECT AVG(grade) FROM grades WHERE id_file = ?;';
		return $this->_db->fetchAssoc($sql, array($id));
    }
	
	public function getGradesList()
    {
        $sql = 'SELECT * FROM 12_serwinska.values;';
        return $this->_db->fetchAll($sql);
    }

	public function addGrade($data)
    {
        $sql = 'INSERT INTO grades VALUES (?,?,?,?)';
        $this->_db->executeQuery(
            $sql, array($data['id_grade'], $data['grade'], $data['id_user'],
            $data['id_file'])
        );
    }
	
  
    public function getGradesDict()
    {
        $grades = $this->getGradesList();
        $data = array();
        foreach ($grades as $row) {
            $data[$row['id_grade']] = $row['value'];
        }
        return $data;
    }
	
}