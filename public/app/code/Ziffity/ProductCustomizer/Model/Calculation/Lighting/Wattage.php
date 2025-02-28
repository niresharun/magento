<?php
namespace Ziffity\ProductCustomizer\Model\Calculation\Lighting;

class Wattage
{
    /**
     * Calculate Top Lighting Wattage.
     *
     * @param float $innerFrameWidth Inner Frame Width.
     *
     * @return float
     */
    public function calculateTopLighting($innerFrameWidth)
    {
        $result = (($innerFrameWidth / 12) * 1.44) + 5;

        return ceil($result);
    }

    /**
     * Calculate Perimeter wattage.
     *
     * @param float $innerFrameWidth  Inner Frame Width.
     * @param float $innerFrameHeight Inner Frame Height.
     *
     * @return float
     */
    public function calculatePerimeterLighting($innerFrameWidth, $innerFrameHeight)
    {
        $result = (((($innerFrameWidth / 12) * 2) + (($innerFrameHeight / 12) * 2)) * 1.44) + 5;

        return ceil($result);
    }
}
