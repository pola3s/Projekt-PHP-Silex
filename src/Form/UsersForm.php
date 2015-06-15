<?php


namespace Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

class UsersForm extends AbstractType
{
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        return  $builder
			->add(
                'firstname', 'text', array(
                'constraints' => array(
                new Assert\NotBlank(), 
                new Assert\Length(
                    array('min' => 1)
                )
                )
                )
            )
            ->add(
                'lastname', 'text', array(
                'constraints' => array(
                new Assert\NotBlank(), 
                new Assert\Length(
                    array('min' => 1)
                )
                )
                )
            )
            ->add(
                'login', 'text', array(
                'constraints' => array(
                new Assert\NotBlank(), 
                new Assert\Length(
                    array('min' => 3)
                )
                )
                )
            )
            ->add(
                'password', 'password', array(
                'constraints' => array(
                new Assert\NotBlank(), 
                new Assert\Length(
                    array('min' => 5)
                )
                )
                )
            )
             ->add(
                 'confirm_password', 'password', array(
                 'constraints' => array(
                 new Assert\NotBlank(), 
                 new Assert\Length(
                     array('min' => 5)
                 )
                 )
                 )
             )
            
            ->getForm();

	}
	
	public function getName()
    {
        return 'usersForm';
    }
	
}