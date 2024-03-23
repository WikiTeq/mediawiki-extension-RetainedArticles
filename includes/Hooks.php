<?php

namespace MediaWiki\Extension\RetainedArticles;

use Article;
use MediaWiki\Hook\OutputPageBeforeHTMLHook;
use MediaWiki\Page\Hook\ArticleDeleteCompleteHook;
use MWException;
use RequestContext;
use Title;

class Hooks implements ArticleDeleteCompleteHook, OutputPageBeforeHTMLHook {

	/**
	 * @inheritDoc
	 */
	public function onArticleDeleteComplete(
		$wikiPage, $user, $reason, $id, $content, $logEntry, $archivedRevisionCount
	) {
		$request = RequestContext::getMain()->getRequest();
		$retainedArticle = $request->getVal( 'retained-article' );
		if ( $retainedArticle ) {
			$retainedTitle = Title::newFromText( $retainedArticle );
			if ( $retainedTitle ) {
				try {
					Tools::createRedirect( $wikiPage->getTitle(), $retainedTitle );
				} catch ( MWException $e ) {
					wfDebugLog(
						__CLASS__,
						__METHOD__ . ' Cannot create redirect page: ' . $e->getText()
					);
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
