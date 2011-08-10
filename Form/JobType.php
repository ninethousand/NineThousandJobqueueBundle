<?php

namespace NineThousand\Bundle\NineThousandJobqueueBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use NineThousand\Bundle\NineThousandJobqueueBundle\Form\ParamType;
use NineThousand\Bundle\NineThousandJobqueueBundle\Form\ArgType;
use NineThousand\Bundle\NineThousandJobqueueBundle\Form\TagType;

class JobType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('retry')
            ->add('cooldown')
            ->add('maxRetries')
            ->add('executable')
            ->add('type')
            ->add('params', 'collection', array('type' => new ParamType()))
            ->add('args', 'collection', array('type' => new ArgType()))
            ->add('tags', 'collection', array('type' => new TagType()))
        ;
    }
    
    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'NineThousand\Bundle\NineThousandJobqueueBundle\Entity\Job',
        );
    }

    public function getName()
    {
        return 'jobqueue_job';
    }
}
