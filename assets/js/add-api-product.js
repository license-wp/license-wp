jQuery( function ( $ ) {

    // Datepicker
    jQuery( '#_last_updated' ).datepicker({
        defaultDate:     '',
		dateFormat:      'yy-mm-dd',
		numberOfMonths:  1,
		showButtonPanel: true,
    });
} );
