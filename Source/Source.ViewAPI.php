<?php
# Copyright (C) 2008-2009 John Reese, LeetCode.net
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU Affero General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU Affero General Public License for more details.

/**
 * Display a list of changeset objects in tabular format.
 * Assumes that a table with four columns has already been defined.
 * @param array Changeset objects
 * @param array Repository objects
 */
function Source_View_Changesets( $p_changesets, $p_repos=null, $p_show_repos=true ) {
	if ( !is_array( $p_changesets ) ) {
		return;
	}

	if ( is_null( $p_repos ) || !is_array( $p_repos ) ) {
		$t_repos = SourceRepo::load_by_changesets( $p_changesets );
	} else {
		$t_repos = $p_repos;
	}

	$t_use_porting = config_get( 'plugin_Source_enable_porting' );

	foreach( $p_changesets as $t_changeset ) {
		$t_repo = $t_repos[ $t_changeset->repo_id ];
		$t_changeset->load_files();

		$t_author = Source_View_Author( $t_changeset, false );
		$t_committer = Source_View_Committer( $t_changeset, false );
		?>

<tr class="row-1">
<td class="category" width="25%" rowspan="<?php echo count( $t_changeset->files ) + 1 ?>">
	<?php echo string_display(
		( $p_show_repos ? $t_repo->name . ': ' : '' ) .
		event_signal( 'EVENT_SOURCE_SHOW_CHANGESET', array( $t_repo, $t_changeset ) )
		) ?>
	<br/><span class="small"><?php echo plugin_lang_get( 'timestamp', 'Source' ), ': ', string_display_line( $t_changeset->timestamp ) ?></span>
	<br/><span class="small"><?php echo plugin_lang_get( 'author', 'Source' ), ': ', $t_author ?></span>
	<?php if ( $t_committer && $t_committer != $t_author ) { ?><br/><span class="small"><?php echo plugin_lang_get( 'committer', 'Source' ), ': ', $t_committer ?></span><?php } ?>
	<?php if ( $t_changeset->committer ) { ?><br/><span class="small"><?php echo plugin_lang_get( 'committer', 'Source' ), ': ', string_display_line( $t_changeset->committer ) ?></span><?php } ?>
	<?php if ( $t_use_porting ) { ?>
	<br/><span class="small"><?php echo plugin_lang_get( 'ported', 'Source' ), ': ',
		( $t_changeset->ported ? string_display_line( $t_changeset->ported ) :
			( is_null( $t_changeset->ported ) ? plugin_lang_get( 'pending', 'Source' ) : plugin_lang_get( 'na', 'Source' ) ) ) ?></span>
	<?php } ?>
	<br/><span class="small-links">
		<?php
		print_bracket_link( plugin_page( 'view', false, 'Source' ) . '&id=' . $t_changeset->id, plugin_lang_get( 'details', 'Source' ) );
		if ( $t_url = event_signal( 'EVENT_SOURCE_URL_CHANGESET', array( $t_repo, $t_changeset ) ) ) {
			print_bracket_link( $t_url, plugin_lang_get( 'diff', 'Source' ) );
		}
		?>
</td>
<td colspan="3"><?php echo string_display_links( $t_changeset->message ) ?></td>
</tr>

		<?php foreach ( $t_changeset->files as $t_file ) { ?>
<tr class="row-2">
<td class="small mono" colspan="2"><?php echo string_display_line( event_signal( 'EVENT_SOURCE_SHOW_FILE', array( $t_repo, $t_changeset, $t_file ) ) ) ?></td>
<td class="center" width="12%"><span class="small-links">
		<?php
		if ( $t_url = event_signal( 'EVENT_SOURCE_URL_FILE_DIFF', array( $t_repo, $t_changeset, $t_file ) ) ) {
			print_bracket_link( $t_url, plugin_lang_get( 'diff', 'Source' ) );
		}
		if ( $t_url = event_signal( 'EVENT_SOURCE_URL_FILE', array( $t_repo, $t_changeset, $t_file ) ) ) {
			print_bracket_link( $t_url, plugin_lang_get( 'file', 'Source' ) );
		}
		?>
</span></td>
</tr>
		<?php } ?>
<tr><td class="spacer"></td></tr>
		<?php
	}
}

/**
 * Display the author information for a changeset.
 * @param object Changeset object
 * @param boolean Echo information
 */
function Source_View_Author( $p_changeset, $p_echo=true ) {
	$t_author_name = !is_blank( $p_changeset->author ) ? string_display_line( $p_changeset->author ) : false;
	$t_author_email = !is_blank( $p_changeset->author_email ) ? string_display_line( $p_changeset->author_email ) : false;
	$t_author_username = $p_changeset->user_id > 0 ? string_display_line( user_get_name( $p_changeset->user_id ) ) : false;

	if ( $t_author_username ) {
		$t_output =  $t_author_username;

	} else if ( $t_author_name ) {
		$t_output =  $t_author_name;

	} else {
		$t_output =  $t_author_email;
	}

	if ( $p_echo ) {
		echo $t_output;
	} else {
		return $t_output;
	}
}

/**
 * Display the committer information for a changeset.
 * @param object Changeset object
 * @param boolean Echo information
 */
function Source_View_Committer( $p_changeset, $p_echo=true ) {
	$t_committer_name = !is_blank( $p_changeset->committer ) ? string_display_line( $p_changeset->committer ) : false;
	$t_committer_email = !is_blank( $p_changeset->committer_email ) ? string_display_line( $p_changeset->committer_email ) : false;
	$t_committer_username = $p_changeset->committer_id > 0 ? string_display_line( user_get_name( $p_changeset->committer_id ) ) : false;

	if ( $t_committer_username ) {
		$t_output =  $t_committer_username;

	} else if ( $t_committer_name ) {
		$t_output =  $t_committer_name;

	} else {
		$t_output =  $t_committer_email;
	}

	if ( $p_echo ) {
		echo $t_output;
	} else {
		return $t_output;
	}
}

