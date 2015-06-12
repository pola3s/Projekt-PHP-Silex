<?php

namespace Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;


class AboutForm extends AbstractType
{
   
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        return  $builder
			->add('email', 'text', array(
                    'label' => 'Email',
                    'constraints' => array(
                        new Assert\NotBlank(),
                        new Assert\Email(
                            array(
                                'message' => 'Email nie jest poprawny'
                            )
                        ),
                        new Assert\Type(
                            array('type' => 'string')
                        )
                    )
                    )
                )
            ->add(
                'phone', 'text', array(
                    'constraints' => array(
                        new Assert\NotBlank(), 
                            new Assert\Length(
                                array('min' => 5)
                            ),
                             new Assert\Regex(
                                 array(
                                 'pattern' => 
                                    "/^([0-9]{9})|(([0-9]{3}-){2}[0-9]{3})$/"
                                 )
                             )
                    )
                )
            )
            ->add(
                'description', 'text', array(
                'constraints' => array(
					new Assert\NotBlank(), 
					new Assert\Length(
						array('min' => 5)
						)
					)
                )
            )
            ->add(
                'website', 'text', array(
                    'constraints' => array(
                        new Assert\NotBlank(),
                        new Assert\Length(
                            array('min' => 5)
                        ),
                        new Assert\Url()
                        )
                )
            )
            ->add(
                'city', 'text', array(
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
        return 'aboutForm';
    }
	
}
	
	