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
			
			$filesController->match('/view/{id}', array($this, 'view'))
							->bind('view');
			$filesController->match('/upload/', array($this, 'upload'))
							->bind('/files/upload');
			$filesController->match('/edit/{id}', array($this, 'edit'))
							->bind('edit');
			$filesController->match('/delete/{name}', array($this, 'delete'))
							->bind('/files/delete');
			$filesController->match('/manager/', array($this, 'manager'))
							->bind('/files/manager');
			return $filesController;
		}
		
		
/// WYŒWIETLANIE ZDJÊÆ, 6 NA STRONIE, DZIA£A!!!!///
		public function index(Application $app, Request $request)
		{

			$pageLimit = 5;
			$page = (int)$request->get('page', 1);
			$FilesModel = new FilesModel($app);
			$pagesCount = $this->_model ->countFilesPages($pageLimit);

			if (($page < 1) || ($page > $pagesCount)) 
			{
			$page = 1;
			}
		
		$files = $this->_model ->getFilesPage($page, $pageLimit, $pagesCount);
		$paginator = array('page' => $page, 'pagesCount' => $pagesCount);
		return $app['twig']->render(
			'files/index.twig', array(
			'files' => $files,
			'paginator' => $paginator
			)
		);
		}
		
	public function view(Application $app, Request $request)
	{
		$id = (int) $request -> get('id', 0); //id zdjêcia
		
		$FilesModel = new FilesModel($app);
		$file = $FilesModel-> getFile($id);
		
		
		
		$id_user = $FilesModel -> getUserByFile($id);
		
		
		$UsersModel = new UsersModel($app);
		$user = $UsersModel -> getUserById2($id_user);
		
		
		return $app['twig']->render('files/view.twig', array(
				'file' => $file,
				'user' => $user,
		));
	}
		
		
	public function upload(Application $app, Request $request)
	{
		
		//if(!$this->_isLoggedIn($app)) {
			// limit access
		//	return $app->redirect('/auth/login');
		//}
   
   
		$CategoriesModel = new CategoriesModel($app);
		$categories = $CategoriesModel->getCategories();
		
		$form = $app['form.factory']->createBuilder('form', $data)
		->add('title', 'text', array(
			'constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 5)))
		))
		->add(
                'category', 'choice', array(
                    'choices' => $categories,
                    )
            )
		->add('description', 'text', array(
			'constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 5)))
		))
		->add('id_user', 'text', array(
			'constraints' => array(new Assert\NotBlank())
		))
		->add(
			'file', 'file', array(
			'label' => 'Choose file',
			'constraints' => array(new Assert\Image())
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
					'content' => 'File successfully uploaded.'
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
	
	$CategoriesModel = new CategoriesModel($app);
	$categories = $CategoriesModel->getCategories();

    if (count($file)) {

        $form = $app['form.factory']->createBuilder('form', $file)
            ->add('id_file', 'hidden', array(
                'constraints' => array(new Assert\NotBlank())
            ))
            ->add('title', 'text', array(
                'constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 5)))
            ))
			->add(
                'category', 'choice', array(
                    'choices' => $categories,
                    )
            )
            ->add('description', 'text', array(
                'constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 5)))
            ))
            ->add('save', 'submit')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $filesModel = new FilesModel($app);
            $filesModel->saveFile2($form->getData());
            return $app->redirect($app['url_generator']->generate('files'), 301);
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
                                    'message', array(
                                        'type' => 'success',
                                        'content' => 
                                            'Zdjecie zosta³o usuniête'
                                    )
                                );
                                return $app->redirect(
                                    $app['url_generator']->generate(
                                        '/files/manager'
                                    ), 301
                                );
                            } catch (\Exception $e) {
                                $errors[] = 'Coœ posz³o niezgodnie z planem';
                            }
                        } catch (\Exception $e) {
                            $errors[] = 'Plik nie zsta³ usuniety';
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
                        'content' => 'Nie znaleziono zdjêcia'
                    )
                );
                return $app->redirect(
                    $app['url_generator']->generate(
                        '/files/manager'
                    ), 301
                );
            }
        } else {
            $app['session']->getFlashBag()->add(
                'message', array(
                    'type' => 'danger',
                    'content' => 'Nie znaleziono zdjêcia'
                )
            );
            return $app->redirect(
                $app['url_generator']->generate(
                    '/files/manager'
                ), 301
            );

        }
    }
	
	public function manager(Application $app, Request $request)
    {
        $files = $this->_model->getFiles();

        return $app['twig']->render(
            'files/manager.twig', array(
                'files' => $files
            )
        );

    }
	
	
}	
			// return $app->redirect(
				// $app['url_generator']->generate(
				// 'files'
				// ), 301
			// );
			// } catch (Exception $e) {
				// $app['session']->getFlashBag()->add(
				// 'message',
					// array(
					// 'type' => 'error',
					// 'content' => 'Cannot upload file.'
					// )
				// );
				// }
			// } else {
				// $app['session']->getFlashBag()->add(
				// 'message',
					// array(
						// 'type' => 'error',
						// 'content' => 'Form contains invalid data.'
						// )
				// );
				// }
			
			// } else {
				// return $app->redirect($app['url_generator']->generate('/files/'), 301);
    // }

			
	
	
