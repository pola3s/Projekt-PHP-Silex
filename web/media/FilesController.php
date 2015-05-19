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
						->bind('delete');
      
		return $filesController;
    }
	

	/// WYŚWIETLANIE ZDJĘĆ, 5 NA STRONIE, DZIAŁA!!!!///
	
	public function index(Application $app, Request $request)
    {
       
		
		$pageLimit = 5;
		$page = (int)$request->get('page', 1);
		$FilesModel = new FilesModel($app);
		$pagesCount = $this->_model ->countFilesPages($pageLimit);  
		
		if (($page < 1) || ($page > $pagesCount)) {
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
		$id = (int) $request -> get('id', 0);  //id zdjęcia
		var_dump($id);
	   
		$FilesModel = new FilesModel($app);
		$file = $FilesModel-> getFile($id);
		var_dump($file);
		
		$id_user = $FilesModel -> getUserByFile($id);
		var_dump($id_user);
	  
		$UsersModel = new UsersModel($app);
		$user = $UsersModel -> getUserById2($id_user);
		
		var_dump($user);
	   

	   return $app['twig']->render('files/view.twig', array( 
			'file' => $file, 
			'user' => $user,
			
		));
	  
	  
	}
	 
	
	
    public function upload(Application $app, Request $request)
    {
			$form = $app['form.factory']->createBuilder('form', $data)
		
			->add('title', 'text', array(
				'constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 5)))
			))
			->add('category', 'text', array(
				'constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 5)))
			))
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

        $id = (int)$request->get('id', 0); // getting id 
		
		//var_dump($id);

        $check = $this->_model->checkFileId($id); 
		
		
        
        if ($check) {
            $file = $this->_model->getFile($id);
            //$user = new UsersModel($app);
            //if ($app['security']->isGranted('IS_AUTHENTICATED_FULLY')) {
            //    $idLoggedUser = $user->getIdCurrentUser($app);
            //} else {
            //    $idLoggedUser = null;
            //}
            $data = array(
                'id_file'=> $file['id_file'],
				'name' => $file['name'],
                'title' => $file['title'],
				'category' => $file['category'],
				'date' => $file['date'],
				'description' => $file['description'],
				'id_user' => $file['id_user']
                
				
            );
			
			//var_dump($data);
			
			
            if (count($file)) {
                // checking if currently logged user is an author 
                // of the post or admin
                //if (
                //   $idLoggedUser == $post['iduser'] || 
                //    $app['security']->isGranted('ROLE_ADMIN')
                //) { 
                    $form = $app['form.factory']->createBuilder('form', $data)
                        ->add(
                            'id_file', 'hidden', array(
                                'data' => $id,
                            )
                        )
						->add(
                            'name', 'hidden', array(
                                'data' => $file['name'],
                            )
                        )
                        ->add(
                            'title', 'text', array(
                                'constraints' => array(
                                    new Assert\NotBlank(),
                                    new Assert\Length(
                                        array(
                                            'min' => 3,
                                            'max' => 45,
                                            'minMessage' => 'Title must 
                                                be at least 
                                                3 characters long.',
                                            'maxMessage' => 'Title cannot 
                                                be longer 
                                                than {{ limit }} 
                                                characters.',
                                        )
                                    ),
                                    new Assert\Type(
                                        array(
                                            'type' => 'string',
                                            'message' 
                                            => 'The title is not a valid.',
                                        )
                                    )
                                )
                            )
                        )
                       
                        ->getForm();

                    $form->handleRequest($request);
					
					

                    if ($form->isValid()) {
                        $data = $form->getData();
						
						//var_dump($data);
                        
                        $model = $this->_model->editFile($data);
                    
                        if (!$model) {
                            $app['session']
                            ->getFlashBag()
                            ->set(
                                'success', 
                                'Post was updated successfully!'
                            );
                            return $app->redirect(
                                $app['url_generator']->generate('files'), 
                                301
                            );
                        } else {
                            $app['session']
                            ->getFlashBag()
                            ->set(
                                'error', 
                                'An error occured, we were not 
                                    able to update the post.'
                            );
                            return $app->redirect(
                                $app['url_generator']->generate('files'), 
                                301
                            );
                        }
                    }

                    return $app['twig']->render(
                        'files/edit.twig', array(
                            'form' => $form->createView(), 
                            'id' => $id
                        )
                    );
                //} else { // user is not author 
                //    $app['session']
                //    ->getFlashBag()
                //    ->set(
                //        'error', 
                //        'It seems you are not an author of this post!'
                //    );
                //    return $app->redirect(
                //        $app['url_generator']->generate('/posts/'), 
                //        301
                //    );
                //}
            } else { // end of count post
                $app['session']
                ->getFlashBag()
                ->set(
                    'error', 
                    'You broke something, 
                    try adding new post instead...'
                );
                return $app->redirect(
                    $app['url_generator']->generate('upload'), 
                    301
                );
            }
        } else {
            $app['session']
            ->getFlashBag()
            ->set(
                'error', 
                'Post with given ID does not exist! Add a new one instead.'
            );
            return $app->redirect(
                $app['url_generator']->generate('upload'), 
                301
            );
        }
    }

	
	

}

	