<?php
/**
 * Categories controller
 *
 * PHP version 5
 *
 * @category Controller
 * @package  Controller
 * @author   Paulina Serwińska <paulina.serwinska@gmail.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     wierzba.wzks.uj.edu.pl/~12_serwinska
 */
 
namespace Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Model\CommentsModel;
use Model\UsersModel;
use Model\FilesModel;

 /**
 * Class CategoriesController
 *
 * @category Controller
 * @package  Controller
 * @author   Paulina Serwińska <paulina.serwinska@gmail.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version  Release: <package_version>
 * @link     wierzba.wzks.uj.edu.pl/~12_serwinska
 * @uses     Silex\Application
 * @uses     Silex\ControllerProviderInterface
 * @uses     Symfony\Component\Config\Definition\Exception\Exception;
 * @uses     Symfony\Component\HttpFoundation\Request;
 * @uses     Symfony\Component\Validator\Constraints as Assert;
 * @uses     Model\CommentsModel;
 * @uses     Model\UsersModel;
 * @uses     Model\FilesModel;
 */
class CommentsController implements ControllerProviderInterface
{
   
    protected $model;

    protected $user;

    protected $files;
    
    /**
    * Connection
    *
    * @param Application $app application object
    *
    * @access public
    * @return \Silex\ControllerCollection
    */
    public function connect(Application $app)
    {
        $this->_model = new CommentsModel($app);
        $this->_user = new UsersModel($app);
        $this->_files = new FilesModel($app);
        $commentController = $app['controllers_factory'];
        $commentController->match('/view/{id_file}', array($this, 'index'))
            ->value('id_file', null)
            ->bind('/comments/');
        $commentController ->match('/view/add/{id_file}', array($this, 'add'))
            ->bind('/comments/add');
        $commentController->match('/edit/{id}', array($this, 'edit'))
            ->bind('/comments/edit');
        $commentController->match('/delete/{id}', array($this, 'delete'))
            ->bind('/comments/delete');
        return $commentController;
    }

      
    /**
    * Show comments
    *
    * @param Application $app     application object
    * @param Request     $request request
    *
    * @access public
    * @return page 
    */ 
    public function index(Application $app, Request $request)
    {
        $id_file = (int)$request->get('id_file', 0);
        
        $filesModel = new FilesModel($app);
        $commentsModel = new CommentsModel($app);
        $comments = $commentsModel->getCommentsList($id_file);
        
        $usersModel = new UsersModel($app);
        
        if ($usersModel ->_isLoggedIn($app)) {
                $idLoggedUser = $usersModel ->getIdCurrentUser($app);
        }
                        
        return $app['twig']->render(
            'comments/index.twig', array(
                'comments' => $comments, 
				'id_file' => $id_file,
				'idLoggedUser' => $idLoggedUser
                )
        );
    } 
    
    /**
    * Add comment
    *
    * @param Application $app     application object
    * @param Request     $request request
    *
    * @access public
    * @return mixed Generates page 
    */ 
    public function add(Application $app, Request $request)
    {
        
        $usersModel = new UsersModel($app);
        $id_file = (int)$request->get('id_file', 0);
    

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
        ->add(
            'content', 'textarea', array(
            'constraints' => array(
            new Assert\NotBlank(), 
            new Assert\Length(
                array('min' => 1)
            )
            )
            )
        )
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
                        'view', array(
                        'id' => $id_file,
                        )
                    ), 301
                );
            } catch (Exception $e) {
                $app['session']->getFlashBag()->add(
                    'message',
                    array(
                    'type' => 'error',
                    'content' => 'Nie można dodać komentarza'
                    )
                );
            }
        } 
        return $app['twig']->render(
            'comments/add.twig',
            array(
            'form' => $form->createView(),
            'id_file' => $id_file,
                        
            )
        );
        
    }
    
    /**
    * Delete comment
    *
    * @param Application $app     application object
    * @param Request     $request request
    *
    * @access public
    * @return mixed Generates page
    */
    public function delete(Application $app, Request $request)
    {
         
        $id_comment = (int) $request->get('id', 0);
 
		$commentsModel = new CommentsModel($app);
        $comment = $commentsModel->getComment($id_comment);
        $id_file = $comment['id_file'];

        if (!$comment || !isset($comment) || !count($comment)) {

            $app['session']->getFlashBag()->add(
                'message', array(
                    'type' => 'danger',
                    'content' => 'Nie znaleziono komentarza'
                )
            );
            return $app->redirect(
                $app['url_generator']->generate(
                    'files',
                    array()
                ), 301
            );
        }

		if (count($comment)) {
                    
                $usersModel = new UsersModel($app);
                $idLoggedUser = $usersModel->getIdCurrentUser($app);
                        
                if ($idLoggedUser == $comment['id_user']  
                    || $app['security']->isGranted('ROLE_ADMIN')
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
                                $app['session']->getFlashBag()->add(
                                    'message',
                                    array(
                                    'type' => 'success',
                                    'content' => 'Komentarz został usunięty'
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
                            } else {
                                $app['session']
                                ->getFlashBag()
                                ->set(
                                    'danger', 
                                    'Nie udało się usunąć komentarza.'
                                );
                                return $app->redirect(
                                    $app['url_generator']->generate(
                                        'view', 
                                        array(
                                           'id' => $id_file,
                                        )    
                                    ), 301
                                );
                            }
                        } else {
                            return $app->redirect(
                                $app['url_generator']->generate(
                                    'files'
                                ), 301
                            );
                        }
                    }
                    return $app['twig']->render(
                        'comments/delete.twig', 
                        array('form' => $form->createView())
                    );
                }
            } 
         
    }

    /**
    * Edit comment
    *
    * @param Application $app     application object
    * @param Request     $request request
    *
    * @access public
    * @return mixed Generates page 
    */ 
    public function edit(Application $app, Request $request)
    {
        
        $id_comment = (int) $request->get('id', 0);
 
		$commentsModel = new CommentsModel($app);
        $comment = $commentsModel->getComment($id_comment);
        $id_file = $comment['id_file'];

        if (!$comment || !isset($comment) || !count($comment)) {

            $app['session']->getFlashBag()->add(
                'message', array(
                    'type' => 'danger',
                    'content' => 'Nie znaleziono komentarza'
                )
            );
            return $app->redirect(
                $app['url_generator']->generate(
                    'files',
                    array()
                ), 301
            );
        }

		if (count($comment)) {

            $user = new UsersModel($app);

            $idLoggedUser = $user->getIdCurrentUser($app);

            if ($idLoggedUser == $comment['id_user']
                || $app['security']->isGranted('ROLE_ADMIN')
            ) {


                if (count($comment)) {
                    $form = $app['form.factory']->createBuilder('form', $comment)
                    ->add(
                        'content', 'textarea', array(
                        'constraints' => array(
                        new Assert\NotBlank(),
                        new Assert\Length(
                            array('min' => 1)
                            )
                        )
                        )
                    )
					->add('save', 'submit')
                    ->getForm();

                    $form->handleRequest($request);

                    if ($form->isValid()) {
                        $commentsModel = new CommentsModel($app);
                        $data = $form->getData();
                        $commentsModel->editComment($data, $id_comment);

                        $app['session']->getFlashBag()->add(
                            'message',
                            array(
                            'type' => 'success',
                            'content' => 'Komentarz został zmieniony'
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
                    }
                    return $app['twig']->render(
                        'comments/edit.twig', array(
                        'form' => $form->createView(),
                        'comment' => $comment
                        )
                    );

                } else {

                      return $app->redirect(
                          $app['url_generator']->generate(
                              '/comments/add'
                          ),
                          301
                      );
                }
            }
        } 

    }
   
    

}
