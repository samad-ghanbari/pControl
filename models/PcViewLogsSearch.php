<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\PcViewLogs;

/**
 * PcViewLogsSearch represents the model behind the search form of `\app\models\PcViewLogs`.
 */
class PcViewLogsSearch extends PcViewLogs
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'exchange_id', 'area', 'ts', 'project_id'], 'integer'],
            [['name', 'lastname', 'office', 'post', 'exchange', 'site_id', 'kv_code', 'action', 'project'], 'safe'],
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
        $query = PcViewLogs::find();

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
            'user_id' => $this->user_id,
            'exchange_id' => $this->exchange_id,
            'area' => $this->area,
            'ts' => $this->ts,
            'project_id' => $this->project_id,
        ]);

        $query->andFilterWhere(['ilike', 'name', $this->name])
            ->andFilterWhere(['ilike', 'lastname', $this->lastname])
            ->andFilterWhere(['ilike', 'office', $this->office])
            ->andFilterWhere(['ilike', 'post', $this->post])
            ->andFilterWhere(['ilike', 'exchange', $this->exchange])
            ->andFilterWhere(['ilike', 'site_id', $this->site_id])
            ->andFilterWhere(['ilike', 'kv_code', $this->kv_code])
            ->andFilterWhere(['ilike', 'action', $this->action])
            ->andFilterWhere(['ilike', 'project', $this->project]);

        return $dataProvider;
    }
}
