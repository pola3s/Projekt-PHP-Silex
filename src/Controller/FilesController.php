<?php


namespace Controller;
use Doctrine\DBAL\DBALException;
use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Model\FilesModel;
use Model\UsersModel;
use Model\CategoriesModel;

	
class FilesController implements ControllerProviderInterface
{
	protected $_model;
	protected $_user;
	
		
	public function connect(Application $app)
	{
	  $this->_model = new FilesModel($app);
      $filesController = $app['controllers_factory'];
      $filesController->match('{page}', array($this, 'index'))
		->value('page', 1)
		->bind('files');
	  $filesController->match('/files/view/{id}', array($this, 'view'))
	    ->bind('view');
	  $filesController->match('files/upload/', array($this, 'upload'))
	    ->bind('/files/upload');
	  $filesController->match('files/edit/{id}', array($this, 'edit'))
		->bind('edit');
	  $filesController->match('files/delete/{name}', array($this, 'delete'))
		->bind('/files/delete');
	  $filesController->match('files/search/', array($this, 'search'))
		->bind('/files/search');
	  $filesController->match('files/results/', array($this, 'results'))
		->bind('/files/results');
			
	return $filesController;
	}
		
		
	protected function _isLoggedIn(Application $app)
	{
		if (null === $user = $app['session']->get('user')) {
			return false;
		} else {
			return true;
		}
	}
	

		public function index(Application $app, Request $request)
		{
			

			$pageLimit = 6;
			$page = (int)$request->get('page', 1);
			$FilesModel = new FilesModel($app);
			$pagesCount = $this->_model ->countFilesPages($pageLimit);

			if (($page < 1) || ($page > $pagesCount)) 
			{
			$page = 1;
			}
			
			
			$files = $this->_model ->getFilesPage($page, $pageLimit, $pagesCount);
			$paginator = array('page' => $page, 'pagesCount' => $pagesCount);
			
			$app->before(function (Request $request) use ($app) {
				$app['twig']->addGlobal('current_page_name', $request->getRequestUri());
			});
		
			return $app['twig']->render(
				'files/index.twig', array(
				'files' => $files,
				'paginator' => $paginator,
				'page' => $page
				)
			);
		}
		
		  
		
	public function view(Application $app, Request $request)
	{
		
		$id = (int) $request -> get('id', 0); //id zdjęcia
		$page = (int) $request -> get('page', 0);
		//var_dump($page);
		$FilesModel = new FilesModel($app);
		$file = $FilesModel -> getFile($id);
		
		$id_category = $FilesModel -> checkCategoryId($id);
		$category = $FilesModel -> getCategory($id_category);
		
		$id_user = $FilesModel-> checkUserId($id);
		$user = $FilesModel -> getFileUploaderName($id_user['id_user']);
		
		var_dump($user);
		
		// $app['breadcrumbs']->addItem('A complex route',array(
			// 'route' => 'complex_named_route',
			// 'params' => array(
				// 'name' => "John",
				// 'id' => 3
				// )
		// ));

		return $app['twig']->render('files/view.twig', array(
				'file' => $file,
				'user' => $user,
				'id_user' => $id_user,
				'category' => $category,
				'page' => $page
		));
		
	
		
						
	}
		
		
	public function upload(Application $app, Request $request)
	{
		
		$usersModel = new UsersModel($app);
        //$idLoggedUser = $usersModel->getIdCurrentUser($app);
		
		if ($usersModel ->_isLoggedIn($app)) {
                $id_user = $usersModel -> getIdCurrentUser($app);
				
            } else {
                return $app->redirect(
					$app['url_generator']->generate(
						'auth_login'
						), 301
				);
            }
            $data = array(
                'id_user' => $id_user,
            );

   
		$CategoriesModel = new CategoriesModel($app);
		$categories = $CategoriesModel->getCategoriesDict();
		
		$form = $app['form.factory']->createBuilder('form', $data)
		->add('title', 'text', array(
			'constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 3)))
		))
		->add('category', 'choice', array(
             'choices' => $categories,
        ))
		->add('description', 'textarea', array(
			'constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 5)))
		))
		
		->add('file', 'file', array(
                    'label' => 'Choose file',
                    'constraints' => array(
                        new Assert\File(
                            array(
                                'maxSize' => '1024k',
                                'mimeTypes' => array(
                                    'image/jpeg',
                                    'image/png',
                                    'image/gif',
                                ),
                            )
                        )
                    )
                )
                )
		->add('save', 'submit', array('label' => 'Upload file'))
		->getForm();
			
		if ($request->isMethod('POST')) {
			$form->bind($request);
			
		if ($form->isValid()) {
			try {
			$files = $request->files->get($form->getName());
			$data = $form->getData();
			
			$id_category = $data['category'];
			
			
            $categoriesModel = new CategoriesModel($app);
            $category = $categoriesModel->getCategoriesList();
            $category = $category[$id_category];
			
			$category_name_array = $categoriesModel->getCategoryName($id_category);
		
			$category_name=$category_name_array['name'];
		
            $data['category']=$category_name;
			
			
			
			$path = dirname(dirname(dirname(__FILE__))).'/web/media';
			$filesModel = new FilesModel($app);
			
			$originalFilename = $files['file']->getClientOriginalName();
			$newFilename = $filesModel->createName($originalFilename);
			$files['file']->move($path, $newFilename);
		
			$filesModel->saveFile($newFilename, $data);
			
			$app['session']->getFlashBag()->add(
				'message',
					array(
					'type' => 'success',
					'content' => 'Zdjęcie zostało dodane!'
					)
				);
			
		
			return $app->redirect(
				$app['url_generator']->generate(
				'files'
				), 301
			);
		
			
			} catch (Exception $e) {
				$app['session']->getFlashBag()->add(
				'message',
					array(
					'type' => 'error',
					'content' => 'Cannot upload file.'
					)
				);
				}
			} else {
				$app['session']->getFlashBag()->add(
				'message',
					array(
						'type' => 'error',
						'content' => 'Form contains invalid data.'
						)
				);
				}
			}
			
				return $app['twig']->render(
				'files/upload.twig',
					array(
						'form' => $form->createView()
					)
				);
	}
		
		
		public function edit(Application $app, Request $request)
		{
			$filesModel = new FilesModel($app);
			$id = (int) $request->get('id', 0);
			
			$file = $filesModel->getFile($id);
			$filename = $filesModel -> getFile($id);
			
			
			$CategoriesModel = new CategoriesModel($app);
			$categories = $CategoriesModel->getCategoriesDict();
			
			if (count($file)) {
				$form = $app['form.factory']->createBuilder('form', $file)
					->add('id_file', 'hidden', array(
						'constraints' => array(new Assert\NotBlank())
						)
					)
					->add('title', 'text', array(
						'constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 2)))
						)
					)
					->add(
						'category', 'choice', array(
							'choices' => $categories,
						)
					)
					->add('description', 'textarea', array(
						'constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 5)))
						)
					)
					->add('save', 'submit')
					->getForm();
					
			$form->handleRequest($request);
			
			if ($form->isValid()) {
			
					$data = $form->getData();
					$id_category = $data['category'];
			
					$categoriesModel = new CategoriesModel($app);
					$category = $categoriesModel->getCategoriesList();
					$category = $category[$id_category];
					
					$category_name_array = $categoriesModel->getCategoryName($id_category);
				
					$category_name=$category_name_array['name'];
					
					$data['category']=$category_name;
					
			
					$filesModel = new FilesModel($app);
					$filesModel->editFile($filename, $data);
					
					$app['session']->getFlashBag()->add(
						'message',
							array(
							'type' => 'success',
							'content' => 'Zdjęcie zostało edytowane!'
							)
					);
			
					return $app->redirect(
								$app['url_generator']->generate(
									'view', 
										array(
											'id' => $id,
										)	
								), 301
					);
			}
					return $app['twig']->render('files/edit.twig', array('form' => $form->createView(), 'file' => $file));
					
			} else {
			
					return $app->redirect($app['url_generator']->generate('/files/add'), 301);
			}
} 
	
	
	public function delete(Application $app, Request $request)
    {
        $name = (string)$request->get('name', 0);
        $check = $this->_model->checkFileName($name);
        if ($check) {
            $file = $this->_model->getFileByName($name);
            $path = dirname(dirname(dirname(__FILE__))) . '/web/media/' . $name;

            if (count($file)) {
                $data = array();
                $form = $app['form.factory']->createBuilder('form', $data)
                    ->add(
                        'name', 'hidden', array(
                            'data' => $name,
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
                            $model = unlink($path);


                            try {
                                $link = $this->_model->removeFile($name);
								
								$app['session']->getFlashBag()->add(
										'message',
											array(
												'type' => 'success',
												'content' => 'Usunięto zdjęcie'
												
											)
								);
                                return $app->redirect(
                                    $app['url_generator']->generate(
                                        'files'
                                    ), 301
                                );
                            } catch (\Exception $e) {
                                $errors[] = 'Coś poszło niezgodnie z planem';
                            }
                        } catch (\Exception $e) {
                            $errors[] = 'Plik nie zstał usuniety';
                        }
                    }
                }

                return $app['twig']->render(
                    'files/delete.twig', array(
                        'form' => $form->createView()
                    )
                );

            } else {
                $app['session']->getFlashBag()->add(
                    'message', array(
                        'type' => 'danger',
                        'content' => 'Nie znaleziono zdjęcia'
                    )
                );
                return $app->redirect(
                    $app['url_generator']->generate(
                        'files'
                    ), 301
                );
            }
        } else {
            $app['session']->getFlashBag()->add(
                'message', array(
                    'type' => 'danger',
                    'content' => 'Nie znaleziono zdjęcia'
                )
            );
			return $app->redirect(
                $app['url_generator']->generate(
                    'files'
                ), 301
            );

        }
    }
	
	public function search(Application $app, Request $request)
    {
        $data = array();
        $categoriesModel = new CategoriesModel($app);
        $choiceCategories = $categoriesModel->getCategoriesDict();
        $choiceCategory = array_combine(
            $choiceCategories, $choiceCategories
        );

        

        $form = $app['form.factory']->createBuilder('form', $data)
            ->add(
                'category', 'choice', array(
                    'choices' => $choiceCategory,
                    'multiple' => false

                )
            )
			
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
		
			return $app->redirect(
                $app['url_generator']->generate(
                    '/files/results', array(
                        'data' => $data
                    )
                ), 301
            );
        }
        return $app['twig']
            ->render(
                'files/search.twig', array(
                    'form' => $form->createView()
                )
            );
    }
	
	public function results(Application $app)
    {
        $data = $app['request']->get('data');
		
		$name = (string)$data['category'];
		
		
	
		$filesModel = new FilesModel($app);
        $files = $filesModel->searchFile($name);
        return $app['twig']
            ->render('files/results.twig', array('files' => $files, 'name' => $name));
    }
}

			
	
	
