<?php

namespace NineThousand\Bundle\NineThousandJobqueueBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class ParamType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('job')
            ->add('key')
            ->add('value')
            ->add('active')
        ;
    }
    
    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'NineThousand\Bundle\NineThousandJobqueueBundle\Entity\Param',
        );
    }

    public function getName()
    {
        return 'jobqueue_param';
    }
}
