<?php
/**
 * About form
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
 * Class AboutForm
 *
 * @category Form
 * @package Form
 * @extends AbstractType
 * @use Symfony\Component\Form\AbstractType
 * @use Symfony\Component\Form\FormBuilderInterface
 * @use Symfony\Component\OptionsResolver\OptionsResolverInterface
 * @use Symfony\Component\Validator\Constraints as Assert
 */

class AboutForm extends AbstractType
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
            ->add('email', 'text', array(
                    'label' => 'Email',
                    'constraints' => array(
                        new Assert\NotBlank(
                            array(
                                'message' => 'Uzupełnij to pole'
                            )
                        ),
                        new Assert\Email(
                            array(
                                'message' => 'Email nie jest poprawny'
                            )
                        ),
                        new Assert\Type(
                            array(
                                'type' => 'string',
                                'message' => 'Email nie jest poprawny',
                            )
                        )
                    )
                    ))
            ->add(
                'phone',
                'text',
                array(
                    'label' => 'Telefon',
                    'constraints' => array(
                        new Assert\NotBlank(
                            array(
                                'message' => 'Uzupełnij to pole'
                            )
                        ),
                        new Assert\Regex(
                            array(
                                 'pattern' => "/^([0-9]{9})|(([0-9]{3}-){2}[0-9]{3})$/",
                                 'message' => 'Niepoprawny numer telefonu'
                                 )
                        )
                    )
                )
            )
            ->add(
                'description',
                'text',
                array(
                'label' => 'Opis',
                'attr' => array(
                    'cols' => '120',
                    'rows' => '5'
                ),
                'constraints' => array(
                     new Assert\NotBlank(
                         array(
                                'message' => 'Uzupełnij to pole'
                            )
                     ),
                     new Assert\Length(
                         array('min' => 5,
                              'minMessage' => 'Opis powinien zawierać minimum 5 znaków',
                            )
                     )
                    )
                )
            )
            ->add(
                'website',
                'text',
                array(
                    'label' => 'Strona www',
                    'data' => 'http://',
                    'constraints' => array(
                         new Assert\NotBlank(
                             array(
                                'message' => 'Uzupełnij to pole'
                             )
                         ),
                         new Assert\Url(
                             array(
                                'message' => 'Poprawny format http://example.com!',
                             )
                         )
                    )
                )
            )
            ->add(
                'city',
                'text',
                array(
                    'label' => 'Miasto',
                    'constraints' => array(
                        new Assert\NotBlank(
                            array(
                                        'message' => 'Uzupełnij to pole',
                                    )
                        ),
                        new Assert\Length(
                            array('min' => 2,
                                 'minMessage' => 'Pole powinno zawierać minimum 2 znaki',
                            )
                        ),
                    )
                )
            )
            
            ->add('Zapisz', 'submit')
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
        return 'aboutForm';
    }
}
