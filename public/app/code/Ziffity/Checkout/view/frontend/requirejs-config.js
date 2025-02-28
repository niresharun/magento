var config = {
    config: {
        mixins: {
		    'Magento_Ui/js/form/element/abstract': {
                'Ziffity_Checkout/js/form/element/abstract-ext': true
            },
        }
    },
	'map': {
        '*': {
            'ui/template/form/element/input': 'Ziffity_Checkout/template/form/element/input'
        }
    }
};
