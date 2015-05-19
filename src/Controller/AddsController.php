<?php

namespace Controller; //przestrzeń nazw kontrolerów

use Silex\Application; // użyte biblioteki
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Model\AddsModel;


class AddsController implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $addsController = $app['controllers_factory'];//definiowanie ścieżek rutowania
        $addsController->get('/{page}', array($this, 'index'))->value('page', 1)->bind('/adds/');
        $addsController->match('/add/', array($this, 'add'))->bind('/adds/add');
        $addsController->match('/edit/{id}', array($this, 'edit'))->bind('/adds/edit');
        $addsController->match('/delete/{id}', array($this, 'delete'))->bind('/adds/delete');
        $addsController->get('/view/{id}', array($this, 'view'))->bind('/adds/view');
        return $addsController;//zmodyfikowana tablica rutingu
    }

   public function index(Application $app, Request $request) //zdefiniowanie akcji, metody publiczne, przekazuje obiekt Application
    {
        $pageLimit = 5;
        $page = (int) $request->get('page', 5);
        $addsModel = new AddsModel($app);
        $pagesCount = $addsModel->countAddsPages($pageLimit);
        if (($page < 5) || ($page > $pagesCount)) {
            $page = 5;
        }
        $adds = $addsModel->getAddsPage($page, $pageLimit, $pagesCount);
        var_dump($adds);
        $paginator = array('page' => $page, 'pagesCount' => $pagesCount);
        return $app['twig']->render('adds/indexx.twig', array('adds' => $adds, 'paginator' => $paginator));
    }
    public function add(Application $app, Request $request)
    {

        // default values:  zdefiniowane domyslne dane
        $data = array(
            'category' => '',
            'name' => '',
            'date' => '',
            'photo (upload from file)' => '',
            'description' => '',
            'your e-mail' => '',
        );
        // zdefiniowanie i stworzenie formularza

        $form = $app['form.factory']->createBuilder('form', $data)
            ->add('category', 'text', array(
                'constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 1)))
            ))
            ->add('type', 'text', array(
                'constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 1)))
            ))
            ->add('name', 'text', array(
                'constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 5)))
            ))
            ->add('date', 'text', array(
                'constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 5)))
            ))
            ->add('photo', 'text', array(
                'constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 5)))
            ))
            ->add('description', 'text', array(
                'constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 5)))
            ))
            ->add('your_email', 'text', array(
                'constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 5)))
            ))
            ->add('save', 'submit')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $addsModel = new AddsModel($app);
            $addsModel->addAdd($data);
            return $app->redirect($app['url_generator']->generate('/adds/'), 301);
        }//redirect przekirowuje na listę wszystkich elementów

        return $app['twig']->render('adds/add.twig', array('form' => $form->createView()));
    }

     public function edit(Application $app, Request $request)
     {
         $addsModel = new AddsModel($app);
         $id = (int) $request->get('id', 0);
         $add = $addsModel->getAdd($id);

          //pobieram  z formularza


        $data = array(
            'id'=> $add['id'],
            'category' => $add['add_cat'],
            'type' => $add['add_type'],
            'name' => $add['add_name'],
            'date' => $add['add_date'],
            'photo' => $add['add_photo'],
            'description' => $add['add_desc'],
            'your_email' => $add['user_email']
        );
         if (count($add)) {

             $form = $app['form.factory']->createBuilder('form', $data)
                 ->add('category', 'text', array(
                     'constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 1)))
                 ))
                 ->add('type', 'text', array(
                     'constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 1)))
                 ))
                 ->add('name', 'text', array(
                     'constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 5)))
                 ))
                 ->add('date', 'text', array(
                     'constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 5)))
                 ))
                 ->add('photo', 'text', array(
                     'constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 5)))
                 ))
                 ->add('description', 'text', array(
                     'constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 5)))
                 ))
                 ->add('your_email', 'text', array(
                     'constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 5)))
                 ))
                 ->add('save', 'submit')
                 ->getForm();

             $form->handleRequest($request);

             if ($form->isValid()) {
                 $addsModel = new AddsModel($app);
                 $addsModel->saveAdd($form->getData());
                 return $app->redirect($app['url_generator']->generate('/adds/'), 301);
             }

             return $app['twig']->render('adds/edit.twig', array('form' => $form->createView(), 'add' => $add));

         } else {
             return $app->redirect($app['url_generator']->generate('/adds/add'), 301);
         }

     }

    public function delete(Application $app, Request $request)
    {
        $id = (int) $request -> get('id', 0);
        $AddsModel = new AddsModel($app);
        $AddsModel -> deleteAdd($id);
        return $app->redirect($app['url_generator']->generate('/adds/'), 301);
    }

    public function view(Application $app, Request $request)
    {
        $id = (int) $request -> get('id', 0);
        $AddsModel = new AddsModel($app);
        $add = $AddsModel-> getAdd($id);

        return $app['twig']->render('adds/view.twig', array( 'add' => $add));
    }

}
