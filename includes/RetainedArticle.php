<?php

namespace MediaWiki\Extension\RetainedArticles;

//use MediaWiki\MediaWikiServices;
//use MediaWiki\Page\PageIdentity;
use stdClass;
use Title;
use WikiPage;

class RetainedArticle {

	/**
	 * @var stdClass
	 */
	private stdClass $row;

	/**
	 * @param stdClass $row
	 */
	public function __construct( stdClass $row ) {
		$this->row = $row;
	}

	/**
	 * @param stdClass $row
	 * @return self
	 */
	public static function newFromRow( stdClass $row ): self {
		return new self( $row );
	}

	/**
	 * @return Title|null
	 */
	public function getRetainedTitle(): ?Title {
		$row = $this->row;
		return Title::makeTitle( $row->ra_retained_page_namespace, $row->ra_retained_page_title );
	}

	/**
	 * @return int|null
	 */
	public function getRetainedTitleId(): int {
		return (int)$this->row->ra_retained_page_id;
	}

	/**
	 * @return Title|null
	 */
	public function getRetainedTitleIfExists(): ?Title {
		$title = $this->getRetainedTitle();
		if ( !$title ) {
			return null;
		}
		if ( $title->exists() ) {
			if ( !$title->isRedirect() ) {
				return $title;
			}
			$title = self::getRedirectTarget( $title );
			if ( $title ) {
				return $title;
			}
		}
		$title = Title::newFromID( $this->getRetainedTitleId() );
		if ( !$title || !$title->exists() ) {
			return null;
		}
		if ( $title->isRedirect() ) {
			return self::getRedirectTarget( $title );
		}
		return $title;
	}

	/**
	 * @param Title $title PageIdentity
	 * @return Title|null
	 */
	private static function getRedirectTarget( Title $title ): ?Title {
//		$services = MediaWikiServices::getInstance();
//		$redirectLookup = $services->getRedirectLookup();
//		$linkTarget = $redirectLookup->getRedirectTarget( $title );
//		$targetTitle = Title::castFromLinkTarget( $linkTarget );
		$page = WikiPage::factory( $title );
		$targetTitle = $page->getRedirectTarget();
		if ( $targetTitle && $targetTitle->exists() ) {
			return $targetTitle;
		}
		return null;
	}
}
