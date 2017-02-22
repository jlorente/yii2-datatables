<?php

/**
 * @author	José Lorente <jose.lorente.martin@gmail.com>
 * @copyright	José Lorente <jose.lorente.martin@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php The MIT License (MIT)
 * @version	1.0
 */

namespace jlorente\datatables\actions;

use Yii;
use yii\base\Action,
    yii\base\InvalidConfigException;
use yii\web\Response;
use jlorente\datatables\models\DataTablesModelInterface;

/**
 * Action to process ajax requests from DataTables plugin.
 * 
 * @see http://datatables.net/manual/server-side for more info
 * @author José Lorente <jose.lorente.martin@gmail.com>
 */
class AjaxAction extends Action {

    /**
     * @var DataTablesModelInterface
     */
    public $dataTablesModelInterface;

    /**
     *
     * @var string
     */
    public $modelClass = 'jlorente\\datatables\\models\\SearchModel';

    /**
     * @inheritdoc
     */
    public function init() {
        if ($this->dataTablesModelInterface === null) {
            throw new InvalidConfigException('dataTablesModelInterface must be set.');
        }
    }

    /**
     * @inheritdoc
     */
    public function run() {
        /* @var $model \jlorente\datatables\models\SearchModel */
        $model = new $this->modelClass([
            'searchModel' => $this->dataTablesModelInterface
        ]);
        $model->load(Yii::$app->request->queryParams, '');
        Yii::$app->response->format = Response::FORMAT_JSON;
        try {
            return $model->getResponse();
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

}
