<?php
namespace Ziffity\ProductCustomizer\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class FontOptions implements OptionSourceInterface
{
    /**
     * Get options as array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'Alegreya SC', 'label' => __('Alegreya SC')],
            ['value' => 'Alfa Slab One', 'label' => __('Alfa Slab One')],
            ['value' => 'Cantata One', 'label' => __('Cantata One')],
            ['value' => 'Cinzel', 'label' => __('Cinzel')],
            ['value' => 'Farsan', 'label' => __('Farsan')],
            ['value' => 'Fira Sans Condensed', 'label' => __('Fira Sans Condensed')],
            ['value' => 'Hammersmith One', 'label' => __('Hammersmith One')],
            ['value' => 'Josefin Slab', 'label' => __('Josefin Slab')],
            ['value' => 'Julius Sans One', 'label' => __('Julius Sans One')],
            ['value' => 'Lora', 'label' => __('Lora')],
            ['value' => 'Montserrat', 'label' => __('Montserrat')],
            ['value' => 'Noticia Text', 'label' => __('Noticia Text')],
            ['value' => 'Oswald', 'label' => __('Oswald')],
            ['value' => 'PT Sans', 'label' => __('PT Sans')],
            ['value' => 'Pacifico', 'label' => __('Pacifico')],
            ['value' => 'Playfair Display', 'label' => __('Playfair Display')],
            ['value' => 'Quattrocento', 'label' => __('Quattrocento')],
            ['value' => 'Quicksand', 'label' => __('Quicksand')],
            ['value' => 'Raleway', 'label' => __('Raleway')],
            ['value' => 'Sacramento', 'label' => __('Sacramento')]
        ];
    }
}
