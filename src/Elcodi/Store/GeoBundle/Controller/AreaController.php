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

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Elcodi\Store\CoreBundle\Controller\Traits\TemplateRenderTrait;

/**
 * Address controllers
 *
 * @Route(
 *      path = "/area",
 * )
 */
class AreaController extends Controller
{
    use TemplateRenderTrait;

    /**
     * Show the city selectors
     *
     * @param integer $areaId The area id
     *
     * @return Response
     *
     * @Route(
     *      path = "/selectors/{areaId}",
     *      name = "city_selectors",
     *      methods = {"GET"},
     *      defaults={
     *          "areaId"   = null
     *      }
     * )
     */
    public function showCitySelectorAction(
        $areaId
    ) {
        $citySelectorBuilder = $this->get('elcodi.store.geo.form.city_selector_builder');

        $citySelectorBuilder->buildSelects($areaId);

        return $this->renderTemplate(
            'Subpages:country-selector.html.twig',
            [
                'selects'   => $citySelectorBuilder->getSelects(),
                'max_depth' => $citySelectorBuilder->getMaxDepth()
            ]
        );
    }
}
