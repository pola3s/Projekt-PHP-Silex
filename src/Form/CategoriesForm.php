<?php


namespace Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

class CategoriesForm extends AbstractType
{
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        return  $builder
			->add(
				'name', 'text', array(
                'constraints' => array(
					new Assert\NotBlank(), 
                    new Assert\Length(
						array('min' => 2)
						)
                    )
                )
            )
                            
            ->add('save', 'submit')
            ->getForm();
			
	}
	
	public function getName()
    {
        return 'categoriesForm';
    }
}