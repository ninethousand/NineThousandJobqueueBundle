<?php

namespace NineThousand\Bundle\NineThousandJobqueueBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class ScheduledJobType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('retry')
            ->add('cooldown')
            ->add('maxRetries')
            ->add('executable')
            ->add('type')
            ->add('schedule')
            ->add('params')
            ->add('args')
            ->add('tags')
        ;
    }

    public function getName()
    {
        return 'jobqueue_scheduled_job';
    }
}
