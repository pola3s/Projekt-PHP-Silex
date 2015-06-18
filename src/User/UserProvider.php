<?php
/**
 * Users Provider
 *
 * PHP version 5
 *
 * @category UserProvider
 * @package  UserProvider
 * @author   Paulina Serwińska <paulina.serwinska@gmail.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     wierzba.wzks.uj.edu.pl/~12_serwinska
 */
namespace User;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

use Model\UsersModel;

/**
 * Class UserProvider.
 *
 * @author   Paulina Serwińska <paulina.serwinska@gmail.com>
 * @package Provider
 * @use Silex\Application
 * @use Symfony\Component\Security\Core\User\UserProviderInterface
 * @use Symfony\Component\Security\Core\User\UserInterface
 * @use Symfony\Component\Security\Core\User\User
 * @use Symfony\Component\Security\Core\Exception\UsernameNotFoundException
 * @use Symfony\Component\Security\Core\Exception\UnsupportedUserException
 * @use Model\UsersModel
 */
class UserProvider implements UserProviderInterface
{
   /**
    * Silex application.
    *
    * @access protected
    * @var Silex\Application $app
    */
    protected $app;

   /**
    * Object constructor.
    *
    * @access public
    * @param Silex\Application $app Silex application
    */
    public function __construct($app)
    {
        $this->_app = $app;
    }

    
   /**
    * Load user by username.
    *
    * @access public
    * @param string $login User login
    *
    * @return User Result
    */
    public function loadUserByUsername($login)
    {
        $userModel = new UsersModel($this->_app);
        $user = $userModel->loadUserByLogin($login);
        return new User(
            $user['login'],
            $user['password'],
            $user['roles'],
            true,
            true,
            true,
            true
        );
    }

   /**
    * Refresh user.
    *
    * @access public
    * @param UserInterface $user User
    *
    * @return User Result
    */
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(
                sprintf(
                    'Instances of "%s" are not supported.',
                    get_class(
                        $user
                    )
                )
            );
        }
        return $this->loadUserByUsername($user->getUsername());
    }

   /**
    * Check if supports selected class.
    *
    * @access public
    * @param string $class Class name
    *
    * @return bool
    */
    public function supportsClass($class)
    {
        return $class === 'Symfony\Component\Security\Core\User\User';
    }
}
