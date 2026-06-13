<?php
declare(strict_types=1);
function mineacle_config(): array { $path=__DIR__.'/config.php'; if(!file_exists($path)) $path=__DIR__.'/config.example.php'; $config=require $path; if(!is_array($config)) throw new RuntimeException('Invalid config file'); return $config; }
function mineacle_pdo(): PDO { static $pdo=null; if($pdo instanceof PDO) return $pdo; $c=mineacle_config(); $db=$c['mysql']; $dsn=sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s',$db['host'],(int)$db['port'],$db['database'],$db['charset']??'utf8mb4'); $pdo=new PDO($dsn,$db['username'],$db['password'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false]); return $pdo; }
function h(?string $v): string { return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function quote_ident(string $id): string { if(!preg_match('/^[A-Za-z0-9_]+$/',$id)) throw new InvalidArgumentException('Unsafe SQL identifier: '.$id); return '`'.$id.'`'; }
function col(array $c,string $g,string $n): string { return $c['litebans'][$g][$n]; }
function table_name(array $c,string $n): string { return $c['litebans'][$n]; }
