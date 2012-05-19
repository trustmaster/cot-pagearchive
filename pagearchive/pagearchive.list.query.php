<?php
/* ====================
[BEGIN_COT_EXT]
Hooks=page.list.query
[END_COT_EXT]
==================== */

defined('COT_CODE') or die('Wrong URL');

// Modifies list query for archive category

if ($c == $cfg['plugin']['pagearchive']['cat'])
{
	require_once cot_langfile('pagearchive', 'plug');
	// Selection filter by category
	$pa_cats = preg_split('#,\s*#', $cfg['plugin']['pagearchive']['cats']);

	$pa_catsub = array();
	foreach ($pa_cats as $pa_cat)
	{
		$pa_catsub = array_merge($pa_catsub, cot_structure_children('page', $pa_cat));
	}

	$where['cat'] = "page_cat IN ('" . implode("','", $pa_catsub) . "')";
	
	// Import date parameters
	$year = cot_import('year', 'G', 'INT');
	$month = cot_import('month', 'G', 'INT');
	if (empty($year) || $year < 1970 || $year > date('Y'))
	{
		cot_die();
	}
	if (!empty($month) && ($month < 1 || $month > 12))
	{
		cot_die();
	}
	
	// Generate selection condition
	$pa_low = empty($month) ? cot_mktime(0, 0, 0, 1, 1, $year) : cot_mktime(0, 0, 0, $month, 1, $year);
	$pa_high = empty($month) ? cot_mktime(0, 0, 0, 1, 1, $year + 1) : cot_mktime(0, 0, 0, $month + 1, 1, $year);

	$where['archive'] = "`{$cfg['plugin']['pagearchive']['field']}` BETWEEN $pa_low AND $pa_high";
	
	// Generate relevant title and breadcrumb
	$month_name = isset($L['pagearch_'.date('F', $pa_low)]) ? $L['pagearch_'.date('F', $pa_low)] : $L[date('F', $pa_low)];
	$catpath = empty($month) ? $cat['title'] . ' ' . $cfg['separator'] . ' ' . $year : $cat['title'] . ' ' . $cfg['separator'] . ' ' . $month_name . ' ' . $year;
	$cat['title'] = empty($month) ? $year . ' - ' . $cat['title'] : $month_name . ' ' . $year . ' - ' . $cat['title'];
}

?>
