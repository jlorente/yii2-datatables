<?php

/**
 * @author	José Lorente <jose.lorente.martin@gmail.com>
 * @copyright	José Lorente <jose.lorente.martin@gmail.com>
 * @license     http://opensource.org/licenses/mit-license.php The MIT License (MIT)
 * @version	1.0
 */

namespace jlorente\datatables\data;

use yii\helpers\ArrayHelper;
use yii\data\ActiveDataProvider as BaseActiveDataProvider;

/**
 * Prepared ActiveDataProvider class for the Server-side processing requests 
 * of the DataTables plugin.
 * 
 * @see http://datatables.net/manual/server-side for more info
 * @author José Lorente <jose.lorente.martin@gmail.com>
 */
class ActiveDataProvider extends BaseActiveDataProvider {

    /**
     * @inheritdoc
     */
    public function __construct($config = []) {
        parent::__construct(ArrayHelper::merge([
                    'sort' => [
                        'class' => Sort::className()
                        , 'sortParam' => 'order'
                    ],
                    'pagination' => [
                        'class' => Pagination::className()
                        , 'offsetParam' => 'start'
                        , 'pageSizeParam' => 'length'
                    ]
                        ], $config));
    }

}
