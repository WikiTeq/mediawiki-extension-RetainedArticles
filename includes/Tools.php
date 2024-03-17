<?php

namespace MediaWiki\Extension\RetainedArticles;

use DBError;
use MediaWiki\MediaWikiServices;
//use MediaWiki\Page\PageIdentity;
use MediaWiki\User\UserIdentity;
use Title;
use Wikimedia\Rdbms\ILoadBalancer;

class Tools {

	/**
	 * @param Title $deletedPage PageIdentity
	 * @param Title $retainedTitle
	 * @param UserIdentity $user
	 * @return bool
	 */
	public static function setRetainedArticle(
		Title $deletedPage, Title $retainedTitle, UserIdentity $user
	): bool {
		$services = MediaWikiServices::getInstance();
//		$actorStore = $services->getActorStore();
		$db = $services->getDBLoadBalancer()->getConnection( ILoadBalancer::DB_PRIMARY );
		$index = [
			'ra_deleted_page_namespace' => $deletedPage->getNamespace(),
			'ra_deleted_page_title' => $deletedPage->getDBkey(),
		];
		$set = [
			'ra_retained_page_id' => $retainedTitle->getId() ?: null,
			'ra_retained_page_namespace' => $retainedTitle->getNamespace(),
			'ra_retained_page_title' => $retainedTitle->getDBkey(),
			// $actorStore->acquireActorId( $user, $db ),
			'ra_actor_id' => $user->getActorId(),
			'ra_timestamp' => $db->timestamp(),
		];
		try {
			$db->upsert(
				'retained_articles',
				[ $index + $set ],
				[ array_keys( $index ) ],
				$set,
				__METHOD__
			);
			return true;
		} catch ( DBError $ex ) {
			wfDebugLog( __CLASS__, __METHOD__ . ': ' . $ex->getMessage() );
		}
		return false;
	}

	/**
	 * @param Title $title PageIdentity
	 * @return RetainedArticle|null
	 */
	public static function getRetainedArticle( Title $title ): ?RetainedArticle {
		$services = MediaWikiServices::getInstance();
		$db = $services->getDBLoadBalancer()->getConnection( ILoadBalancer::DB_REPLICA );
		try {
			$row = $db->selectRow(
				'retained_articles',
				'*',
				[
					'ra_deleted_page_namespace' => $title->getNamespace(),
					'ra_deleted_page_title' => $title->getDBkey(),
				],
				__METHOD__
			);
			return $row ? RetainedArticle::newFromRow( $row ) : null;
		} catch ( DBError $ex ) {
			wfDebugLog( __CLASS__, __METHOD__ . ': ' . $ex->getMessage() );
		}
		return null;
	}
}
