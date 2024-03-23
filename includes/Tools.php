<?php

namespace MediaWiki\Extension\RetainedArticles;

use CommentStoreComment;
use MediaWiki\MediaWikiServices;
use MediaWiki\Storage\SlotRecord;
use MWException;
use MWUnknownContentModelException;
use Title;
use User;
use WikiPage;

class Tools {

	/**
	 * @param Title $title
	 * @param Title $redirectTarget
	 * @return void
	 * @throws MWException
	 * @throws MWUnknownContentModelException
	 */
	public static function createRedirect( Title $title, Title $redirectTarget ) {
		$services = MediaWikiServices::getInstance();
		$contentHandler = $services->getContentHandlerFactory()->getContentHandler( $title->getContentModel() );
		$redirectContent = $contentHandler->makeRedirectContent( $redirectTarget );

		$page = WikiPage::factory( $title );
		$updater = $page->newPageUpdater( User::newSystemUser( 'MediaWiki default' ) );
		$updater->setContent( SlotRecord::MAIN, $redirectContent );
		$edit_summary = CommentStoreComment::newUnsavedComment(
			wfMessage( 'retained-articles-edit-summary' )->text()
		);
		$updater->saveRevision( $edit_summary, EDIT_NEW );
	}
}
