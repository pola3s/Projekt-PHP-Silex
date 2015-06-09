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
use Model\GradesModel;
 
class GradesController implements ControllerProviderInterface
{
   
protected $_model;

protected $_user;

protected $_files;

    public function connect(Application $app)
    {
        $this->_model = new GradesModel($app);
        $this->_user = new UsersModel($app);
        $this->_files = new FilesModel($app);
        $gradesController = $app['controllers_factory'];
        $gradesController->match('/view/{id_file}', array($this, 'index'))
            ->value('page', 1)
            ->bind('/grades/');
        $gradesController ->match('/view/add/{id_file}', array($this, 'add'))
            ->bind('/grades/add');
       
        return $gradesController;
    }

    public function index(Application $app, Request $request)
    {
        $id = (int)$request->get('id_file', 0);
		$filesModel = new FilesModel($app);
	    $gradesModel = new GradesModel($app);
	    $averageGrade = $gradesModel ->getGrades($id);
		
		$roundGrade = round($averageGrade['AVG(grade)'], 2);

		return $app['twig']->render(
        'grades/index.twig', array(
                'roundGrade' => $roundGrade,
	            'id_file' => $id
				)
			);
	}


	public function add(Application $app, Request $request)
    {
		$id_file = (int)$request->get('id_file');
		
		$gradesModel = new GradesModel($app);
        $choiceGrade = $gradesModel->getGradesDict();

		$filesModel = new FilesModel($app);
		$file = $filesModel -> getFile($id_file);
		
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
		
		if($file['id_user'] = $id_user){
			
			$app['session']->getFlashBag()->add(
                        'message', array(
                            'type' => 'warning',
                            'content' => 'Nie możesz ocenić własnego zdjęcia!'
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
		}else{
		
				$data = array(
					'id_file' => $id_file,
					'id_user' => $id_user
				);
				
				$grade = $gradesModel->checkGrade($id_file, $id_user);
			
				if($grade){
				
					$app['session']->getFlashBag()->add(
							'message', array(
								'type' => 'warning',
								'content' => 'Dodałeś już ocenę do tego zdjęcia!'
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
				}else{
			
			
					$form = $app['form.factory']->createBuilder('form', $data)
						->add(
							'grade',
							'choice', array(
								'choices' => $choiceGrade
							)
							)
						->getForm();
				
					$form->handleRequest($request);

					if ($form->isValid()) {
						$gradesModel = new GradesModel($app);
						$data = $form->getData();
				  
						$gradesModel->addGrade($data);
						$app['session']->getFlashBag()->add(
								'message', array(
									'type' => 'success',
									'content' => 'Ocena została dodana'
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
						return $app['twig']
							->render('grades/add.twig', array('form' => $form->createView(), 'file' => $file));
					}
	
			}
	}
		
		
		
		
        
        
}
	
?>
