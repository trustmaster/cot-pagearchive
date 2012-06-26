<?php
/* ====================
[BEGIN_COT_EXT]
Hooks=global
Tags=index.tpl:{PHP|pagearchive('news')}
[END_COT_EXT]
==================== */

defined('COT_CODE') or die('Wrong URL');

require_once cot_incfile('page', 'module');
require_once cot_langfile('pagearchive', 'plug');

/**
 * Page archive widget callback. Use it in templates via {PHP|pagearchive}
 * @param string  $cats      Category codes, sepearated with semicolons
 * @param string  $mode      Display mode: 'monthly', 'yearly' and 'combined'
 * @param string  $tpl       Template code
 * @param boolean $curyear   Only for current year
 * @param integer $limit     Number of years/months to show
 * @param string  $sort      Sort menu items: 'asc' or 'desc'
 * @param array   $blacklist Category black list, sepearated with semicolons
 * @param array   $whitelist Category white list, sepearated with semicolons
 * @return string Widget HTML
 */
function pagearchive($cats = NULL, $mode = NULL, $tpl = NULL, $curyear = NULL, $limit = NULL, $sort = NULL, $blacklist = NULL, $whitelist = NULL)
{
	global $cache, $cfg, $db, $db_pages, $L, $pa_archive_html, $sys;

	if (!$cache || !$cfg['plugin']['pagearchive']['cache'] || !$pa_archive_html)
	{
		// Restore default settings from config
		if (!$cats)
			$cats = $cfg['plugin']['pagearchive']['cats'];
		if (is_null($mode))
			$mode = $cfg['plugin']['pagearchive']['mode'];
		if (is_null($curyear))
			$curyear = $cfg['plugin']['pagearchive']['curyear'];
		if (is_null($limit))
			$limit = $cfg['plugin']['pagearchive']['limit'];
		if (!in_array($sort, array('asc', 'desc')))
			$sort = $cfg['plugin']['pagearchive']['sort'];
		if (is_null($blacklist))
			$blacklist = $cfg['plugin']['pagearchive']['blacklist'];
		if (is_null($whitelist))
			$whitelist = $cfg['plugin']['pagearchive']['whitelist'];

		$field = $cfg['plugin']['pagearchive']['field'];

		// Compile lists
		if (!empty($blacklist))
		{
			$bl = explode(';', $blacklist);
		}

		if (!empty($whitelist))
		{
			$wl = explode(';', $whitelist);
		}

		// Selection filter by category
		$pa_cats = preg_split('#;\s*#', $cats);
		$cats = array();

		foreach ($pa_cats as $cat)
		{
			$cats = array_merge($cats, cot_structure_children('page', $cat, TRUE));
		}

		if (count($cats) > 0)
		{
			if (!empty($blacklist))
			{
				$cats = array_diff($cats, $bl);
			}

			if (!empty($whitelist))
			{
				$cats = array_intersect($cats, $wl);
			}

			$where_cat = "AND page_cat IN ('" . implode("','", $cats) . "')";
		}

		$pa_where = "page_state = 0 AND page_cat <> 'system' AND page_begin <= {$sys['now']} AND (page_expire = 0 OR page_expire > {$sys['now']}) $where_cat";

		// Filter items by year
		if ($curyear)
		{
			$pa_where .= " AND YEAR(FROM_UNIXTIME(`{$field}`)) = YEAR(NOW())";
		}

		// Grouping depends on mode
		if ($mode == 'yearly')
		{
			$pa_groupby = "`year`";
		}
		else
		{
			$pa_groupby = "`year`, `month`";
		}

		// Apply count limit
		$pa_limit = $limit > 0 ? 'LIMIT ' . (int) $limit : '';

		// Select the template
		if (is_null($tpl))
		{
			$tpl_file = $mode == 'monthly' ? cot_tplfile('pagearchive', 'plug') : cot_tplfile("pagearchive.$mode", 'plug');
		}
		else
		{
			$tpl_file = cot_tplfile($tpl, 'plug');
		}
		$pa_t = new XTemplate($tpl_file);

		// Run the query and render the template
		$pa_res = $db->query("SELECT
				MONTH(FROM_UNIXTIME(`{$field}`)) AS `month`,
				MONTHNAME(FROM_UNIXTIME(`{$field}`)) AS `month_name`,
				YEAR(FROM_UNIXTIME(`{$field}`)) AS `year`,
				COUNT(*) AS `count`
			FROM $db_pages
			WHERE $pa_where
			GROUP BY $pa_groupby
			ORDER BY `{$field}` DESC
			$pa_limit");
		$prev_year = '';
		$row_block = $mode == 'monthly' ? 'MAIN.ROW' : 'MAIN.YEAR.ROW';
		$pa_rowset = $pa_res->fetchAll();
		if ($sort != 'desc')
		{
			// Reorder rows manually because LIMIT must apply on DESC only
			usort($pa_rowset, create_function('$a,$b', 'if ($a['.$field.'] == $b['.$field.']) return 0; return ($a['.$field.'] > $b['.$field.']) ? 1 : -1;'));
		}
		foreach ($pa_rowset as $pa_row)
		{
			if ($mode != 'monthly' && $pa_row['year'] != $prev_year && $prev_year != '')
			{
				// Render prev year group
				$pa_t->assign(array(
					'YEAR_YEAR' => $prev_year,
					'YEAR_URL' => cot_url('page', 'c='.$cfg['plugin']['pagearchive']['cat'].'&year='.$prev_year),
					'YEAR_COUNT' => $prev_count
				));
				$pa_t->parse('MAIN.YEAR');
			}
			if ($mode != 'yearly')
			{
				$pa_t->assign(array(
					'ROW_MONTH' => $pa_row['month'],
					'ROW_MONTH_NAME' => isset($L['pagearch_'.$pa_row['month_name']]) ? $L['pagearch_'.$pa_row['month_name']] : $L[$pa_row['month_name']],
					'ROW_YEAR' => $pa_row['year'],
					'ROW_COUNT' => $pa_row['count'],
					'ROW_URL' => cot_url('page', 'c='.$cfg['plugin']['pagearchive']['cat'].'&year='.$pa_row['year'].'&month='.$pa_row['month'])
				));
				$pa_t->parse($row_block);
			}
			$prev_year = $pa_row['year'];
			$prev_count = $pa_row['count'];
		}

		if ($mode != 'monthly' && $prev_year != '')
		{
			// Render the last year group
			$pa_t->assign(array(
				'YEAR_YEAR' => $prev_year,
				'YEAR_URL' => cot_url('page', 'c='.$cfg['plugin']['pagearchive']['cat'].'&year='.$prev_year),
				'YEAR_COUNT' => $prev_count
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
