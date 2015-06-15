<?php
/**
 * Registration controller
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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form;
use Model\UsersModel;
use Form\RegistrationForm;

/**
 * Class RegistrationController
 *
 * @category Controller
 * @package  Controller
 * @author   Paulina Serwińska <paulina.serwinska@gmail.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version  Release: <package_version>
 * @link     wierzba.wzks.uj.edu.pl/~12_serwinska
 * @uses     Silex\Application;
 * @uses     Silex\ControllerProviderInterface;
 * @uses     Symfony\Component\HttpFoundation\Request;
 * @uses     Symfony\Component\Validator\Constraints as Assert;
 * @uses     Symfony\Component\Form;
 * @uses     Model\UsersModel;
 */     
class RegistrationController implements ControllerProviderInterface
{
    protected $_model;
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
        $this->_model = new UsersModel($app);
        $authController = $app['controllers_factory'];
        $authController->match('/', array($this, 'register'))
            ->bind('/register/');
        $authController->match('/success', array($this, 'success'))
            ->bind('/register/success');
        return $authController;
    }

    /**
    * Add new user to database
    *
    * @param Application $app     application object
    * @param Request     $request request
    *
    * @access public
    * @return mixed Generates page
    */  
    public function register(Application $app, Request $request)
    {
        $data = array();
        
		$form = $app['form.factory']
			->createBuilder(new RegistrationForm(), $data)->getForm();
		$form->remove('id_user');


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
                            ), 301
                        );
                    }
                
                    catch (\Exception $e)
                    {
                        $errors[] = 'Rejestracja się nie powiodła,
                        spróbuj jeszcze raz';
                    }
                } else {
                    $app['session']->getFlashBag()->add(
                        'message', array(
                            'type' => 'warning', 
                        'content' => 'Login zajęty'
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
                        'content' => 'Hasła różnią się między sobą!'
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
    
    /**
    * Generates page with information about successful registration
    *
    * @param Application $app application object
    *
    * @access public
    * @return mixed
    */  
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
