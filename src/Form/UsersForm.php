<?php
/**
 * Users form
 *
 * PHP version 5
 *
 * @category Form
 * @package  Form
 * @author   Paulina Serwińska <paulina.serwinska@gmail.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     wierzba.wzks.uj.edu.pl/~12_serwinska
 */

namespace Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class UsersForm
 *
 * @category Form
 * @package Form
 * @extends AbstractType
 * @use Symfony\Component\Form\AbstractType
 * @use Symfony\Component\Form\FormBuilderInterface
 * @use Symfony\Component\OptionsResolver\OptionsResolverInterface
 * @use Symfony\Component\Validator\Constraints as Assert
 */
 
class UsersForm extends AbstractType
{
   /**
    * Form builder
    *
    * @access public
    * @param FormBuilderInterface $builder
    * @param array $options
    *
    * @return FormBuilderInterface
    */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        return  $builder
            ->add(
                'firstname',
                'text',
                array(
                    'label' => 'Imię',
                    'constraints' => array(
                        new Assert\NotBlank(
                            array(
                                'message' => 'Uzupełnij to pole',
                            )
                        ),
                        new Assert\Length(
                            array(
                                'min' => 2,
                                'minMessage' => 'Poprawne imię powinno składać się z minimum 2 znaków',
                            )
                        ),
                        new Assert\Type(
                            array(
                                'type' => 'string',
                                'message' => 'Niepoprawnie wypełnione pole',
                            )
                        )
                    )
                )
            )
             ->add(
                 'lastname',
                 'text',
                 array(
                    'label' => 'Nazwisko',
                    'constraints' => array(
                        new Assert\NotBlank(
                            array(
                                'message' => 'Uzupełnij to pole',
                            )
                        ),
                        new Assert\Length(
                            array(
                                'min' => 2,
                                'minMessage' => 'Poprawne nazwisko powinno składać się z minimum 2 znaków',
                            )
                        ),
                        new Assert\Type(
                            array(
                                'type' => 'string',
                                'message' => 'Niepoprawnie wypełnione pole',
                            )
                        )
                    )
                 )
             )
           ->add(
               'login',
               'text',
               array(
                    'label' => 'Login',
                    'constraints' => array(
                        new Assert\NotBlank(
                            array(
                                'message' => 'Uzupełnij to pole',
                            )
                        ),
                    
                        new Assert\Length(
                            array(
                                'min' => 3,
                                'minMessage' => 'Poprawny login powinien składać się z minimum 3 znaków',
                            )
                        ),
                        new Assert\Type(
                            array(
                                'type' => 'string',
                                'message' => 'Uzupełnij to pole'
                            )
                        )
                    )
                )
           )
              ->add(
                  'password',
                  'password',
                  array(
                    'label' => 'Hasło',
                    'constraints' => array(
                        new Assert\NotBlank(
                            array(
                                'message' => 'Uzupełnij to pole',
                            )
                        )
                    )
                  )
              )
            ->add(
                'confirm_password',
                'password',
                array(
                    'label' => 'Potwierdź hasło',
                    'constraints' => array(
                        new Assert\NotBlank(
                            array(
                                'message' => 'Uzupełnij to pole',
                            )
                        )
                    )
                )
            )
            
            ->getForm();

    }
    
   /**
    * Gets form name.
    *
    * @access public
    *
    * @return string
    */
    public function getName()
    {
        return 'usersForm';
    }
}
