<?php

 
namespace Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Model\UsersModel;
use Model\FilesModel;
use Model\CategoriesModel;
 
class CategoriesController implements ControllerProviderInterface
{
   
    protected $_model;

	protected $_user;

    protected $_files;

    public function connect(Application $app)
    {
        $this->_model = new CategoriesModel($app);
        $this->_user = new UsersModel($app);
        $this->_files = new FilesModel($app);
        $categoriesController = $app['controllers_factory'];
        return $categoriesController;
    }

}
	
