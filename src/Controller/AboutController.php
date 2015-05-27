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
				->value('page', 1)
				->bind('/about/');
			 $AboutController->get('edit/{id_user}', array($this, 'edit'))  // /projekt/web/edit/1 
				->value('page', 1)
				->bind('/about/edit');
			$AboutController->match('add/{id_user}', array($this, 'add'))		  // /projekt/web/about/add/1
				->value('page', 1)
				->bind('/about/add/');
			return $AboutController;
		}
		
	   public function add(Application $app, Request $request)
	   {
		
		$id_user = (int)$request->get('id_user', 0);
		
		$UsersModel = new UsersModel($app);
		$check = $UsersModel->checkUserId($id_user);

		$check = 1;
		

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
               
				->add('save', 'submit', array('label' => 'Upload file'))
				->getForm();
			
		if ($request->isMethod('POST')) {
			$form->bind($request);
			
		if ($form->isValid()) {
			try {
			
			$data = $form->getData();
			
			$AboutModel->saveAbout($data);
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

			

           
        }else {
            $app['session']->getFlashBag()->add(
                'message', array(
                    'type' => 'danger',
                    'content' => 'Nie znaleziono komentarza'
                )
            );
            return $app->redirect(
                $app['url_generator']->generate(
                    'files'
                ), 301
            );
        }
	   }
		
    
}