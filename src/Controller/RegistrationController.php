<?php

namespace Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form;
use Model\UsersModel;


class RegistrationController implements ControllerProviderInterface
{
    protected $_model;

    public function connect(Application $app)
    {
        $this->_model = new UsersModel($app);
        $authController = $app['controllers_factory'];
        $authController->match('/', array($this, 'register'))
            ->bind('/register/');
        $authController->match('/success', array($this, 'success'))
            ->bind('/register/success');
        return $authController;
    }

   
    public function register(Application $app, Request $request)
    {
        $data = array();
        $form = $app['form.factory']->createBuilder('form', $data)
		
			
            ->add(
                'login', 'text', array(
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
                                'message' => 'Email nie jest poprawny'
                            )
                        )
                    )
                )
            )
            ->add(
                'firstname', 'text', array(
                    'label' => 'Imie',
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
                    'label' => 'Haslo',
                    'constraints' => array(
                        new Assert\NotBlank()
                    )
                )
            )
            ->add(
                'confirm_password', 'password', array(
                    'label' => 'Potwierdz haslo',
                    'constraints' => array(
                        new Assert\NotBlank()
                    )
                )
            )
            ->add('save', 'submit', array('label' => 'Zarejestruj'))
			->getForm();


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
                    ->encodePassword($data['password'], '');

                $checkLogin = $this->_model->getUserByLogin(
                    $data['login']
                );
				
                if (!$checkLogin) {
                    try
                    {
                        $this->_model->register(
                            $form->getData(),
                            $password
                        );
               
					return $app->redirect(
						$app['url_generator']->generate(
						 '/register/success'
						)	, 301
					);
                    }
				
                    catch (\Exception $e)
                    {
                        $errors[] = 'Rejestracja siê nie powiod³a,
                        spróbuj jeszcze raz';
                    }
                } else {
                    $app['session']->getFlashBag()->add(
                        'message', array(
                            'type' => 'warning', 
							'content' => 'Login zajêty'
                        )
                    );
                    return $app['twig']->render(
                        'users/register.twig', array(
                            'form' => $form->createView()
                        )
                    );
                }
            } else {
                $app['session']->getFlashBag()->add(
                    'message', array(
                        'type' => 'warning',
                        'content' => 'Has³a ró¿ni¹ siê miêdzy sob¹'
                    )
                );
                return $app['twig']->render(
                    'users/register.twig', array(
                        'form' => $form->createView()
                    )
                );
            }
        }

        return $app['twig']->render(
            'users/register.twig', array(
                'form' => $form->createView()
            )
        );
    }

    public function success(Application $app)
    {
        $link = $app['url_generator']->generate(
            'auth_login'
        );
        return $app['twig']->render(
            'users/successfulRegistration.twig', array(
                'auth_login' => $link
            )
        );
    }
}
