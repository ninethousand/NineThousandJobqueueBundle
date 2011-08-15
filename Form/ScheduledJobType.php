<?php

namespace NineThousand\Bundle\NineThousandJobqueueBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use NineThousand\Bundle\NineThousandJobqueueBundle\Form\ParamType;
use NineThousand\Bundle\NineThousandJobqueueBundle\Form\ArgType;
use NineThousand\Bundle\NineThousandJobqueueBundle\Form\TagType;

class ScheduledJobType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {

        $builder
            ->add('name',      'text', array(
                    'label'      => 'Task Name',
                    'required'   => true,
                ))
            ->add('schedule',   'text', array(
                    'label'      => 'Schedule (Cron notation)',
                    'required'   => true,
                ))
            ->add('executable', 'text', array(
                    'label'      => 'Executable (Full Path)',
                    'required'   => true,
                ))
            ->add('_token', 'csrf')
            ->add('params', 'collection', array(
                    'type'          => new ParamType(),
                    'allow_add'     => true,
                    'allow_delete'  => true,
                ))
            ->add('args', 'collection', array(
                    'type'          => new ArgType(),
                    'allow_add'     => true,
                    'allow_delete'  => true,
                ))
            ->add('tags', 'collection', array(
                    'type'          => new TagType(),
                    'allow_add'     => true,
                    'allow_delete'  => true,
                ))
            ;
        
        if (isset($options['jobcontrol']['type_mapping']) && !empty($options['jobcontrol']['type_mapping'])) {
            $list = array();
            foreach ($options['jobcontrol']['type_mapping'] as $key => $val) {
                $list[$key] = $key;
            }
            $builder->add('type', 'choice', array(
                'choices'   => $list,
                'required'  => true,
            ));
        }
    }
    
    public function getDefaultOptions(array $options)
    {
        return array_merge($options, array(
            'data_class' => 'NineThousand\Bundle\NineThousandJobqueueBundle\Entity\Job',
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'intention'       => 'jobqueue_scheduledjob_nekot',
        ));
    }

    public function getName()
    {
        return 'jobqueue_scheduledjob';
    }
}
