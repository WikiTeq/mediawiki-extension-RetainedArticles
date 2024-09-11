<?php

namespace MediaWiki\Extension\RetainedArticles;

use Article;
use ManualLogEntry;
use MediaWiki\Hook\OutputPageBeforeHTMLHook;
use MediaWiki\Page\Hook\PageDeleteCompleteHook;
use MediaWiki\Page\ProperPageIdentity;
use MediaWiki\Permissions\Authority;
use MediaWiki\Revision\RevisionRecord;
use MWException;
use RequestContext;
use Title;

class Hooks implements OutputPageBeforeHTMLHook, PageDeleteCompleteHook {

	/**
	 * @inheritDoc
	 */
	public function onPageDeleteComplete(
		ProperPageIdentity $page, Authority $deleter, string $reason, int $pageID, RevisionRecord $deletedRev,
		ManualLogEntry $logEntry, int $archivedRevisionCount
	) {
		$request = RequestContext::getMain()->getRequest();
		$retainedArticle = $request->getVal( 'retained-article' );
		if ( $retainedArticle ) {
			$retainedTitle = Title::newFromText( $retainedArticle );
			if ( $retainedTitle ) {
				$title = Title::castFromPageIdentity( $page );
				if ( !$title ) {
					wfDebugLog(
						__CLASS__,
						__METHOD__ . ' Cannot create the title object using ProperPageIdentity: ' . $page->getDBkey()
					);
				} else {
					try {
						Tools::createRedirect( $title, $retainedTitle );
					} catch ( MWException $e ) {
						wfDebugLog(
							__CLASS__,
							__METHOD__ . ' Cannot create redirect page: ' . $e->getText()
						);
					}
				}
			} else {
				wfDebugLog(
					__CLASS__,
					__METHOD__ . ' Cannot create a title object for the string: ' . $retainedArticle
				);
			}
		}
	}

	/**
	 * @inheritDoc
	 */
	public function onOutputPageBeforeHTML( $out, &$text ) {
		static $alreadyHere = false;
		if ( $alreadyHere ) {
			return;
		}
		$alreadyHere = true;
		$title = $out->getTitle();
		if ( !$title || !$title->isRedirect() ) {
			return;
		}

		$article = Article::newFromTitle( $title, $out->getContext() );
		$article->showMissingArticle();
		$alreadyHere = false;
	}
}
