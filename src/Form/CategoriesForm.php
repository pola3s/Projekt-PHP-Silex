<?php
/**
 * Categories form
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
 * Class CategoriesForm
 *
 * @category Form
 * @package Form
 * @extends AbstractType
 * @use Symfony\Component\Form\AbstractType
 * @use Symfony\Component\Form\FormBuilderInterface
 * @use Symfony\Component\OptionsResolver\OptionsResolverInterface
 * @use Symfony\Component\Validator\Constraints as Assert
 */
 
class CategoriesForm extends AbstractType
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
                'name',
                'text',
                array(
                    'label' => 'Nazwa',
                    'constraints' => array(
                        new Assert\NotBlank(
                            array(
                                'message' => 'Uzupełnij to pole',
                            )
                        ),
                        new Assert\Length(
                            array(
                                'min' => 2,
                                'minMessage' => 'Poprawna nazwa kategorii powinna zawierać minimum 2 znaki',
                            )
                        )
                        
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
        return 'categoriesForm';
    }
}
