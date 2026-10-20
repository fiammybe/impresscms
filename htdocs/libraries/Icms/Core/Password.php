<?php
/**
 * Class to encrypt User Passwords.
 *
 * @category	ICMS
 * @package		Core
 * @since		1.2
 * @author		vaughan montgomery (vaughan@impresscms.org)
 * @author		ImpressCMS Project
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @copyright	(c) 2007-2010 The ImpressCMS Project - www.impresscms.org
 * @version	SVN: $Id: Password.php 12310 2013-09-13 21:33:58Z skenow $
 */
/**
 * Password generation and validation
 *
 * @category	ICMS
 * @package		Core
 * @subpackage	Password
 *
 */

namespace Icms\Core;

use Icms\Core\Password\Legacy;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;
use Symfony\Component\PasswordHasher\Hasher\SodiumPasswordHasher;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;

final class Password {

	private $pass;
	private $salt;
	private $uname;

	/**
	 * Constructor for the Password class
	 */
	public function __construct() {
	}

	/**
	 * Access the only instance of this class
	 * @return       object
	 * @static       $instance
	 * @staticvar    object
	 */
	public static function getInstance(): self {
		static $instance;

		if (!isset($instance)) {
			$instance = new self();
		}

		return $instance;
	}

	// ***** Public Functions *****

	/**
	 * This Function creates a unique random Salt Key for use with password encryptions
	 * It can also be used to generate a random AlphaNumeric key sequence of any given length.
	 * @copyright (c) 2007-2008 The ImpressCMS Project - www.impresscms.org
	 * @since    1.1
	 * @param    string  $slength    The length of the key to produce
	 * @return   string  returns the generated random key.
	 */
	public static function createSalt(int $slength = 64): string {
		$salt = '';
		$base = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		// PHP 8+ requires an int seed; microtime(true) keeps legacy variability.
		$seed = (int) (microtime(true) * 1000000);
		mt_srand($seed);
		for ($i=0; $i<=$slength; $i++)
		$salt.= substr($base, mt_rand(0, strlen($base)), 1);

		return $salt;
	}

	/**
	 * This Function creates a unique Crypto Generated Key for use with password encryptions
	 * This functions falls back to standard function createSalt() if PHP < 5.3
	 * @copyright (c) 2007-2008 The ImpressCMS Project - www.impresscms.org
	 * @since    1.3.3
	 * @param    string  $slength    The length of the key to produce
	 * @return   string  returns the generated random key.
	 */
	public function createCryptoKey(int $slength = 64): string {
		if (function_exists('openssl_random_pseudo_bytes')) {
			$key = openssl_random_pseudo_bytes($slength, $strong);
			if ($strong === TRUE) {
				return $key;
			} else {
				return self::createCryptoKey($slength);
			}
		} else {
			return self::createSalt($slength);
		}
	}

	/**
	 * This Public Function checks whether a users password has been expired
	 * @copyright (c) 2007-2008 The ImpressCMS Project - www.impresscms.org
	 * @since    1.1
	 * @param    string  $uname      The username of the account to be checked
	 * @return   bool     returns true if password is expired, false if password is not expired.
	 */
	public function passExpired(string $uname = ''): bool {
		if (empty($uname)) {
			\redirect_header('user.php', 2, _US_SORRYNOTFOUND);
		}

		return self::_passExpired($uname);
	}

	/**
	 * This Public Function returns the User Salt key belonging to username.
	 * @copyright (c) 2007-2008 The ImpressCMS Project - www.impresscms.org
	 * @since    1.1
	 * @param    string  $uname      Username to find User Salt key for.
	 * @return   string  returns the Salt key of the user.
	 *
	 * To be removed in future versions
	 */
	public function getUserSalt(string $uname = ''): string {
		if (empty($uname)) {
			\redirect_header('user.php', 2, _US_SORRYNOTFOUND);
		}

		return self::_getUserSalt($uname);
	}

	/**
	 * This Public Function returns the User Encryption Type belonging to username.
	 * @copyright (c) 2007-2008 The ImpressCMS Project - www.impresscms.org
	 * @since    1.1
	 * @param    string  $uname      Username to find Encryption Type for.
	 * @return   string  returns the Encryption Type of the user.
	 *
	 * to be removed in future versions
	 */
	public function getUserEncType(string $uname = ''): int {
		if (empty($uname)) {
			\redirect_header('user.php', 2, _US_SORRYNOTFOUND);
		}

		return self::_getUserEncType($uname);
	}

	/**
	 * This Public Function is used to Encrypt User Passwords
	 * @copyright (c) 2007-2008 The ImpressCMS Project - www.impresscms.org
	 * @since    1.1
	 * @param    string  $pass       plaintext password to be encrypted
	 * @return   Hash of users password.
	 */
	public function encryptPass(string $pass): string {
		return self::hasher()->hash($pass);
	}

	/**
	 * The password algorithm: Argon2id where PHP offers it (libsodium or
	 * PASSWORD_ARGON2ID), bcrypt otherwise. (Symfony's `auto` chooses bcrypt.)
	 */
	public static function algorithm(): string {
		$sodium = SodiumPasswordHasher::isSupported() && defined('SODIUM_CRYPTO_PWHASH_ALG_ARGON2ID13');

		return $sodium || defined('PASSWORD_ARGON2ID') ? 'argon2id' : 'bcrypt';
	}

	/**
	 * The password hasher of ImpressCMS, for legacy code and the host alike
	 * (App\Security\Password\Hasher\Modern), so that both produce the same
	 * hashes. It verifies bcrypt and Argon2id hashes, and asks for a rehash of
	 * those that do not use algorithm().
	 */
	public static function hasher(): PasswordHasherInterface {
		static $hasher;

		return $hasher ??= (new PasswordHasherFactory(['icms' => ['algorithm' => self::algorithm()]]))->getPasswordHasher('icms');
	}

	/**
	 * Whether a value of the `pass` column should be replaced by a fresh hash of
	 * the password: a legacy or wrapped value, or a hash with outdated parameters.
	 */
	public static function needsRehash(string $pass): bool {
		return $pass === '' || $pass[0] !== '$' || Legacy::isWrapped($pass) || Legacy::isUnwrapped($pass) || self::hasher()->needsRehash($pass);
	}

	/**
	 * Whether $plain is the password of a value of the `pass` column, in any
	 * format: a hash of the hasher, a wrapped or a legacy value (with the
	 * user's `salt` and `enc_type` columns for the oldest format).
	 */
	public static function matches(string $plain, string $pass, string $saltColumn = '', int $encTypeColumn = 1): bool {
		if ($plain === '' || $pass === '') {
			return false;
		}
		$value = Legacy::describe($pass, $saltColumn, $encTypeColumn);
		if (Legacy::isWrapped($value) || Legacy::isUnwrapped($value)) {
			return Legacy::verify($value, $plain, self::hasher(), (string) constant('XOOPS_DB_SALT'));
		}

		return self::hasher()->verify($pass, $plain);
	}

	/**
	 * This Public Function verifies if the users password is correct.
	 * @copyright (c) 2007-2008 The ImpressCMS Project - www.impresscms.org
	 * @since    1.3.3
	 * @param    string  $uname      Username to verify.
	 * @param    string  $pass       Password to verify.
	 * @return   mixed      returns Hash if correct, returns false if incorrect.
	 */
	public function verifyPass(string $pass = '', string $uname = '') {
		if ($pass === '' || $uname === '') {
			return false;
		}

		return self::_verifyPassword($pass, $uname);
	}

	// ***** Private Functions *****

	/**
	 * This Private Function checks whether a users password has been expired
	 * @copyright (c) 2007-2008 The ImpressCMS Project - www.impresscms.org
	 * @since    1.1
	 * @param    string  $uname      The username of the account to be checked
	 * @return   bool     returns true if password is expired, false if password is not expired.
	 */
	private function _passExpired($uname) {
		$uname = @htmlspecialchars($uname, ENT_QUOTES, _CHARSET);
		$table = new \Icms\Db\Legacy\Updater\Table('users');

		if ($table->fieldExists('loginname')) {
			$sql = \icms::$xoopsDB->query(sprintf("SELECT pass_expired FROM %s WHERE loginname = %s",
			\icms::$xoopsDB->prefix('users'), \icms::$xoopsDB->quoteString($uname)));
			list($pass_expired) = \icms::$xoopsDB->fetchRow($sql);
		} elseif ($table->fieldExists('login_name')) {
			$sql = \icms::$xoopsDB->query(sprintf("SELECT pass_expired FROM %s WHERE login_name = %s",
			\icms::$xoopsDB->prefix('users'), \icms::$xoopsDB->quoteString($uname)));
			list($pass_expired) = \icms::$xoopsDB->fetchRow($sql);
		} else {
			$sql = \icms::$xoopsDB->query(sprintf("SELECT pass_expired FROM %s WHERE uname = %s",
			\icms::$xoopsDB->prefix('users'), \icms::$xoopsDB->quoteString($uname)));
			list($pass_expired) = \icms::$xoopsDB->fetchRow($sql);
		}

		if ($pass_expired == 1) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * This Private Function returns the User Salt key belonging to username.
	 * @copyright (c) 2007-2008 The ImpressCMS Project - www.impresscms.org
	 * @since    1.1
	 * @param    string  $uname      Username to find User Salt key for.
	 * @return   string  returns the Salt key of the user.
	 *
	 * To be removed in future versions
	 */
	private function _getUserSalt($uname) {
		$table = new \Icms\Db\Legacy\Updater\Table('users');
		$uname = @htmlspecialchars($uname, ENT_QUOTES, _CHARSET);

		if ($table->fieldExists('loginname')) {
			$sql = \icms::$xoopsDB->query(sprintf("SELECT salt FROM %s WHERE loginname = %s",
			\icms::$xoopsDB->prefix('users'), \icms::$xoopsDB->quoteString($uname)));
			list($salt) = \icms::$xoopsDB->fetchRow($sql);
		} elseif ($table->fieldExists('login_name')) {
			$sql = \icms::$xoopsDB->query(sprintf("SELECT salt FROM %s WHERE login_name = %s",
			\icms::$xoopsDB->prefix('users'), \icms::$xoopsDB->quoteString($uname)));
			list($salt) = \icms::$xoopsDB->fetchRow($sql);
		} else {
			$sql = \icms::$xoopsDB->query(sprintf("SELECT salt FROM %s WHERE uname = %s",
			\icms::$xoopsDB->prefix('users'), \icms::$xoopsDB->quoteString($uname)));
			list($salt) = \icms::$xoopsDB->fetchRow($sql);
		}

		return $salt;
	}

	/**
	 * This Private Function returns the User Encryption Type belonging to username.
	 * @copyright (c) 2007-2008 The ImpressCMS Project - www.impresscms.org
	 * @since    1.2.3
	 * @param    string  $uname      Username to find Enc_type for.
	 * @return   string  returns the Encryption type of the user.
	 *
	 * To be removed in future versions
	 */
	private function _getUserEncType($uname) {
		$table = new \Icms\Db\Legacy\Updater\Table('users');
		$uname = @htmlspecialchars($uname, ENT_QUOTES, _CHARSET);

		if($table->fieldExists('loginname')) {
			$sql = \icms::$xoopsDB->query(sprintf("SELECT enc_type FROM %s WHERE loginname = %s",
			\icms::$xoopsDB->prefix('users'), \icms::$xoopsDB->quoteString($uname)));
			list($enc_type) = \icms::$xoopsDB->fetchRow($sql);
		} elseif($table->fieldExists('login_name')) {
			$sql = \icms::$xoopsDB->query(sprintf("SELECT enc_type FROM %s WHERE login_name = %s",
			\icms::$xoopsDB->prefix('users'), \icms::$xoopsDB->quoteString($uname)));
			list($enc_type) = \icms::$xoopsDB->fetchRow($sql);
		} else {
			$sql = \icms::$xoopsDB->query(sprintf("SELECT enc_type FROM %s WHERE uname = %s",
			\icms::$xoopsDB->prefix('users'), \icms::$xoopsDB->quoteString($uname)));
			list($enc_type) = \icms::$xoopsDB->fetchRow($sql);
		}

		return (int) $enc_type;
	}

	/**
	 * This Private Function returns the User Password Hash belonging to username.
	 * @copyright (c) 2007-2008 The ImpressCMS Project - www.impresscms.org
	 * @since    1.3.3
	 * @param    string  $uname      Username to find hash for.
	 * @return   string  returns the Password hash of the user.
	 */
	private function _getUserHash($uname) {
		if (empty($uname)) {
			redirect_header('user.php', 2, _US_SORRYNOTFOUND);
		}

		$table = new \Icms\Db\Legacy\Updater\Table('users');
		$uname = @htmlspecialchars($uname, ENT_QUOTES, _CHARSET);

		if($table->fieldExists('loginname')) {
			$sql = \icms::$xoopsDB->query(sprintf("SELECT pass FROM %s WHERE loginname = %s",
			\icms::$xoopsDB->prefix('users'), \icms::$xoopsDB->quoteString($uname)));
			list($pass) = \icms::$xoopsDB->fetchRow($sql);
		} elseif($table->fieldExists('login_name')) {
			$sql = \icms::$xoopsDB->query(sprintf("SELECT pass FROM %s WHERE login_name = %s",
			\icms::$xoopsDB->prefix('users'), \icms::$xoopsDB->quoteString($uname)));
			list($pass) = \icms::$xoopsDB->fetchRow($sql);
		} else {
			$sql = \icms::$xoopsDB->query(sprintf("SELECT pass FROM %s WHERE uname = %s",
			\icms::$xoopsDB->prefix('users'), \icms::$xoopsDB->quoteString($uname)));
			list($pass) = \icms::$xoopsDB->fetchRow($sql);
		}

		return $pass;
	}

	/**
	 * This Private Function verifies if the password is correct
	 * @param    string     $pass       Password to be verified
	 * @param    string     $uname      Username of password to be verified
	 * @return   mixed      returns password HASH if correct, returns false if incorrect
	 */
	private function _verifyPassword($pass, $uname) {
		$userHash = (string) self::_getUserHash($uname);
		$userSalt = (string) self::_getUserSalt($uname);

		return self::matches($pass, $userHash, $userSalt, self::_getUserEncType($uname)) ? $userHash : false;
	}
}
