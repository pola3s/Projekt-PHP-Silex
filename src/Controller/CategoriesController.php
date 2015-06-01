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
        $categoriesController->match('/edit/{id}', array($this, 'edit'))
            ->bind('/categories/edit');
        $categoriesController
            ->match('/delete/{id}', array($this, 'delete'))
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

	public function add(Application $app, Request $request)
    {

     

        $form = $app['form.factory']->createBuilder('form', $data)
            ->add('name', 'text', array(
                'constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 2)))
            ))
           
            ->getForm();

       
		$form->handleRequest($request);

			  if ($form->isValid()) {
				  $categoriesModel = new CategoriesModel($app);
				  $data = $form->getData();
				
				 $categoriesModel->addCategory($data);
				 return $app->redirect($app['url_generator']->generate('categories'), 301);
			  }
				return $app['twig']
					->render('categories/add.twig', array('form' => $form->createView()));
	}
	
	public function edit(Application $app, Request $request)
    {
			$categoriesModel = new CategoriesModel($app);
			$id_category = (int) $request->get('id', 0);
			
			$category = $categoriesModel->getCategory($id_category);
		
			
			
			
			if (count($category)) {
				$form = $app['form.factory']->createBuilder('form', $category)
					
					->add('name', 'text', array(
						'constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 2)))
						)
					)
					
					->add('save', 'submit')
					->getForm();
					
			$form->handleRequest($request);
			
			if ($form->isValid()) {
					$categoriesModel = new CategoriesModel($app);
					$data = $form->getData();
					$categoriesModel->saveCategory2($data, $id_category);
					
					return $app->redirect($app['url_generator']->generate('categories'), 301);
			}
					return $app['twig']->render('categories/edit.twig', array('form' => $form->createView(), 'category' => $category));
					
			} else {
			
					return $app->redirect($app['url_generator']->generate('/categories/add'), 301);
			}

	}
	
	public function delete(Application $app, Request $request)
    {
        $id_category = (int) $request -> get('id', 0);
        $categoriesModel = new CategoriesModel($app);
        $categoriesModel -> deleteCategory($id_category);
        return $app->redirect($app['url_generator']->generate('categories'), 301);
    }
	
}
	
