<?php
/* ====================
[BEGIN_COT_EXT]
Code=pagearchive
Name=Page Archive
Description=Groups pages by months like in WordPress blogs
Version=1.7
Date=2013-02-28
Author=Trustmaster
Copyright=&copy; Vladimir Sibirov and Seditio.By 2012-2013
Notes=Create a pseudo-category and set it in plugin config after installation
SQL=
Auth_guests=R
Lock_guests=W12345A
Auth_members=R
Lock_members=W12345A
Requires_modules=page
[END_COT_EXT]

[BEGIN_COT_EXT_CONFIG]
cats=01:string::news:Source category codes (root), separated with semicolons
cat=02:string::archive:Pseudo-category for archive (will contain no pages)
field=03:string::page_date:Field which contains page date
sort=04:select:desc,asc:desc:Menu sort order
mode=05:select:monthly,yearly,combined:monthly:Display mode
curyear=06:radio::0:Only for current year
limit=07:string::0:Number of last months/years to show (0 - all time)
cache=14:radio::1:Enable cache
blacklist=21:string:::Category black list, separated with semicolons
whitelist=21:string:::Category white list, separated with semicolons
[END_COT_EXT_CONFIG]
==================== */

defined('SED_CODE') or die('Wrong URL');
