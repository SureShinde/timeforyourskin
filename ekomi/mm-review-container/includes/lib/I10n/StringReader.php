<?php

/**
 * Provides file-like methods for manipulating a string instead
 * of a physical file.
 */
class I10n_StringReader extends I10n_Streams
{

    var $_str = '';

    function I10n_StringReader($str = '')
    {
        parent::I10n_Streams();
        $this->_str = $str;
        $this->_pos = 0;
    }

    /**
     * @param string $bytes
     * @return string
     */
    function read($bytes)
    {
        $data = $this->substr($this->_str, $this->_pos, $bytes);
        $this->_pos += $bytes;
        if ($this->strlen($this->_str) < $this->_pos) $this->_pos = $this->strlen($this->_str);
        return $data;
    }

    /**
     * @param int $pos
     * @return int
     */
    function seekto($pos)
    {
        $this->_pos = $pos;
        if ($this->strlen($this->_str) < $this->_pos) $this->_pos = $this->strlen($this->_str);
        return $this->_pos;
    }

    function length()
    {
        return $this->strlen($this->_str);
    }

    function read_all()
    {
        return $this->substr($this->_str, $this->_pos, $this->strlen($this->_str));
    }

}