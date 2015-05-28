<?php

 
namespace Model;

use Silex\Application;


class CommentsModel
{
    protected $_db;

    public function __construct(Application $app)
    {
        $this->_db = $app['db'];
    }

    public function getComment($idcomment)
    {
        $sql = 'SELECT * FROM comments WHERE id_comment = ? LIMIT 1';
        return $this->_db->fetchAssoc($sql, array($idcomment));
    }

    public function getCommentsList($id_file)
    {
        $sql = 'SELECT * FROM comments WHERE id_file = ?';
        return $this->_db->fetchAll($sql, array($id_file));
    }

  
     

  
    public function editComment($data)
    {

        if (isset($data['id_comment']) 
        && ctype_digit((string)$data['id_comment'])) {
            $sql = 'UPDATE comments 
                SET content = ?, published_date = ? 
            WHERE id_comment = ?';
            $this->_db->executeQuery(
                $sql, array(
                    $data['content'], 
                    $data['published_date'], 
                    $data['id_comment']
                )
            );
        } else {
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
    }

 
    public function deleteComment($data)
    {
        $sql = 'DELETE FROM `comments` WHERE `id_comment`= ?';
        $this->_db->executeQuery($sql, array($data['id_comment']));
    }

 
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
	
	 public function checkFileId($id_file)
    {
        $sql = 'SELECT * FROM files WHERE id_file=?';
        $result = $this->_db->fetchAll($sql, array($id_file));

        if ($result) {
            return true;
        } else {
            return false;
        }
    }
	
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
    
}