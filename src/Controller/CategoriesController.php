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
				  
				  $app['session']->getFlashBag()->add(
						'message', array(
							'type' => 'success',
                            'content' => 'Kategoria została dodana'
                        )
                    );
				  return $app->redirect($app['url_generator']->generate('categories'), 301);
			  }
				  return $app['twig']
					->render('/categories/add.twig', array('form' => $form->createView()));
	}
	
	public function edit(Application $app, Request $request)
    {
			$categoriesModel = new CategoriesModel($app);
			$id_category = (int) $request->get('id', 0);
			
			$category = $categoriesModel->getCategory($id_category);
			$check = $categoriesModel->checkCategoryId($id_category);
	
			if ($check) {
			
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
							$categoriesModel->editCategory($data, $id_category);
							
							$app['session']->getFlashBag()->add(
								'message', array(
									'type' => 'success',
									'content' => 'Kategoria została zmieniona'
								)
							);
							
							return $app->redirect($app['url_generator']->generate('categories'), 301);
					}		
							return $app['twig']->render('categories/edit.twig', array('form' => $form->createView(), 'category' => $category));
							
					} else {
					
							return $app->redirect($app['url_generator']->generate('/categories/add'), 301);
					}
			} else {
						$app['session']->getFlashBag()->add(
							'message', array(
								'type' => 'danger',
								'content' => 'Nie znaleziono kategroii!'
							)
						);
						return $app->redirect(
								$app['url_generator']->generate(
										'categories'
											
								), 301
							);
					}

	}
	
	public function delete(Application $app, Request $request)
    {
        $id_category = (int) $request -> get('id', 0);
		
		$categoriesModel = new CategoriesModel($app);
		$check = $categoriesModel->checkCategoryId($id_category);
	
		if ($check) {
		 $data = array();

                if (count($id_category)) {
                    $form = $app['form.factory']->createBuilder('form', $data)
                        ->add(
                            'id_category', 'hidden', array(
                            'data' => $id,
                            )
                        )
                        ->add('Yes', 'submit')
                        ->add('No', 'submit')
                        ->getForm();

                    $form->handleRequest($request);

                    if ($form->isValid()) {
                        if ($form->get('Yes')->isClicked()) {
                            $data = $form->getData();
                            try {
                                $model = $this->_model->deleteCategory($id_category);

                                $app['session']->getFlashBag()->add(
                                    'message', array(
                                        'type' => 'success',
                                        'content' => 
                                            'Kategoria została usunięta'
                                    )
                                );
                                return $app->redirect(
                                    $app['url_generator']->generate(
                                        'categories'
                                    ), 301
                                );
                            } catch (\Exception $e) {
                                $errors[] = 'Coś poszło niezgodnie z planem';
                            }
                        } else {
                            return $app->redirect(
                                $app['url_generator']->generate(
                                    'categories'
                                ), 301
                            );
                        }
                    }
                    return $app['twig']->render(
                        'categories/delete.twig', array(
                            'form' => $form->createView()
                        )
                    );
                } 
           
       
            return $app->redirect(
                $app['url_generator']->generate(
                    'categories'
                ), 301
            );
		} else {
						$app['session']->getFlashBag()->add(
							'message', array(
								'type' => 'danger',
								'content' => 'Nie znaleziono kategorii!'
							)
						);
						return $app->redirect(
								$app['url_generator']->generate(
										'/users/panel' 
								), 301
							);
					}
       }
        
}
	
