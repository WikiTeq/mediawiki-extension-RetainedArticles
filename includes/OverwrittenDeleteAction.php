<?php

namespace MediaWiki\Extension\RetainedArticles;

use DeleteAction;
use HTMLForm;
use HTMLTitleTextField;

class OverwrittenDeleteAction extends DeleteAction {

	/**
	 * @inheritDoc
	 */
//	protected function showForm( string $reason ): void {
//		parent::showForm( $reason );
	public function show() {
		parent::show();
		$offset = $this->getOffsetOfSubmitButtonFieldLayout();
		if ( $offset ) {
			$outputPage = $this->getOutput();

			$form = HTMLForm::factory( 'ooui', [], $this->getContext() );
			$field = HTMLForm::loadInputFromParameters(
				'retainedArticle',
				[
					'class' => HTMLTitleTextField::class,
					'label-message' => 'retained-articles-title-label',
					'name' => 'retained-article',
					'required' => false,
				],
				$form
			);
			$retainedArticle = Tools::getRetainedArticle( $this->getTitle() );
			$retainedTitle = $retainedArticle ? $retainedArticle->getRetainedTitle() : null;
			$value = $retainedTitle ? $retainedTitle->getFullText() : '';
			$fieldHtml = $field->getOOUI( $value );
			$outputBodyText = $outputPage->mBodytext;
			// Adds required modules
			$form->prepareForm()->displayForm( false );
			$outputPage->mBodytext = substr_replace( $outputBodyText, $fieldHtml, $offset, 0 );
		}
	}

	/**
	 * @param string $string
	 * @param int|null $offset
	 * @return bool
	 */
	private static function hasSubmitButton( string $string, ?int $offset = 0 ): bool {
		$pattern = <<<EOD
/<button[^>]*type=['"]submit['"]/
EOD;
		return preg_match( $pattern, $string, $m, 0, $offset ) !== false;
	}

	/**
	 * @return int|null
	 */
	private function getOffsetOfSubmitButtonFieldLayout(): ?int {
		$outputPage = $this->getOutput();
		$outputHtml = $outputPage->mBodytext;
		$pattern = <<<EOD
/<div[^>]*class=['"][^'"]*oo-ui-fieldLayout[ '"]/
EOD;
		preg_match_all( $pattern, $outputHtml, $matches, PREG_OFFSET_CAPTURE );
		$offset = null;
		while ( $matches && $matches[0] ) {
			$m = array_pop( $matches[0] );
			if ( self::hasSubmitButton( $outputHtml, $m[1] ) ) {
				$offset = $m[1];
				break;
			}
		}
		return $offset;
	}
}
