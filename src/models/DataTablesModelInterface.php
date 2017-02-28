<?php

/**
 * @author	José Lorente <jose.lorente.martin@gmail.com>
 * @copyright	José Lorente <jose.lorente.martin@gmail.com>
 * @license     http://opensource.org/licenses/mit-license.php The MIT License (MIT)
 * @version	2.0
 */

namespace jlorente\datatables\models;

use yii\db\QueryInterface;

/**
 * Action to process ajax requests from DataTables plugin.
 * 
 * @see http://datatables.net/manual/server-side for more info
 * @author José Lorente <jose.lorente.martin@gmail.com>
 */
interface DataTablesModelInterface {

    /**
     * Gets the columns used to display and search the datatable.
     * 
     * @return array The columns to be displayed and searched.
     * @see \yii\grid\GridView::$columns to see the column format.
     */
    public function getColumns();

    /**
     * Gets the Query object to search the model.
     * 
     * @return QueryInterface
     */
    public function getQuery();

    /**
     * 
     * @return array The attributes to use in the Sort model.
     * @see \yii\data\Sort::$attributes to see the column format.
     */
    public function getSortAttributes();

    /**
     * Custom search implementation for the main search box.
     * 
     * @param QueryInterface $query
     * @param string $value
     */
    public function searchFree($query, $value);
}
