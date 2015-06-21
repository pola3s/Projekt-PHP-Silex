<?php
/**
 * Users Controller
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
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;
use Model\UsersModel;
use Model\FilesModel;
use Model\AboutModel;
use Form\UsersForm;

/**
 * Class UsersController
 *
 * @category Controller
 * @package  Controller
 * @author   Paulina Serwińska <paulina.serwinska@gmail.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version  Release: <package_version>
 * @link     wierzba.wzks.uj.edu.pl/~12_serwinska
 * @uses     Silex\Application;
 * @uses     Silex\ControllerProviderInterface;
 * @uses     Symfony\Component\Config\Definition\Exception\Exception;
 * @uses     Symfony\Component\HttpFoundation\Request;
 * @uses     Symfony\Component\Validator\Constraints;
 * @uses     Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;
 * @uses     Model\UsersModel;
 * @uses     Model\FilesModel;
 * @uses     Model\AboutModel;
 */
class UsersController implements ControllerProviderInterface
{
    /**
    * UsersModel object.
    *
    * @var    $model
    * @access protected
    */
    protected $model;
    
    /**
    * UsersModel object.
    *
    * @var    $user
    * @access protected
    */
    protected $user;

        
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
        $usersController = $app['controllers_factory'];
        $usersController->get('/list/', array($this, 'index'))
            ->bind('/users/');
        $usersController->match('/add/{id}', array($this, 'add'))
            ->bind('/users/add');
        $usersController->match('/edit/{id}', array($this, 'edit'))
            ->bind('/users/edit');
        $usersController->match('/edit_role/{id}', array($this, 'editRole'))
            ->bind('/users/edit_role');
        $usersController->match('/view/{id}', array($this, 'view'))
            ->bind('/users/view');
        $usersController->match('/panel/', array($this, 'panel'))
            ->bind('/users/panel');
        

        return $usersController;
    }
    
    /**
    * Show all users
    *
    * @param Application $app application object
    *
    * @access public
    * @return mixed
    */
    public function index(Application $app)
    {
        try {
            $usersModel = new UsersModel($app);
            $users = $usersModel->getUserList();
            
        } catch (\Exception $e) {
            $errors[] = 'Wystąpił błąd. Spróbuj ponownie później';
        }
        
        return $app['twig']->render(
            'users/index.twig',
            array(
                'users' => $users
                
            )
        );
         
        
    }
    
  
    /**
    * Edit information about user
    *
    * @param Application $app     application object
    * @param Request     $request request
    *
    * @access public
    * @return mixed Generates page
    */
    public function edit(Application $app, Request $request)
    {
        $usersModel = new UsersModel($app);
            
            
        if ($usersModel ->isLoggedIn($app)) {
            $id_user = $usersModel -> getIdCurrentUser($app);
                
        } else {
            return $app->redirect(
                $app['url_generator']->generate(
                    'auth_login'
                ),
                301
            );
        }
            
            
            $currentUserInfo = $usersModel -> getUser($id_user);
           
            

            $data = array(
                'login' => $currentUserInfo['login'],
                'email' => $currentUserInfo['email'],
                'firstname' => $currentUserInfo['firstname'],
                'lastname' => $currentUserInfo['lastname'],
                'password' => '',
                'confirm_password' => ''
            );
            
            $form = $app['form.factory']
            ->createBuilder(new UsersForm(), $data)->getForm();


            $form->handleRequest($request);

            if ($form->isValid()) {
                $data = $form->getData();
                

                $data['login'] = $app
                    ->escape($data['login']);
                $data['email'] = $app
                    ->escape($data['email']);
                $data['firstname'] = $app
                    ->escape($data['firstname']);
                $data['lastname'] = $app
                    ->escape($data['lastname']);
                $data['password'] = $app
                    ->escape($data['password']);
                $data['confirm_password'] = $app
                    ->escape($data['confirm_password']);

                if ($data['password'] === $data['confirm_password']) {
                    $password = $app['security.encoder.digest']
                        ->encodePassword(
                            $data['password'],
                            ''
                        );


                    $checkLogin = $usersModel
                        ->getUserByLogin(
                            $data['login']
                        );

                    if ($data['login'] === $checkLogin
                        || !$checkLogin
                        || (int)$currentUserInfo['id_user']===(int)$checkLogin['id_user']
                    ) {
                        try {
                            $usersModel->updateUser(
                                $currentUserInfo['id_user'],
                                $form->getData(),
                                $password
                            );
                            
                            
                            $app['session']->getFlashBag()->add(
                                'message',
                                array(
                                    'type' => 'success',
                                    'content' => 'Edycja konta udała się,
                                    możesz się teraz ponownie zalogować'
                                )
                            );
                            return $app->redirect(
                                $app['url_generator']
                                    ->generate(
                                        'files'
                                    ),
                                301
                            );
                        } catch (\Exception $e) {
                            $errors[] = 'Edycja konta nie powiodła się';
                        }

                    } else {
                        $app['session']->getFlashBag()->add(
                            'message',
                            array(
                                'type' => 'warning',
                                'content' => 'Login zajęty'
                            )
                        );
                        return $app['twig']->render(
                            'users/edit.twig',
                            array(
                                'form' => $form->createView(),
                                'login' => $currentUser
                            )
                        );
                    }
                } else {
                    $app['session']->getFlashBag()->add(
                        'message',
                        array(
                            'type' => 'warning',
                            'content' => 'Hasła różnią się'
                        )
                    );
                    return $app['twig']->render(
                        'users/edit.twig',
                        array(
                            'form' => $form->createView(),
                            'login' => $currentUser
                        )
                    );

                }
            }
            return $app['twig']->render(
                'users/edit.twig',
                array(
                    'form' => $form->createView(),
                    'login' => $currentUser
                )
            );
    }

    
    /**
    * Edit user's role
    *
    * @param Application $app     application object
    * @param Request     $request request
    *
    * @access public
    * @return mixed Generates page
    */
    public function editRole(Application $app, Request $request)
    {
        $id_user = (int)$request->get('id');
         
        $usersModel = new UsersModel($app);
    
        $user = $usersModel-> getUser($id_user);
        
        $files = $usersModel -> getFileByUser($id_user);
        $about = $usersModel -> getAboutByUser($id_user);

        $user_role = $usersModel -> getUserRole($id_user);
        
        $user_role = $usersModel -> getUserRole($id_user);


        $currentUserInfo = $usersModel -> getUser($id_user);
        
        if ($usersModel ->isLoggedIn($app)) {
                $idLoggedUser = $usersModel ->getIdCurrentUser($app);
        }
    
        
        if ($idLoggedUser != $id_user) {
                $check = $usersModel->checkUserId($id);
             
                $idRoleAdmin = 1;
                
                $adminUsers = $usersModel->checkAdminCount($idRoleAdmin);
               
			   
                $rowsnumber = count($adminUsers);
                
            if ($rowsnumber > 1) {
                 $form = $app['form.factory']->createBuilder('form', $user_role)
                    ->add(
                        'id_role',
                        'choice',
                        array(
                         'label' => 'Rola',
                         'choices' => array(
                            '1'   => 'Administrator',
                            '2' => 'Użytkownik',
                            )
                        )
                    )
                    
                    ->getForm();
            } else {
                 $form = $app['form.factory']->createBuilder('form', $user_role)
                    ->add(
                        'id_role',
                        'choice',
                        array(
                         'label' => 'Rola',
                         'choices' => array(
                            '1'   => 'Administrator'
                            )
                        )
                    )
                    
                    ->getForm();
            }
                
                    
                        
                    $form->handleRequest($request);

            if ($form->isValid()) {
                try {
                    $categoriesModel = new usersModel($app);
                    $user_role = $form->getData();
                    $id_role =  $user_role['id_role'];
                            
                    $usersModel->editRole($id_role, $id_user);
                            
                    $role = $usersModel -> getRoleName($id_role);
                                
                                
                    $app['session']->getFlashBag()->add(
                        'message',
                        array(
                            'type' => 'success',
                            'content' => 'Rola została edytowana'
                        )
                    );
                                    
                    return $app['twig']->render(
                        'users/view.twig',
                        array(
                        'files' => $files,
                        'user' => $user,
                        'about' => $about,
                        'id_user' => $id_user,
                        'role' => $role
                        )
                    );
                } catch (\Exception $e) {
                    $errors[] = 'Nie udało się zmienić roli';
                }
            }
                           
                        return $app['twig']->render(
                            'users/edit_role.twig',
                            array(
                                'form' => $form->createView(),
                                'role' => $role
                                )
                        );
                                    
                           
        
        }
        $app['session']->getFlashBag()->add(
            'message',
            array(
                            'type' => 'danger',
                            'content' => 'Nie możesz zmienić swojej roli!'
                        )
        );
                    return $app->redirect(
                        $app['url_generator']->generate(
                            '/users/'
                        ),
                        301
                    );
                

    }
    
    
    /**
    * Show information about user
    *
    * @param Application $app     application object
    * @param Request     $request request
    *
    * @access public
    * @return mixed Generates page
    */
    public function panel(Application $app, Request $request)
    {
        $usersModel = new UsersModel($app);
        
        $id_user = $usersModel->getIdCurrentUser($app);
        $user = $usersModel-> getUser($id_user);
        
        $files = $usersModel -> getFileByUser($id_user);
        $about = $usersModel -> getAboutByUser($id_user);

        $user_role = $usersModel -> getUserRole($id_user);
        $role = $usersModel -> getRoleName($user_role['id_role']);
        
        
        
        if (count($id_user)) {
            try {
                return $app['twig']->render(
                    'users/info.twig',
                    array(
                        'user' => $user,
                        'files' => $files,
                        'about' => $about,
                        'id_user' => $id_user,
                        'role' => $role
                    )
                );
            } catch (Exception $e) {
                    $app['session']->getFlashBag()->add(
                        'message',
                        array(
                        'type' => 'error',
                        'content' => 'Nie znaleziono użytkownika'
                        )
                    );
            }
        } else {
            $app['session']->getFlashBag()->add(
                'message',
                array(
                    'type' => 'danger',
                    'content' => 'Nie znaleziono użytkownika'
                )
            );
            return $app->redirect(
                $app['url_generator']->generate(
                    '/files'
                ),
                301
            );
        }
    }
    
    /**
    * Show information about user
    *
    * @param Application $app     application object
    * @param Request     $request request
    *
    * @access public
    * @return mixed Generates page
    */
    public function view(Application $app, Request $request)
    {
        $id_user = (int) $request -> get('id', 0);  //id usera
       
        $usersModel = new UsersModel($app);
        $user = $usersModel-> getUser($id_user);

        $files = $usersModel -> getFileByUser($id_user);
        $about = $usersModel -> getAboutByUser($id_user);
        
        $user_role = $usersModel -> getUserRole($id_user);
        $role = $usersModel -> getRoleName($user_role['id_role']);
        
        $check = $usersModel->checkUserId($id_user);

        if ($check) {
            try {
                return $app['twig']->render(
                    'users/info.twig',
                    array(
                        'user' => $user,
                        'files' => $files,
                        'about' => $about,
                        'id_user' => $id_user,
                        'role' => $role
                    )
                );
            } catch (Exception $e) {
                    $app['session']->getFlashBag()->add(
                        'message',
                        array(
                        'type' => 'error',
                        'content' => 'Nie znaleziono użytkownika'
                        )
                    );
            }
                    return $app['twig']->render(
                        'users/view.twig',
                        array(
                        'files' => $files,
                        'user' => $user,
                        'about' => $about,
                        'id_user' => $id_user,
                        'role' => $role
                        
                        )
                    );
        } else {
                    $app['session']->getFlashBag()->add(
                        'message',
                        array(
                            'type' => 'danger',
                            'content' => 'Nie znaleziono użytkownika'
                        )
                    );
                    return $app->redirect(
                        $app['url_generator']->generate(
                            '/users/'
                        ),
                        301
                    );
        }
    
    }
}
