<?php

/**
 * PHP 8.4+ object save handler wrapper for icms_core_Session.
 */
class icms_core_SessionSaveHandler implements SessionHandlerInterface {

	/**
	 * @var icms_core_Session
	 */
	private $session;

	/**
	 * @param icms_core_Session $session
	 */
	public function __construct(icms_core_Session $session) {
		$this->session = $session;
	}

	public function open(string $path, string $name): bool {
		return $this->session->open($path, $name);
	}

	public function close(): bool {
		return $this->session->close();
	}

	public function read(string $id): string|false {
		return $this->session->read($id);
	}

	public function write(string $id, string $data): bool {
		return $this->session->write($id, $data);
	}

	public function destroy(string $id): bool {
		return $this->session->destroy($id);
	}

	public function gc(int $max_lifetime): int|false {
		$result = $this->session->gc($max_lifetime);
		return $result ? 1 : false;
	}
}

