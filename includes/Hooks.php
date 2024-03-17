<?php

namespace MediaWiki\Extension\RetainedArticles;

use Html;
use MediaWiki\Installer\Hook\LoadExtensionSchemaUpdatesHook;
use MediaWiki\Page\Hook\ArticleDeleteCompleteHook;
use MediaWiki\Page\Hook\BeforeDisplayNoArticleTextHook;
//use MediaWiki\Page\Hook\PageDeleteCompleteHook;
use MediaWiki\Page\ProperPageIdentity;
use MediaWiki\Permissions\Authority;
use MediaWiki\Revision\RevisionRecord;
use RequestContext;
use Title;

class Hooks implements ArticleDeleteCompleteHook, LoadExtensionSchemaUpdatesHook, BeforeDisplayNoArticleTextHook {

//	public function onPageDeleteComplete(
//		// ArticleDeleteAfterSuccess?
//		ProperPageIdentity $page, Authority $deleter, string $reason, int $pageID,
//		RevisionRecord $deletedRev, ManualLogEntry $logEntry, int $archivedRevisionCount
//	) {
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
//				Tools::setRetainedArticle( $deletedRev->getPage(), $retainedTitle, $deleter->getUser() );
				Tools::setRetainedArticle( $wikiPage->getTitle(), $retainedTitle, $user );
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
	public function onLoadExtensionSchemaUpdates( $updater ) {
		$dir = __DIR__ . '/../sql/' . $updater->getDB()->getType() . '/';
		$updater->addExtensionTable( 'retained_articles', $dir . 'RetainedArticles.sql' );
	}

	/**
	 * @inheritDoc
	 */
	public function onBeforeDisplayNoArticleText( $article ) {
		$retainedArticle = Tools::getRetainedArticle( $article->getTitle() );
		$user = $article->getContext()->getUser();
		$output = $article->getContext()->getOutput();
		$html = '';
		$title = null;
		if ( $retainedArticle ) {
			$title = $retainedArticle->getRetainedTitleIfExists();
			if ( $title ) {
				$label = Html::element( 'b', [], wfMessage( 'retained-articles-title-label' )->text() );
				$link = Html::element( 'a', [ 'href' => $title->getLinkURL() ], $title->getFullText() );
				$html = Html::rawElement( 'div', [ 'id' => 'mw-retained-article' ], $label . ' ' . $link );
				$output->addInlineStyle( '#mw-retained-article { padding: 3em 0; }' );
			}
		}
		if ( $user->isAllowed( 'delete' ) ) {
			$jsConfig = [];
			if ( $retainedArticle ) {
				$originTitle = $retainedArticle->getRetainedTitle();
				if ( $title ) {
					$jsConfig[ 'manage-retained-article-status' ] = 'exists';
					$jsConfig[ 'manage-retained-article-title' ] = $title->getFullText();
//					if ( !$originTitle->isSamePageAs( $title ) ) {
					if ( $originTitle->getNamespace() === $title->getNamespace() &&
						$originTitle->getDBkey() === $title->getDBkey()
					) {
						$jsConfig[ 'manage-retained-article-origin-title' ] = $originTitle->getFullText();
					}
				} else {
					$jsConfig[ 'manage-retained-article-status' ] = 'non-exists';
					$jsConfig[ 'manage-retained-article-origin-title' ] = $originTitle->getFullText();
				}
			} else {
				$jsConfig[ 'manage-retained-article-status' ] = 'not-set';
			}
			$output->addJsConfigVars( $jsConfig );
			// TODO $output->addModules( [ 'ext.RetainedArticle.Manage' ] );
			$html = Html::rawElement( 'div', [ 'id' => 'mw-manage-retained-article' ], $html );
		}
		if ( $html ) {
			$output->addHTML( $html );
		}
	}
}
