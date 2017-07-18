<?php

/**
 * @author	José Lorente <jose.lorente.martin@gmail.com>
 * @copyright	José Lorente <jose.lorente.martin@gmail.com>
 * @license     http://opensource.org/licenses/mit-license.php The MIT License (MIT)
 * @version	2.0
 */

namespace jlorente\datatables\models;

use Yii;
use yii\base\Model;
use yii\i18n\Formatter;
use jlorente\datatables\grid\DataColumn;
use jlorente\datatables\data\ActiveDataProvider;

/**
 * Action to process ajax requests from DataTables plugin.
 * 
 * @see http://datatables.net/manual/server-side for more info
 * @author José Lorente <jose.lorente.martin@gmail.com>
 */
class SearchModel extends Model {

    /**
     *
     * @var array 
     */
    public $search = ['value' => null, 'regex' => false];

    /**
     *
     * @var arra 
     */
    public $columns = [];

    /**
     *
     * @var DataColumn[] 
     */
    protected $dataColumns = [];

    /**
     * @var array|Formatter the formatter used to format model attribute values into displayable texts.
     * This can be either an instance of [[Formatter]] or an configuration array for creating the [[Formatter]]
     * instance. If this property is not set, the "formatter" application component will be used.
     */
    public $formatter;

    /**
     *
     * @var DataTablesModelInterface
     */
    protected $searchModel;

    /**
     * Original Query object.
     * 
     * @var ActiveQuery 
     */
    protected $query;

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['search', 'columns'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function init() {
        parent::init();
        if ($this->searchModel === null) {
            throw new InvalidConfigException('The "searchModel" property must provided on initialization.');
        }
        if ($this->formatter === null) {
            $this->formatter = Yii::$app->getFormatter();
        } elseif (is_array($this->formatter)) {
            $this->formatter = Yii::createObject($this->formatter);
        }
        if (!$this->formatter instanceof Formatter) {
            throw new InvalidConfigException('The "formatter" property must be either a Format object or a configuration array.');
        }
        $this->initColumns();
    }

    /**
     * DataTablesModelInterface setter method.
     * 
     * @param DataTablesModelInterface $model
     */
    public function setSearchModel(DataTablesModelInterface $model) {
        $this->searchModel = $model;
    }

    /**
     * DataTablesModelInterface getter method.
     * 
     * @return DataTablesModelInterface
     */
    public function getSearchModel() {
        return $this->searchModel;
    }

    /**
     * Perform the search with the given parameters.
     * 
     * @return ActiveDataProvider
     */
    public function getResponse() {
        if (!$this->validate()) {
            return false;
        }
        $dataProvider = $this->search();
        return [
            'draw' => (int) Yii::$app->request->get('draw', 1)
            , 'recordsTotal' => (int) $dataProvider->getTotalCount()
            , 'recordsFiltered' => (int) $dataProvider->getTotalCount()
            , 'data' => $this->processModels($dataProvider->getModels())
        ];
    }

    /**
     * Gets the data provided with the filtered query.
     * 
     * @return ActiveDataProvider
     */
    public function search($searchModelParams = null) {
        $this->searchModel->load($searchModelParams);
        $dProvider = $this->searchModel->getDataProvider();
        $query = $dProvider->query;
        foreach ($this->columns as $column) {
            if (isset($column['searchable']) === true && $column['searchable'] === 'true' && empty($column['search']['value']) === false) {
                $query->andWhere(['like', $column['data'], $column['search']['value']]);
            }
        }
        if (empty($this->search['value']) === false) {
            $this->searchModel->searchFree($query, $this->search['value']);
        }
        return $dProvider;
    }

    /**
     * Creates the response array data with the provided models.
     * 
     * @param array $models
     * @return array
     */
    protected function processModels(array $models) {
        $response = [];
        for ($i = 0, $t = count($models); $i < $t; ++$i) {
            $response[$i] = $this->processModel($models[$i], $models[$i]->getPrimaryKey(), $i);
        }
        return $response;
    }

    /**
     * Creates the response array data with the provided model.
     * 
     * @param mixed $model the data model to be rendered
     * @param mixed $key the key associated with the data model
     * @param int $index the zero-based index of the data model among the model array returned by [[dataProvider]].
     * @return array
     */
    public function processModel($model, $key, $index) {
        $cells = [];
        /* @var $column Column */
        foreach ($this->dataColumns as $column) {
            $cells[$column->attribute] = $column->getFormattedValue($model, $key, $index);
        }
        return $cells;
    }

    /**
     * Creates column objects and initializes them.
     */
    protected function initColumns() {
        /* if (empty($this->columns)) {
          $this->guessColumns();
          } */
        foreach ($this->searchModel->getColumns() as $i => $column) {
            if (is_string($column)) {
                $column = DataColumn::createDataColumn($column, $this);
            } else {
                $column = Yii::createObject(array_merge([
                            'class' => DataColumn::className()
                            , 'grid' => $this
                                        ], $column));
            }

            if (!$column->visible) {
                unset($this->dataColumns[$i]);
                continue;
            }
            $this->dataColumns[$i] = $column;
        }
    }

}
