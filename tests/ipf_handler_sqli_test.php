<?php
/**
 * Standalone security regression test for icms_ipf_Handler get/insert(update)/delete.
 * Object identifiers must be bound as SQL parameters, never interpolated.
 *
 * Run: php tests/ipf_handler_sqli_test.php   (requires pdo_sqlite)
 */
error_reporting(E_ALL);
define('ICMS_ROOT_PATH', dirname(__DIR__) . '/htdocs');
define('XOBJ_DTYPE_INT', 1);
define('XOBJ_DTYPE_TXTBOX', 2);
define('XOBJ_DTYPE_URLLINK', 3);
define('XOBJ_DTYPE_FILE', 4);
define('_CORE_DB_NOTALLOWEDINGET', 'not allowed');

$lib = ICMS_ROOT_PATH . '/libraries/icms/';
foreach (array('db/criteria/Element', 'db/criteria/Item', 'db/criteria/Compo', 'db/legacy/IDatabase',
		'db/legacy/Database', 'db/legacy/PdoDatabase', 'core/ObjectHandler', 'ipf/Handler') as $f) {
	require_once $lib . $f . '.php';
}

class icms_db_legacy_mysql_Utility {
	public static function checkSQL($sql) {
		return true;
	}
}

class icms {
	public static function handler($name) {
		return new class {
			public function getByDirname($d) {
				return null;
			}
		};
	}
}

class TestObject {
	public $vars = array();
	public $handler;
	public $cleanVars = array();
	public $handlerRef;
	public $seoEnabled = false;
	public $isNew = true;
	public $errors = array();

	public function __construct($handler, $keys) {
		$this->handler = $handler;
		foreach ($keys as $k => $type) {
			$this->vars[$k] = array('data_type' => $type, 'value' => null, 'persistent' => true);
		}
		$this->vars['title'] = array('data_type' => XOBJ_DTYPE_TXTBOX, 'value' => null, 'persistent' => true);
	}
	public function assignVars($row) {
		foreach ($row as $k => $v) {
			$this->vars[$k]['value'] = $v;
		}
	}
	public function assignVar($k, $v) {
	}
	public function setVar($k, $v) {
		$this->vars[$k]['value'] = $v;
		$this->cleanVars[$k] = $v;
	}
	public function getVar($k, $format = 's') {
		return $this->vars[$k]['value'];
	}
	public function cleanVars() {
		return true;
	}
	public function setNew() {
		$this->isNew = true;
	}
	public function unsetNew() {
		$this->isNew = false;
	}
	public function isNew() {
		return $this->isNew;
	}
	public function setErrors($e) {
		$this->errors[] = $e;
	}
	public function id() {
		return $this->getVar($this->handler->keyName);
	}
}

class TestHandler extends icms_ipf_Handler {
	public $keys;
	public function __construct($db, $table, $keyName, $keys) {
		$this->db = $db;
		$this->table = $table;
		$this->keyName = $keyName;
		$this->keys = $keys;
		$this->_itemname = 'item';
		$this->className = 'TestObject';
	}
	public function &create($isNew = true) {
		$o = new TestObject($this, $this->keys);
		if ($isNew) {
			$o->setNew();
		} else {
			$o->unsetNew();
		}
		return $o;
	}
}

$pdo = new PDO('sqlite::memory:');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
$db = new icms_db_legacy_PdoDatabase($pdo, true);

$fail = 0;
function check($cond, $msg) {
	global $fail;
	echo ($cond ? 'PASS' : 'FAIL') . ': ' . $msg . "\n";
	if (!$cond) {
		$fail++;
	}
}
function titles($pdo, $table) {
	return $pdo->query("SELECT title FROM $table ORDER BY title")->fetchAll(PDO::FETCH_COLUMN);
}

$payloads = array(
	"1 OR 1=1", "1' OR '1'='1", '1" OR "1"="1', "x`; DROP TABLE t; --", "' UNION SELECT 1,2 --",
	"a' OR ''='", "1; DELETE FROM t", "\\", "%", "SELECT", "0x31", "1/**/OR/**/1=1", "' OR 1=1 #", '?', ':id',
);

// String primary key
$pdo->exec('CREATE TABLE s (k TEXT PRIMARY KEY, title TEXT)');
$h = new TestHandler($db, 's', 'k', array('k' => XOBJ_DTYPE_TXTBOX));
$ids = array_merge(array('plain', "o'brien", 'q"uote', 'back`tick'), $payloads);
foreach ($ids as $id) {
	$o = $h->create();
	$o->setVar('k', $id);
	$o->setVar('title', 'T:' . $id);
	check($h->insert($o, true, false), 'insert string key ' . $id);
}
foreach ($ids as $id) {
	$o = $h->get($id);
	check(!$o->isNew() && $o->getVar('k') === $id, 'get exact string key ' . $id);
}
check($h->get("plain' OR '1'='1")->isNew(), 'string injection returns nothing');
check($h->get('')->isNew(), 'empty string key returns nothing');
check($h->get(array('plain'))->isNew(), 'array for scalar key returns nothing');

// update affects only the intended row
foreach ($ids as $id) {
	$o = $h->get($id);
	$o->setVar('title', 'U');
	$before = $pdo->query("SELECT COUNT(*) FROM s WHERE title='U'")->fetchColumn();
	$o->unsetNew();
	check($h->insert($o, true, false), 'update ' . $id);
	check($pdo->query("SELECT COUNT(*) FROM s WHERE title='U'")->fetchColumn() == $before + 1, 'update touched exactly one row ' . $id);
	check($pdo->query('SELECT title FROM s WHERE k=' . $pdo->quote($id))->fetchColumn() === 'U', 'update hit correct row ' . $id);
}
// delete affects only the intended row
$count = count($ids);
foreach ($ids as $id) {
	$o = $h->get($id);
	check($h->delete($o, true), 'delete ' . $id);
	$count--;
	check($pdo->query('SELECT COUNT(*) FROM s')->fetchColumn() == $count, 'delete removed exactly one row ' . $id);
	check($pdo->query('SELECT COUNT(*) FROM s WHERE k=' . $pdo->quote($id))->fetchColumn() == 0, 'delete removed correct row ' . $id);
}
check($pdo->query("SELECT name FROM sqlite_master WHERE name='s'")->fetchColumn() === 's', 'table survived');

// Integer primary key
$pdo->exec('CREATE TABLE i (id INTEGER PRIMARY KEY, title TEXT)');
$pdo->exec("INSERT INTO i VALUES (1,'one'),(2,'two'),(3,'three')");
$hi = new TestHandler($db, 'i', 'id', array('id' => XOBJ_DTYPE_INT));
check($hi->get(2)->getVar('title') === 'two', 'get int key');
check($hi->get('3')->getVar('title') === 'three', 'get numeric-string int key');
foreach (array("1 OR 1=1", "1' OR '1'='1", "1; DROP TABLE i", "1`", "-1 UNION SELECT 1,2", '1"', "0x1", "1.0", "") as $bad) {
	check($hi->get($bad)->isNew(), 'int key rejects payload ' . $bad);
	$o = $hi->create(false);
	$o->vars['id']['value'] = $bad;
	check($hi->delete($o, true) === false, 'delete rejects int payload ' . $bad);
	$o->vars['title']['value'] = 'HACK';
	$o->cleanVars = array('id' => $bad, 'title' => 'HACK');
	check($hi->insert($o, true, false) === false, 'update rejects int payload ' . $bad);
}
check(titles($pdo, 'i') === array('one', 'three', 'two'), 'int table untouched by payloads');
$o = $hi->get(2);
$o->setVar('title', 'TWO');
$o->unsetNew();
$hi->insert($o, true, false);
check(titles($pdo, 'i') === array('TWO', 'one', 'three'), 'int update only row 2');
$o = $hi->get(1);
$hi->delete($o, true);
check(titles($pdo, 'i') === array('TWO', 'three'), 'int delete only row 1');

// Composite primary key
$pdo->exec('CREATE TABLE c (a INTEGER, b TEXT, title TEXT, PRIMARY KEY (a, b))');
$hc = new TestHandler($db, 'c', array('a', 'b'), array('a' => XOBJ_DTYPE_INT, 'b' => XOBJ_DTYPE_TXTBOX));
$bs = array('x', "o'x", 'q"x', 'b`x', "' OR '1'='1", '1 OR 1=1; --');
foreach ($bs as $b) {
	foreach (array(1, 2) as $a) {
		$o = $hc->create();
		$o->setVar('a', $a);
		$o->setVar('b', $b);
		$o->setVar('title', "$a|$b");
		check($hc->insert($o, true, false), "insert composite $a,$b");
	}
}
foreach ($bs as $b) {
	$o = $hc->get(array(1, $b));
	check(!$o->isNew() && $o->getVar('title') === "1|$b", "get composite 1,$b");
	$o->setVar('title', 'U');
	$o->unsetNew();
	$hc->insert($o, true, false);
	check($pdo->query("SELECT COUNT(*) FROM c WHERE title='U'")->fetchColumn() == 1, "composite update one row $b");
	$hc->delete($o, true);
	check($pdo->query('SELECT COUNT(*) FROM c WHERE title=\'U\'')->fetchColumn() == 0
		&& $pdo->query('SELECT COUNT(*) FROM c WHERE b=' . $pdo->quote($b))->fetchColumn() == 1, "composite delete one row $b");
}
check($hc->get(array(1, "x' OR '1'='1"))->isNew(), 'composite injection in string part');
check($hc->get(array("1 OR 1=1", 'x'))->isNew(), 'composite injection in int part');
check($hc->get(array(1))->isNew(), 'composite with missing part returns nothing');
check($hc->get(1)->isNew(), 'composite with scalar returns nothing');

echo $fail ? "\n$fail FAILED\n" : "\nAll passed\n";
exit($fail ? 1 : 0);
