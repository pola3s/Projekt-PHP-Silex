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
			
			return $app['twig']->render(
                'grades/index.twig', array(
                'averageGrade' => $averageGrade['AVG(grade)'],
				'id_file' => $id
                )
            );
	}


	public function add(Application $app, Request $request)
    {
		$idfile = (int)$request->get('id_file');
		
		$gradesModel = new GradesModel($app);
        $choiceGrade = $gradesModel->getGradesDict();

		
    
		
		
		
		$iduser = 3; ///ZMIENI� !!!!
		
		$data = array(
                'id_file' => $idfile,
                'id_user' => $iduser
            );
			
	
		
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
         return $app->redirect($app['url_generator']->generate('files'), 301);
      }
        return $app['twig']
            ->render('grades/add.twig', array('form' => $form->createView()));
    }
		
		
		
		
        
        
}
	
