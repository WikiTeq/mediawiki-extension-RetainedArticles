<?php

namespace MediaWiki\Extension\RetainedArticles;

use CommentStoreComment;
use MediaWiki\MediaWikiServices;
use MediaWiki\Permissions\PermissionManager;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\User\UserIdentity;
use MWException;
use MWUnknownContentModelException;
use Title;
use User;

class Tools {

	/**
	 * @param Title $title
	 * @param Title $redirectTarget
	 * @param User|null $performer User who performs the recreation, e.g. the deleter.
	 *  Used for permission checks and edit attribution. Falls back to a system user when null.
	 * @return void
	 * @throws MWException
	 * @throws MWUnknownContentModelException
	 */
	public static function createRedirect(
		Title $title, Title $redirectTarget, ?User $performer = null
	) {
		$services = MediaWikiServices::getInstance();
		if ( !$performer ) {
			$performer = User::newSystemUser( 'MediaWiki default' );
		}

		// Respect page protection and the performer's rights before resurrecting
		// the deleted page as a redirect.
		$permissionManager = $services->getPermissionManager();
		if ( !$permissionManager->userCan( 'edit', $performer, $title ) ) {
			throw new MWException(
				'User ' . $performer->getName() .
				' is not allowed to recreate the deleted page as a redirect: ' . $title->getPrefixedText()
			);
		}

		$contentHandler = $services->getContentHandlerFactory()->getContentHandler( $title->getContentModel() );
		$redirectContent = $contentHandler->makeRedirectContent( $redirectTarget );
		if ( !$redirectContent ) {
			throw new MWException(
				'Cannot create redirect content for the title: ' . $title->getPrefixedDBkey()
			);
		}

		$page = $services->getWikiPageFactory()->newFromTitle( $title );
		$updater = $page->newPageUpdater( $performer );
		$updater->setContent( SlotRecord::MAIN, $redirectContent );
		$edit_summary = CommentStoreComment::newUnsavedComment(
			wfMessage( 'retained-articles-edit-summary' )->text()
		);
		$updater->saveRevision( $edit_summary, EDIT_NEW );
	}
}
