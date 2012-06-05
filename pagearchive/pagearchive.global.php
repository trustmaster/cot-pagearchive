<?php
/* ====================
[BEGIN_COT_EXT]
Hooks=global
Tags=index.tpl:{PHP|pagearchive};page.list.tpl:{PHP|pagearchive};page.tpl:{PHP|pagearchive}
[END_COT_EXT]
==================== */

defined('COT_CODE') or die('Wrong URL');

require_once cot_incfile('page', 'module');
require_once cot_langfile('pagearchive', 'plug');

/**
 * Page archive widget callback. Use it in templates via {PHP|pagearchive}
 * @return string Widget HTML
 */
function pagearchive()
{
	global $cache, $cfg, $db, $db_pages, $L, $pa_archive_html, $sys;
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

		if ($cfg['plugin']['pagearchive']['curyear'])
		{
			$pa_where .= " AND YEAR(FROM_UNIXTIME(`{$cfg['plugin']['pagearchive']['field']}`)) = YEAR(NOW())";
		}

		$pa_limit = $cfg['plugin']['pagearchive']['months'] > 0 ? 'LIMIT ' . (int) $cfg['plugin']['pagearchive']['months'] : '';

		$tpl_file = $cfg['plugin']['pagearchive']['yearly'] ? cot_tplfile('pagearchive.yearly', 'plug') : cot_tplfile('pagearchive', 'plug');
		$pa_t = new XTemplate($tpl_file);

		// Run the query and render the template
		$pa_res = $db->query("SELECT
				MONTH(FROM_UNIXTIME(`{$cfg['plugin']['pagearchive']['field']}`)) AS `month`,
				MONTHNAME(FROM_UNIXTIME(`{$cfg['plugin']['pagearchive']['field']}`)) AS `month_name`,
				YEAR(FROM_UNIXTIME(`{$cfg['plugin']['pagearchive']['field']}`)) AS `year`,
				COUNT(*) AS `count`
			FROM $db_pages
			WHERE $pa_where
			GROUP BY `year`, `month`
			ORDER BY `{$cfg['plugin']['pagearchive']['field']}` {$cfg['plugin']['pagearchive']['sort']}
			$pa_limit");
		$prev_year = '';
		$row_block = $cfg['plugin']['pagearchive']['yearly'] ? 'MAIN.YEAR.ROW' : 'MAIN.ROW';
		foreach ($pa_res->fetchAll() as $pa_row)
		{
			if ($cfg['plugin']['pagearchive']['yearly'] && $pa_row['year'] != $prev_year && $prev_year != '')
			{
				// Render prev year group
				$pa_t->assign(array(
					'YEAR_YEAR' => $prev_year,
					'YEAR_URL' => cot_url('page', 'c='.$cfg['plugin']['pagearchive']['cat'].'&year='.$prev_year)
				));
				$pa_t->parse('MAIN.YEAR');
				$prev_year = $pa_row['year'];
			}
			$pa_t->assign(array(
				'ROW_MONTH' => $pa_row['month'],
				'ROW_MONTH_NAME' => isset($L['pagearch_'.$pa_row['month_name']]) ? $L['pagearch_'.$pa_row['month_name']] : $L[$pa_row['month_name']],
				'ROW_YEAR' => $pa_row['year'],
				'ROW_COUNT' => $pa_row['count'],
				'ROW_URL' => cot_url('page', 'c='.$cfg['plugin']['pagearchive']['cat'].'&year='.$pa_row['year'].'&month='.$pa_row['month'])
			));
			$pa_t->parse($row_block);
		}

		if ($cfg['plugin']['pagearchive']['yearly'] && $prev_year != '')
		{
			// Render the last year group
			$pa_t->assign(array(
				'YEAR_YEAR' => $prev_year,
				'YEAR_URL' => cot_url('page', 'c='.$cfg['plugin']['pagearchive']['cat'].'&year='.$prev_year)
			));
			$pa_t->parse('MAIN.YEAR');
		}

		$pa_t->parse();

		$pa_archive_html = $pa_t->text();

		if ($cache && $cfg['plugin']['pagearchive']['cache'])
		{
			$cache->db->store('pa_archive_html', $pa_archive_html, 'system', 1200);
		}
	}

	return $pa_archive_html;
}

?>
