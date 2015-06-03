<?php
require_once __DIR__.'/../vendor/autoload.php';
$app = new Silex\Application();
$app['debug'] = true;

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../src/views',
));

$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new Silex\Provider\FormServiceProvider());
$app->register(new Silex\Provider\ValidatorServiceProvider());
$app->register(new Silex\Provider\TranslationServiceProvider(), array(
    'translator.domains' => array(),
));

$app->register(new Silex\Provider\SessionServiceProvider());

$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
        'driver'    => 'pdo_mysql',
        'host'      => 'localhost',
        'dbname'    => '12_serwinska',
        'user'      => '12_serwinska',
        'password'  => 'Z10w7s3k1k8',
        'charset'   => 'utf8',
    ),
));

$app->register(
    new Silex\Provider\SecurityServiceProvider(), array(
        'security.firewalls' => array(
            'admin' => array(
                'pattern' => '^.*$',
                'form' => array(
                    'login_path' => '/auth/login',
                    'check_path' => '/user_login_check',
                    'default_target_path'=> '/',
                    'username_parameter' => 'form[username]',
                    'password_parameter' => 'form[password]',
                ),
                'logout'  => true,
                'anonymous' => true,
                'logout' => array(
                    'logout_path' => '/auth/logout'
                ),
                'users' => $app->share(
                    function() use ($app) {
                        return new User\UserProvider($app);
                    }
                ),
            ),
        ),
        'security.access_rules' => array(
			array('^/register/$', 'IS_AUTHENTICATED_ANONYMOUSLY'),
			array('^/register/.+$', 'IS_AUTHENTICATED_ANONYMOUSLY'),
			array('^/auth/.+$', 'IS_AUTHENTICATED_ANONYMOUSLY'),
			array('^/.$', 'IS_AUTHENTICATED_ANONYMOUSLY'),
            array('^/users/panel/+$', 'ROLE_USER'),
			array('^/users/view/.+$', 'ROLE_USER'),
			array('^/grades/.*$', 'ROLE_USER'),
			array('^/about/.*$', 'ROLE_USER'),
            array('^/comments/.*$', 'ROLE_USER'),
            array('^/files/.*$', 'ROLE_USER'),
			array('^/categories/.$', 'ROLE_ADMIN'),
			array('^/.+$', 'ROLE_ADMIN')
            


        ),
        'security.role_hierarchy' => array(
            'ROLE_ADMIN' => array('ROLE_USER', 'ROLE_ANONYMUS'),
            'ROLE_USER' => array('ROLE_ANONYMUS'),
        ),
    )
);

use Symfony\Component\HttpFoundation\Response;
$app->error(
    function (\Exception $e, $code) use ($app) {
        if ($code == 404) {
            return new Response(
                $app['twig']->render('404.twig'), 404
            );
        }
    }
);

$app->error(
    function (\Exception $e, $code) use ($app) {
        if ($code == 403) {
            return new Response(
                $app['twig']->render('403.twig'), 403
            );
        }

    }
);


$app->mount('/', new Controller\FilesController());
$app->mount('/users/', new Controller\UsersController());
$app->mount('/auth/', new Controller\AuthController());
$app->mount('/comments/', new Controller\CommentsController());
$app->mount('/grades/', new Controller\GradesController());
$app->mount('/about/', new Controller\AboutController());
$app->mount('/categories/', new Controller\CategoriesController());
$app->mount('/register/', new Controller\RegistrationController());
$app->run();





