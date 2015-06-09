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

class AboutController implements ControllerProviderInterface{


    protected $_model;
	
	protected $_user;


	
   
      public function connect(Application $app)
	  {
			$this->_model = new AboutModel($app);
            $this->_user = new UsersModel($app);
          
            $AboutController = $app['controllers_factory'];
            $AboutController->get('view/{id_user}', array($this, 'index'))  // /projekt/web/about/view/1 
				->bind('/about/');
			 $AboutController->match('edit/{id}', array($this, 'edit'))  // /projekt/web/edit/1 
				->bind('/about/edit');
			$AboutController->match('add/{id}', array($this, 'add'))		  // /projekt/web/about/add/1
				->bind('/about/add');
			return $AboutController;
		}
		
	   public function add(Application $app, Request $request)
	   {
		
		$id_user = (int)$request->get('id', 0);
		
		$UsersModel = new UsersModel($app);
		$check = $UsersModel->checkUserId($id_user);

		if ($check) {

          $form = $app['form.factory']->createBuilder('form', $data)
					->add(
					'email', 'text', array(
						'label' => 'Email',
						'constraints' => array(
							new Assert\NotBlank(),
							new Assert\Email(
								array(
									'message' => 'Email nie jest poprawny'
								)
							),
							new Assert\Type(
								array('type' => 'string')
							)
						)
					)
				)
				->add(
					'phone', 'text', array(
						'constraints' => array(
							new Assert\NotBlank(), 
								new Assert\Length(
									array('min' => 5)
							),
							 new Assert\Regex(
								array(
									'pattern' => 
										"/^([0-9]{9})|(([0-9]{3}-){2}[0-9]{3})$/"
								)
							)
						)
					)
				)
				->add('description', 'text', array(
					'constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 5)))
				))
				->add(
					'website', 'text', array(
						'constraints' => array(
							new Assert\NotBlank(),
							new Assert\Length(
								array('min' => 5)
							),
							new Assert\Url()
							)
					)
				)
				->add('city', 'text', array(
					'constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 2)))
				))
               
				->add('save', 'submit')
				->getForm();
			
		if ($request->isMethod('POST')) {
			$form->bind($request);
			
		if ($form->isValid()) {
			try {
			
			$data = $form->getData();
			
			$aboutModel = new AboutModel($app);
			$aboutModel->saveAbout($data, $id_user);
			
			$app['session']->getFlashBag()->add(
			'message',
				array(
					'type' => 'success',
					'content' => 'Dodano "o mnie".'
				)
			);
			return $app->redirect(
						$app['url_generator']->generate(
								'/users/panel', 
									array(
										'id' => $id_user,
									)	
						), 301
			);
			} catch (Exception $e) {
				$app['session']->getFlashBag()->add(
				'message',
					array(
					'type' => 'danger',
					'content' => 'Nie można dodać "o mnie".'
					)
				);
				}
			} else {
				$app['session']->getFlashBag()->add(
				'message',
					array(
						'type' => 'danger',
						'content' => 'Niepoprawne dane.'
						)
				);
				}
			}
			
				return $app['twig']->render(
				'about/add.twig',
					array(
						'form' => $form->createView()
					)
				);
			}
	   }
	   
	 public function edit(Application $app, Request $request)
	 {
			$aboutModel = new AboutModel($app);
			$id_user = (int) $request->get('id', 0);
			
			$about = $aboutModel->getAbout($id_user);
			
		
		
			if (count($about)) {
				$form = $app['form.factory']->createBuilder('form', $about)
					->add(
					'email', 'text', array(
						'label' => 'Email',
						'constraints' => array(
							new Assert\NotBlank(),
							new Assert\Email(
								array(
									'message' => 'Email nie jest poprawny'
								)
							),
							new Assert\Type(
								array('type' => 'string')
							)
						)
					)
				)
				->add(
					'phone', 'text', array(
						'constraints' => array(
							new Assert\NotBlank(), 
								new Assert\Length(
									array('min' => 5)
							),
							new Assert\Regex(
								array(
									'pattern' => 
										"/^([0-9]{9})|(([0-9]{3}-){2}[0-9]{3})$/"
								)
							)
						)
					)
				)
				->add('description', 'text', array(
					'constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 5)))
				))
				->add(
					'website', 'text', array(
						'constraints' => array(
							new Assert\NotBlank(),
							new Assert\Length(
								array('min' => 5)
							),
							new Assert\Url()
							)
					)
				)
				->add('city', 'text', array(
					'constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 2)))
				))
               
				->add('save', 'submit')
				->getForm();
					
			$form->handleRequest($request);
			
			if ($form->isValid()) {
					$aboutModel = new AboutModel($app);
					$data = $form->getData();
					$aboutModel->editAbout($data, $id_user);
					
					$app['session']->getFlashBag()->add(
						'message',
						
							array(
								'type' => 'success',
								'content' => 'Edytowano "o mnie".'
								)
					);
		
					
					return $app->redirect(
						$app['url_generator']->generate(
								'/users/panel', 
									array(
										'id' => $id_user,
									)	
						), 301
					);
			}
					return $app['twig']->render('about/edit.twig', array('form' => $form->createView(), 'about' => $about));
					
			} else {
			
					return $app->redirect($app['url_generator']->generate('/about/add'), 301);
			}

	}
		
    
}