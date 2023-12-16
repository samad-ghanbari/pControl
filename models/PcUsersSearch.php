<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\PcUsers;

/**
 * PcUsersSearch represents the model behind the search form of `app\models\PcUsers`.
 */
class PcUsersSearch extends PcUsers
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'enabled'], 'integer'],
            [['name', 'lastname', 'nid', 'employee_code', 'office', 'post', 'tel', 'password', 'action_role'], 'safe'],
            [['admin', 'reset_password'], 'boolean'],
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
        $query = PcUsers::find();

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
            'admin' => $this->admin,
            'reset_password' => $this->reset_password,
            'enabled' => $this->enabled,
        ]);

        $query->andFilterWhere(['ilike', 'name', $this->name])
            ->andFilterWhere(['ilike', 'lastname', $this->lastname])
            ->andFilterWhere(['ilike', 'nid', $this->nid])
            ->andFilterWhere(['ilike', 'employee_code', $this->employee_code])
            ->andFilterWhere(['ilike', 'office', $this->office])
            ->andFilterWhere(['ilike', 'post', $this->post])
            ->andFilterWhere(['ilike', 'tel', $this->tel])
            ->andFilterWhere(['ilike', 'action_role', $this->action_role])
            ->andFilterWhere(['ilike', 'password', $this->password]);

        return $dataProvider;
    }
}
