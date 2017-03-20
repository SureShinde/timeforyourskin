<?php
/**
 * Created by PhpStorm.
 * User: manuele
 * Date: 16/01/15
 * Time: 11:17
 */

class Webgriffe_Tntpro_Helper_PointService extends Mage_Core_Helper_Abstract
{
    const TNTPRO_ACTIVE_POINTS_CACHE_KEY = 'tntpro.active.points';
    const SHIPPING_WGTNTPRO_LOCATOR_ENABLED = 'shipping/wgtntpro_locator/enabled';
    const SHIPPING_WGTNTPRO_LOCATOR_POINTS_URL = 'shipping/wgtntpro_locator/points_url';

    public function getDataByCode($code)
    {
        $points = $this->getCachedActivePointsByCode();
        return $points[$code];
    }

    public function getCachedActivePoints()
    {
        $points = Mage::app()->loadCache(self::TNTPRO_ACTIVE_POINTS_CACHE_KEY);
        if ($points === false) {
            $points = $this->__getActivePointsFromRemoteUpstream();
            Mage::app()->saveCache($points, self::TNTPRO_ACTIVE_POINTS_CACHE_KEY, array(), 4*60*60);
        }
        return unserialize($points);
    }

    public function getCachedActivePointsByCode()
    {
        $activeDeliveryPoints = $this->getCachedActivePoints();
        return array_combine(
            array_map(array($this, '__mapPointByCode'), $activeDeliveryPoints),
            $activeDeliveryPoints
        );
    }

    public function findActivePointsByQuery($query, $limit = null)
    {
        $results = array();
        foreach ($this->getCachedActivePoints() as $point) {
            if (
                stristr($point['town'], $query) !== false ||
                stristr($point['postCode'], $query) !== false ||
                stristr($point['companyName'], $query) !== false
            ) {
                $results[] = $point;
            }

            if (is_integer($limit) && count($results) >= $limit) {
                break;
            }
        }
        return $results;
    }

    public function isEnabled()
    {
        return Mage::getStoreConfigFlag(self::SHIPPING_WGTNTPRO_LOCATOR_ENABLED);
    }

    public function getTntPointsUrl()
    {
        return Mage::getStoreConfig(self::SHIPPING_WGTNTPRO_LOCATOR_POINTS_URL);
    }

    private function __getActivePointsFromRemoteUpstream()
    {
        $s = curl_init($this->getTntPointsUrl());
        curl_setopt($s, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($s);
        if (function_exists('gzdecode')) {
            $points = gzdecode($data);
        } else {
            $points = $this->__gzdecodeReplacement($data);
        }
        $points = preg_replace('/^var\s+\w+\s*=\s*/', '', $points);
        $points = json_decode($points, true);
        if (empty($points)) {
            return false;
        }
        return serialize(array_filter($points, array($this, '__mapPointByActive')));
    }

    private function __mapPointByCode($point)
    {
        return $point['code'];
    }

    private function __mapPointByActive($point)
    {
        return $point['active'];
    }

    private function __gzdecodeReplacement($data, &$filename = '', &$error = '', $maxlength = null)
    {
        $len = strlen($data);
        if ($len < 18 || strcmp(substr($data, 0, 2), "\x1f\x8b")) {
            $error = "Not in GZIP format.";
            return null;  // Not GZIP format (See RFC 1952)
        }
        $method = ord(substr($data, 2, 1));  // Compression method
        $flags = ord(substr($data, 3, 1));  // Flags
        if ($flags & 31 != $flags) {
            $error = "Reserved bits not allowed.";
            return null;
        }
        // NOTE: $mtime may be negative (PHP integer limitations)
        $mtime = unpack("V", substr($data, 4, 4));
        $mtime = $mtime[1];
        $xfl = substr($data, 8, 1);
        $os = substr($data, 8, 1);
        $headerlen = 10;
        $extralen = 0;
        $extra = "";
        if ($flags & 4) {
            // 2-byte length prefixed EXTRA data in header
            if ($len - $headerlen - 2 < 8) {
                return false;  // invalid
            }
            $extralen = unpack("v", substr($data, 8, 2));
            $extralen = $extralen[1];
            if ($len - $headerlen - 2 - $extralen < 8) {
                return false;  // invalid
            }
            $extra = substr($data, 10, $extralen);
            $headerlen += 2 + $extralen;
        }
        $filenamelen = 0;
        $filename = "";
        if ($flags & 8) {
            // C-style string
            if ($len - $headerlen - 1 < 8) {
                return false; // invalid
            }
            $filenamelen = strpos(substr($data, $headerlen), chr(0));
            if ($filenamelen === false || $len - $headerlen - $filenamelen - 1 < 8) {
                return false; // invalid
            }
            $filename = substr($data, $headerlen, $filenamelen);
            $headerlen += $filenamelen + 1;
        }
        $commentlen = 0;
        $comment = "";
        if ($flags & 16) {
            // C-style string COMMENT data in header
            if ($len - $headerlen - 1 < 8) {
                return false;    // invalid
            }
            $commentlen = strpos(substr($data, $headerlen), chr(0));
            if ($commentlen === false || $len - $headerlen - $commentlen - 1 < 8) {
                return false;    // Invalid header format
            }
            $comment = substr($data, $headerlen, $commentlen);
            $headerlen += $commentlen + 1;
        }
        $headercrc = "";
        if ($flags & 2) {
            // 2-bytes (lowest order) of CRC32 on header present
            if ($len - $headerlen - 2 < 8) {
                return false;    // invalid
            }
            $calccrc = crc32(substr($data, 0, $headerlen)) & 0xffff;
            $headercrc = unpack("v", substr($data, $headerlen, 2));
            $headercrc = $headercrc[1];
            if ($headercrc != $calccrc) {
                $error = "Header checksum failed.";
                return false;    // Bad header CRC
            }
            $headerlen += 2;
        }
        // GZIP FOOTER
        $datacrc = unpack("V", substr($data, -8, 4));
        $datacrc = sprintf('%u', $datacrc[1] & 0xFFFFFFFF);
        $isize = unpack("V", substr($data, -4));
        $isize = $isize[1];
        // decompression:
        $bodylen = $len - $headerlen - 8;
        if ($bodylen < 1) {
            // IMPLEMENTATION BUG!
            return null;
        }
        $body = substr($data, $headerlen, $bodylen);
        $data = "";
        if ($bodylen > 0) {
            switch ($method) {
                case 8:
                    // Currently the only supported compression method:
                    $data = gzinflate($body, $maxlength);
                    break;
                default:
                    $error = "Unknown compression method.";
                    return false;
            }
        }  // zero-byte body content is allowed
        // Verifiy CRC32
        $crc = sprintf("%u", crc32($data));
        $crcOK = $crc == $datacrc;
        $lenOK = $isize == strlen($data);
        if (!$lenOK || !$crcOK) {
            $error = ($lenOK ? '' : 'Length check FAILED. ') . ($crcOK ? '' : 'Checksum FAILED.');
            return false;
        }
        return $data;
    }
}
