<?php

namespace Ziffity\ProductCustomizer\Model\Size;

use Ziffity\ProductCustomizer\Model\FrameOptionConfigProvider;
use Ziffity\ProductCustomizer\Model\MatOptionConfigProvider;
use Ziffity\ProductCustomizer\Model\ChalkBoardConfigProvider;
use Ziffity\ProductCustomizer\Model\CorkBoardConfigProvider;
use Ziffity\ProductCustomizer\Model\DryeraseBoardConfigProvider;
use Ziffity\ProductCustomizer\Model\FabricOptionConfigProvider;
use Ziffity\ProductCustomizer\Model\LetterBoardConfigProvider;
use Ziffity\ProductCustomizer\Model\LaminateOptionConfigProvider;

class ValidateOptionsSize
{
    protected $frameProvider;

    protected $matOptionConfigProvider;

    protected $chalkBoardConfigProvider;

    protected  $corkBoardConfigProvider;

    protected  $dryeraseBoardConfigProvider;

    protected $fabricOptionConfigProvider;

    protected $letterBoardConfigProvider;

    protected $laminateOptionConfigProvider;

    public function __construct(
        FrameOptionConfigProvider $frameProvider,
        MatOptionConfigProvider $matOptionConfigProvider,
        ChalkBoardConfigProvider $chalkBoardConfigProvider,
        CorkBoardConfigProvider $corkBoardConfigProvider,
        DryeraseBoardConfigProvider $dryeraseBoardConfigProvider,
        FabricOptionConfigProvider $fabricOptionConfigProvider,
        LetterBoardConfigProvider $letterBoardConfigProvider,
        LaminateOptionConfigProvider $laminateOptionConfigProvider
    )
    {
        $this->frameProvider = $frameProvider;
        $this->matOptionConfigProvider = $matOptionConfigProvider;
        $this->chalkBoardConfigProvider = $chalkBoardConfigProvider;
        $this->corkBoardConfigProvider = $corkBoardConfigProvider;
        $this->dryeraseBoardConfigProvider = $dryeraseBoardConfigProvider;
        $this->fabricOptionConfigProvider = $fabricOptionConfigProvider;
        $this->letterBoardConfigProvider = $letterBoardConfigProvider;
        $this->laminateOptionConfigProvider = $laminateOptionConfigProvider;
    }

    public function validateOptions($options, $product)
    {
        if($options){
            $tabs = $this->getTabs($product, $options);
//            if($tabs) {
//                foreach ($tabs as $key => $tabClass){
//                    if (method_exists($tabClass, 'prepareTab')){
//                        $tabClass->prepareTab($product, $options);
//                    }
//                }
//            }

            return $tabs;
        }
    }

    public function getTabs($product, $options)
    {
        $tabs = [];
        foreach ($options['data']['options'] as $key => $option){

            switch ($key){
                case 'frame':
                    $tabs['frame'] = method_exists(FrameOptionConfigProvider::class, 'prepareTab')?
                        $this->frameProvider->prepareTab($product, $options): '';
                    break;
                case 'mat':
                    $tabs['mat'] = method_exists(MatOptionConfigProvider::class, 'prepareTab')?
                        $this->matOptionConfigProvider->prepareTab($product, $options): '';
                    break;
                case 'chalk_board':
                    $tabs['chalk_board'] = method_exists(ChalkBoardConfigProvider::class, 'prepareTab')?
                        $this->chalkBoardConfigProvider->prepareTab($product, $options): '';
                    break;
                case 'cork_board':
                    $tabs['chalk_board'] =  method_exists(CorkBoardConfigProvider::class, 'prepareTab')?
                        $this->corkBoardConfigProvider->prepareTab($product, $options): '';
                    break;
                case 'dryerase_board':
                    $tabs['dryerase_board'] = method_exists(DryeraseBoardConfigProvider::class, 'prepareTab')?
                        $this->corkBoardConfigProvider->prepareTab($product, $options): '';
                    break;
                case 'fabric':
                    $tabs['fabric'] = method_exists(FabricOptionConfigProvider::class, 'prepareTab')?
                        $this->fabricOptionConfigProvider->prepareTab($product, $options): '';
                    break;
                case 'letter_board':
                    $tabs['letter_board'] = method_exists(LetterBoardConfigProvider::class, 'prepareTab')?
                        $this->letterBoardConfigProvider->prepareTab($product, $options): '';
                    break;
                case 'Laminate':
                    $tabs['Laminate'] = method_exists(LaminateOptionConfigProvider::class, 'prepareTab')?
                        $this->laminateOptionConfigProvider->prepareTab($product, $options): '';
                    break;
                default:
                    break;
            }
        }
        return $tabs;
    }
}


