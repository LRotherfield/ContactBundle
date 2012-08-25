<?php

namespace Rothers\ContactBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class ContactType extends AbstractType {

  public function buildForm(FormBuilder $builder, array $options) {
    $builder->add('title', 'entity', array(
        'class' => 'RothersContactBundle:Title'
    ));
    $builder->add('name', 'text');  
    $builder->add('email', 'email');
    $builder->add('telephone', 'text');
    $builder->add('company', 'text');
    $builder->add('message', 'textarea');
  }

  public function getName() {
    return 'contact';
  }

  public function getDefaultOptions(array $options) {
    return array('data_class' => 'Rothers\ContactBundle\Entity\Contact');
  }

}
