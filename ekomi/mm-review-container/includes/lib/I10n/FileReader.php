<?php

class I10n_FileReader extends I10n_Streams
{
    var $_f;        // HTTP socket

    /**
     * @param string $filename
     */
    function I10n_FileReader($filename)
    {
        parent::I10n_Streams();
        $this->_f = fopen($filename, 'rb');
    }

    /**
     * @param int $bytes
     */
    function read($bytes)
    {
        return fread($this->_f, $bytes);
    }

    /**
     * @param int $pos
     * @return boolean
     */
    function seekto($pos)
    {
        if (-1 == fseek($this->_f, $pos, SEEK_SET)) {
            return false;
        }
        $this->_pos = $pos;
        return true;
    }

    function is_resource()
    {
        return is_resource($this->_f);
    }

    function feof()
    {
        return feof($this->_f);
    }

    function close()
    {
        return fclose($this->_f);
    }

    function read_all()
    {
        $all = '';
        while (!$this->feof())
            $all .= $this->read(4096);
        return $all;
    }
}