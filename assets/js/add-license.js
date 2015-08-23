jQuery( function ( $ ) {

    // normal select2 field
    jQuery( '.lwp-select2' ).select2();

    // dem ajax fields
    jQuery( '.lwp-select2-customer' ).select2( {
        ajax: {
            url: ajaxurl,
            dataType: 'json',
            delay: 250,
            data: function ( params ) {
                return {
                    action: 'woocommerce_json_search_customers',
                    security: jQuery( this ).data( 'nonce' ),
                    term: params
                };
            },
            results: function ( data ) {

                // new customers array
                var customers = [];

                // JSON to array
                $.each( data, function ( i, val ) {
                    customers.push( {
                        id: i,
                        text: val.replace( /&ndash;/g, '-' )
                    } );
                } );

                // return customers
                return {
                    results: customers
                };
            },
            cache: false
        },
        minimumInputLength: 3,
    } ).change( function () {

        var aef = jQuery( '#activation_email' );

        aef.attr( 'placeholder', 'Loading...' );

        // update activation email field
        jQuery.post( ajaxurl, {
            action: 'wpl_add_license_get_email',
            id: jQuery( this ).val(),
            nonce: jQuery( this ).data( 'nonce' )
        }, function ( response ) {
            aef.attr( 'placeholder', 'Email address used to activate product' );
            aef.val( response );
        } );
    } );
} );