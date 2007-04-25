<?php
/**
 * Copyright � STMicroelectronics, 2006. All Rights Reserved.
 * 
 * Originally written by Manuel VACELET, 2006.
 * 
 * This file is a part of CodeX.
 * 
 * CodeX is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * CodeX is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with CodeX; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * 
 * 
 */

require_once('Docman_Filter.class.php');
require_once('Docman_ValidateFilter.class.php');
require_once('Docman_SqlFilter.class.php');
require_once('Docman_HtmlFilter.class.php');

class Docman_FilterBo {
    var $filters;

    function Docman_FilterBo() {        
    }

    function getFromMetadata($md) {
        $f = null;

        switch($md->getType()) {
        case PLUGIN_DOCMAN_METADATA_TYPE_TEXT:
            $f = new Docman_Filter($md);
            break;
        case PLUGIN_DOCMAN_METADATA_TYPE_STRING:
            $f = new Docman_Filter($md);
            break;
        case PLUGIN_DOCMAN_METADATA_TYPE_DATE:
            $f = new Docman_FilterDate($md);
            break;
        case PLUGIN_DOCMAN_METADATA_TYPE_LIST:
            $f = new Docman_Filter($md);
            break;
        }

        return $f;
    }

}

?>
