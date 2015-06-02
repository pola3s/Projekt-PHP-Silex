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
        $id_file = (int)$request->get('id_file', 0);
		
		$filesModel = new FilesModel($app);
		$commentsModel = new CommentsModel($app);
		$comments = $commentsModel->getCommentsList($id_file);
		
				return $app['twig']->render(
                'comments/index.twig', array(
                'comments' => $comments, 
				'id_file' => $id_file
                )
            );
	} 
	

	public function add(Application $app, Request $request)
	{
		
		$usersModel = new UsersModel($app);
        
		$id_file = (int)$request->get('id_file', 0);
		
		$check = $this->_files->checkFileId($id_file);
		
		if ($check) {

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
                'published_date' => date('Y-m-d'),
                'id_file' => $id_file,
                'id_user' => $id_user,
            );

			$form = $app['form.factory']->createBuilder('form', $data)
				->add('content', 'text', array(
						'constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 5)))
					))
			
				->add('save', 'submit')
				->getForm();
				
			$form->handleRequest($request);
			if ($form->isValid()) {
               
				
                try { 
					$data = $form->getData();
					
                    $model = $this->_model->addComment($data);
					
					$app['session']->getFlashBag()->add(
                        'message', array(
                            'type' => 'success',
                            'content' => 'Komentarz został dodany'
                        )
                    );
                    return $app->redirect(
                        $app['url_generator']->generate(
                            'view', 
								array(
									'id' => $id_file,
								)	
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
			
			
				return $app['twig']->render(
				'comments/add.twig',
					array(
						'form' => $form->createView()
					)
				);
		}
	}
	
	
	public function delete(Application $app, Request $request)
    {
        $id = (int)$request->get('id', 0);
		

        $check = $this->_model->checkCommentId($id);
		
	

				if ($check) {

					$comment = $this->_model->getComment($id);
					
					
					$data = array();
					
					if (count($comment)) {
					
						$user = new UsersModel($app);
						
						$idLoggedUser = $user->getIdCurrentUser($app);
						
						if (
							$idLoggedUser == $comment['id_user'] || 
							$app['security']->isGranted('ROLE_ADMIN')
						) { 
						
						
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
					} else { // user is not author
						    $app['session']->getFlashBag()->add(
						'message',
							array(
							'type' => 'error',
							'content' => 'Nie jesteś autorem tego wpisu!'
							)
						);
					
						return $app->redirect(
						    $app['url_generator']->generate('files'), 
						    301
						);
					}
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
	
	
	
	 public function edit(Application $app, Request $request)
	 {
	 
		$id_comment = (int) $request->get('id', 0);
		$check = $this->_model->checkCommentId($id_comment);
		
		if ($check) {

			$commentsModel = new CommentsModel($app);
			$comment = $commentsModel -> getComment($id_comment);
					
			if (count($comment)) {
					
				$user = new UsersModel($app);
					
					$idLoggedUser = $user->getIdCurrentUser($app);
						
					if (
						$idLoggedUser == $comment['id_user'] || 
						$app['security']->isGranted('ROLE_ADMIN')
					) { 
				
				
					if (count($comment)) {
						$form = $app['form.factory']->createBuilder('form', $comment)
							->add('content', 'text', array(
								'constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 1)))
							))
							
					   
						->add('save', 'submit')
						->getForm();
							
					$form->handleRequest($request);
					
					if ($form->isValid()) {
							$commentsModel = new CommentsModel($app);
							$data = $form->getData();
							$commentsModel->editComment($data, $id_comment);
							
							
								
							return $app->redirect($app['url_generator']->generate('files'), 301);
					}
							return $app['twig']->render('comments/edit.twig', array('form' => $form->createView(), 'comment' => $comment));
							
					} else {
					
							return $app->redirect($app['url_generator']->generate('/comments/add'), 301);
					}
			 }else { // user is not author
						    $app['session']->getFlashBag()->add(
						'message',
							array(
							'type' => 'error',
							'content' => 'Nie jesteś autorem tego wpisu!'
							)
						);
					
						return $app->redirect(
						    $app['url_generator']->generate('files'), 
						    301
						);
					}
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