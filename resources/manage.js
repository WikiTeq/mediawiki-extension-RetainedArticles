( function ( mw ) {
	'use strict';

	$( function () {
		var status = mw.config.get( 'manage-retained-article-status' );
		var $manageElement = $( '#mw-manage-retained-article' );

		if ( status === 'not-set' ) {
			var addButton = new OO.ui.ButtonInputWidget( {
				label: mw.msg( 'retained-articles-add-button-label' )
			} );
			$manageElement.append( addButton.$element );
		}
	} );
} )( window.mediaWiki );
