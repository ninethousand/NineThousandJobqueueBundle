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

    public function getName()
    {
        return 'ninethousand_bundle_ninethousandjobqueuebundle_paramtype';
    }
}
