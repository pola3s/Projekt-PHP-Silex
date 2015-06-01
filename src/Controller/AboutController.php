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
				->add('email', 'text', array(
					'constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 1)))
				))
				->add('phone', 'text', array(
					'constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 1)))
				))
				->add('description', 'text', array(
					'constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 5)))
				))
				->add('website', 'text', array(
					'constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 5)))
				))
				->add('city', 'text', array(
					'constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 5)))
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
				'files'
				), 301
			);
			} catch (Exception $e) {
				$app['session']->getFlashBag()->add(
				'message',
					array(
					'type' => 'error',
					'content' => 'Nie można dodać "o mnie".'
					)
				);
				}
			} else {
				$app['session']->getFlashBag()->add(
				'message',
					array(
						'type' => 'error',
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
					->add('email', 'text', array(
						'constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 1)))
					))
					->add('phone', 'text', array(
						'constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 1)))
					))
					->add('description', 'text', array(
						'constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 5)))
					))
					->add('website', 'text', array(
						'constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 5)))
					))
					->add('city', 'text', array(
						'constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 5)))
					))
               
				->add('save', 'submit')
				->getForm();
					
			$form->handleRequest($request);
			
			if ($form->isValid()) {
					$aboutModel = new AboutModel($app);
					$data = $form->getData();
					$aboutModel->saveAbout2($data, $id_user);
					
					return $app->redirect($app['url_generator']->generate('files'), 301);
			}
					return $app['twig']->render('about/edit.twig', array('form' => $form->createView(), 'about' => $about));
					
			} else {
			
					return $app->redirect($app['url_generator']->generate('/about/add'), 301);
			}

	}
		
    
}