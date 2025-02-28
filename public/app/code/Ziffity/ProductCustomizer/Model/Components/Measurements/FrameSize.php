<?php
declare(strict_types=1);

namespace Ziffity\ProductCustomizer\Model\Components\Measurements;

use Ziffity\ProductCustomizer\Helper\Data as Helper;

/**
 * Default Config Provider for customframe
 */
class FrameSize
{

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @param Helper $helper
     */
    public function __construct(
        Helper $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Get overall frame width.
     *
     * @param  array $selectionData product selection.
     *
     * @return float
     */
    public function getOverallWidth($selectionData)
    {
        if (!empty($selectionData['frame']['active_item']['img_draw'])) {
            $layerHeight = $selectionData['frame']['active_item']['img_draw']['height']['integer']
                . ' '
                . $selectionData['frame']['active_item']['img_draw']['height']['tenth'];
            $layerHeight = $this->helper->fractionalToFloat($layerHeight);
            return $this->getInnerFrameWidth($selectionData) + $layerHeight * 2;
        }
        return round((float) $this->getInnerFrameWidth($selectionData), 4);
    }

    /**
     * Get Frame Width
     *
     * @param array $selectionData product selection.
     *
     * @return float
     */
    public function getInnerFrameWidth(array $selectionData)
    {
        $width = 0;
        if (!empty($selectionData['size']['type'])) {
            if ($selectionData['size']['type'] == 'frame') { // for Frame size type
                $width = $this->getInnerFrameFrameWidth($selectionData);
            } elseif ($selectionData['size']['type'] == 'graphic') {  // for Graphic size type
                $width = $this->getInnerFrameGraphicWidth($selectionData);
            }
        }
        return round((float) $width, 4);
    }

    /**
     * Get Inner Frame Width.
     *
     * @param array $selectionData product selection.
     *
     * @return float
     */
    protected function getInnerFrameFrameWidth(array $selectionData)
    {
        $width = 0;
        if (isset($selectionData['size']['width'])) {
            $width = $selectionData['size']['width']['integer'] . ' ' . $selectionData['size']['width']['tenth'];
        }
        $width = $this->helper->fractionalToFloat($width);
        return round((float) $width, 4);
    }

    /**
     * Get Inner Frame Width for Graphic Flow.
     *
     * @param array $selectionData product selection.
     *
     * @return float
     */
    protected function getInnerFrameGraphicWidth(array $selectionData)
    {
        // Inner Frame Graphic width - Overlap = Viewable Area Width
        $graphicWidth = $this->getInnerFrameFrameWidth($selectionData);
        $matOverlap = $this->getMatOverlap($selectionData);
        $frameOverlap = $this->getFrameOverlap($selectionData);
        $overlap = $matOverlap;
        if (empty($overlap)) {
            $overlap = $frameOverlap;
        }
        $graphicWidth = $graphicWidth - ($overlap * 2);

        $topMatReveal = $this->getTopMatReveal($selectionData);
        $middleMatReveal = $this->getMatReveal($selectionData, 'middle_mat');
        $bottomMatReveal = $this->getMatReveal($selectionData, 'bottom_mat');
        $width = $graphicWidth + ($topMatReveal + $middleMatReveal + $bottomMatReveal) * 2;
        return round((float) $width, 4);
    }

    /**
     * @param array $selectionData product selection.
     * @return float|int|string
     */
    public function getOverallHeight($selectionData)
    {
        if (isset($selectionData['frame']['active_item']['img_draw'])) {
            $layerHeight = $selectionData['frame']['active_item']['img_draw']['height']['integer'].' '. $selectionData['frame']['active_item']['img_draw']['height']['tenth'];
            $layerHeight = $this->helper->fractionalToFloat($layerHeight);
            return $this->getInnerFrameHeight($selectionData) + $layerHeight * 2;
        }
        return round((float) $this->getInnerFrameHeight($selectionData), 4);
    }

    /**
     * @param array $selectionData product selection.
     * @return float|int|string
     */
    public function getInnerFrameHeight($selectionData)
    {
        $height = 0;
        // Get InnerFrameHeight
        if (!empty($selectionData['size']['type'])) {
            if ($selectionData['size']['type'] == 'frame') { // for Frame size type
                $height = $this->getInnerFrameFrameHeight($selectionData);
            } elseif ($selectionData['size']['type'] == 'graphic') {  // for Graphic size type
                $height = $this->getInnerFrameGraphicHeight($selectionData);
            }
        }
        return round((float) $height, 4);
    }

    /**
     * @param array $selectionData product selection.
     * @return float|int|string
     */
    protected function getInnerFrameFrameHeight(array $selectionData)
    {
        $height = 0;
        if (isset($selectionData['size']['height'])) {
            $height = $selectionData['size']['height']['integer'] . ' ' . $selectionData['size']['height']['tenth'];
        }
        $height = $this->helper->fractionalToFloat($height);
        return round((float) $height, 4);
    }

    /**
     * @param array $selectionData product selection.
     * @return float|int|string
     */
    protected function getInnerFrameGraphicHeight(array $selectionData)
    {
        $height = 0;
        // Graphic width - Overlap = Viewable Area Width
        $viewableAreaHeight = $this->getInnerFrameFrameHeight($selectionData);
        $matOverlap = $this->getMatOverlap($selectionData);
        $frameOverlap = $this->getFrameOverlap($selectionData);
        $overlap = $matOverlap;
        if (empty($overlap)) {
            $overlap = $frameOverlap;
        }

        $viewableAreaHeight = $viewableAreaHeight - ($overlap * 2);

        $topMatReveal = $this->getTopMatReveal($selectionData);
        $middleMatReveal = $this->getMatReveal($selectionData, 'middle_mat');
        $bottomMatReveal = $this->getMatReveal($selectionData, 'bottom_mat');
        $headerHeight = $this->getHeaderHeight($selectionData);
        $labelHeight = $this->getLabelHeight($selectionData);

        $height = $viewableAreaHeight + 2 * ($middleMatReveal + $bottomMatReveal) + $topMatReveal;

        if (!empty($headerHeight)) {
            $headerHeight += (1.5 + 1.5); //1 = gap
            if ($headerHeight < $topMatReveal) {
                $headerHeight = $topMatReveal;
            }
            $height += $headerHeight;
        }

        if (!empty($labelHeight)) {
            $labelHeight += (1.5 + 1.5); //1 = gap
            if ($labelHeight < $topMatReveal) {
                $labelHeight = $topMatReveal;
            }
            $height += $labelHeight;
        }

        if (empty($labelHeight) && empty($headerHeight)) {
            $height += $topMatReveal;
        }

        return round((float) $height, 4);
    }

    /**
     * @param array $selectionData product selection.
     * @return float|int|string
     */
    protected function getMatOverlap(array $selectionData)
    {
        $matOverlap = 0;
        if (!empty($selectionData['mat']['overlap'])) {
            $matOverlap = $this->helper->fractionalToFloat($selectionData['mat']['overlap']);
        }
        return round((float) $matOverlap, 4);
    }

    /**
     * Moulding Width - Back of Moulding Width = Overlap
     *
     * @param array $selectionData product selection.
     *
     * @return float|int|string
     */
    public function getFrameOverlap(array $selectionData)
    {
        $graphicOverlap = $this->getMouldingWidth($selectionData) - $this->getBackOfMouldingWidth($selectionData);
        return round((float) $graphicOverlap, 4);
    }

    /**
     * @param array $selectionData product selection.
     * @return float|int|string
     */
    protected function getMouldingWidth(array $selectionData)
    {
        $mouldingWidth = 0;

        if (isset($selectionData['frame']['active_item']['img_draw']['height'])) {
            $mouldingWidth = $selectionData['frame']['active_item']['img_draw']['height']['integer']
                . ' '
                . $selectionData['frame']['active_item']['img_draw']['height']['tenth'];
            $mouldingWidth = $this->helper->fractionalToFloat($mouldingWidth);
        }
        return round((float) $mouldingWidth, 4);
    }

    /**
     * @param array $selectionData product selection.
     * @return float|int|string
     */
    public function getBackOfMouldingWidth(array $selectionData)
    {
        $backOfMouldingWidth = 0;
        if (!empty($selectionData['frame']['active_item']['img_draw'])) {
            $backOfMouldingWidth = $selectionData['frame']['active_item']['img_draw']['back_of_moulding_width'];
        }
        return round((float) $backOfMouldingWidth, 4);
    }

    /**
     * @param array $selectionData product selection.
     * @return float|int|string
     */
    protected function getTopMatReveal(array $selectionData)
    {
        $reveal = 0;
        if (!empty($selectionData['mat']['sizes']['top'])) {
            $reveal = $selectionData['mat']['sizes']['top']['integer']
                . ' '
                . $selectionData['mat']['sizes']['top']['tenth'];
            $reveal = $this->helper->fractionalToFloat($reveal);
        }
        return round((float) $reveal, 4);
    }

    /**
     * Retrieve mat reveal.
     *
     * @param array $selectionData product selection.
     * @param string $mat  Mat ID (middle, bottom)
     *
     * @return float|int
     */
    protected function getMatReveal(array $selectionData, $mat)
    {
        $reveal = 0;
        if (!empty($selectionData['mat']['active_items'][$mat]['id'])) {
            $reveal = $this->helper->fractionalToFloat($selectionData['mat']['sizes']['reveal']);
        }
        return round((float) $reveal, 4);
    }

    /**
     * @param array $selectionData product selection.
     * @return float|int|string
     */
    protected function getHeaderHeight(array $selectionData)
    {
        $height = 0;
        if (!empty($selectionData['header']['headerDimensions']['height'])) {
            $height = $this->helper->fractionalToFloat($selectionData['header']['headerDimensions']['height']);
        }
        return round((float) $height, 4);
    }

    /**
     * @param array $selectionData product selection.
     * @return float|int|string
     */
    protected function getLabelHeight(array $selectionData)
    {
        $height = 0;
        if (!empty($selectionData['labels']['size']['height'])) {
            $height = $this->helper->fractionalToFloat($selectionData['labels']['size']['height']);
        }
        return round((float) $height, 4);
    }

}
