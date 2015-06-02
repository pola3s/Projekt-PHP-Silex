<?php

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


class UsersController implements ControllerProviderInterface
{
    protected $_model;
	
	protected $_user;

	
    public function connect(Application $app)
    {
        $usersController = $app['controllers_factory'];//definiowanie �cie�ek rutowania
        $usersController->get('/list/', array($this, 'index'))->bind ('/users/');
        $usersController->match('/add/{id}', array($this, 'add'))->bind('/users/add');
        $usersController->match('/edit/{id}', array($this, 'edit'))->bind('/users/edit');
        $usersController->match('/delete/{id}', array($this, 'delete'))->bind('/users/delete');
        $usersController->match('/view/{id}', array($this, 'view'))->bind('/users/view');
		$usersController->match('/panel/', array($this, 'panel'))->bind('/users/panel');
		

        return $usersController;
    }

    public function index(Application $app)
    {
        $usersModel = new UsersModel($app);
        $users = $usersModel->getUserList();
        return $app['twig']->render('users/index.twig', array('users' => $users));
    }

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
            ->add('firstname', 'text', array(
                'constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 1)))
            ))
            ->add('lastname', 'text', array(
                'constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 1)))
            ))
            ->add('login', 'text', array(
                'constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 5)))
            ))
            ->add('password', 'password', array(
                'constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 5)))
            ))
			 ->add('confirm_password', 'password', array(
                'constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 5)))
            ))
            
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
                                'content' => 'Konto zosta�o stworzone'
                            )
                        );
                        return $app->redirect(
                            $app['url_generator']->generate(
                                '/auth/login'
                            ), 301
                        );

                    } catch (\Exception $e) {

                        $errors[] = 'Co� posz�o niezgodnie z planem';
                    }

                } else {
                    $app['session']->getFlashBag()->add(
                        'message', array(
                            'type' => 'warning',
                            'content' => 'Has�a nie s� takie same'
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
                        'content' => 'U�ytkownik o tym nicku ju� istnieje'
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
    public function edit(Application $app, Request $request)
    {
        $usersModel = new UsersModel($app);
        $id = (int) $request->get('id', 0);
        $user = $usersModel->getUser($id);

        //pobieram  z formularza


        $data = array(
            'id' => $user['id_user'],
            'firstname' => $user['firstname'],
            'lastname' => $user['lastname'],
            'login' => $user['login'],
            'password' => $user['login'],
            'confirm_password' => $user['login']
        );
        if (count($user)) {

            $form = $app['form.factory']->createBuilder('form', $data)
                ->add('firstname', 'text', array(
                    'constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 1)))
                ))
                ->add('lastname', 'text', array(
                    'constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 1)))
                ))
                ->add('login', 'text', array(
                    'constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 5)))
                ))
                ->add('password', 'password', array(
                    'constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 5)))
                ))
                ->add('confirm_password', 'password', array(
                    'constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 5)))
                ))
                
                ->add('save', 'submit')
                ->getForm();

            $form->handleRequest($request);

            if ($form->isValid()) {
                $usersModel = new UsersModel($app);
                $usersModel->saveUser($form->getData());
                return $app->redirect($app['url_generator']->generate('/users/'), 301);
            }

            return $app['twig']->render('users/edit.twig', array('form' => $form->createView(), 'user' => $user));

        } else {
            return $app->redirect($app['url_generator']->generate('/users/add'), 301);
        }
    }

    public function delete(Application $app, Request $request)
    {
        $id = (int) $request -> get('id_user');
        return $app->redirect($app['url_generator']->generate('/users/'), 301);
    }

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
	
	public function view(Application $app, Request $request)
	{
		
		
		$id_user = (int) $request -> get('id', 0);  //id usera
		
	   
		$usersModel = new UsersModel($app);
		$user = $usersModel-> getUser($id_user);


		$files = $usersModel -> getFileByUser($id_user);
		$about = $usersModel -> getAboutByUser($id_user);
		
		return $app['twig']->render('users/view.twig', array( 
			'files' => $files, 
			'user' => $user,
			'about' => $about,
			'id_user' => $id_user
			
		));
	
	}
	
}