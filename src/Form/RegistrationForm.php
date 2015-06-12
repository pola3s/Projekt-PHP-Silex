<?php


namespace Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

class RegistrationForm extends AbstractType
{
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        return  $builder
			->add(
                'login', 'text', array(
                    'constraints' => array(
                        new Assert\NotBlank(),
                    
                new Assert\Length(
                    array('min' => 3)
                ),
                new Assert\Type(
                    array('type' => 'string')
                )
                    )
                )
            )
            ->add(
                'email', 'text', array(
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
                'firstname', 'text', array(
                    'label' => 'Imię',
                    'constraints' => array(
                        new Assert\NotBlank(),
                        new Assert\Length(
                            array('min' => 3)
                        ),
                        new Assert\Type(
                            array('type' => 'string')
                        )
                    )
                )    
            )    
            ->add(
                'lastname', 'text', array(
                    'label' => 'Nazwisko',
                    'constraints' => array(
                        new Assert\NotBlank(),
                        new Assert\Length(
                            array('min' => 3)
                        ),
                        new Assert\Type(
                            array('type' => 'string')
                        ),
                    )
                )
            )
            ->add(
                'password', 'password', array(
                    'label' => 'Hasło',
                    'constraints' => array(
                        new Assert\NotBlank()
                    )
                )
            )
            ->add(
                'confirm_password', 'password', array(
                    'label' => 'Potwierdź hasło',
                    'constraints' => array(
                        new Assert\NotBlank()
                    )
                )
            )
            ->add('save', 'submit', array('label' => 'Zarejestruj'))
        ->getForm();
	}
	
	public function getName()
    {
        return 'registrationForm';
    }
	
}