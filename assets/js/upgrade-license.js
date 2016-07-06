jQuery( function ( $ ) {

	// objs
	var obj_price = $( '#lwp-upgrade-license-price:first' );
	var obj_sel = $( '#lwp_new_license:first' );

	// update function
	$.fn.lwp_update_upgrade_price = function ( obj_sel ) {
		this.hide().html( '$' + $( obj_sel ).find( 'option:selected' ).data( 'upgrade_price' ) ).fadeIn();
	};

	// bind change
	$( obj_sel ).change( function () {
		obj_price.lwp_update_upgrade_price( this );
	} );

	// set initial price
	obj_price.lwp_update_upgrade_price( obj_sel );

} );
