jQuery( function ( $ ) {

    // normal select2 field
    jQuery( '.lwp-select2' ).select2();

    // dem ajax fields
    jQuery( '.lwp-select2-customer' ).select2( {
        ajax: {
            url: ajax_url,
            dataType: 'json',
            delay: 250,
            data: function ( params ) {
                return {
                    q: params.term, // search term
                    page: params.page
                };
            },
            processResults: function ( data, page ) {
                return {
                    results: data.items
                };
            },
            cache: true
        }
    } );
} );