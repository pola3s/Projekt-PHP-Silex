<?php
/**
 * Auth controller.
 *
 * @author EPI <epi@uj.edu.pl>
 * @link http://epi.uj.edu.pl
 * @copyright 2015 EPI
 */

namespace Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Model\UsersModel;

/**
 * Class AuthController.
 *
 * @package Controller
 * @implements ControllerProviderInterface
 */
class AuthController implements ControllerProviderInterface
{
    /**
     * Data for view.
     *
     * @access protected
     * @var array $view
     */
    protected $view = array();

    /**
     * Routing settings.
     *
     * @access public
     * @param Silex\Application $app Silex application
     * @return AlbumsController Result
     */
    public function connect(Application $app)
    {
        $authController = $app['controllers_factory'];
        $authController->match('login', array($this, 'login'))
            ->bind('auth_login');
        $authController->match('logout', array($this, 'logout'))
            ->bind('auth_logout');
        return $authController;
    }

    /**
     * Login action.
     *
     * @access public
     * @param Silex\Application $app Silex application
     * @param Symfony\Component\HttpFoundation\Request $request Request object
     * @return string Output
     */
	public function login(Application $app, Request $request)
    {
	
		//$user = array(
        //    'login' => $app['session']->get('_security.last_username')
        //);
		
        $data = array();

        $form = $app['form.factory']->createBuilder('form')
            ->add(
                'username', 'text', array(
                    'label' => 'Login',
                    'data' => $app['session']
                            ->get(
                                '_security.last_username'
                            )
                )
            )
            ->add(
                'password', 'password', array(
                    'label' => 'Haslo'
                )
            )
            ->add('Zaloguj', 'submit')
            ->getForm();

        return $app['twig']->render(
            'auth/login.twig', array(
                'form' => $form->createView(),
                'error' => $app['security.last_error']($request)
            )
        );
	}

    /**
     * Logout action.
     *
     * @access public
     * @param Silex\Application $app Silex application
     * @param Symfony\Component\HttpFoundation\Request $request Request object
     * @return string Output
     */
    public function logout(Application $app, Request $request)
    {
        $app['session']->clear();
        return $app['twig']->render('auth/logout.twig', $this->view);
    }
}