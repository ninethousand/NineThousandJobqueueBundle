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
            ->add('retry',     'choice', array(
                    'choices'    => array('1' => 'Yes', '0' => 'No'),
                    'expanded'   => true,
                    'required'   => true,
                ))
            ->add('cooldown',  'integer', array(
                    'label'      => 'Retry Cooldown (in seconds)',
                    'required'   => false,
                ))
            ->add('maxRetries', 'integer', array(
                    'label'      => 'Maximum Retries',
                    'required'   => false,
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
            'intention'       => 'jobqueue_job_nekot',
        ));
    }

    public function getName()
    {
        return 'jobqueue_job';
    }
}
