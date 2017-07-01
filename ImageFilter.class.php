<?php

class ImageFilter {

	public static function onGetPreferences( $user, &$preferences ) {
		$preferences['displayfiltered'] = array(
			'type' => 'toggle',
			'label-message' => 'tog-displayfiltered',
			'section' => 'rendering/files',
		);

		return true;
	}

	public static function onPageRenderingHash( $hash ) {
		global $wgUser;
		$hash .= '!' . ( $wgUser->getOption( 'displayfiltered' ) ? '1' : '' );
		return true;
	}

	public static function onImageBeforeProduceHTML( &$skin, &$title, &$file, &$frameParams, &$handlerParams, &$time, &$res ) {
		global $wgUser;

		if ( $wgUser->getOption( 'displayfiltered' ) ) {
			return true;
		}

		/* getDescriptionText parses the text (and it screws up the parser), so we
		have to do it manually */
		$revision = Revision::newFromTitle( $title );
		if ( !$revision ) {
			return true;
		}

		$text = $revision->getText();
		if ( !$text ) {
			return true;
		}

		if ( strpos( $text, '__NSFW__' ) === false ) {
			return true;
		} else {
			if ( $frameParams['caption'] !== '' ) {
				$res = $skin->link( $title, $frameParams['caption'] );
			} else {
				$res = $skin->link( $title );
			}
			$res .= wfMessage( 'nsfw-warning' )->text();
			return false;
		}
	}

	public static function onArticleSaveComplete( &$article, &$user, $text, $summary, $minoredit, $watchthis, $sectionanchor, &$flags, $revision, &$status ) {
		$title = $article->getTitle();
		// no change recorded
		if ( $revision == null ) {
			return true;
		}
		if ( $title->getNamespace() == NS_FILE ) {
			$prevRevision = $revision->getPrevious();
			if ( $prevRevision == null ) {
				$prevFlagged = false;
			} else {
				$prevFlagged = strpos( $prevRevision->getRawText(), '__NSFW__' ) !== false;
			}
			$curFlagged = strpos( $revision->getRawText(), '__NSFW__' ) !== false;
			if ( $prevFlagged xor $curFlagged ) {
				# Invalidate cache for all pages using this file
				$update = new HTMLCacheUpdate( $title, 'imagelinks' );
				$update->doUpdate();
				# Invalidate cache for all pages that redirects on this page
				$redirs = $title->getRedirectsHere();
				foreach ( $redirs as $redir ) {
					$update = new HTMLCacheUpdate( $redir, 'imagelinks' );
					$update->doUpdate();
				}
			}
		}
		return true;
	}

}