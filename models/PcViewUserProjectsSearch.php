<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\PcViewUserProjects;

/**
 * PcViewUserProjectsSearch represents the model behind the search form of `app\models\PcViewUserProjects`.
 */
class PcViewUserProjectsSearch extends PcViewUserProjects
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'project_id', 'ts', 'project_weight', 'area', 'exchange_id', 'enabled', 'rw', 'site_editable'], 'integer'],
            [['name', 'lastname', 'user_office', 'post', 'project', 'office', 'contract_subject', 'contract_company', 'contract_date', 'contract_duration', 'exchange'], 'safe'],
            [['project_enabled'], 'boolean'],
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
        $query = PcViewUserProjects::find();

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
            'project_id' => $this->project_id,
            'ts' => $this->ts,
            'project_weight' => $this->project_weight,
            'area' => $this->area,
            'exchange_id' => $this->exchange_id,
            'enabled' => $this->enabled,
            'project_enabled' => $this->project_enabled,
            'rw' => $this->rw,
            'site_editable' => $this->site_editable,
        ]);

        $query->andFilterWhere(['ilike', 'name', $this->name])
            ->andFilterWhere(['ilike', 'lastname', $this->lastname])
            ->andFilterWhere(['ilike', 'user_office', $this->user_office])
            ->andFilterWhere(['ilike', 'post', $this->post])
            ->andFilterWhere(['ilike', 'project', $this->project])
            ->andFilterWhere(['ilike', 'office', $this->office])
            ->andFilterWhere(['ilike', 'contract_subject', $this->contract_subject])
            ->andFilterWhere(['ilike', 'contract_company', $this->contract_company])
            ->andFilterWhere(['ilike', 'contract_date', $this->contract_date])
            ->andFilterWhere(['ilike', 'contract_duration', $this->contract_duration])
            ->andFilterWhere(['ilike', 'exchange', $this->exchange]);

        return $dataProvider;
    }
}
