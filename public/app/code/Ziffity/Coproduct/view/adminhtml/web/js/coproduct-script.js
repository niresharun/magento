require(['jquery', 'domReady!'], function ($) {
    var triggeringAttributeCode = 'applicable_to';
    function handleAttributeVisibility() {
        var triggeringAttributeValue = $('[name="product['+ triggeringAttributeCode+']"]').val();
        if (triggeringAttributeValue === 'Glass') {
            $('[data-index="glass_type"]').show();
            $('[data-index="backingboard_type"]').hide();
        } else if (triggeringAttributeValue === 'Backing Board') {
            $('[data-index="backingboard_type"]').show();
            $('[data-index="glass_type"]').hide();
        } else {
            $('[data-index="glass_type"]').hide();
            $('[data-index="backingboard_type"]').hide();
        }
    }

    handleAttributeVisibility();

    $(document).on('change',  '[name="product['+triggeringAttributeCode+']"]', function() {
        handleAttributeVisibility();
    });
});
