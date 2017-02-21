<?php

/**
 * @author	José Lorente <jose.lorente.martin@gmail.com>
 * @copyright	José Lorente <jose.lorente.martin@gmail.com>
 * @license     http://opensource.org/licenses/mit-license.php The MIT License (MIT)
 * @version	1.0
 */

namespace jlorente\datatables\data;

use yii\data\Pagination as BasePagination;

/**
 * Class to be used in the Server-side processing requests of the DataTables 
 * plugin.
 * 
 * @see http://datatables.net/manual/server-side for more info
 * @author José Lorente <jose.lorente.martin@gmail.com>
 */
class Pagination extends BasePagination {

    /**
     * 
     * @var string 
     */
    public $offsetParam;

    /**
     * @inheritdoc
     * 
     * Datatables sends the item offset instead of the page, so this method is 
     * used to convert the given offset to a page number.
     */
    protected function getQueryParam($name, $defaultValue = null) {
        if ($name === $this->pageParam) {
            $param = parent::getQueryParam($name, null);
            if ($param === null) {
                $offset = parent::getQueryParam($this->offsetParam, 0);
                $param = intval($offset / $this->getPageSize()) + 1;
            }
        } else {
            $param = parent::getQueryParam($name, $defaultValue);
        }
        return $param;
    }

}
