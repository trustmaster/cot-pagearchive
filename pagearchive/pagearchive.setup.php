<?php
/* ====================
[BEGIN_COT_EXT]
Code=pagearchive
Name=Page Archive
Description=Groups pages by months like in WordPress blogs
Version=1.5
Date=2012-06-05
Author=Trustmaster
Copyright=&copy; Vladimir Sibirov 2012
Notes=Create a pseudo-category and set it in plugin config after installation
SQL=
Auth_guests=R
Lock_guests=W12345A
Auth_members=R
Lock_members=W12345A
Requires_modules=page
[END_COT_EXT]

[BEGIN_COT_EXT_CONFIG]
cats=01:string::news:Source category codes (root), comma separated
cat=02:string::archive:Pseudo-category for archive (will contain no pages)
field=03:string::page_date:Field which contains page date
sort=04:select:desc,asc:desc:Menu sort order
yearly=05:radio::0:Group the list by year
curyear=06:radio::0:Only for current year
months=07:string::0:Number of last months to show (0 - all time)
cache=14:radio::1:Enable cache
[END_COT_EXT_CONFIG]
==================== */

defined('SED_CODE') or die('Wrong URL');
?>
