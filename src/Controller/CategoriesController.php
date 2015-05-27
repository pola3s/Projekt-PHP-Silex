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
		$categoriesController->match('', array($this, 'index'))
				
				->bind('categories');
		$categoriesController->match('/add/', array($this, 'add'))
            ->bind('/categories/add');
        $categoriesController->match('/edit/{id_category}', array($this, 'edit'))
            ->bind('/categories/edit');
        $categoriesController
            ->match('/delete/{id_category}', array($this, 'delete'))
            ->bind('/categories/delete');
     
        return $categoriesController;
    }
		
     public function index(Application $app)
    {
        $categoriesModel = new CategoriesModel($app);
        $categories = $categoriesModel->getCategories();
		
        return $app['twig']->render('categories/index.twig', 
			array(
				'categories' => $categories
			)
		);
    }


}
	
