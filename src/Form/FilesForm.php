<?php
/**
 * Files form
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

use Silex\Application;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Model\CategoriesModel;

/**
 * Class FilesForm
 *
 * @category Form
 * @package Form
 * @extends AbstractType
 * @use Silex\Application;
 * @use Symfony\Component\Form\AbstractType
 * @use Symfony\Component\Form\FormBuilderInterface
 * @use Symfony\Component\OptionsResolver\OptionsResolverInterface
 * @use Symfony\Component\Validator\Constraints as Assert
 * #use Model\CategoriesModel;
 */
    
    
    
class FilesForm extends AbstractType
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
                'title',
                'text',
                array(
                'label' => 'Tytuł',
                'constraints' => array(
                    new Assert\NotBlank(
                        array(
                            'message' => 'Uzupełnij to pole',
                        )
                    ),
                    new Assert\Length(
                        array(
                            'min' => 3,
                            'minMessage' => 'Poprawny tytuł powinien zawierać minimum 3 znaki',
                        )
                    )
                )
                )
            )
        ->add(
            'category',
            'choice',
            array(
                'label' => 'Kategoria',
                'choices' => $options['data']['categories'],
            )
        )
        ->add(
            'description',
            'textarea',
            array(
                'label' => 'Opis',
				'attr' => array(
					'cols' => '120', 
					'rows' => '5'
				),
                'constraints' => array(
                new Assert\NotBlank(
                    array(
                        'message' => 'Uzupełnij to pole',
                    )
                ),
                new Assert\Length(
                    array(
                        'min' => 5,
                        'minMessage' => 'Poprawny opis powinien zawierać minimum 5 znaków',
                    )
                )
                )
            )
        )
        
        ->add(
            'file',
            'file',
            array(
                    'label' => 'Wybierz plik',
                    'constraints' => array(
                        new Assert\File(
                            array(
                                'maxSize' => '1024k',
                                'mimeTypes' => array(
                                    'image/jpeg',
                                    'image/png',
                                    'image/gif',
                                ),
                            )
                        )
                    )
                )
        )
        ->add('Zapisz', 'submit', array('label' => 'Dodaj'))
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
        return 'filesForm';
    }
}
