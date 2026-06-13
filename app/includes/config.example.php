<?php
return [
  'site' => [
    'ip' => 'mineacle.net',
    'discord' => 'https://discord.gg/4xrYFxdSWg',
    'support_email' => 'support@mineacle.net',
    'unban_checkout_url' => 'https://store.mineacle.net/checkout/unban?ban={id}&uuid={uuid}&username={username}',
  ],
  'mysql' => [
    'host' => '127.0.0.1',
    'port' => 3306,
    'database' => 'litebans',
    'username' => 'mineacle_bans_readonly',
    'password' => 'CHANGE_ME',
    'charset' => 'utf8mb4',
  ],
  'litebans' => [
    'bans_table' => 'litebans_bans',
    'history_table' => 'litebans_history',
    'bans' => ['id'=>'id','uuid'=>'uuid','ip'=>'ip','reason'=>'reason','staff_name'=>'banned_by_name','time'=>'time','until'=>'until','active'=>'active','ipban'=>'ipban'],
    'history' => ['uuid'=>'uuid','name'=>'name','date'=>'date'],
  ],
  'payments' => ['temporary_unban_price'=>'$9.99','permanent_unban_price'=>'$19.99'],
  'page' => ['limit'=>50],
];
