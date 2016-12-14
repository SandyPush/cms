<?php
class View_Helper_CutStr
{
	/**
	 * only for "GBK"
	 *
	 * @param string $str
	 * @param integer $length
	 * @param boolean $ellipsis
	 */
	function cutStr($str, $length, $ellipsis = true)
	{
		$len = strlen($str);
		
		if ($len <= $length) {
			return $str;
		}
		
		$limit = $ellipsis ? $length - 3 : $length;
        $str = substr($str, 0, $limit);
        
        for ($i = $limit - 1; $i >= 0; $i --) {
        	if (ord($str[$i]) < 129) {
                break;
        	}
        }
        
		if (($limit - $i) % 2 == 0) {
		    $str = substr($str, 0, -1);
		}

        $str .= $ellipsis ? '...' : '';

        return $str;
	}
}