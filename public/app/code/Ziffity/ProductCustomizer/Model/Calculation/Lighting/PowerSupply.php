<?php

namespace Ziffity\ProductCustomizer\Model\Calculation\Lighting;

class PowerSupply
{
    /**
     * Calculate Power Supply.
     *
     * @param float $totalWattage Total wattage.
     *
     * @return float
     */
    public function calculatePowerSupply($totalWattage)
    {
        $result = ($totalWattage * 1.2);
        $powerSupply = 60;
        switch ($result) {
            case $result <= 24:
                $powerSupply = 24;
                break;
            case $result <= 36:
                $powerSupply = 36;
                break;
            case $result <= 60:
                $powerSupply = 60;
                break;
        }

        return $powerSupply;
    }
}
