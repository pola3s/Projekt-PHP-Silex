<?php
/**
 * About controller
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
use Form\AboutForm;
use Model\UsersModel;
use Model\FilesModel;
use Model\AboutModel;

/**
 * Class Aboutcontroller
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
 * @uses     Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;
 * @uses     Model\UsersModel;
 * @uses     Model\FilesModel;
 * @uses     Model\AboutModel;
 */
class AboutController implements ControllerProviderInterface
{
    /**
    * AboutModel object.
    *
    * @var    $model
    * @access protected
    */
    protected $model;
    
    /**
    * UsersModel object
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
            $this->_model = new AboutModel($app);
            $this->_user = new UsersModel($app);
          
            $AboutController = $app['controllers_factory'];
            $AboutController->get('view/{id_user}', array($this, 'index'))
                ->bind('/about/');
             $AboutController->match('edit/{id}', array($this, 'edit'))
                 ->bind('/about/edit');
            $AboutController->match('add/{id}', array($this, 'add'))
                ->bind('/about/add');
            return $AboutController;
    }
        
       /**
        * Add about
        *
        * @param Application $app     application object
        * @param Request     $request request
        *
        * @access public
        * @return mixed Generates page
        */
    public function add(Application $app, Request $request)
    {
        
        $id_user = (int)$request->get('id', 0);
        

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
            
          
        if ($id_user == $id_current_user) {
            $form = $app['form.factory']
            ->createBuilder(new AboutForm(), $data)->getForm();
            $form->remove('id_about');
                
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
                            ),
                            301
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
            
            return $app['twig']->render(
                '403.twig'
            );
    }
        
        /**
        * Edit about
        *
        * @param Application $app     application object
        * @param Request     $request request
        *
        * @access public
        * @return mixed Generates page
        */
        public function edit(Application $app, Request $request)
        {
            $aboutModel = new AboutModel($app);
            $id_user = (int) $request->get('id', 0);
            
            $about = $aboutModel->getAbout($id_user);
            $id_about = $about['id_about'];
            
            $check = $aboutModel->checkAboutId($id_about);
        
        if ($check) {
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
            
            
            if ($id_user == $id_current_user) {
                if (count($about)) {
                    $form = $app['form.factory']
                    ->createBuilder(new AboutForm(), $about)->getForm();
                    
                    $form->handleRequest($request);
            
                    if ($form->isValid()) {
                        try {
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
                                ),
                                301
                            );
                        } catch (\Exception $e) {
                            $errors[] = 'Nie udało się dodać "o mnie".';
                        }
                    }
                    return $app['twig']->render(
                        'about/edit.twig',
                        array(
                        'form' => $form->createView(),
                        'about' => $about)
                    );
                    
                } else {
                    return $app->redirect(
                        $app['url_generator']->generate(
                            '/about/add'
                        ),
                        301
                    );
                }
            
        

            }return $app['twig']->render(
                '403.twig'
            );
        } else {
                    $app['session']->getFlashBag()->add(
                        'message',
                        array(
                            'type' => 'danger',
                            'content' => 'Nie znaleziono "o mnie"!'
                        )
                    );
                    return $app->redirect(
                        $app['url_generator']->generate(
                            '/users/panel',
                            array(
                            'id' => $id_user,
                            )
                        ),
                        301
                    );
        }
        }
}
