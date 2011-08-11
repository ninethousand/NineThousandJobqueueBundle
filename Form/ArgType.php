<?php

namespace NineThousand\Bundle\NineThousandJobqueueBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class ArgType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('value', 'text', array(
                    'label'     => 'Arg',
                    'required'  => false))
        ;
    }
    
    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'NineThousand\Bundle\NineThousandJobqueueBundle\Entity\Arg',
        );
    }

    public function getName()
    {
        return 'jobqueue_arg';
    }
}
