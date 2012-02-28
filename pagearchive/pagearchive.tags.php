<?php
/* ====================
[BEGIN_COT_EXT]
Hooks=index.tags,page.list.tags,page.tags
Tags=index.tpl:{INDEX_PAGEARCHIVE};page.list.tpl:{LIST_PAGEARCHIVE};page.tpl:{PAGE_PAGEARCHIVE}
[END_COT_EXT]
==================== */

defined('COT_CODE') or die('Wrong URL');

$caller = cot_get_caller();

if ($caller == 'index' && $cfg['plugin']['pagearchive']['index']
	|| $caller == 'page.list' && $cfg['plugin']['pagearchive']['list']
	|| $caller == 'page.main' && $cfg['plugin']['pagearchive']['page'])
{
	require_once cot_incfile('page', 'module');

	$pa_tag = 'INDEX_PAGEARCHIVE';
	if ($caller == 'page.list')
	{
		$pa_tag = 'LIST_PAGEARCHIVE';
	}
	elseif ($caller == 'page.main')
	{
		$pa_tag = 'PAGE_PAGEARCHIVE';
	}
	
	if (!$cache || !$cfg['plugin']['pagearchive']['cache'] || !$pa_archive_html)
	{

		// Selection filter by category
		$pa_cats = preg_split('#,\s*#', $cfg['plugin']['pagearchive']['cats']);

		$pa_catsub = array();
		foreach ($pa_cats as $pa_cat)
		{
			$pa_catsub = array_merge($pa_catsub, cot_structure_children('page', $pa_cat));
		}

		$pa_where = "page_state = 0 AND page_cat <> 'system' AND page_begin <= {$sys['now']} AND (page_expire = 0 OR page_expire > {$sys['now']}) AND page_cat IN ('" . implode("','", $pa_catsub) . "')";

		$pa_t = new XTemplate(cot_tplfile('pagearchive', 'plug'));

		// Run the query and render the template
		$pa_res = $db->query("SELECT
				MONTH(FROM_UNIXTIME(`{$cfg['plugin']['pagearchive']['field']}`)) AS `month`,
				MONTHNAME(FROM_UNIXTIME(`{$cfg['plugin']['pagearchive']['field']}`)) AS `month_name`,
				YEAR(FROM_UNIXTIME(`{$cfg['plugin']['pagearchive']['field']}`)) AS `year`,
				COUNT(*) AS `count`
			FROM $db_pages
			WHERE $pa_where
			GROUP BY `year`, `month`
			ORDER BY `{$cfg['plugin']['pagearchive']['field']}` {$cfg['plugin']['pagearchive']['sort']}");
		foreach ($pa_res->fetchAll() as $pa_row)
		{
			$pa_t->assign(array(
				'ROW_MONTH' => $pa_row['month'],
				'ROW_MONTH_NAME' => $L[$pa_row['month_name']],
				'ROW_YEAR' => $pa_row['year'],
				'ROW_COUNT' => $pa_row['count'],
				'ROW_URL' => cot_url('page', 'c='.$cfg['plugin']['pagearchive']['cat'].'&year='.$pa_row['year'].'&month='.$pa_row['month'])
			));
			$pa_t->parse('MAIN.ROW');
		}

		$pa_t->parse();
		
		$pa_archive_html = $pa_t->text();
		
		if ($cache && $cfg['plugin']['pagearchive']['cache'])
		{
			$cache->db->store('pa_archive_html', $pa_archive_html, 'system', 1200);
		}
	}

	// Put it into a single tag
	$t->assign($pa_tag, $pa_archive_html);
}

?>
