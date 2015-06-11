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
    protected $model;
    
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
        $usersModel = new UsersModel($app);
        $users = $usersModel->getUserList();
        return $app['twig']->render('users/index.twig', array('users' => $users));
    }
    
    /**
    * Add new user
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
        $data = array(
          'firstname' => '',
          'lastname' => '',
          'login' => '',
          'password' => ''
         );

        $form = $app['form.factory']->createBuilder('form', $data)
            ->add(
                'firstname', 'text', array(
                'constraints' => array(
                new Assert\NotBlank(), 
                new Assert\Length(
                    array('min' => 1)
                )
                )
                )
            )
            ->add(
                'lastname', 'text', array(
                'constraints' => array(
                new Assert\NotBlank(), 
                new Assert\Length(
                    array('min' => 1)
                )
                )
                )
            )
            ->add(
                'login', 'text', array(
                'constraints' => array(
                new Assert\NotBlank(), 
                new Assert\Length(
                    array('min' => 5)
                )
                )
                )
            )
            ->add(
                'password', 'password', array(
                'constraints' => array(
                new Assert\NotBlank(), 
                new Assert\Length(
                    array('min' => 5)
                )
                )
                )
            )
             ->add(
                 'confirm_password', 'password', array(
                 'constraints' => array(
                 new Assert\NotBlank(), 
                 new Assert\Length(
                     array('min' => 5)
                 )
                 )
                 )
             )
            
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();

            $check = $usersModel
                ->getUserByLogin($data['login']);

            if (!$check) {
                if ($data['password'] === $data['confirm_password']) {

                    $password = $app['security.encoder.digest']
                        ->encodePassword($data['password'], '');
                        
                    try {
                        $usersModel = new UsersModel($app);

                        $usersModel->register($data, $password);


                        $app['session']->getFlashBag()->add(
                            'message', array(
                                'type' => 'success',
                                'content' => 'Konto zostało stworzone'
                            )
                        );
                        return $app->redirect(
                            $app['url_generator']->generate(
                                '/auth/login'
                            ), 301
                        );

                    } catch (\Exception $e) {

                        $errors[] = 'Coś poszło niezgodnie z planem';
                    }

                } else {
                    $app['session']->getFlashBag()->add(
                        'message', array(
                            'type' => 'warning',
                            'content' => 'Hasła nie są takie same'
                        )
                    );
                    return $app['twig']->render(
                        'users/add.twig', array(
                            'form' => $form->createView()
                        )
                    );
                }
            } else {
                $app['session']->getFlashBag()->add(
                    'message', array(
                        'type' => 'danger',
                        'content' => 'Użytkownik o tym nicku już istnieje'
                    )
                );
                return $app['twig']->render(
                    'users/add.twig', array(
                        'form' => $form->createView()
                    )
                );
            }

        }

        return $app['twig']->render(
            'users/add.twig', array(
                'form' => $form->createView()
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
            
            
        if ($usersModel ->_isLoggedIn($app)) {
            $id_user = $usersModel -> getIdCurrentUser($app);
                
        } else {
            return $app->redirect(
                $app['url_generator']->generate(
                    'auth_login'
                ), 301
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
            $form = $app['form.factory']->createBuilder('form', $data)
            ->add(
                'login', 'text', array(
                        'label' => 'Login',
                        'constraints' => array(
                        new Assert\NotBlank()
                        )
                )
            )
            ->add(
                'email', 'text', array(
                        'label' => 'Email',
                        'constraints' => array(
                        new Assert\NotBlank(),
                        new Assert\Email(
                            array(
                                'message' => 'Wrong email'
                            )
                        )
                        )
                )
            )
            ->add(
                'firstname', 'text', array(
                        'label' => 'Imię',
                        'constraints' => array(
                        new Assert\NotBlank(),
                        new Assert\Length(
                            array(
                                'min' => 3
                            )
                        )
                        )
                )
            )
            ->add(
                'lastname', 'text', array(
                        'label' => 'Nazwisko',
                        'constraints' => array(
                        new Assert\NotBlank(),
                        new Assert\Length(
                            array('min' => 3)
                        )
                        )
                )
            )
            ->add(
                'password', 'password', array(
                        'label' => 'Nowe hasło',
                        'constraints' => array(
                        new Assert\NotBlank()
                        )
                )
            )
            ->add(
                'confirm_password', 'password', array(
                        'label' => 'Potwierdź hasło',
                        'constraints' => array(
                        new Assert\NotBlank()
                        )
                )
            )
            ->getForm();


            $form->handleRequest($request);

            if ($form->isValid()) {
                $data = $form->getData();
                var_dump($data);

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
                            $data['password'], ''
                        );


                    $checkLogin = $usersModel
                        ->getUserByLogin(
                            $data['login']
                        );

                    if ($data['login'] === $checkLogin 
                        || !$checkLogin 
                        || (int)$currentUserInfo['id_user'] ===(int)$checkLogin['id_user']
                    ) {
                        try
                        {
                            $usersModel->updateUser(
                                $currentUserInfo['id_user'],
                                $form->getData(),
                                $password
                            );
                            
                            var_dump($data);

                            $app['session']->getFlashBag()->add(
                                'message', array(
                                    'type' => 'success',
                                    'content' => 'Edycja konta udała się,
                                    możesz się teraz ponownie zalogować'
                                )
                            );
                            return $app->redirect(
                                $app['url_generator']
                                    ->generate(
                                        'files'
                                    ), 301
                            );
                        }
                        catch (\Exception $e)
                        {
                            $errors[] = 'Edycja konta nie powiodła się';
                        }

                    } else {
                        $app['session']->getFlashBag()->add(
                            'message', array(
                                'type' => 'warning',
                                'content' => 'Login zajęty'
                            )
                        );
                        return $app['twig']->render(
                            'users/edit.twig', array(
                                'form' => $form->createView(),
                                'login' => $currentUser
                            )
                        );
                    }
                } else {
                    $app['session']->getFlashBag()->add(
                        'message', array(
                            'type' => 'warning',
                            'content' => 'Hasła różnią się'
                        )
                    );
                    return $app['twig']->render(
                        'users/edit.twig', array(
                            'form' => $form->createView(),
                            'login' => $currentUser
                        )
                    );

                }
            }
            return $app['twig']->render(
                'users/edit.twig', array(
                    'form' => $form->createView(),
                    'login' => $currentUser
                )
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

        if (count($id_user)) {
            return $app['twig']->render(
                'users/info.twig', array(
                    'user' => $user,
                    'files' => $files, 
                    'about' => $about,
                    'id_user' => $id_user
                )
            );
        } else {
            $app['session']->getFlashBag()->add(
                'message', array(
                    'type' => 'danger',
                    'content' => 'Nie znaleziono użytkownika'
                )
            );
            return $app->redirect(
                $app['url_generator']->generate(
                    '/files'
                ), 301
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
        
        
        $check = $usersModel->checkUserId($id_user);

        if ($check) {
        
                    return $app['twig']->render(
                        'users/view.twig', array( 
                        'files' => $files, 
                        'user' => $user,
                        'about' => $about,
                        'id_user' => $id_user
                        
                        )
                    );
        } else {
                    $app['session']->getFlashBag()->add(
                        'message', array(
                            'type' => 'danger',
                            'content' => 'Nie znaleziono użytkownika'
                        )
                    );
                    return $app->redirect(
                        $app['url_generator']->generate(
                            '/users/'
                        ), 301
                    );
        }
    
    }
    
}