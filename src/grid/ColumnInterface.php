<?php

/**
 * @author	José Lorente <jose.lorente.martin@gmail.com>
 * @copyright	José Lorente <jose.lorente.martin@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php The MIT License (MIT)
 * @version	1.0
 */

namespace jlorente\datatables\grid;

/**
 * 
 * Class to be used in the Server-side processing requests of the DataTables 
 * plugin.
 * 
 * @see http://datatables.net/manual/server-side for more info
 * @author José Lorente <jose.lorente.martin@gmail.com>
 */
interface ColumnInterface {

    /**
     * Gets the column configuration array.
     * 
     * @return array 
     */
    public function getConfiguration();

    /**
     * Gets the formatted value of the column.
     * 
     * @param mixed $model
     * @param string $key
     * @param int $index
     * @return string
     */
    public function getFormattedValue($model, $key, $index);
}
