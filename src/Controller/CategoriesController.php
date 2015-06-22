<?php
/**
 * Categories controller
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
use Model\UsersModel;
use Model\FilesModel;
use Model\CategoriesModel;
use Form\CategoriesForm;
 
/**
 * Class CategoriesController
 *
 * @category Controller
 * @package  Controller
 * @author   Paulina Serwińska <paulina.serwinska@gmail.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version  Release: <package_version>
 * @link     wierzba.wzks.uj.edu.pl/~12_serwinska
 * @uses     Silex\Application;
 * @uses     Silex\ControllerProviderInterface;
 * @uses     Symfony\Component\Config\Definition\Exception\Exception;
 * @uses     Symfony\Component\HttpFoundation\Request;
 * @uses     Symfony\Component\Validator\Constraints as Assert;
 * @uses     Model\UsersModel;
 * @uses     Model\FilesModel;
 * @uses     Model\CategoriesModel;
 */
class CategoriesController implements ControllerProviderInterface
{
    /**
    * CategoriesModel object.
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
        $this->_model = new CategoriesModel($app);
        $this->_user = new UsersModel($app);
        $this->_files = new FilesModel($app);
        $categoriesController = $app['controllers_factory'];
        $categoriesController->match('', array($this, 'index'))
            ->bind('categories');
        $categoriesController->match('/add/', array($this, 'add'))
            ->bind('/categories/add');
        $categoriesController->match('/edit/{id}', array($this, 'edit'))
            ->bind('/categories/edit');
        $categoriesController
            ->match('/delete/{id}', array($this, 'delete'))
            ->bind('/categories/delete');
     
        return $categoriesController;
    }
    
    
    
    /**
    * Show categories
    *
    * @param Application $app application object
    *
    * @access public
    * @return mixed Generates page
    */
    public function index(Application $app)
    {
        try {
            $categoriesModel = new CategoriesModel($app);
            $categories = $categoriesModel->getCategories();
        } catch (\Exception $e) {
            $app->abort(403, "Wystąpił błąd, spróbuj ponownie później");
        }
        return $app['twig']->render(
            'categories/index.twig',
            array(
                    'categories' => $categories
                )
        );
    }
    
    /**
    * Add category
    *
    * @param Application $app     application object
    * @param Request     $request request
    *
    * @access public
    * @return mixed Generates page
    */
    public function add(Application $app, Request $request)
    {

        $form = $app['form.factory']
            ->createBuilder(new CategoriesForm(), $data)->getForm();
        $form->remove('id_category');

       
        $form->handleRequest($request);

        if ($form->isValid()) {
            try {
                $categoriesModel = new CategoriesModel($app);
                $data = $form->getData();
                    
                $categoriesModel->addCategory($data);
                      
                $app['session']->getFlashBag()->add(
                    'message',
                    array(
                          'type' => 'success',
                          'content' => 'Kategoria została dodana'
                      )
                );
                return $app->redirect(
                    $app['url_generator']->generate(
                        'categories'
                    ),
                    301
                );
            } catch (\PDOException $e) {
                $app->abort(500, "Nie udało się dodać kategorii. Spróbuj ponownie później");
            }
        }
            return $app['twig']
                 ->render(
                     '/categories/add.twig',
                     array(
                      'form' => $form->createView()
                      )
                 );
        
    }
    
    /**
    * Edit category
    *
    * @param Application $app     application object
    * @param Request     $request request
    *
    * @access public
    * @return mixed Generates page
    */
    public function edit(Application $app, Request $request)
    {
        $categoriesModel = new CategoriesModel($app);
        $id_category = (int) $request->get('id', 0);
            
        $category = $categoriesModel->getCategory($id_category);
        $check = $categoriesModel->checkCategoryId($id_category);
    
        if ($check) {
            if (count($category)) {
                $form = $app['form.factory']
                    ->createBuilder(new CategoriesForm(), $category)->getForm();
                            
                $form->handleRequest($request);
                    
                if ($form->isValid()) {
                    try {
                        $categoriesModel = new CategoriesModel($app);
                        $data = $form->getData();
                        $categoriesModel->editCategory($data, $id_category);
                            
                        $app['session']->getFlashBag()->add(
                            'message',
                            array(
                                'type' => 'success',
                                'content' => 'Kategoria została zmieniona'
                            )
                        );
                            
                        return $app->redirect(
                            $app['url_generator']->generate(
                                'categories'
                            ),
                            301
                        );
                    } catch (\PDOException $e) {
                        $app->abort(500, "Nie udało się edytować kategorii. Spróbuj ponownie później");
                    }
                }
                   
                    return $app['twig']->render(
                        'categories/edit.twig',
                        array(
                        'form' => $form->createView(),
                        'category' => $category
                        )
                    );
                            
            } else {
                    return $app->redirect(
                        $app['url_generator']->generate(
                            '/categories/add'
                        ),
                        301
                    );
            }
        } else {
                    $app['session']->getFlashBag()->add(
                        'message',
                        array(
                            'type' => 'danger',
                            'content' => 'Nie znaleziono kategorii!'
                        )
                    );
                    return $app->redirect(
                        $app['url_generator']->generate(
                            'categories'
                        ),
                        301
                    );
        }

    }
    
    /**
    * Delete category
    *
    * @param Application $app     application object
    * @param Request     $request request
    *
    * @access public
    * @return mixed Generates page
    */
    public function delete(Application $app, Request $request)
    {
        $id_category = (int) $request -> get('id', 0);
   
        $categoriesModel = new CategoriesModel($app);
        $check = $categoriesModel->checkCategoryId($id_category);
 
        if ($check) {
             $files = $categoriesModel->getFilesByCategory($id_category);
            
             
            if (!$files) {
                $category = $categoriesModel->getCategory($id_category);
        

                $data = array();

                if (count($category)) {
                    $form = $app['form.factory']->createBuilder('form', $data)
                        ->add(
                            'id_category',
                            'hidden',
                            array(
                            'data' => $id,
                            )
                        )
                        ->add('Tak', 'submit')
                        ->add('Nie', 'submit')
                        ->getForm();

                    $form->handleRequest($request);

                    if ($form->isValid()) {
                        if ($form->get('Tak')->isClicked()) {
                            $data = $form->getData();
                            try {
                                $model = $this->_model->deleteCategory($id_category);

                                $app['session']->getFlashBag()->add(
                                    'message',
                                    array(
                                        'type' => 'success',
                                        'content' =>
                                            'Kategoria została usunięta'
                                    )
                                );
                                return $app->redirect(
                                    $app['url_generator']->generate(
                                        'categories'
                                    ),
                                    301
                                );
                            } catch (\PDOException $e) {
                                $app->abort(500, "Nie udało się usunąć kategorii. Spróbuj ponownie później");
                            }
                        } else {
                            return $app->redirect(
                                $app['url_generator']->generate(
                                    'categories'
                                ),
                                301
                            );
                        }
                    }
                    return $app['twig']->render(
                        'categories/delete.twig',
                        array(
                            'form' => $form->createView()
                        )
                    );
                } else {
                    $app['session']->getFlashBag()->add(
                        'message',
                        array(
                            'type' => 'danger',
                            'content' => 'Nie znaleziono kategorii'
                        )
                    );
                    return $app->redirect(
                        $app['url_generator']->generate(
                            'categories'
                        ),
                        301
                    );
                }
            } else {
                $app['session']->getFlashBag()->add(
                    'message',
                    array(
                        'type' => 'danger',
                        'content' => 'Nie można usunąć niepustej kategorii'
                    )
                );
                return $app->redirect(
                    $app['url_generator']->generate(
                        'categories'
                    ),
                    301
                );
            }
        } else {
            $app['session']->getFlashBag()->add(
                'message',
                array(
                    'type' => 'danger',
                    'content' => 'Nie znaleziono kategorii'
                )
            );
            return $app->redirect(
                $app['url_generator']->generate(
                    'categories'
                ),
                301
            );
        }
    }
}
