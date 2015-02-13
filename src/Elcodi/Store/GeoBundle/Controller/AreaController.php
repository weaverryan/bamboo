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
        $selects        = [];
        $maxDepth       = false;
        $geoApiConsumer = $this->get('elcodi.store.geo.services.geo_api_consumer');

        $childrenAreas = $geoApiConsumer->getChildrenAreas();
        $childSelector = [];
        foreach ($childrenAreas as $childrenArea) {
            $childSelector['label']                        = $childrenArea['type'];
            $childSelector['options'][$childrenArea['id']] = $childrenArea['name'];
        }

        if (!is_null($areaId)) {
            $areaInfo = $geoApiConsumer->getAreaLocation($areaId);
            foreach ($areaInfo as $areaPartialInfo) {
                $childSelector['selected'] = $areaPartialInfo['id'];
                $selects[]                 = $childSelector;

                $childrenAreas = $geoApiConsumer->getChildrenAreas($areaPartialInfo['id']);
                if (!empty($childrenAreas)) {
                    $childSelector = [];
                    foreach ($childrenAreas as $childrenArea) {
                        $childSelector['label'] = $childrenArea['type'];
                        $childSelector['options'][$childrenArea['id']]
                                                = $childrenArea['name'];
                    }

                } else {
                    $maxDepth = true;
                }
                $childSelector['selected'] = $areaPartialInfo['id'];
            }

            if (!empty($childrenAreas)) {
                $selects[] = $childSelector;
            }
        } else{
            $selects[] = $childSelector;
        }

        return $this->renderTemplate(
            'Subpages:country-selector.html.twig',
            [
                'selects'   => $selects,
                'max_depth' => $maxDepth
            ]
        );
    }
}
