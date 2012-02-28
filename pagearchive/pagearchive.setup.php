<?php
/* ====================
[BEGIN_COT_EXT]
Code=pagearchive
Name=Page Archive
Description=Groups pages by months like in WordPress blogs
Version=1.1
Date=2012-02-28
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
index=11:radio::1:Enable menu on index
list=12:radio::0:Enable menu in page list
page=13:radio::0:Enable menu on page
cache=14:radio::1:Enable cache
[END_COT_EXT_CONFIG]
==================== */

defined('SED_CODE') or die('Wrong URL');
?>
