<?php

/**
 *
 * @author Pierre Feyssaguet <pfeyssaguet@gmail.com>
 * @since 18/12/15 10:00
 */

namespace DspSofts\QueryBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QueryType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'DspSofts\QueryBundle\Entity\Query',
        ));
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, array(
                'label' => 'dspsofts.query.label.name',
            ))
            ->add('query', TextareaType::class, array(
                'label' => 'dspsofts.query.label.query',
            ))
            ->add('save', SubmitType::class, array(
                'label' => 'dspsofts.query.action.save',
            ))
        ;
    }
}
