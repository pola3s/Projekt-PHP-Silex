<?php

 
namespace Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Model\CommentsModel;
use Model\UsersModel;
use Model\FilesModel;
 
class CommentsController implements ControllerProviderInterface
{
   
    protected $_model;

	protected $_user;

    protected $_files;

    public function connect(Application $app)
    {
        $this->_model = new CommentsModel($app);
        $this->_user = new UsersModel($app);
        $this->_files = new FilesModel($app);
        $commentController = $app['controllers_factory'];
        $commentController->match('/view/{id_file}', array($this, 'index'))
            ->value('page', 1)
            ->bind('/comments/');
        $commentController ->match('/view/add/{id_file}', array($this, 'add'))
            ->bind('/comments/add');
        $commentController->match('/edit/{id}', array($this, 'edit'))
            ->bind('/comments/edit');
        $commentController->match('/delete/{id}', array($this, 'delete'))
            ->bind('/comments/delete');
        return $commentController;
    }

  
    public function index(Application $app, Request $request)
    {
        $id = (int)$request->get('id_file', 0);
		
		

		$filesModel = new FilesModel($app);
		$commentsModel = new CommentsModel($app);
		$comments = $commentsModel->getCommentsList($id);
			
			

				return $app['twig']->render(
                'comments/index.twig', array(
                'comments' => $comments, 
				'id_file' => $id
                )
            );
	} 
	

	public function add(Application $app, Request $request)
    {

        $idfile = (int)$request->get('id_file');
		
		//var_dump($idfile);

        $check = $this->_files->checkFileId($idfile);
		
		//var_dump($check);

        if ($check) {

            //if ($this->_user->_isLoggedIn($app)) {
             //   $iduser = $this->_user->getIdCurrentUser($app);
            //} else {
            //    $iduser = 0;
            //}
			
			$iduser = 4;
			
			
			//var_dump($iduser);
			
            $data = array(
                'published_date' => date('Y-m-d'),
                'id_file' => $idfile,
                'id_user' => $iduser,
            );

			//var_dump($data);
            $form = $app['form.factory']->createBuilder('form', $data)
                ->add(
                    'Tresc', 'textarea', array('required' => false), array(
                    'constraints' => array(
                        new Assert\NotBlank(),
                        new Assert\Length(
                            array(
                                'min' => 5,
                                'minMessage' => 
                                    'Minimalna ilo�� znak�w to 5',
                            )
                        ),
                        new Assert\Type(
                            array(
                                'type' => 'string',
                                'message' => 'tekst nie jest poprawny',
                            )
                        )
                    )
                )
                )
                ->getForm();

            $form->handleRequest($request);

            if ($form->isValid()) {
                $data = $form->getData();
                try {
                    $model = $this->_model->addComment($data);

                    $app['session']->getFlashBag()->add(
                        'message', array(
                            'type' => 'success',
                            'content' => 'Komentarz zosta� dodany'
                        )
                    );
                    return $app->redirect(
                        $app['url_generator']->generate(
                            '/files/'
                        ), 301
                    );
                } catch (\Exception $e) {
                    $errors[] = 'Co� posz�o niezgodnie z planem';
                }
            }
            return $app['twig']->render(
                'comments/add.twig', array(
                    'form' => $form->createView(), 
                    'id_file' => $idfile
					
					
                )
            );
        } else {
            $app['session']->getFlashBag()->add(
                'message', array(
                    'type' => 'danger',
                    'content' => 'Nie znaleziono komentarza'
                )
            );
            return $app->redirect(
                $app['url_generator']->generate(
                    '/files/'
                ), 301
            );
        }
    }
	
	public function edit(Application $app, Request $request)
	{
				
	} 
	
	
	// public function delete(Application $app, Request $request)
    // {
        // $name = (string)$request->get('name', 0);
        // $check = $this->_model->checkFileName($name);
        // if ($check) {
            // $file = $this->_model->getFileByName($name);
            // $path = dirname(dirname(dirname(__FILE__))) . '/web/media/' . $name;

            // if (count($file)) {
                // $data = array();
                // $form = $app['form.factory']->createBuilder('form', $data)
                    // ->add(
                        // 'name', 'hidden', array(
                            // 'data' => $name,
                        // )
                    // )
                    // ->add('Yes', 'submit')
                    // ->add('No', 'submit')
                    // ->getForm();

                // $form->handleRequest($request);

                // if ($form->isValid()) {
                    // if ($form->get('Yes')->isClicked()) {
                        // $data = $form->getData();

                        // try {
                            // $model = unlink($path);


                            // try {
                                // $link = $this->_model->removeFile($name);

                                // $app['session']->getFlashBag()->add(
                                    // 'message', array(
                                        // 'type' => 'success',
                                        // 'content' => 
                                            // 'Zdjecie zostało usunięte'
                                    // )
                                // );
                                // return $app->redirect(
                                    // $app['url_generator']->generate(
                                        // '/files/'
                                    // ), 301
                                // );
                            // } catch (\Exception $e) {
                                // $errors[] = 'Coś poszło niezgodnie z planem';
                            // }
                        // } catch (\Exception $e) {
                            // $errors[] = 'Plik nie zstał usuniety';
                        // }
                    // }
                // }

                // return $app['twig']->render(
                    // 'files/delete.twig', array(
                        // 'form' => $form->createView()
                    // )
                // );

            // } else {
                // $app['session']->getFlashBag()->add(
                    // 'message', array(
                        // 'type' => 'danger',
                        // 'content' => 'Nie znaleziono zdjęcia'
                    // )
                // );
                // return $app->redirect(
                    // $app['url_generator']->generate(
                        // '/files/manager'
                    // ), 301
                // );
            // }
        // } else {
            // $app['session']->getFlashBag()->add(
                // 'message', array(
                    // 'type' => 'danger',
                    // 'content' => 'Nie znaleziono zdjęcia'
                // )
            // );
            // return $app->redirect(
                // $app['url_generator']->generate(
                    // '/files/manager'
                // ), 301
            // );

        // }
    // }
	
	public function delete(Application $app, Request $request)
    {
        $id = (int)$request->get('id', 0);
		//var_dump($id);

        $check = $this->_model->checkCommentId($id);
		
		//var_dump($check);

        if ($check) {

            $comment = $this->_model->getComment($id);
			
			//var_dump($comment);

            $data = array();
            
            if (count($comment)) {
                $user = new UsersModel($app);
                //$idLoggedUser = $user->getIdCurrentUser($app);
                //if (
                //    $idLoggedUser == $comment['iduser'] || 
                //    $app['security']->isGranted('ROLE_ADMIN')
                //) { 
                // checking if currently logged user 
                // is an author of the post or admin
                $form = $app['form.factory']->createBuilder('form', $data)
                    ->add(
                        'id_comment', 
                        'hidden', 
                        array(
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
                        $model = $this->_model->deleteComment($data);
                        if (!$model) {
                            $app['session']
                            ->getFlashBag()
                            ->set('success', 'Comment was deleted');
                            return $app->redirect(
                                $app['url_generator']->generate("files"), 
                                301
                            );
                        } else {
                            $app['session']
                            ->getFlashBag()
                            ->set(
                                'error', 
                                'An error occured, we were not 
                                able to delete the comment.'
                            );
                            return $app->redirect(
                                $app['url_generator']->generate('files'), 
                                301
                            );
                        }
                    } else {
                        return $app->redirect(
                            $app['url_generator']->generate('files'), 
                            301
                        );
                    }
                }
                return $app['twig']->render(
                    'comments/delete.twig', 
                    array('form' => $form->createView())
                );
                //} else { // user is not author
                //    $app['session']
                //    ->getFlashBag()
                //    ->set(
                //        'error', 
                //        'It seems you are not an author of this comment!'
                //    );
                //return $app->redirect(
                //    $app['url_generator']->generate('/posts/'), 
                //    301
                //);
                //}
            } else { // end of count
                $app['session']
                ->getFlashBag()
                ->set('error', 'Comment not found');
                return $app->redirect(
                    $app['url_generator']->generate("files"), 
                    301
                );
            }
        } else {
            return $app->redirect(
                $app['url_generator']->generate("/files/"), 
                301
            );

        }
    }
	
	

   
	

}