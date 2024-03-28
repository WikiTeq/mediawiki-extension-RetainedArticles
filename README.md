# RetainedArticles MediaWiki Extension

## Introduction
The `RetainedArticles` extension for MediaWiki enhances the article deletion process by enabling redirects to be established for deleted articles, maintaining the article's presence within the wiki through redirection.

## Features
- Option to specify a redirect target during the deletion process.
- Seamless creation of redirects from deleted article titles to specified targets.
- Easy integration with the standard MediaWiki deletion interface.

## Installation
1. Clone this repository into your MediaWiki `extensions/` directory.
2. Include `wfLoadExtension( 'RetainedArticles' );` in your `LocalSettings.php`.

## Usage
When deleting an article, you will be presented with an option to specify a "Retained Article" target. The URL of the deleted article will then redirect visitors to this target article.

## Contributing
Your contributions are welcome! Please fork this repository, make your changes, and submit a pull request for review.
