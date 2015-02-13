<?php

namespace Elcodi\Store\GeoBundle\Form;

use Elcodi\Store\GeoBundle\Services\GeoApiConsumer;

/**
 * Class CitySelectorBuilder
 */
class CitySelectorBuilder
{
    /**
     * @var array
     *
     * The selects structure
     */
    protected $selects;

    /**
     * @var boolean
     *
     * If we've build the max depth select
     */
    protected $maxDepth;

    /**
     * @var GeoApiConsumer
     *
     * Geo Api consumer
     */
    private $geoApiConsumer;

    /**
     * Builds a new class
     *
     * @param GeoApiConsumer $geoApiConsumer
     */
    public function __construct(
        GeoApiConsumer $geoApiConsumer
    ) {
        $this->geoApiConsumer = $geoApiConsumer;
    }

    /**
     * Builds the selects structure
     *
     * @param string $areaId The area id
     */
    public function buildSelects($areaId)
    {
        $this->initProperties();
        $areaInfo = $this->geoApiConsumer->getAreaLocation($areaId);

        $parentId = null;
        $depth    = 0;
        do {
            $childrenAreas = $this->geoApiConsumer->getChildrenAreas($parentId);

            if ($childrenAreas) {
                $childrenExample = reset($childrenAreas);
                $selected        = isset($areaInfo[$depth]['id']) ? $areaInfo[$depth]['id'] : null;

                $selector        = $this->formatSelector(
                    $childrenExample['type'],
                    $this->generateOptions($childrenAreas),
                    $selected
                );
                $this->selects[] = $selector;
            } else {
                $this->maxDepth = true;
            }

            $parentId = isset($areaInfo[$depth]['id'])
                ? $areaInfo[$depth]['id']
                : null;
            ++$depth;
        } while ($parentId);
    }

    /**
     * Get the build selects.
     *
     * @return array
     */
    public function getSelects()
    {
        return $this->selects;
    }

    /**
     * If we've build the max depth select
     *
     * @return bool
     */
    public function getMaxDepth()
    {
        return $this->maxDepth;
    }

    /**
     * Generates all the options for a select given the raw options from the areas info from the API.
     *
     * @param array $rawOptions The raw options
     *
     * @return array
     */
    protected function generateOptions($rawOptions)
    {
        $options = [];
        foreach ($rawOptions as $rawOption) {
            $options[$rawOption['id']] = $rawOption['name'];
        }
        return $options;
    }

    /**
     * Returns a well formatted selector
     *
     * @param string      $label    The selector label
     * @param array       $options  The selector options
     * @param string|null $selected The selected option
     *
     * @return array
     */
    protected function formatSelector($label, $options, $selected = null)
    {
        return [
            'label'    => $label,
            'options'  => $options,
            'selected' => $selected,
        ];
    }

    /**
     * Starts the class properties
     */
    protected function initProperties()
    {
        $this->maxDepth = false;
        $this->selects  = [];
    }
}