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

namespace Elcodi\Store\GeoBundle\Form;

use Symfony\Component\HttpKernel\Exception\HttpException;

use Elcodi\Component\Geo\Services\Interfaces\LocationProviderInterface;
use Elcodi\Component\Geo\ValueObject\LocationData;

/**
 * Class LocationSelectorBuilder
 */
class LocationSelectorBuilder
{
    /**
     * @var string
     *
     * The max location type that we want to show.
     */
    protected $maxLocationType;

    /**
     * @var array
     *
     * The selects structure
     */
    protected $selects;

    /**
     * @var LocationProviderInterface
     *
     * A location provider
     */
    private $locationProvider;

    /**
     * Builds a new class
     *
     * @param LocationProviderInterface $locationProvider
     */
    public function __construct(
        LocationProviderInterface $locationProvider
    ) {
        $this->locationProvider = $locationProvider;
    }

    /**
     * Builds the selects structure
     *
     * @param string $locationId      The location id selected
     * @param string $maxLocationType The max type that we want to show.
     *
     * @return array
     */
    public function getSelects($locationId, $maxLocationType)
    {
        $this->selects         = [];
        $this->maxLocationType = $maxLocationType;

        if ($locationId) {
            $hierarchy    = $this
                ->locationProvider
                ->getHierarchy($locationId);
            $rootLocation = array_shift($hierarchy);

            $this->buildRootSelector($rootLocation->getId());
            $this->buildChildrenSelects($rootLocation, $hierarchy);
        } else {
            $this->buildRootSelector();
        }

        return $this->selects;
    }

    /**
     * Builds the root selector with all the first level options
     *
     * @param null|LocationData $selectedOption The selected option
     */
    protected function buildRootSelector($selectedOption = null)
    {
        $rootLocations   = $this
            ->locationProvider
            ->getRootLocations();

        if (empty($rootLocations)) {
            throw new HttpException(
                500,
                'No locations loaded, please populate your locations'
            );
        }

        $locationExample = reset($rootLocations);

        $this->selects[] = $this->formatSelector(
            $locationExample->getType(),
            $this->generateOptions($rootLocations),
            $selectedOption
        );
    }

    /**
     * Builds the children (Not root) selectors given a hierarchy, we also use
     * the root location to know which one is selected.
     *
     * @param LocationData $selectedRootLocation
     * @param              $hierarchy
     */
    protected function buildChildrenSelects(
        LocationData $selectedRootLocation,
        array $hierarchy
    ) {
        $childrenLocations = $this
            ->locationProvider
            ->getChildren($selectedRootLocation->getId());

        do {
            $selectedRootLocation = array_shift($hierarchy);

            $locationExample = reset($childrenLocations);
            $options         = $this->generateOptions($childrenLocations);

            if ($selectedRootLocation) {
                $selected          = $selectedRootLocation->getId();
                $childrenLocations =
                    $this->maxLocationType !== $locationExample->getType()
                        ? $childrenLocations = $this
                        ->locationProvider
                        ->getChildren($selectedRootLocation->getId())
                        : null;
            } else {
                $selected          = null;
                $childrenLocations = null;
            }

            $this->selects[] = $this->formatSelector(
                $locationExample->getType(),
                $options,
                $selected
            );
        } while ($childrenLocations);
    }

    /**
     * Generates all the options for a select given the raw options from the
     * areas info from the API.
     *
     * @param LocationData[] $rawOptions The raw options
     *
     * @return array
     */
    protected function generateOptions(array $rawOptions)
    {
        $options = [];
        foreach ($rawOptions as $rawOption) {
            $options[$rawOption->getId()] = $rawOption->getName();
        }

        return $options;
    }

    /**
     * Returns a well formatted selector
     *
     * @param string      $type     The selector type
     * @param array       $options  The selector options
     * @param string|null $selected The selected option
     *
     * @return array
     */
    protected function formatSelector(
        $type,
        array $options,
        $selected = null
    ) {
        return [
            'type'     => $type,
            'options'  => $options,
            'selected' => $selected,
        ];
    }
}
