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

namespace Elcodi\Store\GeoBundle\Controller;

use Mmoreram\ControllerExtraBundle\Annotation\Entity as EntityAnnotation;
use Mmoreram\ControllerExtraBundle\Annotation\Form as FormAnnotation;
use Mmoreram\ControllerExtraBundle\Annotation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Elcodi\Component\Geo\Entity\Address;
use Elcodi\Component\Geo\Entity\City;
use Elcodi\Component\Geo\Entity\Country;
use Elcodi\Component\Geo\Entity\Interfaces\AddressInterface;
use Elcodi\Component\Geo\Entity\PostalCode;
use Elcodi\Component\Geo\Entity\Province;
use Elcodi\Component\Geo\Entity\State;
use Elcodi\Store\CoreBundle\Controller\Traits\TemplateRenderTrait;

/**
 * Address controllers
 *
 * @Route(
 *      path = "/user/address",
 * )
 */
class AddressController extends Controller
{
    use TemplateRenderTrait;

    /**
     * List addresses page
     *
     * @return Response Response
     *
     * @Route(
     *      path = "/",
     *      name = "store_address_list",
     *      methods = {"GET"}
     * )
     */
    public function listAction()
    {
        $addresses = $this
            ->get('elcodi.customer_wrapper')
            ->loadCustomer()
            ->getAddresses();

        $citiesInfo          = [];
        $geoApiAddressFinder = $this->get('elcodi.store.geo.services.geo_api_consumer');
        foreach ($addresses as $address) {
            /**
             * @var Address $address
             */
            $citiesInfo[$address->getCity()]
                = $geoApiAddressFinder->getCityLocation($address->getCity());
        }

        return $this->renderTemplate(
            'Pages:address-list.html.twig',
            [
                'addresses'   => $addresses,
                'cities_info' => $citiesInfo,
            ]
        );
    }

    /**
     * Edit address
     *
     * @param AddressInterface $address  $address The address that we are editing
     * @param FormView         $formView THe form view
     * @param boolean          $isValid  If the form is valid
     *
     * @return Response Response
     *
     * @Route(
     *      path = "/edit/{id}",
     *      name = "store_address_edit",
     *      methods = {"GET","POST"}
     * )
     * @Route(
     *      path = "/new",
     *      name = "store_address_new",
     *      methods = {"GET","POST"}
     * )
     *
     * @EntityAnnotation(
     *      class = {
     *          "factory" = "elcodi.factory.address",
     *          "method" = "create",
     *          "static" = false
     *      },
     *      mapping = {
     *          "id" = "~id~"
     *      },
     *      mappingFallback = true,
     *      name = "address",
     *      persist = false
     * )
     * @FormAnnotation(
     *      class         = "store_geo_form_type_address",
     *      name          = "formView",
     *      entity        = "address",
     *      handleRequest = true,
     *      validate      = "isValid"
     * )
     */
    public function editAction(
        AddressInterface $address,
        FormView $formView,
        $isValid
    ) {
        $entityManager = $this->get('elcodi.object_manager.address');

        if ($isValid) {
            $entityManager->flush($address);

            return $this->redirect(
                $this->generateUrl('store_address_list')
            );
        } else {
            $entityManager->clear($address);
        }

        $cityInfo = $this->get('elcodi.store.geo.services.geo_api_consumer')
            ->getCityLocation($address->getCity());

        return $this->renderTemplate(
            'Pages:address-edit.html.twig',
            [
                'address'   => $address,
                'form'      => $formView,
                'city_info' => $cityInfo,
            ]
        );
    }

    /**
     * new address
     *
     * @param AddressInterface $address  $address The address that we are editing
     * @param FormView         $formView THe form view
     * @param boolean          $isValid  If the form is valid
     *
     * @return Response Response
     *
     * @Route(
     *      path = "/new",
     *      name = "store_address_new",
     *      methods = {"GET","POST"}
     * )
     *
     * @EntityAnnotation(
     *      class = {
     *          "factory" = "elcodi.factory.address",
     *          "method" = "create",
     *          "static" = false
     *      },
     *      name = "address",
     *      persist = false
     * )
     * @FormAnnotation(
     *      class         = "store_geo_form_type_address",
     *      name          = "formView",
     *      entity        = "address",
     *      handleRequest = true,
     *      validate      = "isValid"
     * )
     */
    public function newAction(
        AddressInterface $address,
        FormView $formView,
        $isValid
    ) {
        if ($isValid) {
            $addressManager = $this->get('elcodi.object_manager.address');
            $addressManager->persist($address);
            $addressManager->flush();

            $this
                ->get('elcodi.customer_wrapper')
                ->loadCustomer()
                ->addAddress($address);
            $this->get('elcodi.object_manager.customer')
                ->flush();

            return $this->redirect(
                $this->generateUrl('store_address_list')
            );
        }

        return $this->renderTemplate(
            'Pages:address-edit.html.twig',
            [
                'address' => $address,
                'form'    => $formView,
            ]
        );
    }
}
