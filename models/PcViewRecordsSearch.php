<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\PcViewRecords;

/**
 * PcViewRecordsSearch represents the model behind the search form of `app\models\PcViewRecords`.
 */
class PcViewRecordsSearch extends PcViewRecords
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'project_id', 'exchange_id', 'area', 'extype', 'center_id', 'modifier_id', 'modified_ts', 'register_ts', 'phase', 'op_id', 'op_weight', 'project_weight', 'weight', 'priority', 'op_type'], 'integer'],
            [['name', 'abbr', 'center_abbr', 'center_name', 'site_id', 'kv_code', 'address', 'position', 'modifier_name', 'modifier_lastname', 'modifier_office', 'operation', 'op_value'], 'safe'],
            [['done'], 'boolean'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = PcViewRecords::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'project_id' => $this->project_id,
            'exchange_id' => $this->exchange_id,
            'area' => $this->area,
            'extype' => $this->extype,
            'center_id' => $this->center_id,
            'done' => $this->done,
            'modifier_id' => $this->modifier_id,
            'modified_ts' => $this->modified_ts,
            'register_ts' => $this->register_ts,
            'phase' => $this->phase,
            'op_id' => $this->op_id,
            'op_weight' => $this->op_weight,
            'project_weight' => $this->project_weight,
            'weight' => $this->weight,
            'priority' => $this->priority,
            'op_type' => $this->op_type,
        ]);

        $query->andFilterWhere(['ilike', 'name', $this->name])
            ->andFilterWhere(['ilike', 'abbr', $this->abbr])
            ->andFilterWhere(['ilike', 'center_abbr', $this->center_abbr])
            ->andFilterWhere(['ilike', 'center_name', $this->center_name])
            ->andFilterWhere(['ilike', 'site_id', $this->site_id])
            ->andFilterWhere(['ilike', 'kv_code', $this->kv_code])
            ->andFilterWhere(['ilike', 'address', $this->address])
            ->andFilterWhere(['ilike', 'position', $this->position])
            ->andFilterWhere(['ilike', 'modifier_name', $this->modifier_name])
            ->andFilterWhere(['ilike', 'modifier_lastname', $this->modifier_lastname])
            ->andFilterWhere(['ilike', 'modifier_office', $this->modifier_office])
            ->andFilterWhere(['ilike', 'operation', $this->operation])
            ->andFilterWhere(['ilike', 'op_value', $this->op_value]);

        return $dataProvider;
    }
}
