<?php

namespace app\models;
use yii\base\Model;

/**
 *
 * @property int|null $id
 * @property int $area
 * @property string|null $name
 * @property string|null $abbr
 * @property string|null $type
 * @property string|null $center
 * @property int|null $center_id
 * @property string|null $site_id
 * @property string|null $kv_code
 * @property string|null $address
 * @property string|null $position
 * @property int|null $phase
 */
class XlsLomModel extends Model
{
    public $projectId, $equipment, $description, $quantity, $area2, $area3, $area4, $area5, $area6, $area7, $area8, $done;
}
