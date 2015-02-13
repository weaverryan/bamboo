function areaSelectors() {
    // Polyfill for IE8 and below
    if (!Date.now) {
        Date.now = function() { return new Date().getTime(); }
    }

    function bindSelector( container ) {
        var target = container + ' select';
        $(target).on('change', function (e) {
            var optionSelected  = $("option:selected", this).val(),
                selectorsUrl = $(container).data('url') + '/' + optionSelected;

            if(optionSelected) {
                $.ajax(selectorsUrl, {
                    success: function (response) {
                        $(container).replaceWith(response);
                        if (undefined != $(container).data('max-depth')) {
                            document.getElementById('store_geo_form_type_address_cityId').value = optionSelected;
                        }
                        bindSelector(container);
                    }
                });
            }
        });
    }

    bindSelector('#js-area-selectors');
}

if(typeof jQuery=='undefined') {
    var headTag = document.getElementsByTagName("head")[0];
    var jqTag = document.createElement('script');
    jqTag.type = 'text/javascript';
    jqTag.src = 'https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js';
    jqTag.onload = areaSelectors;
    headTag.appendChild(jqTag);
} else {
    areaSelectors();
}