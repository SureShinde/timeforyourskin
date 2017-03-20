<?php



/**
 * Reads the contents of the file in the beginning.
 */
class I10n_CachedFileReader extends I10n_StringReader {
	function I10n_CachedFileReader($filename) {
		parent::StringReader();
		$this->_str = file_get_contents($filename);
		if (false === $this->_str)
			return false;
		$this->_pos = 0;
	}
}