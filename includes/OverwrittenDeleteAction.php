<?php

namespace MediaWiki\Extension\RetainedArticles;

use DeleteAction;

class OverwrittenDeleteAction extends DeleteAction {

	/**
	 * @inheritDoc
	 */
	protected function getFormFields(): array {
		$fields = parent::getFormFields();
		$retainedArticle = [
			'type' => 'title',
			'label-message' => 'retained-articles-title-label',
			'name' => 'retained-article',
			'required' => false,
		];

		$result = [];
		foreach ( $fields as $key => $field ) {
			if ( $key === 'ConfirmB' ) {
				$result['RetainedArticle'] = $retainedArticle;
			}
			$result[$key] = $field;
		}
		if ( !isset( $result['RetainedArticle'] ) ) {
			$result['RetainedArticle'] = $retainedArticle;
		}

		return $result;
	}
}
