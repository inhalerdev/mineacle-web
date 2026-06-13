<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__.'/../includes/bans-lib.php';
try{ $c=mineacle_config(); $search=trim((string)($_GET['search']??'')); $limit=(int)($c['page']['limit']??50); echo json_encode(['success'=>true,'bans'=>fetch_litebans_bans($search,$limit)],JSON_UNESCAPED_SLASHES); }
catch(Throwable $e){ http_response_code(500); echo json_encode(['success'=>false,'error'=>'Unable to load LiteBans data. Check includes/config.php and database permissions.','debug'=>$e->getMessage()],JSON_UNESCAPED_SLASHES); }
