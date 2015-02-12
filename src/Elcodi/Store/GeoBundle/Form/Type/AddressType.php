<?php

/*
 * This file is part of the Elcodi package.
 *
 * Copyright (c) 2014 Elcodi.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Feel free to edit as you please, and have fun.
 *
 * @author Marc Morera <yuhu@mmoreram.com>
 * @author Aldo Chiecchia <zimage@tiscali.it>
 * @author Elcodi Team <tech@elcodi.com>
 */

namespace Elcodi\Store\GeoBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Callback as AssertCallback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

use Elcodi\Component\Geo\Entity\LiteAddress;
use Elcodi\Component\Geo\Factory\LiteAddressFactory;
use Elcodi\Store\GeoBundle\Services\GeoApiConsumer;

/**
 * Class AddressType
 */
class AddressType extends AbstractType
{
    /**
     * @var string
     *
     * The city type
     */
    const CITY_TYPE = 'city';

    /**
     * @var string
     *
     * Lite address factory
     */
    protected $liteAddressFactory;

    /**
     * @var GeoApiConsumer
     *
     * A geo api consumer
     */
    private $geoApiConsumer;

    /**
     * Constructor
     *
     * @param LiteAddressFactory $liteAddressFactory Lite address factory
     * @param GeoApiConsumer     $geoApiConsumer     A geo api consumer
     */
    public function __construct(
        LiteAddressFactory $liteAddressFactory,
        GeoApiConsumer $geoApiConsumer
    ) {
        $this->liteAddressFactory = $liteAddressFactory;
        $this->geoApiConsumer = $geoApiConsumer;
    }

    /**
     * Default form options
     *
     * @param OptionsResolverInterface $resolver
     *
     * @return array With the options
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => $this->liteAddressFactory->getEntityNamespace(),
            'empty_data' => $this->liteAddressFactory->create(),
            'constraints' => [
                new AssertCallback([ [ $this, 'validateCityCode' ] ]),
            ],
        ]);
    }

    /**
     * Buildform function
     *
     * @param FormBuilderInterface $builder the formBuilder
     * @param array                $options the options for this form
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', [
                'required' => true,
                'label'    => 'Address name',
            ])
            ->add('cityId', 'hidden', [
                'required' => false,
            ])
            ->add('postalCode', 'text', [
                'required' => true,
                'label'    => 'Postal code',
            ])
            ->add('recipientName', 'text', [
                'required' => true,
            ])
            ->add('recipientSurname', 'text', [
                'required' => true,
                'label'    => 'Recipient Surname',
            ])
            ->add('address', 'text', [
                'required' => true,
                'label'    => 'Address',
            ])
            ->add('addressMore', 'text', [
                'required' => false,
                'label'    => 'Address more',
            ])
            ->add('phone', 'text', [
                'required' => true,
                'label'    => 'Phone',
            ])
            ->add('mobile', 'text', [
                'required' => false,
                'label'    => 'Mobile',
            ])
            ->add('comments', 'textarea', [
                'required' => false,
                'label'    => 'Comments',
            ])
            ->add('apply', 'submit', [
                'label'    => 'Save address'
            ]);
    }

    /**
     * Checks that the received city is a valid one
     *
     * @param LiteAddress               $object  The address being validated
     * @param ExecutionContextInterface $context The execution context
     */
    public function validateCityCode(LiteAddress $object, ExecutionContextInterface $context)
    {
        $area_info = $this->geoApiConsumer->getAreaInfo($object->getCityId());

        if(
            empty($area_info) ||
            self::CITY_TYPE !== $area_info['type']
        ) {
            $context
                ->buildViolation('Select a city')
                ->addViolation();
        }
    }

    /**
     * Return unique name for this form
     *
     * @return string
     */
    public function getName()
    {
        return 'store_geo_form_type_address';
    }
}
