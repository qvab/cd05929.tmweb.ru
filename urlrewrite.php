<?php
$arUrlRewrite=array (
  7 => 
  array (
    'CONDITION' => '#^/online/([\\.\\-0-9a-zA-Z]+)(/?)([^/]*)#',
    'RULE' => 'alias=$1',
    'ID' => 'bitrix:im.router',
    'PATH' => '/desktop_app/router.php',
    'SORT' => 100,
  ),
  4 => 
  array (
    'CONDITION' => '#^/transport/autopark/(([0-9]+)|add)/#',
    'RULE' => 'WCODE=$1',
    'ID' => '',
    'PATH' => '/transport/autopark/detail.php',
    'SORT' => 100,
  ),
  5 => 
  array (
    'CONDITION' => '#^/farmer/warehouses/(([0-9]+)|add)/#',
    'RULE' => 'WCODE=$1',
    'ID' => '',
    'PATH' => '/farmer/warehouses/detail.php',
    'SORT' => 100,
  ),
  6 => 
  array (
    'CONDITION' => '#^/client/warehouses/(([0-9]+)|add)/#',
    'RULE' => 'WCODE=$1',
    'ID' => '',
    'PATH' => '/client/warehouses/detail.php',
    'SORT' => 100,
  ),
  11 => 
  array (
    'CONDITION' => '#^/client_agent/deals/([0-9]+)/#',
    'RULE' => 'ELEMENT_ID=$1&TRASH=$2',
    'ID' => '',
    'PATH' => '/client_agent/deals/detail.php',
    'SORT' => 100,
  ),
  3 => 
  array (
    'CONDITION' => '#^/transport/deals/([0-9]+)/#',
    'RULE' => 'ELEMENT_ID=$1&TRASH=$2',
    'ID' => '',
    'PATH' => '/transport/deals/detail.php',
    'SORT' => 100,
  ),
  2 => 
  array (
    'CONDITION' => '#^/partner/deals/([0-9]+)/#',
    'RULE' => 'ELEMENT_ID=$1&TRASH=$2',
    'ID' => '',
    'PATH' => '/partner/deals/detail.php',
    'SORT' => 100,
  ),
  0 => 
  array (
    'CONDITION' => '#^/client/deals/([0-9]+)/#',
    'RULE' => 'ELEMENT_ID=$1&TRASH=$2',
    'ID' => '',
    'PATH' => '/client/deals/detail.php',
    'SORT' => 100,
  ),
  1 => 
  array (
    'CONDITION' => '#^/farmer/deals/([0-9]+)/#',
    'RULE' => 'ELEMENT_ID=$1&TRASH=$2',
    'ID' => '',
    'PATH' => '/farmer/deals/detail.php',
    'SORT' => 100,
  ),
  10 => 
  array (
    'CONDITION' => '#^/agent/deals/([0-9]+)/#',
    'RULE' => 'ELEMENT_ID=$1&TRASH=$2',
    'ID' => '',
    'PATH' => '/agent/deals/detail.php',
    'SORT' => 100,
  ),
  8 => 
  array (
    'CONDITION' => '#^/online/(/?)([^/]*)#',
    'RULE' => '',
    'ID' => 'bitrix:im.router',
    'PATH' => '/desktop_app/router.php',
    'SORT' => 100,
  ),
  9 => 
  array (
    'CONDITION' => '#^/news/#',
    'RULE' => '',
    'ID' => 'bitrix:news',
    'PATH' => '/news/index.php',
    'SORT' => 100,
  ),
);
