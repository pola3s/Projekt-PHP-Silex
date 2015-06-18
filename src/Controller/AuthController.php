<?php
/**
 * Auth controller
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
use Model\UsersModel;

/**
 * Class AuthController
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
 * @uses     Symfony\Component\Validator\Constraints;
 * @uses     Model\UsersModel;
 */

class AuthController implements ControllerProviderInterface
{
    
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
        $authController = $app['controllers_factory'];
        $authController->match('login', array($this, 'login'))
            ->bind('auth_login');
        $authController->match('logout', array($this, 'logout'))
            ->bind('auth_logout');
        return $authController;
    }

    /**
     * Logging
     *
     * @param Application $app     application object
     * @param Request     $request request
     *
     * @access public
     * @return mixed Generates page
     */
    public function login(Application $app, Request $request)
    {
        $data = array();

        $form = $app['form.factory']->createBuilder('form')
            ->add(
                'username',
                'text',
                array(
                    'label' => 'Login',
                    'data' => $app['session']
                            ->get(
                                '_security.last_username'
                            )
                )
            )
            ->add(
                'password',
                'password',
                array(
                    'label' => 'Haslo'
                )
            )
            ->add('Zaloguj', 'submit')
            ->getForm();
          
        return $app['twig']->render(
            'auth/login.twig',
            array(
                'form' =>$form->createView(),
                'error' =>$app['security.last_error']($request)
                )
        );
            
    }

    /**
     * Logging out
     *
     * @param Application $app     application object
     * @param Request     $request request
     *
     * @access public
     * @return mixed Generates page
     */
    public function logout(Application $app, Request $request)
    {
        
        $app['session']->clear();
        $app['session']->getFlashBag()->add(
            'message',
            array(
            'type' => 'success',
            'content' => 'Zostałeś wylogowany!'
            )
        );
        return $app['twig']->render('auth/logout.twig', $this->view);
        
    }
}
