<?php
/**
 * Grades controller
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
use Model\GradesModel;
 
/**
 * Class GradesController
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
 * @uses     Model\GradesModel;
 */
class GradesController implements ControllerProviderInterface
{
    /**
    * GradesModel object.
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
    * FilesModel object.
    *
    * @var    $files
    * @access protected
    */
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

    /**
    * Show grades
    *
    * @param Application $app     application object
    * @param Request     $request request
    *
    * @access public
    * @return page
    */
    public function index(Application $app, Request $request)
    {
        $id = (int)$request->get('id_file', 0);
        $filesModel = new FilesModel($app);
        $gradesModel = new GradesModel($app);
        $averageGrade = $gradesModel ->getGrades($id);
        
        $roundGrade = round($averageGrade['AVG(grade)'], 2);

        return $app['twig']->render(
            'grades/index.twig',
            array(
                'roundGrade' => $roundGrade,
                'id_file' => $id
            )
        );
    }

    /**
    * Add grade
    *
    * @param Application $app     application object
    * @param Request     $request request
    *
    * @access public
    * @return mixed Generates page
    */
    public function add(Application $app, Request $request)
    {
        $id_file = (int)$request->get('id_file');
        
        $gradesModel = new GradesModel($app);
        $choiceGrade = $gradesModel->getGradesDict();

        $filesModel = new FilesModel($app);
        $file = $filesModel -> getFile($id_file);
        
        $usersModel = new UsersModel($app);
        
        if ($usersModel ->isLoggedIn($app)) {
            $id_current_user = $usersModel -> getIdCurrentUser($app);
                
        } else {
             return $app->redirect(
                 $app['url_generator']->generate(
                     'auth_login'
                 ),
                 301
             );
        }
      
        if ($file['id_user'] == $id_current_user) {
            $app['session']->getFlashBag()->add(
                'message',
                array(
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
                ),
                301
            );
        } else {
            $data = array(
            'id_file' => $id_file,
            'id_user' => $id_current_user
            );
                
            $grade = $gradesModel->checkGrade($id_file, $id_current_user);
            
            if ($grade) {
                $app['session']->getFlashBag()->add(
                    'message',
                    array(
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
                    ),
                    301
                );
            } else {
                $form = $app['form.factory']->createBuilder('form', $data)
                ->add(
                    'grade',
					
                    'choice',
                    array(
                    'choices' => $choiceGrade,
					'label' => 'Ocena',
                    )
                )
				->add('Zapisz', 'submit', array('label' => 'Dodaj'))
                ->getForm();
                
                $form->handleRequest($request);

                if ($form->isValid()) {
                    try {
                        $gradesModel = new GradesModel($app);
                        $data = $form->getData();
                      
                        $gradesModel->addGrade($data);
                        $app['session']->getFlashBag()->add(
                            'message',
                            array(
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
                            ),
                            301
                        );
                    } catch (Exception $e) {
                        $errors[] = 'Nie udało się dodać oceny';
                    }
                }
                return $app['twig']
                ->render(
                    'grades/add.twig',
                    array(
                    'form' => $form->createView(), 'file' => $file
                    )
                );
            }
    
        }
    }
}
