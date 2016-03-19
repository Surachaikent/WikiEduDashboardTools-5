<?php

require_once 'common.php';

function make_revisions_query() {
	global $end, $namespaces, $sql_rev_ids, $sql_user_ids, $start, $tags;

	if(isset($sql_user_ids) && isset($namespaces)) {
	  $query = "
		SELECT g.page_id, g.page_title, g.page_namespace,
		  c.rev_id, c.rev_timestamp, c.rev_user_text, c.rev_user,
		  case when ct.ct_tag IS NULL then 'false' else 'true' end as system,
		  case when c.rev_parent_id = 0 then 'true' else 'false' end as new_article,
		  CAST(c.rev_len AS SIGNED) - CAST(IFNULL(p.rev_len, 0) AS SIGNED) AS byte_change
		FROM revision_userindex c
		LEFT JOIN revision_userindex p ON p.rev_id = c.rev_parent_id
		INNER JOIN page g ON g.page_id = c.rev_page
		LEFT JOIN change_tag ct
		  ON ct.ct_rev_id = c.rev_id
		  AND ct.ct_tag IN ($tags)
		WHERE g.page_namespace IN ($namespaces)
		AND c.rev_user IN ($sql_user_ids)
		AND c.rev_timestamp BETWEEN $start AND $end
	  ";
	} else if(isset($sql_rev_ids)) {
	  $query = "
		SELECT rev_id, rev_page
		FROM revision_userindex
		WHERE rev_id IN ($sql_rev_ids)
	  ";
	}
	return $query;
}

if (php_sapi_name() !== 'cli' ) {
	echo_query_results(make_revisions_query());
}
