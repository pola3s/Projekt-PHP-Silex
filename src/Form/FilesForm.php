<?php


namespace Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

use Model\CategoriesModel; 

class FilesForm extends AbstractType
{
	protected $_app;
	
	public function __construct(Application $app)
    {
        $this->_app = $app;
       
    }
	
	
	
	
	protected function getCategories($app)
	{
		$CategoriesModel = new CategoriesModel($app);
		$categories = $CategoriesModel->getCategoriesDict();
		return $categories;
    }
	
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
	
		return  $builder
			->add(
            'title', 'text', array(
            'constraints' => array(
            new Assert\NotBlank(), 
            new Assert\Length(
                array('min' => 3)
            )
            )
            )
        )
        ->add(
            'category', 'choice', array(
             'choices' => $categories,
            )
        )
        ->add(
            'description', 'textarea', array(
            'constraints' => array(
            new Assert\NotBlank(), 
            new Assert\Length(
                array('min' => 5)
            )
            )
            )
        )
        
        ->add(
            'file', 'file', array(
                    'label' => 'Choose file',
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
        ->add('save', 'submit', array('label' => 'Upload file'))
        ->getForm();
		
	}
	
		public function getName()
    {
        return 'filesForm';
    }
	
}
            