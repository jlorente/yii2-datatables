<?php

/**
 * @author	José Lorente <jose.lorente.martin@gmail.com>
 * @copyright	José Lorente <jose.lorente.martin@gmail.com>
 * @license     http://opensource.org/licenses/mit-license.php The MIT License (MIT)
 * @version	2.0
 */

namespace jlorente\datatables\models;

use yii\base\Model;
use yii\db\ActiveQuery;
use jlorente\datatables\data\ActiveDataProvider;

/**
 * Action to process ajax requests from DataTables plugin.
 * 
 * @see http://datatables.net/manual/server-side for more info
 * @author José Lorente <jose.lorente.martin@gmail.com>
 */
class SearchModel extends Model {

    public $search = ['value' => null, 'regex' => false];
    public $columns = [];
    protected $query;

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['search', 'column'], 'safe']
        ];
    }

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
     * Perform the search with the given parameters.
     * 
     * @return mixed[]
     */
    public function search() {
        if (!$this->validate()) {
            return false;
        }
        $this->addFilter();
        return new ActiveDataProvider([
            'query' => $this->query
        ]);
    }

    /**
     * Applies the column filters to the query.
     */
    protected function addFilter() {
        foreach ($this->columns as $column) {
            if (isset($column['searchable']) === true && $column['searchable'] === 'true' && empty($column['search']['value']) === false) {
                $this->query->andWhere(['like', $column['data'], $column['search']['value']]);
            }
        }
    }

}
