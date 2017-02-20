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
use yii\db\ActiveQuery;

/**
 * Action to process ajax requests from DataTables plugin.
 * 
 * @see http://datatables.net/manual/server-side for more info
 * @author José Lorente <jose.lorente.martin@gmail.com>
 */
class AjaxAction extends Action {

    /**
     * @var ActiveQuery
     */
    protected $query;

    /**
     *
     * @var string
     */
    public $modelClass = 'jlorente\\datatables\\models\\SearchModel';

    /**
     * Query setter method.
     * 
     * @param ActiveQuery $query
     */
    public function setQuery(ActiveQuery $query) {
        $this->query = $query;
    }

    /**
     * Query getter method.
     * 
     * @return ActiveQuery
     */
    public function getQuery() {
        return $this->query;
    }

    /**
     * @inheritdoc
     */
    public function init() {
        if ($this->query === null) {
            throw new InvalidConfigException(get_class($this) . '::$query must be set.');
        }
    }

    /**
     * @inheritdoc
     */
    public function run() {
        $model = new $this->modelClass();
        $model->query = $this->query;
        $model->load(Yii::$app->request->getBodyParams());
        $dataProvider = $model->search();
        Yii::$app->response->format = Response::FORMAT_JSON;
        try {
            return [
                'draw' => (int) Yii::$app->request->getBodyParam('draw', 1),
                'recordsTotal' => (int) $dataProvider->getTotalCount(),
                'recordsFiltered' => (int) $dataProvider->getCount(),
                'data' => $dataProvider->getModels(),
            ];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

}
