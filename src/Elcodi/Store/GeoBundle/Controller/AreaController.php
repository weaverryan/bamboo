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
     * Loads the area select subpage
     *
     * @param string $parentId   The parent identifier
     * @param string $selectedId The options selected
     *
     * @return Response The area selector subpage
     *
     * @Route(
     *      path = "country",
     *      name = "country_select_not_selected",
     *      methods = {"GET","POST"},
     *      defaults={
     *          "parentId"   = null,
     *          "selectedId" = null
     *      }
     * )
     * @Route(
     *      path = "country/{selectedId}",
     *      name = "country_select",
     *      methods = {"GET","POST"},
     *      defaults={
     *          "parentId"   = null,
     *          "selectedId" = null
     *      }
     * )
     *
     * @Route(
     *      path = "generic/{parentId}/{selectedId}",
     *      name = "area_select",
     *      methods = {"GET","POST"},
     *      defaults={
     *          "parentId"   = null,
     *          "selectedId" = null
     *      }
     * )
     */
    public function showSelectAction(
        $parentId,
        $selectedId
    ) {
        $rawOptions = $this->get('elcodi.store.geo.services.geo_api_consumer')
            ->getChildrenAreas($parentId);

        if(empty($rawOptions)) {
            throw new NotFoundHttpException('No area found');
        }

        $options = [];
        foreach ($rawOptions as $rawOption) {
            $options[$rawOption['id']] = $rawOption['name'];
        }

        return $this->renderTemplate(
            'Subpages:area-select.html.twig',
            [
                'selected' => $selectedId,
                'options'  => $options
            ]
        );
    }
}
